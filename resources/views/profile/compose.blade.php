@extends('layouts.app')

@section('title', 'Compose Message - My Doctor')
@section('main_content_class', 'main-content main-content--wide')

<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
    
    .gmail-main { flex: 1 1 auto; width: calc(100% - 272px); min-width: 0; background-color: #fff; border-radius: 16px; margin: 0 16px 16px 0; min-height: 66vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3); }
    .gmail-toolbar { padding: 8px 16px; display: flex; align-items: center; border-bottom: 1px solid #f1f3f4; height: 48px; background-color: #f2f6fc; }
    .gmail-icon-btn { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #444746; text-decoration: none; border: none; background: transparent; transition: background-color 0.2s; }
    .gmail-icon-btn:hover { background-color: rgba(68,71,70,0.08); color: #444746; }
    
    .gmail-compose-container { flex: 1; overflow-y: auto; padding: 0; display: flex; flex-direction: column; }
    
    .gmail-compose-header { background-color: #f2f6fc; padding: 10px 16px; font-weight: 500; font-size: 14px; color: #1f1f1f; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f3f4; }
    
    .gmail-input-row { display: flex; border-bottom: 1px solid #f1f3f4; padding: 0 16px; align-items: center; min-height: 40px; }
    .gmail-input-label { color: #5f6368; width: 40px; font-size: 14px; margin-right: 8px; }
    .gmail-input-field { flex: 1; border: none; outline: none; font-size: 14px; color: #1f1f1f; padding: 8px 0; background: transparent; }
    
    .gmail-editor { flex: 1; padding: 16px; border: none; outline: none; font-size: 14px; resize: none; width: 100%; font-family: inherit; }
    
    .gmail-compose-footer { padding: 12px 16px; border-top: 1px solid #f1f3f4; display: flex; align-items: center; background-color: #fff; }
    .gmail-send-btn { background-color: #0b57d0; color: #fff; border: none; border-radius: 18px; padding: 0 24px; height: 36px; font-weight: 500; font-size: 14px; cursor: pointer; transition: background-color 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .gmail-send-btn:hover { background-color: #0842a0; }
    
    /* Select2 customization to look like flat input */
    .select2-container--default .select2-selection--single { background-color: transparent; border: none; outline: none; height: auto; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left: 0; line-height: normal; font-size: 14px; color: #1f1f1f; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { display: none; }
    
    /* Scrollbar */
    .gmail-compose-container::-webkit-scrollbar { width: 8px; }
    .gmail-compose-container::-webkit-scrollbar-thumb { background-color: #dadce0; border-radius: 4px; }
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
                <a href="{{ route('profile.mailbox.drafts') }}" class="gmail-nav-item">
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
        <div class="gmail-compose-header">
            <span>New Message</span>
            <a href="{{ route('profile.mailbox') }}" class="gmail-icon-btn d-inline-flex" style="width: 24px; height: 24px;">
                <i class="fas fa-times fs-6"></i>
            </a>
        </div>
        
        @if ($errors->any())
            <div class="alert alert-danger m-3 py-2 px-3 border-0 rounded-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.mailbox.store') }}" class="gmail-compose-container h-100" id="composeForm">
            @csrf
            
            @if(isset($draftId) && $draftId)
                <input type="hidden" name="draft_id" value="{{ $draftId }}">
            @endif

            <div class="gmail-input-row">
                <span class="gmail-input-label">To</span>
                <div class="flex-grow-1">
                    <select class="gmail-input-field w-100" id="receiver_id" name="receiver_id" data-placeholder="Recipients">
                        <option value=""></option>
                        @foreach ($recipients as $user)
                            <option value="{{ $user->id }}"
                                @if(isset($toUserId) && $toUserId == $user->id) selected
                                @elseif(old('receiver_id') == $user->id) selected @endif>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="gmail-input-row">
                <span class="gmail-input-label d-none">Subject</span>
                <input type="text" class="gmail-input-field" placeholder="Subject" name="title" value="{{ old('title', $title ?? '') }}">
            </div>

            <textarea class="gmail-editor" name="message" placeholder="" spellcheck="false">{{ old('message', $message ?? '') }}</textarea>

            <div class="gmail-compose-footer">
                <button type="submit" name="action" value="send" class="gmail-send-btn me-3" id="btnSend">
                    Send
                </button>
                
                <button type="submit" name="action" value="draft" class="btn text-muted fw-medium d-inline-flex align-items-center bg-transparent border-0" id="btnDraft">
                    <i class="fas fa-save me-2"></i> Save draft
                </button>
                
                <div class="ms-auto">
                    <a href="{{ route('profile.mailbox') }}" class="gmail-icon-btn d-inline-flex align-items-center justify-content-center" title="Discard draft">
                        <i class="fas fa-trash-alt fs-6"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#receiver_id').select2({
                placeholder: $(this).data('placeholder'),
                allowClear: true,
                width: '100%'
            });
            
            $('#composeForm').on('submit', function() {
                const isDraft = $(document.activeElement).val() === 'draft';
                if (isDraft) {
                    $('#btnDraft').html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...').prop('disabled', true);
                    $('#btnSend').prop('disabled', true);
                } else {
                    $('#btnSend').html('Sending...').prop('disabled', true);
                    $('#btnDraft').prop('disabled', true);
                }
            });
        });
    </script>
@endsection