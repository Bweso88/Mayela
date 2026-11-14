<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Actions\CreateStudentProfile;
use App\Modules\Identity\Http\Requests\StoreChildRequest;
use App\Modules\Identity\Http\Resources\StudentProfileResource;
use App\Modules\Identity\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
            $request->user()->parentProfile->children()->with('country')->orderBy('first_name')->get()
        );
    }

    /**
     * Ajouter un enfant
     *
     * Crée le compte de l'enfant (connexion par code + PIN) et enregistre le consentement parental.
     */
    public function store(StoreChildRequest $request, CreateStudentProfile $create): StudentProfileResource
    {
        $student = $create(
            $request->safe()->except(['parental_consent', 'pin_confirmation']),
            $request->user()->parentProfile,
            $request->ip(),
            $request->userAgent(),
        );

        return StudentProfileResource::make($student);
    }

    /** Détail d'un enfant */
    public function show(StudentProfile $student): StudentProfileResource
    {
        Gate::authorize('view', $student);

        return StudentProfileResource::make($student->load('country'));
    }
}
