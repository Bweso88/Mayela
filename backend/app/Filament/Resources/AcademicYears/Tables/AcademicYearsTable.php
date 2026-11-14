<?php

namespace App\Filament\Resources\AcademicYears\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademicYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['country', 'curriculum'])->withCount('terms'))
            ->defaultSort('label', 'desc')
            ->columns([
                TextColumn::make('label')->label('Année')->weight('bold'),
                TextColumn::make('country.name')->label('Pays'),
                TextColumn::make('starts_on')->label('Rentrée')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('ends_on')->label('Fin')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('terms_count')->label('Trimestres'),
                TextColumn::make('curriculum.status')->label('Programme')->badge()->placeholder('Aucun'),
                IconColumn::make('is_current')->label('En cours')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
