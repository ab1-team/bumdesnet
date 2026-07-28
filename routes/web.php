<?php

use App\Http\Controllers\MasterAuthController;
use App\Http\Controllers\MasterDashboardController;
use App\Http\Controllers\MasterTenantController;
use App\Http\Controllers\Tenant\HakAksesController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::prefix('master')->group(function () {
            Route::get('/', [HakAksesController::class, 'index']);
            Route::get('/hakakses/{id_user}', [HakAksesController::class, 'hakAkses']);
            Route::post('/hakakses/{id_user}', [HakAksesController::class, 'simpan']);
        });

        Route::middleware(['guest:admin'])->group(function () {
            Route::get('/auth', [MasterAuthController::class, 'index']);
            Route::post('/auth', [MasterAuthController::class, 'login']);
        });

        Route::middleware(['auth'])->group(function () {
            Route::get('/', [MasterDashboardController::class, 'index']);
            Route::resource('/tenant', MasterTenantController::class);
        });
    });

    Route::get('/link', function () {
        $target = __DIR__ . '/../storage/app/public';
        $shortcut = __DIR__ . '/../public/storage';

        try {
            symlink($target, $shortcut);

            return response()->json('Symlink created successfully.');
        } catch (\Exception $e) {
            return response()->json('Failed to create symlink: ' . $e->getMessage());
        }
    });
}
