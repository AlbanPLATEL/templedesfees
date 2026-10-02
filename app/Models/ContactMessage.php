<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Message du formulaire de contact.
 * RGPD : consentement horodate, conservation 12 mois (moins qu'un dossier
 * adoptant, qui peut s'etaler sur deux portees).
 */
class ContactMessage extends Model
{
    public const MOIS_CONSERVATION = 12;

    /**
     * Tous les objets, y compris ceux qu'on ne propose plus.
     *
     * « Visiter l'elevage » a disparu du formulaire le jour ou l'elevage a
     * cesse de recevoir avant le depart des chatons. La clef reste : des
     * messages la portent deja, et les effacer de cette liste les afficherait
     * sous leur nom technique dans le back-office.
     */
    public const OBJETS = [
        'adoption' => 'Adopter un chaton',
        'visite'   => "Visiter l'élevage",
        'race'     => 'Une question sur la race',
        'autre'    => 'Autre demande',
    ];

    /** Ceux que le formulaire propose aujourd'hui. */
    public const OBJETS_PROPOSES = [
        'adoption' => 'Adopter un chaton',
        'race'     => 'Une question sur la race',
        'autre'    => 'Autre demande',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'est_traite'      => 'boolean',
            'consentement_le' => 'datetime',
            'a_purger_le'     => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $message) {
            $message->consentement_le ??= now();
            $message->a_purger_le    ??= $message->consentement_le->copy()->addMonths(self::MOIS_CONSERVATION);
        });
    }

    public function objetLibelle(): string
    {
        return self::OBJETS[$this->objet] ?? $this->objet;
    }

    public function scopeAPurger($query)
    {
        return $query->where('a_purger_le', '<=', now());
    }
}
