<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandeItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'commande_id',
        'stock_id',
        'service_id',
        'quantity',
        'unit_price',
        'discount', 
        'total_price'
    ];
    
    public function commande()
    {
        return $this->belongsTo(Commandes::class, 'commande_id');
    }
    
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
    
    public function service()
    {
        return $this->belongsTo(Services::class);
    }
    
    public function calculateTotalPrice()
    {
        $price = $this->unit_price * $this->quantity;
        if ($this->discount > 0) {
            $price = $price - ($price * ($this->discount / 100));
        }
        return $price;
    }
}