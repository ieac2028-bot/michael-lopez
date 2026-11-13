<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AdminAccountController;
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
use App\Http\Middleware\LocalizeChatbotResponse;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/attorneys', [PageController::class, 'attorneys'])->name('attorneys');
Route::get('/practice-areas', [PageController::class, 'practiceAreas'])->name('practice-areas');
Route::get('/the-firm', [PageController::class, 'firm'])->name('firm');
Route::get('/legal-insights', [PageController::class, 'legalInsights'])->name('legal-insights');
Route::get('/about-us', [PageController::class, 'aboutUs'])->name('about-us');

Route::get('/client-portal', [ClientPortalController::class, 'show'])
    ->middleware(['auth', 'can:access-client-portal'])
    ->name('client-portal');
// General questions — low commitment, no practice area required.
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Actual consultation booking — practice area, phone, preferred date/time required.
Route::middleware(['auth', 'can:access-client-portal'])->group(function () {
    Route::get('/book-consultation', [ConsultationController::class, 'show'])->name('consultation');
    Route::get('/book-consultation/admin-status', [ConsultationController::class, 'adminStatus'])->name('consultation.admin-status');
    Route::get('/book-consultation/new', [ConsultationController::class, 'startNew'])->name('consultation.new');
    Route::get('/book-consultation/{inquiry}', [ConsultationController::class, 'open'])->name('consultation.open');
    Route::post('/book-consultation', [ConsultationController::class, 'store'])->name('consultation.store');
    Route::post('/book-consultation/{inquiry}/messages', [ConsultationController::class, 'sendMessage'])->name('consultation.messages.store');
    Route::get('/book-consultation/{inquiry}/messages/poll', [ConsultationController::class, 'pollMessages'])->name('consultation.messages.poll');
    Route::post('/book-consultation/{inquiry}/payment/sent', [ConsultationController::class, 'markPaymentSent'])->name('consultation.payment.sent');
    Route::post('/book-consultation/{inquiry}/payment/cancel', [ConsultationController::class, 'cancelPayment'])->name('consultation.payment.cancel');
    Route::post('/book-consultation/{inquiry}/payment/refund-review', [ConsultationController::class, 'requestRefundReview'])->name('consultation.payment.refund-review');
});

Route::post('/api/chat', [ChatbotController::class, 'respond'])->middleware(['throttle:30,1', LocalizeChatbotResponse::class])->name('chat.respond');
Route::post('/api/chat/translate', [ChatbotController::class, 'translate'])->middleware('throttle:30,1')->name('chat.translate');

Route::get('/login', AccessController::class)->name('login');
Route::get('/client/login', [ClientAuthController::class, 'showLogin'])->name('client.login');
Route::post('/client/login', [ClientAuthController::class, 'login'])
    ->middleware('throttle:client-login')
    ->name('client.login.store');
Route::get('/client/register', [ClientAuthController::class, 'showRegistration'])->name('client.register');
Route::post('/client/register', [ClientAuthController::class, 'register'])
    ->middleware('throttle:client-register')
    ->name('client.register.store');
Route::get('/client/recovery-codes', [ClientAuthController::class, 'showRecoveryCodes'])
    ->middleware(['auth', 'can:access-client-portal'])
    ->name('client.recovery-codes');
Route::post('/client/logout', [ClientAuthController::class, 'logout'])
    ->middleware(['auth', 'can:access-client-portal'])
    ->name('client.logout');

Route::get('/attorney/login', [AttorneyAuthController::class, 'showLogin'])->name('attorney.login');
Route::post('/attorney/login', [AttorneyAuthController::class, 'login'])
    ->middleware('throttle:attorney-login')
    ->name('attorney.login.store');
Route::middleware(['auth', 'can:access-attorney-portal'])->prefix('attorney-portal')->name('attorney.')->group(function () {
    Route::get('/', [AttorneyAuthController::class, 'dashboard'])->name('dashboard');
    Route::patch('/profile', [AttorneyAuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/matching-availability', [AttorneyAuthController::class, 'updateMatchingAvailability'])->name('matching-availability.update');
    Route::patch('/cases/{caseRecord}', [AttorneyAuthController::class, 'updateCase'])->name('cases.update');
    Route::post('/inquiries/{inquiry}/respond', [AttorneyAuthController::class, 'respondToInquiry'])->name('inquiries.respond');
    Route::post('/logout', [AttorneyAuthController::class, 'logout'])->name('logout');
});

Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendRecoveryCode'])
    ->middleware('throttle:password-email')
    ->name('password.email');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:password-reset')
    ->name('password.update');

Route::get('/admin/login', [AdminAuthController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:admin-login')->name('admin.login.store');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->middleware(['auth', 'can:manage-gpmo'])
    ->name('admin.logout');

Route::middleware(['auth', 'can:manage-gpmo'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/inquiries/poll', [AdminController::class, 'pollInquiryInbox'])->name('inquiries.poll');
    Route::patch('/inquiries/{inquiry}', [AdminController::class, 'updateInquiry'])->name('inquiries.update');
    Route::post('/inquiries/{inquiry}/messages', [AdminController::class, 'sendInquiryMessage'])->name('inquiries.messages.store');
    Route::get('/inquiries/{inquiry}/messages/poll', [AdminController::class, 'pollInquiryMessages'])->name('inquiries.messages.poll');
    Route::post('/inquiries/{inquiry}/payment/confirm', [AdminController::class, 'confirmPaymentDetails'])->name('inquiries.payment.confirm');
    Route::post('/inquiries/{inquiry}/payment/verify-start', [AdminController::class, 'startPaymentVerification'])->name('inquiries.payment.verify-start');
    Route::post('/inquiries/{inquiry}/payment/verify', [AdminController::class, 'verifyPayment'])->name('inquiries.payment.verify');
    Route::post('/inquiries/{inquiry}/intake', [AdminController::class, 'updateIntake'])->name('inquiries.intake.update');
    Route::post('/inquiries/{inquiry}/match', [AdminController::class, 'prepareMatches'])->name('inquiries.match');
    Route::patch('/cases/{caseRecord}', [AdminController::class, 'updateCase'])->name('cases.update');
    Route::delete('/cases/{caseRecord}', fn (string $caseRecord) => abort(404))->name('cases.destroy');
    Route::patch('/attorneys/{attorney}', [AdminController::class, 'updateAttorney'])->name('attorneys.update');
    Route::post('/attorneys/{attorney}/account', [AttorneyAccountController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('attorneys.account.store');

    // Administrator accounts: only existing, active admins reach these (see the group's can:manage-gpmo).
    Route::post('/admins', [AdminAccountController::class, 'store'])->middleware('throttle:10,1')->name('admins.store');
    Route::post('/admins/{admin}/deactivate', [AdminAccountController::class, 'deactivate'])->name('admins.deactivate');
    Route::post('/admins/{admin}/reactivate', [AdminAccountController::class, 'reactivate'])->name('admins.reactivate');
    Route::post('/admins/{admin}/invite', [AdminAccountController::class, 'resendInvite'])->middleware('throttle:10,1')->name('admins.invite.resend');
});
