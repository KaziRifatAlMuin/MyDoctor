@extends('layouts.app')

@section('title', 'Message - My Doctor')

@section('content')
    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <a href="{{ route('profile.notifications') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-bell me-2"></i>Notifications
                            </a>
                            <a href="{{ route('profile.inbox') }}" class="list-group-item list-group-item-action active">
                                <i class="fas fa-inbox me-2"></i>Inbox
                            </a>
                            <a href="{{ route('profile.inbox.sent') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-paper-plane me-2"></i>Sent
                            </a>
                            <a href="{{ route('profile.inbox.compose') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-pen-to-square me-2"></i>Compose
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i>Message</h4>
                        <a href="{{ route('profile.inbox') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>

                    <div class="card-body p-4">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-between">
                                <div>
                                    <div class="text-muted small">From</div>
                                    <div class="fw-semibold">{{ $mailing->sender?->name ?? 'Unknown' }} <span
                                            class="text-muted">({{ $mailing->sender?->email }})</span></div>
                                </div>
                                <div>
                                    <div class="text-muted small">To</div>
                                    <div class="fw-semibold">{{ $mailing->receiver?->name ?? 'Unknown' }} <span
                                            class="text-muted">({{ $mailing->receiver?->email }})</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Title</div>
                            <div class="fw-bold">{{ $mailing->title }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Date</div>
                            <div>{{ optional($mailing->created_at)->format('M d, Y h:i A') }}</div>
                        </div>

                        <div class="mb-4">
                            <div class="text-muted small">Message</div>
                            <div class="border rounded-3 p-3" style="white-space: pre-wrap;">{{ $mailing->message }}</div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            @php
                                $replyTo = $mailing->sender_id;
                                $replyTitle = str_starts_with($mailing->title, 'Re:')
                                    ? $mailing->title
                                    : 'Re: ' . $mailing->title;
                            @endphp
                            <a href="{{ route('profile.inbox.compose', ['to' => $replyTo, 'title' => $replyTitle]) }}"
                                class="btn btn-outline-primary">
                                <i class="fas fa-reply me-2"></i>Reply
                            </a>

                            @if (auth()->id() === $mailing->receiver_id)
                                <form method="POST" action="{{ route('profile.inbox.status', $mailing) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="unread">
                                    <button type="submit" class="btn btn-outline-warning">
                                        <i class="fas fa-envelope me-2"></i>Mark Unread
                                    </button>
                                </form>

                                @if ($mailing->status !== 'archived')
                                    <form method="POST" action="{{ route('profile.inbox.status', $mailing) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="archived">
                                        <button type="submit" class="btn btn-outline-secondary">
                                            <i class="fas fa-box-archive me-2"></i>Archive
                                        </button>
                                    </form>
                                @endif
                            @endif

                            <form method="POST" action="{{ route('profile.inbox.destroy', $mailing) }}"
                                onsubmit="return confirm('Delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
