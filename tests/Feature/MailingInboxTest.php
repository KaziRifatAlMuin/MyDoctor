<?php

namespace Tests\Feature;

use App\Models\Mailing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MailingInboxTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_are_redirected_from_inbox(): void
    {
        $this->get(route('profile.inbox'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_inbox(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.inbox'))
            ->assertStatus(200)
            ->assertViewIs('profile.inbox');
    }

    #[Test]
    public function user_can_send_message_and_receiver_sees_it_in_inbox(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('profile.inbox.store'), [
                'receiver_id' => $receiver->id,
                'title' => 'Hello',
                'message' => 'Test message',
            ])
            ->assertRedirect(route('profile.inbox.sent'));

        $this->assertDatabaseHas('mailings', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'Hello',
            'status' => 'unread',
        ]);

        $this->actingAs($receiver)
            ->get(route('profile.inbox'))
            ->assertSee('Hello')
            ->assertSee($sender->email);
    }

    #[Test]
    public function viewing_a_message_marks_it_as_read_for_receiver(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $mailing = Mailing::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'Mark read',
            'message' => 'Body',
            'status' => 'unread',
        ]);

        $this->actingAs($receiver)
            ->get(route('profile.inbox.show', $mailing))
            ->assertStatus(200);

        $this->assertDatabaseHas('mailings', [
            'id' => $mailing->id,
            'status' => 'read',
        ]);
    }

    #[Test]
    public function receiver_can_archive_message_but_sender_cannot(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $mailing = Mailing::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'Archive me',
            'message' => 'Body',
            'status' => 'unread',
        ]);

        $this->actingAs($sender)
            ->patch(route('profile.inbox.status', $mailing), ['status' => 'archived'])
            ->assertStatus(403);

        $this->actingAs($receiver)
            ->patch(route('profile.inbox.status', $mailing), ['status' => 'archived'])
            ->assertRedirect();

        $this->assertDatabaseHas('mailings', [
            'id' => $mailing->id,
            'status' => 'archived',
        ]);
    }
}
