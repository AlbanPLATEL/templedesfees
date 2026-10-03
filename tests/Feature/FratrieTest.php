<?php

namespace Tests\Feature;

use App\Models\Cat;
use App\Models\Kitten;
use App\Models\Litter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La fratrie d'un chaton : memes pere ET meme mere.
 *
 * L'elevage a deux portees du meme male, Halunke — l'une avec Tika, l'autre
 * avec A'Neora. Leurs chatons sont demi-freres. Les presenter comme une
 * fratrie serait faux, et sur une fiche d'elevage un lien de parente faux ne
 * se rattrape pas : c'est sur lui qu'une famille juge une lignee.
 *
 * Et l'inverse compte autant : un couple peut avoir une seconde portee, et ses
 * chatons restent freres et soeurs d'une annee sur l'autre. « Les chatons de
 * la meme portee » ne suffit donc pas non plus.
 */
class FratrieTest extends TestCase
{
    use RefreshDatabase;

    private function portee(Cat $pere, Cat $mere, string $code): Litter
    {
        return Litter::create([
            'code'           => $code,
            'slug'           => \Illuminate\Support\Str::slug($code),
            'pere_id'        => $pere->id,
            'mere_id'        => $mere->id,
            'date_naissance' => now()->subWeeks(8),
            'nb_chatons'     => 0,
            'est_publiee'    => true,
        ]);
    }

    private function chaton(Litter $portee, string $nom): Kitten
    {
        return Kitten::create([
            'litter_id'  => $portee->id,
            'nom'        => $nom,
            'slug'       => \Illuminate\Support\Str::slug($nom),
            'sexe'       => 'male',
            'robe'       => 'Red',
            'statut'     => 'disponible',
            'est_publie' => true,
        ]);
    }

    public function test_les_demi_freres_ne_sont_pas_une_fratrie(): void
    {
        $this->seed();

        [$pere, $mere1, $mere2] = [
            Cat::where('sexe', 'male')->firstOrFail(),
            Cat::where('sexe', 'femelle')->firstOrFail(),
            Cat::where('sexe', 'femelle')->skip(1)->firstOrFail(),
        ];

        $ici    = $this->portee($pere, $mere1, 'Portée test 1');
        $ailleurs = $this->portee($pere, $mere2, 'Portée test 2');

        $un    = $this->chaton($ici, 'Essai Un');
        $deux  = $this->chaton($ici, 'Essai Deux');
        $cousin = $this->chaton($ailleurs, 'Essai Cousin');

        $fratrie = $un->fratrie()->pluck('nom')->all();

        $this->assertContains('Essai Deux', $fratrie,
            'Un chaton de la même portée est bien un frère.');

        $this->assertNotContains('Essai Cousin', $fratrie,
            'Même père mais mère différente : c’est un demi-frère, pas un frère. '
            .'Le présenter comme tel tromperait la famille sur la lignée.');

        $this->assertNotContains($un->nom, $fratrie, 'Un chaton n’est pas son propre frère.');
    }

    /** Deux portees du meme couple : les chatons restent freres et soeurs. */
    public function test_une_seconde_portee_du_meme_couple_fait_la_meme_fratrie(): void
    {
        $this->seed();

        $pere = Cat::where('sexe', 'male')->firstOrFail();
        $mere = Cat::where('sexe', 'femelle')->firstOrFail();

        $avant  = $this->portee($pere, $mere, 'Portée test A');
        $apres  = $this->portee($pere, $mere, 'Portée test B');

        $aine  = $this->chaton($avant, 'Essai Aîné');
        $cadet = $this->chaton($apres, 'Essai Cadet');

        $this->assertContains('Essai Cadet', $aine->fratrie()->pluck('nom')->all(),
            'Même couple, autre portée : ce sont de vrais frères et sœurs.');
    }

    /** Sans parents connus, on prefere une fratrie vide a une fratrie fausse. */
    public function test_une_portee_sans_parents_n_etablit_aucun_lien(): void
    {
        $this->seed();

        $orpheline = Litter::create([
            'code'        => 'Portée test orpheline',
            'slug'        => 'portee-test-orpheline',
            'pere_id'     => null,
            'mere_id'     => null,
            'nb_chatons'  => 0,
            'est_publiee' => true,
        ]);

        $seul  = $this->chaton($orpheline, 'Essai Seul');
        $this->chaton($orpheline, 'Essai Autre');

        $this->assertSame([], $seul->fratrie()->pluck('nom')->all());
    }
}
