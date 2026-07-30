<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ReportAccessTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    // ─── Access control ────────────────────────────

    public function test_financial_dashboard_redirects_unauthenticated_to_login(): void
    {
        $response = $this->get(route('reports.dashboard'));
        $response->assertRedirect(route('reports.login'));
    }

    public function test_correct_password_grants_access(): void
    {
        // Set a known hash in the environment
        $hash = Hash::make('TestPass123');
        config(['app.financial_password_hash' => $hash]);

        // The controller uses env(), so we need to test via session
        $this->withSession(['financial_authenticated' => true])
            ->get(route('reports.dashboard'))
            ->assertStatus(200);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $response = $this->post(route('reports.authenticate'), [
            'password' => 'wrong_password_here',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNull(session('financial_authenticated'));
    }

    public function test_authenticated_session_can_access_dashboard(): void
    {
        $response = $this->withSession(['financial_authenticated' => true])
            ->get(route('reports.dashboard'));

        $response->assertStatus(200);
    }

    public function test_logout_clears_financial_session(): void
    {
        $response = $this->withSession(['financial_authenticated' => true])
            ->post(route('reports.logout'));

        $response->assertRedirect(route('home'));
        $this->assertNull(session('financial_authenticated'));
    }

    // ─── Dashboard totals ──────────────────────────

    public function test_dashboard_totals_are_correct(): void
    {
        // Setup data
        $product = $this->createProductInStock(100, ['cost_price' => 100]);
        $customer = $this->createWholesaleCustomer(['balance' => 500]);
        $customerCredit = $this->createWholesaleCustomer(['balance' => -200]);
        $supplier = $this->createSupplier(['current_balance' => 3000, 'initial_balance' => 3000]);

        // Create invoices in date range
        $inv1 = Invoice::create([
            'invoice_number' => 'RET-000001', 'type' => 'retail',
            'subtotal' => 1000, 'total' => 1000, 'paid' => 1000,
            'remaining' => 0, 'profit' => 200,
            'payment_status' => 'paid', 'status' => 'active',
        ]);
        $inv1->update(['created_at' => now()]); // Force update timestamp

        $inv2 = Invoice::create([
            'invoice_number' => 'WHL-000001', 'type' => 'wholesale',
            'customer_id' => $customer->id,
            'subtotal' => 2000, 'total' => 2000, 'paid' => 1500,
            'remaining' => 500, 'profit' => 400,
            'payment_status' => 'partial', 'status' => 'active',
        ]);
        $inv2->update(['created_at' => now()]); // Force update timestamp

        Expense::factory()->create(['amount' => 150, 'expense_date' => now()->format('Y-m-d')]);

        $response = $this->withSession(['financial_authenticated' => true])
            ->get(route('reports.dashboard', [
                'date_from' => now()->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('totalSales', 3000.00);      // 1000 + 2000
        $response->assertViewHas('totalProfit', 600.00);       // 200 + 400
        $response->assertViewHas('totalExpenses', 150.00);
        $response->assertViewHas('netProfit', 450.00);         // 600 - 150
        $response->assertViewHas('supplierDebts', 3000.00);
        $response->assertViewHas('customerDebts', 500.00);
        $response->assertViewHas('customerCredits', -200.00);
    }

    public function test_date_filters_include_only_records_within_range(): void
    {
        // Invoice inside range
        $inv1 = Invoice::create([
            'invoice_number' => 'RET-000001', 'type' => 'retail',
            'subtotal' => 500, 'total' => 500, 'paid' => 500,
            'remaining' => 0, 'profit' => 100,
            'payment_status' => 'paid', 'status' => 'active',
        ]);
        $inv1->created_at = '2024-06-15 12:00:00';
        $inv1->save();

        // Invoice outside range
        $inv2 = Invoice::create([
            'invoice_number' => 'RET-000002', 'type' => 'retail',
            'subtotal' => 800, 'total' => 800, 'paid' => 800,
            'remaining' => 0, 'profit' => 200,
            'payment_status' => 'paid', 'status' => 'active',
        ]);
        $inv2->created_at = '2024-07-20 12:00:00';
        $inv2->save();

        $response = $this->withSession(['financial_authenticated' => true])
            ->get(route('reports.dashboard', [
                'date_from' => '2024-06-01',
                'date_to' => '2024-06-30',
            ]));

        $response->assertViewHas('totalSales', 500.00); // Only June invoice
        $response->assertViewHas('totalProfit', 100.00);
    }

    // ─── Stock calculations ────────────────────────

    public function test_low_stock_and_out_of_stock_counts_are_correct(): void
    {
        $this->createProductInStock(3);  // low stock (<=5, >0)
        $this->createProductInStock(5);  // low stock boundary
        $this->createProductInStock(0);  // out of stock
        $this->createProductInStock(0);  // out of stock
        $this->createProductInStock(50); // normal stock

        $response = $this->withSession(['financial_authenticated' => true])
            ->get(route('reports.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('outOfStockProducts', 2);
        $response->assertViewHas('lowStockProducts', function ($products) {
            return $products->count() === 2; // 3 and 5 stock
        });
    }
}
