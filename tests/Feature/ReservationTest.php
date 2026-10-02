<?php

namespace Tests\Feature;

use App\Enums\KittenStatus;
use App\Filament\Resources\AdoptionRequests\Pages\EditAdoptionRequest;
use App\Models\AdoptionRequest;
use App\Enums\ReservationStatus;
use App\Models\Kitten;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Les reservations et l'acompte.
 *
 * C'est la seule partie du site qui touche a l'argent et qui retire un chaton
 * de la vente. Chaque regle y est verifiee separement, y compris celles qui
 * paraissent evidentes : « payer deux fois ne paie pas deux fois » est
 * exactement le genre de chose qu'un webhook rejoue casse en silence.
 */
class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private function chatonDisponible(): Kitten
    {
        $this->seed();

        return Kitten::where('statut', KittenStatus::Disponible)->firstOrFail();
    }

    private function reservationPour(Kitten $chaton, array $attributs = []): Reservation
    {
        return Reservation::create(array_merge([
            'kitten_id'        => $chaton->id,
            'prenom'           => 'Camille',
            'nom'              => 'Dupuis',
            'email'            => 'camille@example.test',
            'acompte_centimes' => 30000,
            'expire_le'        => now()->addDays(7),
        ], $attributs));
    }

    /* ── la regle centrale ───────────────────────────────────────── */

    public function test_une_reservation_en_attente_ne_bloque_pas_le_chaton(): void
    {
        $chaton = $this->chatonDisponible();
        $this->reservationPour($chaton);

        $this->assertSame(KittenStatus::Disponible, $chaton->fresh()->statut,
            'Tant que rien n’est payé, le chaton doit rester proposable.');
    }

    public function test_l_acompte_paye_bloque_le_chaton(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $reservation->payer('pi_essai');

        $this->assertSame(ReservationStatus::Payee, $reservation->fresh()->statut);
        $this->assertSame(KittenStatus::Reserve, $chaton->fresh()->statut);
        $this->assertNotNull($reservation->fresh()->paye_le);
    }

    /**
     * Stripe rejoue ses notifications jusqu'a recevoir un accuse de reception.
     * Le second passage ne doit rien changer — ni la date, ni le statut.
     */
    public function test_payer_deux_fois_n_a_pas_d_effet_supplementaire(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $reservation->payer('pi_essai');
        $premierPaiement = $reservation->fresh()->paye_le;

        $this->travel(2)->minutes();
        $reservation->fresh()->payer('pi_autre');

        $apres = $reservation->fresh();

        $this->assertEquals($premierPaiement, $apres->paye_le, 'La date de paiement ne doit pas bouger.');
        $this->assertSame('pi_essai', $apres->stripe_payment_intent);
    }

    /* ── les sorties ─────────────────────────────────────────────── */

    public function test_l_expiration_rend_le_chaton(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton, ['expire_le' => now()->subDay()]);

        $reservation->expirer();

        $this->assertSame(ReservationStatus::Expiree, $reservation->fresh()->statut);
        $this->assertSame(KittenStatus::Disponible, $chaton->fresh()->statut);
    }

    public function test_le_remboursement_remet_le_chaton_en_vente(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $reservation->payer();
        $this->assertSame(KittenStatus::Reserve, $chaton->fresh()->statut);

        $reservation->rembourser('Changement de situation');

        $this->assertSame(ReservationStatus::Remboursee, $reservation->fresh()->statut);
        $this->assertSame(KittenStatus::Disponible, $chaton->fresh()->statut);
    }

    /**
     * Un chaton deja parti ne revient pas en vente parce qu'on annule une
     * vieille reservation.
     */
    public function test_un_chaton_adopte_n_est_jamais_remis_en_vente(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $reservation->payer();
        $chaton->forceFill(['statut' => KittenStatus::Adopte])->save();

        $reservation->rembourser();

        $this->assertSame(KittenStatus::Adopte, $chaton->fresh()->statut);
    }

    /* ── le menage ───────────────────────────────────────────────── */

    public function test_le_menage_libere_les_perimees_et_epargne_les_payees(): void
    {
        $chaton = $this->chatonDisponible();
        $autre = Kitten::where('statut', KittenStatus::Disponible)
            ->where('id', '!=', $chaton->id)->firstOrFail();

        $perimee = $this->reservationPour($chaton, ['expire_le' => now()->subDay()]);
        $payee = $this->reservationPour($autre, ['expire_le' => now()->subDay()]);
        $payee->payer();

        $this->artisan('reservations:menage')->assertSuccessful();

        $this->assertSame(ReservationStatus::Expiree, $perimee->fresh()->statut);
        $this->assertSame(KittenStatus::Disponible, $chaton->fresh()->statut);

        $this->assertSame(ReservationStatus::Payee, $payee->fresh()->statut,
            'Un acompte encaissé ne se libère pas tout seul.');
        $this->assertSame(KittenStatus::Reserve, $autre->fresh()->statut);
    }

    /* ── la page publique ────────────────────────────────────────── */

    public function test_la_page_de_reservation_repond_et_ne_s_indexe_pas(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $this->get($reservation->lienPublic())
            ->assertOk()
            ->assertSee($chaton->nom)
            // L'espace avant l'euro est insecable : un prix coupe en fin de
            // ligne, sur un contrat comme sur une page, fait desordre.
            ->assertSee("300\u{00A0}€")
            ->assertSee('noindex', escape: false);
    }

    public function test_un_jeton_inconnu_renvoie_une_page_introuvable(): void
    {
        $this->seed();

        $this->get(route('reservation.montrer', ['jeton' => str_repeat('x', 40)]))
            ->assertNotFound();
    }

    public function test_on_ne_peut_pas_payer_une_reservation_perimee(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton, ['expire_le' => now()->subDay()]);

        $this->assertFalse($reservation->peutEtrePayee());

        $this->post(route('reservation.payer', ['jeton' => $reservation->jeton]), ['conditions' => '1'])
            ->assertRedirect();

        $this->assertSame(ReservationStatus::EnAttente, $reservation->fresh()->statut);
        $this->assertSame(KittenStatus::Disponible, $chaton->fresh()->statut);
    }

    public function test_le_paiement_exige_l_acceptation_des_conditions(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $this->post(route('reservation.payer', ['jeton' => $reservation->jeton]), [])
            ->assertSessionHasErrors('conditions');

        $this->assertSame(KittenStatus::Disponible, $chaton->fresh()->statut);
    }

    /* ── le reglement hors ligne ─────────────────────── */

    /**
     * L'eleveur peut ne pas vouloir de paiement en ligne.
     *
     * C'est le cas le plus frequent : l'acompte arrive par virement, par
     * cheque ou de la main a la main le jour de la visite. L'administration
     * savait deja l'enregistrer — « Acompte recu (hors ligne) » marque la
     * reservation payee, bloque le chaton et numerote la facture.
     *
     * La page de la famille, elle, ne le savait pas. Sans compte de paiement
     * et sans mode demonstration, elle proposait quand meme « Verser
     * l'acompte », et le bouton repondait que le paiement n'etait pas ouvert.
     * On ne propose pas un geste pour le refuser ensuite.
     */
    private function horsLigne(): void
    {
        config([
            'chatterie.paiement.demonstration'  => false,
            'chatterie.paiement.stripe.cle_secrete' => null,
        ]);
    }

    public function test_sans_paiement_en_ligne_la_page_n_offre_aucun_bouton_de_versement(): void
    {
        $this->horsLigne();

        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $html = $this->get($reservation->lienPublic())->assertOk()->getContent();

        $this->assertStringNotContainsString(
            route('reservation.payer', ['jeton' => $reservation->jeton]),
            $html,
            'La page propose encore de verser l\'acompte alors qu\'aucun paiement '
            .'en ligne n\'est ouvert : le bouton ne peut que refuser.',
        );
    }

    public function test_sans_paiement_en_ligne_la_page_dit_comment_verser_l_acompte(): void
    {
        $this->horsLigne();

        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $this->get($reservation->lienPublic())
            ->assertOk()
            ->assertSee('Comment verser l’acompte', escape: false)
            // Le reste de la page ne bouge pas : le chaton, le montant, le contrat.
            ->assertSee($chaton->nom)
            ->assertSee("300\u{00A0}€")
            ->assertSee(route('reservation.contrat', ['jeton' => $reservation->jeton]), escape: false);
    }

    /** Le texte que l'eleveur ecrit depuis son espace passe devant le texte par defaut. */
    public function test_l_eleveur_ecrit_lui_meme_la_marche_a_suivre(): void
    {
        $this->horsLigne();

        \App\Models\Setting::updateOrCreate(
            ['cle' => 'paiement.instructions'],
            ['libelle' => 'Comment verser l\'acompte', 'valeur' => 'Virement sur le compte de la chatterie, RIB sur demande.', 'groupe' => 'contact'],
        );

        \Illuminate\Support\Facades\Cache::flush();

        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $this->get($reservation->lienPublic())
            ->assertOk()
            ->assertSee('Virement sur le compte de la chatterie');
    }

    /**
     * Et le circuit complet : l'acompte arrive autrement, l'eleveur
     * l'enregistre, et la famille retrouve sa facture au meme endroit.
     */
    public function test_l_acompte_encaisse_hors_ligne_bloque_le_chaton_et_emet_la_facture(): void
    {
        $this->horsLigne();

        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $reservation->payer();

        $this->assertSame(ReservationStatus::Payee, $reservation->fresh()->statut);
        $this->assertSame(KittenStatus::Reserve, $chaton->fresh()->statut);
        $this->assertTrue($reservation->fresh()->aUneFacture());

        // Hors demonstration, la facture prend une serie datee, pas « DEMO ».
        $this->assertStringStartsWith(now()->format('Y').'-', $reservation->fresh()->facture_numero);

        $this->get(route('reservation.facture', ['jeton' => $reservation->jeton]))->assertOk();
    }

    /* ── la notification de paiement ─────────────────────────────── */

    /**
     * Sans signature valable, n'importe qui connaissant l'adresse du webhook
     * pourrait declarer un acompte paye et bloquer un chaton.
     */
    public function test_une_notification_sans_signature_valable_est_refusee(): void
    {
        $this->seed();
        config(['chatterie.paiement.stripe.webhook' => 'whsec_essai']);

        $this->postJson(route('paiement.notification'), ['type' => 'checkout.session.completed'])
            ->assertStatus(400);
    }

    /* ── le contrat et la facture ────────────────────────────────── */

    public function test_le_contrat_existe_des_la_creation_et_porte_les_bons_montants(): void
    {
        $chaton = $this->chatonDisponible();
        $chaton->forceFill(['prix_centimes' => 150000])->save();

        $reservation = $this->reservationPour($chaton, ['prix_centimes' => 150000]);

        $this->get(route('reservation.contrat', ['jeton' => $reservation->jeton]))
            ->assertOk()
            ->assertSee($chaton->nom)
            ->assertSee('Camille Dupuis')
            ->assertSee("1 500\u{00A0}€")           // le prix
            ->assertSee("300\u{00A0}€")             // l'acompte
            ->assertSee("1 200\u{00A0}€")           // le solde, calcule tout seul
            ->assertSee('noindex', escape: false);
    }

    /**
     * Une facture d'acompte atteste un versement recu. Tant qu'il n'est pas
     * arrive, il n'y a rien a montrer — et surtout rien a numeroter.
     */
    public function test_la_facture_n_existe_pas_avant_l_encaissement(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $this->assertNull($reservation->facture_numero);

        $this->get(route('reservation.facture', ['jeton' => $reservation->jeton]))
            ->assertRedirect(route('reservation.montrer', ['jeton' => $reservation->jeton]));
    }

    public function test_l_encaissement_emet_la_facture(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton, ['prix_centimes' => 150000]);

        $reservation->payer('pi_essai');
        $reservation->refresh();

        $this->assertSame(now()->format('Y').'-0001', $reservation->facture_numero);
        $this->assertNotNull($reservation->facture_le);

        $this->get(route('reservation.facture', ['jeton' => $reservation->jeton]))
            ->assertOk()
            ->assertSee($reservation->facture_numero)
            ->assertSee('Facture acquittée')
            ->assertSee("300\u{00A0}€");
    }

    /**
     * Stripe rejoue ses notifications jusqu'a recevoir un accuse : une facture
     * qui se renumerote a chaque rejeu n'est plus une facture.
     */
    public function test_la_facture_ne_se_renumerote_pas(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);

        $reservation->payer('pi_essai');
        $numero = $reservation->fresh()->facture_numero;

        $reservation->payer('pi_essai');
        $reservation->emettreLaFacture();

        $this->assertSame($numero, $reservation->fresh()->facture_numero);
    }

    /** Une numerotation a trous est precisement ce qu'un controle ne veut pas voir. */
    public function test_les_numeros_de_facture_se_suivent(): void
    {
        $this->seed();

        $numeros = Kitten::where('statut', KittenStatus::Disponible)
            ->take(2)
            ->get()
            ->map(function (Kitten $chaton) {
                $reservation = $this->reservationPour($chaton);
                $reservation->payer();

                return $reservation->fresh()->facture_numero;
            });

        $annee = now()->format('Y');
        $this->assertSame([$annee.'-0001', $annee.'-0002'], $numeros->all());
    }

    /**
     * Le mode demonstration sert a montrer le parcours avant l'ouverture du
     * compte. Ses acomptes simules ne doivent pas consommer les premiers
     * numeros de l'annee, ni laisser un trou le jour ou on les efface.
     */
    public function test_une_facture_de_demonstration_prend_une_serie_a_part(): void
    {
        config([
            'chatterie.paiement.demonstration'   => true,
            'chatterie.paiement.stripe.cle_secrete' => null,
        ]);

        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);
        $reservation->payer();
        $reservation->refresh();

        $this->assertSame('DEMO-0001', $reservation->facture_numero);
        $this->assertTrue($reservation->factureEstFictive());

        $this->get(route('reservation.facture', ['jeton' => $reservation->jeton]))
            ->assertOk()
            ->assertSee('Document de démonstration');
    }

    /* ── du dossier a la reservation ─────────────────────────────── */

    /**
     * Le parcours voulu : la famille remplit le formulaire de pre-adoption,
     * l'eleveuse appelle, puis prepare la reservation depuis le dossier. Rien
     * de ce que la famille a deja ecrit ne doit etre retape.
     */
    public function test_la_reservation_se_prepare_depuis_le_dossier(): void
    {
        $chaton = $this->chatonDisponible();
        $chaton->forceFill(['prix_centimes' => 150000])->save();

        $dossier = AdoptionRequest::create([
            'prenom'      => 'Camille',
            'nom'         => 'Dupuis',
            'email'       => 'camille@example.test',
            'telephone'   => '06 12 34 56 78',
            'code_postal' => '69007',
            'kitten_id'   => $chaton->id,
        ]);

        $eleveuse = User::where('email', 'letempledesfees@outlook.fr')->firstOrFail();

        Livewire::actingAs($eleveuse)
            ->test(EditAdoptionRequest::class, ['record' => $dossier->getKey()])
            ->assertActionVisible('preparer_reservation')
            ->callAction('preparer_reservation', [
                'kitten_id'   => $chaton->id,
                'adresse'     => '12 rue des Lilas',
                'code_postal' => '69007',
                'ville'       => 'Lyon',
                'prix'        => 1500,
                'acompte'     => 300,
            ]);

        $reservation = Reservation::where('adoption_request_id', $dossier->id)->firstOrFail();

        $this->assertSame('Camille', $reservation->prenom);
        $this->assertSame('camille@example.test', $reservation->email);
        $this->assertSame('06 12 34 56 78', $reservation->telephone);
        $this->assertSame('Lyon', $reservation->ville);
        $this->assertSame(150000, $reservation->prix_centimes);
        $this->assertSame(30000, $reservation->acompte_centimes);

        // Le dossier a abouti : il le dit tout seul, sinon la liste des
        // demandes ne veut plus rien dire au bout de trois mois.
        $this->assertSame('accepte', $dossier->fresh()->statut);

        // Et le chaton reste proposable : rien n'est encore paye.
        $this->assertSame(KittenStatus::Disponible, $chaton->fresh()->statut);
    }

    /**
     * Deux acomptes sur le meme chaton, ce sont deux familles a qui on a
     * promis la meme chose.
     */
    public function test_un_dossier_deja_reserve_ne_propose_plus_le_bouton(): void
    {
        $chaton = $this->chatonDisponible();

        $dossier = AdoptionRequest::create([
            'prenom' => 'Camille',
            'email'  => 'camille@example.test',
        ]);

        $this->reservationPour($chaton, ['adoption_request_id' => $dossier->id]);

        $eleveuse = User::where('email', 'letempledesfees@outlook.fr')->firstOrFail();

        Livewire::actingAs($eleveuse)
            ->test(EditAdoptionRequest::class, ['record' => $dossier->getKey()])
            ->assertActionHidden('preparer_reservation');
    }

    /* ── le back-office ──────────────────────────────────────────── */

    public function test_les_ecrans_de_reservation_repondent(): void
    {
        $chaton = $this->chatonDisponible();
        $reservation = $this->reservationPour($chaton);
        $eleveuse = User::where('email', 'letempledesfees@outlook.fr')->firstOrFail();

        foreach (['/admin/reservations', '/admin/reservations/create'] as $ecran) {
            $this->actingAs($eleveuse)->get($ecran)->assertOk("L'écran $ecran ne répond pas.");
        }

        $this->actingAs($eleveuse)
            ->get(\App\Filament\Resources\Reservations\ReservationResource::getUrl('edit', ['record' => $reservation]))
            ->assertOk();
    }
}
