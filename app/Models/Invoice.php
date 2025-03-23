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


    public function getInvoiceDetail()
    {
       
        $invoiceDetails = [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'invoice_link' => $this->invoice_link,
            'already_paid' => $this->already_paid,
            'rest_to_pay' => $this->rest_to_pay,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
        
        return $invoiceDetails;
    }
}