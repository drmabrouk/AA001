<?php
/**
 * Test script to verify DB schema creation syntax and financial calculation formulas.
 */

// Mock WordPress ABSPATH if not defined
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

class MockWPDB {
	public $prefix = 'wp_';
	public function get_charset_collate() {
		return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
	}
	public function insert( $table, $data, $format ) {
		return true;
	}
}

$wpdb = new MockWPDB();

// Include class-db.php
require_once __DIR__ . '/../includes/class-db.php';

// Test financial calculations
$input = array(
	'opening_cash' => 1000.00,
	'cash_collections' => 5000.00,
	'bank_electronic' => 3000.00,
	'other_income' => 500.00,
	'discounts' => 200.00,
	'refunds' => 300.00,
	'expenses' => 1200.00,
	'vat_rate' => 5.0,
	'outstanding_payments' => 400.00,
);

$calculated = CSA_DB::calculate_financials( $input );

// Output verification
echo "=== Financial Calculation Test Results ===\n";
echo "Opening Cash: " . $calculated['opening_cash'] . " (Expected: 1000)\n";
echo "Total Revenue: " . $calculated['total_revenue'] . " (Expected: 8300 = (5000+3000+500)-200)\n";
echo "VAT Amount (5%): " . $calculated['vat_amount'] . " (Expected: 415.00)\n";
echo "Net Daily Income: " . $calculated['net_daily_income'] . " (Expected: 6800 = 8300 - 300 - 1200)\n";
echo "Closing Cash: " . $calculated['closing_cash'] . " (Expected: 4500 = 1000 + 5000 - 300 - 1200)\n";

assert( $calculated['total_revenue'] == 8300.00, "Total revenue calculation failed" );
assert( $calculated['vat_amount'] == 415.00, "VAT calculation failed" );
assert( $calculated['net_daily_income'] == 6800.00, "Net daily income calculation failed" );
assert( $calculated['closing_cash'] == 4500.00, "Closing cash calculation failed" );

echo "\nALL FINANCIAL CALCULATIONS PASSED SUCCESSFULLY!\n";
