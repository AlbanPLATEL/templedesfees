<?php

namespace App\Filament\Widgets;

use App\Enums\HealthTestType;
use App\Enums\ReservationStatus;
use App\Models\AdoptionRequest;
use App\Models\Cat;
use App\Models\ContactMessage;
use App\Models\HealthTest;
use App\Models\Kitten;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Setting;
use Filament\Widgets\Widget;

/**
 * Ce qui attend.
 *
 * Le premier ecran du back-office ne doit pas demander « que voulez-vous
 * faire ? » mais repondre « voila ce qui attend ». Une eleveuse ouvre son
 * administration entre deux biberons : elle n'a pas le temps de parcourir
 * neuf rubriques pour verifier qu'aucune ne reclame quelque chose.
 *
 * Chaque ligne dit ce qui manque, combien, et mene directement au bon
 * endroit. Quand tout est en ordre, la liste le dit aussi — un ecran vide
 * laisserait croire a une panne.
 */
class CeQuiAttend extends Widget
{
    protected string $view = 'filament.widgets.ce-qui-attend';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -3;

    /*
     * Filament charge ses encarts en differe, au moment ou ils entrent dans le
     * champ. C'est bon pour un tableau lourd ; c'est mauvais ici. Cet encart
     * est la premiere chose que l'eleveuse doit lire en arrivant, et il ne
     * coute que sept comptages : il s'affiche avec la page.
     */
    protected static bool $isLazy = false;

    /**
     * @return array<int, array{titre: string, detail: string, nombre: int, url: string, ton: string}>
     */
    public function getTaches(): array
    {
        return collect([
            $this->chatonsEnBrouillon(),
            $this->reservationsEnAttente(),
            $this->demandesNouvelles(),
            $this->messagesNonTraites(),
            $this->avisEnAttente(),
            $this->mentionsIncompletes(),
            $this->facturesIncompletes(),
            $this->echographiesPerimees(),
            $this->fichesSansPhoto(),
        ])->filter(fn (?array $t) => $t !== null && $t['nombre'] > 0)->values()->all();
    }

    /**
     * Un chaton sans numero d'identification ou sans numero de portee LOOF ne
     * peut pas etre publie : c'est la loi sur les annonces de cession. La
     * regle est appliquee ailleurs, silencieusement — ici on la rend visible,
     * sans quoi l'eleveuse cherche pourquoi sa fiche n'apparait pas.
     */
    private function chatonsEnBrouillon(): ?array
    {
        $chatons = Kitten::with('litter')->get()->reject->mentionsCompletes();

        if ($chatons->isEmpty()) {
            return null;
        }

        $manques = $chatons->flatMap(function (Kitten $k) {
            $m = [];
            if (blank($k->icad_numero)) {
                $m[] = 'numéro ICAD';
            }
            if (blank($k->litter?->loof_portee_numero)) {
                $m[] = 'numéro de portée LOOF';
            }

            return $m;
        })->unique()->implode(' et ');

        return [
            'titre'  => $chatons->count() > 1 ? 'Fiches chaton à compléter' : 'Fiche chaton à compléter',
            'detail' => "Il manque le $manques. La fiche reste en ligne et annonce que l'identification est en cours, mais aucun départ ne peut se faire avant — c'est une obligation légale, pas un réglage.",
            'nombre' => $chatons->count(),
            'url'    => route('filament.admin.resources.kittens.index'),
            'ton'    => 'attention',
        ];
    }


    /**
     * Les reservations dont l'acompte n'est pas arrive.
     *
     * Celles dont le delai est passe sont signalees a part : le menage du
     * matin les libere, mais entre-temps l'eleveuse doit savoir qu'une
     * famille n'a pas donne suite — c'est un coup de telephone a passer, pas
     * une ligne a laisser filer.
     */
    private function reservationsEnAttente(): ?array
    {
        $attente = Reservation::where('statut', ReservationStatus::EnAttente)->with('kitten')->get();

        if ($attente->isEmpty()) {
            return null;
        }

        $perimees = $attente->filter->estPerimee();

        return [
            'titre'  => $attente->count() > 1 ? 'Acomptes en attente' : 'Acompte en attente',
            'detail' => $perimees->isNotEmpty()
                ? $perimees->count().' a dépassé le délai — '.$perimees->pluck('kitten.nom')->filter()->implode(', ').'. Le chaton sera remis en vente demain matin.'
                : 'Le lien de paiement est parti, l’acompte n’est pas encore arrivé. Le chaton reste proposable en attendant.',
            'nombre' => $attente->count(),
            'url'    => route('filament.admin.resources.reservations.index'),
            'ton'    => $perimees->isNotEmpty() ? 'urgent' : 'attention',
        ];
    }

    private function demandesNouvelles(): ?array
    {
        $n = AdoptionRequest::where('statut', 'nouveau')->count();

        return $n === 0 ? null : [
            'titre'  => $n > 1 ? 'Demandes d’adoption à lire' : 'Demande d’adoption à lire',
            'detail' => 'Personne n’a encore ouvert ces dossiers. Le site annonce une réponse sous 48 heures.',
            'nombre' => $n,
            'url'    => route('filament.admin.resources.adoption-requests.index'),
            'ton'    => 'urgent',
        ];
    }

    private function messagesNonTraites(): ?array
    {
        $n = ContactMessage::where('est_traite', false)->count();

        return $n === 0 ? null : [
            'titre'  => $n > 1 ? 'Messages sans réponse' : 'Message sans réponse',
            'detail' => 'Reçus par le formulaire de contact. Cochez « traité » une fois la réponse partie.',
            'nombre' => $n,
            'url'    => route('filament.admin.resources.contact-messages.index'),
            'ton'    => 'urgent',
        ];
    }

    private function avisEnAttente(): ?array
    {
        $n = Review::where('est_publie', false)->count();

        return $n === 0 ? null : [
            'titre'  => $n > 1 ? 'Avis à relire' : 'Avis à relire',
            'detail' => 'Déposés par des adoptants. Rien n’apparaît sur le site avant votre relecture.',
            'nombre' => $n,
            'url'    => route('filament.admin.resources.reviews.index'),
            'ton'    => 'normal',
        ];
    }

    /**
     * Les mentions obligatoires manquantes. Elles s'affichent « À compléter »
     * en or sur le site, ce qui est volontaire — mais elles deviennent
     * bloquantes le jour ou l'on encaisse un acompte en ligne.
     */
    private function mentionsIncompletes(): ?array
    {
        $attendues = [
            'legal.siren'      => 'SIREN',
            'legal.certificat' => 'certificat de capacité',
            'legal.directeur'  => 'directeur de la publication',
            'legal.hebergeur'  => 'hébergeur',
        ];

        $manquantes = collect($attendues)
            ->reject(fn ($libelle, $cle) => filled(Setting::get($cle)));

        return $manquantes->isEmpty() ? null : [
            'titre'  => 'Mentions légales à compléter',
            'detail' => 'Il manque : '.$manquantes->implode(', ').'. Elles s’affichent « à compléter » sur le site en attendant.',
            'nombre' => $manquantes->count(),
            'url'    => route('filament.admin.resources.settings.index'),
            'ton'    => 'attention',
        ];
    }

    /**
     * Ce qui manque aux factures deja emises.
     *
     * Ces deux mentions n'ont aucune importance tant qu'aucun acompte n'a ete
     * encaisse — d'ou le comptage des factures avant tout le reste. Le jour ou
     * une facture est partie sans elles, en revanche, il est trop tard pour la
     * corriger : on le dit des la premiere.
     */
    private function facturesIncompletes(): ?array
    {
        $factures = Reservation::whereNotNull('facture_numero')
            ->where('facture_numero', 'not like', 'DEMO-%')
            ->count();

        if ($factures === 0) {
            return null;
        }

        $manquantes = collect([
            'legal.tva'       => 'la mention de TVA',
            'elevage.adresse' => 'l’adresse postale',
        ])->reject(fn ($libelle, $cle) => filled(Setting::get($cle)));

        return $manquantes->isEmpty() ? null : [
            'titre'  => 'Mentions manquantes sur les factures',
            'detail' => 'Il manque '.$manquantes->implode(' et ')
                .' — '.$factures.' facture'.($factures > 1 ? 's' : '').' déjà émise'
                .($factures > 1 ? 's' : '').'. À compléter dans Le site › Réglages.',
            'nombre' => $manquantes->count(),
            'url'    => route('filament.admin.resources.settings.index'),
            'ton'    => 'attention',
        ];
    }

    /**
     * Une echocardiographie ne vaut que pour le jour ou elle a ete faite. Au
     * dela d'un an sur un reproducteur, le site affiche un resultat que plus
     * rien ne garantit.
     */
    private function echographiesPerimees(): ?array
    {
        $n = HealthTest::where('type', HealthTestType::HcmEcho)
            ->whereNotNull('date_examen')
            ->where('date_examen', '<', now()->subYear())
            ->whereHas('cat', fn ($q) => $q->whereIn('role', ['etalon', 'reproductrice']))
            ->count();

        return $n === 0 ? null : [
            'titre'  => $n > 1 ? 'Échocardiographies à refaire' : 'Échocardiographie à refaire',
            'detail' => 'Plus d’un an. Un test ADN vaut pour la vie, une échographie seulement pour le jour où elle a été faite.',
            'nombre' => $n,
            'url'    => route('filament.admin.resources.cats.index'),
            'ton'    => 'attention',
        ];
    }

    private function fichesSansPhoto(): ?array
    {
        $chats = Cat::whereNull('photo_principale')->orWhere('photo_principale', '')->count();
        $chatons = Kitten::whereNull('photo_principale')->orWhere('photo_principale', '')->count();
        $n = $chats + $chatons;

        return $n === 0 ? null : [
            'titre'  => 'Fiches sans photo',
            'detail' => 'Une planche gravée tient la place en attendant. Une photo prise au téléphone suffit : elle est redressée et allégée à l’envoi.',
            'nombre' => $n,
            'url'    => route('filament.admin.resources.cats.index'),
            'ton'    => 'normal',
        ];
    }
}
