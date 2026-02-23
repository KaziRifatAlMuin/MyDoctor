<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logos/applogo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logos/applogo.jpg') }}" type="image/x-icon">
    <title>@yield('title', 'My Doctor') - Healthcare Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background-color: #ffffff;
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
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .page-nav-bar .banner-nav {
            position: relative;
            top: auto;
            left: auto;
            right: auto;
            padding: 14px 40px;
        }

        /* Dark text links on white navbar */
        .page-nav-bar .banner-nav-link {
            color: #2d3748;
            text-shadow: none;
        }

        .page-nav-bar .banner-nav-link:hover {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .page-nav-bar .banner-nav-link.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        /* User circle on white bar */
        .page-nav-bar .user-circle {
            border-color: #667eea;
        }

        /* Navbar Styles - Positioned absolutely on banner */
        .banner-nav {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
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
            color: white;
            font-weight: 500;
            font-size: 1.1rem;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .banner-nav-link:hover {
            color: #ffd700;
            border-bottom: 2px solid #ffd700;
        }

        .banner-nav-link.active {
            color: #ffd700;
            border-bottom: 2px solid #ffd700;
        }

        /* User Circle Menu */
        .user-menu {
            position: relative;
        }

        .user-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid white;
            overflow: hidden;
            transition: transform 0.3s ease;
            background-color: #fff;
        }

        .user-circle:hover {
            transform: scale(1.1);
            border-color: #ffd700;
        }

        .user-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-circle i {
            font-size: 30px;
            color: #667eea;
            line-height: 50px;
            text-align: center;
            width: 100%;
        }

        /* Dropdown Menu */
        .user-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
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

        .dropdown-item-custom i {
            width: 20px;
            color: #667eea;
        }

        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 8px 0;
        }

        /* Notification Badge */
        .notification-badge {
            background-color: #f56565;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
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
        .main-content {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Chatbot Icon */
        .chatbot-icon {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
            border: 3px solid white;
        }

        .chatbot-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 35px rgba(102, 126, 234, 0.6);
        }

        .chatbot-icon i {
            color: white;
            font-size: 30px;
        }

        .chatbot-tooltip {
            position: absolute;
            right: 80px;
            background: white;
            color: #4a5568;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            white-space: nowrap;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .chatbot-icon:hover .chatbot-tooltip {
            opacity: 1;
        }

        /* Chatbot Modal */
        .chatbot-modal {
            position: fixed;
            bottom: 120px;
            right: 30px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: none;
            z-index: 999;
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
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
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
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
                width: 300px;
                right: 15px;
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
    background: rgba(0, 0, 0, 0.5); /* Black overlay with 50% opacity - only dims the image */
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
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
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
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Banner / Navbar -->
    <div class="{{ request()->routeIs('home') ? 'banner' : 'page-nav-bar' }}">
        <!-- Navigation on Banner -->
        <nav class="banner-nav">
            <!-- Logo -->
            <div class="banner-logo">
                <img src="{{ asset('images/logos/applogo.jpg') }}" alt="My Doctor Logo">
            </div>

            <!-- Navigation Menu -->
            <ul class="banner-nav-menu">
                <li class="banner-nav-item">
                    <a href="{{ route('home') }}" class="banner-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="banner-nav-item">
                    <a href="{{ route('medicine') }}" class="banner-nav-link {{ request()->routeIs('medicine*') ? 'active' : '' }}">
                        <i class="fas fa-pills me-1"></i> Medicine
                    </a>
                </li>
                <li class="banner-nav-item">
                    <a href="{{ route('health') }}" class="banner-nav-link {{ request()->routeIs('health*') ? 'active' : '' }}">
                        <i class="fas fa-heartbeat me-1"></i> Health
                    </a>
                </li>
                <li class="banner-nav-item">
                    <a href="{{ route('community') }}" class="banner-nav-link {{ request()->routeIs('community*') ? 'active' : '' }}">
                        <i class="fas fa-users me-1"></i> Community
                    </a>
                </li>
                <li class="banner-nav-item">
                    <a href="{{ route('help') }}" class="banner-nav-link {{ request()->routeIs('help*') ? 'active' : '' }}">
                        <i class="fas fa-question-circle me-1"></i> Help
                    </a>
                </li>
            </ul>

            <!-- User Circle Menu -->
<div class="user-menu" id="userMenu">
    <div class="user-circle" onclick="toggleUserDropdown()">
        @auth
            @if(auth()->user()->Picture)
                <img src="{{ asset('storage/' . auth()->user()->Picture) }}" alt="{{ auth()->user()->Name }}">
            @else
                <i class="fas fa-user-circle"></i>
            @endif
        @else
            <i class="fas fa-user-circle"></i>
        @endauth
    </div>
    
    <div class="user-dropdown" id="userDropdown">
        @auth
            <!-- LOGGED IN USER MENU -->
            <div class="dropdown-header">
                <h6>{{ auth()->user()->Name }}</h6>
                <p>{{ auth()->user()->Email }}</p>
            </div>
            
            <a href="{{ route('profile') }}" class="dropdown-item-custom">
                <i class="fas fa-user"></i> Profile
            </a>
            
            <a href="{{ route('notifications') }}" class="dropdown-item-custom">
                <i class="fas fa-bell"></i> Notifications
                <span class="notification-badge" style="background-color: #6c757d;">0</span>
            </a>
            
            <a href="{{ route('suggestions') }}" class="dropdown-item-custom">
                <i class="fas fa-lightbulb"></i> Suggestions
            </a>
            
            <!-- Register link hidden for authenticated users -->
            
            <!-- Check for admin by email -->
            @if(auth()->user()->email === 'admin@mydoctor.com')
                <div class="divider"></div>
                <a href="{{ route('admin.dashboard') }}" class="dropdown-item-custom">
                    <i class="fas fa-cog"></i> Admin Panel
                </a>
            @endif
            
            <div class="divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item-custom" style="width: 100%; border: none; background: none; cursor: pointer; color: #dc3545;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        @else
            <!-- GUEST USER MENU -->
            <a href="{{ route('login') }}" class="dropdown-item-custom">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            
            <a href="{{ route('register') }}" class="dropdown-item-custom">
                <i class="fas fa-user-plus"></i> Register
            </a>
        @endauth
    </div>
</div>
        </nav>

@if(request()->routeIs('home'))
<!-- Banner Hero Section (Home only) -->
<div class="banner position-relative overflow-hidden">
    <!-- Background Image with Overlay -->
    
    <!-- Banner Content with Container Layout -->
    <div class="container position-relative z-3 py-5">
        <div class="row min-vh-50 align-items-center">
            <!-- Left Column - Text and Buttons -->
            <div class="col-lg-7 text-white">
                <h1 class="display-3 fw-bold mt-5 pt-4 mb-4">Your Health,<br>Our <span class="text-warning">Priority</span></h1>
                <p class="lead mb-4">Experience healthcare reimagined with AI-powered insights, medicine reminders, and community support.</p>
                
                @guest
                    <div class="d-flex gap-3">
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                            Get Started <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <button onclick="toggleChatbot()" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                            <i class="fas fa-robot me-2"></i>Ask AI
                        </button>
                    </div>
                @else
                    <div class="d-flex gap-3">
                        <a href="{{ route('health.tracking') }}" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                            Dashboard <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <button onclick="toggleChatbot()" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                            <i class="fas fa-robot me-2"></i>Ask AI
                        </button>
                    </div>
                @endguest
            </div>
            
            <!-- Right Column - Statistics with Icons -->
            <div class="col-lg-5 mt-5 pt-4 text-white">
                <div class="row g-4">
                    <div class="col-6">
                        <div class="stat-card text-center p-3">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-users fa-3x text-warning"></i>
                            </div>
                            <h3 class="text-white mb-0">50K+</h3>
                            <small class="text-white-50">Active Users</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center p-3">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-file-medical fa-3x text-warning"></i>
                            </div>
                            <h3 class="text-white mb-0">10K+</h3>
                            <small class="text-white-50">Health Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center p-3">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-clock fa-3x text-warning"></i>
                            </div>
                            <h3 class="text-white mb-0">24/7</h3>
                            <small class="text-white-50">AI Support</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center p-3">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-user-md fa-3x text-warning"></i>
                            </div>
                            <h3 class="text-white mb-0">100+</h3>
                            <small class="text-white-50">Doctors</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Chatbot Icon -->
    <div class="chatbot-icon" onclick="toggleChatbot()">
        <i class="fas fa-comment-dots"></i>
        <span class="chatbot-tooltip">Ask me about health!</span>
    </div>

    <!-- Chatbot Modal -->
    <div class="chatbot-modal" id="chatbotModal">
        <div class="chatbot-header">
            <h5><i class="fas fa-robot me-2"></i>Health Assistant</h5>
            <button onclick="toggleChatbot()"><i class="fas fa-times"></i></button>
        </div>
        
        <!-- Disclaimer Banner -->
        <div class="bg-warning bg-opacity-10 p-2 text-center small" style="border-bottom: 1px solid #dee2e6;">
            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
            AI-powered health information - consult a doctor for medical advice
        </div>
        
        <div class="chatbot-messages" id="chatMessages">
            <div style="text-align: center; color: #718096; padding: 20px;">
                <i class="fas fa-robot fa-3x mb-3" style="color: #667eea;"></i>
                <p>Hello! I'm your AI health assistant.<br>How can I help you today?</p>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Ask about symptoms, medicines, diet, exercise, or general health tips
                </small>
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" placeholder="Type your health question..." id="chatInput">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // User dropdown toggle
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Chatbot toggle
        function toggleChatbot() {
            const modal = document.getElementById('chatbotModal');
            modal.classList.toggle('show');
        }

        // Chatbot variables
        let isTyping = false;
        let conversationHistory = [];

        // Send message to chatbot
        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            
            if (!message || isTyping) return;

            // Add user message
            addMessage(message, 'user');
            input.value = '';
            
            // Show typing indicator
            showTypingIndicator();
        }

        // Add message to chat
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
                
                // Format bot response with line breaks and bold text
                let formattedText = escapeHtml(text);
                formattedText = formattedText.replace(/\n/g, '<br>');
                formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                messageDiv.innerHTML = `
                    <div style="background: #f0f2f5; color: #1a1a1a; padding: 12px 18px; border-radius: 18px 18px 18px 0; display: inline-block; max-width: 80%; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        ${formattedText}
                    </div>
                `;
            }
            
            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        // Show typing indicator
        function showTypingIndicator() {
            isTyping = true;
            const messages = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.style.textAlign = 'left';
            typingDiv.style.margin = '10px 0';
            typingDiv.innerHTML = `
                <div style="background: #f0f2f5; color: #666; padding: 12px 18px; border-radius: 18px 18px 18px 0; display: inline-block;">
                    <i class="fas fa-circle-notch fa-spin me-2"></i>Thinking...
                </div>
            `;
            messages.appendChild(typingDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        // Remove typing indicator
        function removeTypingIndicator() {
            isTyping = false;
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // Escape HTML to prevent XSS
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.user-circle') && !event.target.matches('.user-circle *')) {
                const dropdowns = document.getElementsByClassName('user-dropdown');
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        // Enter key for chat
        document.getElementById('chatInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Auto-resize textarea (optional)
        document.getElementById('chatInput')?.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
    
    @stack('scripts')

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="footer-logo mb-3">
                        <img src="{{ asset('images/logos/applogo.jpg') }}" alt="My Doctor" height="40">
                        <span class="fw-bold text-white ms-2">My Doctor</span>
                    </div>
                    <p class="footer-text">
                        Your complete healthcare companion. Track health metrics, get medicine reminders, consult AI, and manage medical records all in one place.
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
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                        <li><a href="{{ route('medicine') }}"><i class="fas fa-chevron-right me-2"></i>Medicine</a></li>
                        <li><a href="{{ route('health') }}"><i class="fas fa-chevron-right me-2"></i>Health</a></li>
                        <li><a href="{{ route('community') }}"><i class="fas fa-chevron-right me-2"></i>Community</a></li>
                        <li><a href="{{ route('help') }}"><i class="fas fa-chevron-right me-2"></i>Help</a></li>
                    </ul>
                </div>

                <!-- Features -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="footer-title">Key Features</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('health.tracking') }}"><i class="fas fa-chevron-right me-2"></i>Health Metrics</a></li>
                        <li><a href="{{ route('medicine.reminders') }}"><i class="fas fa-chevron-right me-2"></i>Medicine Reminders</a></li>
                        <li><a href="{{ route('health.records') }}"><i class="fas fa-chevron-right me-2"></i>Medical Records</a></li>
                        <li><a href="{{ route('health.symptoms') }}"><i class="fas fa-chevron-right me-2"></i>Symptom Tracker</a></li>
                        <li><a href="{{ route('health.suggestions') }}"><i class="fas fa-chevron-right me-2"></i>AI Suggestions</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="footer-title">Contact Us</h5>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Healthcare Avenue, Medical District, Dhaka, Bangladesh</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+880 1234 567890</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>support@mydoctor.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>24/7 Customer Support</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Divider -->
            <hr class="footer-divider">

            <!-- Bottom Bar -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="copyright mb-0">
                        &copy; {{ date('Y') }} My Doctor. All rights reserved. | Making healthcare simple
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="footer-bottom-links">
                        <li><a href="{{ route('privacy.policy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('terms.service') }}">Terms of Service</a></li>
                        <li><a href="{{ route('cookie.policy') }}">Cookie Policy</a></li>
                        <li><a href="{{ route('sitemap') }}">Sitemap</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>