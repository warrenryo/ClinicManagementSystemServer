<?php

namespace App\Models\Scheduling;

use App\Models\Medical\MedicalRecords;
use Illuminate\Database\Eloquent\Model;

class Walkin extends Model
{
    protected $table = 'walkin';
    protected $fillable = [
        'first_name',
        'last_name',
        'birthdate'
    ];

    public function appointment()
    {
        return $this->hasMany(Appointment::class, 'walkin_id', 'id');
    }

    public function medicalRecords()
    {
        return $this->hasOne(MedicalRecords::class, 'walkin_id', 'id');
    }
}
