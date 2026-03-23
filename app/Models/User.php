<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordEmail;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_image',
        'house_no',
        'street',
        'barangay',
        'city',
        'province',
        'phone',
        'gender',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Send the password reset notification securely using a custom Mailable.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->email)->send(new ResetPasswordEmail($token, $this->email));
    }
    public function getFullAddressAttribute()
    {
        return "{$this->house_no} - {$this->street}, {$this->barangay}, {$this->city}, {$this->province}";
    }
}
