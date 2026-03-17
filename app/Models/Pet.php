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
        $vaxCount = $this->vaccinations()->count();
        $latest = $this->latestVaccination;

        // 2. Check if never vaccinated
        if ($vaxCount === 0) {
            return 'unvaccinated';
        }

        // 3. Overdue Check (If next due date has passed)
        if ($latest && $latest->next_due_date) {
            $dueDate = \Carbon\Carbon::parse($latest->next_due_date);

            if ($dueDate->isPast()) {
                return 'overdue';
            }

            // 4. Due Soon (Within 14 days - keeping your original 14-day window)
            if (\Carbon\Carbon::now()->diffInDays($dueDate, false) <= 14) {
                return 'due_soon';
            }
        }

        /** * 5. Logic for Partially vs Fully */
        if ($vaxCount < 2) {
            return 'partially_vaccinated';
        }

        return 'fully_vaccinated';
    }
    public function getVaxStatusAttribute()
    {
        // Get all vaccinations and the single latest one
        $vaccinations = $this->vaccinations;
        $latestVax = $vaccinations->sortByDesc('date_administered')->first();

        $vaxCount = $vaccinations->count();
        $vaccineNames = $vaccinations->pluck('vaccine_name')->map(fn($n) => strtolower($n));
        $hasRabies = $vaccineNames->contains(fn($value) => str_contains($value, 'rabies'));

        // Prepare default data
        $status = [
            'latest_vax' => $latestVax, // Attach the latest record here
            'label' => 'No Records',
            'class' => 'bg-secondary-subtle text-secondary border-secondary',
            'icon' => '<i data-lucide="alert-circle" style="width:16px;"></i>'
        ];

        if ($vaxCount === 0) {
            return (object) $status;
        }

        if ($hasRabies && $vaxCount > 1) {
            $status['label'] = 'Fully Vaccinated';
            $status['class'] = 'bg-success-subtle text-success border-success';
            $status['icon'] = '<i data-lucide="check-circle" style="width:16px;"></i>';
        } else {
            $status['label'] = 'Partially Vaccinated';
            $status['class'] = 'bg-info-subtle text-info border-info';
            $status['icon'] = '<i data-lucide="shield" style="width:16px;"></i>';
        }

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
}
