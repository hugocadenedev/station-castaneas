<?php

namespace Database\Seeders;

use App\Models\Caliber;
use App\Models\Customer;
use App\Models\Fruit;
use App\Models\Supplier;
use App\Models\TareType;
use App\Models\Variety;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $chataigne = Fruit::query()->firstOrCreate(['name' => 'Chataigne'], ['is_active' => true]);
        $cerise = Fruit::query()->firstOrCreate(['name' => 'Cerise'], ['is_active' => true]);
        $marron = Fruit::query()->firstOrCreate(['name' => 'Marron'], ['is_active' => true]);

        foreach ([
            [$chataigne, 'Bouche Rouge'],
            [$chataigne, 'Comballe'],
            [$cerise, 'Burlat'],
            [$marron, 'Marron d\'Olargues'],
        ] as [$fruit, $name]) {
            Variety::query()->firstOrCreate([
                'fruit_id' => $fruit->id,
                'name' => $name,
            ], [
                'is_active' => true,
            ]);
        }

        foreach ([
            ['fruit' => $chataigne, 'name' => 'Petit', 'sort_order' => 1],
            ['fruit' => $chataigne, 'name' => 'Moyen', 'sort_order' => 2],
            ['fruit' => $chataigne, 'name' => 'Gros', 'sort_order' => 3],
            ['fruit' => $cerise, 'name' => '24+', 'sort_order' => 1],
            ['fruit' => $marron, 'name' => 'Extra', 'sort_order' => 1],
        ] as $caliber) {
            Caliber::query()->firstOrCreate([
                'fruit_id' => $caliber['fruit']->id,
                'name' => $caliber['name'],
            ], [
                'sort_order' => $caliber['sort_order'],
                'is_active' => true,
            ]);
        }

        foreach ([
            ['label' => 'Palox bois standard', 'weight_kg' => 35],
            ['label' => 'Palox plastique', 'weight_kg' => 28],
            ['label' => 'Caisse atelier', 'weight_kg' => 4.5],
        ] as $tareType) {
            TareType::query()->firstOrCreate([
                'label' => $tareType['label'],
            ], [
                'weight_kg' => $tareType['weight_kg'],
                'is_active' => true,
            ]);
        }

        foreach ([
            ['name' => 'GAEC des Hauts Bois', 'supplier_code' => 'FOU-001', 'ggn_code' => 'GGN-3001234567890'],
            ['name' => 'Vergers de l\'Aigoual', 'supplier_code' => 'FOU-002', 'ggn_code' => 'GGN-3001234567891'],
        ] as $supplier) {
            Supplier::query()->updateOrCreate([
                'ggn_code' => $supplier['ggn_code'],
            ], [
                'name' => $supplier['name'],
                'supplier_code' => $supplier['supplier_code'],
                'is_active' => true,
            ]);
        }

        foreach ([
            ['name' => 'Maison Ardechoise', 'reference_code' => 'CLI-001', 'contact_name' => 'Claire Brun'],
            ['name' => 'Primeurs du Midi', 'reference_code' => 'CLI-002', 'contact_name' => 'Julien Pascal'],
        ] as $customer) {
            Customer::query()->firstOrCreate([
                'reference_code' => $customer['reference_code'],
            ], $customer + ['is_active' => true]);
        }
    }
}