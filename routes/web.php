<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\VaccineController;
use App\Http\Controllers\AdminReportController;

/*
|--------------------------------------------------------------------------
| Web Routes (PawCare)
|--------------------------------------------------------------------------
*/

// Public routes (views)
Route::view('/', 'index')->name('home');
Route::view('/about', 'about')->name('about');
Route::view('/blog', 'blog')->name('blog');
Route::view('/contact', 'contact')->name('contact');
Route::view('/faq', 'faq')->name('faq');
Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/service', 'service')->name('service');
Route::view('/service-single', 'service-single')->name('service.single');

Route::get('/test-validation-new', function() {
    $results = [];
    $pet = \App\Models\Pet::first();
    if (!$pet) return response()->json(['error' => 'No pet found at all']);
    $owner = \App\Models\User::find($pet->user_id);
    if (!$owner) return response()->json(['error' => 'No owner found for pet', 'pet' => $pet]);

    $validator = new class { use \App\Traits\AppointmentValidation; };

    $results['pet'] = ['id' => $pet->id, 'name' => $pet->name];

    // Clean up
    \App\Models\Appointment::where('pet_id', $pet->id)->where('appointment_date', '>=', now()->toDateString())->delete();

    $results['step1'] = $validator->checkServiceEligibility($pet->id, 'Anti-Rabies', now()->toDateString()) ?: "NULL - OK";

    \App\Models\Appointment::create([
        'user_id' => $owner->id,
        'pet_id' => $pet->id,
        'pet_name' => $pet->name,
        'species' => $pet->species,
        'service_type' => 'Anti-Rabies',
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '08:00',
        'status' => 'approved'
    ]);

    $results['step2'] = $validator->checkServiceEligibility($pet->id, 'Anti-Rabies', now()->toDateString()) ?: "NULL - FAILED";
    $results['step3'] = $validator->checkServiceEligibility($pet->id, '5in1', now()->toDateString()) ?: "NULL - OK";
    $results['step4'] = $validator->checkServiceEligibility($pet->id, 'Combination Vaccination (5-in-1)', now()->toDateString()) ?: "NULL - FAILED";

    return response()->json($results);
})->name('test-validation-new');


// Publicly accessible pet profile for QR scanning (No Login Required)
Route::get('/verify-pet/{pet_id}', [PetController::class, 'publicProfile'])->name('pet.public-profile');

// Keep old static HTML links working (redirects)
Route::redirect('/index.html', '/');
Route::redirect('/about.html', '/about');
Route::redirect('/blog.html', '/blog');
Route::redirect('/contact.html', '/contact');
Route::redirect('/faq.html', '/faq');
Route::redirect('/terms.html', '/terms');
Route::redirect('/privace.html', '/privacy');
Route::redirect('/service.html', '/service');
Route::redirect('/service-single.html', '/service-single');


// Authentication Routes
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update.action');

// Public join instructions page
Route::view('/join', 'join')->name('join');


// Protected Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard (Handled by AdminController to get the stats)
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Actions
    Route::post('/staff/store', [AdminController::class, 'storeStaff'])->name('staff.store');
    //Route::post('/search-pet', [AdminController::class, 'searchPet'])->name('search-pet');
    Route::post('/search-pet-action', [PetController::class, 'adminSearch'])->name('search-pet');
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::put('/staff/update/{id}', [AdminController::class, 'updateStaff'])->name('staff.update');
    Route::delete('/staff/delete/{id}', [AdminController::class, 'destroyStaff'])->name('staff.destroy');
    Route::get('/appointments', [AdminController::class, 'appointments'])->name('appointments');
    Route::post('/appointments/store', [AdminController::class, 'storeAppointment'])->name('appointments.store');
    Route::get('/appointments/create', [AdminController::class, 'createAppointment'])->name('appointments.create');
    Route::post('/appointments/{id}/approve', [AdminController::class, 'approve'])->name('appointments.approve');
    Route::post('/appointments/{id}/reject', [AdminController::class, 'reject'])->name('appointments.reject');
    Route::post('/appointments/{id}/cancel', [AdminController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{id}/reschedule', [AdminController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{id}/done', [AdminController::class, 'markDone'])->name('appointments.done');
    Route::get('/owners', [AdminController::class, 'owners'])->name('owners');
    Route::post('/owners/store', [AdminController::class, 'storeOwner'])->name('owners.store');
    Route::put('/pets/update/{id}', [AdminController::class, 'updatePet'])->name('pets.update');
    Route::post('/pets/store', [AdminController::class, 'storePet'])->name('pets.store');
    Route::delete('/pets/delete/{id}', [AdminController::class, 'destroyPet'])->name('pets.destroy');
    Route::get('/pet-records', [AdminController::class, 'petRecords'])->name('pet-records');
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
    Route::post('/logs/archive', [AdminController::class, 'archiveLogs'])->name('logs.archive');
    Route::post('/logs/{id}/restore', [AdminController::class, 'restoreLog'])->name('logs.restore');
    Route::post('/logs/restore-all', [AdminController::class, 'restoreAllLogs'])->name('logs.restore-all');
    Route::get('/vaccination-status', [VaccineController::class, 'status'])->name('vaccination-status');
    Route::put('/vaccination/update/{id}', [VaccineController::class, 'updateStatus'])->name('vaccinations.update');
    Route::post('/pets/{id}/vaccinate', [VaccineController::class, 'store'])->name('pets.vaccinate');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [AdminController::class, 'updateProfile'])->name('profile.update');
    Route::get('/owner-profile/{id}', [AdminController::class, 'showOwner'])->name('owner.profile');
    Route::post('/owner/{id}/create-account', [AdminController::class, 'createAccount'])->name('owner.createAccount');

    // Archive & Restore Routes
    Route::get('/archive', [AdminController::class, 'archive'])->name('archive');
    Route::post('/pets/{id}/restore-deceased', [AdminController::class, 'restoreDeceasedPet'])->name('pets.restore-deceased');
    Route::post('/pets/{id}/restore', [AdminController::class, 'restorePet'])->name('pets.restore');
    Route::post('/staff/{id}/restore', [AdminController::class, 'restoreStaff'])->name('staff.restore');
    Route::get('/owner/{id}/pets', [AdminController::class, 'getPetsByOwner'])->name('owner.pets');

    // Reports & Analytics Routes
    Route::get('/reports/appointments', [AdminReportController::class, 'appointmentReport'])->name('reports.appointments');
    Route::get('/reports/pet-medical-history/{id}', [AdminReportController::class, 'petMedicalHistory'])->name('reports.pet-medical-history');

    // Calendar API Endpoints
    Route::get('/api/appointments', [AdminController::class, 'getAppointmentsApi'])->name('api.appointments');
    Route::post('/api/appointments/{id}/drag-update', [AdminController::class, 'updateDragAndDrop'])->name('api.appointments.drag-update');
});

// Staff Specific Routes
Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [StaffController::class, 'profile'])->name('profile');

    // Appointments
    Route::get('/appointments', [StaffController::class, 'appointments'])->name('appointments');
    Route::post('/appointments/{id}/status', [StaffController::class, 'updateAppointmentStatus'])->name('appointments.update');
    Route::post('/appointments/store', [StaffController::class, 'storeAppointment'])->name('appointments.store');
    Route::post('/appointments/{id}/reschedule', [StaffController::class, 'reschedule'])->name('appointments.reschedule');
    Route::get('/appointments/booked-slots', [StaffController::class, 'getBookedSlots'])->name('appointments.slots');
    Route::get('/generate-report', [StaffController::class, 'generateReport'])->name('generate-report');

    // Pet Records & Vaccination
    Route::get('/pet-records', [StaffController::class, 'petRecords'])->name('pet-records');
    Route::get('/vaccination-status', [StaffController::class, 'vaccinationStatus'])->name('vaccination-status');
    Route::get('/vaccination-history', [StaffController::class, 'vaccinationHistory'])->name('vaccination-history');
    Route::post('/vaccination/store/{id}', [StaffController::class, 'updateVaccination'])->name('vaccination.store');

    // Owner Profile
    Route::post('/owners/store', [StaffController::class, 'storeOwner'])->name('owners.store');
    Route::get('/owner/{id}', [StaffController::class, 'ownerProfile'])->name('owner.profile');
    Route::get('/owner-profile/{id}', [StaffController::class, 'ownerProfile'])->name('pet-owners');
    Route::post('/owner/{id}/create-account', [StaffController::class, 'createAccount'])->name('owner.createAccount');
    Route::get('/owner/{id}/pets', [StaffController::class, 'getPetsByOwner'])->name('owner.pets');

    // Digital Card Requests
    Route::post('/request-card/{pet_id}', [StaffController::class, 'requestDigitalCard'])->name('request-card');

    // Archive & Restore (Staff Access)
    Route::post('/pets/{id}/restore-deceased', [AdminController::class, 'restoreDeceasedPet'])->name('pets.restore-deceased');
    Route::post('/pets/{id}/restore', [AdminController::class, 'restorePet'])->name('pets.restore');
    Route::delete('/pets/{id}/force-delete', [AdminController::class, 'forceDeletePet'])->name('pets.force-delete');

    // Reports
    Route::get('/reports/pet-medical-history/{id}', [AdminReportController::class, 'petMedicalHistory'])->name('reports.pet-medical-history');

});

// Pet Owner Routes
Route::prefix('owner')->name('pet-owner.')->middleware(['auth'])->group(function () {
    // This points to the dashboard view we worked on
    Route::get('/dashboard', [PetController::class, 'ownerDashboard'])->name('dashboard');

    // Profile page (route name: pet-owner.profile)
    Route::get('/profile', [PetController::class, 'profile'])->name('profile');
    Route::put('/profile/password', [PetController::class, 'updatePassword'])->name('password.update');
    Route::put('/profile/update', [PetController::class, 'updateProfile'])->name('profile.update');

    // Appointment booking page (route name: pet-owner.appointments)
    Route::get('/appointments', [PetController::class, 'appointments'])->name('appointments');
    Route::post('/appointments/book', [PetController::class, 'book'])->name('appointments.book');
    Route::patch('/appointments/{id}/cancel', [PetController::class, 'cancelAppointment'])->name('appointments.cancel');
    Route::get('/vaccination-history/{pet_id?}', [VaccineController::class, 'ownerHistory'])->name('vaccination-history');

    // Pet Records & Vaccination
    Route::get('/pet-records', [PetController::class, 'petRecords'])->name('pet-records');
    Route::put('/pet-records/{id}/update', [PetController::class, 'update'])->name('update-pet');
    Route::get('/pet-records/{id}/digital-id', [PetController::class, 'showDigitalId'])->name('digital-id');
    Route::post('/pet-records/store', [PetController::class, 'store'])->name('pets.store');

    // Reports
    Route::get('/reports/pet-medical-history/{id}', [AdminReportController::class, 'petMedicalHistory'])->name('reports.pet-medical-history');

    // Calendar API Endpoints
    Route::get('/api/appointments/available-slots', [PetController::class, 'getAvailableSlots'])->name('api.available-slots');
});
Route::get('/test-email', function () {

    Mail::raw('PawCare Email Test Successful!', function ($message) {
        $message->to('pawcarev@gmail.com')
            ->subject('PawCare Test Email');
    });

    return 'Email Sent!';
});

