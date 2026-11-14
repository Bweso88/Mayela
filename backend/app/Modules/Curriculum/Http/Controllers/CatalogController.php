<?php

namespace App\Modules\Curriculum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Curriculum\Http\Resources\GradeResource;
use App\Modules\Curriculum\Models\Country;
use App\Modules\Curriculum\Models\Grade;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Catalogue
 *
 * Structure scolaire publique (aucune donnée personnelle), utilisée à l'inscription.
 */
class CatalogController extends Controller
{
    /**
     * Classes proposées dans un pays
     *
     * Classes disponibles dans l'application, du plus petit au plus grand niveau, avec leurs séries.
     *
     * @unauthenticated
     *
     * @urlParam country string required Code ISO du pays. Example: CG
     */
    public function grades(string $country): AnonymousResourceCollection
    {
        $country = Country::active()->where('code', strtoupper($country))->firstOrFail();

        return GradeResource::collection(
            Grade::available()
                ->whereHas('stage', fn ($q) => $q->where('country_id', $country->id))
                ->with(['stage', 'cycle', 'tracks'])
                ->orderBy('position')
                ->get()
        );
    }
}
