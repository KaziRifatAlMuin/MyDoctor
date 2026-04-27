<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineScheduleController;
use App\Http\Controllers\MedicineReminderController;
use App\Http\Controllers\MedicineLogController;
use App\Http\Controllers\SuggestionsController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\PublicHealthController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\ProfileActivityLogController;
use App\Http\Controllers\GeoController;
use App\Models\Disease;
use App\Models\MedicineReminder;
use App\Models\Post;
use App\Models\Symptom;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', function () {
    if (auth()->check() && auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    $totalReminders = MedicineReminder::query()->count();
    $takenReminders = MedicineReminder::query()->where('status', 'taken')->count();

    $homeStats = [
        'active_users' => User::query()->where('role', '!=', 'admin')->count(),
        'approved_posts' => Post::query()->where('is_approved', true)->count(),
        'total_uploads' => Upload::query()->count(),
        'health_catalog' => Disease::query()->count() + Symptom::query()->count(),
        'reminder_adherence' => $totalReminders > 0
            ? (int) round(($takenReminders / $totalReminders) * 100)
            : 0,
        'total_reminders' => $totalReminders,
    ];

    return view('home', compact('homeStats'));
})->name('home');

// Banned page for deactivated users
Route::get('/banned', function () {
    return view('auth.banned');
})->name('banned');

// System maintenance page
Route::get('/maintenance', function () {
    return view('maintenance', [
        'status' => 'Maintenance in progress',
        'estimated_time' => 'Expected to be complete soon',
        'work_description' => 'System repairs and improvements'
    ]);
})->name('maintenance');

// Admin route to toggle maintenance mode (admins only)
Route::post('/admin/maintenance/toggle', [App\Http\Controllers\MaintenanceController::class, 'toggle'])
    ->middleware(['auth', 'admin'])
    ->name('admin.maintenance.toggle');

// Main navigation pages
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

// Local proxy for BD Geo API v2 endpoints (same-origin, no CORS issues in forms)
Route::prefix('geo/v2.0')->group(function () {
    Route::get('/divisions', [GeoController::class, 'divisions']);
    Route::get('/districts', [GeoController::class, 'districtsAll']);
    Route::get('/districts/{divisionId}', [GeoController::class, 'districtsByDivision'])->whereNumber('divisionId');
    Route::get('/upazilas', [GeoController::class, 'upazilasAll']);
    Route::get('/upazilas/{districtId}', [GeoController::class, 'upazilasByDistrict'])->whereNumber('districtId');
    Route::get('/unions/{upazilaId}', [GeoController::class, 'unionsByUpazila'])->whereNumber('upazilaId');
});

// Test helper routes (only enabled in local/testing environments)
if (app()->environment('local') || app()->environment('testing')) {
    Route::get('/_playwright/create-test-user', function (Request $request) {
        $email = (string) $request->query('email', 'playwright@test');
        $password = (string) $request->query('password', 'Password123!');

        $user = App\Models\User::withoutGlobalScopes()->firstOrCreate([
            'email' => $email,
        ], [
            'name' => 'Playwright Test',
            'password' => Illuminate\Support\Facades\Hash::make($password),
            'gender' => 'male',
        ]);

        // Ensure verified and active
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->save();

        // Ensure address exists to satisfy profile requirements
        $user->address()->updateOrCreate([], [
            'division_id' => 6,
            'division' => 'Dhaka',
            'district_id' => 26,
            'district' => 'Dhaka',
            'upazila_id' => 10,
            'upazila' => 'Mirpur',
        ]);

        Illuminate\Support\Facades\Auth::login($user);

        return redirect('/');
    });
}

Route::get('/language/{locale}', function (string $locale) {
    if (!in_array($locale, ['en', 'bn'], true)) {
        abort(404);
    }

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

Route::get('/diseases', [PublicHealthController::class, 'indexDiseases'])->name('public.diseases.index');
Route::get('/symptoms', [PublicHealthController::class, 'indexSymptoms'])->name('public.symptoms.index');
Route::get('/diseases/{disease}', [PublicHealthController::class, 'showDisease'])->name('public.disease.show');
Route::get('/symptoms/{symptom}', [PublicHealthController::class, 'showSymptom'])->name('public.symptoms.show');

/*
|--------------------------------------------------------------------------
| Guest Routes (Login, Register, Password Reset)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Register Routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Password Reset Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class, \App\Http\Middleware\RedirectIfEmailNotVerified::class])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->name('verification.resend');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class, \App\Http\Middleware\RedirectIfEmailNotVerified::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class, 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('home');
    });
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/logs', [ProfileActivityLogController::class, 'index'])->name('profile.logs');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Settings (notification preferences)
    Route::get('/profile/settings', [NotificationPreferenceController::class, 'index'])->name('profile.setting');
    Route::put('/profile/settings', [NotificationPreferenceController::class, 'update'])->name('profile.setting.update');
    Route::get('/profile/notifications', function () {
        return redirect()->route('profile.setting');
    })->name('profile.notifications');
    Route::put('/profile/notifications', function () {
        return redirect()->route('profile.setting');
    })->name('profile.notifications.update');
    Route::post('/profile/notifications/toggle-email', [NotificationPreferenceController::class, 'toggleEmail'])->name('profile.notifications.toggle-email');
    
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
    Route::get('/health/metric/{healthMetric}', function (\App\Models\UserHealth $healthMetric) {
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
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/starred', [App\Http\Controllers\NotificationController::class, 'starred'])->name('starred');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{id}/star', [App\Http\Controllers\NotificationController::class, 'toggleStar'])->name('star');
        Route::delete('/{id}/delete', [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
        Route::delete('/clear-all', [App\Http\Controllers\NotificationController::class, 'clearAll'])->name('clear-all');
    });

    /*
    |--------------------------------------------------------------------------
    | Medicine Reminder Notification Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('medicine/reminders')->name('medicine.reminders.')->group(function () {
        Route::get('/notifications', [MedicineReminderController::class, 'notifications'])->name('notifications');
        Route::get('/unread-count', [MedicineReminderController::class, 'unreadCount'])->name('unread-count');
        Route::post('/notification/{id}/read', [MedicineReminderController::class, 'markNotificationRead'])->name('notification.read');
        Route::post('/notification/mark-all-read', [MedicineReminderController::class, 'markAllRead'])->name('notification.mark-all-read');
        Route::delete('/notification/{id}/delete', [MedicineReminderController::class, 'deleteNotification'])->name('notification.delete');
        Route::post('/{id}/taken-from-notification', [MedicineReminderController::class, 'markTakenFromNotification'])->name('taken-from-notification');
        Route::post('/{id}/missed-from-notification', [MedicineReminderController::class, 'markMissedFromNotification'])->name('missed-from-notification');
    });

    // Suggestions
    Route::get('/suggestions', [SuggestionsController::class, 'index'])->name('suggestions');

    // AI Chatbot
    Route::post('/chatbot/message', [AiChatController::class, 'message'])->name('chatbot.message');
    Route::post('/chatbot/about-me', [AiChatController::class, 'aboutMe'])->name('chatbot.about_me');
    Route::post('/chatbot/smart-suggestions', [AiChatController::class, 'smartSuggestions'])->name('chatbot.smart_suggestions');
});

/*
|--------------------------------------------------------------------------
| Health Related Routes
|--------------------------------------------------------------------------
*/
Route::prefix('health')->name('health.')->group(function () {
    Route::get('/hospitals', function () {
        return redirect()->route('help');
    })->name('hospitals');
    Route::get('/tips', function () {
        return redirect()->route('help');
    })->name('tips');
    
    Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class, 'verified'])->group(function () {
        Route::get('/records', function () {
            return redirect(route('health', [], false) . '#logs');
        })->name('records');
        Route::get('/tracking', function () {
            return redirect(route('health', [], false) . '#metrics');
        })->name('tracking');
        Route::get('/consultation', function () {
            return redirect()->route('help');
        })->name('consultation');
        Route::get('/symptoms', function () {
            return redirect(route('health', [], false) . '#symptomsPane');
        })->name('symptoms');
        Route::get('/suggestions', function () {
            return redirect()->route('suggestions');
        })->name('suggestions');
    });
});

/*
|--------------------------------------------------------------------------
| Community Routes - FULLY WORKING WITH MODAL DYNAMIC UPDATES
|--------------------------------------------------------------------------
*/
Route::prefix('community')->name('community.')->middleware(\App\Http\Middleware\RedirectIfEmailNotVerified::class)->group(function () {
    // Page routes (return HTML)
    Route::get('/', [CommunityController::class, 'home'])->name('home');
    Route::get('/posts', [CommunityController::class, 'postsIndex'])->name('posts.index');
    Route::get('/posts/{post}', [CommunityController::class, 'showPost'])->whereNumber('post')->name('posts.show');
    Route::get('/diseases/{disease}/posts', [CommunityController::class, 'diseasePosts'])->name('disease.posts');

    Route::get('/landing', [CommunityController::class, 'landing'])->name('landing');
    Route::get('/forum', function () {
        return redirect()->route('community.posts.index');
    })->name('index');
    Route::get('/diseases/starred/history', [CommunityController::class, 'starredDiseaseHistory'])->name('diseases.starred.history');
    Route::get('/posts/starred', [CommunityController::class, 'starredPosts'])->name('posts.starred');
    Route::get('/posts/pending', [CommunityController::class, 'pendingPosts'])->name('posts.pending');
    Route::get('/posts/reported', [CommunityController::class, 'userReportedPosts'])->name('posts.reported');
    // Legacy alias kept for compatibility
    Route::get('/forum/posts/{post}', function (\App\Models\Post $post) {
        return redirect()->route('community.posts.show', $post);
    });
    
    // MODAL POST - CRITICAL FOR DYNAMIC RELOADS
    Route::get('/modal-post/{post}', [CommunityController::class, 'modalPost'])->name('modal.post');
    
    // API routes (JSON responses)
    Route::get('/posts/load', [CommunityController::class, 'loadPosts'])->name('posts.load');
    Route::get('/posts/{post}/comments', [CommunityController::class, 'loadComments'])->name('posts.comments.load');
    Route::get('/posts/{post}/comments/more', [CommunityController::class, 'loadMoreComments'])->name('posts.comments.more');
    
    // Post CRUD - MATCHES YOUR JAVASCRIPT EXACTLY
    Route::middleware('auth')->group(function () {
        Route::post('/posts', [CommunityController::class, 'storePost'])->name('posts.store');
        Route::patch('/posts/{post}', [CommunityController::class, 'updatePost'])->name('posts.update');
        Route::delete('/posts/{post}', [CommunityController::class, 'destroyPost'])->name('posts.destroy');
        Route::put('/posts/{post}/likes', [CommunityController::class, 'togglePostLike'])->name('posts.like');
        Route::put('/posts/{post}/star', [CommunityController::class, 'togglePostStar'])->name('posts.star');
        Route::put('/diseases/{disease}/star', [CommunityController::class, 'toggleDiseaseStar'])->name('diseases.star');
        Route::post('/posts/{post}/report', [CommunityController::class, 'reportPost'])->name('posts.report');
        
        Route::patch('/posts/{post}/approve', [CommunityController::class, 'approvePost'])->name('posts.approve');
        Route::patch('/posts/{post}/reject', [CommunityController::class, 'rejectPost'])->name('posts.reject');

        Route::post('/posts/{post}/comments', [CommunityController::class, 'storeComment'])->name('comments.store');
        Route::patch('/comments/{comment}', [CommunityController::class, 'updateComment'])->name('comments.update');
        Route::delete('/comments/{comment}', [CommunityController::class, 'destroyComment'])->name('comments.destroy');
        Route::put('/comments/{comment}/likes', [CommunityController::class, 'toggleCommentLike'])->name('comments.like');
    });
    
    // User details for modals
    Route::get('/users/{userId}', [CommunityController::class, 'getUserDetails'])->name('user.details');
});

/*
|--------------------------------------------------------------------------
| Medicine Routes
|--------------------------------------------------------------------------
*/
Route::prefix('medicine')->name('medicine.')->middleware(['auth', 'verified'])->group(function () {
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
    Route::get('/reminders/{id}/modal', [MedicineReminderController::class, 'modal'])->name('reminders.modal');
    Route::get('/reminders', [MedicineReminderController::class, 'index'])->name('reminders');
    Route::post('/reminders/{id}/taken', [MedicineReminderController::class, 'markTaken'])->name('reminders.taken');
    Route::post('/reminders/{id}/missed', [MedicineReminderController::class, 'markMissed'])->name('reminders.missed');
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
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class, 'verified'])->group(function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
});

// Public user profile
Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'publicShow'])->name('users.show');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class, 'admin', \App\Http\Middleware\RedirectIfEmailNotVerified::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminManagementController::class, 'usersIndex'])->name('users.index');
    Route::post('/users', [AdminManagementController::class, 'usersStore'])->name('users.store');
    Route::patch('/users/{user}', [AdminManagementController::class, 'usersUpdate'])->name('users.update');
    Route::patch('/users/{user}/toggle-active', [AdminManagementController::class, 'usersToggleActive'])->name('users.toggle-active');
    Route::delete('/users/{user}', [AdminManagementController::class, 'usersDestroy'])->name('users.destroy');

    Route::get('/diseases', [AdminManagementController::class, 'diseasesIndex'])->name('diseases.index');
    Route::post('/diseases', [AdminManagementController::class, 'diseasesStore'])->name('diseases.store');
    Route::patch('/diseases/{disease}', [AdminManagementController::class, 'diseasesUpdate'])->name('diseases.update');
    Route::delete('/diseases/{disease}', [AdminManagementController::class, 'diseasesDestroy'])->name('diseases.destroy');

    Route::get('/symptoms', [AdminManagementController::class, 'symptomsIndex'])->name('symptoms.index');
    Route::get('/symtoms', function () {
        return redirect()->route('admin.symptoms.index');
    })->name('symtoms.index');
    Route::post('/symptoms', [AdminManagementController::class, 'symptomsStore'])->name('symptoms.store');
    Route::patch('/symptoms/{symptom}', [AdminManagementController::class, 'symptomsUpdate'])->name('symptoms.update');
    Route::delete('/symptoms/{symptom}', [AdminManagementController::class, 'symptomsDestroy'])->name('symptoms.destroy');

    Route::get('/health', [AdminManagementController::class, 'metricsIndex'])->name('health.index');
    Route::post('/health', [AdminManagementController::class, 'metricsStore'])->name('health.store');
    Route::get('/metrics/{healthMetric}', [AdminManagementController::class, 'metricsShow'])->name('metrics.show');
    Route::patch('/metrics/{healthMetric}', [AdminManagementController::class, 'metricsUpdate'])->name('metrics.update');
    Route::delete('/metrics/{healthMetric}', [AdminManagementController::class, 'metricsDestroy'])->name('metrics.destroy');

    Route::prefix('community')->name('community.')->group(function () {
        Route::get('/posts', [CommunityController::class, 'adminPostsIndex'])->name('posts.index');
        Route::get('/posts/pending', [CommunityController::class, 'adminPendingPosts'])->name('posts.pending');
        Route::get('/posts/reported', [CommunityController::class, 'adminReportedPosts'])->name('posts.reported');
        Route::patch('/posts/{post}/approve', [CommunityController::class, 'approvePost'])->name('posts.approve');
        Route::patch('/posts/{post}/reject', [CommunityController::class, 'rejectPost'])->name('posts.reject');
        Route::delete('/posts/{post}', [CommunityController::class, 'destroyPost'])->name('posts.destroy');
        Route::patch('/community/posts/{post}/clear-report', [CommunityController::class, 'clearReport'])->name('community.posts.clear-report');
        Route::delete('/comments/{comment}', [CommunityController::class, 'destroyComment'])->name('comments.destroy');
        Route::put('/comments/{comment}/likes', [CommunityController::class, 'toggleCommentLike'])->name('comments.like');
        Route::patch('/comments/{comment}', [CommunityController::class, 'updateComment'])->name('comments.update');
        Route::get('/modal-post/{post}', [CommunityController::class, 'modalPost'])->name('modal.post');
        Route::get('/users/{userId}', [CommunityController::class, 'getUserDetails'])->name('user.details');
    });

    Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::get('/logs', [App\Http\Controllers\AdminActivityLogController::class, 'index'])->name('logs.index');
    
    // Future admin routes
    Route::get('/community/reported', [App\Http\Controllers\AdminDashboardController::class, 'reportedPosts'])->name('community.reported');
    Route::get('/medical', [App\Http\Controllers\AdminDashboardController::class, 'medical'])->name('medical.index');
    Route::get('/analytics', [App\Http\Controllers\AdminDashboardController::class, 'analytics'])->name('analytics');
    Route::get('/settings', [App\Http\Controllers\AdminDashboardController::class, 'settings'])->name('settings');

    // ========== DATABASE BACKUP ROUTES ==========
    Route::get('/backups', [App\Http\Controllers\AdminBackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [App\Http\Controllers\AdminBackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/download/{file}', [App\Http\Controllers\AdminBackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{file}', [App\Http\Controllers\AdminBackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('/backups/download-multiple', [App\Http\Controllers\AdminBackupController::class, 'downloadMultiple'])->name('backups.download-multiple');
    // ============================================

});

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class, 'admin', \App\Http\Middleware\RedirectIfEmailNotVerified::class])->prefix('api/users')->group(function () {
    Route::get('{id}', function ($id) {
        $user = \App\Models\User::with('address')->findOrFail($id);
        return response()->json($user->only([
            'id', 'name', 'email', 'phone', 'occupation', 'blood_group', 'date_of_birth', 'picture', 'role', 'gender', 'is_active', 'email_verified_at'
        ]) + [
            'address' => [
                'district' => $user->address?->district,
                'upazila' => $user->address?->upazila,
                'street' => $user->address?->street,
                'house' => $user->address?->house,
            ],
        ]);
    });
    
    Route::get('{id}/medical', function ($id) {
        $user = \App\Models\User::with(['medicines.activeSchedule', 'userDiseases.disease', 'healthMetrics.healthMetric'])->findOrFail($id);
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