<?php

use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\ClaimFormController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Telegram\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('panel')
        : redirect()->route('login');
});

Route::post('/telegram/webhook', WebhookController::class)->name('telegram.webhook');

Route::get('/shikoyat/{token}', [ClaimFormController::class, 'show'])->name('claim.form');
Route::post('/shikoyat/{token}', [ClaimFormController::class, 'store'])->name('claim.store');
Route::get('/shikoyat/{token}/rahmat', [ClaimFormController::class, 'thanks'])->name('claim.thanks');

Route::middleware(['auth'])->group(function () {
    Route::get('/panel', PanelController::class)->name('panel');
    Route::get('/dashboard', fn () => redirect()->route('panel'))->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('api')->group(function () {
        Route::get('/panel-boot', [ReportController::class, 'panelBoot']);
        Route::get('/meta', [ReportController::class, 'meta']);
        Route::get('/dashboard', [ReportController::class, 'dashboard']);
        Route::get('/reports/sales', [ReportController::class, 'sales']);
        Route::get('/reports/customers', [ReportController::class, 'customers']);
        Route::get('/reports/surveys', [ReportController::class, 'surveys']);
        Route::get('/reports/claims', [ReportController::class, 'claims']);
        Route::get('/reports/quality', [ReportController::class, 'quality']);
        Route::get('/reports/sellers', [ReportController::class, 'sellers']);
        Route::get('/reports/sellers/rating.pdf', [ReportController::class, 'sellersRatingPdf']);
        Route::post('/reports/sellers/rating-send', [ReportController::class, 'sellersRatingSend']);
        Route::get('/reports/system', [ReportController::class, 'system']);
        Route::get('/export', [ReportController::class, 'export']);

        Route::patch('/claims/{claim}', [ClaimController::class, 'update']);

        Route::middleware('panel.page:bot')->group(function () {
            Route::get('/settings/telegram', [SettingsController::class, 'telegramShow']);
            Route::post('/settings/telegram', [SettingsController::class, 'telegramSave']);
            Route::post('/settings/telegram/webhook', [SettingsController::class, 'telegramWebhook']);
        });

        Route::middleware('panel.page:tizim')->group(function () {
            Route::get('/settings/billz', [SettingsController::class, 'billzShow']);
            Route::post('/settings/billz', [SettingsController::class, 'billzSave']);
            Route::post('/settings/billz/test', [SettingsController::class, 'billzTest']);
            Route::post('/settings/billz/sync', [SettingsController::class, 'billzSync']);
        });

        Route::middleware('panel.page:xabarnomalar')->group(function () {
            Route::get('/settings/notifications', [SettingsController::class, 'notifications']);
            Route::post('/settings/notifications', [SettingsController::class, 'notificationsSave']);
        });

        Route::middleware('panel.page:xodimlar')->prefix('staff')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\StaffController::class, 'index']);
            Route::post('/roles', [\App\Http\Controllers\Api\StaffController::class, 'storeRole']);
            Route::patch('/roles/{role}', [\App\Http\Controllers\Api\StaffController::class, 'updateRole']);
            Route::delete('/roles/{role}', [\App\Http\Controllers\Api\StaffController::class, 'destroyRole']);
            Route::post('/employees', [\App\Http\Controllers\Api\StaffController::class, 'storeEmployee']);
            Route::patch('/employees/{user}', [\App\Http\Controllers\Api\StaffController::class, 'updateEmployee']);
            Route::delete('/employees/{user}', [\App\Http\Controllers\Api\StaffController::class, 'destroyEmployee']);
        });
    });
});

require __DIR__.'/auth.php';
