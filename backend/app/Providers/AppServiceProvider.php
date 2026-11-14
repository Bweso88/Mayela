<?php

namespace App\Providers;

use App\Modules\Curriculum\Models\AcademicYear;
use App\Modules\Curriculum\Models\Chapter;
use App\Modules\Curriculum\Models\Curriculum;
use App\Modules\Curriculum\Models\EducationStage;
use App\Modules\Curriculum\Models\Grade;
use App\Modules\Curriculum\Models\Subject;
use App\Modules\Curriculum\Models\Track;
use App\Modules\Curriculum\Policies\ChapterPolicy;
use App\Modules\Curriculum\Policies\CurriculumStructurePolicy;
use App\Modules\Identity\Models\StudentProfile;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\StudentProfilePolicy;
use App\Modules\Identity\Policies\UserPolicy;
use App\Modules\Identity\Support\StudentLoginCodeGenerator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Détecte en développement les requêtes N+1 et les attributs non déclarés.
        Model::shouldBeStrict(! $this->app->isProduction());

        Gate::policy(StudentProfile::class, StudentProfilePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Chapter::class, ChapterPolicy::class);
        foreach ([EducationStage::class, Grade::class, Track::class, AcademicYear::class, Curriculum::class, Subject::class] as $model) {
            Gate::policy($model, CurriculumStructurePolicy::class);
        }

        // Le super-admin passe toutes les vérifications d'autorisation.
        Gate::before(fn ($user) => method_exists($user, 'hasRole') && $user->hasRole('super_admin') ? true : null);

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('auth', fn (Request $request) => [
            Limit::perMinute(10)->by('auth-ip:'.$request->ip()),
            Limit::perMinute(5)->by('auth-id:'.mb_strtolower((string) $request->input('identifier', $request->input('email')))),
        ]);

        // Protection contre la force brute sur les PIN enfants (4 à 6 chiffres).
        RateLimiter::for('student-login', fn (Request $request) => [
            Limit::perMinute(config('mayela.student_login.max_attempts_per_minute'))
                ->by('student-code:'.StudentLoginCodeGenerator::normalize((string) $request->input('login_code'))),
            Limit::perHour(30)->by('student-code-hour:'.StudentLoginCodeGenerator::normalize((string) $request->input('login_code'))),
            Limit::perMinute(20)->by('student-ip:'.$request->ip()),
        ]);
    }
}
