<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commandes extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'client_id',
        'total_price',
        'payment_mode',
        'invoice_status',
        'tva',
        'mobile_number',
        'mobile_reference',
        'bank_name',
        'bank_reference',
        'card_type',
        'card_reference',
        'cash_reference'
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function commandeItems()
    {
        return $this->hasMany(CommandeItem::class, 'commande_id');
    }
    
    public function calculateTotalPrice()
    {
        $total = 0;
        foreach ($this->commandeItems as $item) {
            $total += $item->total_price;
        }
        return $total;
    }
}
