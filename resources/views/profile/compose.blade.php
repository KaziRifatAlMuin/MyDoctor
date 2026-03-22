@extends('layouts.app')

@section('title', 'Compose Message - My Doctor')

@section('content')
    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('profile.mailbox.compose') }}"
                                class="list-group-item list-group-item-action active">
                                <i class="fas fa-pen-to-square me-2"></i>Compose
                            </a>
                            <a href="{{ route('profile.mailbox') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-inbox me-2"></i>Inbox
                            </a>
                            <a href="{{ route('profile.mailbox.drafts') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-file-lines me-2"></i>Drafts
                            </a>
                            <a href="{{ route('profile.mailbox.sent') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-paper-plane me-2"></i>Sent
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0"><i class="fas fa-pen-to-square me-2"></i>Compose</h4>
                    </div>

                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('profile.mailbox.store') }}" method="POST">
                            @csrf

                            @if ($draftId)
                                <input type="hidden" name="draft_id" value="{{ $draftId }}">
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-bold">To</label>
                                <select name="receiver_id" class="form-select" {{ $draftId ? '' : 'required' }}>
                                    <option value="" disabled {{ old('receiver_id', $toUserId) ? '' : 'selected' }}>
                                        Select a recipient</option>
                                    @foreach ($recipients as $recipient)
                                        <option value="{{ $recipient->id }}"
                                            {{ (string) old('receiver_id', $toUserId) === (string) $recipient->id ? 'selected' : '' }}>
                                            {{ $recipient->name }} ({{ $recipient->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Title</label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $title) }}" maxlength="255" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Message</label>
                                <textarea name="message" class="form-control" rows="8" maxlength="10000" required>{{ old('message', $message) }}</textarea>
                                <div class="form-text">Max 10,000 characters</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('profile.mailbox') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" name="save_draft" value="1" class="btn btn-outline-secondary">
                                    <i class="fas fa-file-lines me-2"></i>Save Draft
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
