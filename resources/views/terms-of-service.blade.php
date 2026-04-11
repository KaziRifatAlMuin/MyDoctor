@extends('layouts.app')

@section('title', 'Terms of Service - My Doctor')

@section('content')
    <div class="legal-wrap">
        <div class="container py-5">
            <section class="legal-hero mb-4 mb-md-5">
                <h1 class="mb-2"><i class="fas fa-file-contract me-2"></i>Terms of Service</h1>
                <p class="mb-0">These terms govern your use of My Doctor services and features.</p>
                <small class="d-block mt-2">Last updated: {{ now()->format('F d, Y') }}</small>
            </section>

            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="legal-card mb-3">
                        <h5>1. Eligibility and Account Responsibility</h5>
                        <p>You are responsible for maintaining the confidentiality of your account credentials and for all
                            activities performed under your account. You agree to provide accurate profile and health
                            information when using My Doctor.</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>2. Health Information and Guidance</h5>
                        <p>My Doctor provides educational information, reminders, and AI-assisted guidance. It does not
                            replace professional diagnosis, emergency services, or treatment from licensed healthcare
                            providers.</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>3. Community Standards</h5>
                        <p>You agree not to post abusive, misleading, or harmful content. We may moderate, remove, or
                            restrict content/accounts that violate community safety standards.</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>4. Service Availability</h5>
                        <p>We aim for continuous service reliability but cannot guarantee uninterrupted access. Scheduled
                            maintenance or third-party outages may temporarily impact app functionality.</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>5. Limitation of Liability</h5>
                        <p>To the extent permitted by law, My Doctor is provided on an "as is" basis. We are not liable for
                            indirect losses resulting from use of educational recommendations or interrupted service.</p>
                    </article>

                    <article class="legal-card">
                        <h5>6. Contact</h5>
                        <p class="mb-0">For terms-related inquiries, contact us from the Help page or email support using
                            your registered account details.</p>
                    </article>
                </div>

                <div class="col-lg-4">
                    <aside class="legal-side-card mb-3">
                        <h6><i class="fas fa-shield-alt me-2"></i>Platform Promise</h6>
                        <ul class="mb-0">
                            <li>Patient-first product decisions</li>
                            <li>Respectful and safe community space</li>
                            <li>Transparent service practices</li>
                        </ul>
                    </aside>

                    <aside class="legal-side-card">
                        <h6><i class="fas fa-link me-2"></i>Related Pages</h6>
                        <a href="{{ route('privacy.policy') }}" class="btn btn-outline-light w-100 mb-2">Privacy Policy</a>
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
