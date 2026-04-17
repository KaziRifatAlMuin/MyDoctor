<?php

namespace App\Console\Commands;

use App\Models\MedicineReminder;
use App\Notifications\MedicineEmailNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

        // Get ALL pending reminders for debugging
        $allPending = MedicineReminder::with(['schedule.medicine.user'])
            ->where('status', 'pending')
            ->orderBy('reminder_at')
            ->get();
            
        $this->line('📊 Total pending reminders in database: ' . $allPending->count());
        
        if ($allPending->count() > 0) {
            $this->line('📋 All pending reminders:');
            foreach ($allPending as $reminder) {
                $minutesUntil = $now->diffInMinutes($reminder->reminder_at, false);
                $status = $minutesUntil < 0 ? '🔴 PAST' : 
                         ($minutesUntil <= 6 && $minutesUntil >= 4 ? '🟢 NOW (5min)' : 
                         ($minutesUntil < 4 ? '🟡 VERY SOON' : '🟡 FUTURE'));
                $this->line(sprintf(
                    '   %s ID: %d | %s | %s | %s (%d min from now)',
                    $status,
                    $reminder->id,
                    $reminder->reminder_at->format('H:i:s'),
                    $reminder->schedule->medicine->medicine_name,
                    $reminder->status,
                    $minutesUntil
                ));
            }
        }

        // Get reminders scheduled 4-6 minutes from now (to send 5 minutes before)
        $reminders = MedicineReminder::with(['schedule.medicine.user'])
            ->where('status', 'pending')
            ->whereBetween('reminder_at', [$startTime, $endTime])
            ->get();

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
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

                $minutesUntil = $now->diffInMinutes($reminder->reminder_at, false);
                
                $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->line("💊 Processing: {$reminder->schedule->medicine->medicine_name}");
                $this->line("   👤 User: {$user->name} ({$user->email})");
                $this->line("   ⏰ Scheduled: {$reminder->reminder_at->format('H:i:s')}");
                $this->line("   🕒 Current: " . now()->format('H:i:s'));
                $this->line("   ⏱️  Time until reminder: {$minutesUntil} minutes");
                $this->line("   📢 Sending reminder 5 minutes before scheduled time");

                // Send email notification
                if ($user->wantsEmailNotifications()) {
                    try {
                        $user->notify(new MedicineEmailNotification($reminder));
                        $emailCount++;
                        $this->info("   ✅ Email queued (5 minutes before)");
                        Log::info("Email queued for reminder {$reminder->id} to user {$user->id} (5 minutes before)");
                    } catch (\Exception $e) {
                        $this->error("   ❌ Email failed: " . $e->getMessage());
                        Log::error("Email failed: " . $e->getMessage());
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
                ['Emails (5 min before)', $emailCount],
                ['Skipped', $skippedCount],
            ]
        );

        $this->info('✅ Completed at ' . now()->format('Y-m-d H:i:s'));
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        return 0;
    }
}