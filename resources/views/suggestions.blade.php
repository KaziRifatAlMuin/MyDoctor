@extends('layouts.app')

@section('title', __('ui.suggestions.smart_suggestions'))

@push('styles')
    <style>
        .suggestions-section {
            background: #f8f9fb;
            min-height: 100vh;
            padding: 2.5rem 0 3rem;
        }

        .suggestion-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 5px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .suggestion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }

        .suggestion-card.border-danger {
            border-left-color: #e53e3e;
        }

        .suggestion-card.border-warning {
            border-left-color: #dd6b20;
        }

        .suggestion-card.border-info {
            border-left-color: #3182ce;
        }

        .suggestion-card.border-success {
            border-left-color: #38a169;
        }

        .suggestion-card.border-primary {
            border-left-color: #667eea;
        }

        .suggestion-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .suggestion-icon.bg-danger {
            background: rgba(229, 62, 62, 0.12);
            color: #e53e3e;
        }

        .suggestion-icon.bg-warning {
            background: rgba(221, 107, 32, 0.12);
            color: #dd6b20;
        }

        .suggestion-icon.bg-info {
            background: rgba(49, 130, 206, 0.12);
            color: #3182ce;
        }

        .suggestion-icon.bg-success {
            background: rgba(56, 161, 105, 0.12);
            color: #38a169;
        }

        .suggestion-icon.bg-primary {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
        }

        .suggestion-category {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            padding: 0.15rem 0.6rem;
            border-radius: 20px;
            display: inline-block;
        }

        .suggestion-category.cat-danger {
            background: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
        }

        .suggestion-category.cat-warning {
            background: rgba(221, 107, 32, 0.1);
            color: #dd6b20;
        }

        .suggestion-category.cat-info {
            background: rgba(49, 130, 206, 0.1);
            color: #3182ce;
        }

        .suggestion-category.cat-success {
            background: rgba(56, 161, 105, 0.1);
            color: #38a169;
        }

        .suggestion-category.cat-primary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .suggestion-title {
            font-weight: 700;
            font-size: 1rem;
            color: #2d3748;
            margin-bottom: 0.35rem;
        }

        .suggestion-message {
            font-size: 0.88rem;
            color: #4a5568;
            line-height: 1.6;
            margin: 0;
        }

        /* Sidebar summary */
        .summary-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .summary-card-header {
            padding: 1rem 1.25rem 0.6rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-card-header h6 {
            font-weight: 700;
            color: #667eea;
            font-size: 0.92rem;
            margin: 0;
        }

        .summary-card-body {
            padding: 1rem 1.25rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.45rem 0;
            border-bottom: 1px solid #f7f7f7;
            font-size: 0.85rem;
            color: #4a5568;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .summary-dot.green {
            background: #38a169;
        }

        .summary-dot.red {
            background: #e53e3e;
        }

        .summary-dot.orange {
            background: #dd6b20;
        }

        .summary-dot.blue {
            background: #3182ce;
        }

        .summary-dot.purple {
            background: #667eea;
        }

        .filter-btn {
            border: none;
            border-radius: 20px;
            padding: 0.4rem 1rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: #718096;
            background: #edf2f7;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .ai-summary-card {
            background: linear-gradient(150deg, #ffffff 0%, #f6f8ff 100%);
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e9ff;
            padding: 1.1rem 1.2rem;
            margin-bottom: 1rem;
        }

        .ai-summary-title {
            font-size: 1rem;
            font-weight: 700;
            color: #4450a8;
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .ai-summary-title-left {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ai-summary-regenerate {
            border: none;
            background: #eef2ff;
            color: #4756b3;
            font-size: 0.76rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.28rem 0.65rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .ai-summary-regenerate:hover {
            background: #dfe7ff;
            transform: translateY(-1px);
        }

        .ai-summary-title i {
            color: #667eea;
        }

        .ai-summary-note {
            font-size: 0.78rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }

        .ai-summary-body {
            font-size: 0.9rem;
            color: #334155;
            line-height: 1.65;
        }

        .ai-summary-body h2,
        .ai-summary-body h3,
        .ai-summary-body h4 {
            font-size: 0.93rem;
            margin: 0.45rem 0;
            font-weight: 700;
            color: #2d3748;
        }

        .ai-summary-body ul {
            margin: 0.35rem 0 0.55rem 1rem;
            padding-left: 0.5rem;
        }

        .ai-summary-loading {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: #64748b;
            font-size: 0.85rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #a0aec0;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e0;
        }
    </style>
@endpush

@section('content')
    <div class="suggestions-section">
        <div class="container" style="max-width: 1140px;">

            {{-- Page Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold mb-1" style="color: #2d3748;">
                        <i class="fas fa-lightbulb me-2" style="color: #667eea;"></i>{{ __('ui.suggestions.smart_suggestions') }}
                    </h3>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        {{ __('ui.suggestions.personalized_health_recommendations') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    @if(!auth()->check() || !auth()->user()->isAdmin())
                        <button onclick="toggleChatbot()" class="btn btn-sm btn-outline-primary" style="border-radius:10px;">
                            <i class="fas fa-user-md me-1"></i> {{ __('ui.suggestions.ask_mydoctor_ai') }}
                        </button>
                    @endif
                    <a href="{{ route('health') }}" class="btn btn-sm text-white"
                        style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px;">
                        <i class="fas fa-heartbeat me-1"></i> {{ __('ui.suggestions.health_dashboard') }}
                    </a>
                </div>
            </div>

            {{-- AI Summary (top section) --}}
            <div class="ai-summary-card" id="aiSummaryCard">
                <div class="ai-summary-title">
                    <span class="ai-summary-title-left">
                        <i class="fas fa-robot"></i>
                        <span>{{ __('ui.suggestions.health_summary_of_you') }}</span>
                    </span>
                    <button type="button" class="ai-summary-regenerate" id="aiSummaryRegenerateBtn">
                        <i class="fas fa-sync-alt me-1"></i>{{ __('ui.suggestions.regenerate') }}
                    </button>
                </div>
                <div class="ai-summary-note">
                    {{ __('ui.suggestions.generated_by_mydoctor_ai') }}
                </div>
                <div class="ai-summary-body" id="aiSummaryBody">
                    <span class="ai-summary-loading">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        {{ __('ui.suggestions.preparing_ai_summary') }}
                    </span>
                </div>
            </div>

            {{-- Filter Buttons --}}
            <div class="d-flex flex-wrap gap-2 mb-4">
                <button class="filter-btn active" onclick="filterSuggestions('all', this)">{{ __('ui.suggestions.all') }}</button>
                <button class="filter-btn" onclick="filterSuggestions('Metric Alert', this)">{{ __('ui.suggestions.metric_alerts') }}</button>
                <button class="filter-btn" onclick="filterSuggestions('Adherence', this)">{{ __('ui.suggestions.adherence') }}</button>
                <button class="filter-btn" onclick="filterSuggestions('Symptom', this)">{{ __('ui.suggestions.symptoms') }}</button>
                <button class="filter-btn" onclick="filterSuggestions('Condition', this)">{{ __('ui.suggestions.conditions') }}</button>
                <button class="filter-btn" onclick="filterSuggestions('Lifestyle', this)">{{ __('ui.suggestions.lifestyle') }}</button>
                <button class="filter-btn" onclick="filterSuggestions('Wellness', this)">{{ __('ui.suggestions.wellness') }}</button>
                <button class="filter-btn" onclick="filterSuggestions('Getting Started', this)">{{ __('ui.suggestions.getting_started') }}</button>
            </div>

            <div class="row">
                {{-- Main Suggestions Column --}}
                <div class="col-lg-8">
                    <div id="smartSuggestionsContainer">
                        @if (count($suggestions) === 0)
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p class="fw-semibold" style="font-size: 1.1rem; color: #2d3748;">{{ __('ui.suggestions.everything_looks_good') }}</p>
                                <p>{{ __('ui.suggestions.no_specific_suggestions') }}</p>
                            </div>
                        @else
                            @foreach ($suggestions as $s)
                                <div class="suggestion-card border-{{ $s['color'] }}" data-category="{{ $s['category'] }}">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="suggestion-icon bg-{{ $s['color'] }}">
                                            <i class="fas {{ $s['icon'] }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span
                                                    class="suggestion-category cat-{{ $s['color'] }}">{{ __("ui.suggestions.category_{$s['category']}") ?? $s['category'] }}</span>
                                            </div>
                                            <div class="suggestion-title">{{ $s['title'] }}</div>
                                            <p class="suggestion-message">{{ $s['message'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">

                    {{-- Adherence Summary --}}
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-pills me-2"></i>{{ __('ui.suggestions.adherence_30_days') }}</h6>
                        </div>
                        <div class="summary-card-body text-center">
                            @if ($adherenceRate !== null)
                                @php
                                    $ringColor =
                                        $adherenceRate >= 80
                                            ? '#38a169'
                                            : ($adherenceRate >= 50
                                                ? '#dd6b20'
                                                : '#e53e3e');
                                @endphp
                                <div class="position-relative d-inline-block mb-2">
                                    <canvas id="adherenceDonut" width="120" height="120"></canvas>
                                    <div
                                        style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:1.4rem;font-weight:700;color:{{ $ringColor }};">
                                        {{ $adherenceRate }}%
                                    </div>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ __('ui.suggestions.medicine_adherence_rate') }}</p>
                            @else
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ __('ui.suggestions.no_medicine_data_yet') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Active Conditions --}}
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-virus me-2"></i>{{ __('ui.suggestions.active_conditions') }}</h6>
                        </div>
                        <div class="summary-card-body">
                            @forelse($activeConditions as $cond)
                                <div class="summary-item">
                                    <div
                                        class="summary-dot {{ $cond->status === 'active' ? 'red' : ($cond->status === 'chronic' ? 'orange' : 'green') }}">
                                    </div>
                                    <div>
                                        @if($cond->disease)
                                            <a href="{{ route('public.disease.show', $cond->disease) }}" class="fw-semibold text-decoration-none">
                                                {{ $cond->disease->disease_name }}
                                            </a>
                                        @else
                                            <span class="fw-semibold">{{ __('ui.suggestions.unknown') }}</span>
                                        @endif
                                        <span class="text-muted" style="font-size: 0.75rem;"> &middot;
                                            {{ $cond->status_label }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ __('ui.suggestions.no_active_conditions') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Recent Symptoms --}}
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-notes-medical me-2"></i>{{ __('ui.suggestions.recent_symptoms_14d') }}</h6>
                        </div>
                        <div class="summary-card-body">
                            @forelse($recentSymptoms->take(5) as $sym)
                                <div class="summary-item">
                                    <div
                                        class="summary-dot {{ $sym->severity_level >= 7 ? 'red' : ($sym->severity_level >= 4 ? 'orange' : 'green') }}">
                                    </div>
                                    <div class="flex-grow-1">
                                        @if($sym->symptom)
                                            <a href="{{ route('public.symptoms.show', $sym->symptom) }}" class="text-decoration-none">
                                                {{ $sym->symptom_name }}
                                            </a>
                                        @else
                                            <span>{{ $sym->symptom_name }}</span>
                                        @endif
                                    </div>
                                    <span class="text-muted"
                                        style="font-size: 0.75rem;">{{ $sym->severity_level }}/10</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ __('ui.suggestions.no_symptoms_last_14_days') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Quick Links --}}
                    <div class="summary-card">
                        <div class="summary-card-header">
                            <h6><i class="fas fa-link me-2"></i>{{ __('ui.suggestions.quick_actions') }}</h6>
                        </div>
                        <div class="summary-card-body">
                            <a href="{{ route('health') }}#metrics" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-chart-line me-2"></i>{{ __('ui.suggestions.record_a_metric') }}
                            </a>
                            <a href="{{ route('health') }}#symptomsPane" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-notes-medical me-2"></i>{{ __('ui.suggestions.log_a_symptom') }}
                            </a>
                            <a href="{{ route('medicine.reminders') }}" class="d-block text-decoration-none mb-2"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-bell me-2"></i>{{ __('ui.suggestions.view_reminders') }}
                            </a>
                            <a href="{{ route('help') }}" class="d-block text-decoration-none"
                                style="font-size: 0.85rem; color: #667eea;">
                                <i class="fas fa-question-circle me-2"></i>{{ __('ui.suggestions.need_help') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const AI_SUMMARY_USER_ID = {{ (int) $user->id }};
        const AI_SUMMARY_USER_EMAIL = @json((string) $user->email);
        const WEATHER_ADVICE_TEXT = @json((string) ($weatherAdvice ?? ''));
        const WEATHER_ADVICE_LOCATION = @json((string) ($weatherAdviceLocation ?? ''));
        const AI_SUMMARY_CACHE_KEY = `mydoctor.ai_summary_cache.v1.user.${AI_SUMMARY_USER_ID}`;
        const AI_SUMMARY_LAST_ACTIVE_KEY = 'mydoctor.ai_last_active_user';

        document.addEventListener('DOMContentLoaded', function() {
            // Adherence Donut
            const canvas = document.getElementById('adherenceDonut');
            if (canvas) {
                const rate = {{ $adherenceRate ?? 0 }};
                const ringColor = rate >= 80 ? '#38a169' : (rate >= 50 ? '#dd6b20' : '#e53e3e');
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [rate, 100 - rate],
                            backgroundColor: [ringColor, '#edf2f7'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: false,
                        cutout: '78%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 1200
                        }
                    }
                });
            }

                const regenerateBtn = document.getElementById('aiSummaryRegenerateBtn');
            if (regenerateBtn) {
                regenerateBtn.addEventListener('click', function() {
                    loadAiSummary(true);
                });
            }

            loadLlmSmartSuggestions();

            // Reuse session cache on load/refresh; regenerate button forces fresh output.
            try {
                const lastActive = sessionStorage.getItem(AI_SUMMARY_LAST_ACTIVE_KEY);
                if (lastActive === null || String(lastActive) !== String(AI_SUMMARY_USER_ID)) {
                    loadAiSummary(true);
                } else {
                    loadAiSummary(false);
                }
            } catch (e) {
                loadAiSummary(false);
            }

            // Clear cached AI summary on logout forms so next login always regenerates.
            try {
                document.querySelectorAll('form[action="/logout"]').forEach(f => {
                    f.addEventListener('submit', () => {
                        try {
                            sessionStorage.removeItem(AI_SUMMARY_CACHE_KEY);
                            sessionStorage.removeItem(AI_SUMMARY_LAST_ACTIVE_KEY);
                        } catch (e) {}
                    }, { once: true });
                });
            } catch (e) {}
        });

        function filterSuggestions(category, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('.suggestion-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        async function loadAiSummary(forceRefresh = false) {
            const target = document.getElementById('aiSummaryBody');
            if (!target) return;

            if (!forceRefresh) {
                const cached = getCachedAiSummary();
                if (cached) {
                    target.innerHTML = renderChatbotMarkup(cached);
                    return;
                }
            }

            target.innerHTML = '<span class="ai-summary-loading"><i class="fas fa-circle-notch fa-spin"></i>{{ __("ui.suggestions.preparing_ai_summary") }}</span>';

            try {
                const response = await fetch('{{ route('chatbot.about_me') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();
                const reply = typeof data.reply === 'string' && data.reply.trim() !== ''
                    ? data.reply
                    : '{{ __("ui.suggestions.unable_to_generate_ai_summary") }}';

                let finalReply = reply;
                const llmSuggestions = await fetchLlmSuggestionsForSummary();
                if (Array.isArray(llmSuggestions) && llmSuggestions.length >= 4) {
                    finalReply = composeSummaryWithSuggestions(reply, llmSuggestions.slice(0, 5), WEATHER_ADVICE_TEXT, WEATHER_ADVICE_LOCATION);
                } else {
                    finalReply = composeSummaryWithSuggestions(reply, [], WEATHER_ADVICE_TEXT, WEATHER_ADVICE_LOCATION);
                }

                if (response.ok) {
                    cacheAiSummary(finalReply);
                    try {
                        sessionStorage.setItem(AI_SUMMARY_LAST_ACTIVE_KEY, String(AI_SUMMARY_USER_ID));
                    } catch (e) {}
                }

                target.innerHTML = renderChatbotMarkup(finalReply);
            } catch (error) {
                target.innerHTML = '<p class="mb-0">{{ __("ui.suggestions.unable_to_generate_ai_summary_try_again") }}</p>';
            }
        }

        async function fetchLlmSuggestionsForSummary() {
            try {
                const response = await fetch('{{ route('chatbot.smart_suggestions') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();
                if (!response.ok || !Array.isArray(data.suggestions) || data.suggestions.length < 4) {
                    return null;
                }

                return data.suggestions;
            } catch (error) {
                return null;
            }
        }

        function composeSummaryWithSuggestions(summaryText, suggestions, weatherAdvice, weatherLocation) {
            let content = (summaryText || '').trim();

            // Remove any existing smart suggestions section to avoid duplication.
            content = content.replace(/\n(?:\*\*)?(?:Smart Suggestions|স্মার্ট পরামর্শ)(?:\*\*)?\s*:?\s*[\s\S]*$/iu, '').trim();
            content = content.replace(/\n(?:\*\*)?(?:Advice based on weather|আবহাওয়া ভিত্তিক পরামর্শ)(?:\*\*)?\s*:?\s*[\s\S]*$/iu, '').trim();

            let appended = '';

            if (Array.isArray(suggestions) && suggestions.length > 0) {
                const bullets = suggestions.map((s) => {
                    const title = (s.title || 'পরামর্শ').trim();
                    const message = (s.message || '').trim();
                    return `- **${title}**: ${message}`;
                }).join('\n');
                appended += `\n\n**স্মার্ট পরামর্শ**\n${bullets}`;
            }

            const adviceRaw = (weatherAdvice || '').trim();
            if (adviceRaw !== '') {
                const adviceLines = adviceRaw
                    .split(/\r?\n/)
                    .map(l => l.trim())
                    .filter(Boolean)
                    .slice(0, 3);
                if (adviceLines.length > 0) {
                    const locationSuffix = (weatherLocation || '').trim() ? ` (${weatherLocation.trim()})` : '';
                    const adviceBullets = adviceLines
                        .map(line => line.replace(/^[\-*•\d\.\)\s]+/, '').trim())
                        .filter(Boolean)
                        .map(line => `- ${line}`)
                        .join('\n');
                    appended += `\n\n**আবহাওয়া ভিত্তিক পরামর্শ${locationSuffix}:**\n${adviceBullets}`;
                }
            }

            return `${content}${appended}`.trim();
        }

        async function loadLlmSmartSuggestions() {
            const container = document.getElementById('smartSuggestionsContainer');
            if (!container) return;

            try {
                const response = await fetch('{{ route('chatbot.smart_suggestions') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();
                if (!response.ok || !Array.isArray(data.suggestions) || data.suggestions.length < 4) {
                    return;
                }

                container.innerHTML = data.suggestions.slice(0, 5).map(renderSuggestionCard).join('');
            } catch (error) {
                // Keep server-rendered suggestions if LLM suggestions cannot be loaded.
            }
        }

        function renderSuggestionCard(item) {
            const color = normalizeSuggestionColor(item.color);
            const category = escapeHtml(item.category || 'Wellness');
            const icon = escapeHtml(item.icon || 'fa-lightbulb');
            const title = emphasizeLine(escapeHtml(item.title || 'Smart Suggestion'));
            const message = emphasizeLine(escapeHtml(item.message || ''));

            return `
                <div class="suggestion-card border-${color}" data-category="${escapeHtml(item.category || 'Wellness')}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="suggestion-icon bg-${color}">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="suggestion-category cat-${color}">${category}</span>
                            </div>
                            <div class="suggestion-title">${title}</div>
                            <p class="suggestion-message">${message}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        function normalizeSuggestionColor(color) {
            const allowed = ['danger', 'warning', 'info', 'success', 'primary'];
            return allowed.includes(color) ? color : 'primary';
        }

        function getCachedAiSummary() {
            try {
                const raw = sessionStorage.getItem(AI_SUMMARY_CACHE_KEY);
                if (!raw) return null;
                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed.reply !== 'string') return null;
                if ((parsed.userId ?? null) !== AI_SUMMARY_USER_ID) return null;
                return parsed.reply;
            } catch (e) {
                return null;
            }
        }

        function cacheAiSummary(reply) {
            try {
                sessionStorage.setItem(AI_SUMMARY_CACHE_KEY, JSON.stringify({
                    reply,
                    userId: AI_SUMMARY_USER_ID,
                    userEmail: AI_SUMMARY_USER_EMAIL,
                    savedAt: Date.now(),
                }));
            } catch (e) {
                // Ignore cache failures silently.
            }
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

            return html || '<p class="mb-0">{{ __("ui.suggestions.no_ai_summary_available") }}</p>';
        }

        function emphasizeLine(input) {
            let line = input.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // Make the full leading label before the first colon bold.
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

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
@endpush