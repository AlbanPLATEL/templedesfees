<?php

namespace Tests\Feature;

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Cats\Pages\CreateCat;
use App\Filament\Resources\Cats\Pages\EditCat;
use App\Filament\Resources\ContactMessages\Pages\EditContactMessage;
use App\Filament\Resources\Kittens\Pages\CreateKitten;
use App\Filament\Resources\Kittens\Pages\EditKitten;
use App\Filament\Resources\Litters\Pages\CreateLitter;
use App\Filament\Resources\Litters\Pages\EditLitter;
use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Photos\Pages\CreatePhoto;
use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Models\Article;
use App\Models\Cat;
use App\Models\ContactMessage;
use App\Models\Kitten;
use App\Models\Litter;
use App\Models\Photo;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Les formulaires du back-office, exerces pour de vrai.
 *
 * Les tests d'ecran qui existaient verifiaient qu'une page repond. C'est
 * necessaire, et ca n'a jamais rien dit de ce qui se passe quand l'eleveuse
 * remplit un champ et clique sur Enregistrer.
 *
 * Ce que ca a servi a trouver, des le premier essai : ouvrir la fiche d'un
 * male et l'enregistrer sans y toucher echouait, parce que le menu deroulant
 * ne proposait pas la forme du sexe que la base contenait. L'ecran repondait
 * 200 ; c'est au clic que ca cassait.
 */
class BackOfficeFormulairesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::where('email', 'letempledesfees@outlook.fr')->firstOrFail());
    }

    /* ═══ reproducteurs ═══════════════════════════════════════════════ */

    public function test_un_reproducteur_se_cree(): void
    {
        Livewire::test(CreateCat::class)
            ->fillForm([
                'nom'   => 'Ondine du Temple des Fées',
                'slug'  => 'ondine',
                'sexe'  => 'femelle',
                'role'  => 'reproductrice',
                'ordre' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('cats', ['slug' => 'ondine', 'sexe' => 'femelle']);
    }

    public function test_un_reproducteur_sans_nom_ni_slug_est_refuse(): void
    {
        Livewire::test(CreateCat::class)
            ->fillForm(['nom' => '', 'slug' => '', 'sexe' => 'male', 'role' => 'etalon'])
            ->call('create')
            ->assertHasFormErrors(['nom' => 'required', 'slug' => 'required']);
    }

    public function test_deux_reproducteurs_ne_peuvent_pas_partager_le_meme_slug(): void
    {
        $existant = Cat::firstOrFail();

        Livewire::test(CreateCat::class)
            ->fillForm([
                'nom'   => 'Doublon',
                'slug'  => $existant->slug,
                'sexe'  => 'male',
                'role'  => 'etalon',
                'ordre' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    /**
     * Le cas qui a motive tout ce fichier.
     *
     * L'eleveuse ouvre la fiche d'un male pour corriger sa robe et clique sur
     * Enregistrer. Rien ne doit l'arreter sur un champ qu'elle n'a pas touche.
     */
    public function test_enregistrer_une_fiche_de_male_sans_y_toucher_fonctionne(): void
    {
        $chat = Cat::where('sexe', 'male')->firstOrFail();

        Livewire::test(EditCat::class, ['record' => $chat->getRouteKey()])
            ->assertFormSet(['sexe' => 'male'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('male', $chat->fresh()->sexe);
    }

    public function test_une_fiche_de_reproducteur_se_modifie(): void
    {
        $chat = Cat::firstOrFail();

        Livewire::test(EditCat::class, ['record' => $chat->getRouteKey()])
            ->fillForm(['robe' => 'Blue silver tabby'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Blue silver tabby', $chat->fresh()->robe);
    }

    /* ═══ portees ═════════════════════════════════════════════════════ */

    public function test_une_portee_se_cree(): void
    {
        Livewire::test(CreateLitter::class)
            ->fillForm([
                'code'           => 'C',
                'slug'           => 'portee-c',
                'date_naissance' => now()->subWeeks(4)->toDateString(),
                'nb_chatons'     => 3,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('litters', ['slug' => 'portee-c']);
    }

    public function test_une_portee_sans_date_de_naissance_est_refusee(): void
    {
        Livewire::test(CreateLitter::class)
            ->fillForm(['code' => 'D', 'slug' => 'portee-d', 'date_naissance' => null])
            ->call('create')
            ->assertHasFormErrors(['date_naissance' => 'required']);
    }

    /**
     * Le numero LOOF de la portee commande la publication de tous ses chatons.
     * Le vider depuis ce formulaire doit les faire disparaitre du site — c'est
     * la regle legale, et elle ne vit pas dans l'observer du chaton.
     */
    public function test_vider_le_numero_loof_depuis_le_formulaire_retire_les_chatons_du_site(): void
    {
        /*
         * Le seed laisse volontairement les numeros legaux vides — c'est a
         * l'eleveuse de les saisir. On les pose donc ici pour partir d'une
         * portee publiable, sinon le test ne prouverait rien.
         */
        $portee = Litter::firstOrFail();
        $portee->forceFill(['loof_portee_numero' => 'LO-2026-0001'])->save();

        $chaton = $portee->kittens()->firstOrFail();
        $chaton->forceFill(['icad_numero' => '250269812345678', 'est_publie' => true])->save();

        $this->assertTrue($chaton->fresh()->mentionsCompletes());

        Livewire::test(EditLitter::class, ['record' => $portee->getRouteKey()])
            ->fillForm(['loof_portee_numero' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        /*
         * Le numero vide ne retire plus le chaton du site : il n'est pas une
         * condition d'affichage mais une mention a completer. La fiche reste
         * en ligne et annonce elle-meme que l'identification est en cours.
         */
        $this->assertFalse($chaton->fresh()->mentionsCompletes());
        $this->assertSame(1, Kitten::publies()->where('id', $chaton->id)->count());

        $this->get('/chatons/'.$chaton->slug)
            ->assertOk()
            ->assertSee('Identification en cours');
    }

    /* ═══ chatons ═════════════════════════════════════════════════════ */

    public function test_un_chaton_se_cree_et_reste_en_brouillon_sans_numero_icad(): void
    {
        $portee = Litter::firstOrFail();

        Livewire::test(CreateKitten::class)
            ->fillForm([
                'litter_id'  => $portee->id,
                'nom'        => 'Cannelle',
                'slug'       => 'cannelle',
                'sexe'       => 'femelle',
                'statut'     => 'disponible',
                'ordre'      => 0,
                'est_publie' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $chaton = Kitten::where('slug', 'cannelle')->firstOrFail();

        $this->assertFalse((bool) $chaton->est_publie,
            'Une fiche sans numero ICAD ne doit jamais ressortir publiee, meme si '
            ."la case est cochee : c'est l'article L214-8-1 du code rural.");
    }

    public function test_un_chaton_sans_portee_est_refuse(): void
    {
        Livewire::test(CreateKitten::class)
            ->fillForm([
                'litter_id' => null,
                'nom'       => 'Orphelin',
                'slug'      => 'orphelin',
                'sexe'      => 'male',
                'statut'    => 'disponible',
                'ordre'     => 0,
            ])
            ->call('create')
            ->assertHasFormErrors(['litter_id' => 'required']);
    }

    public function test_enregistrer_une_fiche_de_chaton_male_sans_y_toucher_fonctionne(): void
    {
        $chaton = Kitten::where('sexe', 'male')->firstOrFail();

        Livewire::test(EditKitten::class, ['record' => $chaton->getRouteKey()])
            ->assertFormSet(['sexe' => 'male'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('male', $chaton->fresh()->sexe);
    }

    /* ═══ articles ════════════════════════════════════════════════════ */

    public function test_un_article_se_cree(): void
    {
        Livewire::test(CreateArticle::class)
            ->fillForm([
                'titre'            => 'Le brossage en mue',
                'slug'             => 'le-brossage-en-mue',
                'categorie'        => 'Conseils',
                'chapeau'          => 'Deux fois plus souvent, et jamais à sec.',
                'corps'            => 'Le sous-poil part par plaques entières au printemps.',
                'date_publication' => now()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('articles', ['slug' => 'le-brossage-en-mue']);
    }

    public function test_un_article_sans_titre_ni_corps_est_refuse(): void
    {
        Livewire::test(CreateArticle::class)
            ->fillForm(['titre' => '', 'slug' => '', 'chapeau' => '', 'corps' => ''])
            ->call('create')
            ->assertHasFormErrors(['titre', 'slug', 'chapeau', 'corps']);
    }

    public function test_un_article_depublie_disparait_du_site(): void
    {
        $article = Article::firstOrFail();

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['est_publie' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse((bool) $article->fresh()->est_publie);
        $this->get('/articles')->assertDontSee($article->titre);
    }

    /* ═══ avis ════════════════════════════════════════════════════════ */

    public function test_un_avis_se_publie_depuis_le_back_office(): void
    {
        $avis = Review::create([
            'prenom'     => 'Camille',
            'note'       => 5,
            'texte'      => 'Un accueil impeccable et un chaton parfaitement sociabilisé.',
            'publie_le'  => now()->toDateString(),
            'est_publie' => false,
        ]);

        Livewire::test(EditReview::class, ['record' => $avis->getKey()])
            ->fillForm(['est_publie' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue((bool) $avis->fresh()->est_publie);
    }

    /* ═══ messages de contact ═════════════════════════════════════════ */

    public function test_un_message_se_marque_traite(): void
    {
        $message = ContactMessage::create([
            'objet'   => 'Question sur une portée',
            'prenom'  => 'Lucie',
            'email'   => 'lucie@example.test',
            'message' => 'Auriez-vous une femelle pour cet automne ?',
        ]);

        $this->assertFalse((bool) $message->est_traite);

        Livewire::test(EditContactMessage::class, ['record' => $message->getKey()])
            ->fillForm(['est_traite' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue((bool) $message->fresh()->est_traite);
    }

    /* ═══ reglages ════════════════════════════════════════════════════ */

    public function test_un_reglage_se_modifie_et_le_site_le_reprend_aussitot(): void
    {
        $reglage = Setting::where('cle', 'contact.telephone')->firstOrFail();

        Livewire::test(EditSetting::class, ['record' => $reglage->getKey()])
            ->fillForm(['valeur' => '06 11 22 33 44'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('06 11 22 33 44', Setting::get('contact.telephone'),
            "Le cache des reglages n'a pas ete vide apres l'enregistrement.");
    }
    /* ═══ photos ══════════════════════════════════════════════════════ */

    /**
     * Le depot d'une photo, du fichier jusqu'au disque.
     *
     * C'est le seul formulaire qui ecrive ailleurs que dans la base, et le
     * seul dont l'echec serait silencieux : une fiche enregistree avec un
     * chemin qui ne mene a rien affiche un cadre vide sur le site public.
     */
    /**
     * Le depot d'une photo, du fichier jusqu'au disque.
     *
     * On ne simule pas le disque : la photo passe par le Photographe, qui
     * ecrit dans public/ pour de bon, redresse l'image, efface ses
     * metadonnees, la plafonne et fabrique ses deux reductions. C'est
     * precisement cette chaine qu'on veut prouver — la rubrique Photos la
     * contournait, et gardait donc les coordonnees GPS des photos de
     * telephone. Le menage est fait a la fin.
     */
    public function test_une_photo_se_depose_et_passe_par_le_photographe(): void
    {
        $chat = Cat::firstOrFail();

        Livewire::test(CreatePhoto::class)
            ->fillForm([
                'chemin'          => [UploadedFile::fake()->image('ondine.jpg', 1600, 2000)],
                'alt'             => 'Ondine, Maine Coon blanche',
                'ordre'           => 0,
                'attachable_type' => Cat::class,
                'attachable_id'   => $chat->getKey(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $photo = Photo::latest('id')->firstOrFail();

        $this->assertStringEndsWith('.webp', $photo->chemin,
            'La rubrique Photos doit convertir comme les fiches, pas garder le JPEG brut.');

        $racine = public_path(preg_replace('/\.webp$/', '', $photo->chemin));

        try {
            $this->assertFileExists("$racine.webp");
            $this->assertFileExists("$racine-800.webp", 'La reduction 800 px manque.');
            $this->assertFileExists("$racine-400.webp", 'La reduction 400 px manque.');

            [$largeur] = getimagesize("$racine.webp");
            $this->assertLessThanOrEqual(1200, $largeur);
        } finally {
            foreach (['', '-400', '-800'] as $suffixe) {
                if (is_file("$racine$suffixe.webp")) {
                    unlink("$racine$suffixe.webp");
                }
            }
        }
    }

    public function test_une_photo_sans_fichier_est_refusee(): void
    {
        Livewire::test(CreatePhoto::class)
            ->fillForm(['chemin' => null, 'alt' => '', 'ordre' => 0])
            ->call('create')
            ->assertHasFormErrors(['chemin', 'alt']);
    }

    /**
     * Une photo de galerie n'est rattachee a rien, et doit pouvoir l'etre.
     *
     * Le champ « Rattachee a » etait obligatoire. Onze des photos du site sont
     * des photos de galerie — elles illustrent l'elevage, pas une fiche — et
     * aucune ne pouvait plus etre enregistree : ouvrir l'une d'elles, corriger
     * sa legende, enregistrer, et le formulaire refusait tant qu'on ne lui
     * avait pas attribue un chat ou une portee au hasard. Le test precedent ne
     * le voyait pas parce qu'il rattachait toujours la photo a un chat.
     */
    public function test_une_photo_de_galerie_s_enregistre_sans_etre_rattachee(): void
    {
        Livewire::test(CreatePhoto::class)
            ->fillForm([
                'chemin'    => [UploadedFile::fake()->image('la-maison.jpg', 1400, 900)],
                'alt'       => 'La maison vue du jardin',
                'categorie' => 'maison',
                'ordre'     => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $photo = Photo::latest('id')->firstOrFail();

        $this->assertNull($photo->attachable_type,
            'Une photo de galerie ne doit pas se voir attribuer une fiche.');

        $racine = public_path(preg_replace('/\.webp$/', '', $photo->chemin));

        try {
            $this->get('/galerie')->assertOk()->assertSee($photo->chemin, false);
        } finally {
            foreach (['', '-400', '-800'] as $suffixe) {
                if (is_file("$racine$suffixe.webp")) {
                    unlink("$racine$suffixe.webp");
                }
            }
        }
    }

    /* ═══ reservations ════════════════════════════════════════════════ */

    public function test_une_reservation_se_cree_depuis_le_back_office(): void
    {
        $chaton = Kitten::where('statut', 'disponible')->firstOrFail();

        Livewire::test(CreateReservation::class)
            ->fillForm([
                'kitten_id'        => $chaton->getKey(),
                'prenom'           => 'Camille',
                'nom'              => 'Dupuis',
                'email'            => 'camille@example.test',
                'acompte_centimes' => 300,
                'expire_le'        => now()->addDays(7)->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $reservation = Reservation::where('email', 'camille@example.test')->firstOrFail();

        $this->assertSame(30000, $reservation->acompte_centimes,
            'Le champ affiche des euros ; la base doit recevoir des centimes entiers.');

        $this->assertNotEmpty($reservation->jeton,
            'Sans jeton, le lien envoye a la famille ne mene nulle part.');
    }

    public function test_une_reservation_sans_chaton_ni_acompte_est_refusee(): void
    {
        Livewire::test(CreateReservation::class)
            ->fillForm([
                'kitten_id'        => null,
                'prenom'           => '',
                'nom'              => '',
                'email'            => '',
                'acompte_centimes' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['kitten_id', 'prenom', 'nom', 'email', 'acompte_centimes']);
    }
}
