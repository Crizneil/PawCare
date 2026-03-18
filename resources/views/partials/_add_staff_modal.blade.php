<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addStaffModalLabel">Create Staff Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.staff.store') }}" method="POST" id="staffRegistrationForm">
                    @csrf
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Full Name</label>
                        <input type="text" name="name" class="form-control rounded-pill bg-light border-0 px-3" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-pill bg-light border-0 px-3" placeholder="staff@pawcare.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Password</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="password"
                                   class="form-control rounded-pill bg-light border-0 ps-3 pe-5"
                                   placeholder="Min 8 characters" minlength="8" required>
                            <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent pe-3 text-muted"
                                    style="z-index: 10;" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="pw-error" class="text-danger small ms-3 mt-1" style="display:none;">Password must be at least 8 characters.</div>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold text-muted">Confirm Password</label>
                        <div class="position-relative">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control rounded-pill bg-light border-0 ps-3 pe-5"
                                   placeholder="Confirm password" minlength="8" required>
                            <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent pe-3 text-muted"
                                    style="z-index: 10;" onclick="togglePassword('password_confirmation', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light w-50 rounded-pill fw-bold py-2" data-bs-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary w-50 rounded-pill fw-bold py-2">CREATE ACCOUNT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Password Visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');

    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Form Submission Validation
document.getElementById('staffRegistrationForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password');
    const confirm = document.getElementById('password_confirmation');
    const errorDiv = document.getElementById('pw-error');

    // Reset error state
    errorDiv.style.display = 'none';

    // Check 1: Length check
    if (password.value.length < 8) {
        e.preventDefault(); // Stop form submission
        errorDiv.style.display = 'block';
        password.focus();
        return;
    }

    // Check 2: Match check
    if (password.value !== confirm.value) {
        e.preventDefault();
        alert("Passwords do not match!");
        confirm.focus();
        return;
    }
});
</script>
