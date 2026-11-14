<?php

namespace App\Filament\Resources\Tracks\Schemas;

use App\Modules\Curriculum\Enums\TrackKind;
use App\Modules\Curriculum\Models\EducationStage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class TrackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                Select::make('education_stage_id')->label('Ordre d\'enseignement')
                    ->options(fn () => EducationStage::orderBy('position')->pluck('name', 'id'))
                    ->required()->live(),
                Select::make('kind')->label('Type')->options(TrackKind::class)->required(),
                TextInput::make('name')->label('Nom')->required()->maxLength(120),
                TextInput::make('code')->label('Code')->required()->maxLength(40)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('education_stage_id', $get('education_stage_id'))),
                TextInput::make('position')->label('Ordre d\'affichage')->numeric()->default(0)->required(),
            ]),
        ]);
    }
}
