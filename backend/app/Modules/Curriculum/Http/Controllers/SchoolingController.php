<?php

namespace App\Modules\Curriculum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Curriculum\Actions\AssignSchooling;
use App\Modules\Curriculum\Http\Requests\SchoolingRequest;
use App\Modules\Curriculum\Http\Resources\SubjectResource;
use App\Modules\Curriculum\Models\AcademicYear;
use App\Modules\Curriculum\Models\Subject;
use App\Modules\Identity\Http\Resources\StudentProfileResource;
use App\Modules\Identity\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/** @group Scolarité */
class SchoolingController extends Controller
{
    /**
     * Choisir sa classe (élève)
     *
     * `track_id` est obligatoire lorsque la classe propose des séries (ex. Première).
     */
    public function updateOwn(SchoolingRequest $request, AssignSchooling $assign): StudentProfileResource
    {
        $student = $request->user()->studentProfile;
        abort_unless($student, 403);

        return StudentProfileResource::make($assign($student, $request->integer('grade_id'), $request->input('track_id')));
    }

    /** Définir la classe d'un enfant (parent) */
    public function updateChild(SchoolingRequest $request, StudentProfile $student, AssignSchooling $assign): StudentProfileResource
    {
        Gate::authorize('update', $student);

        return StudentProfileResource::make($assign($student, $request->integer('grade_id'), $request->input('track_id')));
    }

    /**
     * Mes matières
     *
     * Matières de la classe (et de la série) de l'élève dans le programme publié de l'année en cours.
     * Liste vide tant que la classe n'est pas choisie ou que le programme n'est pas publié.
     */
    public function subjects(Request $request): AnonymousResourceCollection
    {
        $student = $request->user()->studentProfile;
        abort_unless($student, 403);

        $year = AcademicYear::current()->where('country_id', $student->country_id)
            ->whereHas('curriculum', fn ($q) => $q->published())
            ->with('curriculum')
            ->first();

        $subjects = ($student->grade_id && $year)
            ? Subject::where('curriculum_id', $year->curriculum->id)
                ->forStudent($student->grade_id, $student->track_id)
                ->withCount(['chapters' => fn ($q) => $q->published()])
                ->orderBy('position')
                ->get()
            : collect();

        return SubjectResource::collection($subjects)->additional([
            'meta' => ['academic_year' => $year?->label],
        ]);
    }
}
