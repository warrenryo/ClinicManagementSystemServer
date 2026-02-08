<?php

namespace App\Models\Auth;

use App\Models\Medical\MedicalRecords;
use App\Models\Scheduling\Appointment;
use App\Models\Students\StudentDetails;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $table = 'user_details';
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'phone',
        'address',
        'postal_code',
        'city',
        'profile_img',
        'user_id',
        'positions_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function studentDetails()
    {
        return $this->hasOne(StudentDetails::class, 'user_details_id', 'id');
    }

    public function doctorDetails()
    {
        return $this->hasOne(DoctorDetails::class, 'user_details_id', 'id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecords::class, 'user_details_id', 'id');
    }

    public function employeeDetails()
    {
        return $this->hasOne(EmployeeDetails::class, 'user_details_id', 'id');
    }

    public function appointment()
    {
        return $this->hasMany(Appointment::class, 'user_details_id', 'id');
    }
}
