<?php

namespace App\Filament\Resources\Litters\Schemas;

use App\Enums\CatRole;
use App\Filament\Champs\ChampPhoto;
use App\Models\Cat;
use App\Models\Litter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LitterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('La portée')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Nom de la portée')
                            ->placeholder('Portée X')
                            ->required()
                            ->maxLength(60)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),

                        TextInput::make('slug')
                            ->label('Adresse')
                            ->required()
                            ->maxLength(120)
                            ->unique(ignoreRecord: true),

                        Select::make('pere_id')
                            ->label('Père')
                            ->options(fn () => Cat::where('sexe', 'male')->orderBy('nom')->pluck('nom', 'id'))
                            ->searchable(),

                        Select::make('mere_id')
                            ->label('Mère')
                            ->options(fn () => Cat::where('sexe', 'femelle')->orderBy('nom')->pluck('nom', 'id'))
                            ->searchable(),

                        /*
                         * Pas obligatoire : une portee se prepare parfois avant
                         * que la date soit arretee, et l'exiger bloquait toute
                         * autre modification de la fiche en attendant. Le
                         * tableau de bord la reclame, le site affiche
                         * « a completer » tant qu'elle manque.
                         */
                        DatePicker::make('date_naissance')
                            ->label('Date de naissance')
                            ->displayFormat('d/m/Y')
                            ->helperText('À remplir dès que vous l’avez : c’est elle qui donne l’âge des chatons et la date de départ.')
                            ->live(onBlur: true)
                            // L'age legal de cession se deduit de la naissance : on le pose
                            // pour eviter une saisie a la main qui pourrait le raccourcir.
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (filled($state)) {
                                    $set('date_disponibilite', Carbon::parse($state)
                                        ->addWeeks(Litter::SEMAINES_AVANT_CESSION)
                                        ->toDateString());
                                }
                            }),

                        DatePicker::make('date_disponibilite')
                            ->label('Départs possibles à partir du')
                            ->displayFormat('d/m/Y')
                            ->helperText(Litter::SEMAINES_AVANT_CESSION.' semaines après la naissance, âge légal de cession.'),

                        TextInput::make('nb_chatons')
                            ->label('Nombre de chatons')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Mention obligatoire sur une annonce de cession.'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Photo')
                    ->columns(1)
                    ->schema([
                        ChampPhoto::make('photo_principale', 'Photo de la portée'),
                    ]),

                Section::make('Publication')
                    ->description("Le numéro de portée LOOF commande la publication de TOUS les chatons de cette portée : tant qu'il est vide, aucune de leurs fiches ne peut passer en ligne.")
                    ->columns(1)
                    ->schema([
                        TextInput::make('loof_portee_numero')
                            ->label('Numéro de portée LOOF')
                            ->placeholder('LOOF-2026-0001')
                            ->maxLength(40)
                            ->helperText(function (?Litter $record) {
                                if ($record === null) {
                                    return 'À renseigner dès réception du LOOF.';
                                }

                                $chatons = $record->kittens()->count();

                                return blank($record->loof_portee_numero)
                                    ? "Vide : les {$chatons} fiche(s) chaton de cette portée resteront en brouillon."
                                    : "Renseigné : les fiches chaton de cette portée ne sont plus bloquées par ce numéro.";
                            }),

                        Toggle::make('est_publiee')
                            ->label('Afficher cette portée sur le site')
                            ->helperText('Indépendant des fiches chaton, qui ont leur propre règle.'),
                    ]),
            ]);
    }
}
