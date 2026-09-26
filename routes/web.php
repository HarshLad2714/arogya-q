<?php

use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClinicAdmin\PanelController;
use App\Http\Controllers\ClinicDirectoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\Doctor\ConsoleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LiveQueueController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Patient\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reception\DeskController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\Webhook\QueueWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');

Route::get('/clinics', [ClinicDirectoryController::class, 'index'])->name('clinics.index');
Route::get('/clinics/{clinic}', [ClinicDirectoryController::class, 'show'])->name('clinics.show');

Route::get('/book/{doctor}', [BookingController::class, 'create'])->name('booking.create');
Route::post('/book/{doctor}', [BookingController::class, 'store'])->middleware('auth')->name('booking.store');

Route::get('/track/{token}', [TrackerController::class, 'show'])->name('track.show');
Route::get('/display/{clinic}', [DisplayController::class, 'show'])->name('display.show');

Route::middleware('throttle:180,1')->group(function () {
    Route::get('/live/doctor/{doctor}', [LiveQueueController::class, 'doctor'])->name('live.doctor');
    Route::get('/live/token/{token}', [LiveQueueController::class, 'token'])->name('live.token');
    Route::get('/live/clinic/{clinic}', [LiveQueueController::class, 'clinic'])->name('live.clinic');
});

Route::post('/webhooks/queue', QueueWebhookController::class)->name('webhooks.queue');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/login/otp', [AuthController::class, 'sendLoginOtp'])->middleware('throttle:5,1')->name('login.otp');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/register/clinic', [AuthController::class, 'showClinicRegister'])->name('register.clinic');
    Route::post('/register/clinic', [AuthController::class, 'registerClinic'])->middleware('throttle:10,1');
    Route::get('/otp', [AuthController::class, 'showOtp'])->name('otp.show');
    Route::post('/otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetOtp'])->name('password.email');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:patient')->prefix('me')->name('patient.')->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/visits', [PortalController::class, 'history'])->name('history');
        Route::get('/payments', [PortalController::class, 'payments'])->name('payments');
        Route::post('/payments/{payment}/pay', [PortalController::class, 'pay'])->name('payments.pay');
        Route::get('/prescriptions/{prescription}', [PortalController::class, 'prescription'])->name('prescriptions.show');
        Route::post('/visits/{token}/cancel', [PortalController::class, 'cancel'])->name('tokens.cancel');
        Route::post('/visits/{token}/reschedule', [PortalController::class, 'reschedule'])->name('tokens.reschedule');
        Route::post('/visits/{token}/review', [PortalController::class, 'review'])->name('reviews.store');
    });

    Route::middleware('role:doctor')->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/', [ConsoleController::class, 'dashboard'])->name('dashboard');
        Route::post('/next', [ConsoleController::class, 'next'])->name('next');
        Route::post('/tokens/{token}/complete', [ConsoleController::class, 'complete'])->name('complete');
        Route::post('/tokens/{token}/noshow', [ConsoleController::class, 'noShow'])->name('noshow');
        Route::get('/tokens/{token}/prescription', [ConsoleController::class, 'prescriptionForm'])->name('prescription');
        Route::post('/tokens/{token}/prescription', [ConsoleController::class, 'prescriptionStore'])->name('prescription.store');
        Route::post('/leave', [ConsoleController::class, 'leave'])->name('leave');
    });

    Route::middleware('role:receptionist')->prefix('desk')->name('desk.')->group(function () {
        Route::get('/', [DeskController::class, 'dashboard'])->name('dashboard');
        Route::post('/walk-in', [DeskController::class, 'walkIn'])->name('walkin');
        Route::post('/tokens/{token}/arrive', [DeskController::class, 'arrive'])->name('arrive');
        Route::post('/tokens/{token}/cash', [DeskController::class, 'cash'])->name('cash');
        Route::post('/tokens/{token}/noshow', [DeskController::class, 'noShow'])->name('noshow');
    });

    Route::middleware('role:clinic_admin')->prefix('clinic')->name('clinic.')->group(function () {
        Route::get('/', [PanelController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [PanelController::class, 'editProfile'])->name('profile');
        Route::put('/profile', [PanelController::class, 'updateProfile'])->name('profile.update');
        Route::get('/doctors', [PanelController::class, 'doctors'])->name('doctors');
        Route::get('/doctors/create', [PanelController::class, 'createDoctor'])->name('doctors.create');
        Route::post('/doctors', [PanelController::class, 'storeDoctor'])->name('doctors.store');
        Route::get('/doctors/{doctor}/edit', [PanelController::class, 'editDoctor'])->name('doctors.edit');
        Route::put('/doctors/{doctor}', [PanelController::class, 'updateDoctor'])->name('doctors.update');
        Route::post('/doctors/{doctor}/leave', [PanelController::class, 'storeLeave'])->name('leaves.store');
        Route::delete('/leaves/{leave}', [PanelController::class, 'destroyLeave'])->name('leaves.destroy');
        Route::get('/reviews', [PanelController::class, 'reviews'])->name('reviews');
        Route::post('/reviews/{review}/respond', [PanelController::class, 'respond'])->name('reviews.respond');
        Route::get('/reports', [PanelController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [PanelController::class, 'export'])->name('reports.export');
        Route::post('/reception', [PanelController::class, 'storeReceptionist'])->name('reception.store');
    });

    Route::middleware('role:super_admin')->prefix('platform')->name('platform.')->group(function () {
        Route::get('/', [PlatformController::class, 'dashboard'])->name('dashboard');
        Route::get('/clinics', [PlatformController::class, 'clinics'])->name('clinics');
        Route::post('/clinics/{clinic}/approve', [PlatformController::class, 'approve'])->name('clinics.approve');
        Route::post('/clinics/{clinic}/reject', [PlatformController::class, 'reject'])->name('clinics.reject');
    });
});
