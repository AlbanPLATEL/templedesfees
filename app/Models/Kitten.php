<?php

namespace App\Models;

use App\Enums\KittenStatus;
use App\Models\Concerns\ASexe;
use App\Models\Concerns\AUneGalerie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Un chaton de l'elevage.
 *
 * LES MENTIONS OBLIGATOIRES
 * -------------------------
 * L'article L214-8-1 du code rural impose que toute annonce de cession d'un chat
 * affiche le numero d'identification de l'animal et le numero de portee LOOF.
 *
 * La fiche refusait donc de se publier tant que les deux manquaient. C'etait
 * trop tot : on ne puce pas un nouveau-ne, et le numero de portee met des
 * semaines a revenir du LOOF. Les chatons restaient invisibles precisement
 * pendant les semaines ou les familles se decident.
 *
 * Les numeros ne bloquent donc plus l'affichage. Ils restent reclames, mais la
 * ou ils se voient : mentionsManquantes() les liste, la fiche publique affiche
 * « identification en cours » a leur place, et le tableau de bord les redemande
 * a chaque ouverture tant qu'ils sont vides. Rien n'est cache, et rien n'est
 * invente.
 */
class Kitten extends Model
{
    use ASexe;
    use AUneGalerie;
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'statut'          => KittenStatus::class,
            'prix_centimes'   => 'integer',
            'poids_releve_le' => 'date',
            'est_publie'      => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Le prix convenu, tel qu'on l'ecrit.
     *
     * Il ne parait sur aucune page du site : la chatterie n'affiche pas ses
     * tarifs en vitrine. Il sert au contrat de reservation et a la facture,
     * qui ont besoin du prix pour ecrire le solde restant du au depart.
     */
    public function prixFormate(): string
    {
        return \App\Support\Monnaie::euros($this->prix_centimes);
    }

    /**
     * Une facture emise interdit la suppression de la fiche.
     *
     * Les cles etrangeres sont posees en cascade : effacer un chaton efface
     * ses reservations, et effacer une portee efface ses chatons — donc leurs
     * reservations aussi. C'est le bon comportement pour une fiche saisie par
     * erreur. C'en est un tres mauvais pour un acompte encaisse : la ligne
     * porte un numero de facture, une somme recue et un contrat.
     *
     * Le critere est le numero de facture et non le statut : c'est lui qui
     * engage la comptabilite, et il n'est emis qu'a l'encaissement. Une
     * reservation en attente, elle, ne protege rien et part avec la fiche.
     */
    public function aUneReservationEncaissee(): bool
    {
        return $this->reservations()->whereNotNull('facture_numero')->exists();
    }

    public function peutEtreSupprime(): bool
    {
        return ! $this->aUneReservationEncaissee();
    }

    /** Les reservations portant sur ce chaton, payees ou non. */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function litter(): BelongsTo
    {
        return $this->belongsTo(Litter::class);
    }

    public function adoptionRequests(): HasMany
    {
        return $this->hasMany(AdoptionRequest::class);
    }

    /**
     * Les vrais freres et soeurs : memes pere ET meme mere.
     *
     * Ce n'est pas « les chatons de la meme portee », meme si aujourd'hui les
     * deux reviennent au meme. Un couple peut avoir une seconde portee, et ses
     * chatons restent freres et soeurs d'une annee sur l'autre.
     *
     * Et ce n'est surtout pas « les chatons de l'elevage ». Halunke est le pere
     * de deux portees, l'une avec Tika, l'autre avec A'Neora : ces chatons-la
     * sont demi-freres. Les ranger sous le meme mot serait faux, et sur une
     * fiche d'elevage un lien de parente faux ne se rattrape pas.
     */
    public function fratrie(): Builder
    {
        $portee = $this->litter;

        /* Une portee sans parents connus n'etablit aucun lien : on prefere une
           fratrie vide a une fratrie fausse. */
        if (! $portee?->pere_id || ! $portee?->mere_id) {
            return static::query()->whereRaw('1 = 0');
        }

        return static::query()
            ->whereKeyNot($this->getKey())
            ->whereHas('litter', fn ($q) => $q
                ->where('pere_id', $portee->pere_id)
                ->where('mere_id', $portee->mere_id))
            ->orderBy('ordre')
            ->orderBy('nom');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'attachable')->orderBy('ordre');
    }

    public function altParDefaut(): string
    {
        return $this->nom.', chaton Maine Coon '.\Illuminate\Support\Str::lower($this->robe);
    }

    public function pere(): ?Cat
    {
        return $this->litter?->pere;
    }

    public function mere(): ?Cat
    {
        return $this->litter?->mere;
    }

    /** Les mentions legales manquantes, pour les afficher telles quelles dans le back-office. */
    public function mentionsManquantes(): array
    {
        $manquantes = [];

        if (blank($this->icad_numero)) {
            $manquantes[] = "numéro d'identification ICAD du chaton";
        }

        if (blank($this->litter?->loof_portee_numero)) {
            $manquantes[] = 'numéro de portée LOOF';
        }

        return $manquantes;
    }

    /** Les deux mentions legales sont-elles renseignees ? */
    public function mentionsCompletes(): bool
    {
        return $this->mentionsManquantes() === [];
    }

    public function estDisponible(): bool
    {
        return $this->statut === KittenStatus::Disponible;
    }

    public function ageEnSemaines(): ?int
    {
        return $this->litter?->ageEnSemaines();
    }

    /** Le poids formate a la francaise : 1 480 g. */
    public function poidsFormate(): ?string
    {
        return $this->poids_g ? number_format($this->poids_g, 0, ',', ' ').' g' : null;
    }

    /**
     * Les fiches publiees ET reellement publiables.
     *
     * Le drapeau est_publie ne suffit pas : il est maintenu par KittenObserver,
     * qui n'ecoute que les enregistrements Eloquent. Une mise a jour de masse
     * — Kitten::query()->update(...), une action groupee du back-office —
     * le contourne, et vider loof_portee_numero cote Litter ne repasse aucun
     * chaton en brouillon. La condition legale est donc revalidee ici, a la
     * lecture : le drapeau ne peut plus mentir, quel que soit le chemin
     * d'ecriture qui l'a pose.
     */
    public function scopePublies($query)
    {
        return $query->where('est_publie', true)
            ->orderBy('ordre')
            ->orderBy('nom');
    }

    /**
     * Le miroir SQL de mentionsCompletes(). Il ne filtre plus l'affichage : il
     * sert au back-office a trier les fiches dont les numeros sont arrives de
     * celles qui les attendent encore. Les deux doivent rester d'accord — c'est
     * ce que verifie KittenPublicationTest.
     */
    public function scopePubliables($query)
    {
        return $query
            ->whereNotNull('icad_numero')
            ->where('icad_numero', '<>', '')
            ->whereHas('litter', fn ($q) => $q
                ->whereNotNull('loof_portee_numero')
                ->where('loof_portee_numero', '<>', ''));
    }

    public function scopeDisponibles($query)
    {
        return $query->where('statut', KittenStatus::Disponible->value);
    }

    public function scopeStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }
}
