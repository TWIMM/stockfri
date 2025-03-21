<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commandes extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'client_id',
        'user_id',
        'total_price',
        'payment_mode',
        'magasin_id',
        'invoice_status',
        'tva',
        'mobile_number',
        'mobile_reference',
        'bank_name',
        'already_paid',
        'rest_to_pay',
        'bank_reference',
        'card_type',
        'card_reference',
        'cash_reference'
    ];
    
    public function client()
    {
        return $this->belongsTo(Clients::class);
    }
    public function magasin()
    {
        return $this->belongsTo(Magasins::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
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
