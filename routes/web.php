<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FormAttendeeController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\ResponseController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SignatureController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\PublicFormController;
use App\Http\Controllers\Public\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Form & Receipt Routes (No login required)
Route::prefix('f')->group(function () {
    Route::get('/{slug}', [PublicFormController::class, 'show'])->name('public.form.show');
    Route::get('/{slug}/lookup', [PublicFormController::class, 'lookup'])
        ->middleware('throttle:60,1')
        ->name('public.form.lookup');
    Route::post('/{slug}/submit', [PublicFormController::class, 'submit'])
        ->middleware('throttle:20,1')
        ->name('public.form.submit');
});

Route::get('/receipt/{receiptCode}', [ReceiptController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('receipt.show');

// Admin & Operator Protected Routes
Route::prefix('admin')->middleware(['auth'])->as('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Events
    Route::resource('events', EventController::class);

    // Forms
    Route::resource('forms', FormController::class);
    Route::post('/forms/upload-header-image', [FormController::class, 'uploadHeaderImage'])->name('forms.upload_header');
    Route::get('/forms/{form}/builder', [FormController::class, 'builder'])->name('forms.builder');
    Route::post('/forms/{form}/publish', [FormController::class, 'publish'])->name('forms.publish');
    Route::post('/forms/{form}/close', [FormController::class, 'close'])->name('forms.close');
    Route::post('/forms/{form}/reopen', [FormController::class, 'reopen'])->name('forms.reopen');
    Route::get('/forms/{form}/responses', [ResponseController::class, 'index'])->name('forms.responses');
    Route::post('/forms/{form}/exports', [ExportController::class, 'export'])->name('forms.export');

    // Pre-registered Attendees (Lookup Data)
    Route::get('/forms/{form}/attendees', [FormAttendeeController::class, 'index'])->name('forms.attendees.index');
    Route::post('/forms/{form}/attendees', [FormAttendeeController::class, 'store'])->name('forms.attendees.store');
    Route::put('/forms/{form}/attendees/{attendee}', [FormAttendeeController::class, 'update'])->name('forms.attendees.update');
    Route::delete('/forms/{form}/attendees/{attendee}', [FormAttendeeController::class, 'destroySingle'])->name('forms.attendees.destroy_single');
    Route::post('/forms/{form}/attendees/import', [FormAttendeeController::class, 'import'])->name('forms.attendees.import');
    Route::get('/forms/{form}/attendees/template', [FormAttendeeController::class, 'template'])->name('forms.attendees.template');
    Route::delete('/forms/{form}/attendees', [FormAttendeeController::class, 'destroy'])->name('forms.attendees.destroy');

    // Responses
    Route::get('/responses', [ResponseController::class, 'index'])->name('responses.index');
    Route::get('/responses/{response}', [ResponseController::class, 'show'])->name('responses.show');
    Route::get('/responses/{response}/photo/{fieldKey}', [ResponseController::class, 'photo'])->name('responses.photo');
    Route::post('/responses/{response}/verify', [ResponseController::class, 'verify'])->name('responses.verify');

    // Secure private signature access
    Route::get('/signatures/{signature}', [SignatureController::class, 'show'])->name('signatures.show');

    // Audit logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    // Application & Organization Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/organization', [SettingController::class, 'updateOrganization'])->name('settings.organization');
    Route::put('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::delete('/settings/logo', [SettingController::class, 'removeLogo'])->name('settings.logo.remove');
});
