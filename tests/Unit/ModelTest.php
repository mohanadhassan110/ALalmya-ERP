<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Invoice model
    // ──────────────────────────────────────────────

    public function test_invoice_is_cancelled_returns_true_when_status_is_cancelled(): void
    {
        $invoice = new Invoice(['status' => 'cancelled']);
        $this->assertTrue($invoice->isCancelled());
    }

    public function test_invoice_is_cancelled_returns_false_when_status_is_active(): void
    {
        $invoice = new Invoice(['status' => 'active']);
        $this->assertFalse($invoice->isCancelled());
    }

    public function test_invoice_is_active_returns_true_for_active_and_null(): void
    {
        $active = new Invoice(['status' => 'active']);
        $this->assertTrue($active->isActive());

        $null = new Invoice(['status' => null]);
        $this->assertTrue($null->isActive());
    }

    public function test_invoice_is_active_returns_false_for_cancelled(): void
    {
        $cancelled = new Invoice(['status' => 'cancelled']);
        $this->assertFalse($cancelled->isActive());
    }

    public function test_invoice_active_scope_excludes_cancelled(): void
    {
        Invoice::create([
            'invoice_number' => 'RET-000001',
            'type' => 'retail',
            'subtotal' => 100, 'total' => 100, 'paid' => 100,
            'remaining' => 0, 'profit' => 10,
            'payment_status' => 'paid', 'status' => 'active',
        ]);
        Invoice::create([
            'invoice_number' => 'RET-000002',
            'type' => 'retail',
            'subtotal' => 200, 'total' => 200, 'paid' => 200,
            'remaining' => 0, 'profit' => 20,
            'payment_status' => 'paid', 'status' => 'cancelled',
        ]);

        $this->assertCount(1, Invoice::active()->get());
        $this->assertEquals('RET-000001', Invoice::active()->first()->invoice_number);
    }

    // ──────────────────────────────────────────────
    // Customer model
    // ──────────────────────────────────────────────

    public function test_customer_has_credit_balance(): void
    {
        $debtor = new Customer(['balance' => 500]);
        $this->assertFalse($debtor->hasCreditBalance());

        $creditor = new Customer(['balance' => -200]);
        $this->assertTrue($creditor->hasCreditBalance());

        $zero = new Customer(['balance' => 0]);
        $this->assertFalse($zero->hasCreditBalance());
    }

    public function test_customer_credit_amount_attribute(): void
    {
        $creditor = new Customer(['balance' => -350]);
        $this->assertEquals(350, $creditor->credit_amount);

        $debtor = new Customer(['balance' => 200]);
        $this->assertEquals(0, $debtor->credit_amount);
    }

    public function test_customer_debt_amount_attribute(): void
    {
        $debtor = new Customer(['balance' => 500]);
        $this->assertEquals(500, $debtor->debt_amount);

        $creditor = new Customer(['balance' => -200]);
        $this->assertEquals(0, $creditor->debt_amount);
    }

    // ──────────────────────────────────────────────
    // Product model
    // ──────────────────────────────────────────────

    public function test_product_inventory_value_attribute(): void
    {
        $product = new Product([
            'stock_quantity' => 10,
            'cost_price' => 150.00,
        ]);
        $this->assertEquals(1500.00, $product->inventory_value);
    }

    public function test_product_inventory_value_is_zero_when_no_stock(): void
    {
        $product = new Product([
            'stock_quantity' => 0,
            'cost_price' => 150.00,
        ]);
        $this->assertEquals(0, $product->inventory_value);
    }
}
