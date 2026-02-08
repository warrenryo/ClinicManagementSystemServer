<?php

namespace App\Models\Medical;

use Illuminate\Database\Eloquent\Model;

class MedicalCertificate extends Model
{
    protected $table = 'medical_certificate';
    protected $fillable = [
        'medical_records_id',
        'date_issued',
        'diagnosis',
        'chief_complaint',
        'physical_examination',
        'recommendations',
        'rest_period_from',
        'rest_period_to',
        'number_of_days',
        'fit_to_work',
        'needs_follow_up',
        'follow_up_date',
        'restrictions',
        'remarks',
        'doctor_signature',
    ];

    public function medicalRecords()
    {
        return $this->belongsTo(MedicalRecords::class, 'medical_records_id', 'id');
    }
}
