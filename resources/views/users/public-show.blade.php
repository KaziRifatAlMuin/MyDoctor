@extends('layouts.app')

@section('main_content_class', 'main-content main-content--wide users-main-content')

@section('content')
    <div style="background: linear-gradient(180deg, #eef4ff 0%, #f7f9ff 52%, #f8fbff 100%); min-height: 100vh; padding: 2rem 0 4rem;">
        <div class="container" style="max-width: 930px;">
            <div style="background: #fff; border-radius: 24px; box-shadow: 0 20px 55px rgba(10,50,120,0.12); overflow: hidden; border: 1px solid rgba(15,58,130,0.08);">
                <div style="padding: 2rem; background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 48%, #2b7de9 100%); color: white;">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            @if ($user->picture)
                                <img src="{{ asset('storage/' . $user->picture) }}" alt="{{ $user->name }}"
                                    style="width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.9); box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                            @else
                                <div style="width: 96px; height: 96px; border-radius: 50%; background: rgba(255,255,255,0.16); color: #fff; font-size: 1.9rem; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 4px solid rgba(255,255,255,0.9); box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <h1 style="margin: 0; font-weight: 800; font-size: 1.9rem; line-height: 1.2;">{{ $user->name }}</h1>
                                <p style="margin: 0.45rem 0 0; opacity: 0.94; font-size: 0.95rem;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Member since {{ optional($user->created_at)->format('M d, Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            @if (auth()->check() && auth()->id() !== $user->id)
                                <a href="{{ route('profile.mailbox.compose', ['to' => $user->id]) }}"
                                    style="display:inline-flex; align-items:center; gap:0.45rem; text-decoration:none; border-radius:12px; padding:0.65rem 1rem; background:#ffffff; color:#0b57d0; font-weight:700; border:1px solid #fff;">
                                    <i class="fas fa-paper-plane"></i> Send Mail
                                </a>
                            @elseif (!auth()->check())
                                <a href="{{ route('login') }}"
                                    style="display:inline-flex; align-items:center; gap:0.45rem; text-decoration:none; border-radius:12px; padding:0.65rem 1rem; background:#ffffff; color:#0b57d0; font-weight:700; border:1px solid #fff;">
                                    <i class="fas fa-sign-in-alt"></i> Login to Send Mail
                                </a>
                            @endif
                            <a href="{{ route('users.index') }}"
                                style="display:inline-flex; align-items:center; gap:0.45rem; text-decoration:none; border-radius:12px; padding:0.65rem 1rem; background:rgba(255,255,255,0.18); color:#fff; font-weight:700; border:1px solid rgba(255,255,255,0.42);">
                                <i class="fas fa-users"></i> Back to Users
                            </a>
                        </div>
                    </div>
                </div>

                <div style="padding: 1.4rem 1.4rem 1.6rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div style="background:#fff; border:1px solid #e7eefb; border-radius:14px; padding:1rem; height:100%; box-shadow:0 8px 18px rgba(10,55,130,0.05);">
                                <h5 style="margin:0; color:#0f2f6b; font-weight:700; font-size:1rem;">
                                    <i class="fas fa-id-card me-2 text-primary"></i>Personal Information
                                </h5>

                                @if ($user->show_personal_info)
                                    <div style="margin-top:0.85rem; display:grid; gap:0.55rem;">
                                        <div style="background:#f5f8ff; border:1px solid #dfe9fb; border-radius:10px; padding:0.65rem 0.75rem;">
                                            <small style="display:block; color:#7a8aa8;">Occupation</small>
                                            <strong style="color:#243654;">{{ $user->occupation ?: 'Not specified' }}</strong>
                                        </div>
                                        <div style="background:#fff6f6; border:1px solid #ffdede; border-radius:10px; padding:0.65rem 0.75rem;">
                                            <small style="display:block; color:#9f6b6b;">Blood Group</small>
                                            <strong style="color:#7e1f1f;">{{ $user->blood_group ?: 'Not specified' }}</strong>
                                        </div>
                                    </div>
                                @else
                                    <p style="margin:0.9rem 0 0; color:#6a7992; font-size:0.9rem; line-height:1.5;">
                                        This member has not granted permission to display personal information publicly.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div style="background:#fff; border:1px solid #e7eefb; border-radius:14px; padding:1rem; height:100%; box-shadow:0 8px 18px rgba(10,55,130,0.05);">
                                <h5 style="margin:0; color:#0f2f6b; font-weight:700; font-size:1rem;">
                                    <i class="fas fa-notes-medical me-2 text-success"></i>Disease Visibility
                                </h5>

                                @if ($user->show_diseases)
                                    @php
                                        $publicDiseases = $user->userDiseases ?? collect();
                                    @endphp

                                    @if ($publicDiseases->count())
                                        <div style="margin-top:0.85rem; display:flex; flex-wrap:wrap; gap:0.45rem;">
                                            @foreach ($publicDiseases as $item)
                                                <span style="display:inline-flex; align-items:center; gap:0.35rem; background:#eef8f2; color:#1f6f45; border:1px solid #cfe9da; border-radius:999px; padding:0.35rem 0.68rem; font-size:0.8rem; font-weight:700;">
                                                    <i class="fas fa-leaf" style="font-size:0.7rem;"></i>
                                                    {{ $item->disease->disease_name ?? 'Unknown Disease' }}
                                                    @if (!empty($item->status))
                                                        <span style="opacity:0.85; font-weight:600;">({{ $item->status_label }})</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p style="margin:0.9rem 0 0; color:#6a7992; font-size:0.9rem; line-height:1.5;">
                                            No diseases are listed in this public profile.
                                        </p>
                                    @endif
                                @else
                                    <p style="margin:0.9rem 0 0; color:#6a7992; font-size:0.9rem; line-height:1.5;">
                                        This member has not granted permission to display disease information publicly.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:1rem; background:#f7faff; border:1px dashed #ccdbf7; border-radius:12px; padding:0.82rem 0.95rem; color:#5b6e91; font-size:0.84rem;">
                        <i class="fas fa-shield-alt me-1 text-primary"></i>
                        Public profile data is controlled by member consent from Settings. Hidden sections remain private.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
