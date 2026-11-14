<?php

namespace App\Filament\Resources\Curricula\Schemas;

use App\Modules\Curriculum\Enums\PublicationStatus;
use App\Modules\Curriculum\Models\AcademicYear;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CurriculumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                Select::make('academic_year_id')->label('Année scolaire')
                    ->options(fn () => AcademicYear::with('country')->orderByDesc('label')->get()
                        ->mapWithKeys(fn ($y) => [$y->id => "{$y->label} — {$y->country->name}"]))
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('name')->label('Nom')->required()->maxLength(190)
                    ->placeholder('Programme scolaire 2026-2027'),
                TextInput::make('official_reference')->label('Référence du texte officiel')
                    ->helperText('Obligatoire pour publier : référence du document officiel dont provient le programme.')
                    ->requiredIf('status', PublicationStatus::Published->value)
                    ->maxLength(255),
                Select::make('status')->label('Statut')->options(PublicationStatus::class)
                    ->default(PublicationStatus::Draft)->required(),
            ]),
        ]);
    }
}
