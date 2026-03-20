@php
    /** @var \App\Models\User $user */
    $user = Auth::user();
@endphp
<header class="bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center sticky-top"
        style="z-index: 1045;">
    {{-- LEFT SIDE --}}
    <div class="d-flex align-items-center">

        {{-- Mobile Toggle --}}
        {{-- Sidebar Toggle (Global) --}}
        <button class="btn btn-light border me-3"
                id="sidebarToggle"
                type="button">
            <i data-lucide="menu"></i>
        </button>

        {{-- Page Title Desktop --}}
        <h5 class="fw-bold text-dark mb-0 d-none d-md-block">
            @yield('page_title', 'Dashboard')
        </h5>

        {{-- Logo Mobile --}}
        <img src="{{ asset('assets/images/newlogo.png') }}"
             style="height: 30px;"
             class="d-md-none">
    </div>

    {{-- RIGHT SIDE --}}
    <div class="d-flex align-items-center">

        {{-- Notification Bell --}}
        <div class="dropdown me-3">
            <button class="btn btn-light border-0 position-relative p-2 rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown">
                <i data-lucide="bell" class="size-20 text-muted"></i>
                @if(($upcomingCount ?? 0) > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; padding: 4px 6px;">
                        {{ $upcomingCount }}
                    </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-3 mt-2" style="width: 300px; border-radius: 12px;">
                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    Notifications
                    @if(($upcomingCount ?? 0) > 0)
                        <span class="badge bg-danger-subtle text-danger small">{{ $upcomingCount }} New</span>
                    @endif
                </h6>
                <div class="notification-list">
                    @if(($upcomingCount ?? 0) > 0)
                        <div class="d-flex align-items-center p-2 bg-light rounded-3 mb-2">
                            <div class="bg-primary-subtle text-primary p-2 rounded-circle me-3">
                                <i data-lucide="calendar" class="size-16"></i>
                            </div>
                            <div>
                                <div class="small fw-bold text-dark">Upcoming Vaccinations</div>
                                <div class="text-muted" style="font-size: 11px;">You have {{ $upcomingCount }} pets reaching their deadline soon.</div>
                            </div>
                        </div>
                        <a href="{{ strtolower($user->role) === 'owner' ? route('pet-owner.dashboard') : route('admin.dashboard') }}" 
                           class="btn btn-sm btn-primary w-100 rounded-pill mt-2">View Details</a>
                    @else
                        <div class="text-center py-3 text-muted">
                            <i data-lucide="bell-off" class="d-block mb-2 opacity-50 mx-auto"></i>
                            <span class="small">All clear! No pending alerts.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="text-end me-3 d-none d-sm-block">
            <p class="fw-bold mb-0 small text-dark" style="line-height:1.2;">
                {{ $user->name }}
            </p>
            <p class="text-primary mb-0 small text-uppercase fw-bold"
               style="font-size:10px; letter-spacing:0.5px;">
                {{ $user->role }}
            </p>
        </div>

        <a href="{{
            strtolower($user->role) === 'admin' ? route('admin.profile') :
            (strtolower($user->role) === 'staff' ? route('staff.profile') : route('pet-owner.profile'))
        }}" class="position-relative">

            <img src="{{ $user->profile_image ? '/storage/' . $user->profile_image : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=d35400&color=fff' }}"
                 class="rounded-circle border shadow-sm"
                 style="width:42px; height:42px; object-fit:cover;"
                 alt="Profile Picture"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=d35400&color=fff'">

            <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle"
                  style="width:12px; height:12px;">
            </span>
        </a>

    </div>
</header>
