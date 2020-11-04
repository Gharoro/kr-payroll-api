<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'gender',
        'age',
        'job_title',
        'department',
        'leave_status',
        'paye_status',
        'pension_status',
        'nsitf_status',
        'nhf_status',
        'company_id',
        'company_name',
        'company_address',
        'company_contact',
        'role',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function payrolls()
    {
        return $this->hasMany('App\Models\Payroll');
    }
    public function payslips()
    {
        return $this->hasMany('App\Models\Payslip');
    }
}
