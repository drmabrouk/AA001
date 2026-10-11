<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

class CSA_PDF_Generator {

	public static function generate_pdf( $report_id ) {
		global $wpdb;

		$table_reports = CSA_DB::get_table_name( 'reports' );
		$report = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_reports WHERE id = %d", $report_id ),
			ARRAY_A
		);

		if ( ! $report ) {
			return new WP_Error( 'report_not_found', 'Report not found.' );
		}

		// Decode Branding Snapshot or fall back to user branding
		if ( ! empty( $report['branding_snapshot'] ) ) {
			$branding = json_decode( $report['branding_snapshot'], true );
		} else {
			$branding = CSA_Branding::get_user_branding( $report['user_id'] );
		}

		// Financials
		$table_fin  = CSA_DB::get_table_name( 'financials' );
		$financials = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_fin WHERE report_id = %d", $report_id ),
			ARRAY_A
		);

		// Staff Records
		$table_staff   = CSA_DB::get_table_name( 'staff_attendance' );
		$staff_records = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_staff WHERE report_id = %d", $report_id ),
			ARRAY_A
		);

		// Sports Records
		$table_sports   = CSA_DB::get_table_name( 'sports_attendance' );
		$table_athletes = CSA_DB::get_table_name( 'athlete_attendance' );
		$sports_records = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_sports WHERE report_id = %d", $report_id ),
			ARRAY_A
		);

		if ( ! empty( $sports_records ) ) {
			foreach ( $sports_records as &$sp ) {
				if ( 'individual' === $sp['entry_mode'] ) {
					$sp['athletes'] = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM $table_athletes WHERE sports_attendance_id = %d", $sp['id'] ),
						ARRAY_A
					);
				}
			}
		}

		// Activities
		$table_act  = CSA_DB::get_table_name( 'daily_activities' );
		$activities = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table_act WHERE report_id = %d", $report_id ),
			ARRAY_A
		);

		$currency = ! empty( $branding['default_currency'] ) ? $branding['default_currency'] : 'AED';

		// Render Template HTML
		ob_start();
		include CSA_PLUGIN_DIR . 'templates/pdf/report-template.php';
		$html = ob_get_clean();

		// Configure Dompdf
		$options = new Options();
		$options->set( 'isRemoteEnabled', true );
		$options->set( 'defaultFont', 'Helvetica' );

		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();

		return $dompdf->output();
	}

	public static function get_pdf_download_url( $report_id ) {
		$expires   = time() + ( 7 * DAY_IN_SECONDS ); // 7 days link validity
		$salt      = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'csa_secret_key';
		$token_raw = $report_id . '|' . $expires . '|' . $salt;
		$token     = wp_hash( $token_raw );

		return add_query_arg(
			array(
				'csa_action' => 'download_pdf',
				'report_id'  => $report_id,
				'expires'    => $expires,
				'token'      => $token,
			),
			home_url( '/' )
		);
	}

	public static function handle_pdf_download_request() {
		if ( isset( $_GET['csa_action'] ) && 'download_pdf' === $_GET['csa_action'] && isset( $_GET['report_id'] ) ) {
			$report_id = (int) $_GET['report_id'];
			$expires   = isset( $_GET['expires'] ) ? (int) $_GET['expires'] : 0;
			$token     = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';

			$is_authorized = false;

			// Check secure expiring token (for WhatsApp shared links or external viewing)
			if ( $expires > 0 && ! empty( $token ) ) {
				if ( time() <= $expires ) {
					$salt          = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'csa_secret_key';
					$expected_hash = wp_hash( $report_id . '|' . $expires . '|' . $salt );
					if ( hash_equals( $expected_hash, $token ) ) {
						$is_authorized = true;
					}
				}
			}

			// Check standard logged in user ownership if token not present or expired
			if ( ! $is_authorized ) {
				$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';
				if ( is_user_logged_in() && CSA_Auth::user_can_manage_report( get_current_user_id(), $report_id ) ) {
					$is_authorized = true;
				}
			}

			if ( ! $is_authorized ) {
				wp_die( 'Security check failed, download link expired, or access denied.', 'Access Denied', array( 'response' => 403 ) );
			}

			$pdf_binary = self::generate_pdf( $report_id );

			if ( is_wp_error( $pdf_binary ) ) {
				wp_die( $pdf_binary->get_error_message() );
			}

			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: inline; filename="CSA-Daily-Report-' . $report_id . '.pdf"' );
			header( 'Content-Length: ' . strlen( $pdf_binary ) );

			echo $pdf_binary;
			exit;
		}
	}
}
