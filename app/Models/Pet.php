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
            $dueDate = Carbon::parse($latest->next_due_date);

            if ($dueDate->isPast()) {
                return 'overdue';
            }

            // 4. Due Soon (Within 14 days - keeping your original 14-day window)
            if (Carbon::now()->diffInDays($dueDate, false) <= 14) {
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

        $slug = $this->calculated_status;
        $vaccineName = $latestVax ? " ({$latestVax->vaccine_name})" : "";

        switch ($slug) {
            case 'fully_vaccinated':
                $status['label'] = 'Fully Vaccinated' . $vaccineName;
                $status['class'] = 'bg-success-subtle text-success border-success';
                $status['icon'] = '<i data-lucide="check-circle" style="width:16px;"></i>';
                break;
            case 'partially_vaccinated':
                $status['label'] = 'Partially Vaccinated' . $vaccineName;
                $status['class'] = 'bg-info-subtle text-info border-info';
                $status['icon'] = '<i data-lucide="shield" style="width:16px;"></i>';
                break;
            case 'due_soon':
                $status['label'] = 'Booster Due Soon' . $vaccineName;
                $status['class'] = 'bg-warning-subtle text-warning border-warning';
                $status['icon'] = '<i data-lucide="clock" style="width:16px;"></i>';
                break;
            case 'overdue':
                $status['label'] = 'Vaccination Overdue' . $vaccineName;
                $status['class'] = 'bg-danger-subtle text-danger border-danger';
                $status['icon'] = '<i data-lucide="alert-triangle" style="width:16px;"></i>';
                break;
            case 'unvaccinated':
                $status['label'] = 'Unvaccinated';
                $status['class'] = 'bg-secondary-subtle text-secondary border-secondary';
                $status['icon'] = '<i data-lucide="alert-circle" style="width:16px;"></i>';
                break;
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
    public function scopeWhereVaccinationStatus($query, $status)
    {
        return match($status) {
            'unvaccinated' => $query->whereDoesntHave('vaccinations'),

            'overdue' => $query->whereHas('latestVaccination', function($q) {
                $q->where('next_due_date', '<', now());
            }),

            'due_soon' => $query->whereHas('latestVaccination', function($q) {
                $q->whereBetween('next_due_date', [now(), now()->addDays(30)]);
            }),

            'partially_vaccinated' => $query->has('vaccinations', '>=', 1)
                                        ->has('vaccinations', '<', 2),

            'fully_vaccinated' => $query->whereHas('vaccinations', null, '>=', 2)
                ->whereHas('latestVaccination', function($q) {
                    $q->where('next_due_date', '>', now()->addDays(30));
                }),

            default => $query,
        };
    }
}
