<?php

namespace Database\Seeders;

use App\Enums\CatRole;
use App\Enums\HealthTestType;
use App\Models\Article;
use App\Models\Cat;
use App\Models\Faq;
use App\Models\HealthTest;
use App\Models\Kitten;
use App\Models\Litter;
use App\Models\LitterEvent;
use App\Models\Photo;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Remplit la base avec le contenu de la maquette validee.
 * Les numeros LOOF / ICAD / SIREN sont volontairement laisses vides :
 * c'est exactement ce que l'eleveuse doit venir saisir dans le back-office,
 * et tant qu'ils le sont, les fiches chatons restent en brouillon.
 */
class ElevageSeeder extends Seeder
{
    private array $contenu;

    /** Correspondance role du fichier maquette -> enum. */
    private const ROLES = [
        'Étalon'                          => CatRole::Etalon,
        'Reproductrice'                   => CatRole::Reproductrice,
        'Jeune — en observation'          => CatRole::Observation,
    ];

    /** La maquette abrege les statuts ; la base utilise les valeurs de l'enum. */
    private const STATUTS = [
        'dispo'   => 'disponible',
        'reserve' => 'reserve',
        'adopte'  => 'adopte',
    ];

    /**
     * Les deux bilans types.
     *
     * Un test ADN se preleve a n'importe quel age : un chaton peut etre
     * genotype des la premiere semaine. Une echocardiographie et une
     * radiographie de hanche, elles, n'ont de sens que sur un animal qui a
     * fini de grandir — et un Maine Coon met trois a quatre ans. D'ou deux
     * bilans : celui d'un adulte mis a la reproduction, et celui d'un jeune
     * dont il reste la moitie a faire.
     *
     * @var array<string, array<string, array{0:string,1:string}>>
     */
    private const BILANS = [
        'complets' => [
            'hcm_adn'   => ['N/N — indemne', 'Antagene'],
            'hcm_echo'  => ['Normale',       null],
            'sma'       => ['N/N — indemne', 'Antagene'],
            'pk_def'    => ['N/N — indemne', 'Antagene'],
            'dysplasie' => ['À programmer',  null],
            'fiv_felv'  => ['Négatif',       null],
        ],
        'jeune' => [
            'hcm_adn'   => ['N/N — indemne', 'Antagene'],
            'hcm_echo'  => ['À programmer',  null],
            'sma'       => ['N/N — indemne', 'Antagene'],
            'pk_def'    => ['N/N — indemne', 'Antagene'],
            'dysplasie' => ['À programmer',  null],
            'fiv_felv'  => ['Négatif',       null],
        ],
    ];

    public function run(): void
    {
        $this->contenu = require database_path('seeders/data/content.php');

        $this->reglages();
        $chats = $this->chats();
        $portee = $this->porteeEnCours($chats);
        $this->chatons($portee);
        $this->suivi($portee);
        $this->porteeArchivee($chats);
        $this->galerie();
        $this->questions();
        $this->articles();
    }

    /**
     * Les articles de demarrage.
     *
     * Le site en ligne affiche « Aucun article disponible actuellement » : une
     * rubrique vide fait plus de mal que pas de rubrique. Ces trois-la sont
     * ecrits pour tenir tout seuls — ils repondent a des questions que les
     * familles posent vraiment — et servent de modele a l'eleveuse.
     */
    private function articles(): void
    {
        foreach (require database_path('seeders/data/articles.php') as $a) {
            Article::updateOrCreate(['slug' => $a['slug']], [
                'titre'            => $a['titre'],
                'categorie'        => $a['categorie'],
                'chapeau'          => $a['chapeau'],
                'corps'            => $a['corps'],
                'photo_principale' => 'images/cats/'.$a['photo'].'.webp',
                'date_publication' => now()->subDays($a['jours'])->startOfDay(),
                'est_publie'       => true,
            ]);
        }
    }

    private function reglages(): void
    {
        $reglages = [
            ['cle' => 'elevage.nom',          'libelle' => "Nom de l'élevage",        'valeur' => "La Chatterie du Temple des Fées", 'groupe' => 'general'],
            ['cle' => 'elevage.ville',        'libelle' => 'Ville',                    'valeur' => 'Lapeyrouse-Mornay',            'groupe' => 'general'],
            ['cle' => 'elevage.code_postal',  'libelle' => 'Code postal',              'valeur' => '26210',                        'groupe' => 'general'],
            ['cle' => 'elevage.departement',  'libelle' => 'Département',              'valeur' => 'Drôme',                        'groupe' => 'general'],

            /*
             * L'adresse postale complete. Elle ne s'affiche nulle part sur le
             * site — la carte de la page Contact pointe la commune, pas le
             * portail — mais un contrat et une facture identifient leurs
             * parties : ils ont besoin d'une adresse, et c'est leur seul usage.
             */
            ['cle' => 'elevage.adresse',      'libelle' => 'Adresse postale (contrats et factures uniquement, jamais affichée sur le site)',
             'valeur' => '24 chemin Saint-Charles', 'groupe' => 'general'],
            ['cle' => 'contact.telephone',    'libelle' => 'Téléphone',                'valeur' => '06 77 35 45 87',               'groupe' => 'contact'],
            ['cle' => 'contact.itineraire_google', 'libelle' => 'Lien d’itinéraire Google Maps (vide = vers la commune)', 'valeur' => null, 'groupe' => 'contact'],
            ['cle' => 'contact.itineraire_waze',   'libelle' => 'Lien d’itinéraire Waze (vide = vers la commune)',        'valeur' => null, 'groupe' => 'contact'],
            ['cle' => 'contact.avis_google',  'libelle' => 'Lien vers les avis Google', 'valeur' => null, 'groupe' => 'contact'],
            ['cle' => 'contact.email',        'libelle' => 'Email',                    'valeur' => 'letempledesfees@outlook.fr',    'groupe' => 'contact'],
            ['cle' => 'contact.facebook',     'libelle' => 'Facebook',                 'valeur' => 'https://www.facebook.com/chatteriedutempledesfees', 'groupe' => 'contact'],
            ['cle' => 'contact.instagram',    'libelle' => 'Instagram',                'valeur' => 'https://www.instagram.com/chatteriedutempledesfees/', 'groupe' => 'contact'],
            // Mentions obligatoires : vides, donc signalees "À compléter" sur le site.
            ['cle' => 'legal.siren',          'libelle' => 'SIREN / SIRET',            'valeur' => '819 229 394', 'groupe' => 'legal', 'est_obligatoire' => true],
            ['cle' => 'legal.certificat',     'libelle' => 'Numéro ACACED (attestation de connaissances)', 'valeur' => '2018/08b9-fc7e', 'groupe' => 'legal', 'est_obligatoire' => true],
            ['cle' => 'legal.directeur',      'libelle' => 'Directeur de la publication',  'valeur' => 'Kevin Maljournal', 'groupe' => 'legal', 'est_obligatoire' => true],
            ['cle' => 'legal.hebergeur',      'libelle' => 'Hébergeur',                'valeur' => null, 'groupe' => 'legal', 'est_obligatoire' => true],

            /*
             * Les conditions de l'acompte. Elles vivent en reglage et non dans
             * une vue : l'eleveuse doit pouvoir les reprendre avec son conseil
             * sans demander une intervention. Le texte de depart est dans
             * seeders/data/acompte.php, avec les deux points a faire relire.
             */
            ['cle' => 'legal.acompte', 'libelle' => 'Conditions de l’acompte (texte de départ, à faire valider)',
             'valeur' => require database_path('seeders/data/acompte.php'), 'groupe' => 'legal'],

            /*
             * En memoire d'Olimpia Maryliss Country.
             *
             * C'est la parole de l'eleveur, a la premiere personne : elle vit
             * en reglage pour qu'il puisse la reprendre quand il veut. Les
             * deux annees et le nom de sa fille restent vides — on ne devine
             * ni une date ni une filiation.
             */
            ['cle' => 'hommage.texte', 'libelle' => 'En mémoire d’Olimpia — le texte',
             'valeur' => require database_path('seeders/data/olimpia.php'), 'groupe' => 'hommage'],
            /*
             * Le mot. C'est le detail qui porte tout : « il suffisait d'un mot
             * et elle arrivait en courant ». Il a sa page a lui dans le
             * parcours, en tres grand, parce que c'est ce qui reste quand on
             * a tout oublie du reste.
             */
            ['cle' => 'hommage.mot', 'libelle' => 'En mémoire d’Olimpia — le mot qui la faisait accourir',
             'valeur' => 'le poulette', 'groupe' => 'hommage'],

            ['cle' => 'hommage.adieu', 'libelle' => 'En mémoire d’Olimpia — la dernière phrase, celle qui est signée',
             'valeur' => 'Je ne l’oublierai jamais.', 'groupe' => 'hommage'],

            ['cle' => 'hommage.seuil', 'libelle' => 'En mémoire d’Olimpia — les deux phrases affichées à l’arrivée sur le site',
             'valeur' => require database_path('seeders/data/olimpia-seuil.php'), 'groupe' => 'hommage'],
            ['cle' => 'hommage.dates', 'libelle' => 'En mémoire d’Olimpia — les dates (par exemple « 5 mars 2024 — 22 septembre 2025 »)',
             'valeur' => '5 mars 2024 — 22 septembre 2025', 'groupe' => 'hommage'],
            ['cle' => 'hommage.fille', 'libelle' => 'En mémoire d’Olimpia — l’identifiant de sa fille sur le site (son « slug »)',
             'valeur' => 'alaska', 'groupe' => 'hommage'],

            /*
             * Les clauses du contrat de reservation, meme principe. Le
             * document ecrit le reste tout seul a partir de la fiche.
             */
            ['cle' => 'legal.contrat', 'libelle' => 'Clauses du contrat de réservation (texte de départ, à faire valider)',
             'valeur' => require database_path('seeders/data/contrat.php'), 'groupe' => 'legal'],

            /*
             * La mention de TVA d'une facture depend du regime fiscal de
             * l'elevage, que le site n'a aucun moyen de connaitre. Vide, la
             * ligne ne s'imprime pas et le tableau de bord la reclame des
             * qu'une facture est emise.
             */
            /*
             * L'entreprise individuelle Kevin MALJOURNAL n'a pas de numero de
             * TVA intracommunautaire valide — c'est ce que repond le registre
             * national des entreprises. Elle releve donc de la franchise en
             * base, et la mention obligatoire sur ses factures est celle-ci.
             */
            ['cle' => 'legal.tva', 'libelle' => 'Mention de TVA sur les factures (par exemple : « TVA non applicable, article 293 B du CGI »)',
             'valeur' => 'TVA non applicable, article 293 B du CGI', 'groupe' => 'legal'],
        ];

        foreach ($reglages as $r) {
            $existant = Setting::where('cle', $r['cle'])->first();

            /*
             * Le seeder repose le cadre, jamais le contenu. Les conditions de
             * l'acompte et les clauses du contrat sont faites pour etre
             * reecrites depuis le back-office : rejouer le seeder ne doit pas
             * effacer ce que l'eleveuse a mis des heures a relire avec son
             * conseil. Seul le libelle et le groupe se mettent a jour.
             */
            if ($existant) {
                $existant->update(Arr::except($r, ['valeur']));

                continue;
            }

            Setting::create($r);
        }
    }

    /** @return array<string,Cat> indexe par slug de la maquette */
    private function chats(): array
    {
        $chats = [];
        $ordre = 0;

        foreach ($this->contenu['REPROS'] as $slug => $d) {
            $chat = Cat::updateOrCreate(['slug' => $slug], [
                'nom'              => $d['nom'],
                'sexe'             => (string) Str::of($d['sexe'])->lower()->ascii(),
                'role'             => self::ROLES[$d['role']] ?? CatRole::Observation,
                'annee_naissance'  => (int) $d['naissance'],
                'robe'             => $d['robe'],
                'loof_numero'      => null,   // a saisir depuis le back-office
                'icad_numero'      => null,
                'description'      => $d['texte'],
                'photo_principale' => 'images/cats/'.$d['photo'].'.webp',
                // Un seul cliche par chat pour l'instant : la visionneuse le
                // detecte et n'affiche pas de ruban de vignettes vide.
                'photo_secondaire' => isset($d['photo2']) ? 'images/cats/'.$d['photo2'].'.webp' : null,
                'ordre'            => $ordre++,
                'est_publie'       => true,
            ]);

            foreach (self::BILANS[$d['tests']] as $type => [$resultat, $laboratoire]) {
                HealthTest::updateOrCreate(
                    ['cat_id' => $chat->id, 'type' => $type],
                    [
                        'resultat'    => $resultat,
                        'laboratoire' => $laboratoire,
                        'commentaire' => HealthTestType::from($type)->methode(),
                    ],
                );
            }

            $chats[$slug] = $chat;
        }

        return $chats;
    }

    private function porteeEnCours(array $chats): Litter
    {
        $p = $this->contenu['PORTEE'];
        $naissance = Carbon::createFromFormat('d/m/Y', '12/07/2026')->startOfDay();

        return Litter::updateOrCreate(['slug' => 'portee-b-2026'], [
            'code'                => $p['code'],
            'pere_id'             => $chats[$p['pere']]->id,
            'mere_id'             => $chats[$p['mere']]->id,
            'date_naissance'      => $naissance,
            'date_disponibilite'  => $naissance->copy()->addWeeks(Litter::SEMAINES_AVANT_CESSION),
            'loof_portee_numero'  => null,   // a saisir : bloque la publication des chatons
            'nb_chatons'          => $p['nb'],
            'photo_principale'    => 'images/cats/portee-b.webp',
            'est_publiee'         => true,
        ]);
    }

    private function chatons(Litter $portee): void
    {
        $ordre = 0;

        foreach ($this->contenu['CHATONS'] as $d) {
            Kitten::updateOrCreate(['slug' => Str::slug($d['nom'])], [
                'litter_id'        => $portee->id,
                'nom'              => $d['nom'],
                'reference'        => $d['ref'],
                'sexe'             => (string) Str::of($d['sexe'])->lower()->ascii(),
                'robe'             => $d['robe'],
                'statut'           => self::STATUTS[$d['statut']] ?? 'disponible',
                'poids_g'          => (int) preg_replace('/\D/', '', $d['poids'] ?? ''),
                'poids_releve_le'  => now()->subDays(3),
                'icad_numero'      => null,  // a saisir : la fiche reste en brouillon
                'description'      => $d['texte'],
                'photo_principale' => 'images/cats/'.$d['photo'].'.webp',
                'ordre'            => $ordre++,
                'est_publie'       => true,  // l'observer repassera a false : c'est voulu
            ]);
        }
    }

    private function suivi(Litter $portee): void
    {
        $ordre = 0;

        foreach ($this->contenu['ETAPES'] as $e) {
            $date = null;

            if (preg_match('/^(\d{1,2}) (\p{L}+) (\d{4})$/u', $e['when'], $m)) {
                $date = Carbon::createFromFormat('d/m/Y', sprintf(
                    '%02d/%02d/%d', $m[1], $this->mois($m[2]), $m[3]
                ));
            }

            LitterEvent::updateOrCreate(
                ['litter_id' => $portee->id, 'libelle' => $e['what']],
                [
                    'date_evenement' => $date,
                    'date_libelle'   => $date ? null : $e['when'],
                    'est_fait'       => (bool) ($e['done'] ?? false) || (bool) ($e['now'] ?? false),
                    'est_jalon'      => (bool) ($e['now'] ?? false),
                    'ordre'          => $ordre++,
                ],
            );
        }
    }

    private function porteeArchivee(array $chats): void
    {
        $naissance = Carbon::createFromFormat('d/m/Y', '25/03/2025')->startOfDay();

        $portee = Litter::updateOrCreate(['slug' => 'portee-a-2025'], [
            'code'                => 'Portée A',
            'pere_id'             => $chats['karrington']->id,
            'mere_id'             => $chats['solanna']->id,
            'date_naissance'      => $naissance,
            'date_disponibilite'  => $naissance->copy()->addWeeks(Litter::SEMAINES_AVANT_CESSION),
            'nb_chatons'          => 5,
            'description'         => "Cinq chatons, tous partis en famille au cours de l'été 2025. Portée de démonstration.",
            'photo_principale'    => 'images/cats/portee-b.webp',
            'est_publiee'         => true,
        ]);

        // Aucun nom d'adoptant : le statut porte sur le chaton, pas sur la famille.
        foreach (range(1, 5) as $i) {
            Kitten::updateOrCreate(['slug' => "portee-a-chaton-{$i}"], [
                'litter_id'  => $portee->id,
                'nom'        => "Chaton {$i}",
                'reference'  => sprintf('A-%02d', $i),
                'sexe'       => $i % 2 ? 'male' : 'femelle',
                'robe'       => 'Black tortie',
                'statut'     => 'adopte',
                'ordre'      => $i,
                'est_publie' => false,
            ]);
        }
    }

    private function galerie(): void
    {
        /*
         * Les deux photos qu'on avait d'abord prises pour Olimpia. Kevin les a
         * reconnues : « en haut a gauche, en bas a droite c'est sa fille ».
         * Elles appartiennent donc a la fiche d'Alaska, et leur visibilite
         * suit la sienne.
         */
        $alaska = Cat::where('slug', 'alaska')->first();

        if ($alaska) {
            foreach (['alaska-2', 'alaska-3'] as $rang => $fichier) {
                Photo::updateOrCreate(
                    ['chemin' => "images/cats/{$fichier}.webp"],
                    [
                        'attachable_type' => Cat::class,
                        'attachable_id'   => $alaska->id,
                        'alt'             => 'Alaska du Temple des Fées, Maine Coon blanche',
                        'legende'         => 'Alaska, blanche',
                        'ordre'           => $rang,
                        'est_publiee'     => true,
                    ],
                );
            }
        }

        $ordre = 0;

        foreach ($this->contenu['GALERIE'] as $g) {
            Photo::updateOrCreate(
                ['attachable_type' => null, 'attachable_id' => null, 'chemin' => 'images/cats/'.$g['f'].'.webp'],
                [
                    'alt'       => $g['c'],
                    'legende'   => $g['c'],
                    'categorie' => $g['cat'],
                    'ordre'     => $ordre++,
                ],
            );
        }
    }

    private function questions(): void
    {
        $ordre = 0;

        foreach ($this->contenu['FAQ'] as [$question, $reponse]) {
            // Les liens de la maquette pointaient vers les ancres #/... du prototype.
            $reponse = str_replace(
                ["href='#/adoption' data-nav", "href='#/chatons' data-nav"],
                ['href="/adopter"', 'href="/chatons"'],
                $reponse,
            );

            Faq::updateOrCreate(['question' => $question], [
                'reponse' => $reponse,
                'ordre'   => $ordre++,
            ]);
        }
    }

    private function mois(string $nom): int
    {
        $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin',
                 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

        return (int) array_search(Str::lower($nom), $mois, true) + 1;
    }
}
