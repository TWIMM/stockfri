<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coequipier extends Model
{
    use HasFactory;
    protected $table =  'coequipiers';

    protected $fillable = ['name', 'email', 'tel'];
}
