<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RetailInvoiceTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = $this->createProductInStock(20, [
            'cost_price' => 100.00,
            'wholesale_price' => 130.00,
            'retail_price' => 160.00,
        ]);
    }

    // ─── Successful creation ───────────────────────

    public function test_valid_retail_invoice_is_created_successfully(): void
    {
        $response = $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 2, 'selling_price' => 150.00]],
            paid: 300.00,
        );

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertDatabaseHas('invoices', ['type' => 'retail', 'status' => 'active']);
    }

    public function test_retail_invoice_number_starts_with_ret(): void
    {
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 150.00]],
            paid: 150.00,
        );

        $invoice = Invoice::first();
        $this->assertStringStartsWith('RET-', $invoice->invoice_number);
    }

    // ─── Financial correctness ─────────────────────

    public function test_retail_invoice_calculates_subtotal_discount_total_correctly(): void
    {
        $product2 = $this->createProductInStock(10, [
            'cost_price' => 200.00,
            'wholesale_price' => 250.00,
            'retail_price' => 300.00,
        ]);

        $this->storeRetailInvoice(
            [
                ['product_id' => $this->product->id, 'quantity' => 3, 'selling_price' => 150.00],
                ['product_id' => $product2->id, 'quantity' => 2, 'selling_price' => 280.00],
            ],
            paid: 900.00,
            discount: 10.00,
        );

        $invoice = Invoice::first();
        // subtotal = (3×150) + (2×280) = 450 + 560 = 1010
        $this->assertEquals(1010.00, (float) $invoice->subtotal);
        $this->assertEquals(10.00, (float) $invoice->discount);
        // total = 1010 - 10 = 1000
        $this->assertEquals(1000.00, (float) $invoice->total);
        $this->assertEquals(900.00, (float) $invoice->paid);
        $this->assertEquals(100.00, (float) $invoice->remaining);
    }

    public function test_retail_invoice_profit_calculation_is_correct(): void
    {
        // cost=100, selling=160, qty=5 → profit_per_unit=60, total_profit=300
        // discount=50 → net_profit = 300 - 50 = 250
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 5, 'selling_price' => 160.00]],
            paid: 750.00,
            discount: 50.00,
        );

        $invoice = Invoice::first();
        $this->assertEquals(250.00, (float) $invoice->profit);
    }

    // ─── Payment status ────────────────────────────

    public function test_fully_paid_retail_invoice_has_paid_status(): void
    {
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 150.00]],
            paid: 150.00,
        );

        $this->assertEquals('paid', Invoice::first()->payment_status);
    }

    public function test_overpaid_retail_invoice_has_paid_status(): void
    {
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 150.00]],
            paid: 200.00,
        );

        $this->assertEquals('paid', Invoice::first()->payment_status);
    }

    public function test_partially_paid_retail_invoice_has_partial_status(): void
    {
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 2, 'selling_price' => 150.00]],
            paid: 100.00,
        );

        $this->assertEquals('partial', Invoice::first()->payment_status);
    }

    public function test_unpaid_retail_invoice_has_unpaid_status(): void
    {
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 2, 'selling_price' => 150.00]],
            paid: 0,
        );

        $this->assertEquals('unpaid', Invoice::first()->payment_status);
    }

    // ─── Stock ─────────────────────────────────────

    public function test_creating_retail_invoice_decreases_product_stock(): void
    {
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 3, 'selling_price' => 150.00]],
            paid: 450.00,
        );

        $this->product->refresh();
        $this->assertEquals(17, $this->product->stock_quantity); // 20 - 3
    }

    public function test_retail_invoice_fails_when_quantity_exceeds_stock(): void
    {
        $response = $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 25, 'selling_price' => 150.00]],
            paid: 3750.00,
        );

        $response->assertStatus(500); // Exception from DB transaction
        $this->assertDatabaseCount('invoices', 0);

        // Stock must remain unchanged
        $this->product->refresh();
        $this->assertEquals(20, $this->product->stock_quantity);
    }

    // ─── Validation ────────────────────────────────

    public function test_retail_invoice_fails_with_empty_items(): void
    {
        $response = $this->storeRetailInvoice([], paid: 0);
        $response->assertStatus(422)->assertJsonValidationErrors('items');
    }

    public function test_retail_invoice_fails_with_invalid_product(): void
    {
        $response = $this->storeRetailInvoice(
            [['product_id' => 99999, 'quantity' => 1, 'selling_price' => 100.00]],
            paid: 100.00,
        );

        $response->assertStatus(422)->assertJsonValidationErrors('items.0.product_id');
    }

    public function test_retail_invoice_fails_with_zero_quantity(): void
    {
        $response = $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 0, 'selling_price' => 100.00]],
            paid: 100.00,
        );

        $response->assertStatus(422)->assertJsonValidationErrors('items.0.quantity');
    }

    public function test_retail_invoice_fails_with_negative_quantity(): void
    {
        $response = $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => -1, 'selling_price' => 100.00]],
            paid: 100.00,
        );

        $response->assertStatus(422)->assertJsonValidationErrors('items.0.quantity');
    }

    public function test_retail_invoice_fails_when_discount_exceeds_subtotal(): void
    {
        // subtotal = 1×150 = 150, discount = 200
        $response = $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 150.00]],
            paid: 150.00,
            discount: 200.00,
        );

        $response->assertStatus(500); // Exception in transaction
        $this->assertDatabaseCount('invoices', 0);
    }

    // ─── Cancellation ──────────────────────────────

    public function test_cancelling_retail_invoice_restores_stock(): void
    {
        // Create invoice
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 5, 'selling_price' => 150.00]],
            paid: 750.00,
        );
        $this->product->refresh();
        $this->assertEquals(15, $this->product->stock_quantity);

        // Cancel it
        $invoice = Invoice::first();
        $this->postJson(route('invoices.cancel', $invoice), [
            'cancellation_reason' => 'خطأ في الفاتورة',
        ])->assertStatus(200);

        $this->product->refresh();
        $this->assertEquals(20, $this->product->stock_quantity);

        $invoice->refresh();
        $this->assertTrue($invoice->isCancelled());
    }

    public function test_cancelling_already_cancelled_invoice_returns_error(): void
    {
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 150.00]],
            paid: 150.00,
        );
        $invoice = Invoice::first();

        // First cancellation
        $this->postJson(route('invoices.cancel', $invoice), [
            'cancellation_reason' => 'أولى',
        ])->assertStatus(200);

        // Second cancellation attempt
        $this->postJson(route('invoices.cancel', $invoice), [
            'cancellation_reason' => 'ثانية',
        ])->assertStatus(422);
    }

    public function test_cancel_requires_reason(): void
    {
        $this->storeRetailInvoice(
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 150.00]],
            paid: 150.00,
        );
        $invoice = Invoice::first();

        $this->postJson(route('invoices.cancel', $invoice), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cancellation_reason');
    }
}
