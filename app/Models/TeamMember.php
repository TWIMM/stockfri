<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [ 'name', 'email', 'tel' , 'user_id'];

    public function team()
    {
        return $this->belongsToMany(Team::class, 'team_member_team')
        ->withPivot(['permissions', 'business_id'])
        ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public static function deleteAssociatedUser($email)
    {
        // Find the user by the team member's email
        $user = User::where('email', $email)->first();

        // If the user exists, delete them
        if ($user) {
            $user->delete();  // Or use forceDelete() if you want to permanently delete
        }
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
