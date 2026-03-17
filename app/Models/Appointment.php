<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This "unlocks" the fields so the Controller can save them.
     */
    protected $fillable = [
        'user_id',
        'pet_id',
        'pet_name',
        'species',
        'gender',
        'appointment_date',
        'appointment_time',
        'service_type',
        'status',
        'checked_in_at',
        'late_at',
        'notes',
        'rejection_reason',
        'administered_by',
        'batch_no',
        'next_due_date',
        'vaccine_name',
        'address'
    ];

    /**
     * Relationship: An appointment belongs to a User (Owner).
     * This allows you to do $appointment->user->name in your table.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'name' => 'Guest/Walk-in', // This prevents errors when calling $apt->user->name
            'phone' => 'N/A'
        ]);
    }
    public function vaccination()
    {
        // This links the appointment to the specific medical record created when done
        return $this->hasOne(Vaccination::class, 'appointment_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}
