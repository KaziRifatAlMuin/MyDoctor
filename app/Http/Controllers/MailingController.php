<?php

namespace App\Http\Controllers;

use App\Models\Mailing;
use App\Models\User;
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
            ->with('receiver')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('profile.sent', [
            'messages' => $messages,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $recipients = User::query()
            ->whereKeyNot($user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $toUserId = $request->integer('to');
        $title = (string) $request->query('title', '');

        return view('profile.compose', [
            'recipients' => $recipients,
            'toUserId' => $toUserId,
            'title' => $title,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id', 'different:' . $request->user()->id],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        Mailing::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $validated['receiver_id'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        return redirect()
            ->route('profile.inbox.sent')
            ->with('success', 'Message sent successfully.');
    }

    public function show(Request $request, Mailing $mailing): View
    {
        $this->authorizeAccess($request, $mailing);

        if ($mailing->receiver_id === $request->user()->id && $mailing->status === 'unread') {
            $mailing->update(['status' => 'read']);
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
        if ($mailing->receiver_id !== $request->user()->id) {
            abort(403);
        }

        $mailing->update(['status' => $validated['status']]);

        return back()->with('success', 'Message updated.');
    }

    public function destroy(Request $request, Mailing $mailing): RedirectResponse
    {
        $this->authorizeAccess($request, $mailing);

        $mailing->delete();

        return redirect()
            ->route('profile.inbox')
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
