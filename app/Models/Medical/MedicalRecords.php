<?php

namespace App\Models\Medical;

use App\Models\Auth\UserDetails;
use App\Models\Scheduling\Appointment;
use Illuminate\Database\Eloquent\Model;

class MedicalRecords extends Model
{
    protected $fillable = [
        'appointment_id',
        'user_details_id',
        'temperature',
        'blood_pressure',
        'pulse_rate',
        'height',
        'weight',
        'symptoms',
        'action_taken',
        'remarks',
        'findings',
        'is_done'
    ];

    protected $casts = [
        'action_taken' => 'array',
        'amount' => 'decimal:2',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function userDetails()
    {
        return $this->belongsTo(UserDetails::class, 'user_details_id', 'id');
    }

    public function medicalItems()
    {
        return $this->hasMany(MedicalItems::class, 'medical_records_id', 'id');
    }
}
