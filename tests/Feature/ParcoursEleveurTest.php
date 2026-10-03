<?php

namespace Tests\Feature;

use App\Filament\Resources\Kittens\Pages\CreateKitten;
use App\Filament\Resources\Kittens\Pages\EditKitten;
use App\Filament\Resources\Litters\Pages\CreateLitter;
use App\Filament\Resources\Photos\Pages\CreatePhoto;
use App\Models\Kitten;
use App\Models\Litter;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Le parcours de l'eleveur, du depot de la photo jusqu'a l'annonce en ligne.
 *
 * Kevin a pose la question telle quelle : « est-ce que je pourrais moi tout
 * seul gerer comme je veux les photos de mes chatons pour pouvoir les mettre
 * sur le site quand ils sont en vente ? ». Ce fichier y repond par les faits
 * plutot que par une promesse.
 *
 * Il refait exactement ce qu'il fera : creer la portee, creer la fiche d'un
 * chaton avec sa photo prise au telephone, constater qu'elle reste en
 * brouillon tant que le numero ICAD manque — ses petits ont cinq semaines et
 * ne sont pas puces — puis la publier une fois le numero saisi, changer le
 * statut quand le chaton est reserve, et ajouter d'autres photos ensuite.
 *
 * La photo passe par la vraie chaine : GD ouvre le fichier, le redresse,
 * efface ses metadonnees, le plafonne a 1200 px, l'encode en WebP et fabrique
 * les deux reductions. C'est le seul endroit des tests ou l'on ecrit dans
 * public/ pour de bon, parce que c'est precisement ce qu'on veut prouver. Le
 * menage est fait a la fin, quoi qu'il arrive.
 */
class ParcoursEleveurTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> Les fichiers deposes pendant le test. */
    private array $deposees = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::where('email', 'letempledesfees@outlook.fr')->firstOrFail());
    }

    protected function tearDown(): void
    {
        foreach ($this->deposees as $chemin) {
            $base = public_path(preg_replace('/\.webp$/', '', $chemin));

            foreach (['', '-400', '-800'] as $suffixe) {
                if (is_file("$base$suffixe.webp")) {
                    unlink("$base$suffixe.webp");
                }
            }
        }

        parent::tearDown();
    }

    /** Une photo de telephone : assez grande pour declencher les reductions. */
    private function photoDeTelephone(string $nom): UploadedFile
    {
        return UploadedFile::fake()->image($nom, 1600, 2000);
    }

    private function retenir(?string $chemin): void
    {
        if ($chemin) {
            $this->deposees[] = $chemin;
        }
    }

    public function test_l_eleveur_mene_un_chaton_de_la_photo_jusqu_a_l_annonce(): void
    {
        /* ── 1. Il cree la portee, avec son numero LOOF ─────────────── */

        Livewire::test(CreateLitter::class)
            ->fillForm([
                'code'               => 'C',
                'slug'               => 'portee-c',
                'date_naissance'     => now()->subWeeks(5)->toDateString(),
                'nb_chatons'         => 4,
                'loof_portee_numero' => 'LO-2026-0042',
                'est_publiee'        => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $portee = Litter::where('slug', 'portee-c')->firstOrFail();

        /* ── 2. Il cree la fiche du chaton, avec sa photo ───────────── */

        Livewire::test(CreateKitten::class)
            ->fillForm([
                'litter_id'        => $portee->id,
                'nom'              => 'Cannelle',
                'slug'             => 'cannelle-de-test',
                'reference'        => 'C-01',
                'sexe'             => 'femelle',
                'robe'             => 'Blanche',
                'statut'           => 'disponible',
                'ordre'            => 0,
                'photo_principale' => [$this->photoDeTelephone('cannelle-test.jpg')],
                'est_publie'       => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $chaton = Kitten::where('slug', 'cannelle-de-test')->firstOrFail();
        $this->retenir($chaton->photo_principale);

        /* La photo est bien arrivee, redressee, en WebP, avec ses reductions. */

        $this->assertNotEmpty($chaton->photo_principale,
            "La photo n'a pas ete enregistree sur la fiche.");

        $this->assertStringEndsWith('.webp', $chaton->photo_principale,
            'La photo doit etre convertie en WebP, quelle que soit son origine.');

        $racine = public_path(preg_replace('/\.webp$/', '', $chaton->photo_principale));

        $this->assertFileExists("$racine.webp");
        $this->assertFileExists("$racine-800.webp", 'La reduction 800 px manque.');
        $this->assertFileExists("$racine-400.webp", 'La reduction 400 px manque.');

        [$largeur] = getimagesize("$racine.webp");
        $this->assertLessThanOrEqual(1200, $largeur,
            'La photo aurait du etre plafonnee a 1200 px.');

        /* ── 3. Sans numero ICAD, elle reste en brouillon ───────────── */

        /*
         * Ses petits ont cinq semaines et ne sont pas puces. La fiche se
         * publie quand meme : c'est pendant ces semaines-la que les familles
         * se decident, et la cacher jusqu'au pucage reviendrait a la montrer
         * quand tout est deja reserve. Elle annonce elle-meme que
         * l'identification est en cours.
         */
        $this->assertTrue((bool) $chaton->est_publie);

        $this->get('/chatons')->assertOk()->assertSee('Cannelle');

        $this->get("/chatons/{$chaton->slug}")
            ->assertOk()
            ->assertSee('Identification en cours')
            ->assertSee('À compléter');

        /* ── 4. Il saisit le numero, et publie ──────────────────────── */

        Livewire::test(EditKitten::class, ['record' => $chaton->getRouteKey()])
            ->fillForm([
                'icad_numero' => '250269812345678',
                'est_publie'  => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $chaton->refresh();

        $this->assertTrue((bool) $chaton->est_publie);
        $this->assertSame([], $chaton->mentionsManquantes());

        $this->get('/chatons')
            ->assertOk()
            ->assertSee('Cannelle')
            ->assertSee($chaton->photo_principale, false);

        $this->get("/chatons/{$chaton->slug}")
            ->assertOk()
            ->assertSee('Cannelle')
            ->assertSee('Blanche');

        /* ── 5. Le chaton est reserve : il change son statut ────────── */

        Livewire::test(EditKitten::class, ['record' => $chaton->getRouteKey()])
            ->fillForm(['statut' => 'reserve'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('reserve', $chaton->fresh()->statut->value);

        $this->get('/chatons')->assertOk()->assertSee('Réservé');

        /* ── 6. Il ajoute une autre photo a la fiche ────────────────── */

        Livewire::test(CreatePhoto::class)
            ->fillForm([
                'chemin'          => [$this->photoDeTelephone('cannelle-2.jpg')],
                'alt'             => 'Cannelle à cinq semaines',
                'legende'         => 'Cannelle, blanche',
                'ordre'           => 1,
                'attachable_type' => Kitten::class,
                'attachable_id'   => $chaton->getKey(),
                'est_publiee'     => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $seconde = Photo::latest('id')->firstOrFail();
        $this->retenir($seconde->chemin);

        $this->assertFileExists(public_path($seconde->chemin));

        $this->assertCount(2, $chaton->fresh()->galerie(),
            'La fiche doit montrer sa photo principale et celle ajoutee ensuite.');

        $this->get("/chatons/{$chaton->slug}")
            ->assertOk()
            ->assertSee($seconde->chemin, false);

        /* ── 7. Il la retire du site ────────────────────────────────── */

        Livewire::test(EditKitten::class, ['record' => $chaton->getRouteKey()])
            ->fillForm(['est_publie' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse((bool) $chaton->fresh()->est_publie);

        /*
         * Et ses photos partent avec elle. C'est ce que ce test a permis de
         * trouver : la fiche disparaissait bien, mais la photo ajoutee a
         * l'etape 6 continuait de tourner dans le ruban de l'accueil et dans
         * la galerie. L'eleveur avait pourtant fait ce qu'il fallait.
         */
        $this->get('/chatons')->assertDontSee('Cannelle');
        $this->get('/')->assertDontSee('Cannelle à cinq semaines', false);
        $this->get('/galerie')->assertDontSee('Cannelle à cinq semaines', false);
    }

    /**
     * La photo remplacee ne laisse pas l'ancienne derriere elle sur la fiche :
     * l'eleveur doit pouvoir changer d'avis sans que la vignette du site reste
     * bloquee sur la premiere.
     */
    public function test_l_eleveur_peut_remplacer_la_photo_d_un_chaton(): void
    {
        $portee = Litter::firstOrFail();
        $portee->forceFill(['loof_portee_numero' => 'LO-2026-0042'])->save();

        $chaton = $portee->kittens()->firstOrFail();

        Livewire::test(EditKitten::class, ['record' => $chaton->getRouteKey()])
            ->fillForm(['photo_principale' => [$this->photoDeTelephone('premiere.jpg')]])
            ->call('save')
            ->assertHasNoFormErrors();

        $premiere = $chaton->fresh()->photo_principale;
        $this->retenir($premiere);

        Livewire::test(EditKitten::class, ['record' => $chaton->getRouteKey()])
            ->fillForm(['photo_principale' => [$this->photoDeTelephone('seconde.jpg')]])
            ->call('save')
            ->assertHasNoFormErrors();

        $seconde = $chaton->fresh()->photo_principale;
        $this->retenir($seconde);

        $this->assertNotSame($premiere, $seconde,
            "La fiche montre encore l'ancienne photo apres le remplacement.");

        $this->assertFileExists(public_path($seconde));
    }
    /**
     * Les photos de la galerie n'appartiennent a personne, et doivent le rester.
     *
     * Le jour ou une photo a commence a dependre de la fiche qu'elle illustre,
     * les onze photos de la galerie se sont evaporees : elles etaient
     * enregistrees comme appartenant a une portee d'identifiant zero, donc a
     * une fiche introuvable, donc jamais publiee. La galerie et le ruban de
     * l'accueil se sont vides d'un coup, sans qu'aucun test ne bronche.
     */
    public function test_les_photos_sans_rattachement_restent_visibles(): void
    {
        $detachees = Photo::whereNull('attachable_type')->get();

        $this->assertNotEmpty($detachees,
            "Le seed ne fournit aucune photo de galerie : le test ne prouverait rien.");

        $this->assertSame(
            $detachees->count(),
            Photo::publiees()->whereNull('attachable_type')->count(),
            "Une photo qui n'illustre aucune fiche ne depend de personne : elle "
            .'doit rester visible.'
        );

        $reponse = $this->get('/galerie')->assertOk();

        foreach ($detachees->take(3) as $photo) {
            $reponse->assertSee($photo->chemin, false);
        }
    }

    /**
     * Et l'inverse : depublier un reproducteur emporte ses photos.
     */
    public function test_depublier_un_reproducteur_retire_ses_photos_du_site(): void
    {
        $chat = \App\Models\Cat::where('est_publie', true)->firstOrFail();

        $photo = Photo::create([
            'attachable_type' => \App\Models\Cat::class,
            'attachable_id'   => $chat->getKey(),
            /*
             * Un chemin qui n'appartient qu'a ce test : les photos de galerie
             * du seed reprennent les fichiers des fiches, et le meme chemin
             * serait alors visible par une autre ligne que celle qu'on teste.
             */
            'chemin'          => 'images/cats/visibilite-'.$chat->slug.'.webp',
            'alt'             => $chat->nom,
            'ordre'           => 99,
            'est_publiee'     => true,
        ]);

        $this->get('/galerie')->assertOk()->assertSee($photo->chemin, false);

        $chat->forceFill(['est_publie' => false])->save();

        $this->get('/galerie')->assertOk()->assertDontSee($photo->chemin, false);
    }
}
