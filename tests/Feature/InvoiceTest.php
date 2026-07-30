<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private $category;
    private $product1;
    private $product2;
    private $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create initial test data
        $this->category = Category::create(['name' => 'مفروشات غرف النوم']);
        
        $this->product1 = Product::create([
            'name' => 'طقم ملاية مطرز',
            'category_id' => $this->category->id,
            'cost_price' => 150.00,
            'wholesale_price' => 180.00,
            'retail_price' => 200.00,
            'stock_quantity' => 10,
            'sku' => 'PROD-001'
        ]);

        $this->product2 = Product::create([
            'name' => 'لحاف فايبر دبل',
            'category_id' => $this->category->id,
            'cost_price' => 400.00,
            'wholesale_price' => 450.00,
            'retail_price' => 500.00,
            'stock_quantity' => 5,
            'sku' => 'PROD-002'
        ]);

        $this->customer = Customer::create([
            'name' => 'احمد المحلاوي',
            'phone' => '01012345678',
            'type' => 'wholesale',
            'balance' => 0.00
        ]);
    }

    public function test_it_can_create_retail_invoice_and_deducts_stock()
    {
        $response = $this->postJson(route('invoices.store-retail'), [
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 2,
                    'selling_price' => 190.00
                ]
            ],
            'paid' => 380.00,
            'discount' => 0,
            'notes' => 'تجربة فاتورة تجزئة'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Assert invoice was stored correctly
        $this->assertDatabaseHas('invoices', [
            'type' => 'retail',
            'total' => 380.00,
            'paid' => 380.00,
            'remaining' => 0.00,
            'payment_status' => 'paid',
            'status' => 'active'
        ]);

        // Assert stock was decremented
        $this->product1->refresh();
        $this->assertEquals(8, $this->product1->stock_quantity);
    }

    public function test_it_can_create_wholesale_invoice_with_customer_and_updates_balance()
    {
        $response = $this->postJson(route('invoices.store-wholesale'), [
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 2,
                    'selling_price' => 440.00
                ]
            ],
            'paid' => 300.00, // Partial payment
            'discount' => 0,
            'notes' => 'تجربة فاتورة جملة آجل'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Invoice total = 2 * 440 = 880. Paid = 300. Remaining = 580.
        $this->assertDatabaseHas('invoices', [
            'type' => 'wholesale',
            'customer_id' => $this->customer->id,
            'total' => 880.00,
            'paid' => 300.00,
            'remaining' => 580.00,
            'payment_status' => 'partial'
        ]);

        // Assert customer balance is updated (debt added)
        $this->customer->refresh();
        $this->assertEquals(580.00, $this->customer->balance);

        // Assert customer ledger transaction was recorded
        $this->assertDatabaseHas('customer_transactions', [
            'customer_id' => $this->customer->id,
            'type' => 'invoice',
            'amount' => 880.00,
            'balance_after' => 580.00
        ]);

        // Assert product stock was decremented
        $this->product2->refresh();
        $this->assertEquals(3, $this->product2->stock_quantity);
    }

    public function test_it_can_cancel_an_invoice_and_restores_stock_and_customer_balance()
    {
        // 1. Create a wholesale invoice first
        $invoice = Invoice::create([
            'invoice_number' => 'WHL-000001',
            'type' => 'wholesale',
            'customer_id' => $this->customer->id,
            'subtotal' => 450.00,
            'discount' => 0,
            'total' => 450.00,
            'paid' => 150.00,
            'remaining' => 300.00,
            'profit' => 50.00,
            'payment_status' => 'partial',
            'status' => 'active'
        ]);

        $item = $invoice->items()->create([
            'product_id' => $this->product2->id,
            'product_name' => $this->product2->name,
            'quantity' => 1,
            'cost_price' => 400.00,
            'selling_price' => 450.00,
            'line_total' => 450.00,
            'line_profit' => 50.00
        ]);

        // Deduct stock for the invoice
        $this->product2->decrement('stock_quantity', 1); // was 5, now 4
        $this->customer->balance += 300.00; // was 0, now 300
        $this->customer->save();

        // Register transaction
        $this->customer->transactions()->create([
            'type' => 'invoice',
            'amount' => 450.00,
            'balance_after' => 300.00,
            'description' => 'فاتورة جملة',
            'invoice_id' => $invoice->id
        ]);
        $this->customer->transactions()->create([
            'type' => 'payment',
            'amount' => 150.00,
            'balance_after' => 300.00,
            'description' => 'سداد جزء من الفاتورة',
            'invoice_id' => $invoice->id
        ]);

        // 2. Call cancel endpoint
        $response = $this->postJson(route('invoices.cancel', $invoice), [
            'cancellation_reason' => 'إرجاع البضاعة بطلب من العميل'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 3. Assert states
        $invoice->refresh();
        $this->assertTrue($invoice->isCancelled());
        $this->assertEquals('إرجاع البضاعة بطلب من العميل', $invoice->cancellation_reason);

        // Stock restored (should be 5 again)
        $this->product2->refresh();
        $this->assertEquals(5, $this->product2->stock_quantity);

        // Customer balance restored (should be 0 again because balance calculation: balance = 300 - total(450) + payment(150) = 0)
        $this->customer->refresh();
        $this->assertEquals(0.00, $this->customer->balance);
    }
}
