@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
    <div class="legal-wrap">
        <div class="container py-5">
            <section class="legal-hero mb-4 mb-md-5">
                <h1 class="mb-2"><i class="fas fa-user-shield me-2"></i>Privacy Policy</h1>
                <p class="mb-0">How My Doctor collects, uses, and protects your personal and health-related data.</p>
                <small class="d-block mt-2">Last updated: {{ now()->format('F d, Y') }}</small>
            </section>

            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="legal-card mb-3">
                        <h5>1. Information We Collect</h5>
                        <p>We collect account details (such as name, email, and profile information), health logs you
                            submit, medicine reminders you configure, and files you upload for your own record management.
                        </p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>2. How We Use Information</h5>
                        <p>Data is used to deliver reminders, display your dashboard, provide health suggestions, enable
                            secure community features, and improve product quality and safety.</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>3. Data Visibility and Privacy Controls</h5>
                        <p>You can control what personal information is visible from account settings. Sensitive address
                            details such as house and street are not exposed publicly unless explicitly designed in your
                            privacy controls.</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>4. Data Security</h5>
                        <p>We apply technical and organizational safeguards to protect your data. However, no digital system
                            is absolutely risk-free; you should also protect your password and account access.</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>5. Data Retention</h5>
                        <p>We retain data for service continuity, legal compliance, and account history. You may request
                            account closure through support channels where applicable.</p>
                    </article>

                    <article class="legal-card">
                        <h5>6. Contact and Requests</h5>
                        <p class="mb-0">For privacy-related questions, access requests, or corrections, contact us through
                            the Help page with your registered account information.</p>
                    </article>
                </div>

                <div class="col-lg-4">
                    <aside class="legal-side-card mb-3">
                        <h6><i class="fas fa-lock me-2"></i>Your Data Rights</h6>
                        <ul class="mb-0">
                            <li>Access your profile and health records</li>
                            <li>Update incorrect account information</li>
                            <li>Manage notification and visibility settings</li>
                        </ul>
                    </aside>

                    <aside class="legal-side-card">
                        <h6><i class="fas fa-link me-2"></i>Related Pages</h6>
                        <a href="{{ route('terms.service') }}" class="btn btn-outline-light w-100 mb-2">Terms of Service</a>
                        <a href="{{ route('help') }}" class="btn btn-light w-100">Help Center</a>
                    </aside>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .legal-wrap {
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 10%, rgba(192, 132, 252, 0.2), transparent 30%),
                radial-gradient(circle at 85% 88%, rgba(167, 139, 250, 0.2), transparent 30%),
                #f8f5ff;
        }

        .legal-hero {
            border-radius: 20px;
            padding: 2rem 1.75rem;
            background: linear-gradient(130deg, #5b21b6, #7e22ce);
            color: #f8f1ff;
            box-shadow: 0 16px 34px rgba(91, 33, 182, 0.25);
        }

        .legal-card {
            border-radius: 16px;
            padding: 1.2rem 1.25rem;
            background: #ffffff;
            border: 1px solid rgba(124, 58, 237, 0.15);
            box-shadow: 0 8px 20px rgba(91, 33, 182, 0.08);
        }

        .legal-card h5 {
            color: #4c1d95;
            font-weight: 700;
            margin-bottom: 0.55rem;
        }

        .legal-card p {
            color: #5d4f79;
            margin-bottom: 0;
            line-height: 1.7;
        }

        .legal-side-card {
            border-radius: 16px;
            padding: 1.1rem 1rem;
            background: linear-gradient(150deg, #6d28d9, #8b5cf6);
            color: #f5ecff;
            box-shadow: 0 10px 24px rgba(91, 33, 182, 0.18);
        }

        .legal-side-card h6 {
            font-weight: 700;
            margin-bottom: 0.7rem;
        }

        .legal-side-card ul {
            padding-left: 1.1rem;
            margin-bottom: 0;
        }

        .legal-side-card li {
            margin-bottom: 0.45rem;
        }
    </style>
@endpush
