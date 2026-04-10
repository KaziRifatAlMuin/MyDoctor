@extends('layouts.app')

@section('title', 'Compose Message - My Doctor')
@section('main_content_class', 'main-content main-content--wide')

@push('styles')
    <style>
    .gmail-layout {
        min-height: 66vh;
        display: flex;
        width: 100%;
        background-color: #f6f8fc;
    }

    .gmail-sidebar {
        flex: 0 0 256px;
        background-color: #f6f8fc;
        padding-top: 16px;
        display: flex;
        flex-direction: column;
    }

    .gmail-compose-btn {
        background-color: #c2e7ff;
        color: #001d35;
        border-radius: 16px;
        padding: 0 24px;
        height: 56px;
        font-weight: 500;
        font-size: 15px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.3), 0 1px 3px 1px rgba(60, 64, 67, 0.15);
        margin: 0 0 16px 8px;
        transition: box-shadow 0.2s, background-color 0.2s;
        text-decoration: none;
        width: max-content;
    }

    .gmail-compose-btn:hover {
        box-shadow: 0 1px 3px 0 rgba(60, 64, 67, 0.3), 0 4px 8px 3px rgba(60, 64, 67, 0.15);
        background-color: #b3d7f3;
        color: #001d35;
    }

    .gmail-nav {
        list-style: none;
        padding: 0;
        margin: 0;
        padding-right: 16px;
    }

    .gmail-nav-item {
        display: flex;
        align-items: center;
        padding: 0 12px 0 24px;
        height: 32px;
        border-radius: 0 16px 16px 0;
        color: #444746;
        text-decoration: none;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .gmail-nav-item:hover {
        background-color: #e9eef6;
    }

    .gmail-nav-item.active {
        background-color: #d3e3fd;
        font-weight: 600;
        color: #0b57d0;
    }

    .gmail-nav-item i {
        margin-right: 18px;
        width: 20px;
        text-align: center;
        font-size: 16px;
    }

    .gmail-nav-item .badge {
        margin-left: auto;
        font-size: 12px;
        font-weight: 600;
        color: #444746;
        background: transparent !important;
    }

    .gmail-main {
        flex: 1 1 auto;
        width: calc(100% - 272px);
        min-width: 0;
        background-color: #fff;
        border-radius: 16px;
        margin: 0 16px 16px 0;
        min-height: 66vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.3);
    }

    .gmail-compose-header {
        background-color: #f2f6fc;
        padding: 10px 16px;
        font-weight: 500;
        font-size: 14px;
        color: #1f1f1f;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f3f4;
    }

    .gmail-input-row {
        display: flex;
        border-bottom: 1px solid #f1f3f4;
        padding: 0 16px;
        align-items: center;
        min-height: 40px;
    }

    .gmail-input-label {
        color: #5f6368;
        width: 40px;
        font-size: 14px;
        margin-right: 8px;
    }

    .gmail-input-field {
        flex: 1;
        border: none;
        outline: none;
        font-size: 14px;
        color: #1f1f1f;
        padding: 8px 0;
        background: transparent;
    }

    .gmail-editor-wrapper {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .gmail-compose-footer {
        padding: 12px 16px;
        border-top: 1px solid #f1f3f4;
        display: flex;
        align-items: center;
        background-color: #fff;
    }

    .gmail-send-btn {
        background-color: #0b57d0;
        color: #fff;
        border: none;
        border-radius: 18px;
        padding: 0 24px;
        height: 36px;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .gmail-send-btn:hover {
        background-color: #0842a0;
    }

    /* Recipient Autocomplete Styles */
    #receiver_id_wrapper {
        position: relative;
        width: 100%;
    }

    #receiver_id_input {
        width: 100%;
        padding: 8px 0;
        background: transparent;
        border: none;
        outline: none;
        font-size: 14px;
        color: #1f1f1f;
    }

    #receiver_id_input::placeholder {
        color: #9aa0a6;
    }

    #recipient_dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dadce0;
        border-radius: 8px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: 0 1px 2px rgba(60, 64, 67, 0.3);
    }

    #recipient_dropdown .recipient-option {
        padding: 10px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f4;
        font-size: 14px;
        color: #1f1f1f;
        transition: background 0.2s;
    }

    #recipient_dropdown .recipient-option:hover {
        background-color: #f1f3f4;
    }

    #recipient_dropdown .recipient-name {
        font-weight: 500;
        display: block;
    }

    #recipient_dropdown .recipient-email {
        font-size: 12px;
        color: #5f6368;
    }

    /* TinyMCE styles */
    .gmail-editor-wrapper textarea {
        min-height: 360px;
    }

    .tox.tox-tinymce {
        border: 0 !important;
        border-radius: 0 !important;
    }

    .tox .tox-editor-header {
        box-shadow: none !important;
        border-bottom: 1px solid #f1f3f4 !important;
    }

    .tox .tox-toolbar-overlord,
    .tox .tox-toolbar,
    .tox .tox-toolbar__primary {
        background: #fafbfc !important;
    }

    .tox .tox-edit-area__iframe {
        background: #fff !important;
    }

    .compose-form {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* Icon button */
    .gmail-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #444746;
        text-decoration: none;
        border: none;
        background: transparent;
        transition: background-color 0.2s;
        cursor: pointer;
    }

    .gmail-icon-btn:hover {
        background-color: rgba(68, 71, 70, 0.08);
    }
    </style>
@endpush

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
                        @php
                            $unreadCount = \App\Models\Mailing::where('receiver_id', auth()->id())
                                ->where('status', 'unread')
                                ->count();
                        @endphp
                        @if ($unreadCount > 0)
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
                <li>
                    <a href="{{ route('profile.mailbox.starred') }}" class="gmail-nav-item">
                        <i class="fas fa-star"></i> Starred
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.mailbox.archived') }}" class="gmail-nav-item">
                        <i class="fas fa-box-archive"></i> Archived
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="gmail-main">
            <div class="gmail-compose-header">
                <span>New Message</span>
                <a href="{{ route('profile.mailbox') }}" class="gmail-icon-btn" style="width: 24px; height: 24px;"
                    title="Close">
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

            <form method="POST" action="{{ route('profile.mailbox.store') }}" class="compose-form" id="composeForm">
                @csrf

                @if (isset($draftId) && $draftId)
                    <input type="hidden" name="draft_id" value="{{ $draftId }}">
                @endif

                <div class="gmail-input-row">
                    <span class="gmail-input-label">To</span>
                    <div class="flex-grow-1" id="receiver_id_wrapper">
                        <input type="text" class="gmail-input-field" id="receiver_id_input"
                            placeholder="Type recipient name or email..." autocomplete="off">
                        <div id="recipient_dropdown"></div>
                        <input type="hidden" id="receiver_id" name="receiver_id"
                            value="{{ old('receiver_id', $toUserId ?? '') }}">
                    </div>
                </div>

                <div class="gmail-input-row">
                    <span class="gmail-input-label d-none">Subject</span>
                    <input type="text" class="gmail-input-field" placeholder="Subject" name="title"
                        value="{{ old('title', $title ?? '') }}">
                </div>

                <div class="gmail-editor-wrapper">
                    <textarea id="message" name="message">{{ old('message', $message ?? '') }}</textarea>
                </div>

                <div class="gmail-compose-footer">
                    <button type="submit" name="action" value="send" class="gmail-send-btn me-3" id="btnSend">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>

                    <button type="submit" name="save_draft" value="1"
                        class="btn text-muted fw-medium d-inline-flex align-items-center bg-transparent border-0"
                        id="btnDraft">
                        <i class="fas fa-save me-2"></i> Save draft
                    </button>

                    <div class="ms-auto">
                        <a href="{{ route('profile.mailbox') }}" class="gmail-icon-btn" title="Discard">
                            <i class="fas fa-trash-alt fs-6"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const recipientSearchUrl = @json(route('profile.mailbox.recipients.search'));
            const selectedRecipient = @json($selectedRecipient);
            let selectedRecipientId = @json(old('receiver_id', $toUserId ?? null));
            let submitAction = 'send';
            let formSubmitting = false;
            let searchTimeout = null;
            let activeSearchController = null;

            const recipientInput = document.getElementById('receiver_id_input');
            const recipientDropdown = document.getElementById('recipient_dropdown');
            const recipientSelect = document.getElementById('receiver_id');
            const form = document.getElementById('composeForm');
            const sendButton = document.getElementById('btnSend');
            const draftButton = document.getElementById('btnDraft');
            const messageTextarea = document.getElementById('message');

            if (!form || !recipientInput || !recipientDropdown || !recipientSelect || !messageTextarea || !sendButton ||
                !draftButton) {
                return;
            }

            if (selectedRecipient && selectedRecipientId && String(selectedRecipient.id) === String(selectedRecipientId)) {
                recipientInput.value = `${selectedRecipient.name} <${selectedRecipient.email}>`;
            }

            const escapeHtml = (value) => {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };

                return String(value).replace(/[&<>"']/g, m => map[m]);
            };

            const closeDropdown = () => {
                recipientDropdown.style.display = 'none';
            };

            const showMessage = (text) => {
                recipientDropdown.innerHTML =
                    `<div style="padding: 10px 16px; color: #5f6368; font-size: 14px;">${escapeHtml(text)}</div>`;
                recipientDropdown.style.display = 'block';
            };

            const renderOptions = (users) => {
                if (!Array.isArray(users) || users.length === 0) {
                    showMessage('No recipients found');
                    return;
                }

                recipientDropdown.innerHTML = users.map(user => `
                    <div class="recipient-option" data-id="${user.id}" data-name="${escapeHtml(user.name)}" data-email="${escapeHtml(user.email)}">
                        <span class="recipient-name">${escapeHtml(user.name)}</span>
                        <span class="recipient-email">&lt;${escapeHtml(user.email)}&gt;</span>
                    </div>
                `).join('');
                recipientDropdown.style.display = 'block';

                recipientDropdown.querySelectorAll('.recipient-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        const email = this.dataset.email;

                        recipientInput.value = `${name} <${email}>`;
                        recipientSelect.value = id;
                        selectedRecipientId = id;
                        closeDropdown();
                    });
                });
            };

            const searchRecipients = async (term) => {
                if (!term) {
                    closeDropdown();
                    return;
                }

                if (activeSearchController) {
                    activeSearchController.abort();
                }

                activeSearchController = new AbortController();

                try {
                    const response = await fetch(`${recipientSearchUrl}?q=${encodeURIComponent(term)}`, {
                        headers: {
                            'Accept': 'application/json'
                        },
                        signal: activeSearchController.signal
                    });

                    if (!response.ok) {
                        showMessage('Unable to search recipients');
                        return;
                    }

                    const users = await response.json();
                    renderOptions(users);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        showMessage('Unable to search recipients');
                    }
                }
            };

            recipientInput.addEventListener('input', function() {
                const term = this.value.trim();

                recipientSelect.value = '';
                selectedRecipientId = null;

                if (!term) {
                    closeDropdown();
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchRecipients(term);
                }, 250);
            });

            recipientInput.addEventListener('focus', function() {
                const term = this.value.trim();
                if (term && !selectedRecipientId) {
                    searchRecipients(term);
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target !== recipientInput && !recipientDropdown.contains(e.target)) {
                    closeDropdown();
                }
            });

            const tinyLocalCdnUrl =
                'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js';

            const loadScript = (url) => {
                return new Promise((resolve, reject) => {
                    const existing = document.querySelector(`script[src="${url}"]`);
                    if (existing && window.tinymce) {
                        resolve();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = url;
                    script.referrerPolicy = 'origin';
                    script.crossOrigin = 'anonymous';
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error(`Failed loading script: ${url}`));
                    document.head.appendChild(script);
                });
            };

            const buildBaseConfig = () => ({
                selector: '#message',
                menubar: false,
                statusbar: false,
                height: 420,
                resize: false,
                convert_urls: false,
                content_style: 'body { font-family: Roboto, Arial, sans-serif; font-size:14px; line-height:1.6; margin: 16px; }',
                placeholder: 'Write your message...',
                setup: function(editor) {
                    editor.on('change keyup blur', function() {
                        editor.save();
                    });
                }
            });

            const buildEditorConfig = () => ({
                ...buildBaseConfig(),
                plugins: 'anchor autolink charmap code codesample emoticons link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media table | removeformat code',
                toolbar_mode: 'sliding'
            });

            const initTiny = async () => {
                try {
                    await loadScript(tinyLocalCdnUrl);
                    await tinymce.init(buildEditorConfig());
                } catch (editorError) {
                    console.error('TinyMCE failed to initialize.', editorError);
                }
            };

            initTiny();

            sendButton.addEventListener('click', function() {
                submitAction = 'send';
            });

            draftButton.addEventListener('click', function() {
                submitAction = 'draft';
            });

            form.addEventListener('submit', function(e) {
                if (formSubmitting) {
                    e.preventDefault();
                    return false;
                }

                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }

                const messageValue = messageTextarea.value;
                const plainText = messageValue.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                const recipientId = recipientSelect.value;

                if (!recipientId && submitAction !== 'draft') {
                    e.preventDefault();
                    alert('Please select a recipient.');
                    return false;
                }

                if (!plainText || plainText.length < 2) {
                    e.preventDefault();
                    alert('Please write a message.');
                    return false;
                }

                formSubmitting = true;
                const isDraft = submitAction === 'draft';

                if (isDraft) {
                    draftButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';
                    draftButton.disabled = true;
                    sendButton.disabled = true;
                } else {
                    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';
                    sendButton.disabled = true;
                    draftButton.disabled = true;
                }

                return true;
            });
        });
    </script>
@endpush
