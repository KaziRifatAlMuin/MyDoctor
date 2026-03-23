@extends('layouts.app')

@section('content')
    <div style="background: linear-gradient(180deg, #f0f2f8 0%, #e8ecf4 40%, #f5f7fb 100%); min-height: 100vh; padding: 2rem 0 4rem;">
        <div class="container" style="max-width: 1280px;">
            
            {{-- Header Hero Section --}}
            <div style="background: linear-gradient(135deg, #0b57d0 0%, #1a73e8 45%, #2b7de9 100%); background-size: 220% 220%; animation: heroGradient 8s ease infinite; border-radius: 28px; padding: 3.5rem 3rem; color: white; position: relative; overflow: hidden; margin-bottom: 3rem; box-shadow: 0 20px 60px rgba(11,87,208,0.28);">
                <div style="position: absolute; top: -60%; right: -15%; width: 500px; height: 500px; border-radius: 50%; background: rgba(255,255,255,0.06); pointer-events: none; animation: float 6s ease-in-out infinite;"></div>
                <div style="position: absolute; bottom: -40%; left: 10%; width: 350px; height: 350px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; animation: float 8s ease-in-out infinite reverse;"></div>
                
                <div style="position: relative; z-index: 2;">
                    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div style="width: 90px; height: 90px; border-radius: 20px; background: rgba(255,255,255,0.15); border: 3px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h1 style="font-size: 2.8rem; font-weight: 800; margin: 0; text-shadow: 0 2px 8px rgba(0,0,0,0.15); line-height: 1.1;">Community Members Directory</h1>
                            <p style="font-size: 1.05rem; margin: 0.5rem 0 0; opacity: 0.95;">Explore and connect with fellow health enthusiasts</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics Grid --}}
            <div class="row g-4 mb-4" style="margin-bottom: 3rem;">
                <div class="col-md-3 col-sm-6">
                    <div style="background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 20px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; border: 1px solid rgba(0,0,0,0.05);">
                        <div style="color: #667eea; font-size: 2.2rem; margin-bottom: 0.75rem;"><i class="fas fa-users"></i></div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; margin-bottom: 0.25rem;">{{ $totalUsers }}</div>
                        <div style="font-size: 0.9rem; color: #718096;">Total Members</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div style="background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 20px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; border: 1px solid rgba(0,0,0,0.05);">
                        <div style="color: #38a169; font-size: 2.2rem; margin-bottom: 0.75rem;"><i class="fas fa-user-check"></i></div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; margin-bottom: 0.25rem;">{{ $memberCount }}</div>
                        <div style="font-size: 0.9rem; color: #718096;">Active Members</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div style="background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 20px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; border: 1px solid rgba(0,0,0,0.05);">
                        <div style="color: #dd6b20; font-size: 2.2rem; margin-bottom: 0.75rem;"><i class="fas fa-crown"></i></div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; margin-bottom: 0.25rem;">{{ $adminCount }}</div>
                        <div style="font-size: 0.9rem; color: #718096;">Community Leaders</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div style="background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 20px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; border: 1px solid rgba(0,0,0,0.05);">
                        <div style="color: #3182ce; font-size: 2.2rem; margin-bottom: 0.75rem;"><i class="fas fa-user-plus"></i></div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; margin-bottom: 0.25rem;">{{ $recentUsers }}</div>
                        <div style="font-size: 0.9rem; color: #718096;">New This Week</div>
                    </div>
                </div>
            </div>

            {{-- Search & Filter Section --}}
            <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 20px rgba(0,0,0,0.06); margin-bottom: 3rem; border: 1px solid rgba(0,0,0,0.05);">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 0.5rem; font-size: 0.9rem;">Search Members</label>
                        <div style="position: relative;">
                            <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #667eea; font-size: 1rem;"></i>
                            <input type="text" id="searchInput" placeholder="Search by name..." value="{{ request('search') }}" style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.75rem; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s; box-sizing: border-box;">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 0.5rem; font-size: 0.9rem;">Member Type</label>
                        <select id="roleFilter" onchange="applyFilters()" style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s;">
                            <option value="">All Members</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Community Leaders</option>
                            <option value="member" {{ request('role') == 'member' ? 'selected' : '' }}>Members</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label style="display: block; font-weight: 600; color: #2d3748; margin-bottom: 0.5rem; font-size: 0.9rem;">Sort By</label>
                        <select id="sortBy" onchange="applyFilters()" style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s;">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Members Grid --}}
            @if($users->count() > 0)
                <div class="row g-4">
                    @foreach($users as $user)
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 20px rgba(0,0,0,0.06); transition: all 0.3s; border: 1px solid rgba(0,0,0,0.05); height: 100%; display: flex; flex-direction: column;">
                                {{-- Header Background --}}
                                <div style="height: 100px; background: linear-gradient(135deg, {{ $user->role === 'admin' ? '#0b57d0' : '#38a169' }} 0%, {{ $user->role === 'admin' ? '#1a73e8' : '#48bb78' }} 100%);"></div>

                                {{-- Card Body --}}
                                <div style="padding: 1.5rem; text-align: center; flex: 1; display: flex; flex-direction: column;">
                                    {{-- Avatar --}}
                                    <div style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, {{ $user->role === 'admin' ? '#0b57d0' : '#3182ce' }} 0%, {{ $user->role === 'admin' ? '#1a73e8' : '#2c5aa0' }} 100%); border: 4px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; color: white; margin: -2.5rem auto 1rem;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>

                                    {{-- Name --}}
                                    <h5 style="font-size: 1.1rem; font-weight: 700; color: #2d3748; margin: 0.5rem 0;">{{ $user->name }}</h5>

                                    {{-- Role Badge --}}
                                    <div style="margin: 0.75rem 0;">
                                        @if ($user->role === 'admin')
                                            <span style="display: inline-block; background: linear-gradient(135deg, #0b57d0, #1a73e8); color: white; padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                                <i class="fas fa-crown me-1"></i>Community Leader
                                            </span>
                                        @else
                                            <span style="display: inline-block; background: rgba(56, 161, 105, 0.1); color: #38a169; padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                                <i class="fas fa-user me-1"></i>Member
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Email Status --}}
                                    <div style="margin: 0.75rem 0;">
                                        @if ($user->email_verified_at)
                                            <span style="display: inline-block; background: rgba(56, 161, 105, 0.1); color: #38a169; padding: 0.35rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                                <i class="fas fa-check-circle me-1"></i>Verified
                                            </span>
                                        @else
                                            <span style="display: inline-block; background: rgba(237, 137, 54, 0.1); color: #dd6b20; padding: 0.35rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Join Info --}}
                                    <div style="font-size: 0.85rem; color: #718096; margin-top: auto; padding-top: 1rem; border-top: 1px solid #e0e0e0; margin-bottom: 1rem;">
                                        <i class="fas fa-calendar-alt me-1"></i> Joined {{ $user->created_at->format('M d, Y') }}
                                    </div>

                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <a href="{{ route('users.show', $user) }}" style="width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 0.4rem; text-decoration: none; padding: 0.45rem 0.5rem; border-radius: 8px; border: 1px solid #d2e3fc; background: #edf3fe; color: #0b57d0; font-size: 0.86rem; font-weight: 700; height: 36px;">
                                            <i class="fas fa-user"></i>
                                            <span style="display:inline-block; margin-left:6px;">Profile</span>
                                        </a>
                                        @if (auth()->id() !== $user->id)
                                            <a href="{{ route('profile.mailbox.compose', ['to' => $user->id]) }}" style="width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 0.4rem; text-decoration: none; padding: 0.45rem 0.5rem; border-radius: 8px; border: 1px solid #0b57d0; background: #0b57d0; color: #fff; font-size: 0.86rem; font-weight: 700; height: 36px;">
                                                <i class="fas fa-paper-plane"></i>
                                                <span style="display:inline-block; margin-left:6px;">Send Mail</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($users->hasPages())
                    <div style="display: flex; justify-content: center; margin-top: 4rem;">
                        <nav style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
                            {{-- Previous --}}
                            @if ($users->onFirstPage())
                                <span style="padding: 0.75rem 1rem; border-radius: 8px; color: #cbd5e0; cursor: not-allowed; background: #f7fafc;">
                                    <i class="fas fa-chevron-left me-1"></i> Previous
                                </span>
                            @else
                                <a href="{{ $users->appends(request()->query())->previousPageUrl() }}" style="padding: 0.75rem 1rem; border-radius: 8px; color: #667eea; font-weight: 600; background: rgba(102,126,234,0.1); text-decoration: none; transition: all 0.3s;">
                                    <i class="fas fa-chevron-left me-1"></i> Previous
                                </a>
                            @endif

                            {{-- Numbers --}}
                            @php $start = max(1, $users->currentPage() - 2); $end = min($users->lastPage(), $users->currentPage() + 2); @endphp
                            @if ($start > 1)
                                <a href="{{ $users->appends(request()->query())->url(1) }}" style="padding: 0.75rem 1rem; border-radius: 8px; color: #667eea; font-weight: 600; background: rgba(102,126,234,0.1); text-decoration: none; transition: all 0.3s;">1</a>
                                @if ($start > 2) <span style="padding: 0.75rem 1rem; color: #cbd5e0;">...</span> @endif
                            @endif
                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $users->currentPage())
                                    <span style="padding: 0.75rem 1rem; border-radius: 8px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; font-weight: 700; box-shadow: 0 4px 8px rgba(102,126,234,0.3);">{{ $i }}</span>
                                @else
                                    <a href="{{ $users->appends(request()->query())->url($i) }}" style="padding: 0.75rem 1rem; border-radius: 8px; color: #667eea; font-weight: 600; background: rgba(102,126,234,0.1); text-decoration: none; transition: all 0.3s;">{{ $i }}</a>
                                @endif
                            @endfor
                            @if ($end < $users->lastPage())
                                @if ($end < $users->lastPage() - 1) <span style="padding: 0.75rem 1rem; color: #cbd5e0;">...</span> @endif
                                <a href="{{ $users->appends(request()->query())->url($users->lastPage()) }}" style="padding: 0.75rem 1rem; border-radius: 8px; color: #667eea; font-weight: 600; background: rgba(102,126,234,0.1); text-decoration: none; transition: all 0.3s;">{{ $users->lastPage() }}</a>
                            @endif

                            {{-- Next --}}
                            @if ($users->hasMorePages())
                                <a href="{{ $users->appends(request()->query())->nextPageUrl() }}" style="padding: 0.75rem 1rem; border-radius: 8px; color: #667eea; font-weight: 600; background: rgba(102,126,234,0.1); text-decoration: none; transition: all 0.3s;">
                                    Next <i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            @else
                                <span style="padding: 0.75rem 1rem; border-radius: 8px; color: #cbd5e0; cursor: not-allowed; background: #f7fafc;">
                                    Next <i class="fas fa-chevron-right ms-1"></i>
                                </span>
                            @endif
                        </nav>
                    </div>
                    <div style="text-align: center; margin-top: 1.5rem; color: #718096; font-size: 0.9rem;">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} members
                    </div>
                @endif
            @else
                <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 16px;">
                    <i class="fas fa-users" style="font-size: 3rem; color: #cbd5e0; margin-bottom: 1rem; display: block;"></i>
                    <h4 style="color: #718096; margin-bottom: 0.5rem;">No members found</h4>
                    <p style="color: #a0aec0; margin-bottom: 1.5rem;">Try adjusting your search criteria or filters</p>
                    <button onclick="clearFilters()" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-redo me-1"></i> Clear Filters
                    </button>
                </div>
            @endif

        </div>
    </div>

    <style>
        @keyframes heroGradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
        [style*="border-radius: 16px"]:hover {
            box-shadow: 0 12px 32px rgba(0,0,0,0.12) !important;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1) !important;
        }
    </style>

    <script>
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const role = document.getElementById('roleFilter').value;
            const sort = document.getElementById('sortBy').value;
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (role) params.set('role', role);
            if (sort) params.set('sort', sort);
            window.location.href = '{{ route('users.index') }}?' + params.toString();
        }
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyFilters();
        });
        function clearFilters() {
            window.location.href = '{{ route('users.index') }}';
        }
    </script>
@endsection
