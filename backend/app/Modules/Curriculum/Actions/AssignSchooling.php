<?php

namespace App\Modules\Curriculum\Actions;

use App\Modules\Curriculum\Models\Grade;
use App\Modules\Identity\Models\StudentProfile;
use Illuminate\Validation\ValidationException;

/**
 * Affecte une classe (et une série si la classe en propose) à un élève.
 * Règles : classe proposée dans l'app, du pays de l'élève ; série obligatoire et
 * valide pour la classe quand elle en a ; aucune série sinon.
 */
class AssignSchooling
{
    public function __invoke(StudentProfile $student, int $gradeId, ?int $trackId): StudentProfile
    {
        $grade = Grade::available()
            ->whereHas('stage', fn ($q) => $q->where('country_id', $student->country_id))
            ->with('tracks')
            ->find($gradeId);

        if (! $grade) {
            throw ValidationException::withMessages(['grade_id' => __('Cette classe n\'est pas disponible.')]);
        }

        if ($grade->tracks->isNotEmpty() && ! $grade->tracks->contains('id', $trackId)) {
            throw ValidationException::withMessages(['track_id' => __('Choisis une série valide pour cette classe.')]);
        }

        $student->forceFill([
            'grade_id' => $grade->id,
            'track_id' => $grade->tracks->isNotEmpty() ? $trackId : null,
        ])->save();

        return $student->load(['grade', 'track', 'country']);
    }
}
