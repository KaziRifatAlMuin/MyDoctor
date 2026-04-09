<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineScheduleController;
use App\Http\Controllers\MedicineReminderController;
use App\Http\Controllers\MedicineLogController;
use App\Http\Controllers\SuggestionsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\PublicHealthController;
use Illuminate\Http\Request;



/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', function () {
    return view('home');
})->name('home');

// Main navigation pages
Route::view('/medicine', 'medicine')->name('medicine');
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

Route::get('/language/{locale}', function (string $locale) {
    if (!in_array($locale, ['en', 'bn'], true)) {
        abort(404);
    }

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

Route::get('/diseases/{disease}', [PublicHealthController::class, 'showDisease'])->name('public.diseases.show');
Route::get('/disease/{disease}', [PublicHealthController::class, 'showDisease'])->name('public.disease.show');
Route::get('/symptoms/{symptom}', [PublicHealthController::class, 'showSymptom'])->name('public.symptoms.show');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Settings (notification preferences)
    Route::get('/profile/setting', [NotificationPreferenceController::class, 'index'])->name('profile.setting');
    Route::put('/profile/setting', [NotificationPreferenceController::class, 'update'])->name('profile.setting.update');
    Route::get('/profile/notifications', function () {
        return redirect()->route('profile.setting');
    })->name('profile.notifications');
    Route::put('/profile/notifications', function () {
        return redirect()->route('profile.setting');
    })->name('profile.notifications.update');
    Route::post('/profile/notifications/toggle-email', [NotificationPreferenceController::class, 'toggleEmail'])->name('profile.notifications.toggle-email');
    Route::post('/profile/notifications/toggle-push', [NotificationPreferenceController::class, 'togglePush'])->name('profile.notifications.toggle-push');

    // Mailbox (internal mailings)
    Route::get('/profile/mailbox', [App\Http\Controllers\MailingController::class, 'inbox'])->name('profile.mailbox');
    Route::get('/profile/mailbox/sent', [App\Http\Controllers\MailingController::class, 'sent'])->name('profile.mailbox.sent');
    Route::get('/profile/mailbox/drafts', [App\Http\Controllers\MailingController::class, 'drafts'])->name('profile.mailbox.drafts');
    Route::get('/profile/mailbox/starred', [App\Http\Controllers\MailingController::class, 'starred'])->name('profile.mailbox.starred');
    Route::get('/profile/mailbox/archived', [App\Http\Controllers\MailingController::class, 'archived'])->name('profile.mailbox.archived');
    Route::get('/profile/mailbox/compose', [App\Http\Controllers\MailingController::class, 'create'])->name('profile.mailbox.compose');
    Route::get('/profile/mailbox/recipients/search', [App\Http\Controllers\MailingController::class, 'searchRecipients'])->name('profile.mailbox.recipients.search');
    Route::get('/profile/mailbox/unread-count', [App\Http\Controllers\MailingController::class, 'unreadCount'])->name('profile.mailbox.unread-count');
    Route::post('/profile/mailbox', [App\Http\Controllers\MailingController::class, 'store'])->name('profile.mailbox.store');
    Route::patch('/profile/mailbox/bulk/status', [App\Http\Controllers\MailingController::class, 'bulkUpdateStatus'])->name('profile.mailbox.bulk-status');
    Route::get('/profile/mailbox/{mailing}', [App\Http\Controllers\MailingController::class, 'show'])->name('profile.mailbox.show');
    Route::patch('/profile/mailbox/{mailing}/status', [App\Http\Controllers\MailingController::class, 'updateStatus'])->name('profile.mailbox.status');
    Route::patch('/profile/mailbox/{mailing}/star', [App\Http\Controllers\MailingController::class, 'toggleStar'])->name('profile.mailbox.star');
    Route::delete('/profile/mailbox/{mailing}', [App\Http\Controllers\MailingController::class, 'destroy'])->name('profile.mailbox.destroy');
    
    // Health
    Route::get('/health', [HealthController::class, 'index'])->name('health');
    Route::post('/health/metric', [HealthController::class, 'storeMetric'])->name('health.metric.store');
    Route::post('/health/symptom', [HealthController::class, 'storeSymptom'])->name('health.symptom.store');
    Route::post('/health/disease', [HealthController::class, 'storeDisease'])->name('health.disease.store');
    Route::post('/health/upload', [HealthController::class, 'storeUpload'])->name('health.upload.store');
    Route::put('/health/metric/{healthMetric}', [HealthController::class, 'updateMetric'])->name('health.metric.update');
    Route::get('/health/metric/{healthMetric}', function (\App\Models\HealthMetric $healthMetric) {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.users.show', $healthMetric->user_id);
        }
        return redirect()->route('users.show', $healthMetric->user_id);
    })->name('health.metric.view');
    Route::put('/health/symptom/{symptom}', [HealthController::class, 'updateSymptom'])->name('health.symptom.update');
    // Friendly GET redirect: visiting a symptom URL should return to the user's profile
    Route::get('/health/symptom/{symptom}', function (\App\Models\UserSymptom $symptom) {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.users.show', $symptom->user_id);
        }
        return redirect()->route('users.show', $symptom->user_id);
    })->name('health.symptom.view');
    Route::put('/health/disease/{userDisease}', [HealthController::class, 'updateDisease'])->name('health.disease.update');
    Route::get('/health/disease/{userDisease}', function (\App\Models\UserDisease $userDisease) {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.users.show', $userDisease->user_id);
        }
        return redirect()->route('users.show', $userDisease->user_id);
    })->name('health.disease.view');
    Route::put('/health/upload/{upload}', [HealthController::class, 'updateUpload'])->name('health.upload.update');
    Route::get('/health/upload/{upload}', function (\App\Models\Upload $upload) {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.users.show', $upload->user_id);
        }
        return redirect()->route('users.show', $upload->user_id);
    })->name('health.upload.view');
    Route::delete('/health/metric/{healthMetric}', [HealthController::class, 'destroyMetric'])->name('health.metric.destroy');
    Route::delete('/health/symptom/{symptom}', [HealthController::class, 'destroySymptom'])->name('health.symptom.destroy');
    Route::delete('/health/disease/{userDisease}', [HealthController::class, 'destroyDisease'])->name('health.disease.destroy');
    Route::delete('/health/upload/{upload}', [HealthController::class, 'destroyUpload'])->name('health.upload.destroy');
    
  /*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}/delete', [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
    Route::delete('/clear-all', [App\Http\Controllers\NotificationController::class, 'clearAll'])->name('clear-all');
});




    // Suggestions
    Route::get('/suggestions', [SuggestionsController::class, 'index'])->name('suggestions');

    // AI Chatbot
    Route::post('/chatbot/message', [AiChatController::class, 'message'])->name('chatbot.message');

    // Email verification
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
        try {
            $user = auth()->user();
            $user->updatePushSubscription(
                $request->endpoint,
                $request->keys['p256dh'],
                $request->keys['auth']
            );
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Push subscription error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save subscription'], 500);
        }
    });
    
    Route::post('/push-subscriptions/delete', function (Request $request) {
        try {
            $user = auth()->user();
            $user->deletePushSubscription($request->endpoint);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Push subscription delete error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete subscription'], 500);
        }
    });
});

/*
|--------------------------------------------------------------------------
| Health Related Routes
|--------------------------------------------------------------------------
*/
Route::prefix('health')->name('health.')->group(function () {
    Route::view('/hospitals', 'health.hospitals')->name('hospitals');
    Route::view('/tips', 'health.tips')->name('tips');
    
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
| Community Routes - FULLY WORKING WITH MODAL DYNAMIC UPDATES
|--------------------------------------------------------------------------
*/
Route::prefix('community')->name('community.')->group(function () {
    // Page routes (return HTML)
    Route::get('/landing', [CommunityController::class, 'landing'])->name('landing');
    Route::get('/forum', [CommunityController::class, 'index'])->name('index');
    Route::get('/posts/starred', [CommunityController::class, 'starredPosts'])->name('posts.starred');
    Route::get('/posts/pending', [CommunityController::class, 'pendingPosts'])->name('posts.pending');
    Route::get('/posts/{post}', [CommunityController::class, 'showPost'])->name('posts.show');
    
    // MODAL POST - CRITICAL FOR DYNAMIC RELOADS
    Route::get('/modal-post/{post}', [CommunityController::class, 'modalPost'])->name('modal.post');
    
    // API routes (JSON responses)
    Route::get('/posts/load', [CommunityController::class, 'loadPosts'])->name('posts.load');
    Route::get('/posts/{post}/comments', [CommunityController::class, 'loadComments'])->name('posts.comments.load');
    Route::get('/posts/{post}/comments/more', [CommunityController::class, 'loadMoreComments'])->name('posts.comments.more');
    
    // Post CRUD - MATCHES YOUR JAVASCRIPT EXACTLY
    Route::post('/posts', [CommunityController::class, 'storePost'])->name('posts.store');
    Route::post('/posts/{post}/update', [CommunityController::class, 'updatePost'])->name('posts.update');  // ← POST not PATCH
    Route::post('/posts/{post}/delete', [CommunityController::class, 'destroyPost'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [CommunityController::class, 'togglePostLike'])->name('posts.like');
    Route::post('/posts/{post}/star', [CommunityController::class, 'togglePostStar'])->name('posts.star');
    Route::post('/posts/{post}/report', [CommunityController::class, 'reportPost'])->name('posts.report');
    Route::post('/posts/{post}/approve', [CommunityController::class, 'approvePost'])->name('posts.approve');
    
    // User details for modals
    Route::get('/user/{userId}', [CommunityController::class, 'getUserDetails'])->name('user.details');
    
    // Comment CRUD - MATCHES JAVASCRIPT
    Route::post('/posts/{post}/comments', [CommunityController::class, 'storeComment'])->name('comments.store');
    Route::post('/comments/{comment}/update', [CommunityController::class, 'updateComment'])->name('comments.update');
    Route::post('/comments/{comment}/delete', [CommunityController::class, 'destroyComment'])->name('comments.destroy');
    Route::post('/comments/{comment}/like', [CommunityController::class, 'toggleCommentLike'])->name('comments.like');
});

/*
|--------------------------------------------------------------------------
| Medicine Routes
|--------------------------------------------------------------------------
*/
Route::prefix('medicine')->name('medicine.')->middleware('auth')->group(function () {
    Route::view('/', 'medicine')->name('index');
    Route::get('/my-medicines', [MedicineController::class, 'index'])->name('my-medicines');
    Route::get('/add', [MedicineController::class, 'create'])->name('add');
    Route::post('/store', [MedicineController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [MedicineController::class, 'edit'])->name('edit');
    Route::put('/{id}', [MedicineController::class, 'update'])->name('update');
    Route::delete('/{id}', [MedicineController::class, 'destroy'])->name('destroy');
    
    Route::get('/schedules', [MedicineScheduleController::class, 'index'])->name('schedules');
    Route::get('/schedules/create', [MedicineScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [MedicineScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{id}/edit', [MedicineScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{id}', [MedicineScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{id}', [MedicineScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::post('/schedules/{id}/generate-reminders', [MedicineScheduleController::class, 'generateReminders'])->name('schedules.generate-reminders');
    
    Route::get('/reminders', [MedicineReminderController::class, 'index'])->name('reminders');
    Route::post('/reminders/{id}/taken', [MedicineReminderController::class, 'markTaken'])->name('reminders.taken');
    Route::post('/reminders/{id}/missed', [MedicineReminderController::class, 'markMissed'])->name('reminders.missed');
    Route::post('/reminders/{id}/taken-from-notification', [MedicineReminderController::class, 'markTakenFromNotification'])->name('reminders.taken-from-notification');
    Route::post('/reminders/{id}/snooze', [MedicineReminderController::class, 'snooze'])->name('reminders.snooze');
    Route::post('/reminders/mark-multiple-taken', [MedicineReminderController::class, 'markMultipleTaken'])->name('reminders.mark-multiple-taken');
    
    Route::get('/logs', [MedicineLogController::class, 'index'])->name('logs');
    Route::get('/logs/export', [MedicineLogController::class, 'export'])->name('logs.export');
    
    Route::withoutMiddleware('auth')->group(function () {
        Route::view('/search', 'medicine.search')->name('search');
        Route::view('/delivery', 'medicine.delivery')->name('delivery');
    });
    
    Route::view('/prescriptions', 'medicine.prescriptions')->name('prescriptions');
});

// User routes (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
});

// Public user profile
Route::get('/user/{user}', [App\Http\Controllers\UserController::class, 'publicShow'])->name('users.show');

// Admin user update route
Route::middleware(['auth', 'admin'])->patch('/user/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/user/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}', [App\Http\Controllers\AdminDashboardController::class, 'updateUser'])->name('users.update');
    
    // Future admin routes
    Route::get('/medical', [App\Http\Controllers\AdminDashboardController::class, 'medical'])->name('medical.index');
    Route::get('/analytics', [App\Http\Controllers\AdminDashboardController::class, 'analytics'])->name('analytics');
    Route::get('/settings', [App\Http\Controllers\AdminDashboardController::class, 'settings'])->name('settings');
});

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('api/users')->group(function () {
    Route::get('{id}', function ($id) {
        $user = \App\Models\User::findOrFail($id);
        return response()->json($user->only([
            'id', 'name', 'email', 'phone', 'occupation', 'blood_group', 'date_of_birth', 'picture', 'role', 'email_verified_at'
        ]));
    });
    
    Route::get('{id}/medical', function ($id) {
        $user = \App\Models\User::with(['medicines.activeSchedule', 'userDiseases.disease', 'healthMetrics'])->findOrFail($id);
        return response()->json([
            'medicines' => $user->medicines->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->medicine_name,
                'type' => $m->type,
                'dose' => trim(collect([$m->value_per_dose, $m->unit])->filter()->join(' ')),
                'rule' => $m->rule ? str_replace('_', ' ', $m->rule) : null,
                'frequency' => $m->activeSchedule?->frequency_per_day,
                'start_date' => $m->activeSchedule?->start_date?->format('Y-m-d')
            ]),
            'diseases' => $user->userDiseases->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->disease->disease_name ?? 'Unknown disease',
                'status' => $d->status,
                'diagnosed_at' => $d->diagnosed_at?->format('Y-m-d'),
                'notes' => $d->notes,
            ]),
            'metrics' => $user->healthMetrics->map(fn($metric) => [
                'id' => $metric->id,
                'type' => $metric->metric_type,
                'value' => $metric->value,
                'recorded_at' => $metric->recorded_at?->format('Y-m-d H:i'),
            ]),
        ]);
    });
});
