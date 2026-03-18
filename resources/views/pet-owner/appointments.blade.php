@extends('layout.admin')

@section('page_title', 'Appointments Dashboard')

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-0">Appointment Availability</h2>
                <p class="text-muted small">Select an available date on the calendar below to set an appointment.</p>
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
                <span>Closed (Friday - Sunday)</span>
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
                                        @if(in_array(strtolower($appointment->status), ['pending', 'rescheduled']))
                                            <form action="{{ route('pet-owner.appointments.cancel', $appointment->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3">Cancel</button>
                                            </form>

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

                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-1">Appointment Schedule</label>
                            <h5 class="fw-bold text-primary mb-0" id="appointmentScheduleDisplay">Select a Date</h5>
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
                                    <label class="small fw-bold text-muted mb-1">Pet Name <span class="text-danger">*</span></label>
                                    <select name="pet_id" class="form-select bg-light" required>
                                        <option value="">Please Select Pet here.</option>
                                        @foreach(\App\Models\Pet::where('user_id', auth()->id())->whereIn('status', ['ACTIVE', 'Verified'])->get() as $pet)
                                            <option value="{{ $pet->id }}">{{ $pet->name }} ({{ ucfirst($pet->species) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted mb-1">Service(s) <span class="text-danger">*</span></label>
                                    {{-- Updated Service List --}}
                                    <select id="service_type_select" name="service_type" class="form-select bg-light" required>
                                        <option value="">Please Select Service(s) Here.</option>
                                        <option value="vaccination">Vaccination</option>
                                        <option value="deworming">Deworming</option>
                                        <option value="check-up">Check-up</option>
                                        <option value="kapon">Kapon</option>
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
                        <button type="submit" class="btn btn-primary px-4 fw-bold" id="saveAppointmentBtn">Save Appointment</button>
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
            background-color: #ef4444; /* red-500 */
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
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
document.addEventListener('DOMContentLoaded', async function () {
    var calendarEl = document.getElementById('userCalendar');
    var appointmentModal = new bootstrap.Modal(document.getElementById('setAppointmentModal'));
    var scheduleDisplay = document.getElementById('appointmentScheduleDisplay');
    var dateInput = document.getElementById('appointment_date_input');
    var timeSelect = document.getElementById('appointment_time_select');
    var serviceSelect = document.getElementById('service_type_select');
    var petSelect = document.querySelector('select[name="pet_id"]'); // Added Pet Select reference

    let availabilityData = {};
    let ownerBookedDates = {};
    let isFetching = false;

    const PREDEFINED_TIMES = [
        { label: "8:00 AM - 8:30 AM", value: "08:00" },
        { label: "8:30 AM - 9:00 AM", value: "08:30" },
        { label: "9:00 AM - 9:30 AM", value: "09:00" },
        { label: "9:30 AM - 10:00 AM", value: "09:30" },
        { label: "10:00 AM - 10:30 AM", value: "10:00" },
        { label: "10:30 AM - 11:00 AM", value: "10:30" },
        { label: "11:00 AM - 11:30 AM", value: "11:00" },
        { label: "11:30 AM - 12:00 PM (Morning Cut-off)", value: "11:30" },
        { label: "1:00 PM - 1:30 PM", value: "13:00" },
        { label: "1:30 PM - 2:00 PM", value: "13:30" },
        { label: "2:00 PM - 2:30 PM", value: "14:00" },
        { label: "2:30 PM - 3:00 PM", value: "14:30" },
        { label: "3:00 PM - 3:30 PM", value: "15:00" },
        { label: "3:30 PM - 4:00 PM", value: "15:30" },
        { label: "4:00 PM - 4:30 PM", value: "16:00" },
        { label: "4:30 PM - 5:00 PM", value: "16:30" }
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

            // Ensure we handle the object correctly
            availabilityData = data.booked_slots || {};
            ownerBookedDates = data.owner_booked_dates || {};

            // CRITICAL: This triggers dayCellDidMount to run again with the fresh data
            calendar.render();
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

        Array.from(petSelect.options).forEach(option => {
            if (option.value === "") return;
            const originalText = option.getAttribute('data-original-text') || option.text;
            if (!option.hasAttribute('data-original-text')) option.setAttribute('data-original-text', originalText);

            if (busyPetIds.includes(option.value)) {
                option.disabled = true;
                option.text = originalText + " (Already Scheduled)";
            } else {
                option.disabled = false;
                option.text = originalText;
            }
        });
    }

    function updateAvailableTimes() {
        const selectedDate = dateInput.value;
        const selectedService = serviceSelect.value;
        const bookedTimes = availabilityData[selectedDate] || [];

        timeSelect.innerHTML = '<option value="">Select Time</option>';
        PREDEFINED_TIMES.forEach((timeObj, index) => {
            let opt = document.createElement('option');
            let isUnavailable = bookedTimes.includes(timeObj.value);

            if (selectedService === 'kapon') {
                const nextTimeObj = PREDEFINED_TIMES[index + 1];
                if (timeObj.value === "11:30" || timeObj.value === "16:30" || !nextTimeObj || bookedTimes.includes(nextTimeObj.value)) {
                    isUnavailable = true;
                }
            }

            if (isUnavailable) {
                opt.disabled = true;
                opt.innerText = `${timeObj.label} (Unavailable)`;
            } else {
                opt.value = timeObj.value;
                opt.innerText = timeObj.label;
            }
            timeSelect.appendChild(opt);
        });
    }

    serviceSelect.addEventListener('change', updateAvailableTimes);

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'today prev,next', center: 'title', right: '' },
        height: 'auto',
        datesSet: function (info) {
            fetchAvailability(info.startStr.split('T')[0], info.endStr.split('T')[0]);
        },
        dayCellDidMount: function (info) {
            const dateStr = formatLocalToISODate(info.date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // 1. CLEAR existing states to prevent "ghost" indicators
            info.el.classList.remove('fc-day-available', 'fc-day-limited', 'fc-day-full', 'fc-day-closed', 'fc-day-passed');
            const frame = info.el.querySelector('.fc-daygrid-day-frame');
            if (frame) {
                const oldIndicator = frame.querySelector('.availability-indicator');
                if (oldIndicator) oldIndicator.remove();
            }

            if (info.date < today) {
                info.el.classList.add('fc-day-passed');
                return;
            }

            // 2. NORMALIZE the data (Handle Laravel's object vs array)
            const bookedTimes = availabilityData[dateStr] || [];
            const totalBookedCount = Array.isArray(bookedTimes) ? bookedTimes.length : Object.keys(bookedTimes).length;

            let ownerAppointments = ownerBookedDates[dateStr] || [];
            // Convert {} to [] if Laravel sent an object
            if (ownerAppointments && typeof ownerAppointments === 'object' && !Array.isArray(ownerAppointments)) {
                ownerAppointments = Object.values(ownerAppointments);
            }
            const ownerCount = ownerAppointments.length;

            const dayOfWeek = info.date.getDay();
            const isClosedDay = (dayOfWeek === 0 || dayOfWeek === 5 || dayOfWeek === 6);

            // 3. CREATE indicator
            const indicator = document.createElement('div');
            indicator.className = 'availability-indicator';

            if (isClosedDay) {
                info.el.classList.add('fc-day-closed');
                indicator.classList.add('availability-full');
                indicator.innerText = "Closed";
            }
            // This is the specific logic you updated in PHP
            else if (ownerCount >= 2) {
                info.el.classList.add('fc-day-full');
                indicator.classList.add('availability-full');
                indicator.innerText = "Limit Reached";
            }
            else if (totalBookedCount >= 16) {
                info.el.classList.add('fc-day-full');
                indicator.classList.add('availability-full');
                indicator.innerText = "Fully Booked";
            }
            else if (ownerCount > 0 || totalBookedCount > 0) {
                info.el.classList.add('fc-day-limited');
                indicator.innerText = "Limited Slots";
            }
            else {
                info.el.classList.add('fc-day-available');
                indicator.innerText = "All Slots Free";
            }

            if (frame) {
                frame.appendChild(indicator);
            }
        },
        dateClick: function (info) {
            const dateStr = info.dateStr;
            const dayOfWeek = info.date.getDay();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (info.date < today || [0, 5, 6].includes(dayOfWeek)) return;

            let userBookingsToday = ownerBookedDates[dateStr] || [];
            if (typeof userBookingsToday === 'object' && !Array.isArray(userBookingsToday)) userBookingsToday = Object.values(userBookingsToday);

            if (userBookingsToday.length >= 2) {
                alert("Maximum limit reached for this day.");
                return;
            }

            dateInput.value = dateStr;
            scheduleDisplay.innerText = info.date.toLocaleDateString('default', { year: 'numeric', month: 'long', day: 'numeric' });
            updatePetOptions(dateStr);
            serviceSelect.value = "";
            timeSelect.innerHTML = '<option value="">Please select a service first</option>';
            appointmentModal.show();
        }
    });

    calendar.render();
});
</script>
@endpush
