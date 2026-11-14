<?php

namespace App\Filament\Resources\Grades\Schemas;

use App\Modules\Curriculum\Enums\Universe;
use App\Modules\Curriculum\Models\EducationCycle;
use App\Modules\Curriculum\Models\EducationStage;
use App\Modules\Curriculum\Models\Track;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Classe')->columns(2)->schema([
                Select::make('education_stage_id')->label('Ordre d\'enseignement')
                    ->options(fn () => EducationStage::with('country')->orderBy('position')->get()
                        ->mapWithKeys(fn ($s) => [$s->id => "{$s->name} — {$s->country->name}"]))
                    ->required()->live()
                    ->afterStateUpdated(fn (Set $set) => $set('education_cycle_id', null)),
                Select::make('education_cycle_id')->label('Cycle')
                    ->options(fn (Get $get) => EducationCycle::where('education_stage_id', $get('education_stage_id'))
                        ->orderBy('position')->pluck('name', 'id'))
                    ->placeholder('Aucun'),
                TextInput::make('name')->label('Nom affiché')->required()->maxLength(60),
                TextInput::make('code')->label('Code')->required()->maxLength(20)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('education_stage_id', $get('education_stage_id'))),
                TextInput::make('position')->label('Ordre (du plus petit au plus grand)')->numeric()->required()->default(0),
            ]),
            Section::make('Application élève')->columns(2)->schema([
                Select::make('universe')->label('Univers visuel')->options(Universe::class)->required(),
                Toggle::make('is_available')->label('Proposée aux élèves dans l\'application')
                    ->helperText('Périmètre actuel : CE1 → Terminale.'),
            ]),
            Section::make('Séries / spécialités')
                ->description('Cochez les séries ouvertes dans cette classe. L\'élève devra en choisir une.')
                ->schema([
                    CheckboxList::make('tracks')->hiddenLabel()
                        ->relationship('tracks', 'name', fn ($query, Get $get) => $query
                            ->where('education_stage_id', $get('education_stage_id'))->orderBy('position'))
                        ->columns(4),
                ])
                ->visible(fn (Get $get) => Track::where('education_stage_id', $get('education_stage_id'))->exists()),
        ]);
    }
}
