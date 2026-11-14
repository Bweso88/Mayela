<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\Request;

/** @group Compte */
class MeController extends Controller
{
    /** Utilisateur connecté (avec profil élève ou enfants rattachés) */
    public function __invoke(Request $request): UserResource
    {
        return UserResource::make(
            $request->user()->load(['studentProfile.country', 'parentProfile.children.country'])
        );
    }
}
