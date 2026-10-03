<?php

namespace App\Filament\Resources\Kittens\Schemas;

use App\Enums\KittenStatus;
use App\Filament\Champs\ChampPhoto;
use App\Models\Litter;
use App\Support\Monnaie;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class KittenForm
{
    /**
     * Les mentions legales encore manquantes, calculees sur ce qui est saisi a
     * l'ecran et non sur ce qui est en base : l'eleveuse voit la case se
     * deverrouiller au moment ou elle tape le numero, sans enregistrer.
     * Doit rester d'accord avec Kitten::mentionsManquantes().
     */
    private static function manquantes(Get $get): array
    {
        $manquantes = [];

        if (blank($get('icad_numero'))) {
            $manquantes[] = "le numéro d'identification ICAD du chaton";
        }

        $portee = filled($get('litter_id')) ? Litter::find($get('litter_id')) : null;

        if (blank($portee?->loof_portee_numero)) {
            $manquantes[] = 'le numéro de portée LOOF, à saisir sur la portée';
        }

        return $manquantes;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        Select::make('litter_id')
                            ->label('Portée')
                            ->relationship('litter', 'code')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),

                        TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(80)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),

                        TextInput::make('slug')
                            ->label('Adresse de la fiche')
                            ->helperText('Apparaît dans l’URL. Se remplit tout seul depuis le nom.')
                            ->required()
                            ->maxLength(120)
                            ->unique(ignoreRecord: true),

                        TextInput::make('reference')
                            ->label('Référence')
                            ->placeholder('X-01')
                            ->maxLength(20),

                        Select::make('sexe')
                            ->label('Sexe')
                            ->options(['male' => 'Mâle', 'femelle' => 'Femelle'])
                            ->required(),

                        TextInput::make('robe')
                            ->label('Robe')
                            ->placeholder('Brown tabby spotted rosetted')
                            ->maxLength(120),
                    ]),

                Section::make('Suivi')
                    ->columns(3)
                    ->schema([
                        Select::make('statut')
                            ->label('Statut')
                            ->options(KittenStatus::class)
                            ->default('disponible')
                            ->required()
                            ->helperText('Porte sur le chaton, jamais sur la famille : aucun nom d’adoptant n’est publié.'),

                        /*
                         * Le prix ne parait sur aucune page du site : la
                         * chatterie n'affiche pas ses tarifs en vitrine, et ce
                         * n'est pas a une refonte d'en decider. Il sert au
                         * contrat de reservation et a la facture, qui ont
                         * besoin du prix pour ecrire le solde restant du.
                         */
                        TextInput::make('prix_centimes')
                            ->label('Prix')
                            ->numeric()
                            ->suffix('€')
                            ->formatStateUsing(fn (?int $state) => $state === null ? null : $state / 100)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Monnaie::centimes($state) : null)
                            ->helperText('Jamais affiché sur le site. Repris au contrat de réservation.'),

                        TextInput::make('poids_g')
                            ->label('Poids')
                            ->numeric()
                            ->suffix('g'),

                        DatePicker::make('poids_releve_le')
                            ->label('Pesé le')
                            ->displayFormat('d/m/Y'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        TextInput::make('ordre')
                            ->label('Ordre d’affichage')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),

                Section::make('Photo')
                    ->columns(1)
                    ->schema([
                        ChampPhoto::make('photo_principale', 'Photo du chaton',
                            'Illustre la fiche et la vignette dans la liste des chatons.'),
                    ]),

                /*
                 * Le panneau bloquait le bouton tant que les deux numeros
                 * manquaient, et annoncait que la fiche restait en brouillon.
                 * C'etait trop tot : on ne puce pas un nouveau-ne, et le
                 * numero de portee met des semaines a revenir du LOOF. Les
                 * chatons restaient invisibles pendant les semaines ou les
                 * familles se decident.
                 *
                 * Les numeros sont toujours reclames — ici, sur la fiche
                 * publique et sur le tableau de bord — mais ils ne tiennent
                 * plus la porte fermee.
                 */
                Section::make('Publication')
                    ->description("Une annonce de cession doit porter le numéro d’identification du chaton et le numéro de portée LOOF. Un nouveau-né n’a encore ni l’un ni l’autre : la fiche se publie quand même, et annonce elle-même que l’identification est en cours. Aucun départ ne se fait avant qu’ils soient saisis.")
                    ->columns(1)
                    ->schema([
                        TextInput::make('icad_numero')
                            ->label("Numéro d'identification ICAD")
                            ->placeholder('250 269 000 000 000')
                            ->helperText('Puce électronique du chaton, enregistrée à l’ICAD avant toute cession.')
                            ->maxLength(40)
                            ->live(onBlur: true),

                        Toggle::make('est_publie')
                            ->label('Publier la fiche sur le site')
                            ->helperText(function (Get $get) {
                                $manquantes = self::manquantes($get);

                                return $manquantes === []
                                    ? 'Les deux mentions obligatoires sont renseignées.'
                                    : 'La fiche affichera « à compléter » : il manque '
                                      .implode(' et ', $manquantes).'.';
                            }),
                    ]),
            ]);
    }
}
