<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_retail_invoice_number_is_ret_000001(): void
    {
        $number = Invoice::generateInvoiceNumber('retail');
        $this->assertEquals('RET-000001', $number);
    }

    public function test_first_wholesale_invoice_number_is_whl_000001(): void
    {
        $number = Invoice::generateInvoiceNumber('wholesale');
        $this->assertEquals('WHL-000001', $number);
    }

    public function test_retail_invoice_numbers_increment_correctly(): void
    {
        $cat = Category::factory()->create();
        $product = Product::factory()->inStock(100)->create(['category_id' => $cat->id]);

        // Create 3 retail invoices
        for ($i = 1; $i <= 3; $i++) {
            Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber('retail'),
                'type' => 'retail',
                'subtotal' => 100,
                'total' => 100,
                'paid' => 100,
                'remaining' => 0,
                'profit' => 10,
                'payment_status' => 'paid',
                'status' => 'active',
            ]);
        }

        $nextNumber = Invoice::generateInvoiceNumber('retail');
        $this->assertEquals('RET-000004', $nextNumber);
    }

    public function test_wholesale_invoice_numbers_increment_independently_from_retail(): void
    {
        // Create 2 retail invoices
        for ($i = 1; $i <= 2; $i++) {
            Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber('retail'),
                'type' => 'retail',
                'subtotal' => 100,
                'total' => 100,
                'paid' => 100,
                'remaining' => 0,
                'profit' => 10,
                'payment_status' => 'paid',
                'status' => 'active',
            ]);
        }

        // First wholesale should still be WHL-000001
        $wholesaleNumber = Invoice::generateInvoiceNumber('wholesale');
        $this->assertEquals('WHL-000001', $wholesaleNumber);

        // Create 1 wholesale
        Invoice::create([
            'invoice_number' => $wholesaleNumber,
            'type' => 'wholesale',
            'subtotal' => 200,
            'total' => 200,
            'paid' => 200,
            'remaining' => 0,
            'profit' => 20,
            'payment_status' => 'paid',
            'status' => 'active',
        ]);

        // Next retail should be RET-000003 (unaffected by wholesale)
        $this->assertEquals('RET-000003', Invoice::generateInvoiceNumber('retail'));
        // Next wholesale should be WHL-000002
        $this->assertEquals('WHL-000002', Invoice::generateInvoiceNumber('wholesale'));
    }
}
