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

        // Ensure deterministic core mailbox states exist.
        $senderId = $userIds[0];
        $receiverId = $userIds[1];

        Mailing::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'title' => 'Welcome unread message',
            'message' => 'This seeded message is intentionally unread.',
            'status' => 'unread',
            'is_read' => false,
            'is_starred' => false,
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        Mailing::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'title' => 'Welcome read message',
            'message' => 'This seeded message is intentionally read.',
            'status' => 'read',
            'is_read' => true,
            'is_starred' => true,
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(5),
        ]);

        Mailing::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'title' => 'Welcome archived message',
            'message' => 'This seeded message is intentionally archived.',
            'status' => 'archived',
            'is_read' => true,
            'is_starred' => false,
            'created_at' => now()->subHours(7),
            'updated_at' => now()->subHours(7),
        ]);

        Mailing::create([
            'sender_id' => $senderId,
            'receiver_id' => null,
            'title' => 'Welcome draft message',
            'message' => 'This seeded message is intentionally a draft.',
            'status' => 'draft',
            'is_read' => false,
            'is_starred' => false,
            'created_at' => now()->subHours(9),
            'updated_at' => now()->subHours(9),
        ]);

        // Create sample Gmail-like mailbox data between existing users.
        for ($i = 0; $i < 45; $i++) {
            $senderId = $userIds[array_rand($userIds)];
            $receiverId = $userIds[array_rand($userIds)];

            if ($senderId === $receiverId) {
                $i--;
                continue;
            }

            $createdAt = now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440));
            $status = fake()->randomElement(['unread', 'read', 'archived', 'sent']);
            $isRead = in_array($status, ['read', 'archived', 'sent'], true);
            $isStarred = fake()->boolean(20);

            Mailing::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'title' => fake()->sentence(6),
                'message' => fake()->paragraphs(rand(1, 3), true),
                'status' => $status,
                'is_read' => $isRead,
                'is_starred' => $isStarred,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // Create sample drafts for random users.
        for ($i = 0; $i < 12; $i++) {
            $senderId = $userIds[array_rand($userIds)];
            $createdAt = now()->subDays(rand(0, 20))->subMinutes(rand(0, 1440));

            Mailing::create([
                'sender_id' => $senderId,
                'receiver_id' => null,
                'title' => fake()->sentence(5),
                'message' => fake()->paragraphs(rand(1, 2), true),
                'status' => 'draft',
                'is_read' => false,
                'is_starred' => fake()->boolean(10),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
