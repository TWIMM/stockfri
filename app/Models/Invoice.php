<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'invoice_number',
        'invoice_date',
        'invoice_link',
        'already_paid',
        'rest_to_pay',
        'total_amount',
        'status'
    ];

    /**
     * Get the order that owns the invoice.
     */
    public function commande()
    {
        return $this->belongsTo(Commandes::class, 'commande_id');
    }
}