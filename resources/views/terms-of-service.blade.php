@extends('layouts.app')

@section('title', __('ui.terms.terms_of_service'))

@section('content')
    <div class="legal-wrap">
        <div class="container py-5">
            <section class="legal-hero mb-4 mb-md-5">
                <h1 class="mb-2"><i class="fas fa-file-contract me-2"></i>{{ __('ui.terms.terms_of_service') }}</h1>
                <p class="mb-0">{{ __('ui.terms.terms_govern_use') }}</p>
                <small class="d-block mt-2">{{ __('ui.terms.last_updated') }}: {{ now()->format('F d, Y') }}</small>
            </section>

            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="legal-card mb-3">
                        <h5>{{ __('ui.terms.eligibility_and_account_responsibility') }}</h5>
                        <p>{{ __('ui.terms.eligibility_and_account_responsibility_answer') }}</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>{{ __('ui.terms.health_information_and_guidance') }}</h5>
                        <p>{{ __('ui.terms.health_information_and_guidance_answer') }}</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>{{ __('ui.terms.community_standards') }}</h5>
                        <p>{{ __('ui.terms.community_standards_answer') }}</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>{{ __('ui.terms.service_availability') }}</h5>
                        <p>{{ __('ui.terms.service_availability_answer') }}</p>
                    </article>

                    <article class="legal-card mb-3">
                        <h5>{{ __('ui.terms.limitation_of_liability') }}</h5>
                        <p>{{ __('ui.terms.limitation_of_liability_answer') }}</p>
                    </article>

                    <article class="legal-card">
                        <h5>{{ __('ui.terms.contact') }}</h5>
                        <p class="mb-0">{{ __('ui.terms.contact_answer') }}</p>
                    </article>
                </div>

                <div class="col-lg-4">
                    <aside class="legal-side-card mb-3">
                        <h6><i class="fas fa-shield-alt me-2"></i>{{ __('ui.terms.platform_promise') }}</h6>
                        <ul class="mb-0">
                            <li>{{ __('ui.terms.patient_first_product_decisions') }}</li>
                            <li>{{ __('ui.terms.respectful_safe_community') }}</li>
                            <li>{{ __('ui.terms.transparent_service_practices') }}</li>
                        </ul>
                    </aside>

                    <aside class="legal-side-card">
                        <h6><i class="fas fa-link me-2"></i>{{ __('ui.terms.related_pages') }}</h6>
                        <a href="{{ route('privacy.policy') }}" class="btn btn-outline-light w-100 mb-2">{{ __('ui.terms.privacy_policy') }}</a>
                        <a href="{{ route('help') }}" class="btn btn-light w-100">{{ __('ui.terms.help_center') }}</a>
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