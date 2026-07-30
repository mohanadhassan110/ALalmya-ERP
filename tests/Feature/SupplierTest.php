<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\SupplierTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class SupplierTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_supplier_can_be_created_with_opening_balance(): void
    {
        $this->post(route('suppliers.store'), [
            'name' => 'مصنع الفيوم للمفروشات',
            'phone' => '01098765432',
            'initial_balance' => 5000,
        ]);

        $supplier = Supplier::where('name', 'مصنع الفيوم للمفروشات')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals(5000.00, (float) $supplier->initial_balance);
        $this->assertEquals(5000.00, (float) $supplier->current_balance);
    }

    public function test_opening_balance_creates_supplier_transaction(): void
    {
        $this->post(route('suppliers.store'), [
            'name' => 'مورد تجريبي',
            'initial_balance' => 3000,
        ]);

        $supplier = Supplier::where('name', 'مورد تجريبي')->first();

        $this->assertDatabaseHas('supplier_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'opening_balance',
            'amount' => 3000.00,
            'balance_after' => 3000.00,
        ]);
    }

    public function test_zero_opening_balance_does_not_create_transaction(): void
    {
        $this->post(route('suppliers.store'), [
            'name' => 'مورد صفر',
        ]);

        $supplier = Supplier::where('name', 'مورد صفر')->first();
        $this->assertEquals(0, SupplierTransaction::where('supplier_id', $supplier->id)->count());
    }

    public function test_supplier_payment_decreases_current_balance(): void
    {
        $supplier = $this->createSupplier(['current_balance' => 5000, 'initial_balance' => 5000]);

        $this->post(route('suppliers.payment', $supplier), [
            'amount' => 2000,
            'description' => 'سداد دفعة',
        ]);

        $supplier->refresh();
        $this->assertEquals(3000.00, (float) $supplier->current_balance);
    }

    public function test_supplier_payment_creates_transaction_with_correct_balance_after(): void
    {
        $supplier = $this->createSupplier(['current_balance' => 5000, 'initial_balance' => 5000]);

        $this->post(route('suppliers.payment', $supplier), [
            'amount' => 1500,
        ]);

        $this->assertDatabaseHas('supplier_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'payment',
            'amount' => 1500.00,
            'balance_after' => 3500.00,
        ]);
    }

    public function test_supplier_cannot_be_deleted_when_balance_is_not_zero(): void
    {
        $supplier = $this->createSupplier(['current_balance' => 1000, 'initial_balance' => 1000]);

        $response = $this->delete(route('suppliers.destroy', $supplier));
        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_supplier_can_be_deleted_when_balance_is_zero(): void
    {
        $supplier = $this->createSupplier(['current_balance' => 0, 'initial_balance' => 0]);

        $this->delete(route('suppliers.destroy', $supplier));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
