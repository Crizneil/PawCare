@extends('layout.admin')

@section('content')
<div class="container-fluid p-4">
    <div class="mb-4">
        <a href="{{ route('admin.appointments') }}" class="text-decoration-none text-muted small">
            <i data-lucide="arrow-left" class="size-14"></i> Back to Appointments
        </a>
        <h2 class="fw-bold mt-2">Schedule New Appointment</h2>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.appointments.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Select Pet Owner</label>
                        <select name="user_id" class="form-select rounded-pill">
                            <option selected disabled>Choose Owner...</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Pet Name</label>
                        <input type="text" name="pet_name" class="form-control rounded-pill" placeholder="Enter pet name">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Appointment Date</label>
                        <input type="date" name="appointment_date" class="form-control rounded-pill">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Service Type</label>
                        <select name="service_type" id="service_type" class="form-select bg-light border-0 px-3" required>
                            <option value="">Select Service</option>
                            <option value="Anti-Rabies">Anti-Rabies Vaccination</option>
                            <option value="5in1">5-in-1 Vaccination</option>
                            <option value="4in1">4-in-1 Vaccination (Cat)</option>
                            <option value="Deworming">Deworming</option>
                            <option value="Check-up">Check-up</option>
                            <option value="Kapon">Kapon</option>
                        </select>
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label fw-semibold">Notes / Symptoms</label>
                        <textarea name="notes" class="form-control rounded-4" rows="3" placeholder="Describe the reason for the visit..."></textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary rounded-pill px-5">Confirm Appointment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
