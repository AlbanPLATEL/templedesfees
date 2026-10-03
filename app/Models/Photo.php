<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Photo extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'est_couverture' => 'boolean',
            'est_publiee'    => 'boolean',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Volontairement SANS tri, contrairement aux autres scopes publics : deux
     * appelants veulent un ordre aleatoire — la mosaique de l'accueil et le ruban
     * defilant. Un orderBy pose ici passerait devant leur inRandomOrder() et le
     * hasard disparaitrait. Les appelants trient donc eux-memes, et ils le font
     * tous.
     */
    /**
     * Les photos reellement montrables.
     *
     * Le drapeau de la photo ne suffit pas : une photo appartient a une fiche,
     * et une fiche depubliee emporte ses images avec elle. Sans cette
     * condition, l'eleveur retirait un chaton du site et ses photos
     * continuaient de tourner dans le ruban de l'accueil et dans la galerie —
     * ce qu'il n'avait aucun moyen de deviner, puisqu'il avait bien depublie
     * la fiche.
     *
     * Le chaton est le cas le plus sensible : sa condition n'est pas un
     * drapeau mais la regle legale, celle qui exige le numero d'identification
     * et le numero de portee LOOF. On la revalide ici plutot que de s'en
     * remettre au drapeau. Cf. Kitten::scopePublies().
     */
    public function scopePubliees($query)
    {
        return $query
            ->where('est_publiee', true)
            ->where(fn ($q) => $q
                /*
                 * Sans rattachement, la photo ne depend de personne : c'est le
                 * cas des photos de galerie. L'identifiant a zero est
                 * l'ancienne forme, avant la migration qui les a detachees —
                 * on la tolere pour qu'une base restee en arriere ne vide pas
                 * sa galerie en silence.
                 */
                ->whereNull('attachable_type')
                ->orWhereNull('attachable_id')
                ->orWhere('attachable_id', 0)
                ->orWhereHasMorph(
                    'attachable',
                    [Cat::class, Litter::class, Kitten::class],
                    fn ($fiche, string $type) => match ($type) {
                        Cat::class    => $fiche->where('est_publie', true),
                        Litter::class => $fiche->where('est_publiee', true),
                        Kitten::class => $fiche->where('est_publie', true),
                    },
                ));
    }
}
