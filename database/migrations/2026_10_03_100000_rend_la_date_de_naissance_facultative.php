<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une portée peut exister avant qu'on sache sa date de naissance.
 *
 * L'éleveur prépare ses fiches à partir de ce qu'il a sous la main — les noms,
 * les sexes, les robes, les photos — et complète le reste quand il l'obtient.
 * La date était obligatoire dès la création : il fallait donc en inventer une
 * pour commencer à saisir, et une date inventée sur une fiche d'élevage finit
 * toujours par être lue comme vraie.
 *
 * Elle devient facultative. Les pages publiques ne montrent la portée qu'une
 * fois publiée, et elles savent désormais se taire quand la date manque.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('litters', function (Blueprint $table) {
            $table->date('date_naissance')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('litters', function (Blueprint $table) {
            $table->date('date_naissance')->nullable(false)->change();
        });
    }
};
