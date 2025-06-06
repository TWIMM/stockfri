<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'address', 'user_id' , 'ifu'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
