<?php

namespace App\Filament\Resources\Chapters\Tables;

use App\Modules\Curriculum\Enums\PublicationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ChaptersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['subject.grade', 'term']))
            ->defaultSort('position')
            ->columns([
                TextColumn::make('position')->label('#'),
                TextColumn::make('title')->label('Chapitre')->weight('bold')->searchable()->wrap(),
                TextColumn::make('subject.name')->label('Matière'),
                TextColumn::make('subject.grade.name')->label('Classe'),
                TextColumn::make('term.name')->label('Trimestre')->placeholder('—'),
                TextColumn::make('status')->label('Statut')->badge(),
                IconColumn::make('is_free')->label('Gratuit')->boolean(),
                IconColumn::make('is_demo')->label('Démo')->boolean(),
            ])
            ->filters([
                SelectFilter::make('subject_id')->label('Matière')->relationship('subject', 'name')->preload(),
                SelectFilter::make('status')->label('Statut')->options(PublicationStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }
}
