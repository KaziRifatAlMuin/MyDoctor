<?php

namespace Database\Seeders;

use App\Models\Mailing;
use App\Models\User;
use Illuminate\Database\Seeder;

class MailingSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->all();

        if (count($userIds) < 2) {
            return;
        }

        // Create a handful of sample inbox messages between existing users.
        for ($i = 0; $i < 30; $i++) {
            $senderId = $userIds[array_rand($userIds)];
            $receiverId = $userIds[array_rand($userIds)];

            if ($senderId === $receiverId) {
                $i--;
                continue;
            }

            $createdAt = now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440));

            Mailing::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'title' => fake()->sentence(6),
                'message' => fake()->paragraphs(rand(1, 3), true),
                'status' => fake()->randomElement(['unread', 'read', 'archived']),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
