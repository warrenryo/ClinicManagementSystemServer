<?php

namespace App\Models\Medical;

use App\Helpers\SearchableTrait;
use App\Models\Auth\DoctorDetails;
use App\Models\Auth\UserDetails;
use App\Models\Scheduling\Appointment;
use App\Models\Scheduling\Walkin;
use Illuminate\Database\Eloquent\Model;

class MedicalRecords extends Model
{
    use SearchableTrait;
    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'walkin_id',
        'user_details_id',
        'reference_no',
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

    public function doctorDetails()
    {
        return $this->belongsTo(DoctorDetails::class, 'doctor_id', 'id');
    }

    public function medicalItems()
    {
        return $this->hasMany(MedicalItems::class, 'medical_records_id', 'id');
    }

    public function walkin()
    {
        return $this->belongsTo(Walkin::class, 'walkin_id', 'id');
    }

    public function requestMedRecord()
    {
        return $this->hasOne(RequestMedicalRecords::class, 'medical_records_id', 'id');
    }
}
