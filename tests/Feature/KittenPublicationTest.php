<?php

namespace Tests\Feature;

use App\Models\Cat;
use App\Models\Kitten;
use App\Models\Litter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La regle de l'article L214-8-1 : pas de numero ICAD et de numero de portee LOOF,
 * pas de fiche publiee. C'est la seule contrainte du projet qui soit legale et non
 * editoriale, et elle doit tenir quel que soit le chemin d'ecriture — y compris les
 * mises a jour de masse, qui ne declenchent aucun observer Eloquent.
 */
class KittenPublicationTest extends TestCase
{
    use RefreshDatabase;

    private function portee(?string $loof = 'LOOF-2026-0001'): Litter
    {
        $pere = Cat::create(['nom' => 'Uzumaki', 'slug' => 'uzumaki', 'sexe' => 'male', 'role' => 'etalon']);
        $mere = Cat::create(['nom' => 'Wendy', 'slug' => 'wendy', 'sexe' => 'femelle', 'role' => 'reproductrice']);

        return Litter::create([
            'code'               => 'Portee X',
            'slug'               => 'portee-x-2026',
            'pere_id'            => $pere->id,
            'mere_id'            => $mere->id,
            'date_naissance'     => now()->subWeeks(14)->toDateString(),
            'loof_portee_numero' => $loof,
            'est_publiee'        => true,
        ]);
    }

    private function chaton(Litter $portee, ?string $icad, bool $publie = true, string $nom = 'Xena'): Kitten
    {
        return Kitten::create([
            'litter_id'   => $portee->id,
            'nom'         => $nom,
            'slug'        => \Illuminate\Support\Str::slug($nom),
            'sexe'        => 'femelle',
            'statut'      => 'disponible',
            'icad_numero' => $icad,
            'est_publie'  => $publie,
        ]);
    }

    public function test_une_fiche_complete_est_bien_publiee_et_accessible(): void
    {
        $chaton = $this->chaton($this->portee(), '250269000000001');

        $this->assertTrue($chaton->fresh()->est_publie, 'Une fiche complete doit rester publiee.');
        $this->assertSame(1, Kitten::publies()->count());
        $this->get('/chatons/'.$chaton->slug)->assertOk();
    }

    /**
     * Un chaton se montre avant d'avoir ses numeros.
     *
     * La fiche repassait en brouillon tant que l'ICAD ou le numero de portee
     * manquait. C'etait trop tot : on ne puce pas un nouveau-ne, et le numero
     * de portee met des semaines a revenir du LOOF. Les chatons disparaissaient
     * du site pendant les semaines ou les familles se decident.
     */
    public function test_une_fiche_sans_numero_reste_publiee(): void
    {
        $chaton = $this->chaton($this->portee(), null, publie: true);

        $this->assertTrue($chaton->fresh()->est_publie,
            'Un chaton qui vient de naitre doit pouvoir etre presente.');
        $this->assertSame(["numéro d'identification ICAD du chaton"], $chaton->mentionsManquantes());
        $this->assertFalse($chaton->mentionsCompletes());

        $this->get('/chatons/'.$chaton->slug)->assertOk();
    }

    /** Mais la fiche le dit : rien n'est tu, et rien n'est invente. */
    public function test_la_fiche_annonce_que_l_identification_est_en_cours(): void
    {
        $chaton = $this->chaton($this->portee(), null, publie: true);

        $this->get('/chatons/'.$chaton->slug)
            ->assertOk()
            ->assertSee('Identification en cours')
            ->assertSee('À compléter');
    }

    /** Vider le numero de portee ne fait plus disparaitre ses chatons. */
    public function test_vider_le_numero_de_portee_laisse_les_chatons_en_ligne(): void
    {
        $portee = $this->portee();
        $chaton = $this->chaton($portee, '250269000000001');
        $this->assertSame(1, Kitten::publies()->count());

        Litter::query()->update(['loof_portee_numero' => null]);

        $this->assertSame(1, Kitten::publies()->count());
        $this->get('/chatons/'.$chaton->slug)->assertOk()->assertSee('Identification en cours');
    }

    /**
     * Le scope SQL et la regle PHP doivent dire la meme chose, sinon l'un des
     * deux ment. Ils ne filtrent plus l'affichage, mais le back-office s'en sert
     * pour trier les fiches dont les numeros sont arrives.
     */
    public function test_le_scope_sql_et_la_regle_php_restent_d_accord(): void
    {
        $complet   = $this->chaton($this->portee(), '250269000000001');
        $this->chaton($complet->litter, '', publie: true, nom: 'Yuki');

        $completes = Kitten::publiables()->pluck('id')->all();

        foreach (Kitten::with('litter')->get() as $chaton) {
            $this->assertSame(
                $chaton->mentionsCompletes(),
                in_array($chaton->id, $completes, true),
                "Desaccord sur la fiche {$chaton->slug} entre mentionsCompletes() et le scope."
            );
        }
    }
}
