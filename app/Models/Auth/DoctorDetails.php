<?php

namespace App\Models\Auth;

use App\Helpers\SearchableTrait;
use App\Models\Medical\MedicalRecords;
use App\Models\Scheduling\AppointmentDoctors;
use Illuminate\Database\Eloquent\Model;

class DoctorDetails extends Model
{
    use SearchableTrait;
    protected $table = 'doctor_details';
    protected $fillable = [
        'specialization',
        'license_number',
        'user_details_id'
    ];

    public function userDetails()
    {
        return $this->belongsTo(UserDetails::class, 'user_details_id', 'id');
    }

    public function appointmentDoctors()
    {
        return $this->hasMany(AppointmentDoctors::class, 'doctor_id', 'id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecords::class, 'doctor_id', 'id');
    }
}
