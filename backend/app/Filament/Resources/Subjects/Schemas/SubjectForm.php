<?php

namespace App\Filament\Resources\Subjects\Schemas;

use App\Modules\Curriculum\Models\Curriculum;
use App\Modules\Curriculum\Models\Grade;
use App\Modules\Curriculum\Models\Track;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Matière')->columns(2)->schema([
                Select::make('curriculum_id')->label('Programme')
                    ->options(fn () => Curriculum::with('academicYear')->get()
                        ->mapWithKeys(fn ($c) => [$c->id => "{$c->name} ({$c->academicYear->label})"]))
                    ->required()->live(),
                Select::make('grade_id')->label('Classe')
                    ->options(fn () => Grade::with(['stage', 'cycle'])->orderBy('position')->get()
                        ->mapWithKeys(fn (Grade $g) => [$g->id => $g->full_name]))
                    ->searchable()->required()->live()
                    ->afterStateUpdated(fn (Set $set) => $set('tracks', [])),
                TextInput::make('name')->label('Nom')->required()->maxLength(120),
                TextInput::make('code')->label('Code')->required()->alphaDash()->maxLength(40)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule
                        ->where('curriculum_id', $get('curriculum_id'))->where('grade_id', $get('grade_id'))),
                ColorPicker::make('color')->label('Couleur'),
                TextInput::make('icon')->label('Icône (nom Material)')->maxLength(40),
                TextInput::make('position')->label('Ordre d\'affichage')->numeric()->default(0)->required(),
            ]),
            Section::make('Séries concernées')
                ->description('Ne rien cocher si la matière est commune à toutes les séries de la classe.')
                ->schema([
                    CheckboxList::make('tracks')->hiddenLabel()
                        ->relationship('tracks', 'name', fn ($query, Get $get) => $query
                            ->whereHas('grades', fn ($q) => $q->whereKey($get('grade_id')))->orderBy('position'))
                        ->columns(4),
                ])
                ->visible(fn (Get $get) => filled($get('grade_id'))
                    && Track::whereHas('grades', fn ($q) => $q->whereKey($get('grade_id')))->exists()),
        ]);
    }
}
