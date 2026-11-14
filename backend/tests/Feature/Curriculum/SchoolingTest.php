<?php

use App\Modules\Curriculum\Models\AcademicYear;
use App\Modules\Curriculum\Models\Chapter;
use App\Modules\Curriculum\Models\Curriculum;
use App\Modules\Curriculum\Models\Subject;
use App\Modules\Identity\Enums\Role;
use Laravel\Sanctum\Sanctum;

function setSchooling(object $test, $student, array $payload)
{
    return $test->putJson("/api/v1/parent/children/{$student->uuid}/schooling", $payload);
}

it('permet au parent de définir la classe de son enfant', function () {
    $parent = actingAsParent();
    $child = childOf($parent);

    setSchooling($this, $child, ['grade_id' => grade('CM2')->id])
        ->assertOk()
        ->assertJsonPath('data.grade.code', 'CM2')
        ->assertJsonPath('data.grade.universe', 'junior')
        ->assertJsonPath('data.track', null);
});

it('exige une série valide quand la classe en propose', function () {
    $parent = actingAsParent();
    $child = childOf($parent, ['birth_date' => now()->subYears(16)->toDateString()]);

    setSchooling($this, $child, ['grade_id' => grade('1ERE')->id])
        ->assertUnprocessable()->assertJsonValidationErrors('track_id');

    // La série B n'existe pas en Seconde.
    setSchooling($this, $child, ['grade_id' => grade('2NDE')->id, 'track_id' => track('B')->id])
        ->assertUnprocessable()->assertJsonValidationErrors('track_id');

    setSchooling($this, $child, ['grade_id' => grade('1ERE')->id, 'track_id' => track('C')->id])
        ->assertOk()->assertJsonPath('data.track.code', 'C');
});

it('ignore la série pour une classe qui n\'en a pas', function () {
    $parent = actingAsParent();
    $child = childOf($parent);

    setSchooling($this, $child, ['grade_id' => grade('4E')->id, 'track_id' => track('A')->id])
        ->assertOk()->assertJsonPath('data.track', null);
});

it('refuse une classe hors du périmètre de l\'application', function () {
    $parent = actingAsParent();

    setSchooling($this, childOf($parent), ['grade_id' => grade('CP1')->id])
        ->assertUnprocessable()->assertJsonValidationErrors('grade_id');
});

it('interdit de modifier la classe de l\'enfant d\'un autre parent', function () {
    $other = childOf(parentUser());
    actingAsParent();

    setSchooling($this, $other, ['grade_id' => grade('CM2')->id])->assertForbidden();
});

it('crée un enfant avec sa classe en une seule requête, ou rien en cas d\'erreur', function () {
    actingAsParent();
    $payload = [
        'first_name' => 'Awa', 'last_name' => 'Ngoma', 'birth_date' => now()->subYears(9)->toDateString(),
        'country_code' => 'CG', 'pin' => '1357', 'pin_confirmation' => '1357', 'parental_consent' => true,
    ];

    $this->postJson('/api/v1/parent/children', [...$payload, 'grade_id' => grade('CE2')->id])
        ->assertCreated()->assertJsonPath('data.grade.code', 'CE2');

    $this->postJson('/api/v1/parent/children', [...$payload, 'first_name' => 'Kevin', 'grade_id' => grade('P1')->id])
        ->assertUnprocessable();
    $this->assertDatabaseMissing('student_profiles', ['first_name' => 'Kevin']);
});

it('permet à l\'élève de choisir sa classe', function () {
    $student = childOf(parentUser());
    Sanctum::actingAs($student->user, [Role::Student->value]);

    $this->putJson('/api/v1/me/schooling', ['grade_id' => grade('TLE')->id, 'track_id' => track('D')->id])
        ->assertOk()->assertJsonPath('data.grade.code', 'TLE')->assertJsonPath('data.track.code', 'D');
});

describe('matières de l\'élève', function () {
    beforeEach(function () {
        $this->student = childOf(parentUser());
        $this->student->update(['grade_id' => grade('TLE')->id, 'track_id' => track('C')->id]);
        Sanctum::actingAs($this->student->user, [Role::Student->value]);

        $year = AcademicYear::create(['country_id' => $this->student->country_id, 'label' => '2026-2027', 'is_current' => true]);
        $this->curriculum = Curriculum::create(['academic_year_id' => $year->id, 'name' => 'Programme DÉMO', 'status' => 'draft']);

        $make = fn (string $code, int $position) => Subject::create([
            'curriculum_id' => $this->curriculum->id, 'grade_id' => grade('TLE')->id,
            'code' => $code, 'name' => $code, 'position' => $position,
        ]);
        $this->common = $make('francais', 1);
        $make('philo-a', 2)->tracks()->attach(track('A')->id);
        $make('maths-c', 3)->tracks()->attach(track('C')->id, ['coefficient' => 5]);

        Chapter::create(['subject_id' => $this->common->id, 'title' => 'Chapitre publié', 'status' => 'published', 'is_demo' => true]);
        Chapter::create(['subject_id' => $this->common->id, 'title' => 'Brouillon', 'status' => 'draft']);
    });

    it('ne renvoie rien tant que le programme n\'est pas publié', function () {
        $this->getJson('/api/v1/me/subjects')->assertOk()->assertJsonCount(0, 'data');
    });

    it('filtre par série et ne compte que les chapitres publiés', function () {
        $this->curriculum->update(['status' => 'published', 'official_reference' => 'DÉMO']);

        $response = $this->getJson('/api/v1/me/subjects')->assertOk()->assertJsonPath('meta.academic_year', '2026-2027');

        expect(collect($response->json('data'))->pluck('code')->all())->toBe(['francais', 'maths-c'])
            ->and($response->json('data.0.published_chapters_count'))->toBe(1);
    });
});
