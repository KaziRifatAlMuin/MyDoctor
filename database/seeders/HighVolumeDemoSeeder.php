<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Disease;
use App\Models\Environment;
use App\Models\EnvironmentMetric;
use App\Models\HealthMetric;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineReminder;
use App\Models\MedicineSchedule;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserDisease;
use App\Models\UserHealth;
use App\Models\UserSetting;
use App\Models\UserSymptom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HighVolumeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureMetricDefinitions();
        $this->ensureAdminAccount();

        $users = $this->seedUsers();
        $diseaseIds = Disease::query()->pluck('id')->values();

        if ($users->isEmpty() || $diseaseIds->isEmpty()) {
            return;
        }

        $this->seedUserDiseases($users, $diseaseIds);
        $this->seedHealthAndMedicineData($users);
        $this->seedCommunityData($users, $diseaseIds);
        $this->seedMailings($users);
        $this->seedPushSubscriptions($users);
    }

    private function ensureMetricDefinitions(): void
    {
        HealthMetric::seedDefaults();
    }

    private function ensureAdminAccount(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@mydoctor.com'],
            [
                'name' => 'System Admin',
                'phone' => '01700000000',
                'date_of_birth' => now()->subYears(34)->format('Y-m-d'),
                'occupation' => 'Administrator',
                'blood_group' => 'O+',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => Hash::make('abcd1234'),
                'role' => 'admin',
                'is_active' => true,
                'notification_settings' => [
                    'reminders' => true,
                    'updates' => true,
                    'newsletter' => false,
                ],
                'remember_token' => Str::random(10),
            ]
        );

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

    private function seedUsers()
    {
        $names = [
            'Md Rahim Uddin', 'Nusrat Jahan', 'Abul Hossain', 'Shamima Akter', 'Sabbir Ahmed',
            'Farzana Yasmin', 'Jahid Hasan', 'Rina Begum', 'Tanvir Hossain', 'Rafiya Sultana',
            'Mehedi Hasan', 'Sharmin Ara', 'Fahim Chowdhury', 'Sadia Islam', 'Rashed Karim',
            'Jannatul Ferdous', 'Imran Kabir', 'Nasrin Akter', 'Kamal Uddin',
        ];

        $occupations = [
            'Teacher', 'Farmer', 'Businessman', 'Student', 'Engineer', 'Nurse', 'Accountant',
            'Bank Officer', 'Driver', 'Tailor', 'Shopkeeper', 'Software Developer', 'Civil Servant',
            'Electrician', 'Pharmacist',
        ];

        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        foreach ($names as $index => $name) {
            $emailIndex = $index + 1;

            $user = User::query()->updateOrCreate(
                ['email' => "user{$emailIndex}@gmail.com"],
                [
                    'name' => $name,
                    'phone' => '01' . str_pad((string) mt_rand(300000000, 999999999), 9, '0', STR_PAD_LEFT),
                    'date_of_birth' => now()->subYears(mt_rand(18, 70))->subDays(mt_rand(0, 364))->format('Y-m-d'),
                    'occupation' => $occupations[array_rand($occupations)],
                    'blood_group' => $bloodGroups[array_rand($bloodGroups)],
                    'gender' => collect(['male', 'female', 'other'])->random(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('abcd1234'),
                    'role' => 'member',
                    'is_active' => true,
                    'notification_settings' => [
                        'reminders' => true,
                        'updates' => true,
                        'newsletter' => (bool) random_int(0, 1),
                    ],
                    'remember_token' => Str::random(10),
                ]
            );

            UserSetting::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'show_personal_info' => false,
                    'show_diseases' => false,
                    'show_chatbot' => true,
                    'show_notification_badge' => true,
                    'show_mail_badge' => true,
                ]
            );

            UserAddress::query()->updateOrCreate(
                ['user_id' => $user->id],
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
                    'street' => 'Road ' . random_int(1, 60),
                    'house' => 'House ' . random_int(1, 40),
                ]
            );
        }

        return User::query()
            ->where('role', 'member')
            ->where('email', 'like', 'user%@gmail.com')
            ->orderBy('id')
            ->take(19)
            ->get();
    }

    private function seedUserDiseases($users, $diseaseIds): void
    {
        foreach ($users as $index => $user) {
            $isCore = $index < 7;
            $attachCount = $isCore
                ? min($diseaseIds->count(), random_int(5, 9))
                : min($diseaseIds->count(), random_int(2, 5));

            $selectedDiseaseIds = $diseaseIds->shuffle()->take($attachCount)->values();

            foreach ($selectedDiseaseIds as $diseaseId) {
                UserDisease::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'disease_id' => $diseaseId,
                    ],
                    [
                        'diagnosed_at' => now()->subDays(mt_rand(20, 2500))->format('Y-m-d'),
                        'status' => collect(['active', 'recovered', 'chronic', 'managed'])->random(),
                        'notes' => 'Seeded realistic disease history for demo users.',
                    ]
                );
            }
        }
    }

    private function seedHealthAndMedicineData($users): void
    {
        $coreUserIds = $users->take(7)->pluck('id')->all();

        foreach ($users as $user) {
            $isCore = in_array($user->id, $coreUserIds, true);

            $metricCount = $isCore ? random_int(35, 55) : random_int(8, 18);
            UserHealth::factory()->count($metricCount)->create(['user_id' => $user->id]);

            $symptomCount = $isCore ? random_int(30, 50) : random_int(8, 16);
            UserSymptom::factory()->count($symptomCount)->create(['user_id' => $user->id]);

            $uploadCount = $isCore ? random_int(10, 20) : random_int(3, 8);
            Upload::factory()->count($uploadCount)->create(['user_id' => $user->id]);

            if (Schema::hasTable('environments') && Schema::hasTable('environment_metrics')) {
                $environmentCount = $isCore ? random_int(8, 16) : random_int(2, 7);
                $environments = Environment::factory()->count($environmentCount)->create(['user_id' => $user->id]);

                foreach ($environments as $environment) {
                    EnvironmentMetric::factory()->count($isCore ? random_int(2, 4) : random_int(1, 2))->create([
                        'environment_id' => $environment->id,
                    ]);
                }
            }

            $medicineCount = $isCore ? random_int(4, 8) : random_int(1, 3);
            $medicines = Medicine::factory()->count($medicineCount)->create(['user_id' => $user->id]);

            foreach ($medicines as $medicine) {
                $scheduleCount = $isCore ? random_int(2, 4) : random_int(1, 2);
                $schedules = MedicineSchedule::factory()->count($scheduleCount)->create([
                    'medicine_id' => $medicine->id,
                ]);

                foreach ($schedules as $schedule) {
                    $reminderCount = $isCore ? random_int(4, 8) : random_int(1, 3);
                    MedicineReminder::factory()->count($reminderCount)->create([
                        'schedule_id' => $schedule->id,
                    ]);
                }

                $logDays = $isCore ? random_int(18, 35) : random_int(6, 14);
                for ($d = 0; $d < $logDays; $d++) {
                    $date = now()->subDays($d + ($medicine->id % 10))->format('Y-m-d');
                    $scheduled = random_int(1, 6);
                    $taken = random_int(0, $scheduled);

                    MedicineLog::query()->updateOrCreate(
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

    private function seedCommunityData($users, $diseaseIds): void
    {
        $userIds = $users->pluck('id')->values();
        $userCount = $userIds->count();

        if ($userCount === 0) {
            return;
        }

        $ownerCursor = 0;
        $commenterCursor = 0;
        $posts = collect();
        $comments = collect();

        foreach ($diseaseIds as $diseaseId) {
            $postCountForDisease = random_int(3, 7);

            for ($i = 0; $i < $postCountForDisease; $i++) {
                $ownerId = $userIds[$ownerCursor % $userCount];
                $ownerCursor++;

                $createdAt = now()->subDays(random_int(0, 180))->subMinutes(random_int(0, 720));
                $isApproved = random_int(1, 100) <= 80;

                $post = Post::factory()->create([
                    'user_id' => $ownerId,
                    'disease_id' => $diseaseId,
                    'is_approved' => $isApproved,
                    'approved_at' => $isApproved ? $createdAt : null,
                    'is_reported' => random_int(1, 100) <= 8,
                    'description' => fake()->paragraphs(random_int(2, 4), true),
                    'created_at' => $createdAt,
                ]);

                $posts->push($post);
            }
        }

        foreach ($posts as $post) {
            // Pending (not approved) posts must not have any reactions, comments, or starred flags.
            if (!$post->is_approved) {
                continue;
            }

            $nonOwnerIds = $userIds->reject(fn ($id) => $id === $post->user_id)->values();
            if ($nonOwnerIds->isEmpty()) {
                continue;
            }

            $commentCount = random_int(2, 6);
            for ($i = 0; $i < $commentCount; $i++) {
                $commenterId = $nonOwnerIds[$commenterCursor % $nonOwnerIds->count()];
                $commenterCursor++;

                $comment = Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $commenterId,
                    'comment_details' => fake()->sentences(random_int(1, 3), true),
                    'created_at' => now()->subDays(random_int(0, 150))->subMinutes(random_int(0, 600)),
                ]);

                $comments->push($comment);
            }

            $starUsers = $nonOwnerIds->shuffle()->take(random_int(0, 3))->values();
            foreach ($starUsers as $starUserId) {
                PostLike::query()->firstOrCreate(
                    [
                        'post_id' => $post->id,
                        'user_id' => $starUserId,
                    ],
                    [
                        'is_starred' => true,
                    ]
                );
            }

            $likePool = $nonOwnerIds->diff($starUsers)->values();
            if ($likePool->isNotEmpty()) {
                $likeCount = random_int(1, min(7, $likePool->count()));
                $likeUsers = $likePool->shuffle()->take($likeCount);

                foreach ($likeUsers as $likeUserId) {
                    PostLike::query()->firstOrCreate(
                        [
                            'post_id' => $post->id,
                            'user_id' => $likeUserId,
                        ],
                        [
                            'is_starred' => false,
                        ]
                    );
                }
            }
        }

        foreach ($comments as $comment) {
            $nonCommenterIds = $userIds->reject(fn ($id) => $id === $comment->user_id)->values();
            if ($nonCommenterIds->isEmpty()) {
                continue;
            }

            $likeCount = random_int(0, min(4, $nonCommenterIds->count()));
            $commentLikeUsers = $nonCommenterIds->shuffle()->take($likeCount);

            foreach ($commentLikeUsers as $likeUserId) {
                CommentLike::query()->firstOrCreate([
                    'comment_id' => $comment->id,
                    'user_id' => $likeUserId,
                ]);
            }
        }

        $postCommentCounts = Comment::query()
            ->selectRaw('post_id, COUNT(*) as c')
            ->groupBy('post_id')
            ->pluck('c', 'post_id');

        $postLikeCounts = PostLike::query()
            ->where('is_starred', false)
            ->selectRaw('post_id, COUNT(*) as c')
            ->groupBy('post_id')
            ->pluck('c', 'post_id');

        foreach ($posts as $post) {
            $post->update([
                'comment_count' => (int) ($postCommentCounts[$post->id] ?? 0),
                'like_count' => (int) ($postLikeCounts[$post->id] ?? 0),
            ]);
        }

        $commentLikeCounts = CommentLike::query()
            ->selectRaw('comment_id, COUNT(*) as c')
            ->groupBy('comment_id')
            ->pluck('c', 'comment_id');

        foreach ($comments as $comment) {
            $comment->update(['like_count' => (int) ($commentLikeCounts[$comment->id] ?? 0)]);
        }
    }

    private function seedMailings($users): void
    {
        $userIds = $users->pluck('id')->values();
        if ($userIds->count() < 2) {
            return;
        }

        $subjects = [
            'Follow-up on your symptoms',
            'Medication timing reminder',
            'Weekly wellness plan',
            'Diet and hydration check',
            'Lab report discussion',
            'Blood pressure tracking note',
            'Community support update',
            'Appointment preparation tips',
            'Sleep and stress routine',
            'Exercise progress check',
            'Recovery milestone review',
            'Preventive care suggestions',
        ];

        $messageOpeners = [
            'I reviewed your recent health activity and wanted to share a quick update.',
            'Your latest records show a pattern worth monitoring this week.',
            'Thanks for sharing your logs. Here is a concise recommendation.',
            'This message summarizes your current plan and next steps.',
        ];

        $messagePlans = [
            'Please continue your medicines as prescribed and record each dose on time.',
            'Maintain hydration and keep your sleep routine consistent for the next few days.',
            'Track blood pressure and blood glucose twice daily and avoid skipped entries.',
            'If any symptom worsens, contact your physician and seek urgent care if needed.',
            'Focus on balanced meals and light physical activity unless advised otherwise.',
        ];

        $rows = [];
        $totalMailings = 260;
        $senderCursor = 0;

        for ($i = 0; $i < $totalMailings; $i++) {
            $senderId = $userIds[$senderCursor % $userIds->count()];
            $senderCursor++;

            $roll = random_int(1, 100);
            if ($roll <= 5) {
                $status = 'draft';
            } elseif ($roll <= 20) {
                $status = 'archived';
            } elseif ($roll <= 45) {
                $status = 'read';
            } elseif ($roll <= 70) {
                $status = 'sent';
            } else {
                $status = 'unread';
            }

            $receiverId = null;
            if ($status !== 'draft') {
                $receiverCandidates = $userIds->reject(fn ($id) => $id === $senderId)->values();
                $receiverId = $receiverCandidates->isNotEmpty() ? $receiverCandidates->random() : null;
            }

            $isRead = in_array($status, ['read', 'archived', 'sent'], true);
            $isStarred = random_int(1, 100) <= 14;
            $createdAt = now()->subDays(random_int(0, 240))->subMinutes(random_int(0, 1400));

            $rows[] = [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'title' => $subjects[array_rand($subjects)] . ' #' . random_int(100, 9999),
                'message' => implode("\n\n", [
                    $messageOpeners[array_rand($messageOpeners)],
                    $messagePlans[array_rand($messagePlans)],
                    $messagePlans[array_rand($messagePlans)],
                ]),
                'status' => $status,
                'is_read' => $isRead,
                'is_starred' => $isStarred,
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
        if (!Schema::hasTable('push_subscriptions')) {
            return;
        }

        $rows = [];
        foreach ($users->take(14) as $user) {
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

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('push_subscriptions')->insert($chunk);
        }
    }
}
