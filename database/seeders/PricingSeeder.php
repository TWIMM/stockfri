<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pricing;

class PricingSeeder extends Seeder
{
    public function run()
    {
        Pricing::insert([
            [
                'name' => 'Essentiel',
                'offers' => json_encode([
                    '100 Produits',
                    '10 Fournisseurs',
                    '5 equipes',
                    '5 membres par equipes',
                    'Gestion des commandes', 
                    'Mail professionnelles'
                ]),
                'price' => 15.00,
                'periodicity' => 'monthly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professionnel',
                'offers' => json_encode([
                    '300',
                    'Fournisseurs illimités',
                    '20 Utilisateurs',
                    'Gestion des commandes & ventes',
                    'Statistiques avancées',
                    'Support prioritaire'
                ]),
                'price' => 25.00,
                'periodicity' => 'monthly',
                'created_at' => now(),
                'updated_at' => now(),
            ], 
            [
                'name' => 'Golden',
                'offers' => json_encode([
                    'Produits illimites',
                    'Fournisseurs illimites',
                    'Equipes illimites',
                    'Outils AI',
                    'Gestion des commandes', 
                    'Mail professionnelles'
                ]),
                'price' => 100.00,
                'periodicity' => 'monthly',
                'created_at' => now(),
                'updated_at' => now(),
            ],

             // Annual Plans (Reduced Price)
            [
                'name' => 'Essentiel (Annuel)',
                'offers' => json_encode([
                    '100 Produits',
                    '10 Fournisseurs',
                    '5 équipes',
                    '5 membres par équipe',
                    'Gestion des commandes',
                    'Mail professionnel'
                ]),
                'price' => 150.00, // 12 months for the price of 10
                'periodicity' => 'yearly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professionnel (Annuel)',
                'offers' => json_encode([
                    '300 Produits',
                    'Fournisseurs illimités',
                    '20 Utilisateurs',
                    'Gestion des commandes & ventes',
                    'Statistiques avancées',
                    'Support prioritaire'
                ]),
                'price' => 250.00, // 12 months for the price of 10
                'periodicity' => 'yearly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Golden (Annuel)',
                'offers' => json_encode([
                    'Produits illimités',
                    'Fournisseurs illimités',
                    'Équipes illimitées',
                    'Outils AI',
                    'Gestion des commandes',
                    'Mail professionnel'
                ]),
                'price' => 1000.00, // 12 months for the price of 10
                'periodicity' => 'yearly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
