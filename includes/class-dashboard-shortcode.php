<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSA_Dashboard_Shortcode {

	public static function render( $atts ) {
		if ( ! is_user_logged_in() ) {
			return CSA_Auth::render_login_form();
		}

		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;
		$branding     = CSA_Branding::get_user_branding( $user_id );

		global $wpdb;
		$table_reports = CSA_DB::get_table_name( 'reports' );

		// Retrieve reports for current user (or all if administrator)
		if ( current_user_can( 'administrator' ) ) {
			$reports = $wpdb->get_results( "SELECT * FROM $table_reports ORDER BY report_date DESC, id DESC LIMIT 50", ARRAY_A );
		} else {
			$reports = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_reports WHERE user_id = %d ORDER BY report_date DESC, id DESC LIMIT 50", $user_id ), ARRAY_A );
		}

		$total_reports = count( $reports );
		$today_date    = current_time( 'Y-m-d' );

		ob_start();
		?>
		<div class="csa-dashboard-container">
			<div class="csa-uae-accent">
				<div class="csa-uae-red"></div>
				<div class="csa-uae-green"></div>
				<div class="csa-uae-white"></div>
				<div class="csa-uae-black"></div>
			</div>

			<!-- Dashboard Header -->
			<div class="csa-dashboard-header">
				<div class="csa-header-title">
					<div class="csa-brand-badge"><?php echo esc_html( $branding['academy_name'] ); ?></div>
					<h1>Daily Operations Dashboard</h1>
					<p style="margin: 4px 0 0 0; color: #6b7280; font-size: 13px;">Branch: <strong><?php echo esc_html( $branding['branch_name'] ); ?></strong> | Today: <strong><?php echo esc_html( $today_date ); ?></strong></p>
				</div>
				<div class="csa-header-user-info">
					<div class="csa-user-badge">
						<div class="csa-user-name"><?php echo esc_html( $current_user->display_name ); ?></div>
						<div class="csa-user-role"><?php echo esc_html( implode( ', ', $current_user->roles ) ); ?></div>
					</div>
					<a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" class="csa-btn csa-btn-secondary csa-btn-sm">Sign Out</a>
				</div>
			</div>

			<!-- Navigation Tabs -->
			<div class="csa-nav-tabs">
				<button type="button" class="csa-tab-btn csa-main-tab-btn active" data-tab="csa_tab_overview">Overview & Stats</button>
				<button type="button" class="csa-tab-btn csa-main-tab-btn" data-tab="csa_tab_create">Create New Daily Report</button>
				<button type="button" class="csa-tab-btn csa-main-tab-btn" data-tab="csa_tab_archive">Report Archive (<?php echo (int) $total_reports; ?>)</button>
				<button type="button" class="csa-tab-btn csa-main-tab-btn" data-tab="csa_tab_settings">Branding & Settings</button>
			</div>

			<!-- TAB 1: OVERVIEW -->
			<div id="csa_tab_overview" class="csa-main-tab-content active">
				<div class="csa-stats-grid">
					<div class="csa-stat-card">
						<div class="csa-stat-label">Total Reports Created</div>
						<div class="csa-stat-value"><?php echo (int) $total_reports; ?></div>
					</div>
					<div class="csa-stat-card">
						<div class="csa-stat-label">Configured Branch</div>
						<div class="csa-stat-value" style="font-size: 20px;"><?php echo esc_html( $branding['branch_name'] ); ?></div>
					</div>
					<div class="csa-stat-card">
						<div class="csa-stat-label">Default Currency</div>
						<div class="csa-stat-value"><?php echo esc_html( $branding['default_currency'] ); ?></div>
					</div>
				</div>

				<div class="csa-section-card">
					<h3>
						<span>Recent Daily Reports</span>
						<button type="button" class="csa-btn csa-btn-primary csa-btn-sm" onclick="jQuery('.csa-tab-btn[data-tab=\'csa_tab_create\']').click();">+ Create Report</button>
					</h3>
					<?php if ( empty( $reports ) ) : ?>
						<p style="color: #6b7280; font-style: italic;">No reports found. Click "Create New Daily Report" to begin.</p>
					<?php else : ?>
						<div class="csa-table-responsive">
							<table class="csa-table">
								<thead>
									<tr>
										<th>Report Code</th>
										<th>Date</th>
										<th>Branch</th>
										<th>Prepared By</th>
										<th>Status</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( array_slice( $reports, 0, 5 ) as $rep ) : ?>
										<tr>
											<td><strong><?php echo esc_html( $rep['report_code'] ); ?></strong></td>
											<td><?php echo esc_html( $rep['report_date'] ); ?></td>
											<td><?php echo esc_html( $rep['branch_name'] ); ?></td>
											<td><?php echo esc_html( $rep['prepared_by'] ); ?></td>
											<td><span style="background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:4px; font-size:12px; font-weight:600;"><?php echo esc_html( strtoupper( $rep['status'] ) ); ?></span></td>
											<td>
												<a href="<?php echo esc_url( CSA_PDF_Generator::get_pdf_download_url( $rep['id'] ) ); ?>" class="csa-btn csa-btn-primary csa-btn-sm" target="_blank">PDF Download</a>
												<a href="<?php echo esc_url( CSA_WhatsApp::get_whatsapp_share_url( $rep['id'] ) ); ?>" class="csa-btn csa-btn-success csa-btn-sm" target="_blank">WhatsApp Share</a>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- TAB 2: CREATE NEW DAILY REPORT -->
			<div id="csa_tab_create" class="csa-main-tab-content">
				<form id="csa_daily_report_form">
					<div id="csa_save_status_msg"></div>

					<!-- Form Section Navigation Sub-Tabs -->
					<div class="csa-nav-tabs" style="margin-bottom: 20px; font-size: 13px;">
						<button type="button" class="csa-tab-btn csa-sub-tab-btn active" data-tab="csa_section_a">Sec A: Info</button>
						<button type="button" class="csa-tab-btn csa-sub-tab-btn" data-tab="csa_section_b">Sec B: Financials</button>
						<button type="button" class="csa-tab-btn csa-sub-tab-btn" data-tab="csa_section_c">Sec C: Staff</button>
						<button type="button" class="csa-tab-btn csa-sub-tab-btn" data-tab="csa_section_d">Sec D: Sports & Athletes</button>
						<button type="button" class="csa-tab-btn csa-sub-tab-btn" data-tab="csa_section_e">Sec E: Activities</button>
						<button type="button" class="csa-tab-btn csa-sub-tab-btn" data-tab="csa_section_f">Sec F: Notes</button>
						<button type="button" class="csa-tab-btn csa-sub-tab-btn" data-tab="csa_section_g">Sec G: Review & Submit</button>
					</div>

					<!-- SECTION A: Report Information -->
					<div id="csa_section_a" class="csa-sub-tab-content active">
						<div class="csa-section-card">
							<h3>Section A: Report Information</h3>
							<div class="csa-grid-3">
								<div class="csa-floating-group">
									<input type="date" name="report_date" id="csa_report_date" class="csa-form-control" value="<?php echo esc_attr( $today_date ); ?>" required placeholder=" ">
									<label for="csa_report_date">Report Date *</label>
								</div>
								<div class="csa-floating-group">
									<input type="text" name="academy_name" id="csa_academy_name" class="csa-form-control" value="<?php echo esc_attr( $branding['academy_name'] ); ?>" required placeholder=" ">
									<label for="csa_academy_name">Academy Name *</label>
								</div>
								<div class="csa-floating-group">
									<input type="text" name="branch_name" id="csa_branch_name" class="csa-form-control" value="<?php echo esc_attr( $branding['branch_name'] ); ?>" required placeholder=" ">
									<label for="csa_branch_name">Branch Name *</label>
								</div>
							</div>

							<div class="csa-grid-2">
								<div class="csa-floating-group">
									<input type="text" name="prepared_by" id="csa_prepared_by" class="csa-form-control" value="<?php echo esc_attr( $branding['signature_info'] ); ?>" required placeholder=" ">
									<label for="csa_prepared_by">Prepared By *</label>
								</div>
								<div class="csa-floating-group">
									<input type="text" name="reporting_period" id="csa_reporting_period" class="csa-form-control" value="Full Day Operations" placeholder=" ">
									<label for="csa_reporting_period">Reporting Period (Optional)</label>
								</div>
							</div>

							<div class="csa-floating-group">
								<textarea name="operational_summary" id="csa_operational_summary" class="csa-form-control" rows="3" placeholder=" "></textarea>
								<label for="csa_operational_summary">General Operational Summary</label>
							</div>

							<div class="csa-floating-group">
								<textarea name="overall_remarks" id="csa_overall_remarks" class="csa-form-control" rows="2" placeholder=" "></textarea>
								<label for="csa_overall_remarks">Overall Remarks</label>
							</div>
						</div>
					</div>

					<!-- SECTION B: Financial Summary -->
					<div id="csa_section_b" class="csa-sub-tab-content">
						<div class="csa-section-card">
							<h3>Section B: Financial Summary (<?php echo esc_html( $branding['default_currency'] ); ?>)</h3>
							<div class="csa-grid-3">
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="opening_cash" id="csa_opening_cash" class="csa-form-control csa-fin-calc-trigger" value="0.00" placeholder=" ">
									<label for="csa_opening_cash">Opening Cash Balance</label>
								</div>
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="cash_collections" id="csa_cash_collections" class="csa-form-control csa-fin-calc-trigger" value="0.00" placeholder=" ">
									<label for="csa_cash_collections">Total Cash Collections</label>
								</div>
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="bank_electronic" id="csa_bank_electronic" class="csa-form-control csa-fin-calc-trigger" value="0.00" placeholder=" ">
									<label for="csa_bank_electronic">Bank & Electronic Payments</label>
								</div>
							</div>

							<div class="csa-grid-3">
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="other_income" id="csa_other_income" class="csa-form-control csa-fin-calc-trigger" value="0.00" placeholder=" ">
									<label for="csa_other_income">Other Income</label>
								</div>
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="discounts" id="csa_discounts" class="csa-form-control csa-fin-calc-trigger" value="0.00" placeholder=" ">
									<label for="csa_discounts">Discounts</label>
								</div>
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="refunds" id="csa_refunds" class="csa-form-control csa-fin-calc-trigger" value="0.00" placeholder=" ">
									<label for="csa_refunds">Refunds</label>
								</div>
							</div>

							<div class="csa-grid-3">
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="expenses" id="csa_expenses" class="csa-form-control csa-fin-calc-trigger" value="0.00" placeholder=" ">
									<label for="csa_expenses">Expenses</label>
								</div>
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="vat_rate" id="csa_vat_rate" class="csa-form-control csa-fin-calc-trigger" value="5.00" placeholder=" ">
									<label for="csa_vat_rate">VAT Rate (%)</label>
								</div>
								<div class="csa-floating-group">
									<input type="number" step="0.01" name="outstanding_payments" id="csa_outstanding_payments" class="csa-form-control" value="0.00" placeholder=" ">
									<label for="csa_outstanding_payments">Outstanding Payments</label>
								</div>
							</div>

							<hr style="margin: 20px 0; border: none; border-top: 1px solid var(--csa-border);">

							<!-- Auto Calculated Totals Display -->
							<div class="csa-grid-4">
								<div class="csa-floating-group">
									<input type="text" id="csa_calc_total_revenue" class="csa-form-control" readonly value="0.00" style="background:#f3f4f6; font-weight:700;">
									<label>Total Gross Revenue</label>
								</div>
								<div class="csa-floating-group">
									<input type="text" id="csa_calc_vat_amount" class="csa-form-control" readonly value="0.00" style="background:#f3f4f6; font-weight:700;">
									<label>Calculated VAT Amount</label>
								</div>
								<div class="csa-floating-group">
									<input type="text" id="csa_calc_net_daily_income" class="csa-form-control" readonly value="0.00" style="background:#f3f4f6; font-weight:700; color:#10b981;">
									<label>Net Daily Income</label>
								</div>
								<div class="csa-floating-group">
									<input type="text" id="csa_calc_closing_cash" class="csa-form-control" readonly value="0.00" style="background:#f3f4f6; font-weight:700;">
									<label>Closing Cash Balance</label>
								</div>
							</div>

							<div class="csa-floating-group">
								<textarea name="financial_notes" id="csa_financial_notes" class="csa-form-control" rows="2" placeholder=" "></textarea>
								<label for="csa_financial_notes">Financial Notes / Adjustments</label>
							</div>
						</div>
					</div>

					<!-- SECTION C: Staff Attendance -->
					<div id="csa_section_c" class="csa-sub-tab-content">
						<div class="csa-section-card">
							<h3>
								<span>Section C: Staff Attendance</span>
								<button type="button" id="csa_add_staff_btn" class="csa-btn csa-btn-primary csa-btn-sm">+ Add Staff Member</button>
							</h3>
							<div class="csa-table-responsive">
								<table class="csa-table">
									<thead>
										<tr>
											<th>Staff Member Name</th>
											<th>Job Title / Role</th>
											<th>Attendance Status</th>
											<th>Arrival Time</th>
											<th>Departure Time</th>
											<th>Operational Notes</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody id="csa_staff_tbody">
										<tr>
											<td><input type="text" name="staff[0][name]" class="csa-form-control" placeholder="Staff Name" required></td>
											<td><input type="text" name="staff[0][job_title]" class="csa-form-control" placeholder="Head Coach"></td>
											<td>
												<select name="staff[0][status]" class="csa-form-control">
													<option value="Present">Present</option>
													<option value="Late">Late</option>
													<option value="Absent">Absent</option>
													<option value="On Leave">On Leave</option>
													<option value="Excused Absence">Excused Absence</option>
												</select>
											</td>
											<td><input type="time" name="staff[0][arrival_time]" class="csa-form-control"></td>
											<td><input type="time" name="staff[0][departure_time]" class="csa-form-control"></td>
											<td><input type="text" name="staff[0][notes]" class="csa-form-control" placeholder="Predefined or custom notes"></td>
											<td><button type="button" class="csa-btn csa-btn-danger csa-btn-sm csa-remove-row-btn">&times;</button></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<!-- SECTION D: Sports & Athletes -->
					<div id="csa_section_d" class="csa-sub-tab-content">
						<div style="margin-bottom: 16px; background:#e0f2fe; color:#0369a1; padding:12px; border-radius:6px; font-size:13px;">
							<strong>Note:</strong> Each sports program can be reported using either <strong>Individual Athlete Records</strong> or <strong>Summary Numerical Totals</strong>. Unused sports programs will automatically be omitted from the compiled PDF report.
						</div>

						<?php
						$default_sports = array(
							'Swimming', 'Basketball', 'Football', 'Volleyball', 'Tennis', 'Cricket', 'Gymnastics', 'Athletics'
						);
						foreach ( $default_sports as $s_idx => $sport_name ) :
						?>
							<div class="csa-section-card csa-sport-block-card">
								<h3>
									<span>Sport Program #<?php echo ($s_idx + 1); ?>: <?php echo esc_html( $sport_name ); ?></span>
									<label style="font-size:12px; font-weight:normal; display:flex; align-items:center; gap:8px;">
										<input type="checkbox" name="sports[<?php echo $s_idx; ?>][is_active]" value="1"> Enable This Sport in Report
									</label>
								</h3>

								<input type="hidden" name="sports[<?php echo $s_idx; ?>][sport_name]" value="<?php echo esc_attr( $sport_name ); ?>">

								<div class="csa-grid-3">
									<div class="csa-floating-group">
										<input type="text" name="sports[<?php echo $s_idx; ?>][coach_name]" class="csa-form-control" placeholder=" ">
										<label>Coach / Trainer Name</label>
									</div>
									<div class="csa-floating-group">
										<input type="text" name="sports[<?php echo $s_idx; ?>][session_time]" class="csa-form-control" placeholder=" ">
										<label>Session Scheduled Time</label>
									</div>
									<div class="csa-floating-group">
										<select name="sports[<?php echo $s_idx; ?>][entry_mode]" class="csa-form-control csa-sport-mode-select">
											<option value="summary">Summary-Only Numerical Records</option>
											<option value="individual">Individual Athlete Records</option>
										</select>
										<label>Reporting Method</label>
									</div>
								</div>

								<!-- Numerical Summary Mode Fields -->
								<div class="csa-numerical-summary-wrapper">
									<div class="csa-grid-4">
										<div class="csa-floating-group">
											<input type="number" name="sports[<?php echo $s_idx; ?>][expected_count]" class="csa-form-control" value="0" placeholder=" ">
											<label>Expected Athletes</label>
										</div>
										<div class="csa-floating-group">
											<input type="number" name="sports[<?php echo $s_idx; ?>][present_count]" class="csa-form-control" value="0" placeholder=" ">
											<label>Present Athletes</label>
										</div>
										<div class="csa-floating-group">
											<input type="number" name="sports[<?php echo $s_idx; ?>][absent_count]" class="csa-form-control" value="0" placeholder=" ">
											<label>Absent Athletes</label>
										</div>
										<div class="csa-floating-group">
											<input type="number" name="sports[<?php echo $s_idx; ?>][late_count]" class="csa-form-control" value="0" placeholder=" ">
											<label>Late Athletes</label>
										</div>
									</div>
								</div>

								<!-- Individual Athletes Mode Fields -->
								<div class="csa-individual-athletes-wrapper" style="display:none; margin-top:16px;">
									<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
										<span style="font-weight:600; font-size:14px;">Individual Athlete List</span>
										<button type="button" class="csa-btn csa-btn-primary csa-btn-sm csa-add-athlete-btn" data-sport-index="<?php echo $s_idx; ?>">+ Add Athlete</button>
									</div>
									<table class="csa-table">
										<thead>
											<tr>
												<th>Athlete Name</th>
												<th>Attendance Status</th>
												<th>Absence / Late Reason</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody class="csa-athletes-tbody">
											<!-- Dynamic rows added here -->
										</tbody>
									</table>
								</div>

								<div class="csa-floating-group" style="margin-top:12px;">
									<input type="text" name="sports[<?php echo $s_idx; ?>][program_notes]" class="csa-form-control" placeholder=" ">
									<label>Program / Session Notes</label>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- SECTION E: Daily Activities -->
					<div id="csa_section_e" class="csa-sub-tab-content">
						<div class="csa-section-card">
							<h3>
								<span>Section E: Daily Activities & Operational Events</span>
								<button type="button" id="csa_add_activity_btn" class="csa-btn csa-btn-primary csa-btn-sm">+ Add Operational Activity</button>
							</h3>
							<div id="csa_activities_container">
								<!-- Dynamic activities container -->
							</div>
						</div>
					</div>

					<!-- SECTION F: General Notes & Recommendations -->
					<div id="csa_section_f" class="csa-sub-tab-content">
						<div class="csa-section-card">
							<h3>Section F: General Notes & Recommendations</h3>
							<p style="font-size:13px; color:#6b7280; margin-bottom:16px;">Select optional predefined operational observations or enter custom notes below.</p>

							<div class="csa-grid-2" style="margin-bottom:20px;">
								<label class="csa-checkbox-label">
									<input type="checkbox" name="predefined_notes[]" value="All scheduled training sessions completed as planned."> All scheduled training sessions completed as planned.
								</label>
								<label class="csa-checkbox-label">
									<input type="checkbox" name="predefined_notes[]" value="Equipment inspection completed without major defects."> Equipment inspection completed without major defects.
								</label>
								<label class="csa-checkbox-label">
									<input type="checkbox" name="predefined_notes[]" value="Facility maintenance requested for court/pool area."> Facility maintenance requested for court/pool area.
								</label>
								<label class="csa-checkbox-label">
									<input type="checkbox" name="predefined_notes[]" value="Parent inquiries regarding new registration handled promptly."> Parent inquiries regarding new registration handled.
								</label>
							</div>

							<div class="csa-floating-group">
								<textarea name="custom_general_notes" id="csa_custom_general_notes" class="csa-form-control" rows="4" placeholder=" "></textarea>
								<label for="csa_custom_general_notes">Additional Custom Operational Notes & Recommendations</label>
							</div>
						</div>
					</div>

					<!-- SECTION G: Review & Submit -->
					<div id="csa_section_g" class="csa-sub-tab-content">
						<div class="csa-section-card">
							<h3>Section G: Report Review & Confirmation</h3>
							<p>Please review your daily report totals and details before saving and generating the final PDF.</p>

							<button type="button" id="csa_review_report_btn" class="csa-btn csa-btn-secondary" style="margin-bottom:16px;">Click Here to Validate & Refresh Summary</button>

							<div id="csa_review_summary_box"></div>

							<div style="margin-top:24px;">
								<button type="submit" class="csa-btn csa-btn-success csa-btn-block" style="font-size:16px; padding:14px;">Save Daily Report & Finalize PDF</button>
							</div>
						</div>
					</div>
				</form>
			</div>

			<!-- TAB 3: REPORT ARCHIVE -->
			<div id="csa_tab_archive" class="csa-main-tab-content">
				<div class="csa-section-card">
					<h3>Report Archive & History</h3>
					<div class="csa-grid-3" style="margin-bottom:20px;">
						<div class="csa-floating-group">
							<input type="date" id="csa_archive_date_filter" class="csa-form-control" placeholder=" ">
							<label for="csa_archive_date_filter">Filter by Date</label>
						</div>
						<div class="csa-floating-group">
							<input type="text" id="csa_archive_branch_filter" class="csa-form-control" placeholder=" ">
							<label for="csa_archive_branch_filter">Filter by Branch</label>
						</div>
						<div style="display:flex; align-items:center;">
							<button type="button" id="csa_archive_search_btn" class="csa-btn csa-btn-primary" style="width:100%; height:48px;">Apply Filters</button>
						</div>
					</div>

					<div class="csa-table-responsive">
						<table class="csa-table">
							<thead>
								<tr>
									<th>Report Code</th>
									<th>Date</th>
									<th>Branch</th>
									<th>Prepared By</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php if ( empty( $reports ) ) : ?>
									<tr><td colspan="5" style="text-align:center;">No reports recorded yet.</td></tr>
								<?php else : ?>
									<?php foreach ( $reports as $rep ) : ?>
										<tr class="csa-report-row" data-date="<?php echo esc_attr( $rep['report_date'] ); ?>" data-branch="<?php echo esc_attr( $rep['branch_name'] ); ?>">
											<td><strong><?php echo esc_html( $rep['report_code'] ); ?></strong></td>
											<td><?php echo esc_html( $rep['report_date'] ); ?></td>
											<td><?php echo esc_html( $rep['branch_name'] ); ?></td>
											<td><?php echo esc_html( $rep['prepared_by'] ); ?></td>
											<td>
												<a href="<?php echo esc_url( CSA_PDF_Generator::get_pdf_download_url( $rep['id'] ) ); ?>" class="csa-btn csa-btn-primary csa-btn-sm" target="_blank">PDF Download</a>
												<a href="<?php echo esc_url( CSA_WhatsApp::get_whatsapp_share_url( $rep['id'] ) ); ?>" class="csa-btn csa-btn-success csa-btn-sm" target="_blank">WhatsApp Share</a>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- TAB 4: BRANDING & SETTINGS -->
			<div id="csa_tab_settings" class="csa-main-tab-content">
				<div class="csa-section-card">
					<h3>Personal Branding & Organization Settings</h3>
					<div id="csa_branding_status_msg"></div>

					<form id="csa_branding_settings_form" enctype="multipart/form-data">
						<div class="csa-grid-2">
							<div class="csa-floating-group">
								<input type="text" name="academy_name" class="csa-form-control" value="<?php echo esc_attr( $branding['academy_name'] ); ?>" required placeholder=" ">
								<label>Academy / Organization Name</label>
							</div>
							<div class="csa-floating-group">
								<input type="text" name="branch_name" class="csa-form-control" value="<?php echo esc_attr( $branding['branch_name'] ); ?>" required placeholder=" ">
								<label>Branch Name</label>
							</div>
						</div>

						<div class="csa-grid-2">
							<div class="csa-floating-group">
								<input type="text" name="whatsapp_number" class="csa-form-control" value="<?php echo esc_attr( $branding['whatsapp_number'] ); ?>" placeholder=" ">
								<label>WhatsApp Recipient Number (e.g. +971501234567)</label>
							</div>
							<div class="csa-floating-group">
								<input type="text" name="default_currency" class="csa-form-control" value="<?php echo esc_attr( $branding['default_currency'] ); ?>" required placeholder=" ">
								<label>Default Currency (e.g. AED, USD)</label>
							</div>
						</div>

						<div class="csa-floating-group">
							<input type="text" name="signature_info" class="csa-form-control" value="<?php echo esc_attr( $branding['signature_info'] ); ?>" placeholder=" ">
							<label>Default Prepared By / Supervisor Name</label>
						</div>

						<div class="csa-floating-group">
							<textarea name="contact_info" class="csa-form-control" rows="2" placeholder=" "><?php echo esc_textarea( $branding['contact_info'] ); ?></textarea>
							<label>Branch Contact Details & Address</label>
						</div>

						<div class="csa-floating-group">
							<textarea name="footer_prefs" class="csa-form-control" rows="2" placeholder=" "><?php echo esc_textarea( $branding['footer_prefs'] ); ?></textarea>
							<label>PDF Report Footer Note</label>
						</div>

						<div style="margin-bottom:20px;">
							<label style="display:block; font-size:13px; font-weight:600; margin-bottom:8px;">Academy Logo (PNG, JPG, WebP - max 5MB):</label>
							<?php if ( ! empty( $branding['logo_url'] ) ) : ?>
								<div style="margin-bottom:10px;">
									<img src="<?php echo esc_url( $branding['logo_url'] ); ?>" alt="Academy Logo" style="max-height:60px; border:1px solid #e5e7eb; padding:4px; border-radius:4px;">
								</div>
							<?php endif; ?>
							<input type="file" name="academy_logo" accept="image/png, image/jpeg, image/jpg, image/webp" class="csa-form-control">
						</div>

						<button type="submit" class="csa-btn csa-btn-primary">Save Personal Settings</button>
					</form>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
