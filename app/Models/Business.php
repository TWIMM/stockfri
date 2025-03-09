<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory , SoftDeletes;
    protected $table = 'business'; 


    protected $fillable = [
        'user_id',
        'name',
        'type',
        'description',
        'ifu',
        'commercial_number',
        'number',
        'business_email'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'business_team')->withTimestamps();
        
    }
}
