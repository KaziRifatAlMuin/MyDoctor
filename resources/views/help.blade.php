@extends('layouts.app')

@section('title', __('ui.auto.Help'))

@push('styles')
    <style>
        .help-section {
            background: #f8f9fb;
            min-height: 100vh;
            padding: 2.5rem 0 3rem;
        }

        .help-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            color: white;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .help-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .help-hero h2 {
            font-weight: 800;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .help-hero p {
            opacity: 0.9;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .help-search {
            max-width: 500px;
            margin: 0 auto;
            position: relative;
        }

        .help-search input {
            width: 100%;
            border: none;
            border-radius: 30px;
            padding: 0.8rem 1.5rem 0.8rem 3rem;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .help-search input:focus {
            outline: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .help-search .search-icon {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1rem;
        }

        /* Quick Links Grid */
        .quick-link-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #2d3748;
            transition: transform 0.2s, box-shadow 0.2s;
            display: block;
            height: 100%;
        }

        .quick-link-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
            color: #2d3748;
        }

        .quick-link-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
        }

        .quick-link-icon.ql-purple {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
        }

        .quick-link-icon.ql-green {
            background: rgba(56, 161, 105, 0.12);
            color: #38a169;
        }

        .quick-link-icon.ql-orange {
            background: rgba(221, 107, 32, 0.12);
            color: #dd6b20;
        }

        .quick-link-icon.ql-red {
            background: rgba(229, 62, 62, 0.12);
            color: #e53e3e;
        }

        .quick-link-icon.ql-blue {
            background: rgba(49, 130, 206, 0.12);
            color: #3182ce;
        }

        .quick-link-icon.ql-teal {
            background: rgba(56, 178, 172, 0.12);
            color: #319795;
        }

        .quick-link-title {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.3rem;
        }

        .quick-link-desc {
            font-size: 0.8rem;
            color: #718096;
            margin: 0;
        }

        /* FAQ Section */
        .faq-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .faq-card-header {
            padding: 1.25rem 1.5rem 0.75rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .faq-card-header h5 {
            font-weight: 700;
            color: #667eea;
            font-size: 1.05rem;
            margin: 0;
        }

        .faq-card-body {
            padding: 0;
        }

        .accordion-item {
            border: none !important;
            border-bottom: 1px solid #f0f0f0 !important;
        }

        .accordion-item:last-child {
            border-bottom: none !important;
        }

        .accordion-button {
            font-weight: 600;
            font-size: 0.92rem;
            color: #2d3748;
            padding: 1rem 1.5rem;
            background: white;
            box-shadow: none !important;
        }

        .accordion-button:not(.collapsed) {
            color: #667eea;
            background: rgba(102, 126, 234, 0.04);
        }

        .accordion-button::after {
            background-size: 0.9rem;
        }

        .accordion-body {
            padding: 0 1.5rem 1rem;
            font-size: 0.88rem;
            color: #4a5568;
            line-height: 1.7;
        }

        /* Guide Cards */
        .guide-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .guide-card:hover {
            transform: translateY(-2px);
        }

        .guide-step {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.82rem;
            flex-shrink: 0;
        }

        .guide-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #2d3748;
        }

        .guide-desc {
            font-size: 0.85rem;
            color: #4a5568;
            margin: 0;
        }

        /* Contact Card */
        .contact-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            border-left: 5px solid #667eea;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.6rem 0;
            font-size: 0.88rem;
            color: #4a5568;
        }

        .contact-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .searchable-item {
            transition: opacity 0.2s;
        }

        .searchable-item.hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="help-section">
        <div class="container" style="max-width: 1140px;">

            {{-- Hero --}}
            <div class="help-hero">
                <h2><i class="fas fa-question-circle me-2"></i>How can we help you?</h2>
                <p>Find answers to common questions, guides, and support</p>
                <div class="help-search">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="helpSearchInput" placeholder="Search for help topics..." autocomplete="off">
                </div>
                @if(!auth()->check() || !auth()->user()->isAdmin())
                    <div class="mt-3" style="text-align:center;">
                        <button onclick="toggleChatbot()" class="btn btn-sm btn-light" style="border-radius:12px;border:1px solid rgba(0,0,0,0.06);">
                            <i class="fas fa-user-md me-1"></i> Ask MyDoctor AI
                        </button>
                    </div>
                @endif
            </div>

            {{-- Quick Links --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-general" class="quick-link-card" onclick="scrollToFaq('general')">
                        <div class="quick-link-icon ql-purple"><i class="fas fa-info-circle"></i></div>
                        <div class="quick-link-title">General</div>
                        <p class="quick-link-desc">About MyDoctor</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-health" class="quick-link-card" onclick="scrollToFaq('health')">
                        <div class="quick-link-icon ql-green"><i class="fas fa-heartbeat"></i></div>
                        <div class="quick-link-title">Health</div>
                        <p class="quick-link-desc">Metrics & tracking</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-medicine" class="quick-link-card" onclick="scrollToFaq('medicine')">
                        <div class="quick-link-icon ql-orange"><i class="fas fa-pills"></i></div>
                        <div class="quick-link-title">Medicine</div>
                        <p class="quick-link-desc">Reminders & logs</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-uploads" class="quick-link-card" onclick="scrollToFaq('uploads')">
                        <div class="quick-link-icon ql-red"><i class="fas fa-file-medical"></i></div>
                        <div class="quick-link-title">Uploads</div>
                        <p class="quick-link-desc">Prescriptions & reports</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#faq-account" class="quick-link-card" onclick="scrollToFaq('account')">
                        <div class="quick-link-icon ql-blue"><i class="fas fa-user-cog"></i></div>
                        <div class="quick-link-title">Account</div>
                        <p class="quick-link-desc">Profile & settings</p>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#getting-started" class="quick-link-card">
                        <div class="quick-link-icon ql-teal"><i class="fas fa-rocket"></i></div>
                        <div class="quick-link-title">Get Started</div>
                        <p class="quick-link-desc">Step-by-step guide</p>
                    </a>
                </div>
            </div>

            <div class="row">
                {{-- FAQ Column --}}
                <div class="col-lg-8">

                    {{-- General FAQ --}}
                    <div class="faq-card searchable-item" id="faq-general">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-info-circle me-2"></i>General Questions</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accGeneral">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#g1">What is MyDoctor?</button></h2>
                                    <div id="g1" class="accordion-collapse collapse" data-bs-parent="#accGeneral">
                                        <div class="accordion-body">MyDoctor is a comprehensive personal health management
                                            platform that helps you track health metrics, manage medicines, log symptoms,
                                            store medical records, and get personalized health suggestions — all from one
                                            place.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#g2">Is MyDoctor free to use?</button>
                                    </h2>
                                    <div id="g2" class="accordion-collapse collapse" data-bs-parent="#accGeneral">
                                        <div class="accordion-body">Yes! MyDoctor is completely free. All features including
                                            health tracking, medicine reminders, prescription uploads, and smart suggestions
                                            are available at no cost.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#g3">Is my health data safe?</button>
                                    </h2>
                                    <div id="g3" class="accordion-collapse collapse" data-bs-parent="#accGeneral">
                                        <div class="accordion-body">Absolutely. Your data is stored securely and is only
                                            accessible to you. We use industry-standard encryption and never share your
                                            health information with third parties.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Health FAQ --}}
                    <div class="faq-card searchable-item" id="faq-health">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-heartbeat me-2"></i>Health Tracking</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accHealth">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h1">What health
                                            metrics can I track?</button></h2>
                                    <div id="h1" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">You can track 15+ metric types including blood
                                            pressure, blood glucose, heart rate, body weight, BMI, oxygen saturation,
                                            temperature, cholesterol, hemoglobin, creatinine, and more. Each metric supports
                                            Bangla translations.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h2">How do I record
                                            a health metric?</button></h2>
                                    <div id="h2" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">Go to <strong>Health Dashboard → Metrics</strong> tab
                                            and click "Record Metric". Select the metric type, enter the values, and save.
                                            Your readings will be charted over time for easy tracking.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h3">How do I log
                                            symptoms?</button></h2>
                                    <div id="h3" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">Go to <strong>Health Dashboard → Symptoms</strong> tab
                                            and click "Log Symptom". You can search from 100+ symptoms (with Bangla names),
                                            set severity level (1-10), and add optional notes.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#h4">What are Smart
                                            Suggestions?</button></h2>
                                    <div id="h4" class="accordion-collapse collapse" data-bs-parent="#accHealth">
                                        <div class="accordion-body">Smart Suggestions analyzes your recorded metrics,
                                            symptoms, conditions, and medicine adherence to provide personalized health
                                            recommendations. For example, if your blood pressure is high, you'll get
                                            specific dietary and lifestyle tips.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Medicine FAQ --}}
                    <div class="faq-card searchable-item" id="faq-medicine">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-pills me-2"></i>Medicine Management</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accMedicine">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m1">How do I add a
                                            medicine?</button></h2>
                                    <div id="m1" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">Go to <strong>Medicine → Add Medicine</strong>. Enter
                                            the medicine name, dosage, form (tablet/syrup/etc.), and any additional notes.
                                            Then create a schedule to set when you should take it.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m2">How do medicine
                                            reminders work?</button></h2>
                                    <div id="m2" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">After adding medicine schedules, reminders
                                            automatically appear at the set times. You can mark each dose as "Taken" or
                                            "Missed". You can also snooze reminders. Enable push notifications in your
                                            profile to get browser alerts.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m3">What is
                                            adherence rate?</button></h2>
                                    <div id="m3" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">Adherence rate shows what percentage of your scheduled
                                            medicines you've taken over the last 30 days. A rate above 80% is ideal. Low
                                            adherence will trigger helpful suggestions to improve your routine.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#m4">Can I export my
                                            medicine logs?</button></h2>
                                    <div id="m4" class="accordion-collapse collapse"
                                        data-bs-parent="#accMedicine">
                                        <div class="accordion-body">Yes! Go to <strong>Medicine → Adherence Logs</strong>
                                            and use the "Export CSV" button to download your medication history. You can
                                            filter by date range and specific medicines before exporting.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Uploads FAQ --}}
                    <div class="faq-card searchable-item" id="faq-uploads">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-file-medical me-2"></i>Prescriptions & Reports</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accUploads">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#u1">How do I upload
                                            a prescription?</button></h2>
                                    <div id="u1" class="accordion-collapse collapse" data-bs-parent="#accUploads">
                                        <div class="accordion-body">Go to <strong>Health Dashboard → Prescriptions</strong>
                                            tab and click "Upload". Take a photo or upload an image (JPG/PNG, max 5MB). Add
                                            the doctor name, institution, and date for easy reference.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#u2">Can I upload
                                            medical reports?</button></h2>
                                    <div id="u2" class="accordion-collapse collapse" data-bs-parent="#accUploads">
                                        <div class="accordion-body">Yes! Go to the <strong>Reports</strong> tab in the
                                            Health Dashboard. Upload lab results, X-rays, or other medical documents. Add
                                            notes and summaries for quick reference during doctor visits.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Account FAQ --}}
                    <div class="faq-card searchable-item" id="faq-account">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-user-cog me-2"></i>Account & Settings</h5>
                        </div>
                        <div class="faq-card-body">
                            <div class="accordion" id="accAccount">
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#a1">How do I update
                                            my profile?</button></h2>
                                    <div id="a1" class="accordion-collapse collapse" data-bs-parent="#accAccount">
                                        <div class="accordion-body">Go to <strong>My Profile</strong> from the dashboard or
                                            user menu. You can update your name, phone, date of birth, occupation, blood
                                            group, and profile picture.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#a2">How do I enable
                                            push notifications?</button></h2>
                                    <div id="a2" class="accordion-collapse collapse" data-bs-parent="#accAccount">
                                        <div class="accordion-body">Go to <strong>Profile → Notification
                                                Preferences</strong>. Toggle on push notifications and allow browser
                                            notifications when prompted. This ensures you receive medicine reminders on
                                            time.</div>
                                    </div>
                                </div>
                                <div class="accordion-item searchable-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#a3">Can I change my
                                            password?</button></h2>
                                    <div id="a3" class="accordion-collapse collapse" data-bs-parent="#accAccount">
                                        <div class="accordion-body">Yes, go to <strong>My Profile</strong> and scroll to
                                            the password section. Enter your current password and your new password to
                                            update it securely.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">

                    {{-- Getting Started Guide --}}
                    <div class="faq-card" id="getting-started">
                        <div class="faq-card-header">
                            <h5><i class="fas fa-rocket me-2"></i>Getting Started</h5>
                        </div>
                        <div class="faq-card-body" style="padding: 1rem 1.5rem;">
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">1</div>
                                    <div>
                                        <div class="guide-title">Create Your Profile</div>
                                        <p class="guide-desc">Add your basic info, blood group, and profile picture for a
                                            personalized experience.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">2</div>
                                    <div>
                                        <div class="guide-title">Add Your Medicines</div>
                                        <p class="guide-desc">Enter the medicines you take daily and create schedules so
                                            reminders can work for you.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">3</div>
                                    <div>
                                        <div class="guide-title">Record Health Metrics</div>
                                        <p class="guide-desc">Start logging your BP, glucose, weight, and other vitals to
                                            visualize trends.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">4</div>
                                    <div>
                                        <div class="guide-title">Upload Prescriptions</div>
                                        <p class="guide-desc">Take photos of your prescriptions and reports to keep them
                                            safely organized.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guide-card">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="guide-step">5</div>
                                    <div>
                                        <div class="guide-title">Check Suggestions</div>
                                        <p class="guide-desc">Visit Smart Suggestions to get personalized health
                                            recommendations based on your data.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Contact & Support --}}
                    <div class="contact-card mt-3">
                        <h6 class="fw-bold mb-3" style="color: #667eea;">
                            <i class="fas fa-headset me-2"></i>Need More Help?
                        </h6>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <div class="fw-semibold" style="font-size: 0.85rem;">Email Support</div>
                                <div class="text-muted" style="font-size: 0.8rem;">support@mydoctor.com</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div>
                                <div class="fw-semibold" style="font-size: 0.85rem;">Phone Support</div>
                                <div class="text-muted" style="font-size: 0.8rem;">+880 1XXX-XXXXXX</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="fw-semibold" style="font-size: 0.85rem;">Available</div>
                                <div class="text-muted" style="font-size: 0.8rem;">Sat–Thu, 9AM – 6PM BST</div>
                            </div>
                        </div>
                    </div>

                    {{-- Useful Links --}}
                    <div class="summary-card mt-3">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-link me-2"></i>Useful Links</h6>
                        </div>
                        <div class="summary-card-body">
                            <a href="{{ route('health') }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-heartbeat me-2"></i>Health Dashboard
                            </a>
                            <a href="{{ route('medicine.reminders') }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-bell me-2"></i>Medicine Reminders
                            </a>
                            <a href="{{ route('suggestions') }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-lightbulb me-2"></i>Smart Suggestions
                            </a>
                            <a href="{{ route('dashboard') }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a href="{{ route('community.landing') }}" class="d-block text-decoration-none"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-users me-2"></i>Community
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('helpSearchInput');
            if (!searchInput) return;

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const items = document.querySelectorAll('.accordion-item.searchable-item');

                if (!query) {
                    items.forEach(item => item.classList.remove('hidden'));
                    document.querySelectorAll('.faq-card.searchable-item').forEach(c => c.classList.remove(
                        'hidden'));
                    return;
                }

                // First hide all FAQ sections, then show those with matches
                document.querySelectorAll('.faq-card.searchable-item').forEach(c => c.classList.add(
                    'hidden'));

                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(query)) {
                        item.classList.remove('hidden');
                        item.closest('.faq-card')?.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            });
        });

        function scrollToFaq(id) {
            const el = document.getElementById('faq-' + id);
            if (el) {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    </script>
@endpush
