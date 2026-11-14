<?php

use Illuminate\Support\Facades\Route;

/*
| API REST v1 de Mayela. Chaque module déclare ses routes dans
| app/Modules/<Module>/routes/api.php ; elles sont montées ici sous /api/v1.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    require app_path('Modules/Identity/routes/api.php');
    require app_path('Modules/Curriculum/routes/api.php');
});
