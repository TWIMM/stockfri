<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livraisons extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'commande_id',
        'delivery_date',
        'delivery_status', // none, pending, in_progress, delivered, cancelled
        'delivery_address',
        'delivery_notes',
        'delivered_by',
        'received_by',
        'tracking_number',
        'shipping_method',
        'shipping_cost'
    ];
    
    /**
     * Get the commande that owns the delivery
     */
    public function commande()
    {
        return $this->belongsTo(Commandes::class, 'commande_id');
    }
    
    /**
     * Get the user who delivered the order
     */
    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
    
    /**
     * Get the delivery details
     */
    public function getDeliveryDetails()
    {
        $livraison = $this->with(['commande', 'deliveryPerson'])->find($this->id);
        
        // Prepare the details array
        $details = [
            'id' => $livraison->id,
            'commande_id' => $livraison->commande_id,
            'delivery_date' => $livraison->delivery_date,
            'delivery_status' => $livraison->delivery_status,
            'delivery_address' => $livraison->delivery_address,
            'delivery_notes' => $livraison->delivery_notes,
            'delivered_by' => $livraison->delivered_by,
            'deliverer_name' => $livraison->deliveryPerson ? $livraison->deliveryPerson->name : null,
            'received_by' => $livraison->received_by,
            'tracking_number' => $livraison->tracking_number,
            'shipping_method' => $livraison->shipping_method,
            'shipping_cost' => $livraison->shipping_cost,
            'commande' => $livraison->commande ? $livraison->commande->getCommandeDetails() : null,
        ];
        
        return $details;
    }
}