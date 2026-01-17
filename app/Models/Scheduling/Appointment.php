<?php

namespace App\Models\Scheduling;

use App\Helpers\SearchableTrait;
use App\Models\Auth\UserDetails;
use App\Models\Medical\MedicalRecords;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use SearchableTrait;
    protected $table = 'appointment';

    protected $fillable = [
        'user_details_id',
        'appointment_date',
        'appointment_time',
        'reason',
        'cancel_resched_reason',
        'reschedule_reason',
        'other_reason',
        'status',
        'type',
        'qr_token',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function userDetails()
    {
        return $this->belongsTo(UserDetails::class, 'user_details_id');
    }

    public function appointmentDoctors()
    {
        return $this->hasMany(AppointmentDoctors::class, 'appointment_id', 'id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecords::class, 'appointment_id', 'id');
    }
}
