<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class WholesaleInvoiceTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    private Product $product;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = $this->createProductInStock(30, [
            'cost_price' => 200.00,
            'wholesale_price' => 250.00,
            'retail_price' => 300.00,
        ]);
        $this->customer = $this->createWholesaleCustomer(['balance' => 0]);
    }

    // ─── Successful creation ───────────────────────

    public function test_valid_wholesale_invoice_is_created(): void
    {
        $response = $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 3, 'selling_price' => 240.00]],
            paid: 720.00,
        );

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertDatabaseHas('invoices', ['type' => 'wholesale']);
    }

    public function test_wholesale_invoice_number_starts_with_whl(): void
    {
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 240.00]],
            paid: 240.00,
        );

        $this->assertStringStartsWith('WHL-', Invoice::first()->invoice_number);
    }

    // ─── Customer balance ──────────────────────────

    public function test_wholesale_invoice_updates_customer_balance_correctly(): void
    {
        // total = 5 × 240 = 1200, paid = 500 → remaining = 700
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 5, 'selling_price' => 240.00]],
            paid: 500.00,
        );

        $this->customer->refresh();
        $this->assertEquals(700.00, (float) $this->customer->balance);
    }

    public function test_fully_paid_wholesale_invoice_leaves_customer_balance_zero(): void
    {
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 2, 'selling_price' => 240.00]],
            paid: 480.00,
        );

        $this->customer->refresh();
        $this->assertEquals(0.00, (float) $this->customer->balance);
    }

    public function test_existing_customer_credit_is_applied_to_wholesale_invoice(): void
    {
        // Give customer a 200 credit (negative balance)
        $this->customer->update(['balance' => -200.00]);

        // total = 2 × 240 = 480
        // paid = 100 (cash) + 200 (credit) = 300 effective
        // remaining = 480 - 300 = 180
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 2, 'selling_price' => 240.00]],
            paid: 100.00,
        );

        $this->customer->refresh();
        // Customer balance = -200 (credit) + (480 - 100) = -200 + 380 = 180
        $this->assertEquals(180.00, (float) $this->customer->balance);
    }

    public function test_customer_overpayment_creates_credit_balance(): void
    {
        // total = 1 × 240 = 240, paid = 400 → overpay by 160
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 240.00]],
            paid: 400.00,
        );

        $this->customer->refresh();
        // balance = 0 + (240 - 400) = -160 (credit)
        $this->assertEquals(-160.00, (float) $this->customer->balance);
        $this->assertTrue($this->customer->hasCreditBalance());
    }

    // ─── Ledger transactions ───────────────────────

    public function test_customer_ledger_transactions_are_created_for_wholesale_invoice(): void
    {
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 2, 'selling_price' => 240.00]],
            paid: 300.00,
        );

        // Should have: 1 invoice transaction + 1 payment transaction
        $this->assertDatabaseHas('customer_transactions', [
            'customer_id' => $this->customer->id,
            'type' => 'invoice',
            'amount' => 480.00,
        ]);
        $this->assertDatabaseHas('customer_transactions', [
            'customer_id' => $this->customer->id,
            'type' => 'payment',
            'amount' => 300.00,
        ]);
    }

    public function test_no_payment_transaction_when_paid_is_zero(): void
    {
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 240.00]],
            paid: 0,
        );

        // Only invoice transaction, no payment
        $this->assertEquals(1, CustomerTransaction::where('customer_id', $this->customer->id)->count());
        $this->assertDatabaseHas('customer_transactions', [
            'customer_id' => $this->customer->id,
            'type' => 'invoice',
        ]);
        $this->assertDatabaseMissing('customer_transactions', [
            'customer_id' => $this->customer->id,
            'type' => 'payment',
        ]);
    }

    // ─── Cancellation ──────────────────────────────

    public function test_cancelling_wholesale_invoice_restores_customer_balance(): void
    {
        // total = 2 × 240 = 480, paid = 200 → balance = 280
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 2, 'selling_price' => 240.00]],
            paid: 200.00,
        );
        $this->customer->refresh();
        $this->assertEquals(280.00, (float) $this->customer->balance);

        // Cancel it
        $invoice = Invoice::first();
        $this->postJson(route('invoices.cancel', $invoice), [
            'cancellation_reason' => 'إلغاء بطلب العميل',
        ])->assertStatus(200);

        $this->customer->refresh();
        // balance should go back to 0: 280 - 480 (total) + 200 (payment reversed) = 0
        $this->assertEquals(0.00, (float) $this->customer->balance);
    }

    public function test_cancelling_wholesale_invoice_creates_adjustment_transaction(): void
    {
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 240.00]],
            paid: 100.00,
        );

        $invoice = Invoice::first();
        $this->postJson(route('invoices.cancel', $invoice), [
            'cancellation_reason' => 'إلغاء',
        ])->assertStatus(200);

        $this->assertDatabaseHas('customer_transactions', [
            'customer_id' => $this->customer->id,
            'type' => 'adjustment',
            'invoice_id' => $invoice->id,
        ]);
    }

    public function test_cancelling_wholesale_invoice_restores_stock(): void
    {
        $this->storeWholesaleInvoice(
            $this->customer->id,
            [['product_id' => $this->product->id, 'quantity' => 4, 'selling_price' => 240.00]],
            paid: 960.00,
        );
        $this->product->refresh();
        $this->assertEquals(26, $this->product->stock_quantity);

        $invoice = Invoice::first();
        $this->postJson(route('invoices.cancel', $invoice), [
            'cancellation_reason' => 'إلغاء',
        ]);

        $this->product->refresh();
        $this->assertEquals(30, $this->product->stock_quantity);
    }

    // ─── Validation ────────────────────────────────

    public function test_wholesale_invoice_requires_customer(): void
    {
        $response = $this->postJson(route('invoices.store-wholesale'), [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 240.00]],
            'paid' => 240.00,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('customer_id');
    }

    public function test_wholesale_invoice_requires_valid_customer(): void
    {
        $response = $this->storeWholesaleInvoice(
            99999,
            [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 240.00]],
            paid: 240.00,
        );

        $response->assertStatus(422)->assertJsonValidationErrors('customer_id');
    }
}
