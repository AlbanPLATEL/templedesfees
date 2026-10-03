<?php

namespace Tests\Feature;

use App\Models\Cat;
use App\Models\Kitten;
use App\Models\Litter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le champ « ordre d'affichage » du back-office doit agir, partout.
 *
 * Il ne le faisait pas : l'ordre etait laisse a chaque appelant et trois sur
 * quatre y pensaient. L'affichage paraissait correct par coincidence — les
 * identifiants suivaient l'ordre voulu — et n'aurait pas bouge le jour ou
 * l'eleveuse aurait reordonne une fiche.
 */
class OrdreAffichageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_reordonner_un_reproducteur_change_l_affichage(): void
    {
        $chats = Cat::publies()->get();
        $this->assertGreaterThan(1, $chats->count());

        $dernier = $chats->last();
        $dernier->update(['ordre' => -1]);

        $this->assertSame(
            $dernier->id,
            Cat::publies()->first()->id,
            'Un reproducteur remonté doit passer en tête.'
        );
    }

    public function test_reordonner_un_chaton_change_l_affichage(): void
    {
        /*
         * Le seeder laisse les numeros vides : on les pose ici, explicitement.
         * Une commande de demonstration le faisait avant — elle visait une
         * portee qui n'existe plus, et depubliait tout sur son passage.
         */
        Litter::query()->update(['loof_portee_numero' => 'LO-2026-0001']);

        Kitten::all()->each(fn (Kitten $k) => $k->forceFill([
            'icad_numero' => '250269'.str_pad((string) $k->id, 9, '0', STR_PAD_LEFT),
            'est_publie'  => true,
        ])->save());

        $chatons = Kitten::publies()->get();
        $this->assertGreaterThan(1, $chatons->count());

        $dernier = $chatons->last();
        $dernier->update(['ordre' => -1]);

        $this->assertSame($dernier->id, Kitten::publies()->first()->id);
    }

    /**
     * Le formulaire de pre-reservation propose les chatons disponibles : ils
     * doivent y apparaitre dans le meme ordre que sur la page Chatons, pas dans
     * celui que la base renvoie.
     */
    public function test_le_formulaire_d_adoption_suit_le_meme_ordre(): void
    {
        /*
         * Le seeder laisse les numeros vides : on les pose ici, explicitement.
         * Une commande de demonstration le faisait avant — elle visait une
         * portee qui n'existe plus, et depubliait tout sur son passage.
         */
        Litter::query()->update(['loof_portee_numero' => 'LO-2026-0001']);

        Kitten::all()->each(fn (Kitten $k) => $k->forceFill([
            'icad_numero' => '250269'.str_pad((string) $k->id, 9, '0', STR_PAD_LEFT),
            'est_publie'  => true,
        ])->save());

        $attendu = Kitten::publies()->disponibles()->pluck('id')->all();
        $this->assertNotEmpty($attendu);

        $dernier = Kitten::publies()->disponibles()->get()->last();
        $dernier->update(['ordre' => -1]);

        $apres = Kitten::publies()->disponibles()->pluck('id')->all();

        $this->assertSame($dernier->id, $apres[0]);
        $this->assertNotSame($attendu, $apres, 'L’ordre doit avoir changé.');
    }

    /**
     * La mosaique de l'accueil et le ruban defilant veulent du hasard : le scope
     * des photos reste volontairement sans tri pour ne pas passer devant leur
     * inRandomOrder(). On verifie que ce choix tient.
     */
    public function test_le_scope_des_photos_n_impose_aucun_ordre(): void
    {
        $sql = \App\Models\Photo::publiees()->toSql();

        $this->assertStringNotContainsString('order by', strtolower($sql));
    }
}
