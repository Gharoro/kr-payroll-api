<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;
       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'month',
        'basic_salary',
        'net_salary',
        'housing',
        'transportation',
        'paye_amount',
        'pension_amount',
        'nsitf_amount',
        'nhf_amount',
        'total_deductions',   
        'total_earnings',   
    ];

      
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
