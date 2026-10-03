<?php

namespace App\Filament\Resources\Kittens\Tables;

use App\Enums\KittenStatus;
use App\Models\Kitten;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KittensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // mentionsManquantes() lit la portee : sans cela, une requete par ligne.
            ->modifyQueryUsing(fn (Builder $query) => $query->with('litter'))
            ->defaultSort('ordre')
            ->columns([
                TextColumn::make('nom')
                    ->label('Nom')
                    ->description(fn (Kitten $chaton) => $chaton->reference)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('litter.code')
                    ->label('Portée')
                    ->sortable(),

                TextColumn::make('sexe')
                    ->label('Sexe')
                    ->formatStateUsing(fn (string $state) => $state === 'male' ? 'Mâle' : 'Femelle'),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('icad_numero')
                    ->label('ICAD')
                    ->placeholder('à renseigner')
                    ->searchable()
                    ->fontFamily('mono'),

                // La colonne qui porte la regle : au lieu d'un simple oui/non, elle dit
                // pourquoi une fiche n'est pas en ligne. C'est la reponse a « pourquoi
                // mon chaton n'apparait pas sur le site ? ».
                TextColumn::make('etat')
                    ->label('État')
                    ->badge()
                    ->state(fn (Kitten $chaton) => $chaton->est_publie ? 'En ligne' : 'Brouillon')
                    ->color(fn (Kitten $chaton) => $chaton->est_publie ? 'success' : 'gray')
                    ->description(fn (Kitten $chaton) => $chaton->mentionsCompletes()
                        ? null
                        : 'Il manque '.implode(' et ', $chaton->mentionsManquantes())),

                TextColumn::make('poids_g')
                    ->label('Poids')
                    ->formatStateUsing(fn (?int $state) => $state ? number_format($state, 0, ',', ' ').' g' : null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(KittenStatus::class),

                TernaryFilter::make('est_publie')
                    ->label('Publication')
                    ->placeholder('Toutes')
                    ->trueLabel('En ligne')
                    ->falseLabel('En brouillon'),

                // Le tri le plus utile au quotidien : ce qu'il reste a completer.
                TernaryFilter::make('mentions')
                    ->label('Mentions obligatoires')
                    ->placeholder('Toutes')
                    ->trueLabel('Complètes')
                    ->falseLabel('Incomplètes')
                    ->queries(
                        true:  fn (Builder $query) => $query->publiables(),
                        false: fn (Builder $query) => $query->whereNotIn('id', Kitten::publiables()->select('id')),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                // Pas d'action groupee de publication : elle contournerait
                // KittenObserver. Une fiche se publie une par une, apres controle
                // de ses numeros. Cf. Kitten::scopePublies().
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
