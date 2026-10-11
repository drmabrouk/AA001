<?php
/**
 * Test script for Auth & Branding functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

class MockWPDBAuth {
	public $prefix = 'wp_';
	public function get_row( $query, $output ) {
		return null;
	}
	public function get_var( $query ) {
		return null;
	}
	public function prepare( $query, ...$args ) {
		return $query;
	}
}

$GLOBALS['wpdb'] = new MockWPDBAuth();

if ( ! function_exists( 'get_userdata' ) ) {
	function get_userdata( $id ) {
		return (object) array( 'display_name' => 'John Supervisor' );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $opt, $default = false ) {
		if ( 'csa_default_academy_name' === $opt ) return 'Champions Sports Academy';
		return $default;
	}
}

require_once __DIR__ . '/../includes/class-db.php';
require_once __DIR__ . '/../includes/class-auth.php';
require_once __DIR__ . '/../includes/class-branding.php';

echo "=== Auth & Branding Test ===\n";

// Test default branding output
$default_branding = CSA_Branding::get_user_branding( 123 );
assert( $default_branding['academy_name'] === 'Champions Sports Academy', 'Default academy name mismatch' );
assert( $default_branding['default_currency'] === 'AED', 'Default currency mismatch' );

$snapshot = CSA_Branding::create_branding_snapshot( 123 );
$decoded  = json_decode( $snapshot, true );
assert( $decoded['academy_name'] === 'Champions Sports Academy', 'Snapshot JSON decode failed' );

echo "Auth & Branding logic verified successfully!\n";
