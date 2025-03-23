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
        'validation_status',
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

    public function livraison()
    {
        return $this->hasOne(Livraisons::class, 'commande_id');
    }
    
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

    public function getCommandeDetails()
    {
        $commande = $this->with(['client', 'magasin', 'user', 'commandeItems'])->find($this->id);

        // Prepare the details array
        $details = [
            'id' => $commande->id,
            'client' => $commande->client->name, 
            'client_id' => $commande->client_id,
            'magasin' => $commande->magasin->name, 
            'magasin_id' => $commande->magasin_id,
            'user' => $commande->user->name, 
            'validation_status' => $commande->validation_status,
            'user_id' => $commande->user_id,
            'total_price' => $commande->total_price,
            'invoice_status' => $commande->invoice_status,
            'payment_mode' => $commande->payment_mode,
            'tva' => $commande->tva,
            'mobile_number' => $commande->mobile_number,
            'mobile_reference' => $commande->mobile_reference,
            'bank_name' => $commande->bank_name,
            'bank_reference' => $commande->bank_reference,
            'card_type' => $commande->card_type,
            'card_reference' => $commande->card_reference,
            'cash_reference' => $commande->cash_reference,
            'already_paid' => $commande->already_paid,
            'rest_to_pay' => $commande->rest_to_pay,
            'commande_items' => $commande->commandeItems->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->total_price,
                    'discount' => $item->discount,
                ];
            }),
        ];

        return $details;
    }
}
