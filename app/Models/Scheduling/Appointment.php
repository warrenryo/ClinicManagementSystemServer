<?php

namespace App\Models\Scheduling;

use App\Models\Auth\UserDetails;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    //
    protected $table = 'appointment';

    protected $fillable = [
        'user_details_id',
        'assigned_doctor_id',
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

    public function assignedDoctor()
    {
        return $this->belongsTo(UserDetails::class, 'assigned_doctor_id');
    }
}
