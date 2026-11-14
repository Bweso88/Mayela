<?php

namespace App\Filament\Resources\Tracks;

use App\Filament\Resources\Tracks\Pages\CreateTrack;
use App\Filament\Resources\Tracks\Pages\EditTrack;
use App\Filament\Resources\Tracks\Pages\ListTracks;
use App\Filament\Resources\Tracks\Schemas\TrackForm;
use App\Filament\Resources\Tracks\Tables\TracksTable;
use App\Modules\Curriculum\Models\Track;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrackResource extends Resource
{
    protected static ?string $model = Track::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $modelLabel = 'série / spécialité';

    protected static ?string $pluralModelLabel = 'séries et spécialités';

    protected static string|\UnitEnum|null $navigationGroup = 'Programme scolaire';

    protected static ?string $navigationLabel = 'Séries et spécialités';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TrackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TracksTable::configure($table);
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
            'index' => ListTracks::route('/'),
            'create' => CreateTrack::route('/create'),
            'edit' => EditTrack::route('/{record}/edit'),
        ];
    }
}
