<?php

use App\Filament\Resources\AcademicYears\Pages\CreateAcademicYear;
use App\Filament\Resources\Chapters\Pages\CreateChapter;
use App\Filament\Resources\Curricula\Pages\CreateCurriculum;
use App\Filament\Resources\EducationStages\EducationStageResource;
use App\Filament\Resources\Grades\GradeResource;
use App\Filament\Resources\Grades\Pages\ListGrades;
use App\Modules\Curriculum\Models\AcademicYear;
use App\Modules\Curriculum\Models\Chapter;
use App\Modules\Curriculum\Models\Country;
use App\Modules\Curriculum\Models\Curriculum;
use App\Modules\Curriculum\Models\Subject;
use App\Modules\Identity\Enums\Role;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(fn () => Filament::setCurrentPanel('admin'));

function cgId(): int
{
    return Country::where('code', 'CG')->value('id');
}

it('affiche les pages du programme au super-admin', function (string $url) {
    $this->actingAs(staff())->get($url)->assertOk();
})->with([
    '/admin/education-stages', '/admin/grades', '/admin/tracks', '/admin/academic-years',
    '/admin/curricula', '/admin/subjects', '/admin/chapters', '/admin/grades/create',
]);

it('liste les classes du Congo dans le back-office', function () {
    $this->actingAs(staff());

    Livewire::test(ListGrades::class)->assertCanSeeTableRecords([grade('CE1'), grade('TLE')]);
});

it('réserve la modification de la structure à la permission curriculum.manage', function () {
    $this->actingAs(staff(Role::Contributor));

    expect(GradeResource::canViewAny())->toBeTrue()
        ->and(GradeResource::canCreate())->toBeFalse()
        ->and(EducationStageResource::canEdit(grade('CE1')->stage))->toBeFalse();
});

it('garde une seule année en cours par pays', function () {
    $this->actingAs(staff());
    $old = AcademicYear::create(['country_id' => cgId(), 'label' => '2025-2026', 'is_current' => true]);

    Livewire::test(CreateAcademicYear::class)
        ->fillForm(['country_id' => cgId(), 'label' => '2026-2027', 'is_current' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($old->fresh()->is_current)->toBeFalse();
});

it('exige la référence officielle pour publier un programme et trace la publication', function () {
    $admin = staff();
    $this->actingAs($admin);
    $year = AcademicYear::create(['country_id' => cgId(), 'label' => '2026-2027']);

    Livewire::test(CreateCurriculum::class)
        ->fillForm(['academic_year_id' => $year->id, 'name' => 'Programme 2026-2027', 'status' => 'published'])
        ->call('create')
        ->assertHasFormErrors(['official_reference']);

    Livewire::test(CreateCurriculum::class)
        ->fillForm(['academic_year_id' => $year->id, 'name' => 'Programme 2026-2027', 'status' => 'published', 'official_reference' => 'Réf. test'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Curriculum::first())->published_by->toBe($admin->id)->published_at->not->toBeNull();
});

it('empêche un contributeur de publier un chapitre', function () {
    $year = AcademicYear::create(['country_id' => cgId(), 'label' => '2026-2027']);
    $curriculum = Curriculum::create(['academic_year_id' => $year->id, 'name' => 'P']);
    $subject = Subject::create(['curriculum_id' => $curriculum->id, 'grade_id' => grade('CM2')->id, 'code' => 'maths', 'name' => 'Mathématiques']);
    $this->actingAs(staff(Role::Contributor));

    Livewire::test(CreateChapter::class)
        ->fillForm(['subject_id' => $subject->id, 'title' => 'Les fractions', 'position' => 1, 'status' => 'published'])
        ->call('create')
        ->assertHasFormErrors(['status']);

    Livewire::test(CreateChapter::class)
        ->fillForm(['subject_id' => $subject->id, 'title' => 'Les fractions', 'position' => 1, 'status' => 'in_review'])
        ->call('create')
        ->assertHasNoFormErrors();

    $chapter = Chapter::first();
    expect($chapter->status->value)->toBe('in_review')
        ->and(auth()->user()->can('update', $chapter))->toBeTrue();

    $chapter->update(['status' => 'published']);
    expect(auth()->user()->can('update', $chapter->fresh()))->toBeFalse();
});
