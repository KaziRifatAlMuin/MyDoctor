<?php

namespace App\Http\Controllers;

use App\Models\Mailing;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inbox(Request $request): View
    {
        $userId = $request->user()->id;

        $messages = Mailing::query()
            ->where('receiver_id', $userId)
            ->whereIn('status', ['unread', 'read'])
            ->with('sender')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('profile.inbox', [
            'messages' => $messages,
        ]);
    }

    public function sent(Request $request): View
    {
        $userId = $request->user()->id;

        $messages = Mailing::query()
            ->where('sender_id', $userId)
            ->whereIn('status', ['sent', 'unread', 'read', 'archived'])
            ->with('receiver')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('profile.sent', [
            'messages' => $messages,
        ]);
    }

    public function drafts(Request $request): View
    {
        $userId = $request->user()->id;

        $messages = Mailing::query()
            ->where('sender_id', $userId)
            ->where('status', 'draft')
            ->with('receiver')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('profile.drafts', [
            'messages' => $messages,
        ]);
    }

    public function starred(Request $request): View
    {
        $userId = $request->user()->id;

        $messages = Mailing::query()
            ->where(function ($query) use ($userId) {
                $query->where('receiver_id', $userId)
                    ->orWhere('sender_id', $userId);
            })
            ->where('is_starred', true)
            ->whereIn('status', ['unread', 'read', 'sent', 'archived'])
            ->with(['sender', 'receiver'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('profile.starred', [
            'messages' => $messages,
        ]);
    }

    public function archived(Request $request): View
    {
        $userId = $request->user()->id;

        $messages = Mailing::query()
            ->where('receiver_id', $userId)
            ->where('status', 'archived')
            ->with('sender')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('profile.archived', [
            'messages' => $messages,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $toUserId = $request->integer('to');
        $title = (string) $request->query('title', '');
        $message = '';
        $draftId = null;

        // Load draft if editing one
        if ($request->has('draft')) {
            $draft = Mailing::query()
                ->where('sender_id', $user->id)
                ->where('status', 'draft')
                ->findOrFail($request->integer('draft'));
            
            $toUserId = $draft->receiver_id ?? 0;
            $title = $draft->title;
            $message = $draft->message;
            $draftId = $draft->id;
        }

        $selectedRecipient = null;
        if ($toUserId) {
            $selectedRecipient = User::query()
                ->whereKeyNot($user->id)
                ->find($toUserId, ['id', 'name', 'email']);
        }

        return view('profile.compose', [
            'toUserId' => $toUserId,
            'selectedRecipient' => $selectedRecipient,
            'title' => $title,
            'message' => $message,
            'draftId' => $draftId,
        ]);
    }

    public function searchRecipients(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return response()->json([]);
        }

        $users = User::query()
            ->whereKeyNot($request->user()->id)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Mailing::query()
            ->where('receiver_id', $request->user()->id)
            ->where('status', 'unread')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'draft_id' => ['nullable', 'integer', 'exists:mailings,id'],
            'receiver_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $isSavingDraft = $request->has('save_draft');
        $draftId = $validated['draft_id'] ?? null;

        if (!$isSavingDraft && !$validated['receiver_id']) {
            return back()->withErrors(['receiver_id' => 'Recipient is required to send a message.'])->withInput();
        }

        if (!$isSavingDraft && $validated['receiver_id'] == $request->user()->id) {
            return back()->withErrors(['receiver_id' => 'You cannot send a message to yourself.'])->withInput();
        }

        // If updating a draft
        if ($draftId) {
            $draft = Mailing::query()
                ->where('sender_id', $request->user()->id)
                ->where('status', 'draft')
                ->findOrFail($draftId);
            
            $draft->update([
                'receiver_id' => $isSavingDraft ? null : $validated['receiver_id'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'status' => $isSavingDraft ? 'draft' : 'unread',
                'is_read' => false,
            ]);
        } else {
            Mailing::create([
                'sender_id' => $request->user()->id,
                'receiver_id' => $isSavingDraft ? null : $validated['receiver_id'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'status' => $isSavingDraft ? 'draft' : 'unread',
                'is_read' => false,
                'is_starred' => false,
            ]);
        }

        return redirect()
            ->route($isSavingDraft ? 'profile.mailbox.drafts' : 'profile.mailbox.sent')
            ->with('success', $isSavingDraft ? 'Draft saved successfully.' : 'Message sent successfully.');
    }

    public function show(Request $request, Mailing $mailing): View
    {
        $this->authorizeAccess($request, $mailing);

        if ($mailing->receiver_id === $request->user()->id && $mailing->status === 'unread') {
            $mailing->update(['status' => 'read', 'is_read' => true]);
        }

        $mailing->loadMissing(['sender', 'receiver']);

        return view('profile.message', [
            'mailing' => $mailing,
        ]);
    }

    public function updateStatus(Request $request, Mailing $mailing): RedirectResponse
    {
        $this->authorizeAccess($request, $mailing);

        $validated = $request->validate([
            'status' => ['required', 'in:unread,read,archived'],
        ]);

        // Only the receiver can change unread/read/archive state
        // Sender can only change drafts or delete
        if ($mailing->receiver_id === $request->user()->id) {
            if (!in_array($validated['status'], ['unread', 'read', 'archived'])) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $mailing->update([
            'status' => $validated['status'],
            'is_read' => $validated['status'] !== 'unread',
        ]);

        return back()->with('success', 'Message updated.');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mailing_ids' => ['required', 'array', 'min:1'],
            'mailing_ids.*' => ['integer', 'exists:mailings,id'],
            'status' => ['required', 'in:unread,read,archived'],
        ]);

        $userId = $request->user()->id;
        $ids = $validated['mailing_ids'];

        // Enforce ownership for all selected messages (no partial updates).
        if (in_array($validated['status'], ['read', 'unread'], true)) {
            $ownedCount = Mailing::query()
                ->whereIn('id', $ids)
                ->where('receiver_id', $userId)
                ->count();

            if ($ownedCount !== count($ids)) {
                abort(403);
            }
        } else {
            $ownedCount = Mailing::query()
                ->whereIn('id', $ids)
                ->where(function ($q) use ($userId) {
                    $q->where('receiver_id', $userId)
                      ->orWhere('sender_id', $userId);
                })
                ->count();

            if ($ownedCount !== count($ids)) {
                abort(403);
            }
        }

        // If marking read/unread, only the receiver may update those states.
        if (in_array($validated['status'], ['read', 'unread'], true)) {
            $updated = Mailing::query()
                ->where('receiver_id', $userId)
                ->whereIn('id', $ids)
                ->update([
                    'status' => $validated['status'],
                    'is_read' => $validated['status'] !== 'unread',
                ]);
        } else {
            // For archive action, allow either the receiver or the sender to archive their view.
            $updated = Mailing::query()
                ->whereIn('id', $ids)
                ->where(function ($q) use ($userId) {
                    $q->where('receiver_id', $userId)
                      ->orWhere('sender_id', $userId);
                })
                ->update([
                    'status' => $validated['status'],
                    'is_read' => $validated['status'] !== 'unread',
                ]);
        }

        if ($updated === 0) {
            return back()->with('success', 'No messages were updated.');
        }

        return back()->with('success', sprintf('%d message(s) updated.', $updated));
    }

    public function toggleStar(Request $request, Mailing $mailing): RedirectResponse
    {
        $this->authorizeAccess($request, $mailing);

        $mailing->update([
            'is_starred' => !$mailing->is_starred,
        ]);

        return back()->with('success', $mailing->is_starred ? 'Message starred.' : 'Message unstarred.');
    }

    public function destroy(Request $request, Mailing $mailing): RedirectResponse
    {
        $this->authorizeAccess($request, $mailing);

        $mailing->delete();

        return redirect()
            ->route('profile.mailbox')
            ->with('success', 'Message deleted.');
    }

    private function authorizeAccess(Request $request, Mailing $mailing): void
    {
        $userId = $request->user()->id;

        if ($mailing->sender_id !== $userId && $mailing->receiver_id !== $userId) {
            abort(403);
        }
    }
}
