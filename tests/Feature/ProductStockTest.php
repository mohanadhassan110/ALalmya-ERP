<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ProductStockTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    // ─── Product creation ──────────────────────────

    public function test_product_creation_with_stock_creates_stock_entry(): void
    {
        $category = $this->createCategory();

        $this->post(route('products.store'), [
            'name' => 'طقم ملاية حرير',
            'category_id' => $category->id,
            'cost_price' => 100,
            'wholesale_price' => 130,
            'retail_price' => 160,
            'stock_quantity' => 50,
        ]);

        $product = Product::where('name', 'طقم ملاية حرير')->first();
        $this->assertNotNull($product);
        $this->assertEquals(50, $product->stock_quantity);

        // Stock entry marked as opening stock (no supplier)
        $this->assertDatabaseHas('stock_entries', [
            'product_id' => $product->id,
            'quantity' => 50,
            'is_opening_stock' => true,
            'supplier_id' => null,
        ]);
    }

    public function test_product_creation_with_supplier_increases_supplier_balance(): void
    {
        $category = $this->createCategory();
        $supplier = $this->createSupplier(['current_balance' => 0]);

        $this->post(route('products.store'), [
            'name' => 'لحاف فايبر',
            'category_id' => $category->id,
            'cost_price' => 200,
            'wholesale_price' => 250,
            'stock_quantity' => 10,
            'supplier_id' => $supplier->id,
        ]);

        $supplier->refresh();
        // total_cost = 10 × 200 = 2000
        $this->assertEquals(2000.00, (float) $supplier->current_balance);

        // Supplier transaction should exist
        $this->assertDatabaseHas('supplier_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'purchase',
            'amount' => 2000.00,
            'balance_after' => 2000.00,
        ]);
    }

    public function test_product_creation_without_supplier_is_opening_stock(): void
    {
        $category = $this->createCategory();

        $this->post(route('products.store'), [
            'name' => 'مخدة فايبر',
            'category_id' => $category->id,
            'cost_price' => 50,
            'wholesale_price' => 70,
            'stock_quantity' => 20,
            // No supplier_id
        ]);

        $product = Product::where('name', 'مخدة فايبر')->first();
        $stockEntry = StockEntry::where('product_id', $product->id)->first();

        $this->assertTrue((bool) $stockEntry->is_opening_stock);
        $this->assertNull($stockEntry->supplier_id);
    }

    public function test_product_creation_with_is_opening_stock_flag_ignores_supplier(): void
    {
        $category = $this->createCategory();
        $supplier = $this->createSupplier();

        $this->post(route('products.store'), [
            'name' => 'مرتبة سوست',
            'category_id' => $category->id,
            'cost_price' => 500,
            'wholesale_price' => 600,
            'stock_quantity' => 5,
            'supplier_id' => $supplier->id,
            'is_opening_stock' => 1, // Flag overrides supplier
        ]);

        $product = Product::where('name', 'مرتبة سوست')->first();
        $stockEntry = StockEntry::where('product_id', $product->id)->first();

        $this->assertTrue((bool) $stockEntry->is_opening_stock);
        $this->assertNull($stockEntry->supplier_id);

        // Supplier balance should NOT increase
        $supplier->refresh();
        $this->assertEquals(0, (float) $supplier->current_balance);
    }

    // ─── Adding stock ──────────────────────────────

    public function test_adding_stock_increases_product_quantity(): void
    {
        $product = $this->createProductInStock(10);
        $supplier = $this->createSupplier();

        $this->post(route('products.add-stock', $product), [
            'quantity' => 15,
            'cost_price' => $product->cost_price,
            'supplier_id' => $supplier->id,
        ]);

        $product->refresh();
        $this->assertEquals(25, $product->stock_quantity);
    }

    public function test_adding_supplier_stock_creates_supplier_transaction(): void
    {
        $product = $this->createProductInStock(10, ['cost_price' => 100]);
        $supplier = $this->createSupplier(['current_balance' => 500]);

        $this->post(route('products.add-stock', $product), [
            'quantity' => 20,
            'cost_price' => 120,
            'supplier_id' => $supplier->id,
        ]);

        $supplier->refresh();
        // total_cost = 20 × 120 = 2400
        $this->assertEquals(2900.00, (float) $supplier->current_balance); // 500 + 2400

        $this->assertDatabaseHas('supplier_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'purchase',
            'amount' => 2400.00,
            'balance_after' => 2900.00,
        ]);
    }

    // ─── Product deletion ──────────────────────────

    public function test_product_with_invoice_items_cannot_be_deleted(): void
    {
        $product = $this->createProductInStock(10, ['cost_price' => 100, 'retail_price' => 150]);

        // Create a retail invoice for this product
        $this->postJson(route('invoices.store-retail'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'selling_price' => 150]],
            'paid' => 150,
        ]);

        // Try to delete
        $response = $this->delete(route('products.destroy', $product));
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('error');

        // Product must still exist
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_product_without_invoices_can_be_deleted(): void
    {
        $product = $this->createProductInStock(10);

        $this->delete(route('products.destroy', $product));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // ─── Product search ────────────────────────────

    public function test_product_search_works_by_name(): void
    {
        $p1 = $this->createProductInStock(10, ['name' => 'ملاية حرير أبيض']);
        $p2 = $this->createProductInStock(10, ['name' => 'لحاف شتوي أحمر']);

        $response = $this->getJson(route('api.products.search', ['q' => 'ملاية']));
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals($p1->id, $data[0]['id']);
    }

    public function test_product_search_works_by_sku(): void
    {
        $p1 = $this->createProductInStock(10, ['sku' => 'MLY-001']);

        $response = $this->getJson(route('api.products.search', ['q' => 'MLY-001']));
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals($p1->id, $data[0]['id']);
    }

    public function test_out_of_stock_products_do_not_appear_in_search(): void
    {
        $this->createProductInStock(0, ['name' => 'منتج نفد', 'sku' => 'EMPTY-001']);

        $response = $this->getJson(route('api.products.search', ['q' => 'منتج نفد']));
        $data = $response->json();

        $this->assertCount(0, $data);
    }
}
