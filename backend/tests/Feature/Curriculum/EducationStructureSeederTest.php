<?php

use App\Modules\Curriculum\Models\EducationStage;
use App\Modules\Curriculum\Models\Grade;
use App\Modules\Curriculum\Models\Track;
use Database\Seeders\CongoEducationStructureSeeder;

it('charge la structure du système éducatif congolais fournie par l\'équipe', function () {
    expect(EducationStage::orderBy('position')->pluck('name')->all())
        ->toBe(['Préscolaire', 'Primaire', 'Secondaire général', 'Secondaire technique']);

    expect(Grade::orderBy('position')->pluck('code')->all())
        ->toBe(['P1', 'P2', 'P3', 'CP1', 'CP2', 'CE1', 'CE2', 'CM1', 'CM2', '6E', '5E', '4E', '3E', '2NDE', '1ERE', 'TLE']);

    expect(grade('6E')->cycle->name)->toBe('Collège')
        ->and(grade('TLE')->cycle->name)->toBe('Lycée')
        ->and(grade('CE1')->cycle)->toBeNull();
});

it('rattache les séries attendues à chaque classe du lycée', function (string $grade, array $series) {
    expect(grade($grade)->tracks->pluck('code')->all())->toBe($series);
})->with([
    'Seconde' => ['2NDE', ['A', 'C']],
    'Première' => ['1ERE', ['A', 'B', 'C', 'D']],
    'Terminale' => ['TLE', ['A', 'B', 'C', 'D']],
    'Collège sans série' => ['3E', []],
]);

it('limite les classes proposées dans l\'app au périmètre CE1 → Terminale', function () {
    expect(Grade::available()->orderBy('position')->pluck('code')->all())
        ->toBe(['CE1', 'CE2', 'CM1', 'CM2', '6E', '5E', '4E', '3E', '2NDE', '1ERE', 'TLE']);
});

it('crée les spécialités du technique sans inventer de classes', function () {
    $technique = EducationStage::where('code', 'secondaire_technique')->first();

    expect($technique->tracks->pluck('name')->all())->toBe(['Industriel', 'Commercial', 'Agricole'])
        ->and($technique->grades)->toBeEmpty();
});

it('est idempotent', function () {
    $this->seed(CongoEducationStructureSeeder::class);

    expect(Grade::count())->toBe(16)->and(Track::count())->toBe(7);
});

it('attribue l\'univers visuel selon le cycle', function () {
    expect(grade('CM2')->universe->value)->toBe('junior')
        ->and(grade('5E')->universe->value)->toBe('college')
        ->and(grade('1ERE')->universe->value)->toBe('lycee');
});
