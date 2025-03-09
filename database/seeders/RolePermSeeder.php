<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermSeeder extends Seeder
{
    public function run()
    {
        // Define permissions
        $permissions = [
            // Team Management
            'manage team', 'create team', 'edit team', 'delete team', 'view team',

            // Magasins (Stores)
            'manage magasins', 'create magasins', 'edit magasins', 'delete magasins', 'view magasins',

           
            // Stock Management
            'manage stock', 'create stock', 'edit stock', 'delete stock', 'view stock',

            // Service Management
            'manage services', 'create services', 'edit services', 'delete services', 'view services',

            // Report Management
            'manage reports', 'create reports', 'edit reports', 'delete reports', 'view reports',

            // Invoice Management
            'manage invoices', 'create invoices', 'edit invoices', 'delete invoices', 'view invoices',
            'send invoice',

            // App Settings
            'manage setting_of_app', 'edit setting_of_app', 'view setting_of_app',

            // Other Permissions
            'sell product', 'sell service',
            'manage permissions', 'accountability',
            'manage inventory', 'view sales reports', 'rappel',
            'handle stock', 'handle service', 'handle client', 
        ];

        // Seed permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Fetch all permissions
        $allPermissions = Permission::all()->keyBy('name');

        // Define roles and their respective permissions
        $roles = [
            
            'team_handler' => [
                'manage team', 'create team', 'delete team', 'manage permissions',
            ],
            'sales_handler' => [
                'sell product', 'sell service', 'create invoices', 'send invoice',
            ],
            'comptable' => [
                'accountability', 'view sales reports', 'create invoices', 'send invoice',
            ],
            'rappel' => [
                'rappel',
            ],
            'stock_handler' => [
                'manage stock', 'create stock', 'edit stock', 'delete stock', 'view stock',
            ],
            'service_handler' => [
                'manage services', 'create services', 'edit services', 'delete services', 'view services',
            ],
            'client_handler' => [
                'handle client',
            ],
            'inventory_handler' => [
                'manage inventory',
            ],
            'report_handler' => [
                'manage reports', 'create reports', 'edit reports', 'view reports', 'delete reports',
            ],
            'magasin_handler' => [
                'manage magasins', 'create magasins', 'edit magasins', 'delete magasins', 'view magasins',
            ],
           
            'settings_handler' => [
                'manage setting_of_app', 'edit setting_of_app', 'view setting_of_app',
            ]
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if (in_array('all', $rolePermissions)) {
                // Assign all permissions to admin role
                $role->permissions()->sync($allPermissions->pluck('id')->toArray());
            } else {
                // Assign specific permissions to the role
                $role->permissions()->sync(
                    collect($rolePermissions)->map(fn($perm) => $allPermissions[$perm]->id)->toArray()
                );
            }
        }

        $this->command->info('Roles and Permissions seeded successfully!');
    }
}
