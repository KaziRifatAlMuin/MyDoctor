<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

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
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Notification Preferences
    Route::get('/profile/notifications', [App\Http\Controllers\NotificationPreferenceController::class, 'index'])
        ->name('profile.notifications');
    Route::put('/profile/notifications', [App\Http\Controllers\NotificationPreferenceController::class, 'update'])
        ->name('profile.notifications.update');
    Route::post('/profile/notifications/toggle-email', [App\Http\Controllers\NotificationPreferenceController::class, 'toggleEmail'])
        ->name('profile.notifications.toggle-email');
    Route::post('/profile/notifications/toggle-push', [App\Http\Controllers\NotificationPreferenceController::class, 'togglePush'])
        ->name('profile.notifications.toggle-push');
    
    // Health Dashboard
    Route::get('/health', [HealthController::class, 'index'])->name('health');
    
    // Health CRUD operations — Store
    Route::post('/health/metric', [HealthController::class, 'storeMetric'])->name('health.metric.store');
    Route::post('/health/symptom', [HealthController::class, 'storeSymptom'])->name('health.symptom.store');
    Route::post('/health/disease', [HealthController::class, 'storeDisease'])->name('health.disease.store');
    Route::post('/health/upload', [HealthController::class, 'storeUpload'])->name('health.upload.store');

    // Health CRUD operations — Update
    Route::put('/health/metric/{healthMetric}', [HealthController::class, 'updateMetric'])->name('health.metric.update');
    Route::put('/health/symptom/{symptom}', [HealthController::class, 'updateSymptom'])->name('health.symptom.update');
    Route::put('/health/disease/{userDisease}', [HealthController::class, 'updateDisease'])->name('health.disease.update');
    Route::put('/health/upload/{upload}', [HealthController::class, 'updateUpload'])->name('health.upload.update');

    // Health CRUD operations — Delete
    Route::delete('/health/metric/{healthMetric}', [HealthController::class, 'destroyMetric'])->name('health.metric.destroy');
    Route::delete('/health/symptom/{symptom}', [HealthController::class, 'destroySymptom'])->name('health.symptom.destroy');
    Route::delete('/health/disease/{userDisease}', [HealthController::class, 'destroyDisease'])->name('health.disease.destroy');
    Route::delete('/health/upload/{upload}', [HealthController::class, 'destroyUpload'])->name('health.upload.destroy');
    
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
| Push Subscription Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/push-subscriptions', function (Request $request) {
        $user = auth()->user();
        $user->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth']
        );
        return response()->json(['success' => true]);
    });
    
    Route::post('/push-subscriptions/delete', function (Request $request) {
        $user = auth()->user();
        $user->deletePushSubscription($request->endpoint);
        return response()->json(['success' => true]);
    });
});

/*
|--------------------------------------------------------------------------
| Health Related Routes - Additional Pages
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
| Medicine Related Routes - COMPLETE (from first developer)
|--------------------------------------------------------------------------
*/
Route::prefix('medicine')->name('medicine.')->middleware('auth')->group(function () {
    // Main medicine page
    Route::view('/', 'medicine')->name('index');
    
    // Medicine management
    Route::get('/my-medicines', [App\Http\Controllers\MedicineController::class, 'index'])->name('my-medicines');
    Route::get('/add', [App\Http\Controllers\MedicineController::class, 'create'])->name('add');
    Route::post('/store', [App\Http\Controllers\MedicineController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\MedicineController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\MedicineController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\MedicineController::class, 'destroy'])->name('destroy');
    
    // Schedule management
    Route::get('/schedules', [App\Http\Controllers\MedicineScheduleController::class, 'index'])->name('schedules');
    Route::get('/schedules/create', [App\Http\Controllers\MedicineScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [App\Http\Controllers\MedicineScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{id}/edit', [App\Http\Controllers\MedicineScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{id}', [App\Http\Controllers\MedicineScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{id}', [App\Http\Controllers\MedicineScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::post('/schedules/{id}/generate-reminders', [App\Http\Controllers\MedicineScheduleController::class, 'generateReminders'])->name('schedules.generate-reminders');
    
    // Reminder management
    Route::get('/reminders', [App\Http\Controllers\MedicineReminderController::class, 'index'])->name('reminders');
    Route::post('/reminders/{id}/taken', [App\Http\Controllers\MedicineReminderController::class, 'markTaken'])->name('reminders.taken');
    Route::post('/reminders/{id}/missed', [App\Http\Controllers\MedicineReminderController::class, 'markMissed'])->name('reminders.missed');
    Route::post('/reminders/{id}/taken-from-notification', [App\Http\Controllers\MedicineReminderController::class, 'markTakenFromNotification'])->name('reminders.taken-from-notification');
    Route::post('/reminders/{id}/snooze', [App\Http\Controllers\MedicineReminderController::class, 'snooze'])->name('reminders.snooze');
    Route::post('/reminders/mark-multiple-taken', [App\Http\Controllers\MedicineReminderController::class, 'markMultipleTaken'])->name('reminders.mark-multiple-taken');
    
    // Logs and reports
    Route::get('/logs', [App\Http\Controllers\MedicineLogController::class, 'index'])->name('logs');
    Route::get('/logs/export', [App\Http\Controllers\MedicineLogController::class, 'export'])->name('logs.export');
    
    // Public medicine pages (from second developer)
    Route::withoutMiddleware('auth')->group(function () {
        Route::view('/search', 'medicine.search')->name('search');
        Route::view('/delivery', 'medicine.delivery')->name('delivery');
    });
    
    // Protected medicine pages (from second developer)
    Route::view('/prescriptions', 'medicine.prescriptions')->name('prescriptions');
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