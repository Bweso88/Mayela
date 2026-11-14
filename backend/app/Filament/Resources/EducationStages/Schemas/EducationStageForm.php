<?php

namespace App\Filament\Resources\EducationStages\Schemas;

use App\Modules\Curriculum\Models\Country;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class EducationStageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                Select::make('country_id')->label('Pays')
                    ->options(fn () => Country::orderBy('name')->pluck('name', 'id'))
                    ->default(fn () => Country::where('code', 'CG')->value('id'))
                    ->required()->live(),
                TextInput::make('name')->label('Nom')->required()->maxLength(120),
                TextInput::make('code')->label('Code technique')->required()->maxLength(40)
                    ->alphaDash()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('country_id', $get('country_id'))),
                TextInput::make('position')->label('Ordre d\'affichage')->numeric()->default(0)->required(),
            ]),
            Section::make('Cycles')
                ->description('Facultatif : subdivisions de l\'ordre (ex. collège, lycée).')
                ->schema([
                    Repeater::make('cycles')->hiddenLabel()
                        ->relationship()
                        ->orderColumn('position')
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')->label('Nom')->required()->maxLength(120),
                            TextInput::make('code')->label('Code')->required()->alphaDash()->maxLength(40),
                        ])
                        ->addActionLabel('Ajouter un cycle'),
                ]),
        ]);
    }
}
