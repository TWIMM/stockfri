<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $table = 'teams'; 


    protected $fillable = [ 'name' , 'user_id'];

    // Define the many-to-many relationship with Business
    public function business()
    {
        return $this->belongsToMany(Business::class, 'business_team') ->withTimestamps()
        ->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->belongsToMany(TeamMember::class, 'team_member_team')->withPivot('permissions')->withTimestamps();
    }
}
