<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;
use PHPUnit\Framework\Attributes\DataProvider;

class RouteTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    #[DataProvider('publicGetRoutes')]
    public function test_public_get_routes_respond_successfully(string $routeName, array $params = []): void
    {
        $response = $this->get(route($routeName, $params));
        $response->assertStatus(200);
    }

    public static function publicGetRoutes(): array
    {
        return [
            'home' => ['home'],
            'invoices index' => ['invoices.index'],
            'retail POS' => ['invoices.create-retail'],
            'wholesale POS' => ['invoices.create-wholesale'],
            'products index' => ['products.index'],
            'products create' => ['products.create'],
            'customers index' => ['customers.index'],
            'customers create' => ['customers.create'],
            'suppliers index' => ['suppliers.index'],
            'suppliers create' => ['suppliers.create'],
            'expenses index' => ['expenses.index'],
            'prices index' => ['prices.index'],
            'reports login' => ['reports.login'],
        ];
    }

    public function test_reports_dashboard_requires_authentication(): void
    {
        $this->get(route('reports.dashboard'))
            ->assertRedirect(route('reports.login'));
    }

    public function test_invoice_show_route_works_with_valid_invoice(): void
    {
        $product = $this->createProductInStock(10, ['cost_price' => 100, 'retail_price' => 150]);

        $this->postJson(route('invoices.store-retail'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'selling_price' => 150]],
            'paid' => 150,
        ]);

        $invoice = \App\Models\Invoice::first();
        $this->get(route('invoices.show', $invoice))->assertStatus(200);
    }

    public function test_product_search_api_responds_json(): void
    {
        $this->getJson(route('api.products.search', ['q' => 'test']))
            ->assertStatus(200)
            ->assertJsonIsArray();
    }

    public function test_customer_show_route_works(): void
    {
        $customer = $this->createWholesaleCustomer();
        $this->get(route('customers.show', $customer))->assertStatus(200);
    }

    public function test_supplier_show_route_works(): void
    {
        $supplier = $this->createSupplier();
        $this->get(route('suppliers.show', $supplier))->assertStatus(200);
    }
}
