@extends('layouts.app')

@section('title', 'Sent Messages - My Doctor')

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
                            <a href="{{ route('profile.inbox') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-inbox me-2"></i>Inbox
                            </a>
                            <a href="{{ route('profile.inbox.sent') }}"
                                class="list-group-item list-group-item-action active">
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
                        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Sent</h4>
                        <a href="{{ route('profile.inbox.compose') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-pen-to-square me-1"></i>New Message
                        </a>
                    </div>

                    <div class="card-body p-4">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($messages->count() === 0)
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-paper-plane fa-2x mb-3"></i>
                                <div class="fw-semibold">No sent messages yet</div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>To</th>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($messages as $message)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $message->receiver?->name ?? 'Unknown' }}
                                                    </div>
                                                    <div class="text-muted small">{{ $message->receiver?->email }}</div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('profile.inbox.show', $message) }}"
                                                        class="text-decoration-none">
                                                        {{ $message->title }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @php
                                                        $badgeClass = match ($message->status) {
                                                            'unread' => 'bg-warning text-dark',
                                                            'read' => 'bg-success',
                                                            'archived' => 'bg-secondary',
                                                            default => 'bg-light text-dark',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ ucfirst($message->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-muted">
                                                    {{ optional($message->created_at)->format('M d, Y') }}
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-2 justify-content-end">
                                                        <a href="{{ route('profile.inbox.show', $message) }}"
                                                            class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('profile.inbox.destroy', $message) }}"
                                                            onsubmit="return confirm('Delete this message?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($messages->hasPages())
                                <div class="d-flex justify-content-end">
                                    {{ $messages->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
