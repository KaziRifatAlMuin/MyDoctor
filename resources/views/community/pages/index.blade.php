@extends('layouts.app')

@section('title', __('ui.community.community_forum'))
@section('meta_description', __('ui.community.community_meta_description'))

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

/* Mobile Filter Bar - NEW */
.mobile-filter-bar {
    display: none;
    background: white;
    padding: 12px 16px;
    border-bottom: 1px solid #e4e6eb;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.mobile-filter-bar .filter-row {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.mobile-filter-bar select {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #e4e6eb;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    min-width: 0;
}

.mobile-filter-bar .action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.mobile-filter-bar .action-buttons .btn {
    padding: 8px 12px;
    font-size: 13px;
    white-space: nowrap;
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

/* ==================== UPDATED RESPONSIVE STYLES ==================== */
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
    
    /* Show mobile filter bar */
    .mobile-filter-bar {
        display: block;
    }
    
    /* Hide the filter buttons in header completely on mobile */
    .community-header .desktop-filters {
        display: none !important;
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

@media (max-width: 768px) {
    .community-header {
        padding: 16px 20px;
    }
    
    .community-header h1 {
        font-size: 22px !important;
    }
    
    .community-header p {
        font-size: 13px;
    }
    
    .mobile-filter-bar {
        padding: 10px 12px;
    }
    
    .mobile-filter-bar .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .mobile-filter-bar select {
        width: 100%;
        font-size: 13px;
        padding: 8px 10px;
    }
    
    .mobile-filter-bar .action-buttons {
        justify-content: center;
    }
    
    .mobile-filter-bar .action-buttons .btn {
        flex: 1;
        justify-content: center;
        font-size: 12px;
        padding: 6px 10px;
    }
    
    .main-content {
        padding: 12px;
    }
    
    .content-wrapper {
        max-width: 100%;
    }
    
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

@media (max-width: 576px) {
    .community-header {
        padding: 12px 16px;
    }
    
    .community-header h1 {
        font-size: 18px !important;
    }
    
    .mobile-filter-bar .action-buttons {
        flex-wrap: wrap;
    }
    
    .mobile-filter-bar .action-buttons .btn {
        font-size: 11px;
        padding: 5px 8px;
    }
    
    .main-content {
        padding: 8px;
    }
    
    .post-card {
        padding: 12px;
    }
    
    .post-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .post-actions-menu {
        align-self: flex-end;
    }
    
    .post-user {
        width: 100%;
    }
    
    .post-action-buttons {
        flex-direction: column;
        gap: 6px;
    }
    
    .post-action-btn {
        padding: 8px;
        font-size: 13px;
    }
    
    .comment {
        gap: 8px;
    }
    
    .comment-avatar {
        width: 28px;
        height: 28px;
    }
    
    .comment-content {
        padding: 8px 10px;
    }
    
    .comment-header {
        gap: 6px;
    }
    
    .comment-author {
        font-size: 12px;
    }
    
    .comment-text {
        font-size: 12px;
    }
}
</style>

@php
    $isStarredPage = $isStarredPage ?? false;
    $isPendingPage = $isPendingPage ?? false;
    $isAdminCommunity = $isAdminCommunity ?? false;
    $pendingPreviewPosts = $pendingPreviewPosts ?? collect();
@endphp

<div class="community-container">
    <!-- Header -->
    <div class="community-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 5px;">
                    <i class="fas {{ $isPendingPage ? 'fa-hourglass-half' : ($isStarredPage ? 'fa-star' : 'fa-users') }} me-3" style="color: {{ $isPendingPage ? '#ff9800' : ($isStarredPage ? '#f7b500' : '#1877f2') }};"></i>
                    {{ $isPendingPage ? __('ui.community.pending_posts_title') : ($isStarredPage ? __('ui.community.starred_posts_title') : ($isAdminCommunity ? __('ui.community.admin_community_moderation_title') : __('ui.community.community_forum_title'))) }}
                </h1>
                <p style="color: #65676b; margin: 0;">
                    {{ $isPendingPage ? ($isAdminCommunity ? __('ui.community.posts_awaiting_approval') : __('ui.community.your_posts_waiting_for_approval')) : ($isStarredPage ? __('ui.community.your_saved_posts') : ($isAdminCommunity ? __('ui.community.view_and_manage_community_posts') : __('ui.community.connect_with_others_share_experiences'))) }}
                </p>
            </div>
            
            <!-- Desktop Filters - Hidden on mobile/tablet -->
            <div class="desktop-filters" style="display:flex; align-items:center; gap:10px;">
                <select id="diseaseFilter" onchange="filterByDisease(this.value)" style="padding: 10px 16px; border: 1px solid #e4e6eb; border-radius: 8px; min-width: 220px;">
                    <option value="all" {{ !request('disease') ? 'selected' : '' }}>{{ __('ui.community.all_posts') }}</option>
                    @foreach($diseases as $disease)
                        <option value="{{ $disease->id }}" {{ request('disease') == $disease->id ? 'selected' : '' }}>
                            {{ $disease->display_name }} - {{ $disease->posts_count }} {{ __('ui.community.posts') }}
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('community.home') }}" class="btn btn-sm btn-outline-primary rounded-pill ms-1 d-inline-flex align-items-center" style="white-space:nowrap;">
                    <i class="fas fa-th-large me-2"></i>{{ __('ui.community.disease_cards') }}
                </a>
                @auth
                    @if(! $isAdminCommunity)
                        <a href="{{ $isStarredPage ? route('community.posts.index') : route('community.posts.starred') }}" class="btn btn-sm {{ $isStarredPage ? 'btn-outline-primary' : 'btn-warning' }} rounded-pill ms-1 d-inline-flex align-items-center" style="white-space:nowrap;">
                            <i class="fas fa-star me-2"></i>{{ $isStarredPage ? __('ui.community.all_posts') : __('ui.community.starred_posts') }}
                        </a>
                    @endif
                    <a href="{{ $isPendingPage ? ($isAdminCommunity ? route('admin.community.posts.index') : route('community.posts.index')) : ($isAdminCommunity ? route('admin.community.posts.pending') : route('community.posts.pending')) }}" class="btn btn-sm {{ $isPendingPage ? 'btn-outline-primary' : 'btn-outline-warning' }} rounded-pill ms-1 d-inline-flex align-items-center" style="white-space:nowrap;">
                        <i class="fas fa-hourglass-half me-2"></i>{{ $isPendingPage ? __('ui.community.all_posts') : __('ui.community.pending_posts') }}
                    </a>
                @endauth
                <a href="{{ auth()->check() ? route('users.index') : route('login') }}" class="btn btn-sm btn-primary rounded-pill ms-2 d-inline-flex align-items-center" style="white-space:nowrap;">
                    <i class="fas fa-users me-2"></i> {{ __('ui.community.browse_members') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Bar - Only visible on mobile/tablet, contains all filters moved down -->
    <div class="mobile-filter-bar">
        <div class="filter-row">
            <select id="mobileDiseaseFilter" onchange="filterByDisease(this.value)">
                <option value="all" {{ !request('disease') ? 'selected' : '' }}>{{ __('ui.community.all_posts') }}</option>
                @foreach($diseases as $disease)
                    <option value="{{ $disease->id }}" {{ request('disease') == $disease->id ? 'selected' : '' }}>
                        {{ $disease->display_name }} ({{ $disease->posts_count }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-row" style="margin-top: 8px;">
            <div class="action-buttons">
                <a href="{{ route('community.home') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="fas fa-th-large"></i> {{ __('ui.community.disease_cards') }}
                </a>
                @auth
                    @if(! $isAdminCommunity)
                        <a href="{{ $isStarredPage ? route('community.posts.index') : route('community.posts.starred') }}" class="btn btn-sm {{ $isStarredPage ? 'btn-outline-primary' : 'btn-warning' }} rounded-pill">
                            <i class="fas fa-star"></i> {{ $isStarredPage ? __('ui.community.all_posts') : __('ui.community.starred_posts') }}
                        </a>
                    @endif
                    <a href="{{ $isPendingPage ? ($isAdminCommunity ? route('admin.community.posts.index') : route('community.posts.index')) : ($isAdminCommunity ? route('admin.community.posts.pending') : route('community.posts.pending')) }}" class="btn btn-sm {{ $isPendingPage ? 'btn-outline-primary' : 'btn-outline-warning' }} rounded-pill">
                        <i class="fas fa-hourglass-half"></i> {{ $isPendingPage ? __('ui.community.all_posts') : __('ui.community.pending_posts') }}
                    </a>
                @endauth
                <a href="{{ auth()->check() ? route('users.index') : route('login') }}" class="btn btn-sm btn-primary rounded-pill">
                    <i class="fas fa-users"></i> {{ __('ui.community.browse_members') }}
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
                    {{ __('ui.community.quick_filters') }}
                </h5>
                <div class="quick-filter-buttons">
                    <button class="quick-filter-btn {{ !request('disease') ? 'active' : '' }}" onclick="filterByDisease('all')">
                        <i class="fas {{ $isPendingPage ? 'fa-hourglass-half' : ($isStarredPage ? 'fa-star' : 'fa-globe') }} me-2"></i>
                        <span class="filter-name">{{ $isPendingPage ? __('ui.community.all_pending_posts') : ($isStarredPage ? __('ui.community.all_starred_posts') : __('ui.community.all_posts')) }}</span>
                        <span class="filter-count">{{ $totalPosts }}</span>
                    </button>
                    @foreach($diseases as $disease)
                        <button class="quick-filter-btn {{ request('disease') == $disease->id ? 'active' : '' }}" 
                                onclick="filterByDisease({{ $disease->id }})">
                            <i class="fas fa-heartbeat me-2"></i>
                            <span class="filter-name">
                                {{ $disease->display_name }}
                            </span>
                            <span class="filter-count">{{ $disease->posts_count }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="filter-card">
                <h5 class="filter-title">
                    <i class="fas fa-info-circle me-2" style="color: #17a2b8;"></i>
                    {{ __('ui.community.guidelines') }}
                </h5>
                <ul class="guidelines-list">
                    <li><i class="fas fa-check-circle me-2" style="color: #28a745;"></i>{{ __('ui.community.be_respectful') }}</li>
                    <li><i class="fas fa-check-circle me-2" style="color: #28a745;"></i>{{ __('ui.community.no_medical_misinformation') }}</li>
                    <li><i class="fas fa-check-circle me-2" style="color: #28a745;"></i>{{ __('ui.community.protect_your_privacy') }}</li>
                    <li><i class="fas fa-check-circle me-2" style="color: #28a745;"></i>{{ __('ui.community.report_inappropriate_content') }}</li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                @if($isAdminCommunity && ! $isPendingPage)
                    <div class="filter-card" style="margin-bottom: 20px; border: 1px solid #e7d5ff; box-shadow: 0 8px 20px rgba(122, 63, 184, 0.12);">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom: 12px;">
                            <h5 class="filter-title" style="margin:0; color:#5b21b6;">
                                <i class="fas fa-shield-check me-2" style="color:#7a3fb8;"></i>
                                {{ __('ui.community.post_approval_queue') }}
                            </h5>
                            <a href="{{ route('admin.community.posts.pending') }}" class="btn btn-sm btn-outline-warning rounded-pill">
                                <i class="fas fa-list me-1"></i>{{ __('ui.community.view_all_pending') }}
                            </a>
                        </div>

                        @if($pendingPreviewPosts->isEmpty())
                            <div style="background:#faf5ff; border:1px dashed #d8b4fe; border-radius:10px; padding:14px; color:#6b21a8;">
                                {{ __('ui.community.no_pending_posts') }}
                            </div>
                        @else
                            <div style="display:grid; gap:10px;">
                                @foreach($pendingPreviewPosts as $pendingPost)
                                    <div style="background:#faf5ff; border:1px solid #e9d5ff; border-radius:10px; padding:12px;">
                                        <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                                            <div>
                                                <div style="font-weight:600; color:#4c1d95;">{{ $pendingPost->is_anonymous ? __('ui.community.anonymous_member') : $pendingPost->user->name }}</div>
                                                <div style="font-size:12px; color:#6d28d9; margin-top:2px;">{{ optional($pendingPost->disease)->display_name ?? 'General' }} • {{ $pendingPost->created_at->diffForHumans() }}</div>
                                            </div>
                                            <span class="badge text-bg-warning">{{ __('ui.community.pending') }}</span>
                                        </div>
                                        <div style="margin-top:8px; color:#2b2b2b; font-size:14px; line-height:1.4;">
                                            {{ \Illuminate\Support\Str::limit($pendingPost->description, 140) }}
                                        </div>
                                        <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openPostModal({{ $pendingPost->id }})">
                                                <i class="fas fa-eye me-1"></i>{{ __('ui.community.preview') }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" onclick="approvePost({{ $pendingPost->id }})">
                                                <i class="fas fa-check me-1"></i>{{ __('ui.community.approve') }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $pendingPost->id }}, 'post')">
                                                <i class="fas fa-trash me-1"></i>{{ __('ui.community.reject_delete') }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Create Post -->
                @auth
                    @if(! $isAdminCommunity)
                    <div class="create-post-trigger" onclick="openCreatePostModal()">
                        <div class="trigger-avatar">
                            @if(Auth::user()->picture)
                                <img src="{{ asset('storage/' . Auth::user()->picture) }}" alt="{{ Auth::user()->name }}">
                            @else
                                <div class="avatar-placeholder">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            @endif
                        </div>
                        <div class="trigger-input">
                            <div class="trigger-placeholder">{{ __('ui.community.how_do_you_feel_today') }}</div>
                        </div>
                        <div class="trigger-icons">
                            <button class="trigger-icon" title="{{ __('ui.community.photos') }}"><i class="fas fa-image"></i></button>
                            <button class="trigger-icon" title="{{ __('ui.community.video') }}"><i class="fas fa-video"></i></button>
                        </div>
                    </div>

                    <!-- Create Post Modal -->
                    <div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title">
                                        <i class="fas fa-pen-fancy me-2" style="color: #1877f2;"></i>
                                        {{ __('ui.community.create_post') }}
                                    </h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('ui.actions.close') }}" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>

                                <div class="modal-body">
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
                                                <option value="">{{ __('ui.community.select_disease') }}</option>
                                                @foreach($diseases as $disease)
                                                    <option value="{{ $disease->id }}">
                                                        {{ $disease->display_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div style="position: relative;">
                                        <textarea id="createPostContent" 
                                                  class="modal-textarea" 
                                                  placeholder="{{ __('ui.community.write_your_health_experiences') }}" 
                                                  rows="5"
                                                  oninput="updateCreatePostCharCounter()"></textarea>
                                        
                                        <div class="char-counter-display">
                                            <span id="createPostCharCount">0</span> / <span>5000</span>
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#65676b; cursor:pointer;">
                                            <input type="checkbox" id="createPostAnonymous" style="cursor:pointer;">
                                            {{ __('ui.community.post_anonymously') }}
                                        </label>
                                    </div>

                                    <div class="file-upload-info">
                                        <i class="fas fa-info-circle"></i>
                                        <span>{{ __('ui.community.max_10mb_per_file') }}</span>
                                        <span class="file-upload-stats" id="fileUploadStats">0 {{ __('ui.community.files') }}</span>
                                    </div>

                                    <div id="createPostFilePreview" class="file-preview-area" style="display: none;">
                                        <div class="file-preview-grid" id="createPostFilePreviewGrid"></div>
                                    </div>
                                </div>

                                <div class="modal-footer border-top">
                                    <div style="flex: 1; display: flex; gap: 8px; flex-wrap: wrap;">
                                        <label class="modal-file-btn">
                                            <i class="fas fa-image me-1"></i>{{ __('ui.community.photo') }}
                                            <input type="file" id="createPostImage" class="d-none" accept="image/*" multiple onchange="handleCreatePostFileSelect(this)">
                                        </label>
                                        <label class="modal-file-btn">
                                            <i class="fas fa-video me-1"></i>{{ __('ui.community.video') }}
                                            <input type="file" id="createPostVideo" class="d-none" accept="video/*" multiple onchange="handleCreatePostFileSelect(this)">
                                        </label>
                                        <label class="modal-file-btn">
                                            <i class="fas fa-file me-1"></i>{{ __('ui.community.file') }}
                                            <input type="file" id="createPostFile" class="d-none" accept=".pdf,.doc,.docx,.txt,.xls,.xlsx" multiple onchange="handleCreatePostFileSelect(this)">
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('ui.community.cancel') }}</button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="submitCreatePost()" id="createPostSubmitBtn">
                                        <i class="fas fa-paper-plane me-1"></i>{{ __('ui.community.post') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endauth

                @guest
                    <div class="guest-banner">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h4>{{ __('ui.community.join_community') }}</h4>
                        <p>{{ __('ui.community.connect_with_others') }}</p>
                        <div class="guest-buttons">
                            <a href="{{ route('register') }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>{{ __('ui.community.sign_up') }}
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>{{ __('ui.community.login') }}
                            </a>
                        </div>
                    </div>
                @endguest

                <!-- Posts Feed -->
                <div id="postsFeed">
                    @forelse($posts as $post)
                        @include('community.partials.post', ['post' => $post, 'adminReadOnlyCommunity' => $isAdminCommunity])
                    @empty
                        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                            <i class="fas fa-comments fa-4x mb-3" style="color: #adb5bd;"></i>
                            <h5>{{ $isPendingPage ? __('ui.community.no_pending_posts') : ($isStarredPage ? __('ui.community.no_starred_posts_yet') : __('ui.community.no_discussions_yet')) }}</h5>
                            <p style="color: #65676b;">{{ $isPendingPage ? __('ui.community.new_posts_will_appear') : ($isStarredPage ? __('ui.community.star_posts_to_collect') : __('ui.community.start_conversation')) }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper mt-4">
                    @if ($posts->hasPages())
                        <ul class="pagination">
                            @if ($posts->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">{{ __('ui.community.prev') }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $posts->previousPageUrl() }}" rel="prev">{{ __('ui.community.prev') }}</a></li>
                            @endif

                            @foreach ($posts->links()->elements as $element)
                                @if (is_string($element))
                                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                                @endif
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

                            @if ($posts->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $posts->nextPageUrl() }}" rel="next">{{ __('ui.community.next') }}</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link">{{ __('ui.community.next') }}</span></li>
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
                    {{ __('ui.community.community_stats') }}
                </h5>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($totalUsers) }}</div>
                        <div class="stat-label">{{ __('ui.community.members') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($totalPosts) }}</div>
                        <div class="stat-label">{{ __('ui.community.posts') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($totalComments) }}</div>
                        <div class="stat-label">{{ __('ui.community.comments') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($activeToday) }}</div>
                        <div class="stat-label">{{ __('ui.community.active_today') }}</div>
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
                                <a href="{{ route('public.disease.show', $disease) }}" class="text-decoration-none">
                                    {{ $disease->display_name }}
                                </a>
                            </div>
                        </div>
                        <span class="trending-count">{{ $disease->posts_count }}</span>
                    </div>
                @endforeach
            </div>

            <div class="support-card">
                <i class="fas fa-headset support-icon"></i>
                <h6>{{ __('ui.community.need_help') }}</h6>
                <p class="text-muted small">{{ __('ui.community.support_available') }}</p>
                <a href="{{ route('help') }}" class="btn-support">
                    <i class="fas fa-question-circle me-2"></i>{{ __('ui.community.get_help') }}
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ==================== COMMUNITY-SPECIFIC FUNCTIONS ONLY ====================

// Sync mobile filter with desktop filter (only needed when both exist)
document.addEventListener('DOMContentLoaded', function() {
    const desktopFilter = document.getElementById('diseaseFilter');
    const mobileFilter = document.getElementById('mobileDiseaseFilter');
    
    if (desktopFilter && mobileFilter) {
        // Sync mobile filter with desktop filter when changed
        mobileFilter.addEventListener('change', function() {
            desktopFilter.value = this.value;
        });
        
        desktopFilter.addEventListener('change', function() {
            mobileFilter.value = this.value;
        });
    }
});

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
        showToast(`{{ __('ui.community.some_files_exceed_10mb') }}`, 'warning');
        input.value = '';
        return;
    }

    createPostFiles = [...createPostFiles, ...files];
    input.value = '';
    
    const totalSize = createPostFiles.reduce((sum, f) => sum + f.size, 0);
    const maxTotalSize = 50 * 1024 * 1024;
    
    if (totalSize > maxTotalSize) {
        showToast(`{{ __('ui.community.total_file_size_exceeds_50mb') }}`, 'warning');
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
        if (statsEl) statsEl.textContent = '0 {{ __("ui.community.files") }}';
        return;
    }

    const totalSize = createPostFiles.reduce((sum, f) => sum + f.size, 0);
    if (statsEl) statsEl.textContent = `${createPostFiles.length} {{ __("ui.community.files") }} • ${formatFileSize(totalSize)}`;
    
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
    document.getElementById('fileUploadStats').textContent = '0 {{ __("ui.community.files") }}';
}

function submitCreatePost() {
    const content = document.getElementById('createPostContent').value.trim();
    const diseaseId = document.getElementById('createPostDiseaseId').value;
    const isAnonymous = document.getElementById('createPostAnonymous')?.checked ? '1' : '0';

    if (!content && createPostFiles.length === 0) {
        showToast('{{ __("ui.community.please_write_something_or_add_file") }}', 'warning');
        return;
    }

    if (!diseaseId) {
        showToast('{{ __("ui.community.please_select_disease") }}', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('disease_id', diseaseId);
    formData.append('description', content);
    formData.append('is_anonymous', isAnonymous);

    createPostFiles.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
    });

    const submitBtn = document.getElementById('createPostSubmitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("ui.community.posting") }}...';

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
            const anonymousCheckbox = document.getElementById('createPostAnonymous');
            if (anonymousCheckbox) anonymousCheckbox.checked = false;
            createPostModal.hide();

            if (data.html) {
                const postsFeed = document.getElementById('postsFeed');
                postsFeed.insertAdjacentHTML('afterbegin', data.html);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            showToast(`✅ ${data.message || '{{ __("ui.community.post_submitted_for_approval") }}'}`, 'success');
        } else {
            showToast('❌ ' + (data.message || '{{ __("ui.community.error_creating_post") }}'), 'error');
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
    const anonymousCheckbox = document.getElementById('createPostAnonymous');
    if (anonymousCheckbox) anonymousCheckbox.checked = false;
});

// ==================== FILTER ====================
function filterByDisease(diseaseId) {
    const baseRoute = @json($isAdminCommunity
        ? ($isPendingPage ? route('admin.community.posts.pending') : route('admin.community.posts.index'))
        : ($isPendingPage ? route('community.posts.pending') : ($isStarredPage ? route('community.posts.starred') : route('community.posts.index'))));
    if (diseaseId === 'all') {
        window.location.href = baseRoute;
    } else {
        if (!@json($isPendingPage || $isStarredPage || $isAdminCommunity)) {
            window.location.href = '/community/disease/' + diseaseId + '/posts';
            return;
        }
        window.location.href = baseRoute + '?disease=' + diseaseId;
    }
}

function reportPost(postId) {
    fetch(`/community/posts/${postId}/report`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`✅ ${data.message || '{{ __("ui.community.post_reported_successfully") }}'}`, 'success');
        } else {
            showToast('❌ ' + (data.message || '{{ __("ui.community.unable_to_report_post") }}'), 'error');
        }
    })
    .catch(error => {
        showToast('❌ ' + error.message, 'error');
    });
}

function approvePost(postId) {
    const approveBase = @json($isAdminCommunity ? '/admin/community/posts/' : '/community/posts/');
    fetch(`${approveBase}${postId}/approve`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`✅ ${data.message || '{{ __("ui.community.post_approved_successfully") }}'}`, 'success');
            const postElement = document.getElementById(`post-${postId}`);
            if (postElement && @json($isPendingPage)) {
                postElement.remove();
            }
        } else {
            showToast('❌ ' + (data.message || '{{ __("ui.community.unable_to_approve_post") }}'), 'error');
        }
    })
    .catch(error => {
        showToast('❌ ' + error.message, 'error');
    });
}

// ==================== TOGGLE COMMENTS ====================
function toggleComments(postId) {
    const section = document.getElementById(`comments-section-${postId}`);
    const btn = document.getElementById(`toggle-comments-${postId}`);
    
    if (!section || !btn) return;
    
    const count = btn.querySelector('.comment-count').textContent;

    if (!section.style.display || section.style.display === 'none') {
        section.style.display = 'block';
        btn.innerHTML = `<i class="fas fa-chevron-up me-1"></i><span class="comment-count">${count}</span> {{ __("ui.community.hide_comments") }}`;
    } else {
        section.style.display = 'none';
        btn.innerHTML = `<i class="far fa-comment me-1"></i><span class="comment-count">${count}</span> {{ __("ui.community.comments") }}`;
    }
}

function loadMoreComments(postId) {
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