<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSA_DB {

	public static function get_table_name( $table ) {
		global $wpdb;
		return $wpdb->prefix . 'csa_' . $table;
	}

	public static function init_db() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// 1. Reports Table
		$table_reports = self::get_table_name( 'reports' );
		$sql_reports = "CREATE TABLE $table_reports (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			report_code varchar(50) NOT NULL,
			user_id bigint(20) UNSIGNED NOT NULL,
			report_date date NOT NULL,
			academy_name varchar(255) NOT NULL,
			branch_name varchar(255) NOT NULL,
			prepared_by varchar(255) NOT NULL,
			reporting_period varchar(100) DEFAULT '',
			operational_summary text,
			overall_remarks text,
			status varchar(20) NOT NULL DEFAULT 'finalized',
			branding_snapshot text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY report_date (report_date),
			UNIQUE KEY report_code (report_code)
		) $charset_collate;";
		dbDelta( $sql_reports );

		// 2. Financials Table
		$table_financials = self::get_table_name( 'financials' );
		$sql_financials = "CREATE TABLE $table_financials (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			report_id bigint(20) UNSIGNED NOT NULL,
			opening_cash decimal(12,2) NOT NULL DEFAULT 0.00,
			cash_collections decimal(12,2) NOT NULL DEFAULT 0.00,
			bank_electronic decimal(12,2) NOT NULL DEFAULT 0.00,
			other_income decimal(12,2) NOT NULL DEFAULT 0.00,
			discounts decimal(12,2) NOT NULL DEFAULT 0.00,
			refunds decimal(12,2) NOT NULL DEFAULT 0.00,
			expenses decimal(12,2) NOT NULL DEFAULT 0.00,
			vat_rate decimal(5,2) NOT NULL DEFAULT 5.00,
			vat_amount decimal(12,2) NOT NULL DEFAULT 0.00,
			total_revenue decimal(12,2) NOT NULL DEFAULT 0.00,
			net_daily_income decimal(12,2) NOT NULL DEFAULT 0.00,
			closing_cash decimal(12,2) NOT NULL DEFAULT 0.00,
			outstanding_payments decimal(12,2) NOT NULL DEFAULT 0.00,
			financial_notes text,
			manual_adjustments text,
			PRIMARY KEY  (id),
			KEY report_id (report_id)
		) $charset_collate;";
		dbDelta( $sql_financials );

		// 3. Staff Attendance Table
		$table_staff = self::get_table_name( 'staff_attendance' );
		$sql_staff = "CREATE TABLE $table_staff (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			report_id bigint(20) UNSIGNED NOT NULL,
			staff_name varchar(255) NOT NULL,
			job_title varchar(255) DEFAULT '',
			status varchar(50) NOT NULL DEFAULT 'Present',
			arrival_time varchar(20) DEFAULT '',
			departure_time varchar(20) DEFAULT '',
			late_arrival tinyint(1) DEFAULT 0,
			absence_reason varchar(255) DEFAULT '',
			notes text,
			PRIMARY KEY  (id),
			KEY report_id (report_id)
		) $charset_collate;";
		dbDelta( $sql_staff );

		// 4. Sports Attendance Table
		$table_sports = self::get_table_name( 'sports_attendance' );
		$sql_sports = "CREATE TABLE $table_sports (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			report_id bigint(20) UNSIGNED NOT NULL,
			sport_name varchar(100) NOT NULL,
			coach_name varchar(255) DEFAULT '',
			session_time varchar(100) DEFAULT '',
			expected_count int(11) NOT NULL DEFAULT 0,
			present_count int(11) NOT NULL DEFAULT 0,
			absent_count int(11) NOT NULL DEFAULT 0,
			late_count int(11) NOT NULL DEFAULT 0,
			session_status varchar(100) DEFAULT 'Completed',
			entry_mode varchar(20) NOT NULL DEFAULT 'summary',
			program_notes text,
			PRIMARY KEY  (id),
			KEY report_id (report_id)
		) $charset_collate;";
		dbDelta( $sql_sports );

		// 5. Athlete Attendance Table
		$table_athletes = self::get_table_name( 'athlete_attendance' );
		$sql_athletes = "CREATE TABLE $table_athletes (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			sports_attendance_id bigint(20) UNSIGNED NOT NULL,
			athlete_name varchar(255) NOT NULL,
			status varchar(50) NOT NULL DEFAULT 'Present',
			absence_reason varchar(255) DEFAULT '',
			PRIMARY KEY  (id),
			KEY sports_attendance_id (sports_attendance_id)
		) $charset_collate;";
		dbDelta( $sql_athletes );

		// 6. Daily Activities Table
		$table_activities = self::get_table_name( 'daily_activities' );
		$sql_activities = "CREATE TABLE $table_activities (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			report_id bigint(20) UNSIGNED NOT NULL,
			activity_title varchar(255) NOT NULL,
			description text,
			related_sport varchar(100) DEFAULT '',
			activity_time varchar(20) DEFAULT '',
			staff_involved varchar(255) DEFAULT '',
			outcome text,
			follow_up text,
			notes text,
			PRIMARY KEY  (id),
			KEY report_id (report_id)
		) $charset_collate;";
		dbDelta( $sql_activities );

		// 7. User Branding & Settings Table
		$table_branding = self::get_table_name( 'user_branding' );
		$sql_branding = "CREATE TABLE $table_branding (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			academy_name varchar(255) DEFAULT 'Champions Sports Academy',
			branch_name varchar(255) DEFAULT '',
			logo_url text,
			logo_path text,
			contact_info text,
			whatsapp_number varchar(50) DEFAULT '',
			default_currency varchar(10) DEFAULT 'AED',
			header_prefs text,
			footer_prefs text,
			signature_info text,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY user_id (user_id)
		) $charset_collate;";
		dbDelta( $sql_branding );

		// 8. Audit Logs Table
		$table_audit = self::get_table_name( 'audit_logs' );
		$sql_audit = "CREATE TABLE $table_audit (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			report_id bigint(20) UNSIGNED DEFAULT NULL,
			action_name varchar(100) NOT NULL,
			details text,
			ip_address varchar(50) DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY report_id (report_id)
		) $charset_collate;";
		dbDelta( $sql_audit );
	}

	public static function calculate_financials( $data ) {
		$opening_cash         = isset( $data['opening_cash'] ) ? max( 0, (float) $data['opening_cash'] ) : 0.0;
		$cash_collections     = isset( $data['cash_collections'] ) ? max( 0, (float) $data['cash_collections'] ) : 0.0;
		$bank_electronic     = isset( $data['bank_electronic'] ) ? max( 0, (float) $data['bank_electronic'] ) : 0.0;
		$other_income         = isset( $data['other_income'] ) ? max( 0, (float) $data['other_income'] ) : 0.0;
		$discounts            = isset( $data['discounts'] ) ? max( 0, (float) $data['discounts'] ) : 0.0;
		$refunds              = isset( $data['refunds'] ) ? max( 0, (float) $data['refunds'] ) : 0.0;
		$expenses             = isset( $data['expenses'] ) ? max( 0, (float) $data['expenses'] ) : 0.0;
		$vat_rate             = isset( $data['vat_rate'] ) ? max( 0, (float) $data['vat_rate'] ) : 5.0;
		$outstanding_payments = isset( $data['outstanding_payments'] ) ? max( 0, (float) $data['outstanding_payments'] ) : 0.0;

		// Gross revenue = Total Collections (Cash + Bank/E-payment + Other Income) minus Discounts
		$gross_collections = $cash_collections + $bank_electronic + $other_income;
		$total_revenue     = max( 0, $gross_collections - $discounts );

		// Calculate VAT (included or added based on total revenue)
		// Standard UAE calculation: Net Revenue + VAT = Total Revenue or VAT calculated on taxable total.
		// Formula: VAT Amount = Total Revenue * (vat_rate / 100)
		$vat_amount       = round( $total_revenue * ( $vat_rate / 100 ), 2 );

		// Net Daily Income = Total Revenue - Refunds - Expenses
		$net_daily_income = $total_revenue - $refunds - $expenses;

		// Closing Cash Balance = Opening Cash + Cash Collections - Cash Expenses/Refunds (assuming cash refunds/expenses)
		$closing_cash     = $opening_cash + $cash_collections - $refunds - $expenses;

		return array(
			'opening_cash'         => $opening_cash,
			'cash_collections'     => $cash_collections,
			'bank_electronic'     => $bank_electronic,
			'other_income'         => $other_income,
			'discounts'            => $discounts,
			'refunds'              => $refunds,
			'expenses'             => $expenses,
			'vat_rate'             => $vat_rate,
			'vat_amount'           => $vat_amount,
			'total_revenue'        => $total_revenue,
			'net_daily_income'     => $net_daily_income,
			'closing_cash'         => $closing_cash,
			'outstanding_payments' => $outstanding_payments,
		);
	}

	public static function log_audit( $user_id, $report_id, $action, $details = '' ) {
		global $wpdb;
		$table = self::get_table_name( 'audit_logs' );
		$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';

		$wpdb->insert(
			$table,
			array(
				'user_id'     => $user_id,
				'report_id'   => $report_id,
				'action_name' => $action,
				'details'     => is_array( $details ) || is_object( $details ) ? wp_json_encode( $details ) : $details,
				'ip_address'  => $ip,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
		);
	}
}
