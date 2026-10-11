<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSA_Auth {

	public static function restrict_admin_access() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( current_user_can( 'sports_operations_supervisor' ) && ! current_user_can( 'administrator' ) ) {
			$dashboard_page_id = get_option( 'csa_dashboard_page_id' );
			$redirect_url      = $dashboard_page_id ? get_permalink( $dashboard_page_id ) : home_url( '/' );
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	public static function hide_admin_bar_for_supervisors() {
		if ( current_user_can( 'sports_operations_supervisor' ) && ! current_user_can( 'administrator' ) ) {
			show_admin_bar( false );
		}
	}

	public static function user_can_manage_report( $user_id, $report_id ) {
		if ( ! $user_id || ! $report_id ) {
			return false;
		}

		// Administrators can manage all reports
		if ( user_can( $user_id, 'administrator' ) ) {
			return true;
		}

		global $wpdb;
		$table_reports = CSA_DB::get_table_name( 'reports' );
		$report_owner = $wpdb->get_var(
			$wpdb->prepare( "SELECT user_id FROM $table_reports WHERE id = %d", $report_id )
		);

		if ( null === $report_owner ) {
			return false;
		}

		return (int) $report_owner === (int) $user_id;
	}

	public static function render_login_form() {
		$error_msg = '';
		if ( isset( $_POST['csa_login_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['csa_login_nonce'] ), 'csa_login_action' ) ) {
			$creds = array(
				'user_login'    => sanitize_text_field( $_POST['log'] ),
				'user_password' => $_POST['pwd'],
				'remember'      => isset( $_POST['rememberme'] ),
			);

			$user = wp_signon( $creds, is_ssl() );

			if ( is_wp_error( $user ) ) {
				$error_msg = $user->get_error_message();
			} else {
				$dashboard_id = get_option( 'csa_dashboard_page_id' );
				$redirect     = $dashboard_id ? get_permalink( $dashboard_id ) : home_url( '/' );
				wp_safe_redirect( $redirect );
				exit;
			}
		}

		if ( function_exists( 'wp_style_is' ) && ! wp_style_is( 'csa-dashboard-style', 'enqueued' ) ) {
			wp_enqueue_style( 'csa-dashboard-style', CSA_PLUGIN_URL . 'assets/css/dashboard.css', array(), CSA_VERSION );
		}

		ob_start();
		?>
		<link rel="stylesheet" href="<?php echo esc_url( CSA_PLUGIN_URL . 'assets/css/dashboard.css' ); ?>">
		<div class="csa-login-wrapper">
			<div class="csa-login-card">
				<div class="csa-login-header">
					<div class="csa-brand-badge">CHAMPIONS SPORTS ACADEMY</div>
					<h2>Reports Center</h2>
					<p>Please sign in to access your daily reporting dashboard</p>
				</div>

				<?php if ( ! empty( $error_msg ) ) : ?>
					<div class="csa-alert csa-alert-danger">
						<?php echo esc_html( $error_msg ); ?>
					</div>
				<?php endif; ?>

				<form method="post" class="csa-login-form">
					<?php
					if ( function_exists( 'wp_nonce_field' ) ) {
						wp_nonce_field( 'csa_login_action', 'csa_login_nonce' );
					} else {
						echo '<input type="hidden" name="csa_login_nonce" value="test_nonce">';
					}
					?>
					<div class="csa-form-group csa-floating-group">
						<input type="text" name="log" id="csa_user_login" class="csa-form-control" required placeholder=" " autocomplete="username">
						<label for="csa_user_login">Username or Email Address</label>
					</div>

					<div class="csa-form-group csa-floating-group">
						<input type="password" name="pwd" id="csa_user_pass" class="csa-form-control" required placeholder=" " autocomplete="current-password">
						<label for="csa_user_pass">Password</label>
					</div>

					<div class="csa-form-options">
						<label class="csa-checkbox-label">
							<input type="checkbox" name="rememberme" value="forever"> Remember Me
						</label>
					</div>

					<button type="submit" class="csa-btn csa-btn-primary csa-btn-block">Sign In to Dashboard</button>
				</form>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
