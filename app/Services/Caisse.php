<?php

namespace App\Services;

use App\Models\Reservation;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * La caisse.
 *
 * Tout ce qui touche a l'argent passe par ici, et rien d'autre dans le site
 * ne connait Stripe. Un changement de prestataire se fait donc dans un seul
 * fichier, et surtout : il n'existe qu'un seul endroit a relire quand on veut
 * verifier ce qui se passe avec un paiement.
 *
 * Aucun numero de carte ne touche jamais ce site. On cree une session de
 * paiement chez Stripe, on y envoie la famille, et Stripe nous previent. Le
 * formulaire de carte est chez eux, sur leur domaine, sous leur certification.
 * C'est la seule maniere responsable de proceder pour un site d'elevage.
 */
class Caisse
{
    /** Le prestataire est-il configure ? */
    public static function estOuverte(): bool
    {
        return filled(config('chatterie.paiement.stripe.cle_secrete'));
    }

    /**
     * Le mode demonstration.
     *
     * Il refuse de s'activer des qu'une clef secrete existe : on ne veut
     * surtout pas d'un bouton « payer sans payer » a cote d'un paiement reel.
     */
    public static function enDemonstration(): bool
    {
        return config('chatterie.paiement.demonstration') && ! self::estOuverte();
    }

    private static function client(): StripeClient
    {
        return new StripeClient(config('chatterie.paiement.stripe.cle_secrete'));
    }

    /**
     * Ouvre une session de paiement et renvoie l'adresse ou envoyer la famille.
     *
     * Le jeton de la reservation voyage dans les metadonnees : c'est lui qu'on
     * relira au retour du webhook. On ne se fie pas a l'identifiant de session
     * seul, qui peut nous manquer si la famille ferme l'onglet avant le
     * retour.
     */
    public static function ouvrirLaSession(Reservation $reservation): string
    {
        $chaton = $reservation->kitten;

        $session = self::client()->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $reservation->email,
            'client_reference_id' => $reservation->jeton,

            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($reservation->devise),
                    'unit_amount' => $reservation->acompte_centimes,
                    'product_data' => [
                        'name' => "Acompte de réservation — {$chaton?->nom}",
                        'description' => 'La Chatterie du Temple des Fées · '
                            .'Acompte déduit du prix du chaton au moment du départ.',
                    ],
                ],
            ]],

            'metadata' => [
                'reservation' => $reservation->jeton,
                'chaton'      => $chaton?->nom,
            ],

            'success_url' => route('reservation.merci', ['jeton' => $reservation->jeton]),
            'cancel_url'  => route('reservation.montrer', ['jeton' => $reservation->jeton]),

            // Le lien de paiement ne doit pas survivre a la reservation.
            'expires_at' => max(
                now()->addMinutes(31)->timestamp,
                min(
                    $reservation->expire_le?->timestamp ?? now()->addDay()->timestamp,
                    now()->addDay()->timestamp
                )
            ),
        ]);

        $reservation->forceFill(['stripe_session_id' => $session->id])->save();

        return $session->url;
    }

    /**
     * Lit une notification de Stripe et renvoie la reservation payee, s'il y
     * en a une.
     *
     * La signature est verifiee avant toute chose : sans elle, n'importe qui
     * connaissant l'adresse du webhook pourrait declarer un acompte paye et
     * bloquer un chaton. C'est le seul point du site ou une verification
     * cryptographique est indispensable.
     *
     * @throws SignatureVerificationException
     */
    public static function lireLaNotification(string $charge, string $signature): ?Reservation
    {
        $evenement = Webhook::constructEvent(
            $charge,
            $signature,
            config('chatterie.paiement.stripe.webhook')
        );

        if ($evenement->type !== 'checkout.session.completed') {
            return null;
        }

        /** @var Session $session */
        $session = $evenement->data->object;

        // Une session peut se terminer sans etre payee : virement en attente,
        // par exemple. On ne bloque un chaton que sur un paiement acquis.
        if ($session->payment_status !== 'paid') {
            return null;
        }

        $jeton = $session->metadata->reservation ?? $session->client_reference_id;

        $reservation = Reservation::where('jeton', $jeton)->first();

        $reservation?->payer(is_string($session->payment_intent) ? $session->payment_intent : null);

        return $reservation;
    }

    /**
     * Rembourse l'acompte.
     *
     * Le remboursement part chez Stripe ; c'est le modele qui remet ensuite le
     * chaton en vente. On ne renvoie pas d'exception a l'eleveuse : elle voit
     * le resultat dans la notification du back-office.
     */
    public static function rembourser(Reservation $reservation): bool
    {
        if (! self::estOuverte() || blank($reservation->stripe_payment_intent)) {
            return false;
        }

        self::client()->refunds->create([
            'payment_intent' => $reservation->stripe_payment_intent,
        ]);

        return true;
    }
}
