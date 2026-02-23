<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;


/*
|--------------------------------------------------------------------------
| Public Routes (Accessible to everyone)
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', function () {
    return view('home');
})->name('home');

// Main navigation pages
Route::view('/medicine', 'medicine')->name('medicine');
Route::view('/health', 'health')->name('health');
Route::view('/community', 'community')->name('community');
Route::view('/help', 'help')->name('help');

// Footer pages
Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');
Route::view('/terms-of-service', 'terms-of-service')->name('terms.service');
Route::view('/cookie-policy', 'cookie-policy')->name('cookie.policy');
Route::view('/sitemap', 'sitemap')->name('sitemap');

// Other public routes
Route::view('/appointments', 'appointments')->name('appointments');
Route::view('/pharmacy/nearby', 'pharmacy.nearby')->name('pharmacy.nearby');
Route::view('/emergency', 'emergency')->name('emergency');

/*
|--------------------------------------------------------------------------
| REGISTER ROUTES - Accessible to EVERYONE (including logged in users)
|--------------------------------------------------------------------------
*/
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Guest Routes (Only non-logged in users)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Forgot password
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Only logged in users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Notifications
    Route::get('/notifications', function () {
        return view('notifications');
    })->name('notifications');
    
    Route::get('/notification/{id?}', function ($id = null) {
        return view('notification', ['id' => $id]);
    })->name('notification');
    
    // Suggestions
    Route::get('/suggestions', function () {
        return view('suggestions');
    })->name('suggestions');

    // Email verification: send verification notification
    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Health Related Routes
|--------------------------------------------------------------------------
*/
Route::prefix('health')->name('health.')->group(function () {
    // Public health pages
    Route::view('/hospitals', 'health.hospitals')->name('hospitals');
    Route::view('/tips', 'health.tips')->name('tips');
    
    // Protected health pages
    Route::middleware('auth')->group(function () {
        Route::view('/records', 'health.records')->name('records');
        Route::view('/tracking', 'health.tracking')->name('tracking');
        Route::view('/consultation', 'health.consultation')->name('consultation');
        Route::view('/symptoms', 'health.symptoms')->name('symptoms');
        Route::view('/suggestions', 'health.suggestions')->name('suggestions');
    });
});

/*
|--------------------------------------------------------------------------
| Medicine Related Routes
|--------------------------------------------------------------------------
*/
Route::prefix('medicine')->name('medicine.')->group(function () {
    // Public medicine pages
    Route::view('/search', 'medicine.search')->name('search');
    Route::view('/delivery', 'medicine.delivery')->name('delivery');
    
    // Protected medicine pages
    Route::middleware('auth')->group(function () {
        Route::view('/prescriptions', 'medicine.prescriptions')->name('prescriptions');
        Route::view('/reminders', 'medicine.reminders')->name('reminders');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected by admin middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});
