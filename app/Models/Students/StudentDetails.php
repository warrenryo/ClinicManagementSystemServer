<?php

namespace App\Models\Students;

use App\Models\Auth\UserDetails;
use Illuminate\Database\Eloquent\Model;

class StudentDetails extends Model
{
    protected $table = 'student_details';
    protected $fillable = [
        'user_details_id',
        'student_number',
        'course',
        'year_level',
    ];

    public function UserDetails()
    {
        return $this->belongsTo(UserDetails::class, 'user_details_id', 'id');
    }
}
