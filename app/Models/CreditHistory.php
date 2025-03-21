<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditHistory extends Model
{
    use HasFactory;

    // The name of the table
    protected $table = 'credit_history';

    // The attributes that are mass assignable
    protected $fillable = [
        'client_id',
        'score',
        'punctuality_score',
        'repayment_score',
        'history_score',
        'transaction_score',
        'notes',
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'client_id' => 'integer',
        'score' => 'integer',
        'punctuality_score' => 'float',
        'repayment_score' => 'float',
        'history_score' => 'float',
        'transaction_score' => 'float',
        'notes' => 'string',
    ];

    // Relationship with the Client model
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
