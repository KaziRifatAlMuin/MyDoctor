@extends('layouts.app')

@section('title', 'Read Message - My Doctor')
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
    
    .gmail-read-container { flex: 1; overflow-y: auto; padding: 20px 32px 64px 32px; }
    
    .gmail-subject-header { font-size: 22px; font-weight: 400; color: #1f1f1f; display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; padding-left: 56px; }
    
    .gmail-message-header { display: flex; align-items: center; margin-bottom: 16px; }
    .gmail-avatar { width: 40px; height: 40px; border-radius: 50%; background-color: #a8c7fa; color: #041e49; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 500; margin-right: 16px; flex-shrink: 0; }
    .gmail-sender-info { flex: 1; }
    .gmail-sender-name { font-size: 14px; font-weight: 700; color: #1f1f1f; display: inline-flex; align-items: center; }
    .gmail-sender-email { font-size: 12px; color: #5f6368; margin-left: 8px; font-weight: 400; }
    .gmail-date-info { font-size: 12px; color: #5f6368; display: flex; align-items: center; gap: 12px; margin-left: auto; }
    
    .gmail-message-body { font-size: 14px; color: #1f1f1f; line-height: 1.5; padding-left: 56px; white-space: pre-wrap; font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif; }
    
    .gmail-reply-box { margin-top: 32px; margin-left: 56px; border: 1px solid #dadce0; border-radius: 24px; padding: 12px 16px; cursor: pointer; display: flex; align-items: flex-start; max-width: 600px; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3); transition: box-shadow 0.2s; }
    .gmail-reply-box:hover { box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15); }
    .gmail-reply-avatar { width: 32px; height: 32px; border-radius: 50%; background-color: #e9eef6; display: flex; align-items: center; justify-content: center; margin-right: 12px; }
    .gmail-reply-text { font-size: 14px; color: #5f6368; padding-top: 6px; }
    
    .gmail-reply-btn-area { margin-top: 24px; margin-left: 56px; display: flex; gap: 8px; }
    .gmail-action-btn-pill { border: 1px solid #747775; border-radius: 18px; padding: 0 24px; height: 36px; font-size: 14px; font-weight: 500; color: #444746; background: transparent; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: background-color 0.2s; }
    .gmail-action-btn-pill:hover { background-color: rgba(68,71,70,0.08); color: #444746; }

    /* Scrollbar */
    .gmail-read-container::-webkit-scrollbar { width: 8px; }
    .gmail-read-container::-webkit-scrollbar-thumb { background-color: #dadce0; border-radius: 4px; }
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
                <a href="{{ route('profile.mailbox') }}" class="gmail-nav-item {{ request('folder') !== 'sent' ? 'active' : '' }}">
                    <i class="fas fa-inbox"></i> Inbox
                    @php $unreadCount = \App\Models\Mailing::where('receiver_id', auth()->id())->where('status', 'unread')->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="badge">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('profile.mailbox.drafts') }}" class="gmail-nav-item">
                    <i class="fas fa-file-alt"></i> Drafts
                </a>
            </li>
            <li>
                <a href="{{ route('profile.mailbox.sent') }}" class="gmail-nav-item {{ request('folder') === 'sent' ? 'active' : '' }}">
                    <i class="fas fa-paper-plane"></i> Sent
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="gmail-main">
        <!-- Toolbar -->
        <div class="gmail-toolbar">
            <a href="{{ request('folder') === 'sent' ? route('profile.mailbox.sent') : route('profile.mailbox') }}" class="gmail-icon-btn me-3" title="Back to {{ request('folder') === 'sent' ? 'Sent' : 'Inbox' }}">
                <i class="fas fa-arrow-left"></i>
            </a>
            
            <form method="POST" action="{{ route('profile.mailbox.destroy', $message) }}" class="d-inline mb-0" onsubmit="return confirm('Delete this message?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="gmail-icon-btn" title="Delete">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>
            
            <div class="ms-auto d-flex align-items-center gap-1">
                <!-- Additional toolbar actions like print etc. could go here -->
            </div>
        </div>

        <!-- Read Container -->
        <div class="gmail-read-container">
            <div class="gmail-subject-header">
                {{ $message->title ?: '(No subject)' }}
            </div>
            
            <div class="gmail-message-header">
                @if (request('folder') === 'sent')
                    <div class="gmail-avatar bg-info bg-opacity-25 text-info">
                        {{ strtoupper(substr($message->receiver?->name ?? '?', 0, 1)) }}
                    </div>
                @else
                    <div class="gmail-avatar">
                        {{ strtoupper(substr($message->sender?->name ?? 'S', 0, 1)) }}
                    </div>
                @endif
                
                <div class="gmail-sender-info">
                    <div class="gmail-sender-name">
                        @if (request('folder') === 'sent')
                            To: {{ $message->receiver?->name ?? 'Unknown user' }}
                            <span class="gmail-sender-email">&lt;{{ $message->receiver?->email ?? 'no-email' }}&gt;</span>
                        @else
                            {{ $message->sender?->name ?? 'System User' }}
                            <span class="gmail-sender-email">&lt;{{ $message->sender?->email ?? 'noreply@system.com' }}&gt;</span>
                        @endif
                    </div>
                </div>
                
                <div class="gmail-date-info">
                    {{ optional($message->created_at)->format('M j, Y, g:i A') }} ({{ optional($message->created_at)->diffForHumans() }})
                    <div class="ms-3 d-flex gap-2">
                        <a href="{{ route('profile.mailbox.compose', ['reply_to' => $message->id]) }}" class="gmail-icon-btn shadow-none m-0" style="width: 24px; height: 24px; color: #5f6368;" title="Reply">
                            <i class="fas fa-reply fs-6"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="gmail-message-body">
                @if($message->message)
{{ $message->message }}
                @else
<em class="text-muted">No content provided.</em>
                @endif
            </div>
            
            <!-- Quick Reply Action -->
            <div class="gmail-reply-btn-area">
                <a href="{{ route('profile.mailbox.compose', ['to' => request('folder') === 'sent' ? $message->receiver_id : $message->sender_id, 'title' => str_starts_with($message->title, 'Re:') ? $message->title : 'Re: ' . $message->title]) }}" class="gmail-action-btn-pill">
                    <i class="fas fa-reply"></i> Reply
                </a>
            </div>
        </div>
    </div>
</div>
@endsection