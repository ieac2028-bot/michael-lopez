<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/attorneys', [PageController::class, 'attorneys'])->name('attorneys');
Route::get('/practice-areas', [PageController::class, 'practiceAreas'])->name('practice-areas');
Route::get('/the-firm', [PageController::class, 'firm'])->name('firm');
Route::get('/about-us', [PageController::class, 'aboutUs'])->name('about-us');

Route::get('/client-portal', [ClientPortalController::class, 'show'])->name('client-portal');
Route::post('/client-portal/lookup', [ClientPortalController::class, 'lookup'])->name('client-portal.lookup');

// General questions — low commitment, no practice area required.
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Actual consultation booking — practice area, phone, preferred date/time required.
Route::get('/book-consultation', [ConsultationController::class, 'show'])->name('consultation');
Route::post('/book-consultation', [ConsultationController::class, 'store'])->name('consultation.store');

Route::post('/api/chat', [ChatbotController::class, 'respond'])->name('chat.respond');
