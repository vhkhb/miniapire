<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loans extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'amount', 'term', 'repay_count', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function repayments()
    {
        return $this->hasMany(Repayments::class, 'loan_id');
    }

    public function repaymentCount()
    {
        return $this->repayments()->count();
    }
}
