<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = ['business_id' , 'user_id' ,'category_id', 'name', 'description','quantite_inventorie', 'quantity', 'price'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categorieProduits()
    {
        return $this->belongsTo(CategorieProduits::class);
    }

    public function purchase()
    {
        return $this->hasMany(Commandes::class);

    }
    // Many-to-many relationship with Stock
    public function magasins()
    {
        return $this->belongsToMany(Magasins::class, 'magasin_stock', 'magasin_id', 'stock_id')->withPivot('quantity');
    }
}
