<div class="modal fade" id="viewResultModal{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-orange text-white border-0 py-3" style="border-radius: 1.5rem;">
                <div class="d-flex align-items-center">
                    <i data-lucide="clipboard-list" class="text-orange me-2"></i>
                    <h5 class="modal-title fw-bold text-orange mb-0">Service Summary</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-6">
                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Pet Name</label>
                        <p class="fw-bold text-dark mb-0">{{ $appointment->pet_name }}</p>
                    </div>
                    <div class="col-6 text-end">
                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Owner</label>
                        <p class="fw-bold text-dark mb-0">{{ $appointment->user->name ?? 'Guest Client' }}</p>
                    </div>

                    <div class="col-12"><hr class="my-0 opacity-25"></div>

                    {{-- Service Row --}}
                    <div class="col-6">
                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Service Type</label>
                        <p class="fw-bold text-dark mb-0" style="color: #fd7e14;">
                            {{ ucfirst($appointment->service_type) }}
                        </p>
                    </div>

                    <div class="col-6 text-end">
                        @if(in_array(strtolower($appointment->service_type), ['vaccination', 'deworming']))
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Vaccine/Treatment</label>
                            <span class="fw-bold text-dark mb-0" style="color: #fd7e14;">
                                {{ $appointment->vaccine_name ?? ($appointment->pet->latestVaccination->vaccine_name ?? 'Not Specified') }}
                            </span>
                        @else
                            {{-- Replaced Status with Administered By for Checkups --}}
                            <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Administered By</label>
                            <p class="fw-bold mb-0 text-dark">{{ $appointment->administered_by ?? 'Clinic Staff' }}</p>
                        @endif
                    </div>

                    {{-- Records Row --}}
                    @if(in_array(strtolower($appointment->service_type), ['vaccination', 'deworming']))
                    <div class="col-6">
                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Batch Number</label>
                        <p class="fw-medium mb-0 text-dark">
                            {{ $appointment->batch_no ?? ($appointment->pet->latestVaccination->batch_no ?? '---') }}
                        </p>
                        <small class="text-muted" style="font-size: 0.65rem;">Inventory Record</small>
                    </div>
                    @endif

                    <div class="{{ in_array(strtolower($appointment->service_type), ['vaccination', 'deworming']) ? 'col-6 text-end' : 'col-12 text-center' }}">
                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Date Administered</label>
                        <p class="fw-bold mb-0 text-dark">
                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                        </p>
                    </div>

                    {{-- For Vaccination: Administered By stays at bottom if needed, or hidden if already shown above --}}
                    @if(in_array(strtolower($appointment->service_type), ['vaccination', 'deworming']))
                    <div class="col-12 text-center mt-2">
                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Administered By</label>
                        <p class="fw-bold mb-0 text-dark"><i data-lucide="user" class="me-1" style="width: 14px;"></i> {{ $appointment->administered_by ?? 'Clinic Staff' }}</p>
                    </div>
                    @endif

                    {{-- Next Schedule --}}
                    <div class="col-12 mt-4">
                        <div class="border rounded-4 p-3 d-flex justify-content-between align-items-center" style="background-color: #fffaf5; border-color: #ffe5d0 !important;">
                            <div>
                                <label class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.65rem;">Follow-Up Date</label>
                                <h5 class="fw-bold mb-0 text-dark" style="color: #fd7e14;">
                                    @php
                                        $nextDate = $appointment->next_due_date ?? ($appointment->pet->latestVaccination->next_due_date ?? null);
                                    @endphp
                                    {{ $nextDate ? \Carbon\Carbon::parse($nextDate)->format('F d, Y') : 'No Follow-up Set' }}
                                </h5>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success rounded-pill px-3 py-2 text-white shadow-sm" style="background-color: #fd7e14;">
                                    <i></i> COMPLETED
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light w-100 rounded-pill fw-bold border text-muted" data-bs-dismiss="modal">Close Summary</button>
            </div>
        </div>
    </div>
</div>
