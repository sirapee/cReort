<?php

namespace App\Models;

use App\Traits\TraitUuid;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends EloquentUser implements JWTSubject
{
    use Notifiable;
    use softDeletes;
    use TraitUuid;

    public function uploads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Upload::class, 'user_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'last_name',
        'first_name',
        'permissions',
        'emp_id',
        'job_title',
        'department',
        'deleted',
        'created_by',
        'two_factor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $dates = ['deleted_at'];
    protected $loginNames = ['username', 'email'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function byEmail($email){
        return static::whereEmail($email)->first();
    }
    public static function byUsername($username){
        return self::where('username',$username)->first();
    }
    public static function userExists($username){
        return self::where('username',$username)->exists();
    }
    public static function byEmpId($empId){
        return self::where('emp_id',$empId)->first();
    }


}
