<?php

namespace App\Filament\Resources\Chapters\Schemas;

use App\Modules\Curriculum\Enums\PublicationStatus;
use App\Modules\Curriculum\Models\Subject;
use App\Modules\Curriculum\Models\Term;
use App\Modules\Identity\Enums\Permission;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ChapterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Chapitre')->columns(2)->schema([
                Select::make('subject_id')->label('Matière')
                    ->options(fn () => Subject::with(['grade', 'curriculum.academicYear'])->orderBy('grade_id')->orderBy('position')->get()
                        ->mapWithKeys(fn (Subject $s) => [$s->id => "{$s->name} — {$s->grade->name} — {$s->curriculum->academicYear->label}"]))
                    ->searchable()->required()->live()
                    ->afterStateUpdated(fn (Set $set) => $set('term_id', null)),
                Select::make('term_id')->label('Trimestre')
                    ->options(fn (Get $get) => Term::whereHas('academicYear.curriculum.subjects', fn ($q) => $q->whereKey($get('subject_id')))
                        ->orderBy('number')->pluck('name', 'id'))
                    ->placeholder('Non rattaché'),
                TextInput::make('title')->label('Titre')->required()->maxLength(190)->columnSpanFull(),
                Textarea::make('summary')->label('Résumé')->rows(3)->columnSpanFull(),
                TextInput::make('position')->label('Ordre dans la matière')->numeric()->default(0)->required(),
            ]),
            Section::make('Publication')->columns(3)->schema([
                Select::make('status')->label('Statut')
                    // Seuls les administrateurs pédagogiques publient ; un contributeur envoie en relecture.
                    ->options(fn () => collect(PublicationStatus::cases())
                        ->filter(fn (PublicationStatus $s) => auth()->user()->can(Permission::PublishContent->value)
                            || in_array($s, [PublicationStatus::Draft, PublicationStatus::InReview], true))
                        ->mapWithKeys(fn (PublicationStatus $s) => [$s->value => $s->getLabel()]))
                    ->default(PublicationStatus::Draft->value)->required(),
                Toggle::make('is_free')->label('Accessible en compte gratuit'),
                Toggle::make('is_demo')->label('Contenu de démonstration')
                    ->helperText('Affiché avec la mention « DÉMO » dans l\'application.'),
            ]),
        ]);
    }
}
