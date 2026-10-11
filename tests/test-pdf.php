<?php
/**
 * Test PDF Generation output with Dompdf.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

define( 'CSA_PLUGIN_DIR', __DIR__ . '/../' );

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/class-db.php';
require_once __DIR__ . '/../includes/class-branding.php';

// Mock WP functions for PDF compilation test
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) { return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8' ); }
}

$report = array(
	'id' => 1,
	'report_code' => 'CSA-20260330-1001',
	'report_date' => '2026-03-30',
	'branch_name' => 'Dubai Main Branch',
	'prepared_by' => 'John Supervisor',
	'reporting_period' => 'Full Day',
	'operational_summary' => 'Operations ran smoothly across all sports programs.',
	'overall_remarks' => 'No safety incidents reported.'
);

$branding = array(
	'academy_name' => 'Champions Sports Academy',
	'footer_prefs' => 'Confidential Report',
	'default_currency' => 'AED',
	'logo_path' => '',
	'logo_url' => ''
);

$financials = array(
	'opening_cash' => 1000.00,
	'total_revenue' => 8300.00,
	'net_daily_income' => 6800.00,
	'closing_cash' => 4500.00,
	'cash_collections' => 5000.00,
	'discounts' => 200.00,
	'bank_electronic' => 3000.00,
	'refunds' => 300.00,
	'other_income' => 500.00,
	'expenses' => 1200.00,
	'vat_rate' => 5.0,
	'vat_amount' => 415.00,
	'financial_notes' => 'All balanced.'
);

$currency = 'AED';
$staff_records = array(
	array( 'staff_name' => 'Coach Ahmed', 'job_title' => 'Swimming Head Coach', 'status' => 'Present', 'arrival_time' => '08:00', 'departure_time' => '17:00', 'notes' => 'On time' )
);

$sports_records = array(
	array(
		'sport_name' => 'Swimming',
		'coach_name' => 'Coach Ahmed',
		'session_time' => '09:00 AM',
		'expected_count' => 20,
		'present_count' => 18,
		'absent_count' => 2,
		'late_count' => 0,
		'entry_mode' => 'summary',
		'program_notes' => 'Great performance.'
	)
);

$activities = array();

ob_start();
include CSA_PLUGIN_DIR . 'templates/pdf/report-template.php';
$html = ob_get_clean();

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set( 'defaultFont', 'Helvetica' );
$dompdf = new Dompdf( $options );
$dompdf->loadHtml( $html );
$dompdf->setPaper( 'A4', 'portrait' );
$dompdf->render();

$pdf_output = $dompdf->output();

echo "PDF Output Generated: " . strlen( $pdf_output ) . " bytes.\n";
assert( strlen( $pdf_output ) > 5000, "PDF rendering produced invalid size." );
echo "PDF Generation test passed successfully!\n";
