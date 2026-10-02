<?php

/*
 * Contenu de demarrage du site de la Chatterie du Temple des Fees.
 *
 * LES CHATS SONT REELS. Noms, sexes, robes et annees de naissance sont repris
 * de la fiche publique de chaque chat sur le site actuel de la chatterie.
 *
 * LES NUMEROS D'IDENTIFICATION NE LE SONT PAS. Le site actuel publie le numero
 * de puce de chaque reproducteur en clair. Rien ne l'impose : l'obligation
 * d'affichage porte sur les annonces de CESSION, donc sur les chatons a vendre,
 * pas sur les adultes de l'elevage. Ils sont donc volontairement laisses vides
 * ici, et se saisissent depuis le back-office si l'eleveuse y tient.
 *
 * LA PORTEE ET LES CHATONS SONT UNE DEMONSTRATION. Le site actuel n'annonce
 * aucune portee en cours. Il en faut une pour montrer le fonctionnement des
 * fiches chaton, du verrou legal et du suivi. A remplacer par la vraie portee
 * avant toute mise en ligne.
 *
 * LES TEXTES DE PRESENTATION sont a reecrire avec l'eleveuse : personne
 * d'autre qu'elle ne connait le caractere de ses chats.
 */

return [

    /*
     * Les onze chats de l'elevage.
     *
     * Le site actuel les etiquette TOUS « Reproducteur », y compris cinq nes
     * en 2025 qui ont un an. Un Maine Coon ne se met pas a la reproduction
     * avant d'avoir fini de grandir — la race met trois a quatre ans a se
     * construire — et ses depistages ne valent rien tant qu'il est jeune. Ils
     * sont donc classes « en observation », ce qui est a la fois exact et
     * beaucoup plus rassurant pour un acheteur qui sait lire.
     */
    'REPROS' => [

        'karrington' => [
            'nom' => 'Karrington Laguna Leo',
            'sexe' => 'Mâle',
            'role' => 'Étalon',
            'naissance' => '2021',
            'robe' => 'Red',
            'photo' => 'karrington',
            'texte' => 'Étalon red de la chatterie, né en octobre 2021. Ossature lourde, museau carré et collerette dense en hiver. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE : caractère, comportement avec les chatons, particularités.]',
            'tests' => 'complets',
        ],

        'solanna' => [
            'nom' => 'Solanna de Laf',
            'sexe' => 'Femelle',
            'role' => 'Reproductrice',
            'naissance' => '2021',
            'robe' => 'Bleu tortie',
            'photo' => 'solanna',
            'texte' => 'La doyenne des reproductrices, née en avril 2021. Robe bleu tortie, écaille diluée aux nuances gris-crème. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'complets',
        ],

        'uriana' => [
            'nom' => 'Uriana du Temple des Fées',
            'sexe' => 'Femelle',
            'role' => 'Reproductrice',
            'naissance' => '2023',
            'robe' => 'Black tortie',
            'photo' => 'uriana',
            'texte' => 'Née à la chatterie en mars 2023. Black tortie au masque partagé, très marquée. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'complets',
        ],

        'tika' => [
            'nom' => 'Tika du Temple des Fées',
            'sexe' => 'Femelle',
            'role' => 'Reproductrice',
            'naissance' => '2022',
            'robe' => 'Red',
            'photo' => 'tika',
            'texte' => 'Née à la chatterie en octobre 2022. Red franc, poil dense et queue en panache. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'complets',
        ],

        'delenn' => [
            'nom' => 'Delenn Laguna Leo',
            'sexe' => 'Femelle',
            'role' => 'Reproductrice',
            'naissance' => '2023',
            'robe' => 'Black smoke',
            'photo' => 'delenn',
            'texte' => 'Black smoke née en juillet 2023 : sous-poil clair, pointe noire, la robe change complètement selon la lumière. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'complets',
        ],

        'boonie' => [
            'nom' => 'Boonie Laguna Leo',
            'sexe' => 'Femelle',
            'role' => 'Reproductrice',
            'naissance' => '2022',
            'robe' => 'Black tortie silver ticked tabby',
            'photo' => 'boonie',
            'texte' => 'Née en avril 2022. Une robe rare : écaille noire sur fond silver, avec un poil tické qui donne un effet de sable mouvant. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'complets',
        ],

        'halunke' => [
            'nom' => 'Langstteich\'s Monte Sexy Halunke',
            'sexe' => 'Mâle',
            'role' => 'Jeune — en observation',
            'naissance' => '2025',
            'robe' => 'Noir',
            'photo' => 'halunke',
            'texte' => 'Jeune mâle noir né en février 2025, arrivé d\'un élevage allemand. Il finit de se construire : pas de mise à la reproduction avant que sa croissance soit terminée et son bilan complet. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'jeune',
        ],

        'helios' => [
            'nom' => 'Champion Meines Herzen\'s Helios',
            'sexe' => 'Mâle',
            'role' => 'Jeune — en observation',
            'naissance' => '2025',
            'robe' => 'Red',
            'photo' => 'helios',
            'texte' => 'Jeune mâle red né en août 2025. Encore en croissance. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'jeune',
        ],

        'kora' => [
            'nom' => 'Champion Meines Herzen\'s Kora',
            'sexe' => 'Femelle',
            'role' => 'Jeune — en observation',
            'naissance' => '2025',
            'robe' => 'Noir',
            'photo' => 'kora',
            'texte' => 'Jeune femelle noire née en mai 2025. Encore en croissance. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'jeune',
        ],

        'aneora' => [
            'nom' => 'A\'Neora du Temple des Fées',
            'sexe' => 'Femelle',
            'role' => 'Jeune — en observation',
            'naissance' => '2025',
            'robe' => 'Red silver ticked tabby',
            'photo' => 'aneora',
            'texte' => 'Née à la chatterie en juin 2025. Red silver ticked tabby — le silver éclaircit la base du poil et fait ressortir le roux. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE.]',
            'tests' => 'jeune',
        ],

        'alaska' => [
            'nom' => 'Alaska du Temple des Fées',
            'sexe' => 'Femelle',
            'role' => 'Jeune — en observation',
            'naissance' => '2025',
            'robe' => 'Blanche',
            'photo' => 'alaska',
            'texte' => 'Sœur de portée d\'A\'Neora, née en juin 2025. Robe entièrement blanche. [TEXTE À ÉCRIRE AVEC L\'ÉLEVEUSE : penser à préciser le test de surdité, souvent demandé sur les chats blancs.]',
            'tests' => 'jeune',
        ],
    ],

    /*
     * PORTEE DE DEMONSTRATION — a remplacer par la vraie.
     * Les chatons naissent sans numero ICAD ni numero de portee LOOF : leurs
     * fiches restent donc en brouillon, ce qui est exactement la regle legale
     * que le site fait respecter.
     */
    'PORTEE' => [
        'code' => 'Portée B',
        'pere' => 'karrington',
        'mere' => 'uriana',
        'nb'   => 4,
    ],

    'CHATONS' => [
        [
            'nom' => 'Baccara', 'ref' => 'B-01', 'sexe' => 'Femelle',
            'robe' => 'Black tortie', 'statut' => 'dispo', 'poids' => '1 240 g',
            'photo' => 'chaton-1',
            'texte' => 'La plus posée de la portée. [TEXTE DE DÉMONSTRATION.]',
        ],
        [
            'nom' => 'Balthazar', 'ref' => 'B-02', 'sexe' => 'Mâle',
            'robe' => 'Red', 'statut' => 'dispo', 'poids' => '1 380 g',
            'photo' => 'chaton-2',
            'texte' => 'Le plus lourd de la portée depuis la troisième semaine. [TEXTE DE DÉMONSTRATION.]',
        ],
        [
            'nom' => 'Bergamote', 'ref' => 'B-03', 'sexe' => 'Femelle',
            'robe' => 'Black smoke', 'statut' => 'reserve', 'poids' => '1 190 g',
            'photo' => 'chaton-3',
            'texte' => 'Réservée. [TEXTE DE DÉMONSTRATION.]',
        ],
        [
            'nom' => 'Brume', 'ref' => 'B-04', 'sexe' => 'Femelle',
            'robe' => 'Blue silver tabby', 'statut' => 'reserve', 'poids' => '1 210 g',
            'photo' => 'chaton-4',
            'texte' => 'Réservée. [TEXTE DE DÉMONSTRATION.]',
        ],
    ],

    /* Le suivi de la portee, affiche en chronologie sur la page Chatons. */
    'ETAPES' => [
        ['when' => '12 juillet 2026',  'what' => 'Naissance — quatre chatons, mise bas sans intervention', 'done' => true],
        ['when' => '16 août 2026',     'what' => 'Première vermifugation',                                  'done' => true],
        ['when' => '23 août 2026',     'what' => 'Sevrage commencé — pâtée kitten',                         'done' => true],
        ['when' => '6 septembre 2026', 'what' => 'Identification ICAD et primo-vaccination',                'now'  => true],
        ['when' => '4 octobre 2026',   'what' => 'Rappel de vaccination et deuxième vermifugation'],
        ['when' => '4 octobre 2026',   'what' => 'Âge légal de cession atteint — douze semaines'],
        ['when' => 'À partir du 4 octobre', 'what' => 'Départ en famille, pedigree LOOF et contrat remis'],
    ],

    /*
     * La galerie. Les fichiers sont pour l'instant des reperes generes : ils
     * portent le nom de l'emplacement a remplir, pas une photo.
     */
    /*
     * La galerie.
     *
     * Uniquement les photos que l'eleveuse a elle-meme televersees sur son
     * site : ce sont les seules dont l'origine soit certaine. Les visuels
     * d'illustration de son site actuel sont des banques d'images, et sa
     * photo de salon porte le filigrane d'une photographe professionnelle —
     * ni les unes ni l'autre ne peuvent etre reprises sans licence.
     *
     * Les photos de chatons et de la maison manquent : elles viendront
     * d'elle.
     */
    'GALERIE' => [
        ['f' => 'karrington', 'c' => 'Karrington, red', 'cat' => 'adultes'],
        ['f' => 'solanna', 'c' => 'Solanna, bleu tortie', 'cat' => 'adultes'],
        ['f' => 'uriana', 'c' => 'Uriana, black tortie', 'cat' => 'adultes'],
        ['f' => 'tika', 'c' => 'Tika, red', 'cat' => 'adultes'],
        ['f' => 'delenn', 'c' => 'Delenn, black smoke', 'cat' => 'adultes'],
        ['f' => 'boonie', 'c' => 'Boonie, black tortie silver ticked tabby', 'cat' => 'adultes'],
        ['f' => 'halunke', 'c' => 'Halunke, noir', 'cat' => 'adultes'],
        ['f' => 'helios', 'c' => 'Helios, red', 'cat' => 'adultes'],
        ['f' => 'kora', 'c' => 'Kora, noire', 'cat' => 'adultes'],
        ['f' => 'aneora', 'c' => "A’Neora, red silver ticked tabby", 'cat' => 'adultes'],
        ['f' => 'alaska', 'c' => 'Alaska, blanche', 'cat' => 'adultes'],
    ],

    /*
     * Les questions frequentes. Elles servent aussi de donnees structurees
     * FAQPage : ce sont elles qui peuvent apparaitre depliees dans Google.
     */
    'FAQ' => [
        [
            'À quel âge un chaton peut-il partir ?',
            'Douze semaines, jamais avant. C\'est l\'âge légal en France, et pour un Maine Coon c\'est aussi le minimum raisonnable : la race grandit lentement et un chaton séparé trop tôt de sa mère garde des difficultés à se réguler, sur la nourriture comme sur le jeu.',
        ],
        [
            'Quels dépistages faites-vous sur les parents ?',
            'La cardiomyopathie hypertrophique par test génétique MyBPC3 et par échocardiographie — ce sont deux examens différents, et le second ne se remplace pas par le premier. S\'y ajoutent l\'amyotrophie spinale (SMA) et le déficit en pyruvate kinase (PK-Def) par test ADN, une radiographie de hanche, et le dépistage FIV/FeLV. Chaque résultat est publié, daté, sur la fiche du chat concerné.',
        ],
        [
            'Pourquoi refaire une échographie cardiaque chaque année ?',
            'Parce qu\'un test ADN dit seulement qu\'un chat ne porte pas les mutations connues. Il ne dit rien de celles qu\'on ne connaît pas encore, ni d\'une HCM d\'origine non génétique. L\'échographie regarde le cœur tel qu\'il est le jour de l\'examen : elle ne vaut que pour ce jour-là, et se refait tant que le chat reproduit.',
        ],
        [
            'Le chaton part-il avec son pedigree ?',
            'Oui. Un chat vendu comme Maine Coon sans pedigree LOOF n\'est pas un Maine Coon au sens légal — c\'est un chat d\'apparence. Le pedigree est remis au départ, ou envoyé dès réception s\'il est encore en cours d\'édition au LOOF.',
        ],
        [
            'Combien coûte un chaton, et pourquoi ce prix ?',
            'Le prix se discute de vive voix, parce qu\'il n\'a de sens qu\'expliqué. Il ne paie pas l\'animal : il paie la saillie, neuf semaines de gestation suivie, douze semaines de nourrissage pour la mère et les petits, deux vermifugations, l\'identification, deux vaccins, le certificat vétérinaire, l\'inscription LOOF, et les dépistages des deux parents. La page « Adopter » détaille les douze postes.',
        ],
        [
            'Peut-on venir voir les chatons avant de réserver ?',
            'Non, et c’est pour eux. Tant qu’ils ne sont pas primo-vaccinés, les chatons n’ont aucune défense contre ce qu’on rapporte de l’extérieur : un typhus ou un coryza voyage très bien sous une semelle, depuis un autre élevage, une animalerie ou un salon. Nous ne faisons aucune exception, pas même pour les familles qui ont déjà réservé. En échange, vous recevez des photos et des nouvelles chaque semaine, et nous sommes au téléphone autant que vous le voulez — en visio si vous préférez. Vous rencontrez votre chaton le jour du départ, et vous repartez avec lui.',
        ],
        [
            'Que se passe-t-il si je ne peux plus garder le chat ?',
            'Il revient ici, à n\'importe quel âge et quelle qu\'en soit la raison. C\'est écrit dans le contrat de cession. Un chat né à la chatterie n\'a rien à faire dans une annonce en ligne ni dans un refuge.',
        ],
        [
            'Un Maine Coon peut-il vivre en appartement ?',
            'Oui, à condition de lui donner de la hauteur et du jeu. C\'est un grand chat, pas un chat d\'extérieur : ce qui lui manque en appartement, ce n\'est pas l\'espace au sol, ce sont les points d\'observation en hauteur et une dépense quotidienne. Un arbre à chat solide, des étagères, et deux séances de jeu par jour suffisent.',
        ],
    ],
];
