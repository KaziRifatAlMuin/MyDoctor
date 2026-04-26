<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $rawPageTitle = trim((string) View::yieldContent('title'));
        if ($rawPageTitle === '') {
            $segments = array_filter(request()->segments(), function ($s) {
                return $s !== null && $s !== '' && !is_numeric($s);
            });
            if (!empty($segments)) {
                $joined = implode(' ', $segments);
                $rawPageTitle = \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $joined));
            } else {
                $rawPageTitle = 'Home';
            }
        }

        $normalizedPageTitle = preg_replace('/\s*-\s*My\s*Doctor\s*$/i', '', $rawPageTitle) ?? $rawPageTitle;
        $normalizedPageTitle = preg_replace('/\s*-\s*MyDoctor\s*$/i', '', $normalizedPageTitle) ?? $normalizedPageTitle;
        $normalizedPageTitle = trim($normalizedPageTitle);

        // Site-wide SEO defaults
        $siteName = config('app.name', 'MyDoctor');
        $siteBase = rtrim(config('app.url', env('APP_URL', 'https://mydoctor.rifatalmuin.com')), '/');

        $seoTitle = ($normalizedPageTitle !== '' ? $normalizedPageTitle : 'Home') . ' - ' . $siteName;

        $seoDescription = trim((string) View::yieldContent('meta_description', $siteName . ' is a smart healthcare companion for tracking health metrics, medicine schedules, reminders, and community support.'));
        $seoKeywords = trim((string) View::yieldContent('meta_keywords', 'MyDoctor, healthcare app, medicine reminder, health tracking, symptom tracking, disease management, Bangladesh health'));

        $rawSeoImage = trim((string) View::yieldContent('meta_image', asset('images/logos/applogo_white.jpg')));
        $seoImage = \Illuminate\Support\Str::startsWith($rawSeoImage, ['http://', 'https://']) ? $rawSeoImage : $siteBase . '/' . ltrim($rawSeoImage, '/');
        $seoUrl = $siteBase . request()->getRequestUri();
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ env('VAPID_PUBLIC_KEY') }}">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="MyDoctor">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $seoUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="MyDoctor">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:image" content="{{ $seoImage }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logos/applogo_white.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logos/applogo_white.jpg') }}" type="image/jpeg">
    <link rel="apple-touch-icon" href="{{ asset('images/logos/applogo_white.jpg') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        /* ==================== YOUR EXISTING STYLES ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background-color: #ffffff;
            padding-top: 84px;
        }

        .bn-label {
            display: none !important;
        }

        html[lang^='bn'] .bn-label {
            display: inline !important;
        }

        /* Ensure all avatars are perfect circles */
        .user-avatar,
        .user-avatar img,
        .user-avatar div,
        .comment-avatar,
        .comment-avatar img,
        .comment-avatar div,
        .trigger-avatar,
        .trigger-avatar img,
        .trigger-avatar div,
        .modal-avatar,
        .modal-avatar img,
        .modal-avatar div,
        .user-circle,
        .user-circle img,
        .user-circle span {
            border-radius: 50% !important;
        }

        /* Ensure comment avatars are properly sized */
        .comment-avatar {
            width: 44px !important;
            height: 44px !important;
            min-width: 44px !important;
            min-height: 44px !important;
            border-radius: 50% !important;
            overflow: hidden !important;
            flex-shrink: 0 !important;
        }

        .comment-avatar img,
        .comment-avatar .avatar-placeholder-small {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 50% !important;
        }

        /* User avatar in post cards */
        .user-avatar {
            width: 48px !important;
            height: 48px !important;
            min-width: 48px !important;
            min-height: 48px !important;
            border-radius: 50% !important;
            overflow: hidden !important;
        }

        .user-avatar img,
        .user-avatar .avatar-placeholder {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 50% !important;
        }

        /* Banner Styles */
        .banner {
            position: relative;
            width: 100%;
            height: 400px;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('{{ asset('images/banners/Home banner.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Compact navbar for non-home pages */
        .page-nav-bar {
            position: relative;
            width: 100%;
            background: transparent;
            border-bottom: none;
            box-shadow: none;
        }

        .page-nav-bar .banner-nav {
            padding: 12px 40px;
        }

        /* Dark text links on white navbar */
        /* Navbar Styles - Positioned absolutely on banner */
        .banner-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 12px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1001;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .nav-theme-admin .banner-nav {
            background: linear-gradient(135deg, #4b0082 0%, #7a3fb8 40%, #9b59ff 100%);
            border-bottom: none;
            box-shadow: 0 4px 18px rgba(75, 0, 130, 0.35);
        }

        /* Logo Styles */
        .banner-logo {
            width: 50px;
            height: 50px;
        }

        .banner-logo img {
            width: 100%;
            height: auto;
        }

        /* Admin: make logo appear white on purple navbar */
        .nav-theme-admin .banner-logo img {
            filter: brightness(0) invert(1) saturate(0.8);
        }

        /* Navigation Menu */
        .banner-nav-menu {
            display: flex;
            gap: 2.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .banner-nav-item {
            position: relative;
        }

        .banner-nav-link {
            text-decoration: none;
            color: #2d3748;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
        }

        .banner-nav-link:hover {
            color: #667eea;
            border-bottom: 2px solid #667eea;
        }

        .banner-nav-link.active {
            color: #667eea;
            border-bottom: 2px solid #667eea;
        }

        .nav-theme-admin .banner-nav-link {
            color: #ffffff;
        }

        .nav-theme-admin .banner-nav-link:hover,
        .nav-theme-admin .banner-nav-link.active {
            color: #ffffff;
            border-bottom-color: #ffffff;
        }

        /* User Circle Menu */
        .user-menu {
            position: relative;
        }

        .user-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #667eea;
            overflow: hidden;
            transition: transform 0.3s ease;
            background-color: #fff;
        }

        .user-circle:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }

        .user-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-circle span {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
            line-height: 40px;
            text-align: center;
            width: 100%;
            display: block;
        }

        /* Dropdown Menu */
        .user-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
            min-width: 220px;
            display: none;
            z-index: 1000;
            overflow: hidden;
        }

        .user-dropdown.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .dropdown-header h6 {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .dropdown-header p {
            margin: 5px 0 0;
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .dropdown-item-custom {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .dropdown-item-custom:last-child {
            border-bottom: none;
        }

        .dropdown-item-custom:hover {
            background-color: #f8f9fa;
            color: #667eea;
            padding-left: 25px;
        }

        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 8px 0;
        }

        /* Notification Badge in dropdown */
        .notification-badge {
            background-color: #f56565;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
        }

        /* ========== UPDATED NOTIFICATION BELL STYLES ========== */
        .notification-bell,
        .mailbox-bell {
            position: relative;
            margin-right: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            color: #374151;
            background: transparent;
        }

        .notification-bell i,
        .mailbox-bell i {
            font-size: 1.15rem;
            line-height: 1;
        }

        .language-toggle-switch {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            width: 80px;
            height: 30px;
            padding: 0 8px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            color: #374151;
            background: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            overflow: hidden;
        }

        .language-toggle-switch .toggle-label {
            position: relative;
            z-index: 2;
            color: #6b7280;
            transition: color 0.25s ease;
            font-size: 0.72rem;
            line-height: 1;
        }

        .language-toggle-switch .toggle-knob {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 38px;
            height: 22px;
            border-radius: 6px;
            background: #111827;
            transition: transform 0.25s ease;
            z-index: 1;
        }

        .language-toggle-switch.is-bn .toggle-knob {
            transform: translateX(36px);
        }

        .language-toggle-switch.is-en .toggle-label-en,
        .language-toggle-switch.is-bn .toggle-label-bn {
            color: #ffffff;
        }

        .notification-bell .badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #28a745 !important;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            animation: popIn 0.3s ease;
        }

        .mailbox-bell .badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #dc3545 !important;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .nav-theme-admin .notification-bell,
        .nav-theme-admin .mailbox-bell {
            color: #ffffff;
            background: transparent;
        }

        .nav-theme-admin .language-toggle-switch {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.35);
        }

        .nav-theme-admin .language-toggle-switch .toggle-knob {
            background: rgba(255, 255, 255, 0.95);
        }

        .nav-theme-admin .language-toggle-switch .toggle-label {
            color: rgba(255, 255, 255, 0.78);
        }

        .nav-theme-admin .language-toggle-switch.is-en .toggle-label-en,
        .nav-theme-admin .language-toggle-switch.is-bn .toggle-label-bn {
            color: #111827;
        }

        .notification-bell:hover,
        .mailbox-bell:hover {
            transform: translateY(-1px);
            background: rgba(102, 126, 234, 0.08);
        }

        .language-toggle-switch:hover {
            transform: translateY(-1px);
        }

        @keyframes popIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* Bell shake animation when new notification arrives */
        @keyframes shake {
            0% { transform: rotate(0deg); }
            10% { transform: rotate(15deg); }
            20% { transform: rotate(-15deg); }
            30% { transform: rotate(10deg); }
            40% { transform: rotate(-10deg); }
            50% { transform: rotate(5deg); }
            60% { transform: rotate(-5deg); }
            70% { transform: rotate(2deg); }
            80% { transform: rotate(-2deg); }
            90% { transform: rotate(1deg); }
            100% { transform: rotate(0deg); }
        }

        .notification-bell.shake i {
            animation: shake 0.5s ease-in-out;
        }

        .notification-bell.shake {
            animation: shake 0.5s ease-in-out;
        }

        .notification-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            width: 450px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
            overflow: hidden;
        }

        .notification-dropdown.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        .notification-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h6 {
            margin: 0;
            font-size: 0.9rem;
        }

        .notification-header button {
            background: none;
            border: none;
            color: white;
            font-size: 0.8rem;
            cursor: pointer;
            opacity: 0.9;
        }

        .notification-header button:hover {
            opacity: 1;
            text-decoration: underline;
        }

        .notification-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 15px 20px;
            display: flex;
            gap: 12px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.unread {
            background-color: #e8f0fe;
        }

        .notification-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        .notification-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .notification-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-message {
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: #1a1a1a;
        }

        .notification-preview {
            font-weight: 700;
            font-size: 0.8rem;
            color: #65676b;
            margin-top: 4px;
            padding: 6px 8px;
            background: #f0f2f5;
            border-radius: 8px;
            line-height: 1.4;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #65676b;
        }

        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #65676b;
        }

        .notification-empty i {
            font-size: 48px;
            color: #e0e0e0;
            margin-bottom: 15px;
        }

        .notification-footer {
            padding: 12px 20px;
            text-align: center;
            border-top: 1px solid #f0f0f0;
        }

        .notification-footer a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .notification-footer a:hover {
            text-decoration: underline;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-user-name {
            font-size: 0.84rem;
            font-weight: 600;
            color: #1f2937;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nav-theme-admin .nav-user-name {
            color: #ffffff;
        }

        /* Banner Content */
        .banner-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            width: 80%;
            max-width: 800px;
            z-index: 90;
        }

        .banner-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .banner-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        /* Main Content */
        .app-root {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            flex: 1 0 auto;
        }

        .main-content--wide {
            width: min(1600px, calc(100vw - 24px));
            max-width: min(1600px, calc(100vw - 24px));
            padding-inline: 16px;
        }

        @media (max-width: 768px) {
            .main-content--wide {
                width: 100%;
                max-width: 100%;
                padding-inline: 12px;
            }
        }

        /* Chatbot Icon */
        .chatbot-icon {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 78px;
            height: 78px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            z-index: 1102;
            border: 3px solid white;
        }

        .chatbot-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 35px rgba(102, 126, 234, 0.6);
        }

        .chatbot-icon.glow-pulse {
            animation: chatbotGlowPulse 1.2s ease-in-out infinite;
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.65), 0 8px 35px rgba(102, 126, 234, 0.7);
        }

        @keyframes chatbotGlowPulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.55), 0 8px 30px rgba(102, 126, 234, 0.55);
            }

            60% {
                transform: scale(1.06);
                box-shadow: 0 0 0 14px rgba(102, 126, 234, 0), 0 10px 38px rgba(102, 126, 234, 0.75);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0), 0 8px 30px rgba(102, 126, 234, 0.55);
            }
        }

        .chatbot-icon i {
            color: white;
            font-size: 34px;
        }

        .chatbot-tooltip {
            position: absolute;
            right: 92px;
            background: white;
            color: #4a5568;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            white-space: nowrap;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .chatbot-icon:hover .chatbot-tooltip {
            opacity: 1;
        }

        .chatbot-icon.show-tooltip .chatbot-tooltip {
            opacity: 1;
        }

        /* Chatbot Modal */
        .chatbot-modal {
            position: fixed;
            bottom: 120px;
            right: 30px;
            width: min(520px, calc(100vw - 36px));
            height: min(760px, calc(100vh - 150px));
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1101;
            overflow: hidden;
            flex-direction: column;
        }

        .chatbot-modal.show {
            display: flex;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chatbot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chatbot-header h5 {
            margin: 0;
            font-size: 1.1rem;
        }

        .chatbot-header button {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .chatbot-messages {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            background: #f8fafc;
        }

        .chatbot-settings {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
            font-size: 0.82rem;
        }

        .chatbot-settings input[type="checkbox"] {
            accent-color: #667eea;
            cursor: pointer;
        }

        .chatbot-settings label {
            cursor: pointer;
            user-select: none;
        }

        .typing-bubble {
            background: #f0f2f5;
            border-radius: 18px 18px 18px 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            color: #6b7280;
            font-size: 0.92rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .bot-message-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .bot-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
            font-size: 0.85rem;
        }

        .bot-message-bubble {
            background: #f0f2f5;
            color: #1a1a1a;
            padding: 12px 18px;
            border-radius: 18px 18px 18px 0;
            display: inline-block;
            max-width: 80%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .typing-dots {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .typing-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #667eea;
            opacity: 0.35;
            animation: typingPulse 1s infinite ease-in-out;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.18s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: 0.36s;
        }

        @keyframes typingPulse {
            0%,
            80%,
            100% {
                transform: translateY(0);
                opacity: 0.35;
            }

            40% {
                transform: translateY(-4px);
                opacity: 1;
            }
        }

        .chatbot-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chatbot-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .chatbot-messages::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .chatbot-messages::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .chatbot-input {
            padding: 15px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            background: white;
        }

        .chatbot-input input {
            flex: 1;
            padding: 12px 18px;
            border: 1px solid #e2e8f0;
            border-radius: 25px;
            outline: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .chatbot-input input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .chatbot-input button {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chatbot-input button:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .chatbot-input button i {
            font-size: 1.2rem;
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #1a2639 0%, #2c3e50 100%);
            color: #fff;
            padding: 60px 0 20px;
            margin-top: 50px;
            position: relative;
            z-index: 1;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradientMove 3s ease infinite;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .footer-logo {
            display: flex;
            align-items: center;
        }

        .footer-logo img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            filter: brightness(0) invert(1);
        }

        .footer-text {
            color: #a0aec0;
            line-height: 1.8;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        /* Social Links */
        .social-links {
            display: flex;
            gap: 10px;
        }

        .social-link {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: translateY(-3px);
            color: #fff;
        }

        /* Footer Titles */
        .footer-title {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        /* Footer Links */
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links li a {
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 0.95rem;
        }

        .footer-links li a:hover {
            color: #fff;
            transform: translateX(5px);
        }

        .footer-links li a i {
            font-size: 0.8rem;
            color: #667eea;
        }

        /* Contact Info */
        .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
            color: #a0aec0;
            font-size: 0.95rem;
        }

        .footer-contact li i {
            color: #667eea;
            font-size: 1.2rem;
            margin-top: 3px;
            min-width: 20px;
        }

        .footer-contact li span {
            flex: 1;
            line-height: 1.6;
        }

        /* Newsletter */
        .newsletter .input-group {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            overflow: hidden;
        }

        .newsletter .form-control {
            background: transparent;
            border: none;
            color: #fff;
            padding: 12px 20px;
        }

        .newsletter .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .newsletter .form-control:focus {
            box-shadow: none;
            background: rgba(255, 255, 255, 0.15);
        }

        .newsletter .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 20px;
            border-radius: 0 30px 30px 0;
        }

        .newsletter .btn-primary:hover {
            transform: scale(1.05);
        }

        /* Footer Divider */
        .footer-divider {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 30px 0;
        }

        /* Copyright */
        .copyright {
            color: #a0aec0;
            font-size: 0.9rem;
        }

        /* Footer Bottom Links */
        .footer-bottom-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: flex-end;
            gap: 20px;
        }

        .footer-bottom-links li a {
            color: #a0aec0;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-bottom-links li a:hover {
            color: #fff;
        }

        /* Responsive Footer */
        @media (max-width: 768px) {
            .footer {
                padding: 40px 0 20px;
            }

            .footer-title {
                margin-bottom: 20px;
            }

            .footer-bottom-links {
                justify-content: center;
                margin-top: 15px;
                flex-wrap: wrap;
            }

            .social-links {
                justify-content: center;
            }

            .footer-logo {
                justify-content: center;
            }

            .footer-text {
                text-align: center;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .banner-nav {
                padding: 15px;
                flex-wrap: wrap;
            }

            body {
                padding-top: 112px;
            }

            .banner-nav-menu {
                order: 3;
                width: 100%;
                margin-top: 15px;
                justify-content: center;
                gap: 1rem;
            }

            .banner-content h1 {
                font-size: 2rem;
            }

            .banner-content p {
                font-size: 1rem;
            }

            .banner {
                height: 500px;
            }

            .chatbot-modal {
                width: calc(100vw - 24px);
                height: calc(100vh - 120px);
                right: 15px;
                left: 12px;
                bottom: 90px;
            }

            .notification-dropdown {
                width: 340px;
                right: -20px;
            }
        }

        /* Hero Section with Background Image */
        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }

        .hero-bg-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2;
        }

        /* Statistics Card Styles */
        .statistics-card {
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .backdrop-blur {
            backdrop-filter: blur(10px);
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .col-lg-5 {
                margin-top: 2rem;
            }
        }

        /* Floating Animation */
        .floating-animation {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        /* Remove old banner styles if they conflict */
        .banner {
            min-height: 600px;
            position: relative;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .banner {
                min-height: 500px;
            }

            .banner h1 {
                font-size: 2rem;
            }
        }

        /* Post Modal Styles */
        .modal-lg {
            max-width: 800px;
        }

        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            border-bottom: 1px solid #e4e6eb;
            padding: 16px 20px;
            background: white;
            border-radius: 16px 16px 0 0;
        }

        .modal-header .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .modal-header .btn-close {
            background: #f0f2f5;
            padding: 8px;
            border-radius: 50%;
            opacity: 1;
            transition: all 0.2s;
        }

        .modal-header .btn-close:hover {
            background: #e4e6eb;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 0;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* ==================== COMMUNITY MODAL STYLES ==================== */
        .modal-post-container {
            background: white;
            color: #1a1a1a;
            font-family: inherit;
        }

        .modal-post-container * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Post Header */
        .modal-post-container .post-header {
            padding: 16px 16px 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #e4e6eb;
        }

        .modal-post-container .post-user {
            display: flex;
            gap: 12px;
            cursor: pointer;
        }

        .modal-post-container .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        .modal-post-container .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-post-container .avatar-placeholder {
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

        .modal-post-container .user-name {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            color: #1a1a1a;
        }

        .modal-post-container .post-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: #65676b;
            margin: 0;
        }

        .modal-post-container .post-disease-badge {
            background: #e7f3ff;
            color: #1877f2;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Post Content */
        .modal-post-container .post-text-content {
            margin: 0;
            line-height: 1.5;
            font-size: 15px;
            color: #1a1a1a;
            white-space: pre-wrap;
        }

        .modal-post-container .post-link {
            color: #1877f2;
            text-decoration: none;
            word-break: break-all;
        }

        .modal-post-container .post-link:hover {
            text-decoration: underline;
            color: #0e5a9e;
        }

        /* Post Action Buttons */
        .modal-post-container .post-action-buttons {
            display: flex;
            gap: 8px;
            margin: 12px 16px 0;
            padding: 0;
        }

        .modal-post-container .post-action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background: #f0f2f5;
            color: #1a1a1a;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .modal-post-container .post-action-btn:hover {
            background: #e4e6eb;
        }

        .modal-post-container .post-action-btn.liked {
            background: #fee !important;
            color: #dc3545 !important;
        }

        .modal-post-container .post-action-btn.liked i {
            color: #dc3545 !important;
        }

        /* Comments Section */
        .modal-post-container .comments-section {
            margin: 12px 0 0;
            padding: 16px;
            border-top: 1px solid #e4e6eb;
        }

        .modal-post-container .comment {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }

        .modal-post-container .comment:last-child {
            margin-bottom: 0;
        }

        .modal-post-container .comment-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            cursor: pointer;
        }

        .modal-post-container .comment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-post-container .avatar-placeholder-small {
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

        .modal-post-container .comment-content {
            flex: 1;
            background: #f0f2f5;
            padding: 10px 12px;
            border-radius: 18px;
        }

        .modal-post-container .comment-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .modal-post-container .comment-author {
            font-weight: 600;
            font-size: 13px;
            color: #1a1a1a;
            cursor: pointer;
        }

        .modal-post-container .comment-author:hover {
            text-decoration: underline;
            color: #1877f2;
        }

        .modal-post-container .comment-time {
            font-size: 11px;
            color: #65676b;
        }

        .modal-post-container .comment-text {
            font-size: 13px;
            color: #1a1a1a;
            margin-bottom: 6px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .modal-post-container .comment-like-btn {
            background: none !important;
            border: none !important;
            outline: none !important;
            color: #65676b;
            font-size: 11px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 0;
        }

        .modal-post-container .comment-like-btn.liked {
            color: #dc3545 !important;
        }

        .modal-post-container .comment-like-btn.liked i {
            color: #dc3545 !important;
        }

        .modal-post-container .comment-attachment {
            margin-top: 8px;
        }

        .modal-post-container .comment-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            cursor: pointer;
        }

        .modal-post-container .comment-link {
            color: #1877f2;
            text-decoration: none;
            word-break: break-all;
            display: inline;
        }

        .modal-post-container .comment-link:hover {
            text-decoration: underline;
            color: #0e5a9e;
        }

        /* Comment Form */
        .modal-post-container .comment-form {
            margin-top: 16px;
        }

        .modal-post-container .comment-input-group {
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }

        .modal-post-container .comment-input-wrapper {
            flex: 1;
            display: flex;
            gap: 6px;
            background: #f0f2f5;
            border-radius: 20px;
            padding: 4px 4px 4px 12px;
            align-items: center;
        }

        .modal-post-container .comment-textarea {
            flex: 1;
            border: none;
            background: transparent;
            padding: 8px 0;
            font-size: 13px;
            resize: none;
            outline: none;
            min-height: 32px;
            max-height: 80px;
            font-family: inherit;
            line-height: 1.4;
        }

        .modal-post-container .comment-submit-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 50%;
            background: #1877f2;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .modal-post-container .comment-submit-btn:hover:not(:disabled) {
            background: #166fe5;
            transform: scale(1.05);
        }

        .modal-post-container .comment-submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Video Embed */
        .modal-post-container .video-embed-wrapper {
            width: 100%;
            margin: 12px 0 0;
            padding: 0;
            display: flex;
        }

        .modal-post-container .video-embed-container {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            background: #000;
        }

        .modal-post-container .video-embed-container.reel-container {
            width: auto;
            max-width: 360px;
            aspect-ratio: 9/16;
        }

        .modal-post-container .video-embed-container:hover .fa-play {
            transform: scale(1.2);
        }

        /* Post Attachments */
        .modal-post-container .post-attachments {
            margin: 12px 0 0;
            padding: 0;
        }

        .modal-post-container .single-file-container {
            margin: 0 0 16px;
            padding: 0;
        }

        .modal-post-container .multiple-files-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: 0;
            padding: 0;
        }

        .modal-post-container .file-item {
            border: 1px solid #e4e6eb;
            border-radius: 10px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .modal-post-container .file-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Edit/Delete Icons */
        .modal-post-container button[onclick*="editPost"],
        .modal-post-container button[onclick*="confirmDelete"] {
            transition: all 0.2s;
        }

        .modal-post-container button[onclick*="editPost"]:hover {
            background: #e7f3ff !important;
            transform: scale(1.1);
        }

        .modal-post-container button[onclick*="confirmDelete"]:hover {
            background: #fee !important;
            transform: scale(1.1);
        }

        /* Spinner */
        .modal-post-container .spinner-small {
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

        /* ==================== UNIFIED COMMENT FILE PREVIEW ==================== */
        .comment-file-preview {
            display: none;
            margin-top: 8px;
            width: 100%;
        }

        .comment-file-preview > div {
            padding: 8px 12px;
            background: #f0f2f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .file-preview-content {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .file-preview-content img {
            max-height: 40px;
            border-radius: 4px;
        }

        .file-preview-content i {
            font-size: 20px;
            color: #1877f2;
        }

        .remove-file {
            background: none;
            border: none;
            color: #65676b;
            cursor: pointer;
            padding: 4px 8px;
            transition: color 0.2s;
        }

        .remove-file:hover {
            color: #dc3545;
        }

        /* ==================== EDIT/DELETE MODAL STYLES ==================== */
        #editModal .modal-content,
        #deleteModal .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        #editModal .modal-header,
        #deleteModal .modal-header {
            border-bottom: 1px solid #e4e6eb;
            padding: 16px 20px;
            background: white;
            border-radius: 16px 16px 0 0;
        }

        #editModal .modal-title,
        #deleteModal .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
        }

        #editModal .modal-title i,
        #deleteModal .modal-title i {
            color: #1877f2;
        }

        #editModal .modal-body,
        #deleteModal .modal-body {
            padding: 20px;
        }

        #editModal .form-control {
            border: 1px solid #e4e6eb;
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
        }

        #editModal .form-control:focus {
            outline: none;
            border-color: #1877f2;
            box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.1);
        }

        #editModal .char-counter {
            font-size: 11px;
            color: #65676b;
            margin-top: 4px;
            text-align: right;
        }

        #editModal .modal-footer,
        #deleteModal .modal-footer {
            border-top: 1px solid #e4e6eb;
            padding: 12px 20px;
        }

        #editModal .btn-primary {
            background: #1877f2;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
        }

        #editModal .btn-primary:hover {
            background: #166fe5;
        }

        #editModal .btn-light,
        #deleteModal .btn-light {
            background: #f0f2f5;
            border: 1px solid #e4e6eb;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
        }

        #editModal .btn-light:hover,
        #deleteModal .btn-light:hover {
            background: #e4e6eb;
        }

        #deleteModal .btn-danger {
            background: #dc3545;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
        }

        #deleteModal .btn-danger:hover {
            background: #c82333;
        }

        /* Close button styling for all modals */
        .modal-header .close {
            background: none;
            border: none;
            font-size: 24px;
            line-height: 1;
            opacity: 0.5;
            cursor: pointer;
            transition: opacity 0.2s;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .modal-header .close:hover {
            opacity: 1;
            background: #f0f2f5;
        }

        /* User Modal Styles */
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

        .activity-item {
            transition: background 0.2s;
            cursor: pointer;
        }

        .activity-item:hover {
            background: #e4e6eb !important;
        }

        .activity-item a {
            color: inherit;
            display: block;
        }
        
        /* Medicine Reminder Modal Styles */
        .modal-reminder-container {
            background: white;
            color: #1a1a1a;
            font-family: inherit;
        }

        .modal-reminder-container * {
            box-sizing: border-box;
        }

        .modal-reminder-container button {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-reminder-container button:hover {
            transform: translateY(-1px);
        }
    </style>

    @stack('styles')
</head>

<body>
    @php
        $maintenance = \Illuminate\Support\Facades\Cache::get('maintenance_mode', config('app.maintenance_mode', false));
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        // Allow auth-related pages and home page to render normally
        $isAuthPage = request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*') || request()->routeIs('home');

        if ($maintenance === true && ! $isAdmin && ! $isAuthPage) {
            echo view('maintenance')->render();
            exit;
        }
    @endphp
    <div class="app-root">
        <!-- Banner / Navbar -->
        @php
            $isAdminNav = auth()->check() && auth()->user()->isAdmin();
            $navThemeClass = $isAdminNav ? 'nav-theme-admin' : 'nav-theme-regular';
            $dashboardRoute = $isAdminNav ? route('admin.dashboard') : route('dashboard');
            $profileDashboardRoute = auth()->check() ? route('dashboard') : route('home');
            $canUseChatbot = auth()->check() && !auth()->user()->isAdmin();
            $activeUserSettings = auth()->check() ? auth()->user()->setting : null;
            $showNotificationBadge = $activeUserSettings?->show_notification_badge ?? true;
            $showMailBadge = $activeUserSettings?->show_mail_badge ?? true;
            $showChatbotBubble = $activeUserSettings?->show_chatbot ?? true;
        @endphp
        <div class="{{ request()->routeIs('home') ? 'banner' : 'page-nav-bar' }} {{ $navThemeClass }}">
            <!-- Navigation on Banner -->
            <nav class="banner-nav">
                <!-- Logo -->
                <div class="banner-logo">
                    <img src="{{ asset('images/logos/applogo.png') }}" alt="MyDoctor Logo">
                </div>

                <!-- Navigation Menu -->
                <ul class="banner-nav-menu">
                    @if ($isAdminNav)
                        <li class="banner-nav-item">
                            <a href="{{ route('admin.dashboard') }}"
                                class="banner-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                {{ __('ui.admin_tabs.dashboard') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ route('admin.users.index') }}"
                                class="banner-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                {{ __('ui.admin_tabs.users') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ route('admin.diseases.index') }}"
                                class="banner-nav-link {{ request()->routeIs('admin.diseases.*') ? 'active' : '' }}">
                                {{ __('ui.admin_tabs.diseases') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ route('admin.symptoms.index') }}"
                                class="banner-nav-link {{ request()->routeIs('admin.symptoms.*') ? 'active' : '' }}">
                                {{ __('ui.admin_tabs.symptoms') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ route('admin.health.index') }}"
                                class="banner-nav-link {{ request()->routeIs('admin.health.*') || request()->routeIs('admin.metrics.*') ? 'active' : '' }}">
                                {{ __('ui.admin_tabs.health') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ route('admin.community.posts.index') }}"
                                class="banner-nav-link {{ request()->routeIs('admin.community.*') ? 'active' : '' }}">
                                {{ __('ui.admin_tabs.community') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ route('admin.logs.index') }}"
                                class="banner-nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                                {{ __('ui.admin_tabs.logs') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ route('admin.backups.index') }}"
                                class="banner-nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                                {{ __('ui.admin_tabs.backups') }}
                            </a>
                        </li>
                    @else
                        <li class="banner-nav-item">
                            <a href="{{ route('home') }}"
                                class="banner-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                {{ __('ui.nav.home') }}
                            </a>
                        </li>
                        @auth
                            <li class="banner-nav-item">
                                <a href="{{ $dashboardRoute }}"
                                    class="banner-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    {{ __('ui.nav.dashboard') }}
                                </a>
                            </li>
                        @endauth
                        <li class="banner-nav-item">
                            <a href="{{ auth()->check() ? route('medicine.index', [], false) : route('login', [], false) . '?redirect=' . urlencode(route('medicine.index', [], false)) }}"
                                class="banner-nav-link {{ request()->routeIs('medicine*') ? 'active' : '' }}"
                                title="{{ auth()->check() ? '' : __('ui.nav.login_required') }}">
                                {{ __('ui.nav.medicine') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ auth()->check() ? route('health', [], false) : route('login', [], false) . '?redirect=' . urlencode(route('health', [], false)) }}"
                                class="banner-nav-link {{ request()->routeIs('health*') ? 'active' : '' }}"
                                title="{{ auth()->check() ? '' : __('ui.nav.login_required') }}">
                                {{ __('ui.nav.health') }}
                            </a>
                        </li>
                        <li class="banner-nav-item">
                            <a href="{{ auth()->check() ? route('community.home', [], false) : route('login', [], false) . '?redirect=' . urlencode(route('community.home', [], false)) }}"
                                class="banner-nav-link {{ request()->routeIs('community*') ? 'active' : '' }}"
                                title="{{ auth()->check() ? '' : __('ui.nav.login_required') }}">
                                {{ __('ui.nav.community') }}
                            </a>
                        </li>
                        @auth
                            <li class="banner-nav-item">
                                <a href="{{ auth()->check() ? route('suggestions', [], false) : route('login', [], false) . '?redirect=' . urlencode(route('suggestions', [], false)) }}"
                                    class="banner-nav-link {{ request()->routeIs('suggestions') ? 'active' : '' }}"
                                    title="{{ auth()->check() ? '' : __('ui.nav.login_required') }}">
                                    {{ __('ui.nav.suggestions') }}
                                </a>
                            </li>
                        @endauth
                        <li class="banner-nav-item">
                            <a href="{{ route('help') }}"
                                class="banner-nav-link {{ request()->routeIs('help*') ? 'active' : '' }}">
                                {{ __('ui.nav.help') }}
                            </a>
                        </li>
                    @endif
                </ul>

                <!-- Right Side Navigation (Notifications + User Menu) -->
                <div class="nav-right">
                    @auth
                        @php
                            $unreadMailCount = \App\Models\Mailing::query()
                                ->where('receiver_id', auth()->id())
                                ->where('status', 'unread')
                                ->count();
                        @endphp

                        <a href="{{ route('language.switch', ['locale' => app()->getLocale() === 'en' ? 'bn' : 'en']) }}"
                           class="language-toggle-switch {{ app()->getLocale() === 'bn' ? 'is-bn' : 'is-en' }}"
                           title="{{ __('ui.nav.switch_language') }}"
                           aria-label="{{ __('ui.nav.switch_language') }}">
                            <span class="toggle-label toggle-label-en">EN</span>
                            <span class="toggle-label toggle-label-bn">BN</span>
                            <span class="toggle-knob" aria-hidden="true"></span>
                        </a>

                        <!-- Mailbox -->
                        <a href="{{ route('profile.mailbox') }}" class="mailbox-bell" id="mailboxBell" title="{{ __('ui.nav.mailbox') }}">
                            <i class="fas fa-envelope"></i>
                            <span class="badge" id="mailboxCount" style="display: {{ $showMailBadge && $unreadMailCount > 0 ? 'inline-block' : 'none' }};">
                                {{ $unreadMailCount }}
                            </span>
                        </a>

                        @if (! $isAdminNav)
                            <!-- Notifications -->
                            <div class="notification-bell" id="notificationBell" onclick="toggleNotificationDropdown()">
                                <i class="fas fa-bell"></i>
                                <span class="badge" id="notificationCount" style="display: none;">0</span>
                            </div>

                            <span class="nav-user-name">{{ auth()->user()->name }}</span>

                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h6>{{ __('ui.nav.notifications') }}</h6>
                                    <button onclick="markAllNotificationsRead()">{{ __('ui.nav.mark_all_read') }}</button>
                                </div>
                                <div class="notification-list" id="notificationList">
                                    <div class="notification-empty">
                                        <p>{{ __('ui.nav.no_notifications') }}</p>
                                    </div>
                                </div>
                                <div class="notification-footer">
                                    <a href="{{ route('notifications.index') }}">{{ __('ui.nav.view_all_notifications') }}</a>
                                </div>
                            </div>
                        @else
                            <span class="nav-user-name">{{ auth()->user()->name }}</span>
                        @endif
                    @endauth

                    @guest
                        <a href="{{ route('language.switch', ['locale' => app()->getLocale() === 'en' ? 'bn' : 'en']) }}"
                           class="language-toggle-switch {{ app()->getLocale() === 'bn' ? 'is-bn' : 'is-en' }}"
                           title="{{ __('ui.nav.switch_language') }}"
                           aria-label="{{ __('ui.nav.switch_language') }}">
                            <span class="toggle-label toggle-label-en">EN</span>
                            <span class="toggle-label toggle-label-bn">BN</span>
                            <span class="toggle-knob" aria-hidden="true"></span>
                        </a>
                        <span class="nav-user-name">
                            <a href="{{ route('login', [], false) }}" style="text-decoration:none;color:inherit;">{{ __('ui.menu.login') }}</a>
                            /
                            <a href="{{ route('register', [], false) }}" style="text-decoration:none;color:inherit;">{{ __('ui.menu.register') }}</a>
                        </span>
                    @endguest

                    <!-- User Circle Menu -->
                    <div class="user-menu" id="userMenu">
                        <div class="user-circle" onclick="toggleUserDropdown()" title="{{ auth()->check() ? auth()->user()->name : __('ui.menu.guest') }}">
                            @auth
                                @if (auth()->user()->picture)
                                    <img src="{{ asset('storage/' . auth()->user()->picture) }}"
                                        alt="{{ auth()->user()->name }}">
                                @else
                                    <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                @endif
                            @else
                                <span>?</span>
                            @endauth
                        </div>

                        <div class="user-dropdown" id="userDropdown">
                            @auth
                                <!-- LOGGED IN USER MENU -->
                                <div class="dropdown-header">
                                    <h6>{{ auth()->user()->name }}</h6>
                                    <p>{{ auth()->user()->email }}</p>
                                </div>

                                @if (auth()->user()->isAdmin())
                                    @php
                                        $maintenance = \Illuminate\Support\Facades\Cache::get('maintenance_mode', config('app.maintenance_mode', false));
                                    @endphp
                                    <div class="dropdown-item-custom d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="fas fa-tools me-2"></i>Maintenance Mode
                                        </div>
                                        <button type="button" id="maintenanceToggleBtn" onclick="toggleMaintenance()"
                                            class="btn btn-sm {{ $maintenance ? 'btn-danger' : 'btn-outline-secondary' }}">
                                            <span id="maintenanceToggleText">{{ $maintenance ? 'ON' : 'OFF' }}</span>
                                        </button>
                                    </div>
                                @endif

                                @if (! auth()->user()->isAdmin())
                                    <a href="{{ $profileDashboardRoute }}" class="dropdown-item-custom">
                                        <i class="fas fa-tachometer-alt me-2"></i>{{ __('ui.nav.dashboard') }}
                                    </a>
                                @endif

                                <a href="{{ route('profile') }}" class="dropdown-item-custom">
                                    <i class="fas fa-user me-2"></i>{{ __('ui.menu.profile') }}
                                </a>

                                <a href="{{ route('profile.logs') }}" class="dropdown-item-custom">
                                    <i class="fas fa-stream me-2"></i>{{ __('ui.menu.activity_logs') }}
                                </a>

                                @if (! auth()->user()->isAdmin())
                                    <a href="{{ route('notifications.index') }}" class="dropdown-item-custom" id="notification-link">
                                        <i class="fas fa-bell me-2"></i>{{ __('ui.nav.notifications') }}
                                        <span class="notification-badge" id="notificationCountBadge" style="display: none;">0</span>
                                    </a>
                                @endif

                                <a href="{{ route('profile.mailbox') }}" class="dropdown-item-custom">
                                    <i class="fas fa-envelope me-2"></i>{{ __('ui.nav.mailbox') }}
                                </a>

                                <div class="divider"></div>
                                <a href="{{ route('profile.setting') }}" class="dropdown-item-custom">
                                    <i class="fas fa-cog me-2"></i>{{ __('ui.menu.settings') }}
                                </a>

                                <div class="divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item-custom"
                                        style="width: 100%; border: none; background: none; cursor: pointer; color: #dc3545;">
                                        <i class="fas fa-sign-out-alt me-2"></i>{{ __('ui.menu.logout') }}
                                    </button>
                                </form>
                            @else
                                <!-- GUEST USER MENU -->
                                <a href="{{ route('login', [], false) }}" class="dropdown-item-custom">
                                    <i class="fas fa-sign-in-alt me-2"></i>{{ __('ui.menu.login') }}
                                </a>

                                <a href="{{ route('register', [], false) }}" class="dropdown-item-custom">
                                    <i class="fas fa-user-plus me-2"></i>{{ __('ui.menu.register') }}
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

@if (request()->routeIs('home'))
    <!-- Banner Hero Section (Home only) -->
    <div class="banner position-relative overflow-hidden">
        <div class="container position-relative z-3 py-5">
            <div class="row min-vh-50 align-items-center justify-content-start">
                <div class="col-lg-9 text-white text-start">
                    <h1 class="display-3 fw-bold mt-5 pt-4 mb-4">{{ __('ui.layout.banner_title') }}<br>{{ __('ui.layout.banner_title_our') }} <span
                            class="text-warning">{{ __('ui.layout.banner_title_priority') }}</span></h1>
                    <p class="lead mb-4">{{ __('ui.layout.banner_subtitle') }}</p>

                    @guest
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="{{ route('login', [], false) }}?redirect={{ urlencode(route('dashboard', [], false)) }}"
                                class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                                {{ __('ui.layout.get_started') }} <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                            <a href="{{ route('login', [], false) }}?redirect={{ urlencode(request()->fullUrl()) }}"
                                class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                                <i class="fas fa-user-md me-2"></i>{{ __('ui.layout.ask_ai') }}
                            </a>
                        </div>
                    @else
                        <div class="d-flex gap-3">
                            <a href="{{ route('dashboard') }}"
                                class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                                {{ __('ui.layout.dashboard') }} <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                            @if (!auth()->user()->isAdmin())
                                <button onclick="toggleChatbot()"
                                    class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                                    <i class="fas fa-user-md me-2"></i>{{ __('ui.layout.ask_ai') }}
                                </button>
                            @endif
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
@endif
        </div>

        <!-- Main Content -->
        <main class="@yield('main_content_class', 'main-content')">
            @yield('content')
        </main>

        @if ($canUseChatbot)
            <!-- Chatbot Icon -->
            <div class="chatbot-icon" id="chatbotIcon" onclick="toggleChatbot()">
                <i class="fas fa-user-md"></i>
                <span class="chatbot-tooltip">{{ __('ui.layout.chatbot_tooltip') }}</span>
            </div>

            <!-- Chatbot Modal -->
            <div class="chatbot-modal" id="chatbotModal">
                <div class="chatbot-header">
                    <h5><i class="fas fa-user-md me-2"></i>MyDoctor AI</h5>
                    <button onclick="toggleChatbot()"><i class="fas fa-times"></i></button>
                </div>

                <!-- Disclaimer Banner -->
                <div class="bg-warning bg-opacity-10 p-2 text-center small" style="border-bottom: 1px solid #dee2e6;">
                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                    AI-powered health information - consult a doctor for medical advice
                </div>

                <div class="chatbot-messages" id="chatMessages">
                    <div style="text-align: center; color: #718096; padding: 20px;">
                        <i class="fas fa-user-md fa-3x mb-3" style="color: #667eea;"></i>
                        <p>Hello! I'm MyDoctor AI, your personal health assistant.<br>How can I help you today?</p>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Ask about symptoms, diet, exercise, or general health tips
                        </small>
                    </div>
                </div>
                <div class="chatbot-input">
                    <input type="text" placeholder="Type your health question..." id="chatInput">
                    <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        @endif
    </div>

    <!-- ==================== ALL MODALS ==================== -->
    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center">
                      <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                        <span aria-hidden="true">×</span>
                    </button>
                    <img src="" id="modalImage" class="img-fluid" style="max-height: 90vh;">
                </div>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 position-absolute top-0 end-0 z-3">
                   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-0 text-center">
                    <div id="videoModalContent" style="position: relative; width: 100%; background: #000; border-radius: 8px; overflow: hidden;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Post Modal -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalLabel">
                        <i class="fas fa-file-alt me-2" style="color: #1877f2;"></i>
                        Post
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-0" id="postModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading post...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Medicine Reminder Modal -->
    <div class="modal fade" id="medicineReminderModal" tabindex="-1" aria-labelledby="medicineReminderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-body p-0" id="medicineReminderModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading reminder details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Profile Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">
                        <i class="fas fa-user-circle me-2 text-primary"></i>
                        User Profile
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="userModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Edit Content
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="editContent" rows="6" placeholder="Edit your content..."></textarea>
                    <div class="char-counter mt-2">
                        <span id="editCharCount">0</span> / 5000
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEditBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="footer-logo mb-3">
                        <img src="{{ asset('images/logos/applogo.png') }}" alt="MyDoctor" height="40">
                        <span class="fw-bold text-white ms-2">{{ __('ui.meta.app_name') }}</span>
                    </div>
                    <p class="footer-text">
                        {{ __('ui.footer.about_text') }}
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h5 class="footer-title">{{ __('ui.footer.quick_links') }}</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                        <li><a href="{{ route('medicine.index') }}"><i class="fas fa-chevron-right me-2"></i>Medicine</a></li>
                        <li><a href="{{ route('health') }}"><i class="fas fa-chevron-right me-2"></i>Health</a></li>
                        <li><a href="{{ route('community.landing') }}"><i class="fas fa-chevron-right me-2"></i>Community</a></li>
                        <li><a href="{{ route('help') }}"><i class="fas fa-chevron-right me-2"></i>Help</a></li>
                    </ul>
                </div>

                <!-- Features -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="footer-title">{{ __('ui.footer.key_features') }}</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('health.tracking') }}"><i class="fas fa-chevron-right me-2"></i>Health Metrics</a></li>
                        <li><a href="{{ route('medicine.reminders') }}"><i class="fas fa-chevron-right me-2"></i>Medicine Reminders</a></li>
                        <li><a href="{{ route('health') }}#logs"><i class="fas fa-chevron-right me-2"></i>Medical Records</a></li>
                        <li><a href="{{ route('health.symptoms') }}"><i class="fas fa-chevron-right me-2"></i>Symptom Tracker</a></li>
                        <li><a href="{{ route('suggestions') }}"><i class="fas fa-chevron-right me-2"></i>AI Suggestions</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="footer-title">{{ __('ui.footer.contact_us') }}</h5>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i><span>{{ __('ui.footer.address') }}</span></li>
                        <li><i class="fas fa-phone"></i><span>{{ __('ui.footer.phone') }}</span></li>
                        <li><i class="fas fa-envelope"></i><span>{{ __('ui.footer.email') }}</span></li>
                        <li><i class="fas fa-clock"></i><span>{{ __('ui.footer.support_hours') }}</span></li>
                    </ul>
                </div>
            </div>

            <!-- Divider -->
            <hr class="footer-divider">

            <!-- Bottom Bar -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="copyright mb-0">
                        &copy; {{ date('Y') }} {{ __('ui.meta.app_name') }}. {{ __('ui.footer.rights_reserved') }} | {{ __('ui.footer.tagline') }}
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="footer-bottom-links">
                        <li><a href="{{ route('privacy.policy') }}">{{ __('ui.footer.privacy_policy') }}</a></li>
                        <li><a href="{{ route('terms.service') }}">{{ __('ui.footer.terms_of_service') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ==================== UNIFIED COMMUNITY MODAL SCRIPTS ==================== -->
    <script>
        // ==================== GLOBAL VARIABLES ====================
        window.currentEditId = null;
        window.currentEditType = null;
        window.deleteId = null;
        window.deleteType = null;
        
        // Global storage for comment files
        if (!window.modalCommentFiles) {
            window.modalCommentFiles = {};
        }

        // Helper function to find post content
        window.findPostContent = function(postId) {
            let element = document.getElementById(`post-content-${postId}`);
            if (element) return element;
            
            element = document.querySelector(`.modal-post-container #post-content-${postId}`);
            if (element) return element;
            
            const modalContainer = document.querySelector(`.modal-post-container[data-post-id="${postId}"]`);
            if (modalContainer) {
                element = modalContainer.querySelector('p[style*="white-space: pre-wrap"]');
                if (element) return element;
                element = modalContainer.querySelector('.post-text, .post-text-content');
                if (element) return element;
            }
            
            return null;
        };

        // ==================== POST MODAL FUNCTIONS ====================
        window.getCommunityBasePath = function() {
            return window.location.pathname.startsWith('/admin/community') ? '/admin/community' : '/community';
        };

        window.openPostModal = function(postId, scrollToComments = false) {
            const modalBody = document.getElementById('postModalBody');
            if (!modalBody) {
                console.error('Post modal body not found');
                return;
            }
            
            modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading post...</p></div>';
            
            const postModalEl = document.getElementById('postModal');
            if (!postModalEl) {
                console.error('Post modal element not found');
                return;
            }
            
            const postModal = new bootstrap.Modal(postModalEl);
            postModal.show();
            
            window.scrollToCommentsInModal = scrollToComments || (window.scrollToCommentsPostId === postId);
            if (window.scrollToCommentsInModal) {
                delete window.scrollToCommentsPostId;
            }
            
            fetch(`${window.getCommunityBasePath()}/modal-post/${postId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Post is deleted or cannot be loaded');
                    }
                    return response.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                    
                    const commentsSection = document.getElementById(`comments-section-${postId}`);
                    if (commentsSection) {
                        commentsSection.style.display = 'block';
                    }
                    
                    if (window.scrollToCommentsInModal) {
                        setTimeout(() => {
                            const commentsContainer = document.getElementById(`comments-container-${postId}`);
                            if (commentsContainer) {
                                commentsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                            window.scrollToCommentsInModal = false;
                        }, 500);
                    }
                    
                    setupModalInteractions(postId);
                })
                .catch(error => {
                    console.error('Error loading post:', error);
                    
                    // Show deleted post message with notification data if available
                    let deletedHtml = `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 400px; padding: 40px 20px; text-align: center;">
                            <div style="font-size: 64px; color: #dc3545; margin-bottom: 20px;">
                                <i class="fas fa-trash"></i>
                            </div>
                            <h3 style="color: #333; margin-bottom: 10px;">Post Deleted by Admin</h3>
                            <p style="color: #666; font-size: 16px; margin-bottom: 20px;">
                                This post has been removed from the community.
                            </p>
                            <button class="btn btn-secondary" onclick="document.getElementById('postModal').closest('.modal').querySelector('[data-bs-dismiss=modal]')?.click() || document.getElementById('postModal').parentElement.querySelector('[data-bs-dismiss=modal]')?.click() || (function() { const m = bootstrap.Modal.getInstance(document.getElementById('postModal')); if (m) m.hide(); })()">
                                Back to Community
                            </button>
                        </div>
                    `;
                    
                    modalBody.innerHTML = deletedHtml;
                });
        };

        window.setupModalInteractions = function(postId) {
            document.querySelectorAll('#postModalBody textarea').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });
        };

        // ==================== TOGGLE COMMENTS MODAL ====================
        window.toggleCommentsModal = function(postId) {
            const commentsSection = document.getElementById(`comments-section-${postId}`);
            if (!commentsSection) return;
            
            const isVisible = commentsSection.style.display !== 'none' && commentsSection.style.display !== '';
            const toggleBtn = document.getElementById(`toggle-comments-modal-btn-${postId}`);
            
            if (isVisible) {
                commentsSection.style.display = 'none';
                if (toggleBtn) {
                    toggleBtn.innerHTML = '<i class="far fa-comment"></i><span class="comment-count" data-post="' + postId + '">' + toggleBtn.querySelector('.comment-count').textContent + '</span>';
                }
            } else {
                commentsSection.style.display = 'block';
                if (toggleBtn) {
                    toggleBtn.innerHTML = '<i class="fas fa-chevron-up"></i><span class="comment-count" data-post="' + postId + '">' + toggleBtn.querySelector('.comment-count').textContent + '</span>';
                }
            }
        };

        // ==================== MEDICINE REMINDER MODAL FUNCTIONS ====================
        
        window.openMedicineReminderModal = function(reminderId) {
            const modalBody = document.getElementById('medicineReminderModalBody');
            if (!modalBody) {
                console.error('Medicine reminder modal body not found');
                return;
            }
            
            modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading reminder details...</p></div>';
            
            const reminderModalEl = document.getElementById('medicineReminderModal');
            if (!reminderModalEl) {
                console.error('Medicine reminder modal element not found');
                return;
            }
            
            const reminderModal = new bootstrap.Modal(reminderModalEl);
            reminderModal.show();
            
            fetch(`/medicine/reminders/${reminderId}/modal`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Reminder not found');
                    }
                    return response.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading reminder:', error);
                    modalBody.innerHTML = '<div class="alert alert-danger m-3">Reminder not found or cannot be loaded</div>';
                });
        };

        window.markReminderTakenFromModal = function(reminderId) {
            if (!confirm('Are you sure you want to mark this medicine as taken?')) {
                return;
            }
            
            fetch(`/medicine/reminders/${reminderId}/taken-from-notification`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Medicine marked as taken!', 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('medicineReminderModal'));
                    if (modal) modal.hide();
                    setTimeout(() => {
                        if (typeof loadNotifications === 'function') {
                            loadNotifications();
                        }
                    }, 500);
                } else {
                    showToast(data.message || 'Failed to mark as taken', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error marking as taken', 'error');
            });
        };

        // ==================== UNIFIED FILE HANDLING ====================
        if (!window.modalCommentFiles) {
            window.modalCommentFiles = {};
        }

        // ==================== MODAL-SPECIFIC FUNCTIONS ====================
        window.submitModalComment = async function(event, postId) {
            event.preventDefault();
            
            const textarea = document.getElementById(`modal-comment-input-${postId}`);
            if (!textarea) {
                console.error('Modal textarea not found for postId:', postId);
                return;
            }
            
            const commentText = textarea.value.trim();
            const file = window.modalCommentFiles ? window.modalCommentFiles[postId] : null;
            
            if (!commentText && !file) { 
                if (typeof window.showToast === "function") {
                    window.showToast('Please write something or attach a file', 'warning');
                }
                return; 
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('comment_details', commentText);
            if (file) formData.append('file', file);

            const submitBtn = document.getElementById(`modal-comment-submit-${postId}`);
            if (!submitBtn) return;
            
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-small"></span>';

            try {
                const res = await fetch(`${window.getCommunityBasePath()}/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.success) {
                    const container = document.getElementById(`comments-container-${postId}`);
                    if (container) {
                        if (container.innerHTML.includes("No comments")) container.innerHTML = "";
                        container.insertAdjacentHTML('afterbegin', data.html);
                    }
                    
                    textarea.value = ''; 
                    textarea.style.height = 'auto';
                    window.clearModalCommentFile(postId);
                    
                    const commentCounts = document.querySelectorAll(`.comment-count[data-post="${postId}"]`);
                    commentCounts.forEach(el => { if (el) el.textContent = data.comment_count; });
                    
                    if (typeof window.showToast === "function") {
                        window.showToast('Comment added!', 'success');
                    }
                }
            } catch (err) { 
                console.error(err); 
                if (typeof window.showToast === "function") {
                    window.showToast('Error adding comment', 'error');
                }
            } finally { 
                submitBtn.disabled = false; 
                submitBtn.innerHTML = originalHtml; 
            }
        };

        window.handleModalCommentFileSelect = function(postId, input) {
            const file = input.files[0];
            if (!file) return;

            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                if (typeof window.showToast === "function") {
                    window.showToast('File size cannot exceed 5MB', 'warning');
                }
                input.value = "";
                return;
            }

            if (!window.modalCommentFiles) window.modalCommentFiles = {};
            window.modalCommentFiles[postId] = file;

            const previewArea = document.getElementById(`modal-comment-file-preview-${postId}`);
            const previewContent = document.getElementById(`modal-comment-file-preview-content-${postId}`);
            
            if (!previewArea || !previewContent) {
                console.error('Modal preview elements not found for postId:', postId);
                return;
            }
            
            previewContent.innerHTML = '';

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContent.innerHTML = `
                        <img src="${e.target.result}" style="max-height:40px; border-radius:4px; margin-right:8px;">
                        <span style="font-size:12px;">${file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name}</span>
                        <span style="font-size:11px; color:#65676b; margin-left:4px;">(${(file.size/1024).toFixed(1)} KB)</span>
                    `;
                    previewArea.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                let icon = 'fa-file-alt';
                if (file.type.includes('pdf')) icon = 'fa-file-pdf';
                else if (file.type.includes('video')) icon = 'fa-file-video';
                else if (file.type.includes('word')) icon = 'fa-file-word';
                else if (file.type.includes('excel')) icon = 'fa-file-excel';
                
                previewContent.innerHTML = `
                    <i class="fas ${icon}" style="font-size:20px; color:#1877f2; margin-right:8px;"></i>
                    <span style="font-size:12px;">${file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name}</span>
                    <span style="font-size:11px; color:#65676b; margin-left:4px;">(${(file.size/1024).toFixed(1)} KB)</span>
                `;
                previewArea.style.display = 'block';
            }
        };

        window.clearModalCommentFile = function(postId) {
            if (window.modalCommentFiles) {
                window.modalCommentFiles[postId] = null;
                delete window.modalCommentFiles[postId];
            }
            const input = document.getElementById(`modal-comment-file-${postId}`);
            if (input) input.value = '';
            
            const previewArea = document.getElementById(`modal-comment-file-preview-${postId}`);
            if (previewArea) {
                previewArea.style.display = 'none';
            }
        };

        window.handleCommentFileSelect = function(postId, input) {
            const file = input.files[0];
            if (!file) return;

            const maxSize = 5 * 1024 * 1024;

            if (file.size > maxSize) {
                if (typeof window.showToast === 'function') {
                    window.showToast('File size cannot exceed 5MB', 'warning');
                }
                input.value = "";
                return;
            }

            if (!window.modalCommentFiles) window.modalCommentFiles = {};
            window.modalCommentFiles[postId] = file;

            const form = input.closest('form');
            if (!form) {
                console.error('Form not found');
                return;
            }
            
            const previewArea = form.querySelector('.comment-file-preview');
            const previewContent = previewArea?.querySelector('.file-preview-content');

            if (!previewArea || !previewContent) {
                console.error('Preview elements not found');
                return;
            }

            previewContent.innerHTML = '';

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContent.innerHTML = `
                        <img src="${e.target.result}" style="max-height:40px; border-radius:4px; margin-right:8px;">
                        <span style="font-size:12px;">${file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name}</span>
                        <span style="font-size:11px; color:#65676b; margin-left:4px;">(${window.formatFileSize ? window.formatFileSize(file.size) : (file.size/1024).toFixed(1) + ' KB'})</span>
                    `;
                    previewArea.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                let icon = 'fa-file-alt';
                if (file.type.includes('pdf')) icon = 'fa-file-pdf';
                else if (file.type.includes('video')) icon = 'fa-file-video';
                else if (file.type.includes('word')) icon = 'fa-file-word';
                else if (file.type.includes('excel')) icon = 'fa-file-excel';
                
                previewContent.innerHTML = `
                    <i class="fas ${icon}" style="font-size:20px; color:#1877f2; margin-right:8px;"></i>
                    <span style="font-size:12px;">${file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name}</span>
                    <span style="font-size:11px; color:#65676b; margin-left:4px;">(${window.formatFileSize ? window.formatFileSize(file.size) : (file.size/1024).toFixed(1) + ' KB'})</span>
                `;
                previewArea.style.display = 'block';
            }
        };

        window.clearCommentFile = function(postId) {
            if (window.modalCommentFiles) {
                window.modalCommentFiles[postId] = null;
                delete window.modalCommentFiles[postId];
            }

            const fileInput = document.getElementById(`comment-file-${postId}`);
            if (fileInput) {
                const form = fileInput.closest('form');
                if (form) {
                    const previewArea = form.querySelector('.comment-file-preview');
                    if (previewArea) {
                        previewArea.style.display = 'none';
                        const previewContent = previewArea.querySelector('.file-preview-content');
                        if (previewContent) {
                            previewContent.innerHTML = '';
                        }
                    }
                }
                fileInput.value = '';
            }
        };

        // ==================== UNIFIED COMMENT SUBMIT ====================
        window.submitComment = async function(event, postId) {
            event.preventDefault();

            const textarea = document.getElementById(`comment-input-${postId}`);
            if (!textarea) {
                console.error('Textarea not found');
                return;
            }
            
            const comment = textarea.value.trim();
            const file = window.modalCommentFiles ? window.modalCommentFiles[postId] : null;

            if (!comment && !file) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Please write something or attach a file', 'warning');
                }
                return;
            }

            const formData = new FormData();
            formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);
            formData.append("comment_details", comment);

            if (file) {
                formData.append("file", file);
            }

            const submitBtn = document.getElementById(`comment-submit-${postId}`);
            const originalHtml = submitBtn ? submitBtn.innerHTML : '<i class="fas fa-paper-plane"></i>';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-small"></span>';
            }

            try {
                const response = await fetch(`${window.getCommunityBasePath()}/posts/${postId}/comments`, {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    const container = document.getElementById(`comments-container-${postId}`);
                    if (container) {
                        if (container.innerHTML.includes("No comments")) {
                            container.innerHTML = "";
                        }
                        container.insertAdjacentHTML("afterbegin", data.html);
                    }

                    if (textarea) {
                        textarea.value = "";
                        textarea.style.height = "auto";
                    }
                    
                    if (typeof window.clearCommentFile === 'function') {
                        window.clearCommentFile(postId);
                    }

                    const commentCounts = document.querySelectorAll(`#post-${postId} .comment-count, .modal-post-container .comment-count`);
                    commentCounts.forEach(el => {
                        if (el) el.textContent = data.comment_count;
                    });

                    if (typeof window.showToast === 'function') {
                        window.showToast('Comment added!', 'success');
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message || 'Error adding comment', 'error');
                    }
                }
            } catch (error) {
                console.error(error);
                if (typeof window.showToast === 'function') {
                    window.showToast('Error adding comment', 'error');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            }
        };

        // ==================== LIKE FUNCTIONS ====================
        window.toggleLike = async function(postId, button) {
            try {
                const response = await fetch(`${window.getCommunityBasePath()}/posts/${postId}/likes`, {
                    method: "PUT",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const countSpan = button.querySelector(".like-count");
                    if (countSpan) countSpan.textContent = data.count;

                    const icon = button.querySelector("i");
                    
                    if (data.liked) {
                        icon.classList.remove("far");
                        icon.classList.add("fas");
                        button.classList.add("liked");
                        icon.style.color = "#dc3545";
                    } else {
                        icon.classList.remove("fas");
                        icon.classList.add("far");
                        button.classList.remove("liked");
                        icon.style.color = "inherit";
                    }

                }
            } catch (err) {
                console.error("Like error:", err);
            }
        };

        window.toggleStar = async function(postId, button) {
            if (!button) return;

            try {
                const response = await fetch(`${window.getCommunityBasePath()}/posts/${postId}/star`, {
                    method: "PUT",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const starButtons = document.querySelectorAll(`[id="star-btn-${postId}"]`);
                    starButtons.forEach((starButton) => {
                        const starIcon = starButton.querySelector('i');
                        if (data.starred) {
                            starButton.classList.add('starred');
                            if (starIcon) {
                                starIcon.classList.remove('far');
                                starIcon.classList.add('fas', 'text-warning');
                            }
                        } else {
                            starButton.classList.remove('starred');
                            if (starIcon) {
                                starIcon.classList.remove('fas', 'text-warning');
                                starIcon.classList.add('far');
                            }
                        }
                    });

                    const likeButtons = document.querySelectorAll(`[id="like-btn-${postId}"]`);
                    likeButtons.forEach((likeBtn) => {
                        const likeCount = likeBtn.querySelector('.like-count');
                        if (likeCount && typeof data.count !== 'undefined') {
                            likeCount.textContent = data.count;
                        }
                    });

                    if (typeof window.showToast === 'function' && data.message) {
                        window.showToast(data.message, 'success');
                    }
                } else if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'Unable to star post', 'error');
                }
            } catch (err) {
                console.error("Star error:", err);
                if (typeof window.showToast === 'function') {
                    window.showToast('Unable to star post', 'error');
                }
            }
        };

        window.toggleCommentLike = async function(commentId, button) {
            try {
                const response = await fetch(`${window.getCommunityBasePath()}/comments/${commentId}/likes`, {
                    method: "PUT",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const countSpan = button.querySelector(".like-count");
                    if (countSpan) countSpan.textContent = data.count;

                    const icon = button.querySelector("i");
                    
                    if (data.liked) {
                        icon.classList.remove("far");
                        icon.classList.add("fas");
                        button.classList.add("liked");
                        icon.style.color = "#dc3545";
                    } else {
                        icon.classList.remove("fas");
                        icon.classList.add("far");
                        button.classList.remove("liked");
                        icon.style.color = "inherit";
                    }
                }
            } catch (err) {
                console.error("Comment like error:", err);
            }
        };

        // ==================== EDIT FUNCTIONS ====================
        window.editPost = function(postId) {
            window.currentEditId = postId;
            window.currentEditType = 'post';
            
            const contentElement = window.findPostContent(postId);
            
            let content = '';
            if (contentElement) {
                content = contentElement.textContent.trim();
            }
            
            const editContent = document.getElementById('editContent');
            const editCharCount = document.getElementById('editCharCount');
            
            if (editContent) {
                editContent.value = content;
            }
            if (editCharCount) {
                editCharCount.textContent = content.length;
            }
            
            const editModalEl = document.getElementById('editModal');
            if (editModalEl) {
                const editModal = new bootstrap.Modal(editModalEl);
                editModal.show();
            }
        };

        window.editComment = function(commentId) {
            window.currentEditId = commentId;
            window.currentEditType = 'comment';
            
            const contentElement = document.getElementById(`comment-content-${commentId}`);
            if (!contentElement) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Error: Could not find comment content', 'error');
                }
                return;
            }
            
            const content = contentElement.textContent.trim();
            
            const editContent = document.getElementById('editContent');
            const editCharCount = document.getElementById('editCharCount');
            
            if (editContent) {
                editContent.value = content;
            }
            if (editCharCount) {
                editCharCount.textContent = content.length;
            }
            
            const editModalEl = document.getElementById('editModal');
            if (editModalEl) {
                const editModal = new bootstrap.Modal(editModalEl);
                editModal.show();
            }
        };

        // ==================== DELETE FUNCTIONS ====================
        window.confirmDelete = function(id, type) {
            window.deleteId = id;
            window.deleteType = type;
            
            const deleteModalEl = document.getElementById('deleteModal');
            if (deleteModalEl) {
                const deleteModal = new bootstrap.Modal(deleteModalEl);
                deleteModal.show();
            }
        };

        // ==================== USER MODAL FUNCTIONS ====================
        window.showUserModal = function(userId) {
            const modalBody = document.getElementById('userModalBody');
            if (!modalBody) {
                console.error('User modal body not found');
                return;
            }
            
            modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            const userModalEl = document.getElementById('userModal');
            if (!userModalEl) {
                console.error('User modal element not found');
                return;
            }
            
            const userModal = new bootstrap.Modal(userModalEl);
            userModal.show();
            
            fetch(`${window.getCommunityBasePath()}/users/${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderUserModal(data.user);
                    } else {
                        modalBody.innerHTML = '<div class="alert alert-danger">Error loading user details</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = '<div class="alert alert-danger">Failed to load user details</div>';
                });
        };

        window.renderUserModal = function(user) {
            const modalBody = document.getElementById('userModalBody');
            if (!modalBody) return;
            
            let recentActivityHtml = '';
            if (user.recent_posts && user.recent_posts.length > 0) {
                recentActivityHtml += '<div class="mt-3 px-3"><h6 class="text-start px-3 mb-2">Recent Posts</h6>';
                user.recent_posts.forEach(post => {
                    recentActivityHtml += `
                        <div class="activity-item mb-2 p-2 bg-light rounded">
                            <a href="javascript:void(0)" onclick="openPostModal(${post.id})" class="text-decoration-none">
                                <div class="small">${post.description}</div>
                                <small class="text-muted">${post.created_at}</small>
                            </a>
                        </div>
                    `;
                });
                recentActivityHtml += '</div>';
            }
            
            if (user.recent_comments && user.recent_comments.length > 0) {
                recentActivityHtml += '<div class="mt-3 px-3"><h6 class="text-start px-3 mb-2">Recent Comments</h6>';
                user.recent_comments.forEach(comment => {
                    recentActivityHtml += `
                        <div class="activity-item mb-2 p-2 bg-light rounded">
                            <a href="javascript:void(0)" onclick="openPostModal(${comment.post_id})" class="text-decoration-none">
                                <div class="small">${comment.comment_details}</div>
                                <small class="text-muted">${comment.created_at}</small>
                            </a>
                        </div>
                    `;
                });
                recentActivityHtml += '</div>';
            }
            
            modalBody.innerHTML = `
                <div class="text-center">
                    ${user.avatar 
                        ? `<img src="${user.avatar}" alt="${user.name}" class="user-modal-avatar">`
                        : `<div class="user-modal-placeholder">${user.name.charAt(0).toUpperCase()}</div>`
                    }
                    <h5 class="mb-1">${user.name}</h5>
                    <p class="text-muted small mb-2">${user.email}</p>
                    <p class="small text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i> ${user.location || 'Not specified'}</p>
                    <p class="small text-muted mb-3"><i class="fas fa-calendar-alt me-1"></i> Joined ${user.joined}</p>
                    <p class="mb-3 px-3">${user.bio || 'No bio provided'}</p>
                    
                    <div class="row g-2 mb-3 px-3">
                        <div class="col-6">
                            <div class="user-stat-card">
                                <div class="user-stat-value">${user.posts_count || 0}</div>
                                <div class="user-stat-label">Posts</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="user-stat-card">
                                <div class="user-stat-value">${user.comments_count || 0}</div>
                                <div class="user-stat-label">Comments</div>
                            </div>
                        </div>
                    </div>
                    
                    ${recentActivityHtml}
                </div>
            `;
        };

        // ==================== UTILITY FUNCTIONS ====================
        window.formatFileSize = function(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        };

        window.openImageModal = function(src) {
            const modalImage = document.getElementById('modalImage');
            if (modalImage) {
                modalImage.src = src;
                const imageModalEl = document.getElementById('imageModal');
                const imageModal = new bootstrap.Modal(imageModalEl);
                imageModal.show();
                setTimeout(() => {
                    imageModalEl.style.zIndex = '1060';
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length > 0) {
                        backdrops[backdrops.length - 1].style.zIndex = '1059';
                    }
                }, 100);
            }
        };

        window.openVideoModal = function(type, source, isReel = false) {
            const modalContent = document.getElementById('videoModalContent');
            if (!modalContent) return;
            
            if (type === 'youtube') {
                if (isReel) {
                    modalContent.innerHTML = `
                        <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
                            <div style="width: 400px; max-width: 100%;">
                                <div style="position: relative; width: 100%; padding-bottom: 177.78%;">
                                    <iframe 
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; border-radius: 12px;"
                                        src="https://www.youtube.com/embed/${source}?autoplay=1&rel=0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    modalContent.innerHTML = `
                        <div style="position: relative; width: 100%; padding-bottom: 56.25%;">
                            <iframe 
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; border-radius: 8px;"
                                src="https://www.youtube.com/embed/${source}?autoplay=1&rel=0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    `;
                }
            } else if (type === 'file') {
                if (isReel) {
                    modalContent.innerHTML = `
                        <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
                            <div style="width: 400px; max-width: 100%;">
                                <div style="position: relative; width: 100%; padding-bottom: 177.78%; background: #000; border-radius: 12px; overflow: hidden;">
                                    <video controls autoplay style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                        <source src="${source}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    modalContent.innerHTML = `
                        <div style="position: relative; width: 100%; padding-bottom: 56.25%; background: #000; border-radius: 8px; overflow: hidden;">
                            <video controls autoplay style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                <source src="${source}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    `;
                }
            }
            
            const videoModalEl = document.getElementById('videoModal');
            const videoModal = new bootstrap.Modal(videoModalEl);
            videoModal.show();
            
            setTimeout(() => {
                videoModalEl.style.zIndex = '1060';
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 0) {
                    backdrops[backdrops.length - 1].style.zIndex = '1059';
                }
            }, 100);
        };

        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const id = 'toast-' + Date.now();
            const bgColor = type === 'success' ? '#28a745' : type === 'warning' ? '#ffc107' : '#dc3545';
            const icon = type === 'success' ? 'fa-check-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle';
            
            const html = `
                <div id="${id}" class="toast align-items-center text-white border-0" role="alert" style="background: ${bgColor};">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas ${icon} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
            
            const toast = new bootstrap.Toast(document.getElementById(id), { delay: 3000 });
            toast.show();
            
            document.getElementById(id).addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        };
    </script>

    <!-- ==================== MODAL EVENT HANDLERS ==================== -->
    <script>
        // Set up the confirm delete button handler
        document.addEventListener('DOMContentLoaded', function() {
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    if (!window.deleteId || !window.deleteType) return;

                    const basePath = window.getCommunityBasePath();
                    const url = window.deleteType === 'post'
                        ? `${basePath}/posts/${window.deleteId}`
                        : `${basePath}/comments/${window.deleteId}`;

                    const deleteBtn = this;
                    const originalText = deleteBtn.innerHTML;
                    deleteBtn.disabled = true;
                    deleteBtn.innerHTML = '<span class="spinner-small me-2"></span> Deleting...';

                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const selector = window.deleteType === 'post' ? `#post-${window.deleteId}` : `#comment-${window.deleteId}`;
                            const element = document.querySelector(selector);
                            
                            if (element) {
                                element.style.opacity = '0';
                                element.style.transition = 'opacity 0.3s ease-out';
                                
                                setTimeout(() => {
                                    element.remove();
                                    
                                    if (window.deleteType === 'comment') {
                                        document.querySelectorAll(`#post-${window.deleteId} .comment-count, .modal-post-container .comment-count`).forEach(el => {
                                            el.textContent = Math.max(0, parseInt(el.textContent) - 1);
                                        });
                                    }
                                }, 300);
                            }
                            
                            const modalElement = document.querySelector(`.modal-post-container ${selector}`);
                            if (modalElement) {
                                modalElement.remove();
                            }
                            
                            const deleteModalEl = document.getElementById('deleteModal');
                            const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
                            if (deleteModal) {
                                deleteModal.hide();
                            }
                            
                            if (typeof window.showToast === 'function') {
                                window.showToast(`${window.deleteType} deleted!`, 'success');
                            }
                        } else {
                            if (typeof window.showToast === 'function') {
                                window.showToast(data.message || 'Error deleting', 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof window.showToast === 'function') {
                            window.showToast('Error: ' + error.message, 'error');
                        }
                    })
                    .finally(() => {
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = originalText;
                    });
                });
            }
        });

        // Set up the save edit button handler
        document.addEventListener('DOMContentLoaded', function() {
            const saveEditBtn = document.getElementById('saveEditBtn');
            if (saveEditBtn) {
                saveEditBtn.addEventListener('click', function() {
                    const content = document.getElementById('editContent').value.trim();
                    if (!content || !window.currentEditId) {
                        if (typeof window.showToast === 'function') {
                            window.showToast('Please enter some content', 'warning');
                        }
                        return;
                    }

                    const basePath = window.getCommunityBasePath();
                    const url = window.currentEditType === 'post'
                        ? `${basePath}/posts/${window.currentEditId}`
                        : `${basePath}/comments/${window.currentEditId}`;

                    const saveBtn = this;
                    const originalText = saveBtn.innerHTML;
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-small me-2"></span> Saving...';

                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ 
                            description: content,
                            comment_details: content
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (window.currentEditType === 'post') {
                                const postElement = document.getElementById(`post-content-${window.currentEditId}`);
                                if (postElement) {
                                    postElement.textContent = content;
                                }
                                
                                const modalPostElement = document.querySelector(`.modal-post-container #post-content-${window.currentEditId}`);
                                if (modalPostElement) {
                                    modalPostElement.textContent = content;
                                }
                                
                                const modalContainer = document.querySelector(`.modal-post-container[data-post-id="${window.currentEditId}"]`);
                                if (modalContainer) {
                                    const textElement = modalContainer.querySelector('p[style*="white-space: pre-wrap"]');
                                    if (textElement) {
                                        textElement.innerHTML = content.replace(/\n/g, '<br>');
                                    }
                                }
                            } else {
                                const commentElement = document.getElementById(`comment-content-${window.currentEditId}`);
                                if (commentElement) {
                                    commentElement.textContent = content;
                                    commentElement.style.opacity = '0.5';
                                    setTimeout(() => commentElement.style.opacity = '1', 300);
                                }
                                
                                const modalCommentElement = document.querySelector(`.modal-post-container #comment-content-${window.currentEditId}`);
                                if (modalCommentElement) {
                                    modalCommentElement.textContent = content;
                                }
                            }
                            
                            const editModalEl = document.getElementById('editModal');
                            const editModal = bootstrap.Modal.getInstance(editModalEl);
                            if (editModal) {
                                editModal.hide();
                            }
                            
                            if (typeof window.showToast === 'function') {
                                window.showToast(`${window.currentEditType} updated!`, 'success');
                            }
                        } else {
                            if (typeof window.showToast === 'function') {
                                window.showToast(data.message || 'Error updating', 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof window.showToast === 'function') {
                            window.showToast('Error: ' + error.message, 'error');
                        }
                    })
                    .finally(() => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;
                    });
                });
            }
        });
    </script>

    <!-- ==================== NOTIFICATION & CHATBOT SCRIPTS ==================== -->
    <script>
        // Toggle maintenance (admin)
        async function toggleMaintenance() {
            const btn = document.getElementById('maintenanceToggleBtn');
            const text = document.getElementById('maintenanceToggleText');
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!btn) return;
            btn.disabled = true;
            try {
                const res = await fetch('{{ route('admin.maintenance.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                });
                if (res.ok) {
                    const data = await res.json();
                    const on = !!data.maintenance;
                    text.textContent = on ? 'ON' : 'OFF';
                    btn.classList.toggle('btn-danger', on);
                    btn.classList.toggle('btn-outline-secondary', !on);
                }
            } catch (e) {
                console.error(e);
            } finally {
                btn.disabled = false;
            }
        }

        // User dropdown toggle
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');

            const notifDropdown = document.getElementById('notificationDropdown');
            if (notifDropdown && notifDropdown.classList.contains('show')) {
                notifDropdown.classList.remove('show');
            }
        }

        @if (! $isAdminNav)
        // Notification dropdown toggle
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            if (!dropdown) return;
            dropdown.classList.toggle('show');

            const userDropdown = document.getElementById('userDropdown');
            if (userDropdown && userDropdown.classList.contains('show')) {
                userDropdown.classList.remove('show');
            }

            if (dropdown.classList.contains('show')) {
                loadNotifications();
            }
        }

        // Chatbot toggle
        function toggleChatbot() {
            const modal = document.getElementById('chatbotModal');
            if (!modal) {
                window.location.href = '{{ route('login', [], false) }}?redirect=' + encodeURIComponent(window.location.href);
                return;
            }
            modal.classList.toggle('show');
        }

        // Quick notification toggles
        function toggleEmailQuick() {
            fetch('{{ route('profile.notifications.toggle-email') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => {
                console.log('Email notifications toggled');
            });
        }

        // ========== NOTIFICATION FUNCTIONS ==========
        
        function loadNotifications() {
            const list = document.getElementById('notificationList');
            if (!list) return;
            
            list.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Loading notifications...</p>
                </div>
            `;
            
            // Fetch both community notifications and medicine reminders
            Promise.all([
                fetch('/notifications?limit=10', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(res => res.json()).catch(() => ({ notifications: [], unread_count: 0 })),
                
                fetch('/medicine/reminders/notifications?limit=10', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(res => res.json()).catch(() => ({ notifications: [], unread_count: 0 }))
            ])
            .then(([communityData, medicineData]) => {
                const communityNotifications = (communityData.notifications || []).map(n => ({
                    ...n,
                    type: 'community',
                    from_user: n.from_user,
                    data: n.data,
                    read_at: n.read_at,
                    created_at: n.created_at,
                    message: n.message,
                    id: n.id,
                    notification_id: n.id
                }));
                
                const medicineNotifications = (medicineData.notifications || []).map(n => ({
                    ...n,
                    type: 'medicine',
                    reminder_id: n.reminder_id,
                    medicine_name: n.medicine_name,
                    scheduled_time: n.scheduled_time,
                    message: n.message,
                    read_at: n.read_at,
                    created_at: n.created_at,
                    notification_id: n.id,
                    action_url: n.action_url,
                    taken_url: n.taken_url
                }));
                
                const allNotifications = [...communityNotifications, ...medicineNotifications]
                    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                
                const totalUnread = (communityData.unread_count || 0) + (medicineData.unread_count || 0);
                
                updateNotificationDropdown(allNotifications);
                updateNotificationCount(totalUnread);
            })
            .catch(err => {
                console.error('Failed to load notifications:', err);
                list.innerHTML = `
                    <div class="text-center p-4">
                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                        <p class="mt-2">Failed to load notifications</p>
                        <button onclick="loadNotifications()" class="btn btn-sm btn-primary mt-2">Retry</button>
                    </div>
                `;
            });
        }

        function updateNotificationDropdown(notifications) {
            const list = document.getElementById('notificationList');
            
            if (!notifications || notifications.length === 0) {
                list.innerHTML = `
                    <div class="notification-empty">
                        <i class="fas fa-bell-slash"></i>
                        <p>No notifications yet</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            notifications.forEach(notif => {
                const time = new Date(notif.created_at).toLocaleString('en-US', { 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit', 
                    minute: '2-digit'
                });
                
                if (notif.type === 'medicine' || notif.type === 'medicine_reminder') {
                    const reminderId = notif.reminder_id;
                    const medicineName = notif.medicine_name;
                    const scheduledTime = notif.scheduled_time;
                    
                    html += `
                        <div class="notification-item ${notif.read_at ? '' : 'unread'}" 
                             onclick="handleMedicineReminderClick(${reminderId || 'null'}, event, ${notif.notification_id})">
                            <div class="notification-avatar">
                                <div class="notification-avatar-placeholder" style="background: linear-gradient(135deg, #28a745, #20c997);">
                                    <i class="fas fa-pills"></i>
                                </div>
                            </div>
                            <div class="notification-content">
                                <div class="notification-message">
                                    <strong>💊 Medicine Reminder</strong><br>
                                    Time to take: <strong>${escapeHtml(medicineName || 'Medicine')}</strong>
                                    ${scheduledTime ? ` at ${escapeHtml(scheduledTime)}` : ''}
                                </div>
                                <div class="notification-time">${time}</div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-success rounded-pill" onclick="event.stopPropagation(); markReminderTakenFromNotification(${reminderId || 'null'}, ${notif.notification_id})">
                                        <i class="fas fa-check me-1"></i> Taken
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    const avatar = notif.from_user?.picture 
                        ? `<img src="{{ asset('storage/') }}/${notif.from_user.picture}" alt="${notif.from_user.name || 'User'}">`
                        : `<div class="notification-avatar-placeholder">${(notif.from_user?.name || 'U').charAt(0).toUpperCase()}</div>`;
                    
                    const postId = notif.data?.post_id || notif.post_id;
                    
                    let iconHtml = '';
                    if (notif.type === 'post_approved') {
                        iconHtml = '<div class="notification-avatar-placeholder" style="background: linear-gradient(135deg, #28a745, #20c997);"><i class="fas fa-check-circle"></i></div>';
                    } else if (notif.type === 'post_rejected') {
                        iconHtml = '<div class="notification-avatar-placeholder" style="background: linear-gradient(135deg, #dc3545, #c82333);"><i class="fas fa-times-circle"></i></div>';
                    } else if (notif.type === 'post_deleted') {
                        iconHtml = '<div class="notification-avatar-placeholder" style="background: linear-gradient(135deg, #dc3545, #c82333);"><i class="fas fa-trash"></i></div>';
                    } else if (notif.type === 'post_reported') {
                        iconHtml = '<div class="notification-avatar-placeholder" style="background: linear-gradient(135deg, #ffc107, #ff9800);"><i class="fas fa-flag"></i></div>';
                    } else {
                        iconHtml = avatar;
                    }
                    
                    // Only add click handler if there's a valid postId
                    const clickHandler = postId ? `onclick="handleNotificationClick(event, ${notif.id}, '${postId}')"` : '';
                    
                    html += `
                        <div class="notification-item ${notif.read_at ? '' : 'unread'}" 
                             ${clickHandler}>
                            <div class="notification-avatar">
                                ${iconHtml}
                            </div>
                            <div class="notification-content">
                                <div class="notification-message">${escapeHtml(notif.message)}</div><br>
                                ${notif.post_preview ? `<div class="notification-preview mt-1"><strong class="d-block">${escapeHtml(notif.post_preview)}</strong></div>` : ''}
                                <div class="notification-time">${time}</div>
                            </div>
                        </div>
                    `;
                }
            });
            
            list.innerHTML = html;
        }

        function handleMedicineReminderClick(reminderId, event, notificationId) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // If reminderId is null or invalid, show an error
            if (!reminderId || reminderId === 'null') {
                console.error('Invalid reminder ID:', reminderId);
                showToast('Unable to load medicine reminder', 'error');
                return;
            }
            
            if (notificationId) {
                fetch(`/medicine/reminders/notification/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                }).catch(err => console.error('Error marking as read:', err));
            }
            
            openMedicineReminderModal(reminderId);
        }

        function markReminderTakenFromNotification(reminderId, notificationId) {
            if (!reminderId || reminderId === 'null') {
                console.error('Invalid reminder ID:', reminderId);
                showToast('Unable to mark as taken', 'error');
                return;
            }
            
            if (!confirm('Mark this medicine as taken?')) return;
            
            fetch(`/medicine/reminders/${reminderId}/taken-from-notification`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Medicine marked as taken!', 'success');
                    
                    if (notificationId) {
                        fetch(`/medicine/reminders/notification/${notificationId}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        }).catch(err => console.error('Error:', err));
                    }
                    
                    setTimeout(() => loadNotifications(), 500);
                } else {
                    showToast(data.message || 'Failed to mark as taken', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error marking as taken', 'error');
            });
        }

        function handleNotificationClick(event, notificationId, postId) {
            event.preventDefault();
            
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }

            // If no postId, just mark as read and return
            if (!postId || postId === 'null' || postId === 'undefined') {
                if (notificationId) {
                    fetch(`/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).catch(err => console.error('Error marking as read:', err));
                }
                return;
            }

            if (!notificationId) {
                return openPostModal(postId, notificationId);
            }

            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to mark notification as read');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    return fetch('/notifications/unread-count', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                }
                throw new Error(data.message || 'Failed to mark notification as read');
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to refresh unread count');
                return res.json();
            })
            .then(countData => {
                updateNotificationCount(countData.count || 0);
                openPostModal(postId, notificationId);
            })
            .catch(err => {
                console.error('Error handling notification click:', err);
                openPostModal(postId, notificationId);
            });
        }

        function markAllNotificationsRead() {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('unread');
                    });
                    updateNotificationCount(0);
                    loadNotifications();
                }
            })
            .catch(err => console.error('Error marking all as read:', err));
        }

        function updateNotificationCount(count) {
            const badge = document.getElementById('notificationCount');
            const bell = document.getElementById('notificationBell');
            const badge2 = document.getElementById('notificationCountBadge');
            if (!badge) return;

            const allowBadge = @json((bool) ($showNotificationBadge ?? true));
            if (!allowBadge) {
                badge.style.display = 'none';
                if (badge2) {
                    badge2.style.display = 'none';
                }
                return;
            }

            const previousCount = parseInt(badge.textContent) || 0;
            
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
                
                if (bell && count > previousCount) {
                    bell.classList.add('shake');
                    setTimeout(() => bell.classList.remove('shake'), 500);
                }
            } else {
                badge.style.display = 'none';
            }
            
            if (badge2) {
                if (count > 0) {
                    badge2.textContent = count;
                    badge2.style.display = 'inline-block';
                } else {
                    badge2.style.display = 'none';
                }
            }
        }

        function updateMailboxCount(count) {
            const badge = document.getElementById('mailboxCount');
            if (!badge) return;

            const allowBadge = @json((bool) ($showMailBadge ?? true));
            if (!allowBadge) {
                badge.style.display = 'none';
                return;
            }

            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function renderChatbotMarkup(text) {
            const escaped = escapeHtml(text);
            const lines = escaped.split(/\r?\n/);
            let html = '';
            let inList = false;

            for (const rawLine of lines) {
                const line = rawLine.trim();

                if (line === '') {
                    if (inList) {
                        html += '</ul>';
                        inList = false;
                    }
                    continue;
                }

                if (/^#{2,4}\s+/.test(line)) {
                    if (inList) {
                        html += '</ul>';
                        inList = false;
                    }
                    html += `<h3>${emphasizeLine(line.replace(/^#{2,4}\s+/, ''))}</h3>`;
                    continue;
                }

                if (line.startsWith('- ') || line.startsWith('* ') || /^\d+\.\s+/.test(line)) {
                    if (!inList) {
                        html += '<ul>';
                        inList = true;
                    }
                    const cleaned = line.replace(/^(-|\*|\d+\.)\s+/, '');
                    html += `<li>${emphasizeLine(cleaned)}</li>`;
                    continue;
                }

                if (inList) {
                    html += '</ul>';
                    inList = false;
                }

                html += `<p>${emphasizeLine(line)}</p>`;
            }

            if (inList) {
                html += '</ul>';
            }

            return html || '<p class="mb-0">No content.</p>';
        }

        function emphasizeLine(input) {
            let line = input.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            line = line.replace(/^\s*([^:<>]{2,120})\s*:\s*(.*)$/i, (m, label, rest) => {
                return `<strong>${label.trim()}:</strong> ${rest}`;
            });

            const keywordPattern = /\b(summary|details|suggestions|tips|overview|condition|health|symptom|symptoms|disease|diseases|medicine|medicines|metric|metrics|adherence|warning|urgent|improve|monitor|doctor|exercise|sleep|hydration|stress|chronic|active|managed|severity|diagnosed|risk|risks|trend|blood\s+pressure|glucose|heart\s+rate|bmi|eczema|conjunctivitis|tachycardia)\b/gi;
            const valuePattern = /\b(\d+\/?\d*\s*(?:mg\/dL|mmhg|bpm|%)?)\b/gi;

            const firstColon = line.indexOf(':');
            if (firstColon !== -1) {
                const head = line.slice(0, firstColon + 1);
                let tail = line.slice(firstColon + 1);
                tail = tail.replace(keywordPattern, '<strong>$1</strong>');
                tail = tail.replace(valuePattern, '<strong>$1</strong>');
                line = head + tail;
            } else {
                line = line.replace(keywordPattern, '<strong>$1</strong>');
            }

            if (!/<strong>/.test(line)) {
                line = line.replace(/^((?:\w+\s+){1,3}\w+)/, '<strong>$1</strong>');
            }

            return line;
        }

        let notificationInterval;

        function startNotificationUpdates() {
            if (notificationInterval) {
                clearInterval(notificationInterval);
            }
            
            notificationInterval = setInterval(() => {
                const dropdown = document.getElementById('notificationDropdown');
                if (!dropdown.classList.contains('show')) {
                    fetch('/notifications/unread-count', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => updateNotificationCount(data.count))
                    .catch(err => console.error('Error updating count:', err));

                    fetch('/profile/mailbox/unread-count', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => updateMailboxCount(data.count || 0))
                    .catch(err => console.error('Error updating mailbox count:', err));
                }
            }, 30000);
        }
        @endif

        window.onclick = function(event) {
            if (!event.target.matches('.user-circle') && !event.target.matches('.user-circle *') &&
                !event.target.matches('.notification-bell') && !event.target.matches('.notification-bell *')) {
                const dropdowns = document.getElementsByClassName('user-dropdown');
                const notifDropdowns = document.getElementsByClassName('notification-dropdown');
                
                for (let i = 0; i < dropdowns.length; i++) {
                    if (dropdowns[i].classList.contains('show')) {
                        dropdowns[i].classList.remove('show');
                    }
                }
                
                for (let i = 0; i < notifDropdowns.length; i++) {
                    if (notifDropdowns[i].classList.contains('show')) {
                        notifDropdowns[i].classList.remove('show');
                    }
                }
            }
        };

        let isTyping = false;
        let conversationHistory = [];
        let chatbotPromptTimeout;
        const allowChatbotBubble = @json((bool) ($showChatbotBubble ?? true));

        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "")  + expires + "; path=/";
        }

        function getCookie(name) {
            const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
            return v ? v.pop() : null;
        }

        function updateChatbotVisibility(enabled) {
            const chatbotIcon = document.getElementById('chatbotIcon');
            if (!chatbotIcon) return;
            if (enabled) {
                chatbotIcon.style.display = '';
                startChatbotPromptCycle();
            } else {
                chatbotIcon.style.display = 'none';
                chatbotIcon.classList.remove('glow-pulse', 'show-tooltip');
                if (chatbotPromptTimeout) {
                    clearTimeout(chatbotPromptTimeout);
                }
            }
        }

        function initializeChatbotPromptSetting() {
            if (!allowChatbotBubble) {
                updateChatbotVisibility(false);
                return;
            }

            const saved = getCookie('chatbot_bubble_enabled');
            const enabled = saved === null ? '1' : saved;

            updateChatbotVisibility(enabled === '1');
        }

        function startChatbotPromptCycle() {
            const chatbotIcon = document.getElementById('chatbotIcon');
            if (!chatbotIcon) return;

            if (!allowChatbotBubble) {
                chatbotIcon.classList.remove('glow-pulse', 'show-tooltip');
                return;
            }

            if (chatbotPromptTimeout) {
                clearTimeout(chatbotPromptTimeout);
            }

            const saved = getCookie('chatbot_bubble_enabled');
            const enabled = saved === null ? '1' : saved;
            if (enabled !== '1') {
                chatbotIcon.classList.remove('glow-pulse', 'show-tooltip');
                return;
            }

            const showPrompt = () => {
                chatbotIcon.classList.add('glow-pulse', 'show-tooltip');

                if (chatbotPromptTimeout) {
                    clearTimeout(chatbotPromptTimeout);
                }

                chatbotPromptTimeout = setTimeout(() => {
                    chatbotIcon.classList.remove('glow-pulse', 'show-tooltip');
                }, 10000);
            };

            showPrompt();
        }

        async function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            if (!message || isTyping) return;

            addMessage(message, 'user');
            conversationHistory.push({ role: 'user', content: message });
            conversationHistory = conversationHistory.slice(-12);
            input.value = '';

            showTypingIndicator();

            try {
                const response = await fetch('{{ route('chatbot.message') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        message,
                        history: conversationHistory.slice(0, -1)
                    })
                });

                const data = await response.json();
                removeTypingIndicator();

                let reply = null;
                if (response.ok && typeof data.reply === 'string' && data.reply.trim() !== '') {
                    reply = data.reply;
                } else {
                    if (typeof data.reply === 'string' && data.reply.trim() !== '') {
                        reply = data.reply;
                    } else if (typeof data.message === 'string' && data.message.trim() !== '') {
                        reply = data.message;
                    } else if (data.errors && typeof data.errors === 'object') {
                        const first = Object.values(data.errors)[0];
                        if (Array.isArray(first) && first.length) {
                            reply = first[0];
                        } else if (typeof first === 'string') {
                            reply = first;
                        }
                    }
                }

                if (!reply) {
                    reply = 'I could not generate a reply right now. Please try again.';
                }

                addMessage(reply, 'bot');
                conversationHistory.push({ role: 'assistant', content: reply });
                conversationHistory = conversationHistory.slice(-12);
            } catch (error) {
                removeTypingIndicator();
                addMessage('I am having trouble connecting right now. Please try again in a moment.', 'bot');
            }
        }

        function addMessage(text, sender) {
            const messages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');

            if (sender === 'user') {
                messageDiv.style.textAlign = 'right';
                messageDiv.style.margin = '10px 0';
                messageDiv.innerHTML = `
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 18px; border-radius: 18px 18px 0 18px; display: inline-block; max-width: 80%; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        ${escapeHtml(text)}
                    </div>
                `;
            } else {
                messageDiv.style.textAlign = 'left';
                messageDiv.style.margin = '10px 0';

                let formattedText = renderChatbotMarkup(text);

                messageDiv.innerHTML = `
                    <div class="bot-message-row">
                        <span class="bot-avatar"><i class="fas fa-user-md"></i></span>
                        <div class="bot-message-bubble">
                            ${formattedText}
                        </div>
                    </div>
                `;
            }

            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        function showTypingIndicator() {
            isTyping = true;
            const messages = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.style.textAlign = 'left';
            typingDiv.style.margin = '10px 0';
            typingDiv.innerHTML = `
                <div class="typing-bubble">
                    <span>MyDoctor AI is typing</span>
                    <span class="typing-dots" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </span>
                </div>
            `;
            messages.appendChild(typingDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        function removeTypingIndicator() {
            isTyping = false;
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        document.getElementById('chatInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        document.getElementById('chatInput')?.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        window.toggleEmailQuick = toggleEmailQuick;
        @if (! $isAdminNav)
            window.toggleNotificationDropdown = toggleNotificationDropdown;
            window.markAllNotificationsRead = markAllNotificationsRead;
            window.handleNotificationClick = handleNotificationClick;
            window.handleMedicineReminderClick = handleMedicineReminderClick;
            window.markReminderTakenFromNotification = markReminderTakenFromNotification;
        @endif
        window.toggleChatbot = toggleChatbot;
        window.sendMessage = sendMessage;
        window.addMessage = addMessage;

        document.addEventListener('DOMContentLoaded', function() {
            const appLocale = document.documentElement.lang?.startsWith('bn') ? 'bn' : 'en';
            const autoTranslations = @json(app()->getLocale() === 'bn' ? __('ui.auto') : []);

            const splitMixedLabel = (value) => {
                if (!value || typeof value !== 'string') return null;
                const match = value.match(/^(.*?)\s*\(([\u0980-\u09FF][^)]*)\)\s*$/u);
                if (!match) return null;
                return {
                    en: match[1].trim(),
                    bn: match[2].trim(),
                };
            };

            const applyLocaleToMixedContent = (root = document.body) => {
                const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
                    acceptNode(node) {
                        if (!node || !node.nodeValue || !node.nodeValue.trim()) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        const parentTag = node.parentElement?.tagName;
                        if (parentTag && ['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEXTAREA'].includes(parentTag)) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        return NodeFilter.FILTER_ACCEPT;
                    }
                });

                const textNodes = [];
                while (walker.nextNode()) {
                    textNodes.push(walker.currentNode);
                }

                textNodes.forEach((node) => {
                    const original = node.nodeValue;
                    const parsed = splitMixedLabel(original.trim());
                    if (!parsed) return;

                    const localized = appLocale === 'bn' ? parsed.bn : parsed.en;
                    const leading = original.match(/^\s*/)?.[0] ?? '';
                    const trailing = original.match(/\s*$/)?.[0] ?? '';
                    node.nodeValue = `${leading}${localized}${trailing}`;
                });

                root.querySelectorAll('input[placeholder], textarea[placeholder], [title]').forEach((element) => {
                    if (element.hasAttribute('placeholder')) {
                        const placeholder = element.getAttribute('placeholder') || '';
                        const parsed = splitMixedLabel(placeholder);
                        if (parsed) {
                            element.setAttribute('placeholder', appLocale === 'bn' ? parsed.bn : parsed.en);
                        }
                    }

                    if (element.hasAttribute('title')) {
                        const title = element.getAttribute('title') || '';
                        const parsed = splitMixedLabel(title);
                        if (parsed) {
                            element.setAttribute('title', appLocale === 'bn' ? parsed.bn : parsed.en);
                        }
                    }
                });
            };

            const applyExactTextTranslations = (root = document.body) => {
                if (appLocale !== 'bn' || !autoTranslations || typeof autoTranslations !== 'object') {
                    return;
                }

                const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
                    acceptNode(node) {
                        if (!node || !node.nodeValue || !node.nodeValue.trim()) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        const parentEl = node.parentElement;
                        const parentTag = parentEl?.tagName;
                        if (parentTag && ['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEXTAREA'].includes(parentTag)) {
                            return NodeFilter.FILTER_REJECT;
                        }

                        if (parentEl && (parentEl.classList.contains('nav-user-name') || parentEl.closest('.dropdown-header'))) {
                            return NodeFilter.FILTER_REJECT;
                        }

                        return NodeFilter.FILTER_ACCEPT;
                    }
                });

                const textNodes = [];
                while (walker.nextNode()) {
                    textNodes.push(walker.currentNode);
                }

                textNodes.forEach((node) => {
                    const original = node.nodeValue;
                    const trimmed = original.trim();
                    if (!trimmed) return;

                    const translated = autoTranslations[trimmed];
                    if (!translated) return;

                    const leading = original.match(/^\s*/)?.[0] ?? '';
                    const trailing = original.match(/\s*$/)?.[0] ?? '';
                    node.nodeValue = `${leading}${translated}${trailing}`;
                });

                root.querySelectorAll('input[placeholder], textarea[placeholder], [title]').forEach((element) => {
                    if (element.hasAttribute('placeholder')) {
                        const placeholder = (element.getAttribute('placeholder') || '').trim();
                        if (autoTranslations[placeholder]) {
                            element.setAttribute('placeholder', autoTranslations[placeholder]);
                        }
                    }

                    if (element.hasAttribute('title')) {
                        const title = (element.getAttribute('title') || '').trim();
                        if (autoTranslations[title]) {
                            element.setAttribute('title', autoTranslations[title]);
                        }
                    }
                });

                const currentTitle = document.title || '';
                if (currentTitle) {
                    if (autoTranslations[currentTitle]) {
                        document.title = autoTranslations[currentTitle];
                    } else if (currentTitle.includes(' - ')) {
                        const parts = currentTitle.split(' - ');
                        const translatedParts = parts.map((part) => autoTranslations[part.trim()] || part.trim());
                        document.title = translatedParts.join(' - ');
                    }
                }
            };

            applyLocaleToMixedContent();
            applyExactTextTranslations();

            initializeChatbotPromptSetting();
            startChatbotPromptCycle();

            if ('{{ Auth::check() }}' === '1') {
                @if (! $isAdminNav)
                    Promise.all([
                        fetch('/notifications/unread-count', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }).then(res => res.json()),
                        fetch('/profile/mailbox/unread-count', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }).then(res => res.json()),
                    ])
                    .then(([notificationData, mailboxData]) => {
                        updateNotificationCount(notificationData.count || 0);
                        updateMailboxCount(mailboxData.count || 0);
                        startNotificationUpdates();
                    })
                    .catch(err => console.error('Error loading initial counts:', err));
                @else
                    fetch('/profile/mailbox/unread-count', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }).then(res => res.json())
                    .then(data => updateMailboxCount(data.count || 0))
                    .catch(err => console.error('Error updating mailbox count:', err));
                @endif
            }
        });
    </script>

    <!-- Global Edit Functions - Available on all pages -->
    <script>
        window.metricFieldDefs = window.metricFieldDefs || {};
        window.symptomsList = window.symptomsList || {};

        window.openEditMetric = function(id, type, values, recordedAt) {
            try {
                const metricLabel = document.getElementById('metricModalLabel');
                if (metricLabel) metricLabel.textContent = 'Edit Health Metric';
                const metricSubmit = document.getElementById('metricSubmitLabel');
                if (metricSubmit) metricSubmit.textContent = 'Update Metric';
                const form = document.getElementById('metricForm');
                if (form) {
                    form.action = '/health/metric/' + id;
                    form.method = 'POST';
                }
                const methodInput = document.getElementById('metricFormMethod');
                if (methodInput) methodInput.value = 'PUT';
                const metricTypeSelect = document.getElementById('metricTypeSelect');
                if (metricTypeSelect) metricTypeSelect.value = type;

                const cfg = (window.metricFieldDefs || {})[type];
                const valMap = {};
                if (cfg) {
                    cfg.forEach(f => {
                        const key = f.name.replace('value_', '');
                        if (values[key] !== undefined) valMap[f.name] = values[key];
                    });
                }

                const fieldsContainer = document.getElementById('metricFieldsContainer');
                if (fieldsContainer) {
                    fieldsContainer.innerHTML = '';
                    if (cfg) {
                        const row = document.createElement('div');
                        row.className = 'row g-3 mb-3';
                        cfg.forEach(f => {
                            const col = document.createElement('div');
                            col.className = cfg.length === 1 ? 'col-12' : 'col-6';
                            const val = valMap[f.name] !== undefined ? valMap[f.name] : '';
                            col.innerHTML = `
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">${f.label}</label>
                                <input type="number" name="${f.name}" class="form-control" style="border-radius: 10px;"
                                    placeholder="${f.placeholder}" min="${f.min}" max="${f.max}"
                                    step="${f.step || '1'}" value="${val}" required>
                            `;
                            row.appendChild(col);
                        });
                        fieldsContainer.appendChild(row);
                    }
                }

                const recordedAtInput = document.getElementById('metricRecordedAt');
                if (recordedAtInput) recordedAtInput.value = recordedAt;
                
                const modal = document.getElementById('addMetricModal');
                if (modal) {
                    try {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    } catch (e) {
                        console.error('Failed to open modal:', e);
                        modal.style.display = 'block';
                    }
                } else {
                    console.error('Metric modal not found');
                }
            } catch (error) {
                console.error('Error in openEditMetric:', error);
            }
        };

        window.openEditSymptom = function(id, name, severity, recordedAt, note) {
            try {
                const symptomLabel = document.getElementById('symptomModalLabel');
                if (symptomLabel) symptomLabel.textContent = 'Edit Symptom';
                const symptomSubmit = document.getElementById('symptomSubmitLabel');
                if (symptomSubmit) symptomSubmit.textContent = 'Update Symptom';
                const form = document.getElementById('symptomForm');
                if (form) {
                    form.action = '/health/symptom/' + id;
                    form.method = 'POST';
                }
                const methodInput = document.getElementById('symptomFormMethod');
                if (methodInput) methodInput.value = 'PUT';
                const bn = (window.symptomsList || {})[name] || '';
                const searchInput = document.getElementById('symptomSearchInput');
                if (searchInput) searchInput.value = name + (bn ? ' (' + bn + ')' : '');
                const nameHidden = document.getElementById('symptomNameHidden');
                if (nameHidden) nameHidden.value = name;
                const severityInput = document.getElementById('severityRange');
                if (severityInput) severityInput.value = severity;
                const severityValue = document.getElementById('severityValue');
                if (severityValue) severityValue.textContent = severity;
                const recordedAtInput = document.getElementById('symptomRecordedAt');
                if (recordedAtInput) recordedAtInput.value = recordedAt;
                const noteInput = document.getElementById('symptomNote');
                if (noteInput) noteInput.value = note || '';
                
                const modal = document.getElementById('addSymptomModal');
                if (modal) {
                    try {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    } catch (e) {
                        console.error('Failed to open modal:', e);
                        modal.style.display = 'block';
                    }
                } else {
                    console.error('Symptom modal not found');
                }
            } catch (error) {
                console.error('Error in openEditSymptom:', error);
            }
        };

        window.openEditDisease = function(id, diseaseId, diseaseName, status, diagnosedAt, notes) {
            try {
                const diseaseLabel = document.getElementById('diseaseModalLabel');
                if (diseaseLabel) diseaseLabel.textContent = 'Edit Disease Record';
                const diseaseSubmit = document.getElementById('diseaseSubmitLabel');
                if (diseaseSubmit) diseaseSubmit.textContent = 'Update Disease';
                const form = document.getElementById('diseaseForm');
                if (form) {
                    form.action = '/health/disease/' + id;
                    form.method = 'POST';
                }
                const methodInput = document.getElementById('diseaseFormMethod');
                if (methodInput) methodInput.value = 'PUT';
                const selectWrapper = document.getElementById('diseaseSelectWrapper');
                if (selectWrapper) selectWrapper.style.display = 'none';
                const idHidden = document.getElementById('diseaseIdHidden');
                if (idHidden) {
                    idHidden.removeAttribute('required');
                    idHidden.value = diseaseId;
                }
                const searchInput = document.getElementById('diseaseSearchInput');
                if (searchInput) searchInput.value = diseaseName;
                const statusInput = document.getElementById('diseaseStatus');
                if (statusInput) statusInput.value = status;
                const dateInput = document.getElementById('diseaseDiagnosedAt');
                if (dateInput) dateInput.value = diagnosedAt || '';
                const notesInput = document.getElementById('diseaseNotes');
                if (notesInput) notesInput.value = notes || '';
                
                const modal = document.getElementById('addDiseaseModal');
                if (modal) {
                    try {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    } catch (e) {
                        console.error('Failed to open modal:', e);
                        modal.style.display = 'block';
                    }
                } else {
                    console.error('Disease modal not found');
                }
            } catch (error) {
                console.error('Error in openEditDisease:', error);
            }
        };

        window.openEditUpload = function(id, title, type, doctorName, institution, docDate, notes, summary) {
            try {
                const uploadLabel = document.getElementById('uploadModalLabel');
                if (uploadLabel) uploadLabel.textContent = 'Edit Document';
                const uploadSubmit = document.getElementById('uploadSubmitLabel');
                if (uploadSubmit) uploadSubmit.textContent = 'Update';
                const form = document.getElementById('uploadForm');
                if (form) {
                    form.action = '/health/upload/' + id;
                    form.method = 'POST';
                }
                const methodInput = document.getElementById('uploadFormMethod');
                if (methodInput) methodInput.value = 'PUT';
                const fileInput = document.getElementById('uploadFileInput');
                if (fileInput) fileInput.removeAttribute('required');
                const fileHint = document.getElementById('uploadFileHint');
                if (fileHint) fileHint.textContent = 'Leave empty to keep existing image.';
                const titleInput = document.getElementById('uploadTitle');
                if (titleInput) titleInput.value = title;
                const typeInput = document.getElementById('uploadType');
                if (typeInput) typeInput.value = type;
                const doctorInput = document.getElementById('uploadDoctorName');
                if (doctorInput) doctorInput.value = doctorName || '';
                const institutionInput = document.getElementById('uploadInstitution');
                if (institutionInput) institutionInput.value = institution || '';
                const dateInput = document.getElementById('uploadDocumentDate');
                if (dateInput) dateInput.value = docDate || '';
                const notesInput = document.getElementById('uploadNotes');
                if (notesInput) notesInput.value = notes || '';
                const summaryInput = document.getElementById('uploadSummary');
                if (summaryInput) summaryInput.value = summary || '';
                
                const modal = document.getElementById('addUploadModal');
                if (modal) {
                    try {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    } catch (e) {
                        console.error('Failed to open modal:', e);
                        modal.style.display = 'block';
                    }
                } else {
                    console.error('Upload modal not found');
                }
            } catch (error) {
                console.error('Error in openEditUpload:', error);
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            ['addMetricModal','addSymptomModal','addDiseaseModal','addUploadModal'].forEach(modalId => {
                const el = document.getElementById(modalId);
                if (!el) return;
                el.addEventListener('hidden.bs.modal', function() {
                    if (modalId === 'addMetricModal') {
                        const metricLabel = document.getElementById('metricModalLabel');
                        if (metricLabel) metricLabel.textContent = 'Record Health Metric';
                        const metricSubmit = document.getElementById('metricSubmitLabel');
                        if (metricSubmit) metricSubmit.textContent = 'Save Metric';
                        const metricForm = document.getElementById('metricForm');
                        if (metricForm) {
                            metricForm.action = '{{ route("health.metric.store") }}';
                            document.getElementById('metricFormMethod').value = 'POST';
                        }
                    } else if (modalId === 'addSymptomModal') {
                        const symptomLabel = document.getElementById('symptomModalLabel');
                        if (symptomLabel) symptomLabel.textContent = 'Log Symptom';
                        const symptomSubmit = document.getElementById('symptomSubmitLabel');
                        if (symptomSubmit) symptomSubmit.textContent = 'Save Symptom';
                        const symptomForm = document.getElementById('symptomForm');
                        if (symptomForm) {
                            symptomForm.action = '{{ route("health.symptom.store") }}';
                            document.getElementById('symptomFormMethod').value = 'POST';
                        }
                        const selectWrapper = document.getElementById('symptomSelectWrapper');
                        if (selectWrapper) selectWrapper.style.display = 'block';
                        const symptomNameHidden = document.getElementById('symptomNameHidden');
                        if (symptomNameHidden) symptomNameHidden.value = '';
                        const symptomInput = document.getElementById('symptomSearchInput');
                        if (symptomInput) symptomInput.value = '';
                    } else if (modalId === 'addDiseaseModal') {
                        const diseaseLabel = document.getElementById('diseaseModalLabel');
                        if (diseaseLabel) diseaseLabel.textContent = 'Add Disease Record';
                        const diseaseSubmit = document.getElementById('diseaseSubmitLabel');
                        if (diseaseSubmit) diseaseSubmit.textContent = 'Save Record';
                        const diseaseForm = document.getElementById('diseaseForm');
                        if (diseaseForm) {
                            diseaseForm.action = '{{ route("health.disease.store") }}';
                            document.getElementById('diseaseFormMethod').value = 'POST';
                        }
                        const diseaseWrapper = document.getElementById('diseaseSelectWrapper');
                        if (diseaseWrapper) diseaseWrapper.style.display = 'block';
                        const diseaseIdField = document.getElementById('diseaseIdHidden');
                        if (diseaseIdField) diseaseIdField.setAttribute('required', 'required');
                    } else if (modalId === 'addUploadModal') {
                        const uploadLabel = document.getElementById('uploadModalLabel');
                        if (uploadLabel) uploadLabel.textContent = 'Upload Document';
                        const uploadSubmit = document.getElementById('uploadSubmitLabel');
                        if (uploadSubmit) uploadSubmit.textContent = 'Upload';
                        const uploadForm = document.getElementById('uploadForm');
                        if (uploadForm) {
                            uploadForm.action = '{{ route("health.upload.store") }}';
                            document.getElementById('uploadFormMethod').value = 'POST';
                        }
                        const uploadFileInput = document.getElementById('uploadFileInput');
                        if (uploadFileInput) uploadFileInput.setAttribute('required', 'required');
                        const uploadHint = document.getElementById('uploadFileHint');
                        if (uploadHint) uploadHint.textContent = 'Choose an image or PDF file.';
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>