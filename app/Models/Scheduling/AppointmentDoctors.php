<?php

namespace App\Models\Scheduling;

use App\Models\Auth\DoctorDetails;
use Illuminate\Database\Eloquent\Model;

class AppointmentDoctors extends Model
{
    protected $table = 'appointment_doctors';

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'reassign_reason',
        'status',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function doctorDetails()
    {
        return $this->belongsTo(DoctorDetails::class, 'doctor_id', 'id');
    }
}
