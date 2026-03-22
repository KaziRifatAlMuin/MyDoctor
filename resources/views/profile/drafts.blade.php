@extends('layouts.app')

@section('title', 'Drafts - My Doctor')
@section('main_content_class', 'main-content main-content--wide')

<!-- Gmail-like Custom Styles -->
<style>
    .gmail-layout { min-height: 66vh; display: flex; width: 100%; background-color: #f6f8fc; }
    .gmail-sidebar { flex: 0 0 256px; background-color: #f6f8fc; padding-top: 16px; display: flex; flex-direction: column; }
    .gmail-compose-btn { background-color: #c2e7ff; color: #001d35; border-radius: 16px; padding: 0 24px; height: 56px; font-weight: 500; font-size: 15px; border: none; display: inline-flex; align-items: center; gap: 12px; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); margin: 0 0 16px 8px; transition: box-shadow 0.2s, background-color 0.2s; text-decoration: none; width: max-content; }
    .gmail-compose-btn:hover { box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15); background-color: #b3d7f3; color: #001d35; }
    .gmail-nav { list-style: none; padding: 0; margin: 0; padding-right: 16px; }
    .gmail-nav-item { display: flex; align-items: center; padding: 0 12px 0 24px; height: 32px; border-radius: 0 16px 16px 0; color: #444746; text-decoration: none; font-size: 14px; margin-bottom: 2px; }
    .gmail-nav-item:hover { background-color: #e9eef6; }
    .gmail-nav-item.active { background-color: #d3e3fd; font-weight: 600; color: #0b57d0; }
    .gmail-nav-item i { margin-right: 18px; width: 20px; text-align: center; font-size: 16px; }
    .gmail-nav-item .badge { margin-left: auto; font-size: 12px; font-weight: 600; color: #444746; background: transparent !important; }
    
    .gmail-main { flex: 1 1 auto; width: calc(100% - 272px); min-width: 0; background-color: #fff; border-radius: 16px; margin: 0 16px 16px 0; min-height: 66vh; display: flex; flex-direction: column; overflow: hidden; }
    .gmail-toolbar { padding: 8px 16px; display: flex; align-items: center; border-bottom: 1px solid #f1f3f4; height: 48px; }
    .gmail-icon-btn { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #444746; text-decoration: none; border: none; background: transparent; transition: background-color 0.2s; }
    .gmail-icon-btn:hover { background-color: rgba(68,71,70,0.08); color: #444746; }
    
    .gmail-list { flex: 1; overflow-y: auto; }
    .gmail-row { display: flex; align-items: center; padding: 0 16px; height: 40px; border-bottom: 1px solid #f1f3f4; cursor: pointer; text-decoration: none; color: inherit; background-color: #fff; }
    .gmail-row:hover { box-shadow: inset 1px 0 0 #dadce0, inset -1px 0 0 #dadce0, 0 1px 2px 0 rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15); border-bottom-color: transparent; z-index: 1; position: relative; }
    .gmail-row-icons { display: flex; align-items: center; width: 60px; color: #c4c7c5; }
    .gmail-sender { width: 200px; font-size: 14px; font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 16px; color: #d93025; }
    .gmail-subject-container { flex: 1; display: flex; align-items: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 14px; }
    .gmail-subject { font-weight: 400; color: #1f1f1f; margin-right: 6px; }
    .gmail-snippet { color: #5f6368; font-weight: 400; }
    .gmail-date { width: 80px; text-align: right; font-size: 12px; font-weight: 400; color: #5f6368; }
    
    .gmail-action-btn { visibility: hidden; background: none; border: none; color: #5f6368; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; }
    .gmail-row:hover .gmail-date { display: none; }
    .gmail-row:hover .gmail-action-btn { visibility: visible; }
    .gmail-action-btn:hover { background-color: rgba(68,71,70,0.08); color: #1f1f1f; }
    
    .gmail-list::-webkit-scrollbar { width: 8px; }
    .gmail-list::-webkit-scrollbar-thumb { background-color: #dadce0; border-radius: 4px; }
</style>

@section('content')
<div class="gmail-layout">
    <!-- Sidebar -->
    <div class="gmail-sidebar">
        <a href="{{ route('profile.mailbox.compose') }}" class="gmail-compose-btn">
            <i class="fas fa-pen"></i> Compose
        </a>
        
        <ul class="gmail-nav">
            <li>
                <a href="{{ route('profile.mailbox') }}" class="gmail-nav-item">
                    <i class="fas fa-inbox"></i> Inbox
                    @php $unreadCount = \App\Models\Mailing::where('receiver_id', auth()->id())->where('status', 'unread')->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="badge">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('profile.mailbox.drafts') }}" class="gmail-nav-item active">
                    <i class="fas fa-file-alt"></i> Drafts
                </a>
            </li>
            <li>
                <a href="{{ route('profile.mailbox.sent') }}" class="gmail-nav-item">
                    <i class="fas fa-paper-plane"></i> Sent
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="gmail-main">
        <!-- Toolbar -->
        <div class="gmail-toolbar">
            <button class="gmail-icon-btn d-none d-md-flex me-2" onclick="window.location.reload();" title="Refresh">
                <i class="fas fa-redo-alt fs-6 text-muted"></i>
            </button>
            <div class="ms-auto d-flex align-items-center">
                @if ($messages->hasPages())
                    <span class="text-muted small me-3">{{ $messages->firstItem() }}-{{ $messages->lastItem() }} of {{ $messages->total() }}</span>
                    <a href="{{ $messages->previousPageUrl() }}" class="gmail-icon-btn {{ $messages->onFirstPage() ? 'disabled opacity-50' : '' }}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <a href="{{ $messages->nextPageUrl() }}" class="gmail-icon-btn {{ !$messages->hasMorePages() ? 'disabled opacity-50' : '' }}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @endif
            </div>
        </div>

        <!-- Email List -->
        <div class="gmail-list">
            @if(session('success'))
                <div class="alert alert-success m-3 py-2 px-3 border-0 bg-success bg-opacity-10 text-success rounded-3">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($messages as $message)
                <div class="gmail-row">
                    <div class="gmail-row-icons">
                        <i class="far fa-square me-3"></i>
                    </div>
                    
                    <a href="{{ route('profile.mailbox.compose', ['draft' => $message->id]) }}" class="gmail-sender text-decoration-none d-block">
                        Draft
                    </a>
                    
                    <a href="{{ route('profile.mailbox.compose', ['draft' => $message->id]) }}" class="gmail-subject-container text-decoration-none">
                        <span class="gmail-subject">{{ $message->title ?: '(No subject)' }}</span>
                        <span class="gmail-snippet">- {{ \Illuminate\Support\Str::limit($message->message, 80) }}</span>
                    </a>
                    
                    <div class="gmail-date">
                        {{ optional($message->created_at)->isToday() ? optional($message->created_at)->format('g:i A') : optional($message->created_at)->format('M j') }}
                    </div>
                    
                    <form method="POST" action="{{ route('profile.mailbox.destroy', $message) }}" class="d-inline mb-0 h-100" onsubmit="return confirm('Delete this draft?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="gmail-action-btn" title="Delete draft">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted opacity-75">
                    <i class="fas fa-file-alt mb-3" style="font-size: 3rem;"></i>
                    <h5>No draft messages</h5>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection