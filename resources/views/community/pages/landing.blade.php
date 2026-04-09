@extends('layouts.app')

@section('title', 'Community - Connect, Share, Heal Together')

@section('content')
<!-- Hero Section -->
<section class="community-hero py-5">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <span class="badge bg-primary-soft text-primary mb-3 px-3 py-2 rounded-pill">
                    <i class="fas fa-users me-2"></i>Join 50,000+ Members
                </span>
                <h1 class="display-4 fw-bold mb-4">
                    Connect, Share, and
                    <span class="text-primary">Heal Together</span>
                </h1>
                <p class="lead text-muted mb-4">
                    Join a supportive community where you can share experiences, ask questions, 
                    and find comfort in knowing you're not alone on your health journey.
                </p>
                <div class="d-flex flex-wrap gap-3 button-group">
                    <!-- Redirect to login if not authenticated -->
                    <a href="{{ auth()->check() ? route('community.posts.index') : route('login') }}" class="btn btn-primary btn-lg rounded-pill px-5 join-discussions-btn">
                        <i class="fas fa-comments me-2"></i>Join Discussions
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg rounded-pill px-5 signup-btn">
                            <i class="fas fa-user-plus me-2"></i>Sign Up Free
                        </a>
                    @endguest
                </div>
                
                <!-- Stats -->
                <div class="row mt-5 g-4">
                    <div class="col-4">
                        <div class="stat-item">
                            <h3 class="fw-bold text-primary mb-1">50K+</h3>
                            <small class="text-muted">Active Members</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <h3 class="fw-bold text-primary mb-1">10K+</h3>
                            <small class="text-muted">Discussions</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <h3 class="fw-bold text-primary mb-1">24/7</h3>
                            <small class="text-muted">Support</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="hero-image-wrapper text-center">
                    <img src="{{ asset('images/community-hero.svg') }}" 
                         alt="Community illustration" 
                         class="img-fluid floating-animation"
                         onerror="this.onerror=null; this.src='{{ asset('images/default-community.png') }}';">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3 px-3 py-2 rounded-pill">
                <i class="fas fa-star me-2"></i>Why Join Us
            </span>
            <h2 class="fw-bold mb-3">More Than Just a Forum</h2>
            <p class="text-muted lead">A safe space designed for meaningful health discussions</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card h-100">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon bg-primary-soft text-primary">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                    <h4>Safe & Anonymous</h4>
                    <p class="text-muted">Share your experiences anonymously or with your real identity - you're in control of your privacy.</p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card h-100">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon bg-success-soft text-success">
                            <i class="fas fa-brain fa-2x"></i>
                        </div>
                    </div>
                    <h4>Expert Moderated</h4>
                    <p class="text-muted">All discussions are moderated by healthcare professionals to ensure accurate information.</p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card h-100">
                    <div class="feature-icon-wrapper mb-4">
                        <div class="feature-icon bg-info-soft text-info">
                            <i class="fas fa-heartbeat fa-2x"></i>
                        </div>
                    </div>
                    <h4>Disease-Filtered Posts</h4>
                    <p class="text-muted">Find and connect with people facing similar health challenges through disease-specific posts.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-soft text-primary mb-3 px-3 py-2 rounded-pill">
                <i class="fas fa-map-signs me-2"></i>Simple Process
            </span>
            <h2 class="fw-bold mb-3">How It Works</h2>
            <p class="text-muted lead">Get started in three simple steps</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="step-card text-center">
                    <div class="step-number mx-auto mb-4">1</div>
                    <div class="step-icon mb-3">
                        <i class="fas fa-user-plus fa-3x text-primary"></i>
                    </div>
                    <h4>Create Account</h4>
                    <p class="text-muted">Sign up for free and set up your health profile</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="step-card text-center">
                    <div class="step-number mx-auto mb-4">2</div>
                    <div class="step-icon mb-3">
                        <i class="fas fa-pen fa-3x text-primary"></i>
                    </div>
                    <h4>Share Your Story</h4>
                    <p class="text-muted">Post about your health journey and experiences</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="step-card text-center">
                    <div class="step-number mx-auto mb-4">3</div>
                    <div class="step-icon mb-3">
                        <i class="fas fa-comments fa-3x text-primary"></i>
                    </div>
                    <h4>Connect & Support</h4>
                    <p class="text-muted">Engage with others, share advice, and find support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center text-center text-white">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-4">Ready to Join Our Community?</h2>
                <p class="lead mb-4 opacity-90">Connect with thousands of people who understand your journey</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap button-group">
                    <!-- Redirect to login if not authenticated -->
                    <a href="{{ auth()->check() ? route('community.posts.index') : route('login') }}" class="btn btn-light btn-lg rounded-pill px-5 start-connecting-btn">
                        <i class="fas fa-comments me-2"></i>Start Connecting
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg rounded-pill px-5 signup-cta-btn">
                            <i class="fas fa-user-plus me-2"></i>Sign Up Free
                        </a>
                    @endguest
                </div>
                <p class="mt-4 mb-0 small opacity-75">
                    <i class="fas fa-shield-alt me-2"></i>100% Free • Privacy Protected • Expert Moderated
                </p>
            </div>
        </div>
    </div>
    <!-- Background overlay -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%); pointer-events: none;"></div>
</section>
@endsection

@push('styles')
<style>
/* Hero Section */
.community-hero {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    min-height: 600px;
    display: flex;
    align-items: center;
    position: relative;
    z-index: 1;
}

.min-vh-50 {
    min-height: 50vh;
}

/* Badge Styles */
.bg-primary-soft {
    background-color: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.bg-success-soft {
    background-color: rgba(72, 187, 120, 0.1);
    color: #48bb78;
}

.bg-info-soft {
    background-color: rgba(66, 153, 225, 0.1);
    color: #4299e1;
}

.bg-warning-soft {
    background-color: rgba(237, 137, 54, 0.1);
    color: #ed8936;
}

.bg-danger-soft {
    background-color: rgba(245, 101, 101, 0.1);
    color: #f56565;
}

/* Feature Cards */
.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
    position: relative;
    z-index: 1;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.1);
}

.feature-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
}

/* Step Cards */
.step-card {
    padding: 2rem;
    position: relative;
    z-index: 1;
}

.step-number {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

/* Stat Items */
.stat-item {
    text-align: center;
    padding: 1rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    position: relative;
    z-index: 1;
}

.stat-item h3 {
    font-size: 1.8rem;
    margin-bottom: 0.25rem;
}

.stat-item small {
    font-size: 0.85rem;
}

/* Animation */
.floating-animation {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}

/* BUTTON FIXES - CRITICAL */
.btn {
    display: inline-block !important;
    font-weight: 500 !important;
    text-align: center !important;
    vertical-align: middle !important;
    cursor: pointer !important;
    user-select: none !important;
    border: 1px solid transparent !important;
    padding: 0.5rem 1rem !important;
    font-size: 1rem !important;
    line-height: 1.5 !important;
    border-radius: 50rem !important;
    transition: all 0.3s ease !important;
    text-decoration: none !important;
    position: relative !important;
    z-index: 999 !important;
    pointer-events: auto !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    border: none !important;
}

.btn-primary:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3) !important;
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
    color: white !important;
}

.btn-outline-primary {
    border: 2px solid #667eea !important;
    color: #667eea !important;
    background: transparent !important;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-color: transparent !important;
    color: white !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3) !important;
}

.btn-light {
    background: white !important;
    color: #667eea !important;
    border: none !important;
}

.btn-light:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 10px 20px rgba(255, 255, 255, 0.3) !important;
    background: #f8f9fa !important;
    color: #764ba2 !important;
}

.btn-outline-light {
    border: 2px solid white !important;
    color: white !important;
    background: transparent !important;
}

.btn-outline-light:hover {
    background: white !important;
    color: #667eea !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 10px 20px rgba(255, 255, 255, 0.3) !important;
}

.btn-lg {
    padding: 0.75rem 1.5rem !important;
    font-size: 1.1rem !important;
}

/* Button group fixes */
.button-group {
    position: relative !important;
    z-index: 999 !important;
    margin: 20px 0 !important;
}

/* Ensure buttons are clickable */
a.btn,
button.btn {
    pointer-events: auto !important;
    cursor: pointer !important;
    position: relative !important;
    z-index: 9999 !important;
}

/* Fix for any overlapping elements */
.hero-image-wrapper {
    position: relative;
    z-index: 1;
    pointer-events: none;
}

.hero-image-wrapper img {
    pointer-events: none;
    max-width: 100%;
    height: auto;
}

/* CTA Section */
.cta-section {
    position: relative !important;
    overflow: hidden !important;
    z-index: 1 !important;
}

.cta-section .container {
    position: relative !important;
    z-index: 20 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .community-hero {
        min-height: auto;
        padding: 60px 0;
    }
    
    .display-4 {
        font-size: 2rem;
    }
    
    .step-card {
        padding: 1.5rem;
    }
    
    .stat-item h3 {
        font-size: 1.4rem;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem !important;
        font-size: 1rem !important;
    }
}

/* AOS Animation Styles */
[data-aos] {
    pointer-events: none;
}

[data-aos].aos-animate {
    pointer-events: auto;
}

/* Ensure parent containers don't block clicks */
.container, .row, .col-lg-6, .col-lg-8 {
    position: relative;
    z-index: 10;
}

/* Debug outline - remove after fixing */
/* 
.btn {
    outline: 2px solid red !important;
} 
*/
</style>
@endpush

@push('scripts')
<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    // Initialize AOS with optimized settings
    AOS.init({
        duration: 800,
        once: true,
        offset: 100,
        disable: 'mobile'
    });

    // Debug: Check if buttons are clickable
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.btn');
        console.log('Found buttons:', buttons.length);
        
        buttons.forEach((btn, index) => {
            console.log(`Button ${index}:`, {
                text: btn.textContent.trim(),
                href: btn.getAttribute('href'),
                classes: btn.className,
                computedStyle: window.getComputedStyle(btn).pointerEvents,
                isVisible: btn.offsetParent !== null,
                rect: btn.getBoundingClientRect()
            });
            
            // Add click test
            btn.addEventListener('click', function(e) {
                console.log('Button clicked:', this.textContent.trim(), 'Href:', this.getAttribute('href'));
                // Don't prevent default - let the navigation happen
            });
        });
    });

    // Add smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endpush