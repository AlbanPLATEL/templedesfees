<?php

/*
 * Contenu editorial fixe du site de la Chatterie du Temple des Fees.
 *
 * - 'morphologie'    : les reperes cliquables de la page « Le Maine Coon ».
 *                      x et y sont des pourcentages sur la photo affichee ;
 *                      changer de photo impose de les repositionner.
 * - 'couverture'     : le detail de ce que couvre l'adoption (page Adopter).
 * - 'carte'          : la carte de la page Contact.
 * - 'back_office'    : qui a le droit d'entrer dans l'espace de gestion.
 * - 'anciennes_urls' : les adresses du site actuel, a rediriger le jour de la
 *                      bascule.
 *
 * Ces blocs ne bougent quasiment jamais : ils restent en configuration plutot
 * qu'en base. Le reste du contenu (chats, portees, chatons, questions) vit en
 * base de donnees et se modifie depuis le back-office.
 */

return [

    /*
     * Lire un Maine Coon.
     *
     * Rien n'est decoratif chez cette race : chaque trait vient des hivers du
     * Nord-Est americain, ou seuls tenaient les chats bien equipes. C'est
     * l'angle de la page — expliquer a quoi sert ce qu'on regarde.
     *
     * Les coordonnees sont calees sur images/cats/tika.webp, choisie parce
     * qu'elle est assise de profil, corps entier : chaque trait du standard
     * s'y montre. Changer cette photo impose de les reprendre.
     */
    'morphologie' => [
        [
            'x' => 62,
            'y' => 9,
            'categorie' => 'Oreilles',
            'titre' => 'Lynx tips',
            'texte' => 'Les touffes de poils qui prolongent la pointe de l\'oreille. Elles brisent le vent et protègent le conduit du froid. Grandes oreilles larges à la base, plantées haut : c\'est l\'un des traits les plus recherchés du standard.',
        ],
        [
            'x' => 57,
            'y' => 30,
            'categorie' => 'Tête',
            'titre' => 'Museau carré',
            'texte' => 'Le museau du Maine Coon se coupe net, en angle droit vu de profil, avec un menton ferme aligné sous le nez. C\'est ce qui lui donne son air sérieux, et c\'est un point que les juges regardent en premier.',
        ],
        [
            'x' => 55,
            'y' => 41,
            'categorie' => 'Poil',
            'titre' => 'Collerette',
            'texte' => 'La fraise de poils longs autour du cou, très marquée en hiver, beaucoup plus discrète après la mue de printemps. Elle protège la gorge — la zone la plus exposée quand le chat dort en boule dans la neige.',
        ],
        [
            'x' => 25,
            'y' => 55,
            'categorie' => 'Fourrure',
            'titre' => 'Poil mi-long hydrofuge',
            'texte' => 'Court sur les épaules, long sur les flancs et la culotte. Cette répartition n\'est pas un hasard : elle laisse l\'épaule libre pour marcher et couvre ce qui touche le sol. Le poil repousse l\'eau plutôt que de l\'absorber.',
        ],
        [
            'x' => 28,
            'y' => 83,
            'categorie' => 'Queue',
            'titre' => 'Queue en panache',
            'texte' => 'Longue — elle doit atteindre au moins l\'épaule quand on la rabat — et très fournie. Le chat s\'en couvre le museau et les coussinets pour dormir. C\'est une couverture, pas un ornement.',
        ],
        [
            'x' => 44,
            'y' => 68,
            'categorie' => 'Ossature',
            'titre' => 'Corps rectangulaire',
            'texte' => 'Poitrine large, ossature lourde, corps plus long que haut. Un mâle adulte pèse entre six et neuf kilos, et met trois à quatre ans à finir de se construire — deux fois plus longtemps qu\'un chat de gouttière.',
        ],
    ],

    /*
     * Ce que couvre l'adoption.
     *
     * Le prix d'un chaton n'est pas le prix de l'animal : c'est le prix des
     * neuf mois qui le precedent. Cette liste les detaille, poste par poste.
     */
    'couverture' => [
        [
            'numero' => '01',
            'titre' => 'Saillie et suivi de gestation',
            'detail' => 'Échographie de confirmation, alimentation spécifique de la mère pendant neuf semaines, visites vétérinaires.',
            'quand' => 'Avant la naissance',
        ],
        [
            'numero' => '02',
            'titre' => 'Mise bas et première semaine',
            'detail' => 'Surveillance continue jour et nuit, pesée quotidienne, aide à la tétée si un chaton décroche.',
            'quand' => 'Semaine 1',
        ],
        [
            'numero' => '03',
            'titre' => 'Douze semaines de nourrissage',
            'detail' => 'Lait maternisé au besoin, puis pâtée et croquettes kitten de qualité, pour la mère comme pour les petits. Un Maine Coon mange beaucoup, et sa mère encore plus pendant l\'allaitement.',
            'quand' => 'Semaines 1 à 12',
        ],
        [
            'numero' => '04',
            'titre' => 'Deux vermifugations',
            'detail' => 'Protocole complet, renouvelé avant le départ.',
            'quand' => 'Semaines 5 et 9',
        ],
        [
            'numero' => '05',
            'titre' => 'Identification ICAD',
            'detail' => 'Puce électronique posée et enregistrée au nom de l\'élevage, puis transférée à la famille.',
            'quand' => 'Semaine 6',
        ],
        [
            'numero' => '06',
            'titre' => 'Primo-vaccination et rappel',
            'detail' => 'Typhus et coryza, deux injections, carnet de santé tenu à jour.',
            'quand' => 'Semaines 8 et 12',
        ],
        [
            'numero' => '07',
            'titre' => 'Certificat vétérinaire de bonne santé',
            'detail' => 'Établi moins de huit jours avant la cession, obligatoire et remis en main propre.',
            'quand' => 'Avant le départ',
        ],
        [
            'numero' => '08',
            'titre' => 'Inscription LOOF et pedigree',
            'detail' => 'Déclaration de saillie, déclaration de portée, édition du pedigree officiel.',
            'quand' => 'Semaines 1 à 12',
        ],
        [
            'numero' => '09',
            'titre' => 'Dépistages des parents',
            'detail' => 'HCM par test ADN et par échocardiographie, SMA et PK-Def par test ADN, radiographie de hanche, dépistage FIV/FeLV.',
            'quand' => 'Toute l\'année',
        ],
        [
            'numero' => '10',
            'titre' => 'Socialisation quotidienne',
            'detail' => 'Manipulation dès la naissance, habituation aux bruits, aux enfants, au chien, au transport et à la voiture.',
            'quand' => 'Chaque jour',
        ],
        [
            'numero' => '11',
            'titre' => 'Contrat et document d\'information',
            'detail' => 'Contrat de cession écrit, document d\'information sur les besoins de l\'espèce, conseils d\'arrivée.',
            'quand' => 'Au départ',
        ],
        [
            'numero' => '12',
            'titre' => 'Suivi après le départ',
            'detail' => 'Disponibilité à vie pour les questions, et reprise du chat si votre situation change.',
            'quand' => 'Sans limite',
        ],
    ],

    /*
     * Carte de la page Contact.
     *
     * Coordonnees de la commune, pas du portail : elles situent l'elevage a la
     * bonne place sur une carte et dans les donnees structurees sans publier un
     * point GPS exact. La chatterie affiche son adresse complete sur son site
     * actuel — si elle souhaite que la carte y pointe precisement, il suffit de
     * remplacer ces coordonnees et d'ajuster le rayon.
     */
    'carte' => [
        'zone' => [
            'lat'    => 45.2833,
            'lng'    => 4.9833,
            'rayon'  => 1600,          // metres
            'titre'  => 'Lapeyrouse-Mornay',
            'detail' => "L'élevage — chemin Saint-Charles",
        ],
        'reperes' => [
            ['lat' => 45.2408, 'lng' => 4.8147, 'titre' => "Saint-Rambert-d'Albon", 'detail' => '10 min en voiture'],
            ['lat' => 45.0453, 'lng' => 4.8705, 'titre' => 'Valence',               'detail' => '45 min par la D538'],
            ['lat' => 45.4397, 'lng' => 4.3872, 'titre' => 'Saint-Étienne',         'detail' => "1 h par la N88"],
            ['lat' => 45.7640, 'lng' => 4.8357, 'titre' => 'Lyon',                  'detail' => "1 h par l'A7"],
        ],
    ],

    /*
     * Qui entre dans l'espace de gestion. La liste est ici, en clair, et non
     * dans un role en base : ouvrir le back-office a quelqu'un doit etre une
     * decision, pas un effet de bord de la creation d'un compte.
     */
    /*
     * Le livre de la maison.
     *
     * Six pages qui disent ce qui ne se negocie pas chez cette eleveuse.
     * Elles vivent ici plutot qu'en base : ce ne sont pas des donnees qui
     * changent au fil des portees, c'est la ligne de conduite de l'elevage,
     * et elle se modifie en connaissance de cause.
     *
     * Le composant <x-livre> les repartit deux par deux sur ses feuillets :
     * en ajouter ou en retirer suffit, il n'y a pas de mise en page a
     * reprendre. Un nombre impair laisse une derniere page blanche, ce qui
     * est exactement ce qu'on trouve a la fin d'un ouvrage.
     */
    'livre' => [
        [
            'titre' => "L'attente",
            'texte' => "Un chaton part à douze semaines, jamais avant. Ce n’est pas une précaution de confort : c’est le temps qu’il lui faut pour apprendre de sa mère et de sa fratrie ce qu’aucun humain ne peut lui enseigner. Un chaton séparé trop tôt mord, griffe et supporte mal la solitude, et cela ne se rattrape pas.",
        ],
        [
            'titre' => "Le dépistage",
            'texte' => "La cardiomyopathie hypertrophique se cherche de deux façons qui ne se remplacent pas : le test génétique, une fois pour la vie, et l’échocardiographie, qui ne vaut que pour le jour où elle a été faite. Un élevage qui n’affiche que le test ADN n’a fait que la moitié du chemin.",
        ],
        [
            'titre' => "La maison",
            'texte' => "Les chatons naissent au milieu de la maison, pas dans un box au fond du jardin. Ils grandissent avec l’aspirateur, la sonnette, les casseroles et les allées et venues de la maison. C’est ce qui fait un chat qui ne se cache pas sous le canapé le jour où il change de vie.",
        ],
        [
            'titre' => "Le nombre",
            'texte' => "Une à deux portées par an, pas davantage. Une femelle qui enchaîne s’use, et des chatons qui se suivent ne reçoivent plus la même présence. Moins de portées veut dire plus d’attente pour vous, et c’est le seul arrangement possible.",
        ],
        [
            'titre' => "La franchise",
            'texte' => "Un résultat qui manque s’affiche tel quel, en or, sur la fiche du chat concerné. Nous ne masquons pas ce qui n’est pas encore fait, et nous ne publions pas un examen que nous n’avons pas. Vous saurez donc toujours où nous en sommes, y compris quand cela ne nous arrange pas.",
        ],
        [
            'titre' => "Le retour",
            'texte' => "Un chat né ici qui ne peut plus rester chez vous revient ici, à n’importe quel âge et quelle qu’en soit la raison. Cela figure au contrat. Un éleveur qui ne reprend pas ses chats n’est pas un éleveur, c’est un vendeur.",
        ],
    ],

    /*
     * L'acompte.
     *
     * Une reservation nait d'une decision de l'eleveuse, apres la visite : ce
     * n'est pas un panier qu'un inconnu remplit. Elle envoie un lien, la
     * famille paie, le chaton est bloque. Tant que rien n'est paye, le chaton
     * reste proposable — c'est exactement ce que le site promet.
     *
     * Le montant est en centimes, en entier : un acompte en flottant finit
     * toujours par produire un 199,99 la ou on avait tape 200.
     */
    'paiement' => [
        'acompte_defaut_centimes' => (int) env('ACOMPTE_CENTIMES', 30000),
        'delai_jours'             => (int) env('ACOMPTE_DELAI_JOURS', 7),

        /*
         * Les clefs Stripe. Elles n'ont rien a faire dans le depot : elles
         * vivent dans .env, qui n'est pas versionne.
         *
         * Stripe fournit des clefs de TEST, gratuites et immediates, qui font
         * tourner exactement le meme circuit avec la carte 4242 4242 4242 4242.
         * C'est avec elles qu'on montre le parcours a la cliente.
         */
        'stripe' => [
            'cle_publique' => env('STRIPE_KEY'),
            'cle_secrete'  => env('STRIPE_SECRET'),
            'webhook'      => env('STRIPE_WEBHOOK_SECRET'),
        ],

        /*
         * Le mode demonstration.
         *
         * Sans compte Stripe, il remplace la page de paiement par un bouton
         * qui marque la reservation payee. Il sert a montrer le parcours
         * complet a la cliente avant d'ouvrir un compte, et a rien d'autre.
         *
         * Il refuse de s'activer des qu'une clef secrete est renseignee : on
         * ne veut surtout pas d'un bouton « payer sans payer » a cote d'un
         * paiement reel. La page le dit en toutes lettres quand il est actif.
         */
        'demonstration' => (bool) env('PAIEMENT_DEMONSTRATION', false),
    ],

    /*
     * Apercu statique.
     *
     * L'export (php artisan site:export) rejoue le site en HTML pur, pour
     * une mise en ligne sans serveur — une page GitHub, par exemple. Dans ce
     * mode il n'y a personne pour recevoir un formulaire : les trois
     * formulaires du site le disent alors franchement et donnent le
     * telephone et l'adresse electronique, qui eux fonctionnent partout.
     */
    'apercu_statique' => env('APERCU_STATIQUE', false),

    'back_office' => [
        'emails' => [
            'letempledesfees@outlook.fr',
        ],

        /*
         * Le compte de gestion. Le mot de passe vit dans .env, qui n'est pas
         * versionne : ecrit ici, il partirait sur GitHub avec le reste du
         * code, et un mot de passe publie n'en est plus un.
         *
         * Il se pose avec : php artisan back-office:acces
         */
        'compte' => [
            'email'        => env('BACK_OFFICE_EMAIL', 'letempledesfees@outlook.fr'),
            'nom'          => 'La Chatterie du Temple des Fées',
            'mot_de_passe' => env('BACK_OFFICE_MOT_DE_PASSE'),
        ],
    ],

    /*
     * Adresses du site actuel.
     *
     * Le site en ligne est un Next.js dont toutes les URL sont en anglais
     * (/cats, /gallery, /infos/health) alors que le contenu est francais. La
     * refonte les repasse en francais ; ces redirections permanentes evitent
     * que les liens deja partages, les signets et les resultats de recherche
     * existants ne tombent dans le vide le jour de la bascule.
     *
     * /articles n'y figure pas non plus, mais pour la raison inverse : la
     * rubrique existe desormais pour de vrai, au meme chemin. Leurs liens
     * deja partages tombent directement sur la bonne page.
     *
     * /sign-in n'y figure volontairement pas : la page de connexion du site
     * actuel est indexable et liee depuis le menu et le pied de page, ce qui
     * n'a aucune raison d'etre. Elle disparait, sans redirection.
     */
    'anciennes_urls' => [
        'cats'              => 'cats.index',
        'gallery'           => 'gallery',
        'infos'             => 'breed',
        'infos/origins'     => 'breed',
        'infos/nutrition'   => 'breed',
        'infos/health'      => 'breed',
        'infos/preparation' => 'breed',
        'about'             => 'home',
        'legal'             => 'legal',
        'legal/mentions'    => 'legal',
        'legal/privacy'     => 'legal',
        'legal/terms'       => 'legal',
    ],
];
