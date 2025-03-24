<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'commande_id',
        'amount',
        //'payment_method',
        //'is_late',
        'due_date',
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}
