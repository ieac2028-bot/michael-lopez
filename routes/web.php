<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttorneyAccountController;
use App\Http\Controllers\AttorneyAuthController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/attorneys', [PageController::class, 'attorneys'])->name('attorneys');
Route::get('/practice-areas', [PageController::class, 'practiceAreas'])->name('practice-areas');
Route::get('/the-firm', [PageController::class, 'firm'])->name('firm');
Route::get('/legal-insights', [PageController::class, 'legalInsights'])->name('legal-insights');
Route::get('/about-us', [PageController::class, 'aboutUs'])->name('about-us');

Route::get('/client-portal', [ClientPortalController::class, 'show'])
    ->middleware(['auth', 'can:access-client-portal', 'verified'])
    ->name('client-portal');
Route::post('/client-portal/lookup', [ClientPortalController::class, 'lookup'])
    ->middleware(['auth', 'can:access-client-portal', 'verified', 'throttle:client-portal-lookup'])
    ->name('client-portal.lookup');

// General questions — low commitment, no practice area required.
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Actual consultation booking — practice area, phone, preferred date/time required.
Route::get('/book-consultation', [ConsultationController::class, 'show'])->name('consultation');
Route::post('/book-consultation', [ConsultationController::class, 'store'])->name('consultation.store');

Route::post('/api/chat', [ChatbotController::class, 'respond'])->name('chat.respond');

Route::get('/login', AccessController::class)->name('login');
Route::get('/client/login', [ClientAuthController::class, 'showLogin'])->name('client.login');
Route::post('/client/login', [ClientAuthController::class, 'login'])
    ->middleware('throttle:client-login')
    ->name('client.login.store');
Route::get('/client/register', [ClientAuthController::class, 'showRegistration'])->name('client.register');
Route::post('/client/register', [ClientAuthController::class, 'register'])
    ->middleware('throttle:client-register')
    ->name('client.register.store');
Route::post('/client/logout', [ClientAuthController::class, 'logout'])
    ->middleware(['auth', 'can:access-client-portal'])
    ->name('client.logout');

Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware(['auth', 'can:verify-safalaw-account'])
    ->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [ClientAuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'can:verify-safalaw-account', 'signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [ClientAuthController::class, 'resendVerification'])
    ->middleware(['auth', 'can:verify-safalaw-account', 'throttle:6,1'])
    ->name('verification.send');

Route::get('/attorney/login', [AttorneyAuthController::class, 'showLogin'])->name('attorney.login');
Route::post('/attorney/login', [AttorneyAuthController::class, 'login'])
    ->middleware('throttle:attorney-login')
    ->name('attorney.login.store');
Route::middleware(['auth', 'can:access-attorney-portal'])->prefix('attorney-portal')->name('attorney.')->group(function () {
    Route::get('/', [AttorneyAuthController::class, 'dashboard'])->middleware('verified')->name('dashboard');
    Route::patch('/cases/{caseRecord}', [AttorneyAuthController::class, 'updateCase'])->middleware('verified')->name('cases.update');
    Route::post('/logout', [AttorneyAuthController::class, 'logout'])->name('logout');
});

Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
    ->middleware('throttle:password-email')
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:password-reset')
    ->name('password.update');

Route::get('/admin/login', [AdminAuthController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:admin-login')->name('admin.login.store');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->middleware(['auth', 'can:manage-safalaw'])
    ->name('admin.logout');

Route::middleware(['auth', 'can:manage-safalaw'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::patch('/inquiries/{inquiry}', [AdminController::class, 'updateInquiry'])->name('inquiries.update');
    Route::post('/inquiries/{inquiry}/accept', [AdminController::class, 'acceptInquiry'])->name('inquiries.accept');
    Route::patch('/cases/{caseRecord}', [AdminController::class, 'updateCase'])->name('cases.update');
    Route::delete('/cases/{caseRecord}', fn (string $caseRecord) => abort(404))->name('cases.destroy');
    Route::patch('/attorneys/{attorney}', [AdminController::class, 'updateAttorney'])->name('attorneys.update');
    Route::post('/attorneys/{attorney}/account', [AttorneyAccountController::class, 'store'])->name('attorneys.account.store');
});
