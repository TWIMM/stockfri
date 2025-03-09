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
}
