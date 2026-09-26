<?php

namespace Tests\Feature;

use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Cats\Pages\EditCat;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Filament\Resources\Kittens\Pages\EditKitten;
use App\Filament\Resources\Litters\Pages\EditLitter;
use App\Filament\Resources\Litters\RelationManagers\EventsRelationManager;
use App\Filament\Resources\Photos\Pages\EditPhoto;
use App\Models\Article;
use App\Models\Cat;
use App\Models\Faq;
use App\Models\Kitten;
use App\Models\Litter;
use App\Models\LitterEvent;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * L'ordre d'affichage, depuis le back-office jusqu'a la page publique.
 *
 * Un champ « ordre » qui ne reordonne rien est le genre de defaut qu'on ne
 * remarque pas : l'eleveur change le nombre, enregistre, la page ne bouge pas,
 * et il conclut qu'il s'y est mal pris. Chaque liste ordonnable est donc
 * exercee en entier — on saisit depuis l'ecran qu'il utilisera, et on relit la
 * page que les familles verront.
 *
 * Les deux listes qui se reordonnent a la souris — les questions frequentes et
 * les etapes d'une portee — passent par le meme chemin que le glisser-deposer.
 */
class BackOfficeOrdreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::where('email', 'letempledesfees@outlook.fr')->firstOrFail());
    }

    /**
     * L'ordre d'apparition de plusieurs textes dans le corps d'une page.
     *
     * L'en-tete est ecarte, et ce n'est pas un detail de confort : la balise
     * og:image d'une page porte le nom d'un fichier qui se retrouve plus bas
     * dans la grille. Mesuree sur le document entier, la premiere photo de la
     * galerie paraissait donc toujours en tete, quel que soit son rang reel —
     * une liste correctement triee serait passee pour cassee.
     */
    private function ordreDansLaPage(string $html, array $reperes): array
    {
        $corps = mb_strpos($html, '<main');

        if ($corps !== false) {
            $html = mb_substr($html, $corps);
        }

        $positions = [];

        foreach ($reperes as $repere) {
            $positions[$repere] = mb_strpos($html, $repere);
        }

        asort($positions);

        return array_keys($positions);
    }

    /* ═══ ce qui se classe par un nombre saisi ════════════════════════ */

    public function test_reordonner_un_reproducteur_depuis_sa_fiche_change_la_page(): void
    {
        $chats = Cat::where('est_publie', true)->orderBy('ordre')->take(2)->get();
        [$premier, $second] = [$chats[0], $chats[1]];

        Livewire::test(EditCat::class, ['record' => $premier->getRouteKey()])
            ->fillForm(['ordre' => 999])
            ->call('save')
            ->assertHasNoFormErrors();

        $ordre = $this->ordreDansLaPage(
            $this->get('/elevage')->assertOk()->getContent(),
            [$premier->nom, $second->nom],
        );

        $this->assertSame([$second->nom, $premier->nom], $ordre,
            "Le reproducteur passe en dernier dans le back-office reste en tete sur le site.");
    }

    public function test_reordonner_un_chaton_depuis_sa_fiche_change_la_page(): void
    {
        $portee = Litter::firstOrFail();
        $portee->forceFill(['loof_portee_numero' => 'LO-2026-0042'])->save();

        $chatons = $portee->kittens()->orderBy('ordre')->take(2)->get();

        foreach ($chatons as $i => $c) {
            $c->forceFill(['icad_numero' => '25026981234567'.$i, 'est_publie' => true])->save();
        }

        [$premier, $second] = [$chatons[0], $chatons[1]];

        Livewire::test(EditKitten::class, ['record' => $premier->getRouteKey()])
            ->fillForm(['ordre' => 999])
            ->call('save')
            ->assertHasNoFormErrors();

        $ordre = $this->ordreDansLaPage(
            $this->get('/chatons')->assertOk()->getContent(),
            [$premier->nom, $second->nom],
        );

        $this->assertSame([$second->nom, $premier->nom], $ordre);
    }

    public function test_reordonner_une_photo_change_la_galerie(): void
    {
        $photos = Photo::whereNull('attachable_type')->orderBy('ordre')->take(2)->get();

        $this->assertCount(2, $photos, 'Il faut deux photos de galerie pour que le test prouve quelque chose.');

        [$premiere, $seconde] = [$photos[0], $photos[1]];

        Livewire::test(EditPhoto::class, ['record' => $premiere->getKey()])
            ->fillForm(['ordre' => 999])
            ->call('save')
            ->assertHasNoFormErrors();

        $ordre = $this->ordreDansLaPage(
            $this->get('/galerie')->assertOk()->getContent(),
            [$premiere->chemin, $seconde->chemin],
        );

        $this->assertSame([$seconde->chemin, $premiere->chemin], $ordre);
    }

    /**
     * Les articles ne se classent pas par un nombre mais par leur date de
     * publication : c'est un journal, le dernier ecrit passe devant.
     */
    public function test_la_date_de_publication_classe_les_articles(): void
    {
        $articles = Article::where('est_publie', true)->take(2)->get();

        $this->assertCount(2, $articles);

        [$a, $b] = [$articles[0], $articles[1]];

        Livewire::test(EditArticle::class, ['record' => $a->getRouteKey()])
            ->fillForm(['date_publication' => now()->subYears(3)->toDateString()])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditArticle::class, ['record' => $b->getRouteKey()])
            ->fillForm(['date_publication' => now()->subDay()->toDateString()])
            ->call('save')
            ->assertHasNoFormErrors();

        $ordre = $this->ordreDansLaPage(
            $this->get('/articles')->assertOk()->getContent(),
            [$a->titre, $b->titre],
        );

        $this->assertSame([$b->titre, $a->titre], $ordre,
            "Le dernier article ecrit doit ouvrir la rubrique.");
    }

    /* ═══ ce qui se reordonne a la souris ═════════════════════════════ */

    public function test_les_questions_se_reordonnent_au_glisser_deposer(): void
    {
        $questions = Faq::publiees()->take(3)->get();

        $this->assertCount(3, $questions);

        $inverse = $questions->pluck('id')->reverse()->values()->all();

        Livewire::test(ListFaqs::class)->call('reorderTable', $inverse);

        $this->assertSame(
            $inverse,
            Faq::publiees()->take(3)->pluck('id')->all(),
            "Le glisser-deposer n'a pas enregistre le nouvel ordre.",
        );

        $ordre = $this->ordreDansLaPage(
            $this->get('/questions')->assertOk()->getContent(),
            $questions->pluck('question')->all(),
        );

        $this->assertSame($questions->reverse()->pluck('question')->values()->all(), $ordre);
    }

    public function test_les_etapes_d_une_portee_se_reordonnent_au_glisser_deposer(): void
    {
        $portee = Litter::has('events', '>=', 2)->firstOrFail();
        $etapes = $portee->events()->orderBy('ordre')->take(2)->get();

        $inverse = $etapes->pluck('id')->reverse()->values()->all();

        Livewire::test(EventsRelationManager::class, [
            'ownerRecord' => $portee,
            'pageClass'   => EditLitter::class,
        ])->call('reorderTable', $inverse);

        $this->assertSame(
            $inverse,
            $portee->fresh()->events()->take(2)->pluck('id')->all(),
        );
    }

    /* ═══ les textes ══════════════════════════════════════════════════ */

    /**
     * Ce que l'eleveur ecrit doit arriver tel quel, accents et apostrophes
     * compris, et se retrouver a l'ecran sans etre echappe deux fois.
     */
    public function test_un_texte_accentue_arrive_intact_sur_le_site(): void
    {
        $texte = 'Né à la maison, il a grandi au milieu des allées et venues — et ça se voit à « son aplomb ».';

        $portee = Litter::firstOrFail();
        $portee->forceFill(['loof_portee_numero' => 'LO-2026-0042'])->save();

        $chaton = $portee->kittens()->firstOrFail();
        $chaton->forceFill(['icad_numero' => '250269812345678', 'est_publie' => true])->save();

        Livewire::test(EditKitten::class, ['record' => $chaton->getRouteKey()])
            ->fillForm(['description' => $texte])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($texte, $chaton->fresh()->description);

        $this->get("/chatons/{$chaton->slug}")
            ->assertOk()
            ->assertSee($texte);
    }

    /**
     * Et le cas qui compte le plus : un texte de reglage, celui que l'eleveur
     * modifiera le plus souvent, doit etre repris par le site sans vider de
     * cache a la main.
     */
    public function test_un_texte_de_reglage_se_retrouve_aussitot_sur_le_site(): void
    {
        $reglage = \App\Models\Setting::where('cle', 'contact.telephone')->firstOrFail();

        Livewire::test(\App\Filament\Resources\Settings\Pages\EditSetting::class, [
            'record' => $reglage->getKey(),
        ])
            ->fillForm(['valeur' => '06 12 34 56 78'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/contact')->assertOk()->assertSee('06 12 34 56 78', false);
    }

    /**
     * Le nom de l'elevage ne doit avoir qu'une seule source.
     *
     * Il se presentait a une dizaine d'endroits, ecrit en dur a chaque fois.
     * Le jour ou l'eleveur corrige son nom depuis son administration, la
     * moitie de la page le suit et l'autre non : le pied change, l'onglet
     * reste, et rien ne signale l'ecart — une page a moitie renommee ne
     * declenche aucune erreur.
     *
     * On renomme donc l'elevage depuis l'ecran de l'eleveur, et on exige que
     * l'ancien nom ne subsiste nulle part dans l'accueil.
     *
     * Une exception, et une seule : la marque du bandeau. Ce n'est pas un champ
     * de texte mais un logotype, compose en trois registres pour tenir dans la
     * largeur — le renommer demande de recomposer la marque, pas de changer une
     * valeur. Le nom y est donc coupe par des balises, et le controle ci-dessous
     * ne le voit pas : c'est voulu, pas un oubli.
     */
    public function test_renommer_l_elevage_renomme_toute_la_page(): void
    {
        $reglage = \App\Models\Setting::where('cle', 'elevage.nom')->firstOrFail();
        $ancien  = $reglage->valeur;
        $nouveau = 'Chatterie des Quatre Vents';

        Livewire::test(\App\Filament\Resources\Settings\Pages\EditSetting::class, [
            'record' => $reglage->getKey(),
        ])
            ->fillForm(['valeur' => $nouveau])
            ->call('save')
            ->assertHasNoFormErrors();

        $html = $this->get('/')->assertOk()->getContent();

        $attendus = [
            "<title>{$nouveau}"                                  => "le titre de l'onglet",
            "<meta property=\"og:site_name\" content=\"{$nouveau}\">" => 'le nom du site partage',
            "<meta property=\"og:title\" content=\"{$nouveau}\">"     => 'le titre partage',
            "<h4>{$nouveau}</h4>"                                => 'la signature du pied',
        ];

        foreach ($attendus as $fragment => $ou) {
            $this->assertStringContainsString($fragment, $html,
                "Renommer l'elevage n'a pas atteint {$ou}.");
        }

        $this->assertStringNotContainsString($ancien, $html,
            "L'ancien nom subsiste dans l'accueil : il y est ecrit en dur "
            .'quelque part au lieu de venir du reglage.');
    }
}
