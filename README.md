# La Chatterie du Temple des Fées

Site de la Chatterie du Temple des Fées — élevage de Maine Coon à
Lapeyrouse-Mornay (26210), Drôme. Laravel 12, Blade, MySQL.

Laravel 12 et non 13 : le XAMPP de la machine de dev sert le site en PHP 8.2,
et Laravel 13 exige PHP 8.3. Laravel 12 accepte PHP 8.2 et c'est aussi la
version la mieux couverte par Filament.

Refonte réalisée par Net Strategy.

## Installation

```powershell
composer install
npm install
npm run build          # ou `npm run dev` pendant le développement
php artisan key:generate
```

> PowerShell 5 ne comprend pas `&&` : enchaîner les commandes avec `;`
> ou les lancer une par une.

Créer la base dans phpMyAdmin (XAMPP) :

```sql
CREATE DATABASE templedesfees CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Puis :

```bash
php artisan migrate --seed
```

Le site est accessible sur **http://127.0.0.1:8000** (`php artisan serve`)
ou sur `http://localhost/templedesfees/public` via Apache.

Le seed crée le compte du back-office et **tire un mot de passe au hasard**,
affiché une seule fois à la fin. Pour en choisir un, le poser dans `.env`
avant de lancer le seed :

```
BACK_OFFICE_EMAIL=letempledesfees@outlook.fr
BACK_OFFICE_MOT_DE_PASSE=...
```

Il n'y a volontairement pas de mot de passe par défaut écrit dans le dépôt :
celui-ci part sur GitHub pour l'aperçu, et un mot de passe publié n'en est
plus un.

## Attention : deux PHP sur cette machine

`php` en ligne de commande est un **Herd Lite 8.4 sans l'extension `intl`**,
alors qu'Apache sert le site en **8.2 avec `intl`**. Les tests du back-office
échouent avec le premier et passent avec le second. Lancer la suite avec :

```bash
C:\xampp\php\php.exe artisan test
```

## La charte graphique

`resources/css/app.css` — direction « Sanctuaire » : nuit d'émeraude, dorure,
ivoire. Trois polices auto-hébergées dans `public/fonts`, aucun appel à Google.

- **Cinzel** — capitales romaines : la marque, les titres de chapitre, les noms.
- **Cormorant Garamond** — les grands titres et les citations en italique.
- **Jost** — le texte courant et les étiquettes.

Deux règles à ne pas contourner :

1. **L'or n'est jamais un aplat.** C'est toujours le dégradé `--dorure`, appliqué
   au remplissage d'un texte (classe `.or`) ou à un filet. Un jaune plat casse
   l'illusion de dorure immédiatement.
2. **L'arche est la signature.** Composant `.arche`, trois filets successifs.
   Elle encadre tous les portraits de chats, du hero à la vignette de 60 px.

Le site se lit comme un récit : chaque section de l'accueil est un chapitre
numéroté, annoncé par une frise (`<x-fleuron>`).

## Ce qui est en place

| Domaine | Fichiers |
|---|---|
| Reproducteurs et dépistages | `app/Models/Cat.php`, `HealthTest.php`, `app/Enums/HealthTestType.php` |
| Portées, chatons, suivi | `Litter.php`, `Kitten.php`, `LitterEvent.php` |
| Dossiers adoptants (RGPD) | `AdoptionRequest.php`, `app/Console/Commands/PurgeRgpd.php` |
| Messages de contact (RGPD) | `ContactMessage.php`, `ContactController.php` |
| Réglages et mentions légales | `Setting.php` |
| Pages publiques | `routes/web.php`, `app/Http/Controllers/`, `resources/views/pages/` |
| Charte graphique | `resources/css/app.css` |
| Contenu éditorial fixe | `config/chatterie.php` |
| Contenu de démarrage | `database/seeders/data/content.php` |

## Les dépistages du Maine Coon

La cardiomyopathie hypertrophique est la maladie de la race, et elle se dépiste
de **deux façons qui ne se remplacent pas** :

- le **test génétique MyBPC3** cherche les mutations connues — une fois pour la vie ;
- l'**échocardiographie** regarde le cœur tel qu'il est le jour de l'examen, et
  se renouvelle tant que le chat reproduit.

Un chat indemne génétiquement peut développer une HCM d'une autre origine. Un
élevage qui n'affiche que le test ADN n'a fait que la moitié du chemin.
S'y ajoutent la SMA et le PK-Def (test ADN), la dysplasie de la hanche
(radiographie cotée) et le dépistage FIV/FeLV.

Voir `app/Enums/HealthTestType.php`.

## La règle métier à ne pas contourner

Une fiche chaton **ne peut pas être publiée** tant que son numéro
d'identification ICAD et le numéro de portée LOOF sont vides. C'est une
obligation légale (annonces de cession d'animaux de compagnie), appliquée à
trois niveaux :

1. `Kitten::estPubliable()` — la règle elle-même ;
2. `App\Observers\KittenObserver` — repasse `est_publie` à `false` à chaque
   enregistrement si les numéros manquent, même si la case est cochée ;
3. `Kitten::scopePublies()` — utilisé par toutes les requêtes du site public,
   et `KittenController::show()` renvoie un 404 sur une fiche non publiée.

Le seeder laisse volontairement ces numéros vides : au premier lancement, les
fiches chatons sont donc en brouillon. C'est le comportement attendu.

```powershell
php artisan demo:numeros           # remplit les numéros, publie les fiches
php artisan demo:numeros --reset   # vide les numéros, tout repasse en brouillon
```

Faire l'aller-retour devant la cliente est la démonstration la plus parlante de
la règle. La commande est à supprimer une fois le back-office en place.

Aucun nom d'adoptant n'est jamais affiché côté public. Les statuts
« réservé » et « adopté » portent sur le chaton, pas sur la famille.

## Les données de démarrage

**Les onze chats sont réels** — noms, sexes, robes et années de naissance repris
des fiches publiques du site actuel de la chatterie.

**Les numéros d'identification ne le sont pas.** Le site actuel publie le numéro
de puce de chaque reproducteur en clair ; rien ne l'impose, l'obligation
d'affichage portant sur les annonces de cession, donc sur les chatons à vendre.
Ils sont laissés vides et se saisissent depuis le back-office.

**La portée et les chatons sont une démonstration.** Le site actuel n'annonce
aucune portée en cours. À remplacer par la vraie avant toute mise en ligne.

**Les textes de présentation des chats sont à écrire avec l'éleveuse** : ils
portent tous un `[TEXTE À ÉCRIRE]` bien visible.

## Reste à faire

Par ordre de priorité :

1. **Les photos de chatons et de la maison.** Les onze chats ont leur vraie
   photo, reprise de leur fiche sur le site en ligne. Il manque les chatons
   (5 emplacements : `chaton-1` à `chaton-4` et `portee-b`), occupés par des
   planches gravées aux couleurs de la charte — une case encore vide se lit
   comme une intention, pas comme un site inachevé. Elles se regénèrent avec
   `C:\xampp\php\php.exe scripts/plaques-attente.php`.

   **Ce que je n'ai volontairement pas repris du site en ligne**, et pourquoi :
   - le **bandeau d'accueil** est un montage qui porte l'ancien logo doré, les
     coordonnées de la chatterie et un nom incrustés dans l'image ;
   - les **illustrations des pages d'information** (alimentation, santé, eau,
     origines) sont des photos de banque d'images, et ce ne sont pas des
     Maine Coon ;
   - la photo « à propos » porte le filigrane **« © Julia Bénard |
     arkuswork.com »** : c'est le tirage de contrôle d'une photographe
     professionnelle, à ne pas republier sans sa licence. À signaler à
     l'éleveuse, qui l'utilise peut-être sans le savoir.
2. **Les textes de présentation des chats**, à écrire avec l'éleveuse. Ils
   portent tous un `[TEXTE À ÉCRIRE]` bien visible dans
   `database/seeders/data/content.php`.
3. **La vraie portée** à la place de la portée B de démonstration.
4. **Les mentions légales obligatoires** : SIREN, certificat de capacité,
   directeur de publication, hébergeur. Elles s'affichent « À compléter » en or
   tant qu'elles manquent, ce qui est volontaire.
5. **Envoi des emails** de notification et d'accusé de réception
   (`AdoptionController::store`), une fois le SMTP configuré.
6. **Repositionner les repères de morphologie** (`config/chatterie.php`, clé
   `morphologie`) quand la photo de la page « Le Maine Coon » sera remplacée :
   les coordonnées sont en pourcentages sur l'image actuelle.

Idée non faite, à discuter : éclater « Le Maine Coon » en un hub plus quatre
pages filles (origines, besoins, santé, préparation). La page actuelle couvre
les quatre sujets avec des ancres ; quatre URL distinctes ranqueraient sur
quatre requêtes différentes.

## Carte de la page Contact

Dessinée dans la page, en SVG — aucune tuile, aucune clé, aucun traceur, rien
qui vienne de l'extérieur. Le composant est
`resources/views/components/carte-situation.blade.php`.

Les villes ne sont pas placées à la main : elles sont projetées depuis leurs
coordonnées réelles, dans `config/chatterie.php`, clé `carte`. Corriger une
latitude là-bas déplace le point sur la carte. Les repères visent la commune et
non le portail : la chatterie publie son adresse complète sur son site actuel,
le nouveau ne le fait pas par défaut.

Le fond de plan précédent venait des tuiles sombres de CARTO, qui réclament
désormais une clé : la carte s'affichait barrée de « API KEY REQUIRED ».
Leaflet a été retiré du projet à cette occasion (160 ko de moins).

## Entrer dans le back-office

```bash
php artisan back-office:acces
```

Le compte est créé par le seeder avec un mot de passe aléatoire, affiché une
seule fois. Passée cette ligne, personne ne le connaît plus — et rejouer le
seeder entier pour retrouver l'accès republie des fiches et repose des numéros
de démonstration, ce qui n'a rien à voir.

Le mot de passe se choisit dans `.env`, qui n'est pas versionné :

```
BACK_OFFICE_MOT_DE_PASSE=celui-que-vous-voulez
```

puis la commande le pose et rappelle par où entrer. Il n'est **pas** demandé en
argument : un mot de passe tapé dans un terminal reste en clair dans
l'historique du shell, indéfiniment.

Le panneau est à `/admin`. Il n'ouvre qu'aux adresses listées dans
`config/chatterie.php › back_office.emails` : un compte juste et un mot de
passe juste, avec une adresse absente de cette liste, donnent une porte close
sans explication. La commande le signale.

## En mémoire d'Olimpia

Une page à elle seule, à `/olimpia`. Pas une fiche de chat parmi les autres,
pas une vignette dans la galerie : elle n'est plus de l'élevage, elle en est
l'origine, et un hommage ne se met pas en grille.

Le texte est celui de l'éleveur, à la première personne. Il a été remis en
phrases — la ponctuation, les accords — et rien d'autre : **on ne réécrit pas
le deuil de quelqu'un.** Il vit en réglage (`hommage.texte`) pour qu'il puisse
le reprendre quand il veut, depuis Le site › Réglages.

Deux champs restent vides, et c'est voulu : `hommage.dates`, les deux années,
et `hommage.fille`, l'identifiant de la chatte qu'il décrit comme « exactement
la même ». Ni une date ni une filiation ne se devinent. Tant qu'ils sont vides,
les blocs correspondants n'existent pas — pas d'encadré vide pour faire joli.

La page s'ouvre en **plein écran** : son portrait tient tout l'écran sous le
bandeau, son nom posé dessus, et le seul geste proposé est de descendre. C'est
le même cliché que dans l'arche plus bas, cadré autrement — serré sur la tête
là-haut, entier en dessous. Une affiche, puis une planche.

On y arrive par le **seuil** : un voile plein écran posé par-dessus le site à
l'arrivée. On la regarde, on lit deux phrases, on entre. Trois sorties, aucune
cachée — la croix, le bouton « Entrer sur le site », la touche Échap — plus le
clic à côté de la feuille.

Deux mémoires, et pas une seule. **`sessionStorage`** : refermé, il ne revient
pas de la visite — c'est la règle par défaut. **`localStorage`** : la case
« Ne plus afficher » cochée, il ne revient jamais. Les deux peuvent manquer
(navigation privée, cookies bloqués), donc chaque lecture et chaque écriture
est sous `try` : au pire le seuil se represente, jamais une page qui casse.

`?banniere=1` sur n'importe quelle adresse le force, quelles que soient ces
mémoires : c'est le lien qu'on envoie pour le montrer à quelqu'un.

Il ne paraît ni sur sa propre page, ni sur les pages de réservation : une
famille en train de verser un acompte n'a pas à voir surgir autre chose. Ses
deux phrases vivent en réglage (`hommage.seuil`), comme le reste de sa parole.

Et par deux liens permanents : un bloc en fin de page « Nos chats », et le pied
de page. **Pas dans le menu** — la place y manquait pour une neuvième rubrique,
et une page qui rend hommage n'est pas une rubrique comme les autres.

Ses photos se posent avec `php scripts/photos-olimpia.php <photo1> <photo2> …`.
**La première est la principale** : c'est elle qui ouvre le seuil, qui tient
l'arche de sa page, et qui part en aperçu quand on partage le lien. Les autres
se relaient derrière le texte du seuil, **toutes**, en fondu de deux secondes
et demie toutes les six secondes, et s'alignent en planche de tirages sous son
récit. Les plus petites y passent aussi : étalées en plein écran elles sont
moins nettes que les autres, c'est assumé — le grain et le voile en rattrapent
une bonne part.

Aucune n'est jamais agrandie : si le fichier d'origine fait 1080 px de large,
la plus grande fera 1080 px. Le ré-encodage supprime au passage les métadonnées
EXIF, donc la géolocalisation des clichés — l'adresse de l'élevage n'est pas
publique.

## La réservation et l'acompte

Une réservation **naît d'une décision de l'éleveuse**, après la visite : ce n'est
pas un panier qu'un inconnu remplit. C'est ce que dit le parcours d'adoption sur
le site, et c'est ce que le code applique.

Le circuit suit le parcours annoncé sur le site, dans cet ordre :

1. La famille remplit le **formulaire de pré-adoption**. C'est la seule porte
   d'entrée publique : nulle part sur le site on ne peut réserver un chaton.
2. L'éleveuse appelle, reçoit la famille, et décide.
3. Depuis le dossier, le bouton **« Préparer la réservation »** ouvre une fenêtre
   déjà remplie de ce que la famille a écrit. Il reste l'adresse postale, le prix,
   l'acompte et les deux dates. Le dossier passe en « acceptée » tout seul.
4. Le site produit d'un coup le **contrat de réservation** et un **lien privé** —
   quarante caractères tirés au hasard, pas de compte à créer.
5. La famille lit le contrat, coche, et paie par carte **sur le domaine de
   Stripe**. Aucun numéro de carte ne touche ce site ni n'y est conservé.
6. La notification de Stripe marque l'acompte reçu, **le chaton passe en
   « réservé »**, et la **facture d'acompte** est numérotée.

**Tant que rien n'est payé, le chaton reste proposable.** Une réservation en
attente ne réserve rien. Passé l'échéance, `reservations:menage` — planifiée
tous les matins à 6 h — la fait expirer et rend le chaton.

L'acompte reçu autrement — un chèque remis à la visite, un virement — s'enregistre
par le bouton « Acompte reçu (hors ligne) » : c'est le cas le plus fréquent chez
un éleveur.

### Le contrat et la facture

Les deux documents **s'écrivent tout seuls** à partir de la fiche : le chaton et
ses numéros, la portée et ses parents, la famille, les montants, les dates. Rien
ne se retape, donc rien ne peut diverger entre ce qui est signé et ce qui est
facturé.

Seul le texte des clauses vient d'un réglage (`legal.contrat`, `legal.acompte`),
modifiable dans **Le site › Réglages** : l'éleveuse doit pouvoir le reprendre
avec son conseil sans demander une intervention.

- **Le contrat de réservation** existe dès la création. C'est lui qu'on joint au
  lien. Les conditions de l'acompte y figurent en annexe, pas derrière un lien.
- **La facture d'acompte** n'existe qu'une fois l'acompte encaissé : une facture
  d'acompte atteste un versement, elle ne l'annonce pas.

Les deux s'ouvrent derrière le **même jeton** que la page de paiement — la
famille depuis son lien, l'éleveuse depuis sa fiche. Une seule adresse, un seul
document, jamais une copie qui diverge.

**Pas de PDF côté serveur** : la page *est* le document, imprimable en A4 et
enregistrable en PDF depuis le navigateur. Le fichier qui en sort reste
sélectionnable et cherchable, ce qu'une image collée dans un PDF ne serait pas.
La feuille de style est à part (`resources/css/document.css`) : le site est nuit
et or, un contrat s'imprime sur du papier blanc.

**La numérotation** est chronologique, continue, remise à un chaque année —
`2026-0001`, `2026-0002`. Le numéro est alloué à l'encaissement et jamais avant :
une facture numérotée pour un acompte jamais versé laisserait un trou, et une
numérotation à trous est exactement ce qu'un contrôle ne veut pas voir. Les
acomptes simulés en mode démonstration prennent une série `DEMO-` qui ne
consomme rien.

Le **prix** vit sur la fiche du chaton et n'apparaît sur aucune page publique :
la chatterie n'affiche pas ses tarifs en vitrine. Il sert au contrat et au calcul
du solde. De même pour l'**adresse postale** de l'élevage (`elevage.adresse`) :
un contrat identifie ses parties, la carte de la page Contact pointe la commune.

### Les clefs

Elles vivent dans `.env`, jamais dans le dépôt :

```
ACOMPTE_CENTIMES=30000          # 300 €, en centimes : un montant en flottant
ACOMPTE_DELAI_JOURS=7           # finit toujours par produire un 199,99
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

**Les clefs de test suffisent pour montrer le parcours** : elles sont gratuites
et immédiates, et font tourner exactement le même circuit avec la carte
`4242 4242 4242 4242`. Le webhook se branche sur `/paiement/notification`, et
`stripe listen --forward-to localhost:8000/paiement/notification` le fait
remonter en local.

Sans compte Stripe du tout, `PAIEMENT_DEMONSTRATION=true` remplace le paiement
par un bouton qui marque la réservation payée. Il sert à montrer le parcours et
à rien d'autre : **il refuse de s'activer dès qu'une clef secrète existe**, et la
page l'annonce en toutes lettres.

### Montrer le parcours aujourd'hui

```bash
php artisan demo:numeros        # publie les fiches chatons
php artisan demo:reservation    # crée une réservation et donne le lien
```

Avec `PAIEMENT_DEMONSTRATION=true`, le bouton marque la réservation payée sans
carte ni prélèvement, et le chaton passe en « réservé » sur le site. La page
l'annonce en rouge, en toutes lettres. `demo:reservation --reset` efface tout et
rend les chatons.

Les **conditions de l'acompte** sont écrites et affichées — sur la page de
réservation, juste au-dessus de la case à cocher, et sur les mentions légales.
C'est un **texte de départ**, dans `seeders/data/acompte.php`, modifiable depuis
Le site › Réglages. Deux points méritent une relecture par un professionnel :
l'acompte acquis en cas de renoncement, et le droit de rétractation d'une vente
à distance.

⚠️ Ce texte a été écrit quand le parcours imposait une visite avant toute
réservation : c'est ce déplacement qui devait empêcher la vente d'être conclue
à distance. **L'élevage ne reçoit plus de visite avant le départ**, pour une
raison sanitaire tenant aux chatons non vaccinés. La réservation et l'acompte
se nouent donc désormais sans que personne se soit déplacé, et la question des
quatorze jours de rétractation se pose franchement. À trancher avant la mise
en ligne, pas après.

En revanche, **aucun numéro SIREN ni certificat de capacité n'est inventé**.
Un numéro d'immatriculation fabriqué, publié sur un site, est une fausse mention
légale, même provisoire. Ces champs restent vides et s'affichent « À compléter »
en or, ce que la charte a prévu : c'est honnête, ça se montre, et le tableau de
bord les réclame.

### Ce qui reste bloquant

Encaisser exige le **SIREN**, le **certificat de capacité** et la **mention de
TVA** qui va sur les factures (`legal.tva`, laissée vide : le régime fiscal de
l'élevage ne s'invente pas). Ils s'affichent « à compléter » sur le site en attendant, ce qui est
volontaire — mais ils ne sont pas optionnels le jour où l'on prend de l'argent.
Le tableau de bord les réclame.

Restent à écrire avec l'éleveuse : les **conditions de l'acompte** (déductible,
acquis en cas de renoncement, délai de rétractation). La page de paiement les
résume aujourd'hui en trois lignes qu'il faudra faire valider.

## Les outils de mise au point

`scripts/` contient de quoi fabriquer et regarder :

| Script | Ce qu'il fait |
| --- | --- |
| `banc.py <page> <selecteur> <nom>` | Sort un bloc de page sur `/apercus-banc/<nom>/`, seul et en haut d'une page vide. L'aperçu de la machine de dev refuse de faire défiler : sans lui, tout ce qui n'est pas au premier écran est invisible. |
| `banc-livre.py` | Le même, dédié au livre de l'accueil, sur `/apercus-livre/`. |
| `vignettes.php` | Pose deux réductions (800 et 400 px) à côté de chaque photo. À relancer après chaque ajout. |
| `blason.php <logo>` | Tire du logo le blason doré et les icônes d'onglet. |
| `plaques-attente.php` | Regénère les planches gravées qui tiennent la place d'une photo manquante. |

Les bancs ne sont pas versionnés et n'entrent pas dans l'export.

## Les images

Une photo de chat fait 1200 px. Elle n'est jamais servie telle quelle dans une
vignette : le composant `<x-img>` déclare les réductions en `srcset`, et
l'appelant précise en `sizes` la place que l'image occupe vraiment. **Sans
`sizes`, le navigateur suppose toute la largeur de la fenêtre et reprend
systématiquement la plus grande** — l'attribut n'est donc pas optionnel.

Quand les réductions n'existent pas, pour une photo qu'on vient de déposer, le
composant sert l'original et rien ne casse.

## Aperçu statique (GitHub Pages)

Le site se rejoue en HTML pur, pour être montré sans louer d'hébergement :

```bash
php artisan site:export
```

Chaque adresse est demandée à l'application comme le ferait un navigateur, la
réponse est écrite dans `docs/`, puis **tous les liens sont repris en
relatif**. L'export fonctionne donc à la racine d'un domaine, dans un
sous-dossier (`https://untel.github.io/templedesfees/`) et même ouvert depuis
le disque, sans rien reconfigurer.

Pour publier : pousser le dépôt, puis dans **Settings › Pages**, choisir la
branche et le dossier **/docs**. Aucune action GitHub à écrire, aucun PHP côté
serveur.

Options :

| Option | Effet |
| --- | --- |
| `--sortie=docs` | Dossier de destination (défaut : `docs`) |
| `--base=https://untel.github.io/templedesfees` | Rend absolues les balises canonique et Open Graph, pour un partage propre |
| `--garder` | N'efface pas le dossier avant d'écrire |

Ce que l'export prévoit :

- `.nojekyll`, sans quoi GitHub passerait le dossier à Jekyll ;
- un `robots.txt` interdisant l'indexation et une balise `noindex` sur chaque
  page — l'aperçu ne doit pas se retrouver référencé à côté du vrai site, les
  deux y perdraient ;
- `404.html`, que GitHub Pages sert pour toute adresse inconnue ;
- les trois formulaires (contact, avis, pré-réservation) restent affichés et
  manipulables, mais coiffés d'une note qui dit qu'il n'y a pas de serveur
  derrière, et qui donne le téléphone et l'adresse électronique. C'est
  l'indicateur `chatterie.apercu_statique` qui les bascule, posé par la
  commande.

Ce que l'export ne contient pas : le back-office Filament, qui a besoin de PHP
et de la base. L'aperçu ne montre que le site public.

**Refaire l'export après chaque changement**, sinon `docs/` reste sur
l'ancienne version :

```bash
npm run build
php artisan site:export
```
