<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'googleId',
        'password',
        'tel',
        'sex',
        'type',
        'otp_hashed',
        'is_email_verified',
        'address',  
        'country',  
        'city',  
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }


    public function business()
    {
        return $this->hasMany(Business::class);
    }

    public function team()
    {
        return $this->hasMany(Team::class);
    }

    public function categorieProduits()
    {
        return $this->hasMany(CategorieProduits::class);
    }
    public function fournisseurs()
    {
        return $this->hasMany(Fournisseur::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
   
}
