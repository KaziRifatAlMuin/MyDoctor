@extends('layouts.app')

@section('title', 'Home - My Doctor')

@section('content')


<!-- Section 3: Features with Illustrations -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary">Everything You Need in One Place</h2>
            <p class="text-muted">Comprehensive healthcare tools designed for your wellness journey</p>
        </div>
        
        <div class="row g-4">
            <!-- Community Posting -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card-modern text-center p-4 h-100">
                    <div class="feature-image mb-4">
                        <img src="{{ asset('images/com.jpg') }}" alt="Community illustration showing people connecting" class="img-fluid" style="height: 150px; object-fit: contain;">
                    </div>
                    <h4>Community Posting</h4>
                    <p class="text-muted">Connect with others sharing similar health experiences</p>
                    <a href="{{ route('community') }}" class="btn btn-outline-primary rounded-pill px-4">Learn More</a>
                </div>
            </div>
            
            <!-- Medicine Reminder -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card-modern text-center p-4 h-100">
                    <div class="feature-image mb-4">
                        <img src="{{ asset('images/med.jpg') }}" alt="Medicine reminder illustration with clock and pills" class="img-fluid" style="height: 150px; object-fit: contain;">
                    </div>
                    <h4>Medicine Reminders</h4>
                    <p class="text-muted">Never miss a dose with smart notifications</p>
                    <a href="{{ route('medicine.reminders') }}" class="btn btn-outline-primary rounded-pill px-4">Learn More</a>
                </div>
            </div>
            
            <!-- Health Metrics Tracking -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card-modern text-center p-4 h-100">
                    <div class="feature-image mb-4">
                        <img src="{{ asset('images/HM.jpg') }}" alt="Health metrics tracking illustration with charts" class="img-fluid" style="height: 150px; object-fit: contain;">
                    </div>
                    <h4>Health Metrics</h4>
                    <p class="text-muted">Track BP, sugar, cholesterol and more</p>
                    <a href="{{ route('health.tracking') }}" class="btn btn-outline-primary rounded-pill px-4">Learn More</a>
                </div>
            </div>
            
            <!-- AI ChatBot -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card-modern text-center p-4 h-100">
                    <div class="feature-image mb-4">
                        <img src="{{ asset('images/ai.jpg') }}" alt="AI chatbot illustration with robot and chat bubbles" class="img-fluid" style="height: 150px; object-fit: contain;">
                    </div>
                    <h4>AI Health Assistant</h4>
                    <p class="text-muted">Get instant answers about diseases and symptoms</p>
                    <button onclick="toggleChatbot()" class="btn btn-outline-primary rounded-pill px-4 border-0 bg-transparent">Ask AI</button>
                </div>
            </div>
            
            <!-- Medical Records -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card-modern text-center p-4 h-100">
                    <div class="feature-image mb-4">
                        <img src="{{ asset('images/mr.jpg') }}" alt="Medical records illustration with documents and folder" class="img-fluid" style="height: 150px; object-fit: contain;">
                    </div>
                    <h4>Medical Records</h4>
                    <p class="text-muted">Store prescriptions and reports securely</p>
                    <a href="{{ route('health.records') }}" class="btn btn-outline-primary rounded-pill px-4">Learn More</a>
                </div>
            </div>
            
            <!-- Symptom Tracker -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card-modern text-center p-4 h-100">
                    <div class="feature-image mb-4">
                        <img src="{{ asset('images/sym.jpg') }}" alt="Symptom tracker illustration with checklist and body diagram" class="img-fluid" style="height: 150px; object-fit: contain;">
                    </div>
                    <h4>Symptom Tracker</h4>
                    <p class="text-muted">Track symptoms and get primary suggestions</p>
                    <a href="{{ route('health.symptoms') }}" class="btn btn-outline-primary rounded-pill px-4">Learn More</a>
                </div>
            </div>
            
            <!-- Personalized Suggestions -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card-modern text-center p-4 h-100">
                    <div class="feature-image mb-4">
                        <!-- Placeholder for Suggestions illustration - add image later -->
                        <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                            <i class="fas fa-map-marked-alt fa-4x text-primary opacity-50"></i>
                        </div>
                    </div>
                    <h4>Smart Health Suggestions</h4>
                    <p class="text-muted">Personalized recommendations based on your health metrics</p>
                    <a href="{{ route('health.suggestions') }}" class="btn btn-outline-primary rounded-pill px-4">View Suggestions</a>
                </div>
            </div>
            
            <!-- Health Tips -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card-modern text-center p-4 h-100">
                    <div class="feature-image mb-4">
                        <!-- Placeholder for Health Tips illustration - add image later -->
                        <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                            <i class="fas fa-lightbulb fa-4x text-warning opacity-50"></i>
                        </div>
                    </div>
                    <h4>Health Tips</h4>
                    <p class="text-muted">Daily health tips and articles for healthy lifestyle</p>
                    <a href="{{ route('health.tips') }}" class="btn btn-outline-primary rounded-pill px-4">Read Tips</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 4: How It Works with Step Images -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary">Your Journey to Better Health</h2>
            <p class="text-muted">Simple steps to start your wellness journey</p>
        </div>
        
        <div class="row g-4">
            <!-- Step 1: Register -->
            <div class="col-md-3">
                <div class="step-card text-center">
                    <div class="step-image mb-4">
                        <img src="{{ asset('images/reg.jpg') }}" alt="Person registering on mobile phone" class="img-fluid rounded-circle border border-4 border-primary p-2" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <div class="step-number-badge bg-primary text-white mx-auto mb-3">1</div>
                    <h5>Create Account</h5>
                    <p class="text-muted small">Sign up free in 30 seconds</p>
                </div>
            </div>
            
            <!-- Step 2: Track Health -->
            <div class="col-md-3">
                <div class="step-card text-center">
                    <div class="step-image mb-4">
                        <img src="{{ asset('images/ht.jpg') }}" alt="Person measuring blood pressure and tracking health" class="img-fluid rounded-circle border border-4 border-success p-2" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <div class="step-number-badge bg-success text-white mx-auto mb-3">2</div>
                    <h5>Track Health</h5>
                    <p class="text-muted small">Log metrics and symptoms</p>
                </div>
            </div>
            
            <!-- Step 3: Get Insights -->
            <div class="col-md-3">
                <div class="step-card text-center">
                    <div class="step-image mb-4">
                        <!-- Placeholder for Analyze step image - add later -->
                        <div class="bg-light rounded-circle border border-4 border-warning p-2 mx-auto d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                            <i class="fas fa-chart-pie fa-3x text-warning"></i>
                        </div>
                    </div>
                    <div class="step-number-badge bg-warning text-white mx-auto mb-3">3</div>
                    <h5>Get Insights</h5>
                    <p class="text-muted small">AI-powered suggestions</p>
                </div>
            </div>
            
            <!-- Step 4: Improve Health -->
            <div class="col-md-3">
                <div class="step-card text-center">
                    <div class="step-image mb-4">
                        <!-- Placeholder for Improve step image - add later -->
                        <div class="bg-light rounded-circle border border-4 border-info p-2 mx-auto d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                            <i class="fas fa-heartbeat fa-3x text-info"></i>
                        </div>
                    </div>
                    <div class="step-number-badge bg-info text-white mx-auto mb-3">4</div>
                    <h5>Improve Health</h5>
                    <p class="text-muted small">Stay on track with reminders</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 5: Testimonials with Person Photos -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary">What Our Users Say</h2>
            <p class="text-muted">Trusted by thousands of patients and doctors</p>
        </div>
        
        <div class="row g-4">
            <!-- Testimonial 1 - Patient -->
            <div class="col-md-4">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="d-flex align-items-center mb-4">
                        <!-- Patient 1 photo - add later -->
                        <div class="bg-secondary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-circle fa-3x text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Rahima Begum</h6>
                            <small class="text-muted">Patient since 2023</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="text-muted fst-italic">"The medicine reminders have been a lifesaver for my father. The AI chatbot helps us understand symptoms before visiting the doctor."</p>
                </div>
            </div>
            
            <!-- Testimonial 2 - Doctor -->
            <div class="col-md-4">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="d-flex align-items-center mb-4">
                        <!-- Doctor 1 photo - add later -->
                        <div class="bg-secondary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-md fa-3x text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Dr. Shahidul Islam</h6>
                            <small class="text-muted">Cardiologist</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="text-muted fst-italic">"My patients love tracking their BP and sugar levels. The reports help me make better treatment decisions."</p>
                </div>
            </div>
            
            <!-- Testimonial 3 - Patient -->
            <div class="col-md-4">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="d-flex align-items-center mb-4">
                        <!-- Patient 2 photo - add later -->
                        <div class="bg-secondary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-circle fa-3x text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Nasir Uddin</h6>
                            <small class="text-muted">Diabetes Patient</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="text-muted fst-italic">"Tracking my blood sugar has never been easier. The community support group keeps me motivated."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 7: Impact Statistics Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row g-4 text-white text-center">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <!-- Users icon - add later -->
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-3x text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">50,000+</h2>
                    <p>Active Users</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <!-- Records icon - add later -->
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-file-medical fa-3x text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">100,000+</h2>
                    <p>Health Records</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <!-- Reminders icon - add later -->
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-bell fa-3x text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">1M+</h2>
                    <p>Medicine Reminders</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <!-- Doctors icon - add later -->
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-md fa-3x text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">500+</h2>
                    <p>Partner Doctors</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="cta-card text-center p-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 30px;">
            <h2 class="text-white fw-bold mb-3">Ready to take control of your health?</h2>
            <p class="text-white text-opacity-90 mb-4 fs-5">Join thousands of users who are already managing their health better with My Doctor</p>
            @guest
                <a href="{{ route('register') }}" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                    <i class="fas fa-user-plus me-2"></i>Sign Up Free
                </a>
            @else
                <a href="{{ route('health.tracking') }}" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-semibold">
                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                </a>
            @endguest
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Hero Section */
    .hero-section {
        min-height: 600px;
        position: relative;
        margin-top: -20px;
    }
    
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
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
        z-index: 2;
    }
    
    .floating-animation {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
    
    /* Dashboard Card */
    .dashboard-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        overflow: hidden;
    }
    
    .avatar-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Feature Cards */
    .feature-card-modern {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .feature-card-modern:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    
    .feature-image img {
        transition: transform 0.3s ease;
    }
    
    .feature-card-modern:hover .feature-image img {
        transform: scale(1.05);
    }
    
    /* Step Cards */
    .step-number-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin-top: -30px;
        position: relative;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* Testimonial Cards */
    .testimonial-card {
        transition: transform 0.3s ease;
    }
    
    .testimonial-card:hover {
        transform: translateY(-5px);
    }
    
    /* Statistics */
    .bg-opacity-20 {
        --bs-bg-opacity: 0.2;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .hero-section {
            min-height: 500px;
        }
        
        .hero-section h1 {
            font-size: 2rem;
        }
        
        .feature-card-modern {
            padding: 20px !important;
        }
    }
</style>
@endpush