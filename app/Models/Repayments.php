<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repayments extends Model
{
    protected $table = 'repayments';

    use HasFactory;

    public function loan()
    {
        return $this->belongsTo(Loans::class,'loan_id');
    }
}
