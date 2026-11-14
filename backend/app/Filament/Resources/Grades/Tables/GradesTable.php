<?php

namespace App\Filament\Resources\Grades\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['stage', 'cycle', 'tracks']))
            ->defaultSort('position')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->label('Classe')->weight('bold')->searchable(),
                TextColumn::make('stage.name')->label('Ordre'),
                TextColumn::make('cycle.name')->label('Cycle')->placeholder('—'),
                TextColumn::make('tracks.code')->label('Séries')->badge()->placeholder('—'),
                TextColumn::make('universe')->label('Univers')->badge(),
                IconColumn::make('is_available')->label('Dans l\'app')->boolean(),
            ])
            ->filters([
                SelectFilter::make('education_stage_id')->label('Ordre')->relationship('stage', 'name'),
                TernaryFilter::make('is_available')->label('Proposée dans l\'app'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
