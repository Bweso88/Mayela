<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['grade', 'curriculum.academicYear', 'tracks'])->withCount('chapters'))
            ->defaultSort('position')
            ->columns([
                ColorColumn::make('color')->label(''),
                TextColumn::make('name')->label('Matière')->weight('bold')->searchable(),
                TextColumn::make('grade.name')->label('Classe')->sortable(),
                TextColumn::make('tracks.code')->label('Séries')->badge()->placeholder('Toutes'),
                TextColumn::make('curriculum.academicYear.label')->label('Année'),
                TextColumn::make('chapters_count')->label('Chapitres'),
            ])
            ->filters([
                SelectFilter::make('grade_id')->label('Classe')->relationship('grade', 'name')->preload(),
                SelectFilter::make('curriculum_id')->label('Programme')->relationship('curriculum', 'name'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
