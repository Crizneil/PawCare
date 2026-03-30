<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Models\Appointment;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',      // The link to the owner
        'pet_id',
        'name',
        'species',
        'gender',
        'birthday',
        'breed',
        'owner',
        'owner_phone',
        'owner_gender',
        'house_no',
        'street',
        'barangay',
        'city',
        'province',
        'vaccine_type',
        'last_date',
        'next_date',
        'image_url',
        'status',
    ];

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'pet_id', 'id');
    }

    public function latestVaccination()
    {
        return $this->hasOne(Vaccination::class)->latestOfMany('date_administered');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => $this->owner ?? 'Guest',
        ]);
    }
    public function getCalculatedStatusAttribute()
    {
        $latest = $this->latestVaccination;

        // 1. Check if never vaccinated
        if (!$latest) {
            return 'unvaccinated';
        }

        // 2. Overdue Check (If next due date has passed)
        if ($latest->next_due_date) {
            $dueDate = \Carbon\Carbon::parse($latest->next_due_date);

            if ($dueDate->isPast()) {
                return 'overdue';
            }

            // 3. Due Soon (Within 14 days)
            if (\Carbon\Carbon::now()->diffInDays($dueDate, false) <= 14) {
                return 'due_soon';
            }
        }

        // 4. If they have any record and aren't overdue/due soon, they are "Vaccinated"
        return 'vaccinated';
    }
    public function getVaxStatusAttribute()
    {
        $calcStatus = $this->calculated_status;
        $latestVax = $this->latestVaccination;

        $status = [
            'latest_vax' => $latestVax,
            'label' => ucwords(str_replace('_', ' ', $calcStatus)),
            'class' => match($calcStatus) {
                'vaccinated'   => 'bg-success-subtle text-success border-success',
                'due_soon'     => 'bg-warning-subtle text-warning border-warning',
                'overdue'      => 'bg-danger-subtle text-danger border-danger',
                'unvaccinated' => 'bg-secondary-subtle text-secondary border-secondary',
                default        => 'bg-light text-dark'
            },
            'icon' => match($calcStatus) {
                'vaccinated' => '<i data-lucide="check-circle" style="width:16px;"></i>',
                'unvaccinated' => '<i data-lucide="alert-circle" style="width:16px;"></i>',
                default => '<i data-lucide="clock" style="width:16px;"></i>',
            }
        ];

        return (object) $status;
    }
    public function appointments(): HasMany
    {
        // This assumes your appointments table has a 'pet_id' column
        return $this->hasMany(Appointment::class);
    }

    /**
     * Scope a query to only include active pets.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Scope a query to exclude deceased pets.
     */
    public function scopeNotDeceased($query)
    {
        return $query->whereNotIn('status', ['DECEASED', 'INACTIVE']);
    }
    public function scopeWhereVaccinationStatus($query, $status)
    {
        return match($status) {
            'unvaccinated' => $query->whereDoesntHave('vaccinations'),

            'overdue' => $query->whereHas('latestVaccination', function($q) {
                $q->where('next_due_date', '<', now());
            }),

            'due_soon' => $query->whereHas('latestVaccination', function($q) {
                $q->whereBetween('next_due_date', [now(), now()->addDays(14)]);
            }),

            // Updated: Merged status logic for the filter
            'vaccinated' => $query->whereHas('latestVaccination', function($q) {
                $q->where(function($sub) {
                    $sub->where('next_due_date', '>', now()->addDays(14))
                        ->orWhereNull('next_due_date');
                });
            }),

            default => $query,
        };
    }
}
