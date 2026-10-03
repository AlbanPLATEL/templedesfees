<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test de fumee : chaque page publique repond 200 sur un jeu de donnees seede.
 * Ne verifie pas le contenu — sert a attraper une vue cassee, une relation
 * manquante ou un helper absent avant qu'ils ne se voient en demonstration.
 */
class PagesPubliquesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function pages(): array
    {
        return [
            'accueil'         => ['/'],
            'chatons'         => ['/chatons'],
            'elevage'         => ['/elevage'],
            'le maine coon'       => ['/le-maine-coon'],
            'galerie'         => ['/galerie'],
            'adopter'         => ['/adopter'],
            'questions'       => ['/questions'],
            'contact'         => ['/contact'],
            'mentions'        => ['/mentions-legales'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function test_la_page_repond(string $chemin): void
    {
        $this->get($chemin)->assertOk();
    }

    public function test_une_fiche_reproducteur_repond(): void
    {
        $chat = \App\Models\Cat::publies()->firstOrFail();

        $this->get('/elevage/'.$chat->slug)->assertOk();
    }

    /**
     * Entre deux portees, aucun chaton n'est en ligne. Ce n'est pas une panne :
     * la page doit repondre et le dire, pas tomber en erreur.
     */
    public function test_la_liste_des_chatons_repond_meme_sans_fiche_publiee(): void
    {
        \App\Models\Kitten::query()->update(['est_publie' => false]);

        $this->assertSame(0, \App\Models\Kitten::publies()->count());

        $this->get('/chatons')->assertOk();
    }
}
