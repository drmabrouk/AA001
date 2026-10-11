<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSA_WhatsApp {

	public static function format_phone_number( $phone ) {
		// Strip non-digit characters except leading plus
		$clean = preg_replace( '/[^\d]/', '', $phone );
		return $clean;
	}

	public static function get_whatsapp_share_url( $report_id ) {
		global $wpdb;

		$table_reports = CSA_DB::get_table_name( 'reports' );
		$report = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_reports WHERE id = %d", $report_id ),
			ARRAY_A
		);

		if ( ! $report ) {
			return '#';
		}

		$branding = CSA_Branding::get_user_branding( $report['user_id'] );
		$phone    = self::format_phone_number( $branding['whatsapp_number'] );

		$pdf_url = CSA_PDF_Generator::get_pdf_download_url( $report_id );

		$message = sprintf(
			"Daily Operations Report - %s\nAcademy: %s\nBranch: %s\nDate: %s\nRef: %s\n\nDownload Report PDF:\n%s",
			$report['report_code'],
			$branding['academy_name'],
			$report['branch_name'],
			$report['report_date'],
			$report['report_code'],
			$pdf_url
		);

		$encoded_message = rawurlencode( $message );

		if ( ! empty( $phone ) ) {
			return 'https://api.whatsapp.com/send?phone=' . $phone . '&text=' . $encoded_message;
		}

		return 'https://api.whatsapp.com/send?text=' . $encoded_message;
	}
}
