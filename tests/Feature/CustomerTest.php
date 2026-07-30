<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class CustomerTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_customer_can_be_created_as_retail(): void
    {
        $this->post(route('customers.store'), [
            'name' => 'محمد أحمد',
            'phone' => '01012345678',
            'type' => 'retail',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'محمد أحمد',
            'type' => 'retail',
            'balance' => 0,
        ]);
    }

    public function test_customer_can_be_created_as_wholesale(): void
    {
        $this->post(route('customers.store'), [
            'name' => 'تاجر الصعيد',
            'type' => 'wholesale',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'تاجر الصعيد',
            'type' => 'wholesale',
        ]);
    }

    public function test_customer_payment_decreases_debt(): void
    {
        $customer = $this->createWholesaleCustomer(['balance' => 1000]);

        $this->post(route('customers.payment', $customer), [
            'amount' => 400,
            'type' => 'payment',
        ]);

        $customer->refresh();
        $this->assertEquals(600.00, (float) $customer->balance);
    }

    public function test_customer_advance_payment_creates_credit(): void
    {
        $customer = $this->createWholesaleCustomer(['balance' => 0]);

        $this->post(route('customers.payment', $customer), [
            'amount' => 500,
            'type' => 'advance',
        ]);

        $customer->refresh();
        // Advance reduces balance, creating negative (credit)
        $this->assertEquals(-500.00, (float) $customer->balance);
        $this->assertTrue($customer->hasCreditBalance());
    }

    public function test_customer_ledger_records_payment_correctly(): void
    {
        $customer = $this->createWholesaleCustomer(['balance' => 800]);

        $this->post(route('customers.payment', $customer), [
            'amount' => 300,
            'type' => 'payment',
            'description' => 'سداد نقدي',
        ]);

        $this->assertDatabaseHas('customer_transactions', [
            'customer_id' => $customer->id,
            'type' => 'payment',
            'amount' => 300.00,
            'balance_after' => 500.00,
        ]);
    }

    public function test_customer_ledger_records_advance_correctly(): void
    {
        $customer = $this->createWholesaleCustomer(['balance' => 200]);

        $this->post(route('customers.payment', $customer), [
            'amount' => 500,
            'type' => 'advance',
        ]);

        $customer->refresh();
        $this->assertEquals(-300.00, (float) $customer->balance);

        $this->assertDatabaseHas('customer_transactions', [
            'customer_id' => $customer->id,
            'type' => 'advance',
            'amount' => 500.00,
            'balance_after' => -300.00,
        ]);
    }

    public function test_customer_cannot_be_deleted_when_balance_is_not_zero(): void
    {
        $customer = $this->createWholesaleCustomer(['balance' => 500]);

        $response = $this->delete(route('customers.destroy', $customer));
        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_customer_with_credit_balance_cannot_be_deleted(): void
    {
        $customer = $this->createWholesaleCustomer(['balance' => -200]);

        $response = $this->delete(route('customers.destroy', $customer));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_customer_with_zero_balance_can_be_deleted(): void
    {
        $customer = $this->createWholesaleCustomer(['balance' => 0]);

        $this->delete(route('customers.destroy', $customer));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
