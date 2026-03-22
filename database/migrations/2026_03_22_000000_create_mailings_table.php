<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('recipient_email');
            $table->string('subject');
            $table->longText('body');
            $table->string('mailable_type')->nullable(); // e.g., 'MedicineReminderMail', 'NotificationMail'
            $table->unsignedBigInteger('mailable_id')->nullable(); // ID of the related model
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailings');
    }
};
