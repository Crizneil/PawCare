<div class="modal fade" id="viewPetModal{{ $pet->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark ps-2 pt-2">Pet Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-0 px-4 pb-4">
                <div class="row">

                    {{-- LEFT SIDE PET IMAGE --}}
                    <div class="col-md-4 text-center border-end">
                        <div class="mb-3 mt-3">

                            @if($pet->image_url)
                                <img src="{{ asset($pet->image_url) }}"
                                     class="rounded-4 shadow-sm border"
                                     style="width:100%; height:200px; object-fit:cover;">
                            @else
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center border"
                                     style="height:200px;">
                                    <i data-lucide="dog" class="text-muted" style="width:60px;height:60px;"></i>
                                </div>
                            @endif
                        </div>
                        <h4 class="fw-bold mb-0">{{ $pet->name }}</h4>
                        <span class="badge bg-light text-dark border mb-3">#{{ $pet->id }}</span>

                        {{-- VACCINATION STATUS --}}
                        <div class="text-start mt-3">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1">
                                Current Status
                            </label>
                            <span class="badge rounded-pill {{ $pet->vax_status->class }} w-100 py-2
                                d-flex align-items-center justify-content-center"
                                style="font-size:0.85rem;">
                                {!! $pet->vax_status->icon !!}
                                <span class="ms-2">{{ $pet->vax_status->label }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- RIGHT SIDE --}}
                    <div class="col-md-8 ps-md-4 mt-3 mt-md-0">
                        {{-- PET PROFILE --}}
                        <h6 class="fw-bold text-orange mb-3 mt-2">
                            <i data-lucide="info" class="me-2" style="width:16px;"></i>
                            Pet Profile
                        </h6>
                        <div class="row mb-4">
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Species</small>
                                <span class="fw-bold">{{ $pet->species ?? 'Dog' }}</span>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Breed</small>
                                <span class="fw-bold">{{ $pet->breed ?? 'Unknown' }}</span>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Gender</small>
                                <span class="fw-bold text-capitalize">{{ $pet->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">Date of Birth / Age</small>
                                <span class="fw-bold">
                                    {{ $pet->birthday ? \Carbon\Carbon::parse($pet->birthday)->format('M d, Y') : 'N/A' }}
                                    @if($pet->birthday)
                                        <small class="text-muted">
                                            ({{ \Carbon\Carbon::parse($pet->birthday)->age }} yrs)
                                        </small>
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- OWNER INFORMATION --}}
                        <h6 class="fw-bold text-orange mb-3">
                            <i data-lucide="user" class="me-2" style="width:16px;"></i>
                            Owner Information
                        </h6>
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <small class="text-muted d-block">Owner Name</small>
                                    <span class="fw-bold">
                                        {{-- If user exists and isn't a generic 'Guest' account, show user name; else show pet owner column --}}
                                        {{ $pet->user && $pet->user->name !== 'Guest' ? $pet->user->name : ($pet->owner ?? 'Guest') }}
                                    </span>

                                    <small class="text-muted d-block mt-2">Contact Number</small>
                                    <span class="fw-bold">
                                        {{ $pet->user->phone ?? ($pet->owner_phone ?? ($pet->phone ?? 'N/A')) }}
                                    </span>

                                    <small class="text-muted d-block mt-2">Address</small>
                                    <span class="fw-bold" style="font-size: 0.85rem;">
                                        @if($pet->house_no || $pet->street || $pet->barangay)
                                            {{ $pet->house_no }} {{ $pet->street }}, {{ $pet->barangay }}, {{ $pet->city }}, {{ $pet->province }}
                                        @elseif($pet->user && $pet->user->full_address)
                                            {{ $pet->user->full_address }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                <div class="col-4 text-end">
                                    {{-- Role-aware View Profile Button --}}
                                    @php
                                        $isUser = $pet->user_id;
                                        $rolePrefix = auth()->user()->role === 'admin' ? 'admin' : 'staff';

                                        // Determine the correct route name
                                        $routeName = $isUser ? $rolePrefix . '.owner.profile' : $rolePrefix . '.owner.profile';

                                        // Note: Check your route names in web.php.
                                        // If your staff route is 'staff.pet-owners', use this logic:
                                        $finalRoute = $isUser
                                            ? route($rolePrefix . (auth()->user()->role === 'staff' ? '.pet-owners' : '.owner.profile'), $pet->user_id)
                                            : route($rolePrefix . '.owner.profile', ['id' => $pet->id, 'type' => 'walkin']);
                                    @endphp

                                    <a href="{{ $finalRoute }}" class="btn btn-sm btn-orange rounded-pill px-3 shadow-sm">
                                        <i data-lucide="user" class="me-1" style="width:14px;"></i>
                                        View Owner Profile
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- VACCINATION HISTORY --}}
                        <h6 class="fw-bold text-orange mb-3">
                            <i data-lucide="shield-check" class="me-2" style="width:16px;"></i>
                            Vaccination History
                        </h6>
                        <div class="bg-light rounded-3 p-3 border">
                            <div class="vstack gap-2">
                                @forelse($pet->vaccinations->sortByDesc('date_administered') as $vax)
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <div>
                                        <span class="fw-bold d-block" style="font-size:0.9rem;">
                                            <i data-lucide="check-circle-2"
                                               class="text-success me-1"
                                               style="width:14px;"></i>
                                            {{ $vax->vaccine_name }}
                                        </span>
                                        <small class="text-muted">
                                            Date Administered:
                                            <span class="badge bg-white text-dark border rounded-pill px-3">
                                                {{ \Carbon\Carbon::parse($vax->date_administered)->format('M d, Y') }}
                                            </span>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block"
                                               style="font-size:0.7rem; font-weight:700;">
                                            NEXT DUE
                                        </small>
                                        <span class="badge bg-white text-dark border rounded-pill px-3">
                                            {{ \Carbon\Carbon::parse($vax->next_due_date)->format('m/d/Y') }}
                                        </span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-2">
                                    <p class="text-muted small mb-0">
                                        No vaccination records found.
                                    </p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
