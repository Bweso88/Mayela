<?php

namespace App\Filament\Resources\Curricula\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurriculaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('academicYear.country')->withCount('subjects'))
            ->columns([
                TextColumn::make('name')->label('Programme')->weight('bold')->searchable(),
                TextColumn::make('academicYear.label')->label('Année'),
                TextColumn::make('academicYear.country.name')->label('Pays'),
                TextColumn::make('subjects_count')->label('Matières'),
                TextColumn::make('official_reference')->label('Référence officielle')->placeholder('À renseigner')->limit(40),
                TextColumn::make('status')->label('Statut')->badge(),
                TextColumn::make('published_at')->label('Publié le')->date('d/m/Y')->placeholder('—'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
