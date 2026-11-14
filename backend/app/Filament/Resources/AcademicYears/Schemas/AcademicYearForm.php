<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use App\Modules\Curriculum\Models\Country;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Année scolaire')->columns(2)->schema([
                Select::make('country_id')->label('Pays')
                    ->options(fn () => Country::orderBy('name')->pluck('name', 'id'))
                    ->default(fn () => Country::where('code', 'CG')->value('id'))
                    ->required()->live(),
                TextInput::make('label')->label('Libellé')->placeholder('2026-2027')->required()
                    ->regex('/^\d{4}-\d{4}$/')
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('country_id', $get('country_id'))),
                DatePicker::make('starts_on')->label('Rentrée'),
                DatePicker::make('ends_on')->label('Fin d\'année')->afterOrEqual('starts_on'),
                Toggle::make('is_current')->label('Année en cours')
                    ->helperText('Une seule année en cours par pays : les autres sont désactivées automatiquement.'),
            ]),
            Section::make('Trimestres')->schema([
                Repeater::make('terms')->hiddenLabel()
                    ->relationship()
                    ->orderColumn('number')
                    ->columns(3)
                    ->defaultItems(0)
                    ->maxItems(4)
                    ->schema([
                        TextInput::make('name')->label('Nom')->required()->placeholder('1er trimestre'),
                        DatePicker::make('starts_on')->label('Début'),
                        DatePicker::make('ends_on')->label('Fin'),
                    ])
                    ->addActionLabel('Ajouter un trimestre'),
            ]),
        ]);
    }
}
