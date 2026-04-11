<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Disease;
use App\Models\Environment;
use App\Models\EnvironmentMetric;
use Illuminate\Support\Facades\Schema;
use App\Models\HealthMetric;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineReminder;
use App\Models\MedicineSchedule;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Symptom;
use App\Models\UserSymptom;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\UserAddress;
use App\Models\UserDisease;
use App\Models\UserHealth;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HighVolumeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureMetricDefinitions();
        $this->ensureAdminAccount();
        $users = $this->seedUsers();
        $diseaseIds = Disease::query()->pluck('id')->values();

        if ($diseaseIds->isEmpty()) {
            return;
        }

        $this->seedUserDiseases($users, $diseaseIds);
        $this->seedHealthAndMedicineData($users);
        [$posts, $comments] = $this->seedCommunityData($users, $diseaseIds);
        $this->seedMailings($users);
        $this->seedPushSubscriptions($users);
    }

    private function ensureAdminAccount(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@mydoctor.com'],
            [
                'name' => 'System Admin',
                'phone' => '01700000000',
                'date_of_birth' => now()->subYears(32)->format('Y-m-d'),
                'occupation' => 'Administrator',
                'blood_group' => 'O+',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('abcd1234'),
                'role' => 'admin',
                'notification_settings' => [
                    'reminders' => true,
                    'updates' => true,
                    'newsletter' => false,
                ],
                'remember_token' => Str::random(10),
            ]
        );

        $admin = User::query()->where('email', 'admin@mydoctor.com')->first();
        if ($admin) {
            UserSetting::query()->updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'show_personal_info' => false,
                    'show_diseases' => false,
                    'show_chatbot' => false,
                    'show_notification_badge' => true,
                    'show_mail_badge' => true,
                ]
            );

            UserAddress::query()->updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'division_id' => 6,
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'district_id' => 26,
                    'district' => 'Dhaka',
                    'district_bn' => 'ঢাকা',
                    'upazila_id' => 8,
                    'upazila' => 'Dhanmondi',
                    'upazila_bn' => 'ধানমন্ডি',
                    'street' => 'Admin Road',
                    'house' => 'A-1',
                ]
            );
        }
    }

    private function seedUsers()
    {
        $names = [
            'Md Rahim Uddin', 'Nusrat Jahan', 'Abul Hossain', 'Shamima Akter', 'Sabbir Ahmed',
            'Farzana Yasmin', 'Jahid Hasan', 'Mst Rina Begum', 'Tanvir Hossain', 'Rafiya Sultana',
            'Mehedi Hasan', 'Sharmin Ara', 'Fahim Chowdhury', 'Sadia Islam', 'Rashed Karim',
            'Jannatul Ferdous', 'Imran Kabir', 'Nasrin Akter', 'Kamal Uddin', 'Tania Rahman',
            'Mahmudul Hasan', 'Moumita Das', 'Arifur Rahman', 'Samia Nahar', 'Saiful Islam',
            'Rumana Ahmed', 'Mizanur Rahman', 'Shaila Parvin', 'Rakib Hasan', 'Popy Akter',
            'Shakil Ahmed', 'Laboni Khatun', 'Asif Iqbal', 'Mitu Rani', 'Biplob Kumar',
            'Jui Akter', 'Anisur Rahman', 'Salma Begum', 'Parvez Mia', 'Afia Anjum',
            'Belal Hossain', 'Dilruba Yasmin', 'Habibur Rahman', 'Maliha Tabassum', 'Rony Ahmed',
            'Fariha Jannat', 'Kawsar Ali', 'Sanjida Islam', 'Sohanur Rahman', 'Nabila Afroz',
            'Tarek Hasan', 'Ishrat Jahan', 'Samiul Islam', 'Mithila Rahman', 'Minhaz Uddin',
            'Rukaiya Akter', 'Rasel Chowdhury', 'Sharmeen Sultana', 'Yasin Arafat', 'Purnima Das',
        ];

        $occupations = [
            'Teacher', 'Farmer', 'Businessman', 'Student', 'Engineer', 'Nurse', 'Accountant',
            'Bank Officer', 'Driver', 'Tailor', 'Shopkeeper', 'Software Developer', 'Civil Servant',
            'Electrician', 'Pharmacist',
        ];

        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        $rows = [];
        $now = now();

        for ($i = 1; $i <= 24; $i++) {
            $rows[] = [
                'name' => $names[$i - 1],
                'email' => "user{$i}@gmail.com",
                'phone' => '01' . str_pad((string) mt_rand(300000000, 999999999), 9, '0', STR_PAD_LEFT),
                'date_of_birth' => now()->subYears(mt_rand(18, 70))->subDays(mt_rand(0, 364))->format('Y-m-d'),
                'occupation' => $occupations[array_rand($occupations)],
                'blood_group' => $bloodGroups[array_rand($bloodGroups)],
                'gender' => collect(['male', 'female', 'other'])->random(),
                'email_verified_at' => $now,
                'password' => Hash::make('abcd1234'),
                'role' => 'member',
                'notification_settings' => json_encode([
                    'reminders' => true,
                    'updates' => true,
                    'newsletter' => (bool) random_int(0, 1),
                ]),
                'remember_token' => Str::random(10),
                'created_at' => $now->copy()->subDays(mt_rand(30, 1000)),
                'updated_at' => $now,
            ];
        }

        User::query()->insert($rows);

        $users = User::query()
            ->where('email', 'like', 'user%@gmail.com')
            ->orderBy('id')
            ->get();

        $settingsRows = [];
        foreach ($users as $user) {
            $settingsRows[] = [
                'user_id' => $user->id,
                'email_notifications' => true,
                'push_notifications' => true,
                'show_personal_info' => false,
                'show_diseases' => false,
                'show_chatbot' => true,
                'show_notification_badge' => true,
                'show_mail_badge' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($settingsRows !== []) {
            UserSetting::query()->upsert(
                $settingsRows,
                ['user_id'],
                ['email_notifications', 'push_notifications', 'show_personal_info', 'show_diseases', 'show_chatbot', 'show_notification_badge', 'show_mail_badge', 'updated_at']
            );
        }

        return $users;
    }

    private function seedUserDiseases($users, $diseaseIds): void
    {
        $userIds = $users->pluck('id')->values();
        $maxPerUser = min(10, $diseaseIds->count());
        $minPerUser = min(2, $maxPerUser);

        $targetCounts = [];
        $assigned = [];
        foreach ($userIds as $uid) {
            $targetCounts[$uid] = random_int($minPerUser, $maxPerUser);
            $assigned[$uid] = [];
        }

        // Ensure all diseases are covered across the 60 users.
        foreach ($diseaseIds as $diseaseId) {
            $eligible = $userIds->filter(fn ($uid) => count($assigned[$uid]) < $targetCounts[$uid])->values();
            $uid = $eligible->isNotEmpty()
                ? $eligible->random()
                : $userIds->random();

            $assigned[$uid][$diseaseId] = true;
        }

        foreach ($userIds as $uid) {
            $pool = $diseaseIds->shuffle()->values();
            foreach ($pool as $diseaseId) {
                if (count($assigned[$uid]) >= $targetCounts[$uid]) {
                    break;
                }
                $assigned[$uid][$diseaseId] = true;
            }
        }

        $rows = [];
        foreach ($assigned as $uid => $diseaseSet) {
            foreach (array_keys($diseaseSet) as $diseaseId) {
                $rows[] = [
                    'user_id' => $uid,
                    'disease_id' => $diseaseId,
                    'diagnosed_at' => now()->subDays(mt_rand(30, 2500))->format('Y-m-d'),
                    'status' => collect(['active', 'recovered', 'chronic', 'managed'])->random(),
                    'notes' => 'Routine follow-up and care plan active.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        UserDisease::query()->insert($rows);
    }

    private function seedHealthAndMedicineData($users): void
    {
        $topUserIds = $users->pluck('id')->take(5)->all();

        foreach ($users as $user) {
            $isTop = in_array($user->id, $topUserIds, true);

            $addressCount = $isTop ? random_int(1, 2) : 1;
            UserAddress::factory()->count($addressCount)->create(['user_id' => $user->id]);

            $metricCount = $isTop ? random_int(25, 45) : random_int(8, 20);
            UserHealth::factory()->count($metricCount)->create(['user_id' => $user->id]);

            $symptomCount = $isTop ? random_int(25, 45) : random_int(8, 20);
            UserSymptom::factory()->count($symptomCount)->create(['user_id' => $user->id]);

            $envCount = $isTop ? random_int(8, 18) : random_int(4, 10);
            if (Schema::hasTable('environments') && Schema::hasTable('environment_metrics')) {
                $environments = Environment::factory()->count($envCount)->create(['user_id' => $user->id]);
                foreach ($environments as $environment) {
                    EnvironmentMetric::factory()->count($isTop ? random_int(2, 4) : random_int(1, 3))->create([
                        'environment_id' => $environment->id,
                    ]);
                }
            }

            $uploadCount = $isTop ? random_int(8, 20) : random_int(4, 10);
            Upload::factory()->count($uploadCount)->create(['user_id' => $user->id]);

            $medicineCount = $isTop ? random_int(4, 8) : random_int(2, 5);
            $medicines = Medicine::factory()->count($medicineCount)->create(['user_id' => $user->id]);

            foreach ($medicines as $medicine) {
                $scheduleCount = $isTop ? random_int(2, 4) : random_int(1, 2);
                $schedules = MedicineSchedule::factory()->count($scheduleCount)->create([
                    'medicine_id' => $medicine->id,
                ]);

                foreach ($schedules as $schedule) {
                    $reminderCount = $isTop ? random_int(3, 6) : random_int(2, 4);
                    MedicineReminder::factory()->count($reminderCount)->create([
                        'schedule_id' => $schedule->id,
                    ]);
                }

                $logDays = $isTop ? random_int(14, 28) : random_int(7, 16);
                for ($d = 0; $d < $logDays; $d++) {
                    $date = now()->subDays($d + ($medicine->id % 10))->format('Y-m-d');
                    $scheduled = random_int(1, 6);
                    $taken = random_int(0, $scheduled);
                    MedicineLog::query()->firstOrCreate(
                        [
                            'medicine_id' => $medicine->id,
                            'user_id' => $user->id,
                            'date' => $date,
                        ],
                        [
                            'total_scheduled' => $scheduled,
                            'total_taken' => $taken,
                            'total_missed' => $scheduled - $taken,
                        ]
                    );
                }
            }
        }
    }

    private function ensureMetricDefinitions(): void
    {
        HealthMetric::seedDefaults();
    }

    private function seedCommunityData($users, $diseaseIds): array
    {
        $topUserIds = $users->pluck('id')->take(5)->values();
        $otherUserIds = $users->pluck('id')->slice(5)->values();

        $posts = collect();
        foreach ($users as $user) {
            $isTop = $topUserIds->contains($user->id);
            $postCount = $isTop ? random_int(7, 12) : random_int(1, 3);

            for ($i = 0; $i < $postCount; $i++) {
                $post = Post::factory()->create([
                    'user_id' => $user->id,
                    'disease_id' => $diseaseIds->random(),
                ]);
                $posts->push($post);
            }
        }

        $comments = collect();
        $userIds = $users->pluck('id')->values();

        foreach ($posts as $post) {
            $isTopPostOwner = $topUserIds->contains($post->user_id);
            $commentCount = $isTopPostOwner ? random_int(3, 7) : random_int(0, 2);

            for ($i = 0; $i < $commentCount; $i++) {
                $preferTopEngagers = random_int(1, 100) <= 80;
                $commenterPool = $preferTopEngagers && $topUserIds->isNotEmpty()
                    ? $topUserIds
                    : $userIds;

                $commenterId = $commenterPool
                    ->reject(fn ($id) => $id === $post->user_id)
                    ->values()
                    ->whenEmpty(fn ($collection) => $userIds)
                    ->random();

                $comment = Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $commenterId,
                ]);
                $comments->push($comment);
            }

            $possibleLikers = $userIds->reject(fn ($id) => $id === $post->user_id)->values();
            $topLikers = $possibleLikers->intersect($topUserIds)->values()->shuffle();
            $otherLikers = $possibleLikers->intersect($otherUserIds)->values()->shuffle();

            $topLikeCount = min(
                $topLikers->count(),
                $isTopPostOwner ? random_int(2, 5) : random_int(1, 3)
            );
            $otherLikeCount = min(
                $otherLikers->count(),
                $isTopPostOwner ? random_int(0, 2) : random_int(0, 1)
            );

            $likers = $topLikers->take($topLikeCount)
                ->merge($otherLikers->take($otherLikeCount))
                ->shuffle()
                ->values();

            foreach ($likers as $likerId) {
                PostLike::query()->firstOrCreate([
                    'post_id' => $post->id,
                    'user_id' => $likerId,
                ]);
            }
        }

        foreach ($comments as $comment) {
            $possibleLikers = $userIds->reject(fn ($id) => $id === $comment->user_id)->values();
            $topLikers = $possibleLikers->intersect($topUserIds)->values()->shuffle();
            $otherLikers = $possibleLikers->intersect($otherUserIds)->values()->shuffle();
            $isTopCommentOwner = $topUserIds->contains($comment->user_id);

            $topLikeCount = min(
                $topLikers->count(),
                $isTopCommentOwner ? random_int(1, 4) : random_int(1, 2)
            );
            $otherLikeCount = min(
                $otherLikers->count(),
                $isTopCommentOwner ? random_int(0, 1) : random_int(0, 1)
            );

            $likers = $topLikers->take($topLikeCount)
                ->merge($otherLikers->take($otherLikeCount))
                ->shuffle()
                ->values();

            foreach ($likers as $likerId) {
                CommentLike::query()->firstOrCreate([
                    'comment_id' => $comment->id,
                    'user_id' => $likerId,
                ]);
            }
        }

        // Refresh counter columns.
        $postCommentCounts = Comment::query()->selectRaw('post_id, COUNT(*) as c')->groupBy('post_id')->pluck('c', 'post_id');
        $postLikeCounts = PostLike::query()->selectRaw('post_id, COUNT(*) as c')->groupBy('post_id')->pluck('c', 'post_id');
        foreach ($posts as $post) {
            $post->update([
                'comment_count' => (int) ($postCommentCounts[$post->id] ?? 0),
                'like_count' => (int) ($postLikeCounts[$post->id] ?? 0),
            ]);
        }

        $commentLikeCounts = CommentLike::query()->selectRaw('comment_id, COUNT(*) as c')->groupBy('comment_id')->pluck('c', 'comment_id');
        foreach ($comments as $comment) {
            $comment->update(['like_count' => (int) ($commentLikeCounts[$comment->id] ?? 0)]);
        }

        return [$posts, $comments];
    }

    private function seedMailings($users): void
    {
        $topUserIds = $users->pluck('id')->take(5)->values();
        $otherUserIds = $users->pluck('id')->slice(5)->values();
        $allUserIds = $users->pluck('id')->values();

        $healthSubjects = [
            'Blood pressure follow-up', 'Diabetes management reminder', 'Heart health check-in',
            'Medication adherence support', 'Nutrition and hydration tips', 'Sleep and stress guidance',
            'Asthma symptom update', 'Kidney function report reminder', 'Routine wellness consultation',
            'Exercise and mobility check', 'Fever and symptom monitoring', 'Lab report interpretation',
        ];

        $rows = [];
        $totalMailings = 160;

        for ($i = 1; $i <= $totalMailings; $i++) {
            $isDraft = $i > 140;

            if ($isDraft) {
                $senderId = $topUserIds->random();
                $receiverId = random_int(1, 100) <= 70 ? null : $allUserIds->reject(fn ($id) => $id === $senderId)->random();
                $status = 'draft';
            } else {
                $senderId = $i <= 110 ? $topUserIds->random() : $otherUserIds->random();
                $receiverId = $allUserIds->reject(fn ($id) => $id === $senderId)->random();
                $status = collect(['unread', 'read', 'archived', 'sent'])->random();
            }

            $createdAt = now()->subDays(random_int(0, 210))->subMinutes(random_int(0, 1440));

            $rows[] = [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'title' => $healthSubjects[array_rand($healthSubjects)] . ' #' . random_int(10, 999),
                'message' => implode("\n\n", [
                    'Dear patient, this is a health follow-up message regarding your recent condition and treatment plan.',
                    'Please continue your prescribed medicines, maintain hydration, and track key metrics daily.',
                    'If symptoms worsen, please contact your doctor or nearest hospital immediately.',
                ]),
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('mailings')->insert($chunk);
        }
    }

    private function seedPushSubscriptions($users): void
    {
        $rows = [];
        foreach ($users->take(18) as $user) {
            $rows[] = [
                'subscribable_type' => User::class,
                'subscribable_id' => $user->id,
                'endpoint' => 'https://push.example.com/sub/' . $user->id . '/' . Str::uuid(),
                'public_key' => base64_encode(Str::random(32)),
                'auth_token' => base64_encode(Str::random(16)),
                'content_encoding' => 'aesgcm',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('push_subscriptions')->insert($rows);
    }
}
