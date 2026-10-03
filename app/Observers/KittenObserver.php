<?php

namespace App\Observers;

use App\Exceptions\FactureEmise;
use App\Models\Kitten;

/**
 * Le garde-fou d'une fiche chaton.
 *
 * Il en gardait deux. Le premier repassait en brouillon toute fiche sans
 * numero ICAD ni numero de portee : il a ete retire, parce qu'un nouveau-ne
 * n'a ni l'un ni l'autre et doit pouvoir etre presente. Les numeros se
 * reclament desormais a l'ecran plutot qu'en masquant la fiche.
 *
 * Reste celui-ci : une fiche dont l'acompte a ete encaisse ne s'efface pas. La
 * cle etrangere est en cascade, donc la supprimer emporterait la reservation,
 * son numero de facture et la somme recue. Le refus est pose ici plutot que
 * dans le back-office pour qu'aucun chemin d'ecriture n'y echappe — action
 * groupee, commande artisan, console.
 */
class KittenObserver
{
    public function deleting(Kitten $kitten): void
    {
        if ($kitten->peutEtreSupprime()) {
            return;
        }

        throw new FactureEmise(
            "Le chaton « {$kitten->nom} » porte une réservation encaissée : "
            .'sa facture et le montant reçu seraient effacés avec la fiche.'
        );
    }
}
