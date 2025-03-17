<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermSeeder extends Seeder
{
    public function run()
    {
        // Définir les permissions
        $permissions = [
            // Gestion de l'équipe
            //'gérer équipe', 'créer équipe', 'modifier équipe', 'supprimer équipe', 'voir équipe',

            // Magasins
            'gérer magasins', 'créer magasins', 'modifier magasins', 'supprimer magasins', 'voir magasins',

            // Gestion du stock
            'gérer stock', 'créer stock', 'modifier stock', 'supprimer stock', 'voir stock',

            // Gestion des services
            'gérer services', 'créer services', 'modifier services', 'supprimer services', 'voir services',

            // Gestion des rapports
            'gérer rapports', 'créer rapports', 'modifier rapports', 'supprimer rapports', 'voir rapports',

            // Gestion des factures
            'gérer factures', 'créer factures', 'modifier factures', 'supprimer factures', 'voir factures',
            'envoyer facture',

            // Paramètres de l'application
            'gérer paramètres_application', 'modifier paramètres_application', 'voir paramètres_application',

            // Autres permissions
            'vendre produit', 'vendre service',
            /* 'gérer permissions', */ 'comptabilité',
            'gérer inventaire', 'voir rapports de ventes', 'rappel',
            'gérer stock', 'gérer service', 'gérer client', 
        ];

        // Insérer les permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Récupérer toutes les permissions
        $allPermissions = Permission::all()->keyBy('name');

        // Définir les rôles et leurs permissions respectives
        $roles = [
            /* 'gestionnaire_équipe' => [
                'gérer équipe', 'créer équipe', 'supprimer équipe', 'gérer permissions',
            ], */
            'gestionnaire_ventes' => [
                'vendre produit', 'vendre service', 'créer factures', 'envoyer facture',
            ],
            'comptable' => [
                'comptabilité', 'voir rapports de ventes', 'créer factures', 'envoyer facture',
            ],
            'rappel' => [
                'rappel',
            ],
            'gestionnaire_stock' => [
                'gérer stock', 'créer stock', 'modifier stock', 'supprimer stock', 'voir stock',
            ],
            'gestionnaire_services' => [
                'gérer services', 'créer services', 'modifier services', 'supprimer services', 'voir services',
            ],
            'gestionnaire_clients' => [
                'gérer client',
            ],
            'gestionnaire_inventaire' => [
                'gérer inventaire',
            ],
            'gestionnaire_rapports' => [
                'gérer rapports', 'créer rapports', 'modifier rapports', 'voir rapports', 'supprimer rapports',
            ],
            'gestionnaire_magasins' => [
                'gérer magasins', 'créer magasins', 'modifier magasins', 'supprimer magasins', 'voir magasins',
            ],
            'gestionnaire_paramètres' => [
                'gérer paramètres_application', 'modifier paramètres_application', 'voir paramètres_application',
            ]
        ];

        // Créer les rôles et leur assigner les permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if (in_array('all', $rolePermissions)) {
                // Assigner toutes les permissions au rôle admin
                $role->permissions()->sync($allPermissions->pluck('id')->toArray());
            } else {
                // Assigner des permissions spécifiques au rôle
                $role->permissions()->sync(
                    collect($rolePermissions)->map(fn($perm) => $allPermissions[$perm]->id)->toArray()
                );
            }
        }

        $this->command->info('Rôles et Permissions insérés avec succès !');
    }
}
