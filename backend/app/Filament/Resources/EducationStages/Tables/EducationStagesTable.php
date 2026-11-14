<?php

namespace App\Filament\Resources\EducationStages\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EducationStagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('country')->withCount(['cycles', 'grades', 'tracks']))
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('name')->label('Ordre d\'enseignement')->searchable(),
                TextColumn::make('country.name')->label('Pays'),
                TextColumn::make('cycles_count')->label('Cycles'),
                TextColumn::make('grades_count')->label('Classes'),
                TextColumn::make('tracks_count')->label('Séries / spécialités'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
