@extends('layouts.app')

@section('title', 'Medicine Reminders')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-pills me-2"></i>Medicine Reminder Notifications</h4>
                </div>
                <div class="card-body">
                    @if($notifications->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No medicine reminder notifications yet.</p>
                        </div>
                    @else
                        <div class="list-group">
                            @foreach($notifications as $notification)
                                <div class="list-group-item {{ $notification->read_at ? '' : 'bg-light' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-pills text-success me-2"></i>
                                                {{ $notification->data['medicine_name'] ?? 'Medicine Reminder' }}
                                            </h6>
                                            <p class="mb-1">{{ $notification->data['message'] ?? 'Time to take your medicine' }}</p>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                                @if($notification->data['scheduled_time'] ?? false)
                                                    • Scheduled: {{ $notification->data['scheduled_time'] }}
                                                @endif
                                            </small>
                                        </div>
                                        @if(!$notification->read_at)
                                            <span class="badge bg-primary rounded-pill">New</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection