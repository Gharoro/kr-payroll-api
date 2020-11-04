<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'basic_salary',
        'net_salary',
        'housing',
        'transportation',
        'paye',
        'pension',
        'nsitf',
        'nhf',
        'deductions',   
    ];

      
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
