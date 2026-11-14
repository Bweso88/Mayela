<?php

namespace Database\Seeders;

use App\Modules\Curriculum\Enums\TrackKind;
use App\Modules\Curriculum\Enums\Universe;
use App\Modules\Curriculum\Models\Country;
use App\Modules\Curriculum\Models\EducationStage;
use App\Modules\Curriculum\Models\Grade;
use App\Modules\Curriculum\Models\Track;
use Illuminate\Database\Seeder;

/**
 * Structure du système éducatif de la République du Congo, telle que fournie par
 * l'équipe pédagogique Mayela (14/11/2026). Seule la structure est chargée ici :
 * aucune matière ni aucun chapitre (ils viennent du programme officiel, importé et validé).
 *
 * Périmètre de l'application : CE1 → Terminale (secondaire général). Les autres classes
 * existent dans la structure mais ne sont pas proposées aux élèves (is_available = false).
 * Les spécialités du secondaire technique sans classes détaillées sont complétées dans le back-office.
 *
 * Idempotent : relançable sans doublons.
 */
class CongoEducationStructureSeeder extends Seeder
{
    private const STRUCTURE = [
        ['code' => 'prescolaire', 'name' => 'Préscolaire', 'cycles' => [
            [null, [['P1', 'P1'], ['P2', 'P2'], ['P3', 'P3']], Universe::Junior, false],
        ]],
        ['code' => 'primaire', 'name' => 'Primaire', 'cycles' => [
            [null, [['CP1', 'CP1'], ['CP2', 'CP2']], Universe::Junior, false],
            [null, [['CE1', 'CE1'], ['CE2', 'CE2'], ['CM1', 'CM1'], ['CM2', 'CM2']], Universe::Junior, true],
        ]],
        ['code' => 'secondaire_general', 'name' => 'Secondaire général', 'cycles' => [
            [['college', 'Collège'], [['6E', '6e'], ['5E', '5e'], ['4E', '4e'], ['3E', '3e']], Universe::College, true],
            [['lycee', 'Lycée'], [['2NDE', 'Seconde'], ['1ERE', 'Première'], ['TLE', 'Terminale']], Universe::Lycee, true],
        ]],
        ['code' => 'secondaire_technique', 'name' => 'Secondaire technique', 'cycles' => []],
    ];

    /** Séries par classe du lycée général. */
    private const SERIES = [
        '2NDE' => ['A', 'C'],
        '1ERE' => ['A', 'B', 'C', 'D'],
        'TLE' => ['A', 'B', 'C', 'D'],
    ];

    private const TECHNICAL_SPECIALTIES = [
        'INDUSTRIEL' => 'Industriel',
        'COMMERCIAL' => 'Commercial',
        'AGRICOLE' => 'Agricole',
    ];

    public function run(): void
    {
        $country = Country::where('code', 'CG')->firstOrFail();
        $gradePosition = 0;

        foreach (self::STRUCTURE as $stagePosition => $stageData) {
            $stage = EducationStage::updateOrCreate(
                ['country_id' => $country->id, 'code' => $stageData['code']],
                ['name' => $stageData['name'], 'position' => $stagePosition],
            );

            foreach ($stageData['cycles'] as $cyclePosition => [$cycleData, $grades, $universe, $available]) {
                $cycle = $cycleData
                    ? $stage->cycles()->updateOrCreate(['code' => $cycleData[0]], ['name' => $cycleData[1], 'position' => $cyclePosition])
                    : null;

                foreach ($grades as [$code, $name]) {
                    Grade::updateOrCreate(
                        ['education_stage_id' => $stage->id, 'code' => $code],
                        [
                            'education_cycle_id' => $cycle?->id,
                            'name' => $name,
                            'position' => ++$gradePosition,
                            'universe' => $universe,
                            'is_available' => $available,
                        ],
                    );
                }
            }
        }

        $this->seedSeries($country);
        $this->seedTechnicalSpecialties($country);
    }

    private function seedSeries(Country $country): void
    {
        $stage = EducationStage::where(['country_id' => $country->id, 'code' => 'secondaire_general'])->firstOrFail();

        foreach (self::SERIES as $gradeCode => $series) {
            $grade = $stage->grades()->where('code', $gradeCode)->firstOrFail();
            $trackIds = collect($series)->map(fn (string $letter) => Track::updateOrCreate(
                ['education_stage_id' => $stage->id, 'code' => $letter],
                ['kind' => TrackKind::Series, 'name' => "Série {$letter}", 'position' => ord($letter)],
            )->id);

            $grade->tracks()->syncWithoutDetaching($trackIds);
        }
    }

    private function seedTechnicalSpecialties(Country $country): void
    {
        $stage = EducationStage::where(['country_id' => $country->id, 'code' => 'secondaire_technique'])->firstOrFail();

        foreach (array_keys(self::TECHNICAL_SPECIALTIES) as $position => $code) {
            Track::updateOrCreate(
                ['education_stage_id' => $stage->id, 'code' => $code],
                ['kind' => TrackKind::Specialty, 'name' => self::TECHNICAL_SPECIALTIES[$code], 'position' => $position],
            );
        }
    }
}
