@extends('layouts.app')

@section('title', 'Community - Connect & Share Health Experiences')
@section('meta_description', 'Join our health community to share experiences, ask questions, and get support from others on similar health journeys.')

@section('content')
<style>
/* ==================== OVERRIDE APP.BLADE.PHP CONSTRAINTS ==================== */
body {
    overflow-x: hidden;
}

.main-content, 
.container, 
.container-fluid,
.app-root {
    max-width: 100% !important;
    width: 100% !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}

/* ==================== CUSTOM FULL WIDTH LAYOUT ==================== */
.community-container {
    background: #f0f2f5;
    min-height: calc(100vh - 60px);
    width: 100vw;
    max-width: 100vw;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
    position: relative;
    left: 0;
    right: 0;
}

/* Header */
.community-header {
    background: white;
    padding: 20px 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-bottom: 1px solid #e4e6eb;
    width: 100%;
}

/* Main Row */
.community-row {
    display: flex;
    width: 100%;
    margin: 0;
}

/* Left Sidebar */
.left-sidebar {
    width: 350px;
    flex-shrink: 0;
    background: #f0f2f5;
    border-right: 1px solid #e4e6eb;
    padding: 20px;
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 30px);
    overflow-y: auto;
    align-self: flex-start;
}

.left-sidebar::-webkit-scrollbar {
    width: 6px;
}

.left-sidebar::-webkit-scrollbar-track {
    background: #f0f2f5;
    border-radius: 10px;
}

.left-sidebar::-webkit-scrollbar-thumb {
    background: #c0c2c5;
    border-radius: 10px;
}

.left-sidebar::-webkit-scrollbar-thumb:hover {
    background: #a0a2a5;
}

/* Main Content */
.main-content {
    flex: 1;
    background: #f0f2f5;
    min-height: calc(100vh - 101px);
    padding: 20px;
    display: flex;
    justify-content: center;
}

.content-wrapper {
    width: 100%;
    max-width: 800px;
}

/* Right Sidebar */
.right-sidebar {
    width: 350px;
    flex-shrink: 0;
    background: #f0f2f5;
    border-left: 1px solid #e4e6eb;
    padding: 20px;
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 30px);
    overflow-y: auto;
    align-self: flex-start;
}

.right-sidebar::-webkit-scrollbar {
    width: 6px;
}

.right-sidebar::-webkit-scrollbar-track {
    background: #f0f2f5;
    border-radius: 10px;
}

.right-sidebar::-webkit-scrollbar-thumb {
    background: #c0c2c5;
    border-radius: 10px;
}

.right-sidebar::-webkit-scrollbar-thumb:hover {
    background: #a0a2a5;
}

/* ==================== QUICK FILTERS ==================== */
.filter-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.filter-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
    color: #1a1a1a;
}

.quick-filter-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 400px;
    overflow-y: auto;
    padding-right: 5px;
}

.quick-filter-buttons::-webkit-scrollbar {
    width: 6px;
}

.quick-filter-buttons::-webkit-scrollbar-track {
    background: #f0f2f5;
    border-radius: 10px;
}

.quick-filter-buttons::-webkit-scrollbar-thumb {
    background: #c0c2c5;
    border-radius: 10px;
}

.quick-filter-buttons::-webkit-scrollbar-thumb:hover {
    background: #a0a2a5;
}

.quick-filter-btn {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border: none;
    border-radius: 8px;
    background: #f0f2f5;
    color: #1a1a1a;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
    text-align: left;
}

.quick-filter-btn:hover {
    background: #e4e6eb;
}

.quick-filter-btn.active {
    background: #e7f3ff;
    color: #1877f2;
}

.quick-filter-btn.active i {
    color: #1877f2;
}

.filter-name {
    flex: 1;
    font-weight: 500;
}

.filter-count {
    background: #e4e6eb;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    margin-left: 10px;
}

.quick-filter-btn.active .filter-count {
    background: #1877f2;
    color: white;
}

/* Guidelines */
.guidelines-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.guidelines-list li {
    font-size: 14px;
    color: #65676b;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
}

.guidelines-list li i {
    font-size: 14px;
}

/* ==================== CREATE POST TRIGGER ==================== */
.create-post-trigger {
    background: white;
    border-radius: 12px;
    padding: 7px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    gap: 12px;
    cursor: pointer;
    transition: box-shadow 0.2s;
}

.create-post-trigger:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.trigger-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.trigger-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.trigger-input {
    flex: 1;
    display: flex;
    align-items: center;
}

.trigger-placeholder {
    width: 100%;
    padding: 10px 16px;
    background: #f0f2f5;
    border-radius: 20px;
    color: #65676b;
    font-size: 14px;
}

.trigger-icons {
    display: flex;
    gap: 8px;
}

.trigger-icon {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background: #f0f2f5;
    color: #1877f2;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all 0.2s;
}

.trigger-icon:hover {
    background: #e4e6eb;
    transform: scale(1.1);
}

/* ==================== MODAL STYLES ==================== */
.modal-user-info {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e4e6eb;
}

.modal-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.modal-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.modal-avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.modal-user-name {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
}

.modal-disease-select {
    width: 100%;
    padding: 6px;
    border: 1px solid #e4e6eb;
    border-radius: 4px;
    font-size: 13px;
    color: #1a1a1a;
}

.modal-textarea {
    width: 100%;
    border: 1px solid #e4e6eb;
    border-radius: 8px;
    padding: 12px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    min-height: 120px;
    margin-bottom: 12px;
}

.modal-textarea:focus {
    outline: none;
    border-color: #1877f2;
    box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.1);
}

/* Character counter styling */
.char-counter-display {
    position: absolute;
    bottom: 8px;
    right: 12px;
    font-size: 11px;
    color: #65676b;
    background: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 500;
}

.char-counter-display.warning {
    color: #ff9800;
}

.char-counter-display.error {
    color: #dc3545;
}

/* File Preview - IMPROVED FOR MULTIPLE FILES */
.file-preview-area {
    margin-bottom: 12px;
}

.file-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 10px;
    max-height: 300px;
    overflow-y: auto;
    padding: 5px;
}

.file-preview-item {
    border: 1px solid #e4e6eb;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    position: relative;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.file-preview-thumb {
    width: 100%;
    height: 80px;
    background: #f0f2f5;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.file-preview-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-preview-thumb i {
    font-size: 32px;
    color: #1877f2;
}

.file-preview-info {
    padding: 6px;
    font-size: 11px;
    border-top: 1px solid #e4e6eb;
}

.file-preview-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 500;
    margin-bottom: 2px;
}

.file-preview-size {
    color: #65676b;
    font-size: 10px;
}

.file-preview-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    border: 1px solid #dc3545;
    color: #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
    z-index: 10;
}

.file-preview-remove:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.1);
}

.file-upload-info {
    font-size: 12px;
    color: #65676b;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f0f2f5;
    border-radius: 6px;
    margin-bottom: 12px;
}

.file-upload-stats {
    margin-left: auto;
    font-weight: 500;
    color: #1877f2;
}

.modal-file-btn {
    padding: 6px 12px;
    background: #f0f2f5;
    border: none;
    border-radius: 6px;
    color: #1a1a1a;
    font-size: 13px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}

.modal-file-btn:hover {
    background: #e4e6eb;
}

.modal-body {
    padding: 20px;
}

.modal-header, .modal-footer {
    padding: 12px 20px;
}

/* ==================== POST CARD ==================== */
.post-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.post-user {
    display: flex;
    gap: 12px;
    cursor: pointer;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    cursor: pointer;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-name {
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 4px;
    color: #1a1a1a;
    cursor: pointer;
}

.user-name:hover {
    text-decoration: underline;
    color: #1877f2;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: #65676b;
    flex-wrap: wrap;
}

.post-disease-badge {
    background: #e7f3ff;
    color: #1877f2;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 12px;
    text-align: center;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.post-disease-badge span {
    font-size: 10px;
    color: #65676b;
}

.post-actions-menu {
    display: flex;
    gap: 6px;
    position: relative;
    z-index: 100;
}

.post-menu-btn {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 50%;
    background: #f0f2f5;
    color: #65676b;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.post-menu-btn:hover {
    background: #e4e6eb;
    color: #1a1a1a;
}

.post-menu-btn.text-danger:hover {
    background: #fee;
    color: #dc3545;
}

.post-text {
    font-size: 15px;
    line-height: 1.6;
    color: #1a1a1a;
    margin-bottom: 12px;
    
}

.post-attachment-image {
    width: 100%;
    max-height: 500px;
    object-fit: contain;
    background: #f0f2f5;
    border-radius: 12px;
    cursor: pointer;
    margin-bottom: 12px;
}

.post-attachment-video {
    width: 100%;
    max-height: 500px;
    border-radius: 12px;
    margin-bottom: 12px;
}

.post-attachment-file {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f0f2f5;
    border-radius: 8px;
    margin-bottom: 12px;
}

.post-attachment-file i {
    font-size: 24px;
    color: #1877f2;
}

.post-attachment-file .file-info {
    flex: 1;
}

.post-attachment-file .file-name {
    font-weight: 500;
    font-size: 14px;
}

.post-attachment-file .file-size {
    font-size: 12px;
    color: #65676b;
}

.post-attachment-file .download-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: white;
    color: #1877f2;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.post-attachment-file .download-btn:hover {
    background: #1877f2;
    color: white;
}

.files-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 12px;
}

.file-item {
    border: 1px solid #e4e6eb;
    border-radius: 10px;
    overflow: hidden;
    background: white;
    transition: transform 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.file-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* ==================== POST ACTION BUTTONS ==================== */
.post-action-buttons {
    display: flex;
    gap: 8px;
    margin-top: 8px;
}

.post-action-btn {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 6px;
    background: #f0f2f5;
    color: #1a1a1a;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}

.post-action-btn:hover {
    background: #e4e6eb;
}

.post-action-btn.liked {
    background: #fee;
    color: #dc3545;
}

.post-action-btn.liked i {
    color: #dc3545;
}

/* ==================== COMMENTS ==================== */
.comments-section {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e4e6eb;
}

.comment {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}

.comment-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    cursor: pointer;
}

.comment-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-small {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 12px;
}

.comment-content {
    flex: 1;
    background: #f0f2f5;
    padding: 10px 12px;
    border-radius: 18px;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}

.comment-author {
    font-weight: 600;
    font-size: 13px;
    color: #1a1a1a;
    cursor: pointer;
}

.comment-author:hover {
    text-decoration: underline;
    color: #1877f2;
}

.comment-time {
    font-size: 11px;
    color: #65676b;
}

.comment-actions {
    margin-left: auto;
    display: flex;
    gap: 6px;
}

.comment-action-btn {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    padding: 4px;
    color: #65676b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
}

.comment-action-btn:focus,
.comment-action-btn:active,
.comment-action-btn:hover {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
}

.comment-action-btn.text-danger {
    color: #dc3545;
}

.comment-action-btn.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.1) !important;
    border-radius: 4px;
}

.comment-action-btn:hover:not(.text-danger) {
    background-color: rgba(0, 0, 0, 0.05) !important;
    border-radius: 4px;
}

.comment-text {
    font-size: 13px;
    color: #1a1a1a;
    margin-bottom: 6px;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.comment-like-btn {
    background: none !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    color: #65676b;
    font-size: 11px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 0;
}

.comment-like-btn:focus,
.comment-like-btn:active {
    outline: none !important;
    border: none !important;
    box-shadow: none !important;
}

.comment-like-btn.liked {
    color: #dc3545;
}

.comment-like-btn.liked i {
    color: #dc3545;
}

.comment-attachment {
    margin-top: 8px;
}

.comment-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 8px;
    cursor: pointer;
}

/* Link styles for comments */
.comment-link {
    color: #1877f2;
    text-decoration: none;
    word-break: break-all;
    display: inline;
    margin: 0;
    padding: 0;
}

.comment-link:hover {
    text-decoration: underline;
    color: #0e5a9e;
}

/* Load more comments button */
.load-more-comments {
    width: 100%;
    padding: 8px;
    background: none;
    border: 1px solid #e4e6eb;
    border-radius: 6px;
    color: #1877f2;
    font-size: 13px;
    cursor: pointer;
    margin-bottom: 12px;
}

.load-more-comments:hover {
    background: #e7f3ff;
}

/* ==================== RIGHT SIDEBAR ==================== */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f0f2f5;
    border-radius: 8px;
}

.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #1877f2;
}

.stat-label {
    font-size: 12px;
    color: #65676b;
}

.trending-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e4e6eb;
}

.trending-name {
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.trending-name small {
    color: #65676b;
    font-size: 11px;
}

.trending-count {
    font-size: 12px;
    color: #1877f2;
    background: #e7f3ff;
    padding: 3px 10px;
    border-radius: 20px;
}

.support-card {
    text-align: center;
    padding: 20px;
    background: white;
    border-radius: 12px;
}

.support-icon {
    font-size: 36px;
    color: #1877f2;
}

.btn-support {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: #f0f2f5;
    color: #1a1a1a;
    border-radius: 6px;
    text-decoration: none;
    margin-top: 10px;
}

.btn-support:hover {
    background: #e4e6eb;
}

/* ==================== GUEST BANNER ==================== */
.guest-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 40px;
    margin-bottom: 20px;
    text-align: center;
    color: white;
}

.guest-banner h4 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 10px;
}

.guest-banner p {
    margin-bottom: 20px;
    opacity: 0.9;
}

.guest-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.guest-buttons .btn {
    padding: 10px 30px;
    border-radius: 6px;
    font-weight: 500;
}

.guest-buttons .btn-primary {
    background: white;
    color: #667eea;
    border: none;
}

.guest-buttons .btn-outline-primary {
    border: 2px solid white;
    color: white;
    background: transparent;
}

.guest-buttons .btn-outline-primary:hover {
    background: white;
    color: #667eea;
}

/* ==================== PAGINATION - NO ICONS ==================== */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 40px;
    margin-bottom: 20px;
    padding: 20px;
}

.pagination-wrapper .pagination {
    display: flex;
    align-items: center;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
    background: white;
    border-radius: 12px;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.pagination-wrapper .page-item {
    display: flex;
    align-items: center;
}

.pagination-wrapper .page-link {
    padding: 8px 12px;
    border: 1px solid #e4e6eb;
    border-radius: 6px;
    color: #1877f2;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    cursor: pointer;
    min-width: 40px;
    text-align: center;
    background: white;
}

.pagination-wrapper .page-link:hover:not(.disabled) {
    background: #e7f3ff;
    border-color: #1877f2;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(24, 119, 242, 0.15);
}

.pagination-wrapper .page-item.active .page-link {
    background: #1877f2;
    color: white;
    border-color: #1877f2;
}

.pagination-wrapper .page-item.disabled .page-link {
    color: #adb5bd;
    border-color: #e4e6eb;
    cursor: not-allowed;
    opacity: 0.5;
    background: #f8f9fa;
}

/* Hide any icons in pagination */
.pagination-wrapper .page-link i,
.pagination-wrapper .page-link svg,
.pagination-wrapper .page-link .fas,
.pagination-wrapper .page-link .far {
    display: none !important;
}

/* Use text for prev/next */
.pagination-wrapper .page-item:first-child .page-link::before {
    content: "";
}

.pagination-wrapper .page-item:last-child .page-link::before {
    content: "";
}

.pagination-wrapper .page-item:first-child .page-link i,
.pagination-wrapper .page-item:last-child .page-link i {
    display: none !important;
}

/* Hide any default Bootstrap text */
.pagination-wrapper .page-text {
    display: none;
}

.spinner-small {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.toast-container {
    z-index: 9999;
}

.toast {
    min-width: 280px;
    border-radius: 8px;
}

.user-modal-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #1877f2;
    margin: 0 auto 15px;
}

.user-modal-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 600;
    border: 3px solid #1877f2;
    margin: 0 auto 15px;
}

.user-stat-card {
    background: #f0f2f5;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
}

.user-stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #1877f2;
}

.user-stat-label {
    font-size: 12px;
    color: #65676b;
}

.activity-list {
    max-height: 200px;
    overflow-y: auto;
}

.activity-item {
    padding: 8px 12px;
    border-bottom: 1px solid #e4e6eb;
    transition: background 0.2s;
}

.activity-item:hover {
    background: #f0f2f5;
}

.activity-item a {
    text-decoration: none;
    color: inherit;
}

.activity-item small {
    color: #65676b;
}

/* Responsive */
@media (max-width: 1200px) {
    .left-sidebar, .right-sidebar {
        width: 300px;
    }
    
    .pagination-wrapper .pagination {
        padding: 10px 12px;
    }
    
    .pagination-wrapper .page-link {
        padding: 6px 10px;
        font-size: 13px;
        min-width: 36px;
    }
}

@media (max-width: 992px) {
    .left-sidebar, .right-sidebar {
        display: none;
    }
    
    .main-content {
        width: 100%;
    }
    
    .files-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .pagination-wrapper {
        margin-top: 30px;
    }
    
    .pagination-wrapper .pagination {
        flex-wrap: wrap;
        gap: 6px;
        padding: 12px;
    }
}

@media (max-width: 576px) {
    .create-post-footer {
        flex-direction: column;
        gap: 12px;
    }
    
    .post-actions-right {
        width: 100%;
    }
    
    .disease-select {
        width: 100%;
    }
    
    .files-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .pagination-wrapper {
        padding: 15px 10px;
        margin-top: 25px;
    }
    
    .pagination-wrapper .pagination {
        gap: 4px;
        padding: 8px 10px;
    }
    
    .pagination-wrapper .page-link {
        padding: 5px 8px;
        font-size: 12px;
        min-width: 32px;
    }
}
</style>

@php
    $isStarredPage = $isStarredPage ?? false;
@endphp

<div class="community-container">
    <!-- Header -->
    <div class="community-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 5px;">
                    <i class="fas {{ $isStarredPage ? 'fa-star' : 'fa-users' }} me-3" style="color: {{ $isStarredPage ? '#f7b500' : '#1877f2' }};"></i>
                    {{ $isStarredPage ? 'Starred Posts' : 'Community Forum' }}
                </h1>
                <p style="color: #65676b; margin: 0;">
                    {{ $isStarredPage ? 'Your saved posts in one focused feed' : 'Connect with others, share experiences, and get support' }}
                </p>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <select id="diseaseFilter" onchange="filterByDisease(this.value)" style="padding: 10px 16px; border: 1px solid #e4e6eb; border-radius: 8px; min-width: 220px;">
                    <option value="all" {{ !request('disease') ? 'selected' : '' }}>All Diseases</option>
                    @foreach($diseases as $disease)
                        <option value="{{ $disease->id }}" {{ request('disease') == $disease->id ? 'selected' : '' }}>
                            {{ $disease->disease_name }} - {{ $disease->posts_count }} posts
                        </option>
                    @endforeach
                </select>
                @auth
                    <a href="{{ $isStarredPage ? route('community.index') : route('community.posts.starred') }}" class="btn btn-sm {{ $isStarredPage ? 'btn-outline-primary' : 'btn-warning' }} rounded-pill ms-1 d-inline-flex align-items-center" style="white-space:nowrap;">
                        <i class="fas fa-star me-2"></i>{{ $isStarredPage ? 'All Posts' : 'Starred Posts' }}
                    </a>
                @endauth
                <a href="{{ auth()->check() ? route('users.index') : route('login') }}" class="btn btn-sm btn-primary rounded-pill ms-2 d-none d-md-inline-flex align-items-center" style="white-space:nowrap;">
                    <i class="fas fa-users me-2"></i> Browse Members
                </a>
                <a href="{{ auth()->check() ? route('users.index') : route('login') }}" class="btn btn-sm btn-primary rounded-pill ms-2 d-md-none align-items-center" style="white-space:nowrap;">
                    <i class="fas fa-users"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Row -->
    <div style="display: flex;">
        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <div class="filter-card">
                <h5 class="filter-title">
                    <i class="fas fa-filter me-2" style="color: #1877f2;"></i>
                    Quick Filters
                </h5>
                <div class="quick-filter-buttons">
                    <button class="quick-filter-btn {{ !request('disease') ? 'active' : '' }}" onclick="filterByDisease('all')">
                        <i class="fas {{ $isStarredPage ? 'fa-star' : 'fa-globe' }} me-2"></i>
                        <span class="filter-name">{{ $isStarredPage ? 'All Starred Posts' : 'All Posts' }}</span>
                        <span class="filter-count">{{ $totalPosts }}</span>
                    </button>
                    @foreach($diseases as $disease)
                        <button class="quick-filter-btn {{ request('disease') == $disease->id ? 'active' : '' }}" 
                                onclick="filterByDisease({{ $disease->id }})">
                            <i class="fas fa-heartbeat me-2"></i>
                            <span class="filter-name">
                                {{ $disease->disease_name }}
                            </span>
                            <span class="filter-count">{{ $disease->posts_count }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="filter-card">
                <h5 class="filter-title">
                    <i class="fas fa-info-circle me-2" style="color: #17a2b8;"></i>
                    Guidelines
                </h5>
                <ul class="guidelines-list">
                    <li><i class="fas fa-check-circle me-2" style="color: #28a745;"></i>Be respectful and supportive</li>
                    <li><i class="fas fa-check-circle me-2" style="color: #28a745;"></i>No medical misinformation</li>
                    <li><i class="fas fa-check-circle me-2" style="color: #28a745;"></i>Protect your privacy</li>
                    <li><i class="fas fa-check-circle me-2" style="color: #28a745;"></i>Report inappropriate content</li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                <!-- Create Post -->
                @auth
                    <!-- Clickable Create Post Box -->
                    <div class="create-post-trigger" onclick="openCreatePostModal()">
                        <div class="trigger-avatar">
                            @if(Auth::user()->picture)
                                <img src="{{ asset('storage/' . Auth::user()->picture) }}" alt="{{ Auth::user()->name }}">
                            @else
                                <div class="avatar-placeholder">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            @endif
                        </div>
                        <div class="trigger-input">
                            <div class="trigger-placeholder">How do you feel today?</div>
                        </div>
                        <div class="trigger-icons">
                            <button class="trigger-icon" title="Photos"><i class="fas fa-image"></i></button>
                            <button class="trigger-icon" title="Video"><i class="fas fa-video"></i></button>
                        </div>
                    </div>

                    <!-- Create Post Modal -->
                    <div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <!-- Header -->
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title">
                                        <i class="fas fa-pen-fancy me-2" style="color: #1877f2;"></i>
                                        Create Post
                                    </h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>

                                <!-- Body -->
                                <div class="modal-body">
                                    <!-- User Info -->
                                    <div class="modal-user-info">
                                        <div class="modal-avatar">
                                            @if(Auth::user()->picture)
                                                <img src="{{ asset('storage/' . Auth::user()->picture) }}" alt="{{ Auth::user()->name }}">
                                            @else
                                                <div class="modal-avatar-placeholder">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                                            @endif
                                        </div>
                                        <div style="flex: 1;">
                                            <div class="modal-user-name">{{ Auth::user()->name }}</div>
                                            <select id="createPostDiseaseId" class="modal-disease-select">
                                                <option value="">Select disease</option>
                                                @foreach($diseases as $disease)
                                                    <option value="{{ $disease->id }}">
                                                        {{ $disease->disease_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Text Area with Character Counter -->
                                    <div style="position: relative;">
                                        <textarea id="createPostContent" 
                                                  class="modal-textarea" 
                                                  placeholder="Write your health experiences..." 
                                                  rows="5"
                                                  oninput="updateCreatePostCharCounter()"></textarea>
                                        
                                        <div class="char-counter-display">
                                            <span id="createPostCharCount">0</span> / <span>5000</span>
                                        </div>
                                    </div>

                                    <!-- File Upload Info -->
                                    <div class="file-upload-info">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Max 10MB per file • 50MB total</span>
                                        <span class="file-upload-stats" id="fileUploadStats">0 files</span>
                                    </div>

                                    <!-- File Preview Grid -->
                                    <div id="createPostFilePreview" class="file-preview-area" style="display: none;">
                                        <div class="file-preview-grid" id="createPostFilePreviewGrid"></div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="modal-footer border-top">
                                    <div style="flex: 1; display: flex; gap: 8px; flex-wrap: wrap;">
                                        <label class="modal-file-btn">
                                            <i class="fas fa-image me-1"></i>Photo
                                            <input type="file" id="createPostImage" class="d-none" accept="image/*" multiple onchange="handleCreatePostFileSelect(this)">
                                        </label>
                                        <label class="modal-file-btn">
                                            <i class="fas fa-video me-1"></i>Video
                                            <input type="file" id="createPostVideo" class="d-none" accept="video/*" multiple onchange="handleCreatePostFileSelect(this)">
                                        </label>
                                        <label class="modal-file-btn">
                                            <i class="fas fa-file me-1"></i>File
                                            <input type="file" id="createPostFile" class="d-none" accept=".pdf,.doc,.docx,.txt,.xls,.xlsx" multiple onchange="handleCreatePostFileSelect(this)">
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="submitCreatePost()" id="createPostSubmitBtn">
                                        <i class="fas fa-paper-plane me-1"></i>Post
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth

                @guest
                    <div class="guest-banner">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h4>Join the Community</h4>
                        <p>Connect with thousands of people on similar health journeys</p>
                        <div class="guest-buttons">
                            <a href="{{ route('register') }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Sign Up
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </div>
                    </div>
                @endguest

                <!-- Posts Feed -->
                <div id="postsFeed">
                    @forelse($posts as $post)
                        @include('community.partials.post', ['post' => $post])
                    @empty
                        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                            <i class="fas fa-comments fa-4x mb-3" style="color: #adb5bd;"></i>
                            <h5>{{ $isStarredPage ? 'No starred posts yet' : 'No discussions yet' }}</h5>
                            <p style="color: #65676b;">{{ $isStarredPage ? 'Star posts from the feed to collect them here.' : 'Be the first to start a conversation!' }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination - NO ICONS -->
                <div class="pagination-wrapper mt-4">
                    @if ($posts->hasPages())
                        <ul class="pagination">
                            {{-- Previous Page Link --}}
                            @if ($posts->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">Prev</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $posts->previousPageUrl() }}" rel="prev">Prev</a></li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($posts->links()->elements as $element)
                                {{-- "Three Dots" Separator --}}
                                @if (is_string($element))
                                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                                @endif

                                {{-- Array Of Links --}}
                                @if (is_array($element))
                                    @foreach ($element as $page => $url)
                                        @if ($page == $posts->currentPage())
                                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($posts->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $posts->nextPageUrl() }}" rel="next">Next</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link">Next</span></li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="filter-card">
                <h5 class="filter-title">
                    <i class="fas fa-chart-line me-2" style="color: #1877f2;"></i>
                    Community Stats
                </h5>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($totalUsers) }}</div>
                        <div class="stat-label">Members</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($totalPosts) }}</div>
                        <div class="stat-label">Posts</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($totalComments) }}</div>
                        <div class="stat-label">Comments</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($activeToday) }}</div>
                        <div class="stat-label">Active Today</div>
                    </div>
                </div>
            </div>

            <div class="filter-card">
                <h5 class="filter-title">
                    <i class="fas fa-fire me-2" style="color: #ffc107;"></i>
                    {{ __('ui.community.trending_diseases') }}
                </h5>
                @foreach($trendingDiseases as $disease)
                    <div class="trending-item">
                        <div>
                            <div class="trending-name">
                                {{ $disease->disease_name }}
                            </div>
                        </div>
                        <span class="trending-count">{{ $disease->posts_count }}</span>
                    </div>
                @endforeach
            </div>

            <div class="support-card">
                <i class="fas fa-headset support-icon"></i>
                <h6>Need Help?</h6>
                <p class="text-muted small">24/7 Support available</p>
                <a href="{{ route('help') }}" class="btn-support">
                    <i class="fas fa-question-circle me-2"></i>Get Help
                </a>
            </div>
        </div>
    </div>
</div>

<!-- NO MODALS HERE - They are inherited from layouts.app -->

@endsection

@push('scripts')
<script>
// ==================== COMMUNITY-SPECIFIC FUNCTIONS ONLY ====================

// ==================== GLOBALS ====================
const csrfToken = '{{ csrf_token() }}';
let currentPostFiles = [];
let createPostFiles = [];
let createPostModal = null;

// ==================== CREATE POST MODAL FUNCTIONS ====================
function openCreatePostModal() {
    if (!createPostModal) {
        createPostModal = new bootstrap.Modal(document.getElementById('createPostModal'));
    }
    createPostModal.show();
}

function updateCreatePostCharCounter() {
    const textarea = document.getElementById('createPostContent');
    if (!textarea) return;
    
    const count = textarea.value.length;
    const counterDisplay = document.querySelector('.char-counter-display');
    const charCountSpan = document.getElementById('createPostCharCount');
    
    if (!counterDisplay || !charCountSpan) return;
    
    charCountSpan.textContent = count;
    
    counterDisplay.classList.remove('warning', 'error');
    if (count > 4500) {
        counterDisplay.classList.add('error');
    } else if (count > 4000) {
        counterDisplay.classList.add('warning');
    }
}

function handleCreatePostFileSelect(input) {
    const files = Array.from(input.files);
    if (files.length === 0) return;

    const maxFileSize = 10 * 1024 * 1024;
    const oversized = files.filter(f => f.size > maxFileSize);
    if (oversized.length > 0) {
        showToast(`Some files exceed 10MB`, 'warning');
        input.value = '';
        return;
    }

    createPostFiles = [...createPostFiles, ...files];
    input.value = '';
    
    const totalSize = createPostFiles.reduce((sum, f) => sum + f.size, 0);
    const maxTotalSize = 50 * 1024 * 1024;
    
    if (totalSize > maxTotalSize) {
        showToast(`Total file size exceeds 50MB limit`, 'warning');
        createPostFiles = createPostFiles.slice(0, createPostFiles.length - files.length);
        return;
    }
    
    updateCreatePostFilePreview();
}

function updateCreatePostFilePreview() {
    const previewArea = document.getElementById('createPostFilePreview');
    const previewGrid = document.getElementById('createPostFilePreviewGrid');
    const statsEl = document.getElementById('fileUploadStats');
    
    if (createPostFiles.length === 0) {
        previewArea.style.display = 'none';
        if (statsEl) statsEl.textContent = '0 files';
        return;
    }

    const totalSize = createPostFiles.reduce((sum, f) => sum + f.size, 0);
    if (statsEl) statsEl.textContent = `${createPostFiles.length} files • ${formatFileSize(totalSize)}`;
    
    let html = '';
    
    createPostFiles.forEach((file, index) => {
        const fileSize = formatFileSize(file.size);
        let thumbnail = '';
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const thumbEl = document.getElementById(`thumb-${index}`);
                if (thumbEl) {
                    thumbEl.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
                }
            };
            reader.readAsDataURL(file);
            thumbnail = `<div id="thumb-${index}" class="file-preview-thumb"></div>`;
        } else {
            let icon = 'fa-file-alt';
            if (file.type.includes('pdf')) icon = 'fa-file-pdf';
            else if (file.type.includes('video')) icon = 'fa-file-video';
            else if (file.type.includes('word')) icon = 'fa-file-word';
            else if (file.type.includes('excel')) icon = 'fa-file-excel';
            
            thumbnail = `<div class="file-preview-thumb"><i class="fas ${icon}"></i></div>`;
        }
        
        html += `
            <div class="file-preview-item" data-index="${index}">
                <div class="file-preview-remove" onclick="removeCreatePostFile(${index})">
                    <i class="fas fa-times"></i>
                </div>
                ${thumbnail}
                <div class="file-preview-info">
                    <div class="file-preview-name" title="${file.name}">${file.name.length > 15 ? file.name.substring(0,12)+'...' : file.name}</div>
                    <div class="file-preview-size">${fileSize}</div>
                </div>
            </div>
        `;
    });

    previewGrid.innerHTML = html;
    previewArea.style.display = 'block';
}

function removeCreatePostFile(index) {
    createPostFiles.splice(index, 1);
    updateCreatePostFilePreview();
}

function clearCreatePostFile() {
    createPostFiles = [];
    document.getElementById('createPostImage').value = '';
    document.getElementById('createPostVideo').value = '';
    document.getElementById('createPostFile').value = '';
    document.getElementById('createPostFilePreview').style.display = 'none';
    document.getElementById('fileUploadStats').textContent = '0 files';
}

function submitCreatePost() {
    const content = document.getElementById('createPostContent').value.trim();
    const diseaseId = document.getElementById('createPostDiseaseId').value;

    if (!content && createPostFiles.length === 0) {
        showToast('Please write something or add a file', 'warning');
        return;
    }

    if (!diseaseId) {
        showToast('Please select a disease', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('disease_id', diseaseId);
    formData.append('description', content);

    createPostFiles.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });

    const submitBtn = document.getElementById('createPostSubmitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Posting...';

    fetch('/community/posts', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const textarea = document.getElementById('createPostContent');
            textarea.value = '';
            textarea.style.height = 'auto';
            updateCreatePostCharCounter();
            clearCreatePostFile();
            document.getElementById('createPostDiseaseId').value = '';
            createPostModal.hide();
            
            const postsFeed = document.getElementById('postsFeed');
            postsFeed.insertAdjacentHTML('afterbegin', data.html);
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            showToast('✅ Post created successfully!', 'success');
        } else {
            showToast('❌ ' + (data.message || 'Error creating post'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('❌ ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Clear modal when closed
document.getElementById('createPostModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('createPostContent').value = '';
    clearCreatePostFile();
    document.getElementById('createPostDiseaseId').value = '';
});

// ==================== FILTER ====================
function filterByDisease(diseaseId) {
    const baseRoute = @json($isStarredPage ? route('community.posts.starred') : route('community.index'));
    if (diseaseId === 'all') {
        window.location.href = baseRoute;
    } else {
        window.location.href = baseRoute + '?disease=' + diseaseId;
    }
}

// ==================== TOGGLE COMMENTS (Community specific) ====================
function toggleComments(postId) {
    const section = document.getElementById(`comments-section-${postId}`);
    const btn = document.getElementById(`toggle-comments-${postId}`);
    
    if (!section || !btn) return;
    
    const count = btn.querySelector('.comment-count').textContent;

    if (!section.style.display || section.style.display === 'none') {
        section.style.display = 'block';
        btn.innerHTML = `<i class="fas fa-chevron-up me-1"></i><span class="comment-count">${count}</span> Hide Comments`;
    } else {
        section.style.display = 'none';
        btn.innerHTML = `<i class="far fa-comment me-1"></i><span class="comment-count">${count}</span> Comments`;
    }
}

function loadMoreComments(postId) {
    // Instead of loading more comments, open the modal
    openPostModal(postId);
}

// ==================== POST MENU ====================
function togglePostMenu(postId) {
    const menu = document.getElementById(`post-menu-${postId}`);
    if (!menu) return;
    
    document.querySelectorAll('.post-dropdown-menu').forEach(m => {
        if (m.id !== `post-menu-${postId}`) {
            m.style.display = 'none';
        }
    });
    
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.post-actions-menu')) {
        document.querySelectorAll('.post-dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

</script>
@endpush