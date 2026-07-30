<?php

namespace Tests\Traits;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;

/**
 * Shared helpers for creating realistic test data without depending on factories
 * when inline creation is more readable.
 */
trait CreatesTestData
{
    protected function createCategory(array $overrides = []): Category
    {
        return Category::factory()->create($overrides);
    }

    protected function createProduct(array $overrides = []): Product
    {
        return Product::factory()->create($overrides);
    }

    protected function createProductInStock(int $qty = 20, array $overrides = []): Product
    {
        return Product::factory()->inStock($qty)->create($overrides);
    }

    protected function createWholesaleCustomer(array $overrides = []): Customer
    {
        return Customer::factory()->wholesale()->create($overrides);
    }

    protected function createSupplier(array $overrides = []): Supplier
    {
        return Supplier::factory()->create($overrides);
    }

    /**
     * Helper to post a valid retail invoice and return the response.
     */
    protected function storeRetailInvoice(array $items, float $paid, float $discount = 0, ?string $notes = null)
    {
        return $this->postJson(route('invoices.store-retail'), [
            'items' => $items,
            'paid' => $paid,
            'discount' => $discount,
            'notes' => $notes,
        ]);
    }

    /**
     * Helper to post a valid wholesale invoice and return the response.
     */
    protected function storeWholesaleInvoice(int $customerId, array $items, float $paid, float $discount = 0, ?string $notes = null)
    {
        return $this->postJson(route('invoices.store-wholesale'), [
            'customer_id' => $customerId,
            'items' => $items,
            'paid' => $paid,
            'discount' => $discount,
            'notes' => $notes,
        ]);
    }
}
