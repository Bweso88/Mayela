<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Curriculum\Models\Country;
use App\Modules\Identity\Enums\ConsentType;
use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Models\ParentProfile;
use App\Modules\Identity\Models\StudentProfile;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Support\StudentLoginCodeGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Crée un compte élève, soit par un parent (enfant rattaché), soit par l'élève
 * lui-même s'il a atteint l'âge de la majorité numérique.
 * Le consentement « compte » est horodaté dans tous les cas.
 */
class CreateStudentProfile
{
    public function __construct(private StudentLoginCodeGenerator $codes) {}

    /**
     * @param  array{first_name: string, last_name: string, birth_date: string, country_code: string,
     *               school_name?: ?string, pin?: ?string, email?: ?string, phone?: ?string, password?: ?string}  $data
     */
    public function __invoke(array $data, ?ParentProfile $parent, ?string $ip = null, ?string $userAgent = null): StudentProfile
    {
        $age = Carbon::parse($data['birth_date'])->age;

        if ($parent === null && $age < config('mayela.digital_majority_age')) {
            throw ValidationException::withMessages([
                'birth_date' => __('Un parent ou tuteur doit créer le compte des élèves de moins de :age ans.', [
                    'age' => config('mayela.digital_majority_age'),
                ]),
            ]);
        }

        return DB::transaction(function () use ($data, $parent, $ip, $userAgent) {
            $country = Country::active()->where('code', strtoupper($data['country_code']))->firstOrFail();

            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                // Compte créé par un parent : pas de mot de passe connu, connexion par code + PIN.
                'password' => $data['password'] ?? Str::random(48),
                'locale' => $country->default_locale,
            ]);
            $user->assignRole(Role::Student->value);

            $student = $user->studentProfile()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birth_date' => $data['birth_date'],
                'country_id' => $country->id,
                'school_name' => $data['school_name'] ?? null,
                'login_code' => $this->codes->generate(),
                'pin_hash' => isset($data['pin']) ? Hash::make($data['pin']) : null,
            ]);

            if ($parent) {
                $isFirstParent = ! $student->parents()->exists();
                $student->parents()->attach($parent->id, ['is_primary' => $isFirstParent]);
            }

            $student->consents()->create([
                'parent_profile_id' => $parent?->id,
                'type' => ConsentType::Account,
                'version' => config('mayela.consent_version'),
                'granted_at' => now(),
                'ip_address' => $ip,
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
            ]);

            return $student->load(['user', ...StudentProfile::API_RELATIONS]);
        });
    }
}
