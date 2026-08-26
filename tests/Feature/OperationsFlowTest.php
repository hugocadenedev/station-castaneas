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
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
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

    public function test_reception_can_be_created_without_gross_weight_and_completed_later(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();

        $createResponse = $this->actingAs($user)->post(route('receptions.store'), [
            'received_at' => now()->format('Y-m-d H:i:s'),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'gross_weight_kg' => null,
            'conformity_status' => 'conforming',
        ]);

        $createResponse->assertRedirect(route('receptions.index'));

        $reception = Reception::query()->firstOrFail();

        $this->assertNull($reception->gross_weight_kg);

        $updateResponse = $this->actingAs($user)->patch(route('receptions.update', $reception), [
            'gross_weight_kg' => 145.750,
        ]);

        $updateResponse->assertRedirect(route('receptions.index'));
        $this->assertSame('145.750', $reception->fresh()->gross_weight_kg);
    }

    public function test_superadmin_can_delete_unused_fruit_with_its_unused_references(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $fruit = Fruit::query()->create([
            'name' => 'Fruit test '.Str::random(8),
            'is_active' => true,
        ]);

        $variety = Variety::query()->create([
            'fruit_id' => $fruit->id,
            'name' => 'Variete test '.Str::random(8),
            'is_active' => true,
        ]);

        $caliber = Caliber::query()->create([
            'fruit_id' => $fruit->id,
            'name' => 'Calibre test '.Str::random(8),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('backoffice.fruits.destroy', $fruit));

        $response->assertRedirect(route('backoffice.index', ['section' => 'production']));

        $this->assertSoftDeleted('fruits', ['id' => $fruit->id]);
        $this->assertSoftDeleted('varieties', ['id' => $variety->id]);
        $this->assertSoftDeleted('calibers', ['id' => $caliber->id]);
    }

    public function test_superadmin_can_create_supplier_from_backoffice(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $name = 'Fournisseur test '.Str::random(8);
        $supplierCode = 'FOU-'.random_int(100, 999);
        $ggnCode = 'GGN-'.random_int(1000000000000, 9999999999999);
        $email = 'supplier-'.Str::lower(Str::random(6)).'@example.test';

        $response = $this->actingAs($user)->post(route('backoffice.suppliers.store'), [
            'name' => $name,
            'supplier_code' => $supplierCode,
            'ggn_code' => $ggnCode,
            'email' => $email,
            'phone' => '0600000000',
        ]);

        $response->assertRedirect(route('backoffice.index', ['section' => 'fournisseurs']));

        $this->assertDatabaseHas('suppliers', [
            'name' => $name,
            'supplier_code' => $supplierCode,
            'ggn_code' => $ggnCode,
            'email' => $email,
        ]);
    }

    public function test_superadmin_can_delete_unused_supplier(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $supplier = Supplier::query()->create([
            'name' => 'Suppression test '.Str::random(8),
            'supplier_code' => 'FOU-'.random_int(100, 999),
            'ggn_code' => 'GGN-'.random_int(1000000000000, 9999999999999),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('backoffice.suppliers.destroy', $supplier));

        $response->assertRedirect(route('backoffice.index', ['section' => 'fournisseurs']));
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_reception_deletion_removes_related_calibrations_paloxes_and_order_links(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $caliber = Caliber::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $tareType = TareType::query()->firstOrFail();

        $reception = Reception::query()->create([
            'reception_number' => 'REC-'.now()->format('Ymd').'-'.random_int(1000, 9999),
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 250.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'pending',
        ]);

        $calibration = Calibration::query()->create([
            'reception_id' => $reception->id,
            'caliber_id' => $caliber->id,
            'tare_type_id' => $tareType->id,
            'performed_by' => $user->id,
            'calibrated_at' => Carbon::now(),
            'net_weight_kg' => 200.000,
            'waste_weight_kg' => 5.000,
        ]);

        $palox = Palox::query()->create([
            'reception_id' => $reception->id,
            'calibration_id' => $calibration->id,
            'created_by' => $user->id,
            'palox_number' => 'PAL-TEST-'.Str::upper(Str::random(8)),
            'initial_net_weight_kg' => 200.000,
            'remaining_net_weight_kg' => 150.000,
            'under_contract' => false,
            'availability_status' => 'partial',
            'labeled_at' => Carbon::now(),
        ]);

        $order = CustomerOrder::query()->create([
            'client_name' => 'Client test suppression',
            'order_number' => 'CMD-'.Str::upper(Str::random(8)),
            'ordered_at' => Carbon::now(),
            'created_by' => $user->id,
        ]);

        $order->paloxes()->attach($palox->id, ['picked_net_weight_kg' => 50.000]);

        $response = $this->actingAs($user)->delete(route('receptions.destroy', $reception));

        $response->assertRedirect(route('receptions.index'));
        $this->assertDatabaseMissing('receptions', ['id' => $reception->id]);
        $this->assertDatabaseMissing('calibrations', ['id' => $calibration->id]);
        $this->assertDatabaseMissing('paloxes', ['id' => $palox->id]);
        $this->assertDatabaseMissing('customer_order_palox', ['palox_id' => $palox->id]);
        $this->assertDatabaseHas('customer_orders', ['id' => $order->id]);
    }

    public function test_superadmin_can_create_user_even_if_operator_role_was_missing(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('superadmin');

        Role::query()->where('name', 'operateur')->delete();

        $email = 'user-'.Str::lower(Str::random(8)).'@example.test';

        $response = $this->actingAs($user)->post(route('backoffice.users.store'), [
            'name' => 'Operateur Test',
            'email' => $email,
            'password' => 'motdepasse8',
            'role' => 'operateur',
        ]);

        $response->assertRedirect(route('backoffice.index', ['section' => 'utilisateurs']));
        $this->assertDatabaseHas('users', ['email' => $email, 'name' => 'Operateur Test']);
        $this->assertTrue(User::query()->where('email', $email)->firstOrFail()->hasRole('operateur'));
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
        $tareType = TareType::query()->where('is_active', true)->firstOrFail();

        $response = $this->actingAs($user)->post(route('calibrages.store'), [
            'reception_id' => $reception->id,
            'caliber_id' => $caliber->id,
            'tare_type_id' => $tareType->id,
            'tare_weight_kg' => 12.500,
            'calibrated_at' => now()->format('Y-m-d H:i:s'),
            'net_weight_kg' => 180.000,
            'waste_weight_kg' => 40.000,
        ]);

        $response->assertRedirect(route('calibrages.create', ['reception_id' => $reception->id]));

        $reception->refresh();
        $calibration = Calibration::query()->firstOrFail();
        $palox = Palox::query()->firstOrFail();

        $this->assertSame('pending', $reception->processing_status);
        $this->assertSame($calibration->id, $palox->calibration_id);
        $this->assertMatchesRegularExpression('/^\d{2}-\d{3}$/', $palox->palox_number);
        $this->assertSame('available', $palox->availability_status);

        $finalizeResponse = $this->actingAs($user)->post(route('calibrages.finalize', $reception));

        $finalizeResponse->assertRedirect(route('calibrages.index'));
        $this->assertSame('calibrated', $reception->fresh()->processing_status);
    }

    public function test_calibration_can_be_built_step_by_step_with_multiple_paloxes_before_finalization(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $caliber = Caliber::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $tareType = TareType::query()->where('is_active', true)->firstOrFail();

        $reception = Reception::query()->create([
            'reception_number' => 'REC-TEST-STEP-01',
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 300.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'pending',
        ]);

        foreach ([120.000, 110.000] as $netWeight) {
            $response = $this->actingAs($user)->post(route('calibrages.store'), [
                'reception_id' => $reception->id,
                'caliber_id' => $caliber->id,
                'tare_type_id' => $tareType->id,
                'tare_weight_kg' => 12.500,
                'calibrated_at' => now()->format('Y-m-d H:i:s'),
                'net_weight_kg' => $netWeight,
                'waste_weight_kg' => 5.000,
            ]);

            $response->assertRedirect(route('calibrages.create', ['reception_id' => $reception->id]));
        }

        $this->assertDatabaseCount('calibrations', 2);
        $this->assertDatabaseCount('paloxes', 2);
        $this->assertSame('pending', $reception->fresh()->processing_status);

        $finalizeResponse = $this->actingAs($user)->post(route('calibrages.finalize', $reception));

        $finalizeResponse->assertRedirect(route('calibrages.index'));
        $this->assertSame('calibrated', $reception->fresh()->processing_status);
    }

    public function test_calibration_store_redirect_renders_create_page_with_saved_palox(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $caliber = Caliber::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $tareType = TareType::query()->where('is_active', true)->firstOrFail();

        $reception = Reception::query()->create([
            'reception_number' => 'REC-TEST-REDIRECT-01',
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 210.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('calibrages.store'), [
                'reception_id' => $reception->id,
                'caliber_id' => $caliber->id,
                'tare_type_id' => $tareType->id,
                'tare_weight_kg' => 12.500,
                'calibrated_at' => now()->format('Y-m-d H:i:s'),
                'net_weight_kg' => 180.000,
                'waste_weight_kg' => 15.000,
            ]);

        $response->assertOk();
        $response->assertSee('Valider le calibrage', false);
        $response->assertSee(Palox::query()->firstOrFail()->palox_number, false);
    }

    public function test_last_palox_can_be_removed_before_calibration_finalization(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $caliber = Caliber::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $tareType = TareType::query()->where('is_active', true)->firstOrFail();

        $reception = Reception::query()->create([
            'reception_number' => 'REC-TEST-STEP-03',
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 250.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'pending',
        ]);

        foreach ([130.000, 90.000] as $netWeight) {
            $this->actingAs($user)->post(route('calibrages.store'), [
                'reception_id' => $reception->id,
                'caliber_id' => $caliber->id,
                'tare_type_id' => $tareType->id,
                'tare_weight_kg' => 12.500,
                'calibrated_at' => now()->format('Y-m-d H:i:s'),
                'net_weight_kg' => $netWeight,
                'waste_weight_kg' => 5.000,
            ]);
        }

        $lastPaloxId = Palox::query()->latest('id')->firstOrFail()->id;
        $lastCalibrationId = Calibration::query()->latest('id')->firstOrFail()->id;

        $response = $this->actingAs($user)->delete(route('calibrages.destroy-last-palox', $reception));

        $response->assertRedirect(route('calibrages.create', ['reception_id' => $reception->id]));
        $this->assertDatabaseCount('paloxes', 1);
        $this->assertDatabaseCount('calibrations', 1);
        $this->assertDatabaseMissing('paloxes', ['id' => $lastPaloxId]);
        $this->assertDatabaseMissing('calibrations', ['id' => $lastCalibrationId]);
        $this->assertSame('pending', $reception->fresh()->processing_status);
    }

    public function test_palox_is_not_available_for_orders_before_calibration_is_finalized(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $caliber = Caliber::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $tareType = TareType::query()->where('is_active', true)->firstOrFail();

        $reception = Reception::query()->create([
            'reception_number' => 'REC-TEST-STEP-02',
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 210.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'pending',
        ]);

        $this->actingAs($user)->post(route('calibrages.store'), [
            'reception_id' => $reception->id,
            'caliber_id' => $caliber->id,
            'tare_type_id' => $tareType->id,
            'tare_weight_kg' => 10.000,
            'calibrated_at' => now()->format('Y-m-d H:i:s'),
            'net_weight_kg' => 180.000,
            'waste_weight_kg' => 20.000,
        ]);

        $createOrderPage = $this->actingAs($user)->get(route('commandes.create'));

        $createOrderPage->assertOk();
        $createOrderPage->assertDontSee(Palox::query()->firstOrFail()->palox_number);

        $this->actingAs($user)->post(route('calibrages.finalize', $reception));

        $createOrderPageAfterFinalize = $this->actingAs($user)->get(route('commandes.create'));

        $createOrderPageAfterFinalize->assertSee(Palox::query()->firstOrFail()->palox_number);
    }

    public function test_calibration_rejects_caliber_from_another_fruit(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $otherFruit = Fruit::query()->whereKeyNot($fruit->id)->firstOrFail();
        $otherCaliber = Caliber::query()->where('fruit_id', $otherFruit->id)->firstOrFail();
        $tareType = TareType::query()->where('is_active', true)->firstOrFail();

        $reception = Reception::query()->create([
            'reception_number' => 'REC-TEST-0003',
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 220.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'pending',
        ]);

        $response = $this->from(route('calibrages.create'))->actingAs($user)->post(route('calibrages.store'), [
            'reception_id' => $reception->id,
            'caliber_id' => $otherCaliber->id,
            'tare_type_id' => $tareType->id,
            'tare_weight_kg' => 12.500,
            'calibrated_at' => now()->format('Y-m-d H:i:s'),
            'net_weight_kg' => 180.000,
            'waste_weight_kg' => 40.000,
        ]);

        $response->assertRedirect(route('calibrages.create'));
        $response->assertSessionHasErrors('caliber_id');
        $this->assertDatabaseCount('calibrations', 0);
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

    public function test_calibration_index_lists_calibrated_receptions_and_detail_page(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ReferenceDataSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('operateur');

        $supplier = Supplier::query()->firstOrFail();
        $fruit = Fruit::query()->firstOrFail();
        $variety = Variety::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $caliber = Caliber::query()->where('fruit_id', $fruit->id)->firstOrFail();
        $tareType = TareType::query()->where('is_active', true)->firstOrFail();

        $reception = Reception::query()->create([
            'reception_number' => 'REC-DETAIL-0001',
            'received_at' => now(),
            'supplier_id' => $supplier->id,
            'fruit_id' => $fruit->id,
            'variety_id' => $variety->id,
            'received_by' => $user->id,
            'gross_weight_kg' => 210.000,
            'conformity_status' => 'conforming',
            'processing_status' => 'calibrated',
        ]);

        $calibration = Calibration::query()->create([
            'reception_id' => $reception->id,
            'caliber_id' => $caliber->id,
            'tare_type_id' => $tareType->id,
            'tare_weight_kg' => 8.000,
            'performed_by' => $user->id,
            'calibrated_at' => now(),
            'net_weight_kg' => 180.000,
            'waste_weight_kg' => 20.000,
        ]);

        $palox = Palox::query()->create([
            'reception_id' => $reception->id,
            'calibration_id' => $calibration->id,
            'created_by' => $user->id,
            'palox_number' => 'PAL-DETAIL-01',
            'initial_net_weight_kg' => 180.000,
            'remaining_net_weight_kg' => 180.000,
            'under_contract' => false,
            'availability_status' => 'available',
            'labeled_at' => now(),
        ]);

        $indexResponse = $this->actingAs($user)->get(route('calibrages.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('REC-DETAIL-0001');
        $indexResponse->assertSee('Voir le détail');

        $showResponse = $this->actingAs($user)->get(route('calibrages.show', $reception));

        $showResponse->assertOk();
        $showResponse->assertSee('REC-DETAIL-0001');
        $showResponse->assertSee($palox->palox_number);
        $showResponse->assertSee($caliber->name);
    }
}