<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Actions\AuthenticateStudentWithCode;
use App\Modules\Identity\Actions\AuthenticateWithPassword;
use App\Modules\Identity\Actions\CreateStudentProfile;
use App\Modules\Identity\Actions\IssueApiToken;
use App\Modules\Identity\Actions\RegisterParent;
use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Http\Requests\RegisterParentRequest;
use App\Modules\Identity\Http\Requests\RegisterStudentRequest;
use App\Modules\Identity\Http\Requests\StudentLoginRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use App\Modules\Identity\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Authentification
 *
 * Inscription et connexion depuis l'application mobile. Chaque connexion crée un
 * jeton par appareil à envoyer dans l'en-tête `Authorization: Bearer <token>`.
 */
class AuthController extends Controller
{
    /**
     * Inscription d'un parent / tuteur
     *
     * @unauthenticated
     */
    public function registerParent(RegisterParentRequest $request, RegisterParent $register, IssueApiToken $issue): JsonResponse
    {
        $user = $register($request->validated());

        return $this->tokenResponse($user, $issue($user, $request->string('device_name'), Role::Parent->value), 201);
    }

    /**
     * Inscription autonome d'un élève (âge ≥ majorité numérique)
     *
     * En dessous de l'âge configuré (15 ans), le compte doit être créé par un parent.
     *
     * @unauthenticated
     */
    public function registerStudent(RegisterStudentRequest $request, CreateStudentProfile $create, IssueApiToken $issue): JsonResponse
    {
        $student = $create($request->validated(), null, $request->ip(), $request->userAgent());

        return $this->tokenResponse($student->user, $issue($student->user, $request->string('device_name'), Role::Student->value), 201);
    }

    /**
     * Connexion par e-mail ou téléphone
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request, AuthenticateWithPassword $authenticate, IssueApiToken $issue): JsonResponse
    {
        $user = $authenticate($request->string('identifier'), $request->string('password'));
        $ability = $user->hasRole(Role::Parent->value) ? Role::Parent->value : Role::Student->value;

        return $this->tokenResponse($user, $issue($user, $request->string('device_name'), $ability));
    }

    /**
     * Connexion enfant (code élève + PIN)
     *
     * @unauthenticated
     */
    public function studentLogin(StudentLoginRequest $request, AuthenticateStudentWithCode $authenticate, IssueApiToken $issue): JsonResponse
    {
        $user = $authenticate($request->string('login_code'), $request->string('pin'));

        return $this->tokenResponse($user, $issue($user, $request->string('device_name'), Role::Student->value));
    }

    /** Déconnexion (révoque le jeton de l'appareil courant) */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    private function tokenResponse(User $user, string $token, int $status = 200): JsonResponse
    {
        $user->load(['studentProfile.country', 'parentProfile.children.country']);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => UserResource::make($user),
        ], $status);
    }
}
