<?php

namespace App\Filament\Resources\EducationStages;

use App\Filament\Resources\EducationStages\Pages\CreateEducationStage;
use App\Filament\Resources\EducationStages\Pages\EditEducationStage;
use App\Filament\Resources\EducationStages\Pages\ListEducationStages;
use App\Filament\Resources\EducationStages\Schemas\EducationStageForm;
use App\Filament\Resources\EducationStages\Tables\EducationStagesTable;
use App\Modules\Curriculum\Models\EducationStage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EducationStageResource extends Resource
{
    protected static ?string $model = EducationStage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $modelLabel = "ordre d'enseignement";

    protected static ?string $pluralModelLabel = "ordres d'enseignement";

    protected static string|\UnitEnum|null $navigationGroup = 'Programme scolaire';

    protected static ?string $navigationLabel = "Ordres d'enseignement";

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EducationStageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EducationStagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEducationStages::route('/'),
            'create' => CreateEducationStage::route('/create'),
            'edit' => EditEducationStage::route('/{record}/edit'),
        ];
    }
}
