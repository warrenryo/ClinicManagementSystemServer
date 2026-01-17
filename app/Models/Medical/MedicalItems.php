<?php

namespace App\Models\Medical;

use App\Models\Inventory\Products;
use Illuminate\Database\Eloquent\Model;

class MedicalItems extends Model
{
    protected $table = 'medical_items';
    protected $fillable = ['medical_records_id', 'products_id', 'quantity'];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecords::class, 'medical_records_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'products_id', 'id');
    }
}
