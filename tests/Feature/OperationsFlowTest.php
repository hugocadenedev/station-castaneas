<?php

namespace Tests\Feature;

use App\Models\Calibration;
use App\Models\Caliber;
use App\Models\CustomerOrder;
use App\Models\Fruit;
use App\Models\Palox;
use App\Models\Reception;
use App\Models\Supplier;
use App\Models\TareType;
use App\Models\User;
use App\Models\Variety;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_conforming_reception_is_stored_with_non_conforming_stock_status(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();

        $response = $this->actingAs($user)->post(route('receptions.store'), [
            'received_at' => now()->format('Y-m-d H:i:s'),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'gross_weight_kg' => 120.500,
            'conformity_status' => 'non_conforming',
            'non_conformity_reason' => 'Taux de défaut visuel trop élevé',
        ]);

        $response->assertRedirect(route('receptions.index'));

        $reception = Reception::query()->firstOrFail();

        $this->assertStringStartsWith('REC-', $reception->reception_number);
        $this->assertSame('non_conforming', $reception->conformity_status);
        $this->assertSame('stocked_non_conforming', $reception->processing_status);
    }

    public function test_calibration_creates_a_palox_and_marks_reception_as_calibrated(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $caliber = Caliber::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $reception = Reception::query()->create([
            'reception_number' => 'REC-TEST-0001',
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 220.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('calibrages.store'), [
            'reception_id' => $reception->id,
            'caliber_id' => $caliber->id,
            'tare_weight_kg' => 12.500,
            'calibrated_at' => now()->format('Y-m-d H:i:s'),
            'net_weight_kg' => 180.000,
            'waste_weight_kg' => 40.000,
        ]);

        $response->assertRedirect(route('calibrages.index'));

        $reception->refresh();
        $calibration = Calibration::query()->firstOrFail();
        $palox = Palox::query()->firstOrFail();

        $this->assertSame('calibrated', $reception->processing_status);
        $this->assertSame($calibration->id, $palox->calibration_id);
        $this->assertStringStartsWith('PAL-', $palox->palox_number);
        $this->assertSame('available', $palox->availability_status);
    }

    public function test_order_creation_decrements_stock_to_zero_and_exhausts_palox(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $caliber = Caliber::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $reception = Reception::query()->create([
            'reception_number' => 'REC-TEST-0002',
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 100.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'calibrated',
        ]);

        $manualTareType = TareType::query()->firstOrCreate([
            'label' => 'Saisie manuelle',
        ], [
            'weight_kg' => 0,
            'is_active' => false,
        ]);

        $calibration = Calibration::query()->create([
            'reception_id' => $reception->id,
            'caliber_id' => $caliber->id,
            'tare_type_id' => $manualTareType->id,
            'tare_weight_kg' => 8.000,
            'performed_by' => $user->id,
            'calibrated_at' => now(),
            'net_weight_kg' => 100.000,
            'waste_weight_kg' => 0,
        ]);

        $palox = Palox::query()->create([
            'reception_id' => $reception->id,
            'calibration_id' => $calibration->id,
            'created_by' => $user->id,
            'palox_number' => 'PAL-TEST-0001',
            'initial_net_weight_kg' => 100.000,
            'remaining_net_weight_kg' => 100.000,
            'under_contract' => false,
            'availability_status' => 'available',
            'labeled_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('commandes.store'), [
            'client_name' => 'Client Test',
            'order_number' => '',
            'ordered_at' => now()->format('Y-m-d H:i:s'),
            'lines' => [
                ['palox_id' => $palox->id],
            ],
        ]);

        $response->assertRedirect(route('commandes.index'));

        $palox->refresh();
        $order = CustomerOrder::query()->firstOrFail();

        $this->assertStringStartsWith('CMD-', $order->order_number);
        $this->assertSame('0.000', $palox->remaining_net_weight_kg);
        $this->assertSame('exhausted', $palox->availability_status);
        $this->assertSame(100.0, (float) $order->paloxes()->firstOrFail()->pivot->picked_net_weight_kg);

        $updateResponse = $this->actingAs($user)->patch(route('commandes.update', $order), [
            'order_number' => 'CMD-MANUEL-42',
        ]);

        $updateResponse->assertRedirect(route('commandes.index'));
        $this->assertSame('CMD-MANUEL-42', $order->fresh()->order_number);
    }
}