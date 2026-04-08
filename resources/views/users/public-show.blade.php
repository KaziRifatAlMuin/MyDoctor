@extends('layouts.app')

@section('main_content_class', 'main-content main-content--wide users-main-content')

@section('content')
    <div
        style="background: linear-gradient(180deg, #f0f2f8 0%, #e8ecf4 40%, #f5f7fb 100%); min-height: 100vh; padding: 2rem 0 4rem;">
        <div class="container" style="max-width: 760px;">
            <div
                style="background: white; border-radius: 22px; box-shadow: 0 20px 55px rgba(0,0,0,0.12); overflow: hidden; border: 1px solid rgba(0,0,0,0.06);">
                <div style="height: 140px; background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 45%, #2b7de9 100%);">
                </div>

                <div style="padding: 0 2rem 2rem; text-align: center; margin-top: -52px;">
                    @if ($user->picture)
                        <img src="{{ asset('storage/' . $user->picture) }}" alt="{{ $user->name }}"
                            style="width: 104px; height: 104px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                    @else
                        <div
                            style="width: 104px; height: 104px; border-radius: 50%; margin: 0 auto; background: linear-gradient(135deg, #0b57d0, #1a73e8); color: #fff; font-size: 2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 4px solid #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <h1 style="margin: 1rem 0 0.4rem; color: #1f2937; font-weight: 800; font-size: 1.8rem;">
                        {{ $user->name }}</h1>
                    <p style="margin: 0; color: #6b7280; font-size: 0.96rem;">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Joined {{ optional($user->created_at)->format('M d, Y') }}
                    </p>

                    <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 0.6rem; flex-wrap: wrap;">
                        @if (auth()->check() && auth()->id() !== $user->id)
                            <a href="{{ route('profile.mailbox.compose', ['to' => $user->id]) }}"
                                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; text-decoration: none; border-radius: 10px; padding: 0.68rem 1.1rem; font-weight: 700; color: #fff; background: #0b57d0; border: 1px solid #0b57d0;">
                                <i class="fas fa-paper-plane"></i>
                                Send Mail
                            </a>
                        @elseif (!auth()->check())
                            <a href="{{ route('login') }}"
                                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; text-decoration: none; border-radius: 10px; padding: 0.68rem 1.1rem; font-weight: 700; color: #fff; background: #0b57d0; border: 1px solid #0b57d0;">
                                <i class="fas fa-sign-in-alt"></i>
                                Login to Send Mail
                            </a>
                        @endif

                        <a href="{{ route('users.index') }}"
                            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; text-decoration: none; border-radius: 10px; padding: 0.68rem 1.1rem; font-weight: 700; color: #0b57d0; background: #edf3fe; border: 1px solid #d2e3fc;">
                            <i class="fas fa-users"></i>
                            Back to Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
