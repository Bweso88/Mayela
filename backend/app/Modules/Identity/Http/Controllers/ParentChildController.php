<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Curriculum\Actions\AssignSchooling;
use App\Modules\Identity\Actions\CreateStudentProfile;
use App\Modules\Identity\Http\Requests\StoreChildRequest;
use App\Modules\Identity\Http\Resources\StudentProfileResource;
use App\Modules\Identity\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * @group Espace parent
 *
 * Gestion des enfants rattachés au compte parent.
 */
class ParentChildController extends Controller
{
    /** Liste des enfants */
    public function index(Request $request): AnonymousResourceCollection
    {
        return StudentProfileResource::collection(
            $request->user()->parentProfile->children()->with(StudentProfile::API_RELATIONS)->orderBy('first_name')->get()
        );
    }

    /**
     * Ajouter un enfant
     *
     * Crée le compte de l'enfant (connexion par code + PIN) et enregistre le consentement parental.
     */
    public function store(StoreChildRequest $request, CreateStudentProfile $create, AssignSchooling $assign): StudentProfileResource
    {
        // Une classe invalide annule toute la création (transaction).
        $student = DB::transaction(function () use ($request, $create, $assign) {
            $student = $create(
                $request->safe()->except(['parental_consent', 'pin_confirmation', 'grade_id', 'track_id']),
                $request->user()->parentProfile,
                $request->ip(),
                $request->userAgent(),
            );

            return $request->filled('grade_id')
                ? $assign($student, $request->integer('grade_id'), $request->input('track_id'))
                : $student;
        });

        return StudentProfileResource::make($student);
    }

    /** Détail d'un enfant */
    public function show(StudentProfile $student): StudentProfileResource
    {
        Gate::authorize('view', $student);

        return StudentProfileResource::make($student->load(StudentProfile::API_RELATIONS));
    }
}
