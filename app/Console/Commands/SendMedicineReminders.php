<?php

namespace App\Console\Commands;

use App\Models\MedicineReminder;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Mail\MedicineReminderMail;
use Illuminate\Support\Facades\Mail;

class SendMedicineReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send medicine reminders 5 minutes before scheduled time via push and email';

    public function handle()
    {
        $now = now();
        
        // Check 4-6 minutes ahead to send 5 minutes before reminder
        $startTime = $now->copy()->addMinutes(4);
        $endTime = $now->copy()->addMinutes(6);
        
        $this->info('🔍 Checking for pending reminders...');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('📅 Current server time: ' . $now->format('Y-m-d H:i:s'));
        $this->line('⏰ Looking for reminders between:');
        $this->line('   From: ' . $startTime->format('Y-m-d H:i:s') . ' (4 mins ahead)');
        $this->line('   To:   ' . $endTime->format('Y-m-d H:i:s') . ' (6 mins ahead)');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Get reminders scheduled 4-6 minutes from now (to send 5 minutes before)
        $reminders = MedicineReminder::with(['schedule.medicine.user'])
            ->where('status', 'pending')
            ->whereBetween('reminder_at', [$startTime, $endTime])
            ->get();

        $this->line('🎯 Reminders in 4-6-min-ahead window (5 minutes before): ' . $reminders->count());
        
        if ($reminders->count() > 0) {
            $this->line('📋 Reminders to process now (will be sent 5 minutes before):');
            foreach ($reminders as $reminder) {
                $minutesUntil = $now->diffInMinutes($reminder->reminder_at, false);
                $this->line(sprintf(
                    '   ▶️  ID: %d | %s | %s | %s | (in %d min - sending 5 min before)',
                    $reminder->id,
                    $reminder->reminder_at->format('H:i:s'),
                    $reminder->schedule->medicine->medicine_name,
                    $reminder->status,
                    $minutesUntil
                ));
            }
        }

        // Get or create system user for notifications
        $systemUser = User::firstOrCreate(
            ['email' => 'system@mydoctor.com'],
            [
                'name' => 'System',
                'password' => bcrypt(uniqid()),
                'gender' => 'other',
                'role' => 'member',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $notificationCount = 0;
        $emailCount = 0;
        $skippedCount = 0;

        foreach ($reminders as $reminder) {
            try {
                $user = $reminder->schedule->medicine->user;
                
                if (!$user) {
                    $skippedCount++;
                    $this->warn("⚠️  No user found for reminder ID: {$reminder->id}");
                    continue;
                }

                $medicine = $reminder->schedule->medicine;
                $minutesUntil = $now->diffInMinutes($reminder->reminder_at, false);
                
                $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->line("💊 Processing: {$medicine->medicine_name}");
                $this->line("   👤 User: {$user->name} ({$user->email})");
                $this->line("   ⏰ Scheduled: {$reminder->reminder_at->format('H:i:s')}");
                $this->line("   🕒 Current: " . now()->format('H:i:s'));
                $this->line("   ⏱️  Time until reminder: {$minutesUntil} minutes");
                $this->line("   📢 Sending reminder 5 minutes before scheduled time");

                // Create database notification with TAKEN and NOT TAKEN buttons
                try {
                    $postPreview = "Time to take your medicine: {$medicine->medicine_name}";
                    
                    // Store both action URLs in notification data
                    Notification::create([
                        'user_id' => $user->id,
                        'from_user_id' => $systemUser->id,
                        'type' => 'medicine_reminder',
                        'notifiable_type' => MedicineReminder::class,
                        'notifiable_id' => $reminder->id,
                        'message' => $postPreview,
                        'data' => [
                            'type' => 'medicine_reminder',
                            'reminder_id' => $reminder->id,
                            'medicine_id' => $medicine->id,
                            'medicine_name' => $medicine->medicine_name,
                            'dosage' => $medicine->value_per_dose ? "{$medicine->value_per_dose} {$medicine->unit}" : null,
                            'scheduled_time' => $reminder->reminder_at->format('h:i A'),
                            'message' => $postPreview,
                            'action_url' => route('medicine.reminders'),
                            // TAKEN button URL
                            'taken_url' => route('medicine.reminders.taken-from-notification', $reminder->id),
                            'from_user_id' => $systemUser->id,
                            'from_user_name' => $systemUser->name,
                        ],
                    ]);
                    
                    $notificationCount++;
                    $this->info("   ✅ Database notification sent with Taken/Missed buttons");
                    Log::info("Medicine reminder database notification sent for reminder {$reminder->id} to user {$user->id}");
                    
                } catch (\Exception $e) {
                    $this->error("   ❌ Database notification failed: " . $e->getMessage());
                    Log::error("Medicine reminder database notification failed: " . $e->getMessage());
                    $skippedCount++;
                }

                // Send email notification manually (bypassing Laravel's notification system)
                if ($user->wantsEmailNotifications()) {
                    try {
                        Mail::to($user->email)->send(new \App\Mail\MedicineReminderMail($reminder));
                        $emailCount++;
                        $this->info("   ✅ Email notification sent");
                        Log::info("Medicine reminder email sent for reminder {$reminder->id} to user {$user->id}");
                    } catch (\Exception $e) {
                        $this->error("   ❌ Email notification failed: " . $e->getMessage());
                        Log::error("Medicine reminder email failed: " . $e->getMessage());
                    }
                } else {
                    $this->line("   ⏸️  Email disabled by user");
                }

            } catch (\Exception $e) {
                $this->error("❌ Error processing reminder: " . $e->getMessage());
                Log::error("Reminder send failed: " . $e->getMessage());
                $skippedCount++;
            }
        }

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->table(
            ['Type', 'Count'],
            [
                ['Database Notifications', $notificationCount],
                ['Email Notifications', $emailCount],
                ['Skipped/Failed', $skippedCount],
            ]
        );

        $this->info('✅ Completed at ' . now()->format('Y-m-d H:i:s'));
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        return 0;
    }
}