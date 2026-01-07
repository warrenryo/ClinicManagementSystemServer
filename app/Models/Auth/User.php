<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRoles;
use App\Helpers\SearchableTrait;
use Illuminate\Support\Str;
use App\Models\Auth\UserDetails;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SearchableTrait;
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'role',
        'is_active',
        'user_access',
        'access_token',
        'refresh_token',
        'refresh_token_createdAt',
        'refresh_token_expiresAt',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRoles::class,
            'user_access' => 'array'
        ];
    }

    public function userDetails()
    {
        return $this->hasOne(UserDetails::class, 'user_id', 'id');
    }
}
