<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementDeStocks extends Model
{
    use HasFactory;

    protected $fillable = [ 'user_id', 'magasin_id' , 'prix_fournisseur', 'fournisseur_id', 'stock_id', 'type_de_mouvement' , 'files_paths' , 'quantity'];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
