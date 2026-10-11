<?php
/**
 * Test script for WhatsApp share URL generation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

require_once __DIR__ . '/../includes/class-whatsapp.php';

$phone = "+971 (50) 123-4567";
$formatted = CSA_WhatsApp::format_phone_number( $phone );
assert( $formatted === "971501234567", "Phone number formatting failed" );

echo "WhatsApp Phone Formatting Test Passed: $formatted\n";
