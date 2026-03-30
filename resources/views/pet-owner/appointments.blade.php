@extends('layout.admin')

@section('page_title', 'Appointments Dashboard')

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-0">Appointment Availability</h2>
                <p class="text-muted small">Select an available date on the calendar below to set an appointment.</p>

                {{-- Today's Quick Status Badge --}}
                @php
                    $statusText = 'Available Slot';
                    $statusColor = 'success';
                    $isDisabled = false;

                    if ($totalBookedToday >= $totalSlots) {
                        $statusText = 'Fully Booked';
                        $statusColor = 'danger';
                        $isDisabled = true;
                    }

                    elseif ($totalBookedToday >= ($totalSlots * 0.8)) { // Optional: show warning at 80% capacity
                        $statusText = 'Limited Slots';
                        $statusColor = 'warning';
                    }
                @endphp

                <div class="mt-2">
                    <span class="badge bg-{{ $statusColor }} px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-info-circle me-1"></i> Today's Clinic: <strong>{{ $statusText }}</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- Master Appointment Calendar --}}
        <div class="card shadow-sm border-0 mb-3 p-3 rounded-4">
            <div id="userCalendar"></div>
        </div>
        {{-- Calendar Legend --}}
        <div class="d-flex flex-wrap align-items-center gap-3 mb-4 small text-muted">
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #16f543;"></span>
                <span>All Slots Free (No Bookings)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #f4df68;"></span>
                <span>Limited Slots (Some Times Taken)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #fa4e45;"></span>
                <span>Fully Booked / Limit Reached</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #ececec;"></span>
                <span>Closed (Saturday - Sunday)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #f8f9fa;"></span>
                <span>Past Day</span>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h3 class="fw-bold mb-0">My Appointments</h3>
                <p class="text-muted small">Manage your scheduled healthcare visits here.</p>
            </div>
        </div>

        {{-- Appointment History Table --}}
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    {{-- Added custom-mobile-table class here --}}
                    <table class="table align-middle mb-0 custom-mobile-table">
                        <thead class="bg-light">
                            <tr class="text-secondary text-uppercase small fw-bold">
                                <th class="ps-4">Date & Time</th>
                                <th>Pet</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                <tr>
                                    {{-- Added data-label to all <td> tags --}}
                                    <td class="ps-4" data-label="Date & Time">
                                        <div class="fw-bold text-dark">
                                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td data-label="Pet">
                                        <div class="fw-bold text-dark">{{ $appointment->pet_name }}</div>
                                        <div class="small text-muted">{{ ucfirst($appointment->species) }}</div>
                                    </td>
                                    <td data-label="Service">
                                        <div class="badge bg-info-subtle text-info border border-info px-3 rounded-pill">
                                            {{ ucfirst($appointment->service_type) }}
                                        </div>
                                    </td>
                                    <td data-label="Status">
                                        @php
                                            $statusStyles = [
                                                'pending' => 'bg-warning-subtle text-warning border-warning',
                                                'approved' => 'bg-primary-subtle text-primary border-primary',
                                                'completed' => 'bg-success-subtle text-success border-success',
                                                'done' => 'bg-success-subtle text-success border-success',
                                                'missed' => 'bg-danger-subtle text-danger border-danger',
                                                'cancelled' => 'bg-secondary-subtle text-secondary border-secondary',
                                                'rejected' => 'bg-danger-subtle text-danger border-danger',
                                                'rescheduled' => 'bg-light text-dark',
                                            ];
                                            $currentStyle = $statusStyles[strtolower($appointment->status)] ?? 'bg-light text-dark';
                                        @endphp
                                        <span class="badge rounded-pill border px-3 fw-bold {{ $currentStyle }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                    {{-- Action label matches your CSS trigger --}}
                                    <td class="text-end pe-4" data-label="Actions">
                                        @if(in_array(strtolower($appointment->status), ['pending', 'rescheduled', 'approved']))
                                            {{-- This button now triggers the modal instead of submitting directly --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cancelModal{{ $appointment->id }}">
                                                Cancel
                                            </button>

                                        @elseif(strtolower($appointment->status) === 'completed' || strtolower($appointment->status) === 'done')
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm"
                                                    data-bs-toggle="modal" data-bs-target="#viewResultModal{{ $appointment->id }}">
                                                View
                                            </button>
                                        @else
                                            <span class="text-muted small">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <p class="text-muted mb-0">No appointments scheduled yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@foreach ($appointments as $appointment)
    {{-- Cancel Modal --}}
    @if(in_array(strtolower($appointment->status), ['pending', 'rescheduled', 'approved']))
        @include('partials._cancel_modal', ['appointment' => $appointment])
    @endif

    {{-- View Results Modal --}}
    @if(strtolower($appointment->status) === 'completed' || strtolower($appointment->status) === 'done')
        @include('partials._view_vaccination_details_modal', ['appointment' => $appointment])
    @endif
@endforeach

    {{-- Appointment Booking Modal --}}
    <div class="modal fade" id="setAppointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="fw-bold text-dark m-0">Set an Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pet-owner.appointments.book') }}" method="POST" id="setAppointmentForm">
                    @csrf
                    <input type="hidden" name="appointment_date" id="appointment_date_input">
                    <div class="modal-body p-4 text-start">

                        <div class="mb-4 text-center">
                            <label class="small fw-bold text-muted mb-1">Appointment Schedule</label>
                            <h5 class="fw-bold text-primary mb-0" id="appointmentScheduleDisplay">Select a Date</h5>
                            <div id="modalStatusBadge" class="mt-2 text-center"></div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6 border-end-md">
                                <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Owner Information</h6>
                                <div class="mb-3">
                                    <label class="small text-muted fw-bold mb-1">Name</label>
                                    <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted fw-bold mb-1">Contact #</label>
                                    <input type="text" class="form-control bg-light" value="{{ auth()->user()->phone ?? 'N/A' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-muted fw-bold mb-1">Address</label>
                                    <textarea class="form-control bg-light" name="address" readonly rows="2">{{ auth()->user()->full_address }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Pet Information</h6>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted mb-1">Select Pet(s) <span class="text-danger">*</span></label>
                                    <div id="pet_checkbox_container" class="p-2 border rounded bg-light" style="max-height: 150px; overflow-y: auto;">
                                        @foreach(\App\Models\Pet::where('user_id', auth()->id())->whereIn('status', ['ACTIVE', 'Verified'])->get() as $pet)
                                            <div class="form-check mb-2 pet-checkbox-item">
                                                <input class="form-check-input pet-id-checkbox" type="checkbox" name="pet_ids[]"
                                                    value="{{ $pet->id }}" id="pet_{{ $pet->id }}"
                                                    data-name="{{ $pet->name }}">
                                                <label class="form-check-label small" for="pet_{{ $pet->id }}">
                                                    {{ $pet->name }} <span class="text-muted">({{ ucfirst($pet->species) }})</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">You can select multiple pets for this visit.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted mb-1">Service(s) <span class="text-danger">*</span></label>
                                    {{-- Updated Service List --}}
                                    <select id="service_type_select" name="service_type" class="form-select bg-light" required>
                                        <option value="">Please Select Service(s) Here.</option>
                                        <option value="Anti-Rabies">Anti-Rabies Vaccination</option>
                                        <option value="5in1">5-in-1 Vaccination</option>
                                        <option value="4in1">4-in-1 Vaccination</option>
                                        <option value="Deworming">Deworming</option>
                                        <option value="Check-up">Check-up</option>
                                        <option value="Kapon">Kapon</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted mb-1">Time Slot <span class="text-danger">*</span></label>
                                    <select name="appointment_time" id="appointment_time_select" class="form-select bg-light" required>
                                        <option value="">Select Time</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-3 bg-light d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary px-4 fw-bold" id="saveAppointmentBtn"
                            {{ ($totalBookedToday >= $totalSlots || $userBookedToday >= 2) ? 'disabled' : '' }}>
                            Save Appointment
                        </button>
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
@push('scripts')
    <style>
        /* --- Modern FullCalendar Aesthetics --- */
        #userCalendar {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #ffffff;
            border-radius: 16px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: #f1f5f9;
        }

        .fc-scrollgrid {
            border: none !important;
        }

        .fc-day-today {
            background-color: #f8fafc !important;
        }

        .fc .fc-button-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: #fff;
            text-transform: capitalize;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
        }

        .fc .fc-button-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }

        .fc .fc-button-primary:not(:disabled):active,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        .border-end-md {
            border-right: 1px dashed #e2e8f0;
        }

        @media (max-width: 768px) {
            .border-end-md {
                border-right: none;
                border-bottom: 1px dashed #e2e8f0;
                padding-bottom: 1.5rem;
                margin-bottom: 1.5rem;
            }
        }

        /* Available / Booked styles for background cells */
        .fc-day-available {
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #f0fdf4 !important; /* Premium soft green */
        }

        .fc-day-available:hover {
            background-color: #dcfce7 !important;
            transform: scale(0.98);
            border-radius: 8px;
            box-shadow: inset 0 0 0 2px #4ade80;
        }

        .fc-day-limited {
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #fefce8 !important; /* Premium soft yellow */
        }

        .fc-day-limited:hover {
            background-color: #fef08a !important;
            transform: scale(0.98);
            border-radius: 8px;
            box-shadow: inset 0 0 0 2px #facc15;
        }

        .fc-day-full {
            background-color: #fef2f2 !important;
            cursor: not-allowed;
        }

        .fc-day-passed {
            background-color: #f8fafc !important;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .fc-day-closed {
            background-color: #f1f5f9 !important;
            cursor: not-allowed;
            opacity: 0.7;
            background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0,0,0,0.03) 10px, rgba(0,0,0,0.03) 20px);
        }

        .fc-daygrid-day-number {
            padding: 12px;
            font-weight: 700;
            font-size: 1.15em;
            color: #475569;
        }

        /* The block indicator for slots */
        .availability-indicator {
            background-color: #10b981; /* emerald-500 */
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 6px 12px;
            margin: 8px auto 0;
            font-size: 0.75rem;
            font-weight: 700;
            max-width: 85%;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .availability-full {
            background-color: #ef4444 !important; /* red-500 */
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }

        .availability-limited {
            background-color: #facc15 !important; /* yellow-400 */
            color: #854d0e !important; /* dark yellow text */
            box-shadow: 0 2px 4px rgba(250, 204, 21, 0.2);
        }

        .availability-free {
            background-color: #10b981 !important; /* emerald-500 */
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        /* Modal Enhancements */
        .modal-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }

        .form-select.bg-light,
        .form-control.bg-light {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }

        .form-select.bg-light:focus,
        .form-control.bg-light:focus {
            background-color: #fff !important;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
    </style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('userCalendar');
    const appointmentModal = new bootstrap.Modal(document.getElementById('setAppointmentModal'));
    const scheduleDisplay = document.getElementById('appointmentScheduleDisplay');
    const dateInput = document.getElementById('appointment_date_input');
    const timeSelect = document.getElementById('appointment_time_select');
    const serviceSelect = document.getElementById('service_type_select');
    const petSelect = document.querySelector('select[name="pet_id"]');

    let availabilityData = {};
    let ownerBookedDates = {};
    let isFetching = false;

    // SERVER-SIDE USER ID LOG
    console.log("[JS DEBUG] Logic is checking for User ID:", {{ auth()->id() ?? 'null' }});

    const PREDEFINED_TIMES = [
        { label: "8:00 AM - 8:30 AM", value: "08:00" }, { label: "8:30 AM - 9:00 AM", value: "08:30" },
        { label: "9:00 AM - 9:30 AM", value: "09:00" }, { label: "9:30 AM - 10:00 AM", value: "09:30" },
        { label: "10:00 AM - 10:30 AM", value: "10:00" }, { label: "10:30 AM - 11:00 AM", value: "10:30" },
        { label: "11:00 AM - 11:30 AM", value: "11:00" }, { label: "11:30 AM - 12:00 PM (Cut-off)", value: "11:30" },
        { label: "1:00 PM - 1:30 PM", value: "13:00" }, { label: "1:30 PM - 2:00 PM", value: "13:30" },
        { label: "2:00 PM - 2:30 PM", value: "14:00" }, { label: "2:30 PM - 3:00 PM", value: "14:30" },
        { label: "3:00 PM - 3:30 PM", value: "15:00" }, { label: "3:30 PM - 4:00 PM", value: "15:30" },
        { label: "4:00 PM - 4:30 PM", value: "16:00" }, { label: "4:30 PM - 5:00 PM", value: "16:30" }
    ];

    const formatLocalToISODate = (dateObj) => {
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    async function fetchAvailability(startStr, endStr) {
        if (isFetching) return;
        isFetching = true;
        try {
            const res = await fetch(`{{ route('pet-owner.api.available-slots') }}?start=${startStr}&end=${endStr}`);
            const data = await res.json();
            console.log("[API DEBUG] Raw response:", data);
            availabilityData = data.booked_slots || {};
            ownerBookedDates = data.owner_booked_dates || {};

            // Dynamically update the DOM since FullCalendar's dayCellDidMount ran before the API returned
            updateAllCalendarCells();
        } catch (err) {
            console.error("Failed to fetch slots", err);
        } finally {
            isFetching = false;
        }
    }

    function updatePetOptions(selectedDate) {
        let bookedForThisDate = ownerBookedDates[selectedDate] || [];
        if (typeof bookedForThisDate === 'object' && !Array.isArray(bookedForThisDate)) {
            bookedForThisDate = Object.values(bookedForThisDate);
        }
        const busyPetIds = bookedForThisDate.map(appt => String(appt.pet_id));

        const checkboxes = document.querySelectorAll('.pet-id-checkbox');
        checkboxes.forEach(checkbox => {
            const parentDiv = checkbox.closest('.pet-checkbox-item');
            if (busyPetIds.includes(checkbox.value)) {
                checkbox.disabled = true;
                checkbox.checked = false;
                parentDiv.style.opacity = '0.5';
                parentDiv.querySelector('label').innerText += " (Scheduled)";
            } else {
                checkbox.disabled = false;
                parentDiv.style.opacity = '1';
                // Restore original text logic if needed
            }
        });
    }

    function updateAllCalendarCells() {
        const dayCells = document.querySelectorAll('.fc-daygrid-day');
        dayCells.forEach(cell => {
            const dateStr = cell.getAttribute('data-date');
            if (!dateStr) return;

            const valParts = dateStr.split('-');
            const cellDate = new Date(valParts[0], valParts[1] - 1, valParts[2]);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);

            // Reset classes
            cell.classList.remove('fc-day-available', 'fc-day-limited', 'fc-day-full', 'fc-day-closed', 'fc-day-passed');
            const frame = cell.querySelector('.fc-daygrid-day-frame');
            if (!frame) return;

            const oldIndicator = frame.querySelector('.availability-indicator');
            if (oldIndicator) oldIndicator.remove();

            // 1. Past Dates
            if (cellDate < todayDate) {
                cell.classList.add('fc-day-passed');
                return;
            }

            // 2. Availability Logic
            const bookedTimes = availabilityData[dateStr] || [];
            const totalBookedCount = Array.isArray(bookedTimes) ? bookedTimes.length : Object.keys(bookedTimes).length;
            const dayOfWeek = cellDate.getDay();
            const isClosedDay = (dayOfWeek === 0 || dayOfWeek === 6);

            const indicator = document.createElement('div');
            indicator.className = 'availability-indicator';

            if (isClosedDay) {
                cell.classList.add('fc-day-closed');
                indicator.classList.add('availability-full');
                indicator.innerText = "Closed";
            }
            // Logic: Use 16 or 20 based on your clinic's max capacity
            // This count now includes "Pending" because of the Controller update
            else if (totalBookedCount >= 16) {
                cell.classList.add('fc-day-full');
                indicator.classList.add('availability-full');
                indicator.innerText = "Fully Booked";
            } else if (totalBookedCount >= 1) {
                cell.classList.add('fc-day-limited');
                indicator.classList.add('availability-limited');
                indicator.innerText = "Limited Slots";
            } else {
                cell.classList.add('fc-day-available');
                indicator.classList.add('availability-free');
                indicator.innerText = "Available Slot";
            }

            frame.appendChild(indicator);
        });
    }

    function updateAvailableTimes() {
        const selectedDate = dateInput.value;
        const selectedService = serviceSelect.value;
        const bookedTimes = availabilityData[selectedDate] || [];

        // Get current time for "Today" check
        const now = new Date();
        const todayStr = formatLocalToISODate(now);

        // Current time in minutes from midnight for easy comparison
        const currentTotalMinutes = (now.getHours() * 60) + now.getMinutes();

        timeSelect.innerHTML = '<option value="">Select Time</option>';

        PREDEFINED_TIMES.forEach((timeObj, index) => {
            let isUnavailable = bookedTimes.includes(timeObj.value);

            // 1. HIDE PASSED TIMES (Only if the selected date is Today)
            if (selectedDate === todayStr) {
                const [slotHour, slotMinute] = timeObj.value.split(':').map(Number);
                const slotTotalMinutes = (slotHour * 60) + slotMinute;

                // If the slot is in the past or starts right now, hide it
                if (slotTotalMinutes <= currentTotalMinutes) {
                    isUnavailable = true;
                }
            }

            // 2. KAPON LOGIC (Double slot blocking)
            if (selectedService === 'kapon') {
                const nextTimeObj = PREDEFINED_TIMES[index + 1];

                // Block if:
                // - Current slot is booked
                // - It's the morning cut-off (11:30) or end of day (16:30)
                // - There is no next slot available
                // - The next 30-minute slot is already booked by someone else
                if (isUnavailable ||
                    timeObj.value === "11:30" ||
                    timeObj.value === "16:30" ||
                    !nextTimeObj ||
                    bookedTimes.includes(nextTimeObj.value)) {
                    isUnavailable = true;
                }
            }

            // 3. RENDER OPTIONS
            if (!isUnavailable) {
                let opt = document.createElement('option');
                opt.value = timeObj.value;
                opt.innerText = timeObj.label;
                timeSelect.appendChild(opt);
            }
        });

        // 4. FEEDBACK IF EMPTY
        if (timeSelect.options.length <= 1) {
            let opt = document.createElement('option');
            opt.disabled = true;
            opt.innerText = "No available slots for this service";
            timeSelect.appendChild(opt);
        }
    }

    serviceSelect.addEventListener('change', updateAvailableTimes);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'today prev,next', center: 'title', right: '' },
        height: 'auto',
        datesSet: function (info) {
            fetchAvailability(info.startStr.split('T')[0], info.endStr.split('T')[0]);
        },
        dayCellDidMount: function (info) {
            const dateStr = formatLocalToISODate(info.date);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);

            info.el.classList.remove('fc-day-available', 'fc-day-limited', 'fc-day-full', 'fc-day-closed', 'fc-day-passed');
            const frame = info.el.querySelector('.fc-daygrid-day-frame');
            if (frame) {
                const oldIndicator = frame.querySelector('.availability-indicator');
                if (oldIndicator) oldIndicator.remove();
            }

            if (info.date < todayDate) {
                info.el.classList.add('fc-day-passed');
                return;
            }

            const bookedTimes = availabilityData[dateStr] || [];
            const totalBookedCount = Array.isArray(bookedTimes) ? bookedTimes.length : Object.keys(bookedTimes).length;
            const ownerAppointments = ownerBookedDates[dateStr] || [];
            const ownerCount = Array.isArray(ownerAppointments) ? ownerAppointments.length : Object.keys(ownerAppointments).length;

            // LOG RAW DATA FOR EVERY DATE WITH BOOKINGS
            if (ownerCount > 0 || totalBookedCount > 0) {
                console.log(`[Calendar Debug] Date: ${dateStr}, My Count: ${ownerCount}, Total Count: ${totalBookedCount}, Raw My Data:`, ownerAppointments);
            }

            const dayOfWeek = info.date.getDay();
            const isClosedDay = (dayOfWeek === 0 || dayOfWeek === 6);
            const indicator = document.createElement('div');
            indicator.className = 'availability-indicator';

            if (isClosedDay) {
                info.el.classList.add('fc-day-closed');
                indicator.classList.add('availability-full');
                indicator.innerText = "Closed";
            } else if (ownerCount >= 2) {
                info.el.classList.add('fc-day-full');
                indicator.classList.add('availability-full');
                indicator.innerText = "Limit Reached";
                console.log(`[Limit Reached] Date: ${dateStr}, My Bookings: ${ownerCount}`);
            } else if (totalBookedCount >= 20) {
                info.el.classList.add('fc-day-full');
                indicator.classList.add('availability-full');
                indicator.innerText = "Fully Booked";
            } else if (ownerCount === 1) {
                info.el.classList.add('fc-day-limited');
                indicator.classList.add('availability-limited');
                indicator.innerText = "Limited Slot";
                console.log(`[Limited Slot] Date: ${dateStr}, My Bookings: ${ownerCount}`);
            } else {
                info.el.classList.add('fc-day-available');
                indicator.classList.add('availability-free');
                indicator.innerText = "Available Slot";
            }

            if (frame) frame.appendChild(indicator);
        },
        dateClick: function (info) {
            const dateStr = info.dateStr;
            const dayOfWeek = info.date.getDay();
            const todayCheck = new Date();
            todayCheck.setHours(0, 0, 0, 0);

            if (info.date < todayCheck || [0, 6].includes(dayOfWeek)) return;

            const dayBookings = availabilityData[dateStr] || [];
            const totalBookedCount = dayBookings.length;

            // We no longer check ownerCount here so they can book more than 2
            if (totalBookedCount >= 16) {
                alert("Clinic is fully booked for this date.");
                return;
            }

            dateInput.value = dateStr;
            scheduleDisplay.innerText = info.date.toLocaleDateString('default', { year: 'numeric', month: 'long', day: 'numeric' });

            // This still prevents booking the SAME pet twice on one day
            updatePetOptions(dateStr);

            const statusBadge = document.getElementById('modalStatusBadge');
            if (statusBadge) {
                if (totalBookedCount >= 20) {
                    statusBadge.innerHTML = '<span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">Clinic Fully Booked</span>';
                } else if (totalBookedCount >= 1) {
                    statusBadge.innerHTML = '<span class="badge bg-warning px-3 py-2 rounded-pill shadow-sm text-dark">Limited Slots Available</span>';
                } else {
                    statusBadge.innerHTML = '<span class="badge bg-success px-3 py-2 rounded-pill shadow-sm">Available Slot</span>';
                }
            }

            const saveBtn = document.getElementById('saveAppointmentBtn');
            if (saveBtn) saveBtn.disabled = (totalBookedCount >= 20);

            appointmentModal.show();
        }
    });

    calendar.render();
});
</script>
@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('setAppointmentModal'));
            myModal.show();
        });
    </script>
@endif
@endpush
