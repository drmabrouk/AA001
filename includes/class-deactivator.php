<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Champions_Daily_Operations_Deactivator {

	public static function deactivate() {
		// Do not delete database tables or user reports on deactivation to preserve user data.
		flush_rewrite_rules();
	}
}
