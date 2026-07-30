<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ExpenseAndPriceTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    // ─── Expenses ──────────────────────────────────

    public function test_valid_expense_is_stored_successfully(): void
    {
        $response = $this->post(route('expenses.store'), [
            'amount' => 150.50,
            'reason' => 'إيجار المحل',
            'expense_date' => '2024-03-15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'amount' => 150.50,
            'reason' => 'إيجار المحل',
        ]);
        $expense = Expense::first();
        $this->assertEquals('2024-03-15', $expense->expense_date->format('Y-m-d'));
    }

    public function test_expense_requires_amount(): void
    {
        $response = $this->post(route('expenses.store'), [
            'reason' => 'اختبار',
            'expense_date' => '2024-03-15',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_expense_requires_reason(): void
    {
        $response = $this->post(route('expenses.store'), [
            'amount' => 100,
            'expense_date' => '2024-03-15',
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_expense_requires_date(): void
    {
        $response = $this->post(route('expenses.store'), [
            'amount' => 100,
            'reason' => 'اختبار',
        ]);

        $response->assertSessionHasErrors('expense_date');
    }

    public function test_expense_appears_in_correct_date_report(): void
    {
        // Create expenses on different dates
        Expense::factory()->create(['expense_date' => '2024-03-15', 'amount' => 100]);
        Expense::factory()->create(['expense_date' => '2024-03-15', 'amount' => 200]);
        Expense::factory()->create(['expense_date' => '2024-03-16', 'amount' => 50]);

        $response = $this->get(route('expenses.index', ['date' => '2024-03-15']));
        $response->assertStatus(200);
        $response->assertViewHas('todayTotal', 300.00);
        $response->assertViewHas('expenses', function ($expenses) {
            return $expenses->count() === 2;
        });
    }

    public function test_expense_can_be_deleted(): void
    {
        $expense = Expense::factory()->create();

        $this->delete(route('expenses.destroy', $expense));
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    // ─── Prices ────────────────────────────────────

    public function test_price_update_changes_only_submitted_fields(): void
    {
        $product = $this->createProductInStock(10, [
            'cost_price' => 100,
            'wholesale_price' => 130,
            'retail_price' => 160,
        ]);

        // Only update wholesale price
        $this->put(route('prices.update', $product), [
            'wholesale_price' => 140,
        ]);

        $product->refresh();
        $this->assertEquals(100.00, (float) $product->cost_price); // unchanged
        $this->assertEquals(140.00, (float) $product->wholesale_price); // changed
        $this->assertEquals(160.00, (float) $product->retail_price); // unchanged
    }

    public function test_price_update_validates_non_negative_values(): void
    {
        $product = $this->createProductInStock(10);

        $response = $this->put(route('prices.update', $product), [
            'cost_price' => -50,
        ]);

        $response->assertSessionHasErrors('cost_price');
    }
}
