<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Compte du back-office.
         *
         * Le mot de passe vient de .env, qui n'est pas dans le depot : ecrit
         * ici en clair, il partait sur GitHub avec le reste du code, et un
         * mot de passe publie n'en est plus un. A defaut, un mot de passe
         * aleatoire est tire et affiche une seule fois, a la fin du seed.
         */
        $motDePasse = env('BACK_OFFICE_MOT_DE_PASSE') ?: Str::password(16);

        User::updateOrCreate(
            ['email' => env('BACK_OFFICE_EMAIL', 'letempledesfees@outlook.fr')],
            ['name' => "La Chatterie du Temple des Fées", 'password' => Hash::make($motDePasse)],
        );

        if (! env('BACK_OFFICE_MOT_DE_PASSE')) {
            $this->command?->warn("Mot de passe du back-office : $motDePasse");
            $this->command?->line('  Il n\'est affiché qu\'une fois. Le fixer dans .env avec BACK_OFFICE_MOT_DE_PASSE.');
        }

        $this->call(ElevageSeeder::class);
    }
}
