<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementDeStocks extends Model
{
    use HasFactory;

    protected $fillable = [ 'user_id', 'stock_id', 'type_de_mouvement'];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
