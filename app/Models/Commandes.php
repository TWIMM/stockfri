<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commandes extends Model
{
    use HasFactory;


    protected $fillable = ['client_id', 'stock_id', 'service_id', 'quantity', 'total_price'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
