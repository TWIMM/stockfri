<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $fillable = ['business_id','user_id', 'quantity',  'title', 'description', 'price'];
    
    public function commandeItems()
    {
        return $this->hasMany(CommandeItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function purchases()
    {
        return $this->hasMany(Commandes::class);
    }
}
