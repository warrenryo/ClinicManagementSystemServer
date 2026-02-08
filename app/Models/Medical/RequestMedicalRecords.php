<?php

namespace App\Models\Medical;

use App\Models\Auth\UserDetails;
use Illuminate\Database\Eloquent\Model;

class RequestMedicalRecords extends Model
{
    protected $table = 'request_medical_records';
    protected $fillable = [
        'user_details_id',
        'medical_records_id',
        'is_done'
    ];

    public function userDetails()
    {
        return $this->belongsTo(UserDetails::class, 'user_details_id', 'id');
    }

    public function medicalRecords()
    {
        return $this->belongsTo(MedicalRecords::class, 'medical_records_id', 'id');
    }
}
