<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Magasins extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'user_id', 'name', 'description', 'address', 'email' , 'tel'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    public function commandes()
    {
        return $this->hasMany(Commandes::class);
    }

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, 'magasin_stock', 'magasin_id', 'stock_id')->withPivot('quantity'); // Include the pivot column 'quantity'

    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
   
}
