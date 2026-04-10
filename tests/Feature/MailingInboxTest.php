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
        $this->get(route('profile.mailbox'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_inbox(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.mailbox'))
            ->assertStatus(200)
            ->assertViewIs('profile.inbox');
    }

    #[Test]
    public function user_can_send_message_and_receiver_sees_it_in_inbox(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('profile.mailbox.store'), [
                'receiver_id' => $receiver->id,
                'title' => 'Hello',
                'message' => 'Test message',
            ])
            ->assertRedirect(route('profile.mailbox.sent'));

        $this->assertDatabaseHas('mailings', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'Hello',
            'status' => 'unread',
        ]);

        $this->actingAs($receiver)
            ->get(route('profile.mailbox'))
            ->assertSee('Hello')
            ->assertSee($sender->name);
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
            ->get(route('profile.mailbox.show', $mailing))
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
            ->patch(route('profile.mailbox.status', $mailing), ['status' => 'archived'])
            ->assertStatus(403);

        $this->actingAs($receiver)
            ->patch(route('profile.mailbox.status', $mailing), ['status' => 'archived'])
            ->assertRedirect();

        $this->assertDatabaseHas('mailings', [
            'id' => $mailing->id,
            'status' => 'archived',
        ]);
    }

    #[Test]
    public function receiver_can_mark_read_and_unread_and_badge_count_changes(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $mailing = Mailing::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'Toggle state',
            'message' => 'Body',
            'status' => 'unread',
            'is_read' => false,
        ]);

        $this->actingAs($receiver)
            ->getJson(route('profile.mailbox.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 1]);

        $this->actingAs($receiver)
            ->patch(route('profile.mailbox.status', $mailing), ['status' => 'read'])
            ->assertRedirect();

        $this->assertDatabaseHas('mailings', [
            'id' => $mailing->id,
            'status' => 'read',
            'is_read' => true,
        ]);

        $this->actingAs($receiver)
            ->getJson(route('profile.mailbox.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 0]);

        $this->actingAs($receiver)
            ->patch(route('profile.mailbox.status', $mailing), ['status' => 'unread'])
            ->assertRedirect();

        $this->assertDatabaseHas('mailings', [
            'id' => $mailing->id,
            'status' => 'unread',
            'is_read' => false,
        ]);

        $this->actingAs($receiver)
            ->getJson(route('profile.mailbox.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    #[Test]
    public function mailbox_unread_count_endpoint_returns_count_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Mailing::create([
            'sender_id' => $other->id,
            'receiver_id' => $user->id,
            'title' => 'Unread one',
            'message' => 'Body',
            'status' => 'unread',
        ]);

        Mailing::create([
            'sender_id' => $other->id,
            'receiver_id' => $user->id,
            'title' => 'Read one',
            'message' => 'Body',
            'status' => 'read',
        ]);

        Mailing::create([
            'sender_id' => $user->id,
            'receiver_id' => $other->id,
            'title' => 'Outgoing',
            'message' => 'Body',
            'status' => 'unread',
        ]);

        $this->actingAs($user)
            ->getJson(route('profile.mailbox.unread-count'))
            ->assertOk()
            ->assertJson([
                'count' => 1,
            ]);
    }

    #[Test]
    public function receiver_can_bulk_mark_messages_as_read(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $first = Mailing::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'Unread one',
            'message' => 'Body',
            'status' => 'unread',
            'is_read' => false,
        ]);

        $second = Mailing::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'Unread two',
            'message' => 'Body',
            'status' => 'unread',
            'is_read' => false,
        ]);

        $this->actingAs($receiver)
            ->patch(route('profile.mailbox.bulk-status'), [
                'mailing_ids' => [$first->id, $second->id],
                'status' => 'read',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('mailings', [
            'id' => $first->id,
            'status' => 'read',
            'is_read' => true,
        ]);

        $this->assertDatabaseHas('mailings', [
            'id' => $second->id,
            'status' => 'read',
            'is_read' => true,
        ]);
    }

    #[Test]
    public function sender_cannot_bulk_mark_sent_messages_as_read_or_unread(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $sent = Mailing::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'title' => 'Sent item',
            'message' => 'Body',
            'status' => 'unread',
            'is_read' => false,
        ]);

        $this->actingAs($sender)
            ->patch(route('profile.mailbox.bulk-status'), [
                'mailing_ids' => [$sent->id],
                'status' => 'read',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('mailings', [
            'id' => $sent->id,
            'status' => 'unread',
            'is_read' => false,
        ]);
    }
}
