<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSA_Branding {

	public static function get_user_branding( $user_id ) {
		global $wpdb;
		$table = CSA_DB::get_table_name( 'user_branding' );

		$output_type = defined( 'ARRAY_A' ) ? ARRAY_A : 'ARRAY_A';
		$branding = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table WHERE user_id = %d", $user_id ),
			$output_type
		);

		$user_info = get_userdata( $user_id );
		$display_name = $user_info ? $user_info->display_name : 'Authorized Supervisor';

		if ( ! $branding ) {
			return array(
				'user_id'          => $user_id,
				'academy_name'     => get_option( 'csa_default_academy_name', 'Champions Sports Academy' ),
				'branch_name'      => 'Main Branch',
				'logo_url'         => '',
				'logo_path'        => '',
				'contact_info'     => '',
				'whatsapp_number'  => '',
				'default_currency' => 'AED',
				'header_prefs'     => '',
				'footer_prefs'     => 'Champions Sports Academy - Confidential Operational Report',
				'signature_info'   => $display_name,
			);
		}

		return array(
			'user_id'          => $user_id,
			'academy_name'     => ! empty( $branding['academy_name'] ) ? $branding['academy_name'] : 'Champions Sports Academy',
			'branch_name'      => ! empty( $branding['branch_name'] ) ? $branding['branch_name'] : 'Main Branch',
			'logo_url'         => ! empty( $branding['logo_url'] ) ? $branding['logo_url'] : '',
			'logo_path'        => ! empty( $branding['logo_path'] ) ? $branding['logo_path'] : '',
			'contact_info'     => isset( $branding['contact_info'] ) ? $branding['contact_info'] : '',
			'whatsapp_number'  => isset( $branding['whatsapp_number'] ) ? $branding['whatsapp_number'] : '',
			'default_currency' => ! empty( $branding['default_currency'] ) ? $branding['default_currency'] : 'AED',
			'header_prefs'     => isset( $branding['header_prefs'] ) ? $branding['header_prefs'] : '',
			'footer_prefs'     => isset( $branding['footer_prefs'] ) ? $branding['footer_prefs'] : 'Champions Sports Academy - Confidential Operational Report',
			'signature_info'   => ! empty( $branding['signature_info'] ) ? $branding['signature_info'] : $display_name,
		);
	}

	public static function save_user_branding( $user_id, $data, $file = null ) {
		global $wpdb;
		$table = CSA_DB::get_table_name( 'user_branding' );

		$current = self::get_user_branding( $user_id );
		$logo_url  = $current['logo_url'];
		$logo_path = $current['logo_path'];

		// Handle File Upload if provided
		if ( ! empty( $file ) && isset( $file['tmp_name'] ) && ! empty( $file['tmp_name'] ) ) {
			$allowed_types = array( 'image/png', 'image/jpeg', 'image/jpg', 'image/webp' );
			$max_size      = 5 * 1024 * 1024; // 5MB limit

			if ( in_array( $file['type'], $allowed_types, true ) && $file['size'] <= $max_size ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';

				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $file, $upload_overrides );

				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$logo_url  = $movefile['url'];
					$logo_path = $movefile['file'];
				} else {
					return new WP_Error( 'upload_failed', isset( $movefile['error'] ) ? $movefile['error'] : 'File upload failed.' );
				}
			} else {
				return new WP_Error( 'invalid_file', 'Invalid file format or file size exceeds 5MB limit. Please upload PNG, JPG, or WebP image.' );
			}
		}

		$academy_name    = isset( $data['academy_name'] ) ? sanitize_text_field( $data['academy_name'] ) : $current['academy_name'];
		$branch_name     = isset( $data['branch_name'] ) ? sanitize_text_field( $data['branch_name'] ) : $current['branch_name'];
		$whatsapp_number = isset( $data['whatsapp_number'] ) ? sanitize_text_field( $data['whatsapp_number'] ) : $current['whatsapp_number'];
		$currency        = isset( $data['default_currency'] ) ? sanitize_text_field( $data['default_currency'] ) : $current['default_currency'];
		$contact_info    = isset( $data['contact_info'] ) ? sanitize_textarea_field( $data['contact_info'] ) : $current['contact_info'];
		$header_prefs    = isset( $data['header_prefs'] ) ? sanitize_textarea_field( $data['header_prefs'] ) : $current['header_prefs'];
		$footer_prefs    = isset( $data['footer_prefs'] ) ? sanitize_textarea_field( $data['footer_prefs'] ) : $current['footer_prefs'];
		$signature_info  = isset( $data['signature_info'] ) ? sanitize_text_field( $data['signature_info'] ) : $current['signature_info'];

		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE user_id = %d", $user_id ) );

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'academy_name'     => $academy_name,
					'branch_name'      => $branch_name,
					'logo_url'         => $logo_url,
					'logo_path'        => $logo_path,
					'contact_info'     => $contact_info,
					'whatsapp_number'  => $whatsapp_number,
					'default_currency' => $currency,
					'header_prefs'     => $header_prefs,
					'footer_prefs'     => $footer_prefs,
					'signature_info'   => $signature_info,
				),
				array( 'user_id' => $user_id ),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			$wpdb->insert(
				$table,
				array(
					'user_id'          => $user_id,
					'academy_name'     => $academy_name,
					'branch_name'      => $branch_name,
					'logo_url'         => $logo_url,
					'logo_path'        => $logo_path,
					'contact_info'     => $contact_info,
					'whatsapp_number'  => $whatsapp_number,
					'default_currency' => $currency,
					'header_prefs'     => $header_prefs,
					'footer_prefs'     => $footer_prefs,
					'signature_info'   => $signature_info,
				),
				array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);
		}

		return true;
	}

	public static function create_branding_snapshot( $user_id ) {
		$branding = self::get_user_branding( $user_id );
		return function_exists( 'wp_json_encode' ) ? wp_json_encode( $branding ) : json_encode( $branding );
	}
}
