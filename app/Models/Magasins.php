<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'name', 'description', 'quantity', 'price'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function purchase()
    {
        return $this->hasMany(Commandes::class);
    }

    // Fonction pour ajouter des articles (restockage)
    public function restock($quantity)
    {
        $this->quantity += $quantity;
        $this->save();
    }

    // Fonction pour retirer des articles (déstockage)
    public function deStock($quantity)
    {
        if ($this->quantity >= $quantity) {
            $this->quantity -= $quantity;
            $this->save();
        } else {
            throw new \Exception("Quantité insuffisante pour déstockage.");
        }
    }

    // Fonction pour gérer un retour de produit (retour des clients)
    public function returnStock($quantity)
    {
        $this->quantity += $quantity;
        $this->save();
    }

    // Fonction pour transférer des articles entre magasins
    public function transferStock($quantity, $destinationStore)
    {
        if ($this->quantity >= $quantity) {
            $this->deStock($quantity);
            $destinationStore->restock($quantity);
        } else {
            throw new \Exception("Quantité insuffisante pour transfert.");
        }
    }
}
