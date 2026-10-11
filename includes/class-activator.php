<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Champions_Daily_Operations_Activator {

	public static function activate() {
		// 1. Initialize Database Tables
		require_once CSA_PLUGIN_DIR . 'includes/class-db.php';
		CSA_DB::init_db();

		// 2. Register Custom Role
		self::register_roles();

		// 3. Create Dashboard Page if not exists
		self::create_dashboard_page();

		// 4. Default Settings
		if ( ! get_option( 'csa_default_academy_name' ) ) {
			update_option( 'csa_default_academy_name', 'Champions Sports Academy' );
		}
		if ( false === get_option( 'csa_default_vat_rate' ) ) {
			update_option( 'csa_default_vat_rate', 5.0 );
		}

		flush_rewrite_rules();
	}

	private static function register_roles() {
		add_role(
			'sports_operations_supervisor',
			__( 'Sports Operations Supervisor', 'champions-daily-operations' ),
			array(
				'read'                      => true,
				'upload_files'              => true,
				'edit_posts'                => false,
				'delete_posts'              => false,
				'manage_csa_daily_reports' => true,
			)
		);

		// Add custom capability to administrator role
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( 'manage_csa_daily_reports' );
			$admin_role->add_cap( 'manage_csa_settings' );
		}
	}

	private static function create_dashboard_page() {
		$page_option_key = 'csa_dashboard_page_id';
		$existing_page_id = get_option( $page_option_key );

		if ( $existing_page_id && get_post( $existing_page_id ) ) {
			return; // Page already exists
		}

		// Search by post_name or content to prevent duplicates
		$query = new WP_Query( array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			's'              => '[champions_daily_operations_dashboard]',
			'posts_per_page' => 1,
		) );

		if ( $query->have_posts() ) {
			$posts = $query->get_posts();
			update_option( $page_option_key, $posts[0]->ID );
			return;
		}

		$page_data = array(
			'post_title'    => __( 'Daily Operations Dashboard', 'champions-daily-operations' ),
			'post_content'  => '[champions_daily_operations_dashboard]',
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'comment_status' => 'closed',
		);

		$page_id = wp_insert_post( $page_data );
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( $page_option_key, $page_id );
		}
	}
}
