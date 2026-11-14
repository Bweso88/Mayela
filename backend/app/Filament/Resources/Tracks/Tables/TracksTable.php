<?php

namespace App\Filament\Resources\Tracks\Tables;

use App\Modules\Curriculum\Enums\TrackKind;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TracksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['stage', 'grades']))
            ->defaultSort('position')
            ->columns([
                TextColumn::make('name')->label('Nom')->weight('bold')->searchable(),
                TextColumn::make('code')->label('Code'),
                TextColumn::make('kind')->label('Type')->badge(),
                TextColumn::make('stage.name')->label('Ordre'),
                TextColumn::make('grades.name')->label('Classes')->badge()->placeholder('Aucune classe rattachée'),
            ])
            ->filters([SelectFilter::make('kind')->label('Type')->options(TrackKind::class)])
            ->recordActions([EditAction::make()]);
    }
}
