<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class EmployeeDetails extends Model
{
    protected $table = 'employee_details';
    protected $fillable = [
        'employee_no',
        'department',
        'position',
        'user_details_id'
    ];

    public function userDetails()
    {
        return $this->belongsTo(UserDetails::class, 'user_details_id', 'id');
    }
}
