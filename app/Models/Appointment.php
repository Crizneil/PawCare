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

    /**
     * Check if the appointment is considered "Late"
     */
    public function isLate()
    {
        // Only Pending or Approved appointments can be late
        if (!in_array(strtolower($this->status), ['pending', 'approved'])) {
            return false;
        }

        $now = \Carbon\Carbon::now('Asia/Manila');
        $appointmentDateTime = \Carbon\Carbon::parse($this->appointment_date . ' ' . $this->appointment_time, 'Asia/Manila');

        // Late if planned time + 30 minutes grace period is in the past
        return $now->gt($appointmentDateTime->addMinutes(30));
    }

    public function scopeWhereLate($query)
    {
        $now = \Carbon\Carbon::now('Asia/Manila')->subMinutes(30);
        return $query->whereIn('status', ['pending', 'approved', 'Approved'])
            ->where(function($q) use ($now) {
                $q->where('appointment_date', '<', $now->toDateString())
                  ->orWhere(function($q2) use ($now) {
                      $q2->where('appointment_date', $now->toDateString())
                         ->where('appointment_time', '<', $now->toTimeString());
                  });
            });
    }
}
