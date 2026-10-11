<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Champions_Daily_Operations {

	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->plugin_name = 'champions-daily-operations';
		$this->version     = CSA_VERSION;
	}

	public function run() {
		$this->load_dependencies();
		$this->define_hooks();
	}

	private function load_dependencies() {
		require_once CSA_PLUGIN_DIR . 'includes/class-db.php';
		require_once CSA_PLUGIN_DIR . 'includes/class-auth.php';
		require_once CSA_PLUGIN_DIR . 'includes/class-branding.php';
		require_once CSA_PLUGIN_DIR . 'includes/class-pdf-generator.php';
		require_once CSA_PLUGIN_DIR . 'includes/class-whatsapp.php';
		require_once CSA_PLUGIN_DIR . 'includes/class-dashboard-shortcode.php';
		require_once CSA_PLUGIN_DIR . 'includes/class-ajax-handler.php';
	}

	private function define_hooks() {
		// Restrict supervisor access to wp-admin and hide admin bar
		add_action( 'admin_init', array( 'CSA_Auth', 'restrict_admin_access' ) );
		add_action( 'after_setup_theme', array( 'CSA_Auth', 'hide_admin_bar_for_supervisors' ) );
		add_action( 'template_redirect', array( 'CSA_Auth', 'handle_login_submit' ) );

		// Enqueue scripts & styles for dashboard
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Handle direct PDF downloads and expiring links
		add_action( 'init', array( 'CSA_PDF_Generator', 'handle_pdf_download_request' ) );

		// Register Shortcode
		add_shortcode( 'champions_daily_operations_dashboard', array( 'CSA_Dashboard_Shortcode', 'render' ) );

		// Register AJAX handlers
		CSA_AJAX_Handler::register_hooks();
	}

	public function enqueue_assets() {
		global $post;
		$dashboard_page_id = get_option( 'csa_dashboard_page_id' );

		if ( is_a( $post, 'WP_Post' ) && ( (int) $post->ID === (int) $dashboard_page_id || has_shortcode( $post->post_content, 'champions_daily_operations_dashboard' ) ) ) {
			wp_enqueue_style(
				'google-fonts-cairo',
				'https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap',
				array(),
				null
			);

			wp_enqueue_style(
				'csa-dashboard-style',
				CSA_PLUGIN_URL . 'assets/css/dashboard.css',
				array(),
				$this->version
			);

			wp_enqueue_script(
				'csa-dashboard-script',
				CSA_PLUGIN_URL . 'assets/js/dashboard.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			wp_localize_script(
				'csa-dashboard-script',
				'csa_vars',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'csa_dashboard_nonce' ),
					'is_user_logged_in' => is_user_logged_in(),
					'default_vat_rate'  => get_option( 'csa_default_vat_rate', 5.0 ),
				)
			);
		}
	}
}
