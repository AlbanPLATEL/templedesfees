<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Kitten;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le tableau de bord doit repondre « voila ce qui attend », pas « que
 * voulez-vous faire ? ».
 *
 * Ces tests verifient qu'il dit la verite : qu'il signale ce qui manque, qu'il
 * en donne la raison, et qu'il se tait quand tout est en ordre. Un tableau de
 * bord qui annonce des taches fantomes est pire qu'un tableau de bord vide —
 * on cesse de le lire.
 */
class TableauDeBordTest extends TestCase
{
    use RefreshDatabase;

    private function eleveuse(): User
    {
        $this->seed();

        return User::where('email', 'letempledesfees@outlook.fr')->firstOrFail();
    }

    public function test_il_reclame_les_fiches_chaton_a_completer_et_dit_pourquoi(): void
    {
        $eleveuse = $this->eleveuse();

        // Au sortir du seed, les numeros legaux sont volontairement vides.
        $this->assertTrue(Kitten::get()->reject->mentionsCompletes()->isNotEmpty());

        $this->actingAs($eleveuse)
            ->get('/admin')
            ->assertOk()
            ->assertSee('à compléter', false)
            ->assertSee('ICAD')
            // La fiche n'est plus cachee : la carte ne doit pas le pretendre.
            ->assertDontSee('n’apparaît pas sur le site', false);
    }

    public function test_il_signale_un_message_sans_reponse(): void
    {
        $eleveuse = $this->eleveuse();

        ContactMessage::create([
            'objet'      => 'autre',
            'prenom'     => 'Camille',
            'nom'        => 'Dupuis',
            'email'      => 'camille@example.test',
            'message'    => 'Bonjour, vos chatons sont-ils disponibles ?',
            'est_traite' => false,
        ]);

        $this->actingAs($eleveuse)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Message sans réponse');
    }

    /**
     * Le test ne cite aucune mention en particulier.
     *
     * Une premiere version verifiait que le SIREN etait signale. Le jour ou on
     * l'a renseigne — il est public, il figure au registre national des
     * entreprises — le test a echoue alors que le tableau de bord disait
     * exactement ce qu'il fallait. Ce qu'on veut tenir, c'est le comportement :
     * une mention vide est signalee, et quand plus rien ne manque, la carte
     * disparait.
     */
    public function test_il_signale_les_mentions_legales_manquantes(): void
    {
        $eleveuse = $this->eleveuse();

        $obligatoires = [
            'legal.siren'      => 'SIREN',
            'legal.certificat' => 'certificat de capacité',
            'legal.directeur'  => 'directeur de la publication',
            'legal.hebergeur'  => 'hébergeur',
        ];

        // Toutes vides : la carte les nomme une par une.
        foreach (array_keys($obligatoires) as $cle) {
            Setting::where('cle', $cle)->firstOrFail()->update(['valeur' => null]);
        }

        $reponse = $this->actingAs($eleveuse)->get('/admin')->assertOk()
            ->assertSee('Mentions légales à compléter');

        foreach ($obligatoires as $libelle) {
            $reponse->assertSee($libelle, false);
        }
    }

    public function test_la_carte_des_mentions_disparait_quand_tout_est_rempli(): void
    {
        $eleveuse = $this->eleveuse();

        foreach (['legal.siren', 'legal.certificat', 'legal.directeur', 'legal.hebergeur'] as $cle) {
            Setting::where('cle', $cle)->firstOrFail()->update(['valeur' => 'renseigné']);
        }

        $this->actingAs($eleveuse)->get('/admin')->assertOk()
            ->assertDontSee('Mentions légales à compléter');
    }

    /**
     * Le cas qui compte vraiment : quand il n'y a rien a faire, il faut le
     * dire. Un ecran vide se lit comme une panne.
     */
    public function test_il_se_tait_quand_tout_est_en_ordre(): void
    {
        $eleveuse = $this->eleveuse();

        // On met la maison en ordre : numeros legaux, fiches publiables,
        // messages traites, photos posees.
        $this->artisan('demo:numeros');

        Kitten::get()->reject->mentionsCompletes()->each->delete();
        ContactMessage::query()->update(['est_traite' => true]);
        \App\Models\Review::query()->update(['est_publie' => true]);
        \App\Models\Cat::whereNull('photo_principale')->orWhere('photo_principale', '')
            ->update(['photo_principale' => 'images/cats/tika.webp']);
        \App\Models\Kitten::whereNull('photo_principale')->orWhere('photo_principale', '')
            ->update(['photo_principale' => 'images/cats/tika.webp']);

        foreach (['legal.siren', 'legal.certificat', 'legal.directeur', 'legal.hebergeur'] as $cle) {
            Setting::where('cle', $cle)->first()?->update(['valeur' => 'renseigné']);
        }

        Setting::all_cached();

        $this->actingAs($eleveuse)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Rien à faire pour le moment');
    }
}
