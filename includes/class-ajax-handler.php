<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSA_AJAX_Handler {

	public static function register_hooks() {
		add_action( 'wp_ajax_csa_save_daily_report', array( __CLASS__, 'save_daily_report' ) );
		add_action( 'wp_ajax_csa_save_branding_settings', array( __CLASS__, 'save_branding_settings' ) );
	}

	public static function save_daily_report() {
		check_ajax_referer( 'csa_dashboard_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized user.' ) );
		}

		$user_id     = get_current_user_id();
		$report_date = isset( $_POST['report_date'] ) ? sanitize_text_field( $_POST['report_date'] ) : current_time( 'Y-m-d' );
		$academy     = isset( $_POST['academy_name'] ) ? sanitize_text_field( $_POST['academy_name'] ) : 'Champions Sports Academy';
		$branch      = isset( $_POST['branch_name'] ) ? sanitize_text_field( $_POST['branch_name'] ) : 'Main Branch';
		$prepared_by = isset( $_POST['prepared_by'] ) ? sanitize_text_field( $_POST['prepared_by'] ) : '';
		$period      = isset( $_POST['reporting_period'] ) ? sanitize_text_field( $_POST['reporting_period'] ) : '';
		$summary     = isset( $_POST['operational_summary'] ) ? sanitize_textarea_field( $_POST['operational_summary'] ) : '';
		$remarks     = isset( $_POST['overall_remarks'] ) ? sanitize_textarea_field( $_POST['overall_remarks'] ) : '';

		global $wpdb;
		$table_reports = CSA_DB::get_table_name( 'reports' );

		// Generate unique report code e.g. CSA-20260330-8492
		$report_code = 'CSA-' . date( 'Ymd', strtotime( $report_date ) ) . '-' . rand( 1000, 9999 );

		// Branding Snapshot
		$branding_snapshot = CSA_Branding::create_branding_snapshot( $user_id );

		$inserted = $wpdb->insert(
			$table_reports,
			array(
				'report_code'         => $report_code,
				'user_id'             => $user_id,
				'report_date'         => $report_date,
				'academy_name'        => $academy,
				'branch_name'         => $branch,
				'prepared_by'         => $prepared_by,
				'reporting_period'    => $period,
				'operational_summary' => $summary,
				'overall_remarks'     => $remarks,
				'status'              => 'finalized',
				'branding_snapshot'  => $branding_snapshot,
				'created_at'          => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			wp_send_json_error( array( 'message' => 'Failed to save main report record.' ) );
		}

		$report_id = $wpdb->insert_id;

		// Save Financials
		$fin_data = CSA_DB::calculate_financials( array(
			'opening_cash'         => isset( $_POST['opening_cash'] ) ? $_POST['opening_cash'] : 0,
			'cash_collections'     => isset( $_POST['cash_collections'] ) ? $_POST['cash_collections'] : 0,
			'bank_electronic'     => isset( $_POST['bank_electronic'] ) ? $_POST['bank_electronic'] : 0,
			'other_income'         => isset( $_POST['other_income'] ) ? $_POST['other_income'] : 0,
			'discounts'            => isset( $_POST['discounts'] ) ? $_POST['discounts'] : 0,
			'refunds'              => isset( $_POST['refunds'] ) ? $_POST['refunds'] : 0,
			'expenses'             => isset( $_POST['expenses'] ) ? $_POST['expenses'] : 0,
			'vat_rate'             => isset( $_POST['vat_rate'] ) ? $_POST['vat_rate'] : 5,
			'outstanding_payments' => isset( $_POST['outstanding_payments'] ) ? $_POST['outstanding_payments'] : 0,
		) );

		$table_fin = CSA_DB::get_table_name( 'financials' );
		$wpdb->insert(
			$table_fin,
			array(
				'report_id'            => $report_id,
				'opening_cash'         => $fin_data['opening_cash'],
				'cash_collections'     => $fin_data['cash_collections'],
				'bank_electronic'     => $fin_data['bank_electronic'],
				'other_income'         => $fin_data['other_income'],
				'discounts'            => $fin_data['discounts'],
				'refunds'              => $fin_data['refunds'],
				'expenses'             => $fin_data['expenses'],
				'vat_rate'             => $fin_data['vat_rate'],
				'vat_amount'           => $fin_data['vat_amount'],
				'total_revenue'        => $fin_data['total_revenue'],
				'net_daily_income'     => $fin_data['net_daily_income'],
				'closing_cash'         => $fin_data['closing_cash'],
				'outstanding_payments' => $fin_data['outstanding_payments'],
				'financial_notes'      => isset( $_POST['financial_notes'] ) ? sanitize_textarea_field( $_POST['financial_notes'] ) : '',
			),
			array( '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s' )
		);

		// Save Staff Attendance
		if ( ! empty( $_POST['staff'] ) && is_array( $_POST['staff'] ) ) {
			$table_staff = CSA_DB::get_table_name( 'staff_attendance' );
			foreach ( $_POST['staff'] as $st ) {
				if ( empty( $st['name'] ) ) continue;
				$wpdb->insert(
					$table_staff,
					array(
						'report_id'      => $report_id,
						'staff_name'     => sanitize_text_field( $st['name'] ),
						'job_title'      => isset( $st['job_title'] ) ? sanitize_text_field( $st['job_title'] ) : '',
						'status'         => isset( $st['status'] ) ? sanitize_text_field( $st['status'] ) : 'Present',
						'arrival_time'   => isset( $st['arrival_time'] ) ? sanitize_text_field( $st['arrival_time'] ) : '',
						'departure_time' => isset( $st['departure_time'] ) ? sanitize_text_field( $st['departure_time'] ) : '',
						'notes'          => isset( $st['notes'] ) ? sanitize_textarea_field( $st['notes'] ) : '',
					),
					array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
				);
			}
		}

		// Save Sports & Athlete Attendance
		if ( ! empty( $_POST['sports'] ) && is_array( $_POST['sports'] ) ) {
			$table_sports = CSA_DB::get_table_name( 'sports_attendance' );
			$table_athletes = CSA_DB::get_table_name( 'athlete_attendance' );

			foreach ( $_POST['sports'] as $sp ) {
				if ( empty( $sp['is_active'] ) ) continue; // Skip unused sport programs

				$sport_name = sanitize_text_field( $sp['sport_name'] );
				$mode       = isset( $sp['entry_mode'] ) && 'individual' === $sp['entry_mode'] ? 'individual' : 'summary';

				$expected = isset( $sp['expected_count'] ) ? (int) $sp['expected_count'] : 0;
				$present  = isset( $sp['present_count'] ) ? (int) $sp['present_count'] : 0;
				$absent   = isset( $sp['absent_count'] ) ? (int) $sp['absent_count'] : 0;
				$late     = isset( $sp['late_count'] ) ? (int) $sp['late_count'] : 0;

				// If mode is individual, calculate totals from individual athlete entries
				if ( 'individual' === $mode && ! empty( $sp['athletes'] ) && is_array( $sp['athletes'] ) ) {
					$calc_expected = count( $sp['athletes'] );
					$calc_present  = 0;
					$calc_absent   = 0;
					$calc_late     = 0;

					foreach ( $sp['athletes'] as $ath ) {
						if ( empty( $ath['name'] ) ) continue;
						$st = isset( $ath['status'] ) ? $ath['status'] : 'Present';
						if ( 'Present' === $st ) $calc_present++;
						elseif ( 'Absent' === $st ) $calc_absent++;
						elseif ( 'Late' === $st ) $calc_late++;
					}

					$expected = $calc_expected;
					$present  = $calc_present;
					$absent   = $calc_absent;
					$late     = $calc_late;
				}

				$wpdb->insert(
					$table_sports,
					array(
						'report_id'      => $report_id,
						'sport_name'     => $sport_name,
						'coach_name'     => isset( $sp['coach_name'] ) ? sanitize_text_field( $sp['coach_name'] ) : '',
						'session_time'   => isset( $sp['session_time'] ) ? sanitize_text_field( $sp['session_time'] ) : '',
						'expected_count' => $expected,
						'present_count'  => $present,
						'absent_count'   => $absent,
						'late_count'     => $late,
						'session_status' => 'Completed',
						'entry_mode'     => $mode,
						'program_notes'  => isset( $sp['program_notes'] ) ? sanitize_textarea_field( $sp['program_notes'] ) : '',
					),
					array( '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s' )
				);

				$sports_att_id = $wpdb->insert_id;

				if ( 'individual' === $mode && ! empty( $sp['athletes'] ) && is_array( $sp['athletes'] ) ) {
					foreach ( $sp['athletes'] as $ath ) {
						if ( empty( $ath['name'] ) ) continue;
						$wpdb->insert(
							$table_athletes,
							array(
								'sports_attendance_id' => $sports_att_id,
								'athlete_name'         => sanitize_text_field( $ath['name'] ),
								'status'               => isset( $ath['status'] ) ? sanitize_text_field( $ath['status'] ) : 'Present',
								'absence_reason'       => isset( $ath['reason'] ) ? sanitize_text_field( $ath['reason'] ) : '',
							),
							array( '%d', '%s', '%s', '%s' )
						);
					}
				}
			}
		}

		// Save Daily Activities
		if ( ! empty( $_POST['activities'] ) && is_array( $_POST['activities'] ) ) {
			$table_act = CSA_DB::get_table_name( 'daily_activities' );
			foreach ( $_POST['activities'] as $act ) {
				if ( empty( $act['title'] ) ) continue;
				$wpdb->insert(
					$table_act,
					array(
						'report_id'      => $report_id,
						'activity_title' => sanitize_text_field( $act['title'] ),
						'description'    => isset( $act['description'] ) ? sanitize_textarea_field( $act['description'] ) : '',
						'related_sport'  => isset( $act['related_sport'] ) ? sanitize_text_field( $act['related_sport'] ) : '',
						'activity_time'  => isset( $act['time'] ) ? sanitize_text_field( $act['time'] ) : '',
						'staff_involved' => isset( $act['staff_involved'] ) ? sanitize_text_field( $act['staff_involved'] ) : '',
						'outcome'        => isset( $act['outcome'] ) ? sanitize_text_field( $act['outcome'] ) : '',
						'follow_up'      => isset( $act['follow_up'] ) ? sanitize_text_field( $act['follow_up'] ) : '',
					),
					array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
				);
			}
		}

		CSA_DB::log_audit( $user_id, $report_id, 'create_report', 'Report created successfully' );

		wp_send_json_success( array(
			'message'   => 'Daily report saved successfully!',
			'report_id' => $report_id,
		) );
	}

	public static function save_branding_settings() {
		check_ajax_referer( 'csa_dashboard_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized user.' ) );
		}

		$user_id = get_current_user_id();
		$file    = isset( $_FILES['academy_logo'] ) ? $_FILES['academy_logo'] : null;

		$saved = CSA_Branding::save_user_branding( $user_id, $_POST, $file );

		if ( is_wp_error( $saved ) ) {
			wp_send_json_error( array( 'message' => $saved->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Branding preferences saved successfully.' ) );
	}
}
