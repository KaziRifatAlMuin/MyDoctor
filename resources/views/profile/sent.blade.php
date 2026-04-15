@extends('layouts.app')

@section('title', __('ui.mailbox.sent_title'))
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

    .gmail-bulk-form { display: flex; align-items: center; gap: 8px; margin-left: 8px; }
    .gmail-bulk-select { width: 16px; height: 16px; cursor: pointer; }
    .gmail-bulk-btn { border: 1px solid #dadce0; background: #fff; color: #444746; font-size: 12px; border-radius: 999px; padding: 4px 10px; line-height: 1.2; }
    .gmail-bulk-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    
    .gmail-list { flex: 1; overflow-y: auto; }
    .gmail-row { display: flex; align-items: center; padding: 0 16px; height: 40px; border-bottom: 1px solid #f1f3f4; cursor: pointer; text-decoration: none; color: inherit; background-color: #fff; }
    .gmail-row:hover { box-shadow: inset 1px 0 0 #dadce0, inset -1px 0 0 #dadce0, 0 1px 2px 0 rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15); border-bottom-color: transparent; z-index: 1; position: relative; }
    .gmail-row-icons {
        display: flex;
        align-items: center;
        width: 72px;
        gap: 8px;
        padding-left: 8px;
        color: #c4c7c5;
        flex-shrink: 0;
        box-sizing: border-box;
        justify-content: flex-start;
    }

    .gmail-row-icons .gmail-row-select-sent {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .gmail-row-icons button { padding: 0; }
    .gmail-row > a { min-width: 0; }
    .gmail-sender { width: 200px; font-size: 14px; font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 16px; color: #1f1f1f; }
    .gmail-subject-container { flex: 1; display: flex; align-items: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 14px; }
    .gmail-subject { font-weight: 400; color: #1f1f1f; margin-right: 6px; }
    .gmail-snippet { color: #5f6368; font-weight: 400; }
    .gmail-date { width: 80px; text-align: right; font-size: 12px; font-weight: 400; color: #5f6368; }
    
    .gmail-list::-webkit-scrollbar { width: 8px; }
    .gmail-list::-webkit-scrollbar-thumb { background-color: #dadce0; border-radius: 4px; }
</style>

@section('content')
<div class="gmail-layout">
    <!-- Sidebar -->
    <div class="gmail-sidebar">
        <a href="{{ route('profile.mailbox.compose') }}" class="gmail-compose-btn">
            <i class="fas fa-pen"></i> {{ __('ui.mailbox.compose') }}
        </a>
        
        <ul class="gmail-nav">
            <li>
                <a href="{{ route('profile.mailbox') }}" class="gmail-nav-item">
                    <i class="fas fa-inbox"></i> {{ __('ui.mailbox.inbox') }}
                    @php $unreadCount = \App\Models\Mailing::where('receiver_id', auth()->id())->where('status', 'unread')->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="badge">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('profile.mailbox.drafts') }}" class="gmail-nav-item">
                    <i class="fas fa-file-alt"></i> {{ __('ui.mailbox.drafts') }}
                </a>
            </li>
            <li>
                <a href="{{ route('profile.mailbox.sent') }}" class="gmail-nav-item active">
                    <i class="fas fa-paper-plane"></i> {{ __('ui.mailbox.sent') }}
                </a>
            </li>
            <li>
                <a href="{{ route('profile.mailbox.starred') }}" class="gmail-nav-item">
                    <i class="fas fa-star"></i> {{ __('ui.mailbox.starred') }}
                </a>
            </li>
            <li>
                <a href="{{ route('profile.mailbox.archived') }}" class="gmail-nav-item">
                    <i class="fas fa-box-archive"></i> {{ __('ui.mailbox.archived') }}
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="gmail-main">
        <!-- Toolbar -->
        <div class="gmail-toolbar">
            <button class="gmail-icon-btn d-none d-md-flex me-2" onclick="window.location.reload();" title="{{ __('ui.mailbox.refresh') }}">
                <i class="fas fa-redo-alt fs-6 text-muted"></i>
            </button>
            <form id="bulkStatusFormSent" method="POST" action="{{ route('profile.mailbox.bulk-status') }}" class="gmail-bulk-form">
                @csrf
                @method('PATCH')
                <input id="bulkSelectAllSent" type="checkbox" class="gmail-bulk-select" title="{{ __('ui.mailbox.select_all') }}">
                <input type="hidden" name="status" id="bulkStatusInputSent" value="">
                <button type="button" class="gmail-bulk-btn bulk-action-btn-sent" data-status="archived" disabled>{{ __('ui.mailbox.archive') }}</button>
            </form>

            <div class="ms-auto d-flex align-items-center">
                @if ($messages->hasPages())
                    <span class="text-muted small me-3">{{ $messages->firstItem() }}-{{ $messages->lastItem() }} {{ __('ui.mailbox.of') }} {{ $messages->total() }}</span>
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
                        <input type="checkbox" class="gmail-row-select-sent" name="mailing_ids[]" value="{{ $message->id }}" form="bulkStatusFormSent" title="{{ __('ui.mailbox.select_message') }}">

                        <form method="POST" action="{{ route('profile.mailbox.star', $message) }}" class="d-inline" onclick="event.stopPropagation();">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="p-0 border-0 bg-transparent" title="{{ __('ui.mailbox.toggle_star') }}">
                                <i class="{{ $message->is_starred ? 'fas text-warning' : 'far' }} fa-star"></i>
                            </button>
                        </form>
                    </div>

                    <a href="{{ route('profile.mailbox.show', ['mailing' => $message->id, 'folder' => 'sent']) }}" class="d-flex align-items-center flex-grow-1 text-decoration-none text-reset">
                        <div class="gmail-sender">
                            {{ __('ui.mailbox.to_label') }}: {{ $message->receiver?->name ?? __('ui.mailbox.unknown_user') }}
                        </div>

                        <div class="gmail-subject-container">
                            <span class="gmail-subject">{{ $message->title ?: __('ui.mailbox.no_subject') }}</span>
                            <span class="gmail-snippet">- {{ \Illuminate\Support\Str::limit($message->message, 80) }}</span>
                        </div>

                        <div class="gmail-date">
                            {{ optional($message->created_at)->isToday() ? optional($message->created_at)->format('g:i A') : optional($message->created_at)->format('M j') }}
                        </div>
                    </a>
                </div>
            @empty
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted opacity-75">
                    <i class="fas fa-paper-plane mb-3" style="font-size: 3rem;"></i>
                    <h5>{{ __('ui.mailbox.no_sent_messages') }}</h5>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('bulkSelectAllSent');
            const rowChecks = Array.from(document.querySelectorAll('.gmail-row-select-sent'));
            const actionButtons = Array.from(document.querySelectorAll('.bulk-action-btn-sent'));
            const bulkStatusInput = document.getElementById('bulkStatusInputSent');
            const bulkForm = document.getElementById('bulkStatusFormSent');

            if (!bulkForm) return;

            const syncActions = () => {
                const selectedCount = rowChecks.filter((box) => box.checked).length;
                const hasSelection = selectedCount > 0;
                actionButtons.forEach((btn) => btn.disabled = !hasSelection);
                if (!hasSelection) selectAll.checked = false;
                else selectAll.checked = rowChecks.every((box) => box.checked);
            };

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    rowChecks.forEach((box) => box.checked = selectAll.checked);
                    syncActions();
                });
            }

            rowChecks.forEach((box) => box.addEventListener('change', syncActions));

            actionButtons.forEach((btn) => btn.addEventListener('click', function () {
                if (btn.disabled) return;
                bulkStatusInput.value = btn.dataset.status || '';
                bulkForm.submit();
            }));

            syncActions();
        });
    </script>
</div>
@endsection