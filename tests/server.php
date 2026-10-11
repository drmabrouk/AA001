<?php
/**
 * Mock WordPress environment runner for Playwright E2E verification.
 */

define( 'ABSPATH', __DIR__ . '/../' );
define( 'CSA_PLUGIN_DIR', __DIR__ . '/../' );
if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}
define( 'CSA_PLUGIN_URL', '/' );
define( 'CSA_VERSION', '1.0.0' );

// Mock Functions
function is_user_logged_in() { return true; }
function wp_get_current_user() {
	return (object) array( 'ID' => 1, 'display_name' => 'John Supervisor', 'roles' => array( 'sports_operations_supervisor' ) );
}
function get_userdata($id) {
	return (object) array( 'ID' => $id, 'display_name' => 'John Supervisor' );
}
function get_current_user_id() { return 1; }
function current_user_can( $cap ) { return false; }
function current_time( $format ) { return date( 'Y-m-d H:i:s' ); }
function esc_html( $s ) { return htmlspecialchars( (string)$s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( (string)$s, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $s ) { return htmlspecialchars( (string)$s, ENT_QUOTES, 'UTF-8' ); }
function esc_textarea( $s ) { return htmlspecialchars( (string)$s, ENT_QUOTES, 'UTF-8' ); }
function get_option( $opt, $def = false ) { return $def; }
function wp_logout_url( $r = '' ) { return '#logout'; }
function get_permalink( $id = 0 ) { return '/'; }
function home_url( $path = '' ) { return $path; }
function sanitize_text_field( $str ) { return trim( strip_tags( (string)$str ) ); }
function sanitize_textarea_field( $str ) { return trim( strip_tags( (string)$str ) ); }
function check_ajax_referer() { return true; }
function wp_create_nonce( $action = '' ) { return 'test_nonce'; }
function wp_verify_nonce( $nonce, $action = '' ) { return true; }
function add_query_arg( $args, $url = '' ) { return $url . '?' . http_build_query( $args ); }
function wp_die( $msg = '' ) { die( $msg ); }
function wp_send_json_success( $data ) {
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'success' => true, 'data' => $data ) );
	exit;
}
function wp_send_json_error( $data ) {
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'success' => false, 'data' => $data ) );
	exit;
}

class MockWPDBServer {
	public $prefix = 'wp_';
	public $insert_id = 101;
	public function get_row( $q, $out = '' ) { return null; }
	public function get_results( $q, $out = '' ) {
		return array(
			array(
				'id' => 1,
				'report_code' => 'CSA-20260330-1001',
				'report_date' => '2026-03-30',
				'branch_name' => 'Dubai Main Branch',
				'prepared_by' => 'John Supervisor',
				'status' => 'finalized'
			)
		);
	}
	public function get_var( $q ) { return null; }
	public function prepare( $q, ...$args ) { return $q; }
	public function insert( $t, $d, $f ) { return true; }
}

$GLOBALS['wpdb'] = new MockWPDBServer();

require_once __DIR__ . '/../includes/class-db.php';
require_once __DIR__ . '/../includes/class-auth.php';
require_once __DIR__ . '/../includes/class-branding.php';
require_once __DIR__ . '/../includes/class-pdf-generator.php';
require_once __DIR__ . '/../includes/class-whatsapp.php';
require_once __DIR__ . '/../includes/class-dashboard-shortcode.php';
require_once __DIR__ . '/../includes/class-ajax-handler.php';

// Handle static CSS/JS assets or mock AJAX requests
$uri = $_SERVER['REQUEST_URI'];

if ( strpos( $uri, 'assets/css/dashboard.css' ) !== false ) {
	header( 'Content-Type: text/css' );
	readfile( __DIR__ . '/../assets/css/dashboard.css' );
	exit;
}
if ( strpos( $uri, 'assets/js/dashboard.js' ) !== false ) {
	header( 'Content-Type: application/javascript' );
	readfile( __DIR__ . '/../assets/js/dashboard.js' );
	exit;
}

if ( strpos( $uri, 'admin-ajax.php' ) !== false ) {
	if ( isset( $_POST['action'] ) && 'csa_save_daily_report' === $_POST['action'] ) {
		CSA_AJAX_Handler::save_daily_report();
	}
	if ( isset( $_POST['action'] ) && 'csa_save_branding_settings' === $_POST['action'] ) {
		CSA_AJAX_Handler::save_branding_settings();
	}
	exit;
}

$dashboard_html = CSA_Dashboard_Shortcode::render( array() );
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Champions Sports Academy - Operations Portal</title>
	<link rel="stylesheet" href="/assets/css/dashboard.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
		var csa_vars = {
			ajax_url: '/admin-ajax.php',
			nonce: 'test_nonce',
			is_user_logged_in: true,
			default_vat_rate: 5.0
		};
	</script>
	<script src="/assets/js/dashboard.js"></script>
</head>
<body style="background:#f3f4f6; margin:0; padding:20px;">
	<?php echo $dashboard_html; ?>
</body>
</html>
