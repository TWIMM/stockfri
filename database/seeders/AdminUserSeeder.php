<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder to create an admin user.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'iptveher@gmail.com',
            'password' => Hash::make('admintest@'),
            'tel' => '+90909090',
            //'sex' => 'male', // Adjust as needed
            'type' => 'admin_sys',
            'email_verified_at' => 1,
            'address' => 'Admin Address',  
            'country' => 'Country',  
            'city' => 'City',  
        ]);

        $this->command->info('Admin user created successfully!');
    }
}