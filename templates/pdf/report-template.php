<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Daily Operations Report - <?php echo esc_html( $report['report_code'] ); ?></title>
	<style>
		<?php echo file_get_contents( CSA_PLUGIN_DIR . 'assets/css/pdf.css' ); ?>
	</style>
</head>
<body>

	<header>
		<table class="header-table">
			<tr>
				<td style="width: 20%;">
					<?php if ( ! empty( $branding['logo_path'] ) && file_exists( $branding['logo_path'] ) ) : ?>
						<img src="data:image/png;base64,<?php echo base64_encode( file_get_contents( $branding['logo_path'] ) ); ?>" style="max-height: 55px; max-width: 120px;">
					<?php elseif ( ! empty( $branding['logo_url'] ) ) : ?>
						<img src="<?php echo esc_url( $branding['logo_url'] ); ?>" style="max-height: 55px; max-width: 120px;">
					<?php endif; ?>
				</td>
				<td style="width: 50%;">
					<div class="academy-title"><?php echo esc_html( $branding['academy_name'] ); ?></div>
					<div class="branch-title">BRANCH: <?php echo esc_html( $report['branch_name'] ); ?></div>
				</td>
				<td style="width: 30%; text-align: right;">
					<div class="report-title">Daily Operations Report</div>
					<div class="report-date">Date: <strong><?php echo esc_html( $report['report_date'] ); ?></strong></div>
					<div class="report-date">Ref: <strong><?php echo esc_html( $report['report_code'] ); ?></strong></div>
				</td>
			</tr>
		</table>
	</header>

	<footer>
		<div class="footer-left">
			<?php echo esc_html( $branding['footer_prefs'] ); ?> | Prepared By: <?php echo esc_html( $report['prepared_by'] ); ?>
		</div>
		<div class="footer-right">
			<span class="page-number"></span>
		</div>
	</footer>

	<main>
		<!-- PAGE 1: EXECUTIVE & FINANCIAL SUMMARY -->
		<div class="section">
			<div class="section-title">Section A: Operational Overview</div>
			<table class="data-table">
				<tr>
					<td style="width: 25%; font-weight: bold; background: #f3f4f6;">Reporting Period</td>
					<td style="width: 25%;"><?php echo esc_html( $report['reporting_period'] ? $report['reporting_period'] : 'Full Day' ); ?></td>
					<td style="width: 25%; font-weight: bold; background: #f3f4f6;">Prepared By</td>
					<td style="width: 25%;"><?php echo esc_html( $report['prepared_by'] ); ?></td>
				</tr>
				<?php if ( ! empty( $report['operational_summary'] ) ) : ?>
					<tr>
						<td style="font-weight: bold; background: #f3f4f6;">Operational Summary</td>
						<td colspan="3"><?php echo nl2br( esc_html( $report['operational_summary'] ) ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( ! empty( $report['overall_remarks'] ) ) : ?>
					<tr>
						<td style="font-weight: bold; background: #f3f4f6;">Overall Remarks</td>
						<td colspan="3"><?php echo nl2br( esc_html( $report['overall_remarks'] ) ); ?></td>
					</tr>
				<?php endif; ?>
			</table>
		</div>

		<?php
		$add_data = ! empty( $report['additional_data'] ) ? json_decode( $report['additional_data'], true ) : array();
		$retail   = ! empty( $add_data['retail'] ) ? $add_data['retail'] : array();
		$comms    = ! empty( $add_data['comms'] ) ? $add_data['comms'] : array();
		$pre_notes = ! empty( $add_data['predefined_notes'] ) ? $add_data['predefined_notes'] : array();
		$cust_notes = ! empty( $add_data['custom_notes'] ) ? $add_data['custom_notes'] : '';
		?>

		<?php if ( ! empty( $financials ) ) : ?>
			<div class="section">
				<div class="section-title">Section B: Financial Summary (<?php echo esc_html( $currency ); ?>)</div>

				<table class="grid-cards">
					<tr>
						<td class="card" style="width: 25%;">
							<div class="card-label">Opening Cash</div>
							<div class="card-value"><?php echo number_format( $financials['opening_cash'], 2 ); ?></div>
						</td>
						<td class="card" style="width: 25%;">
							<div class="card-label">Total Revenue</div>
							<div class="card-value"><?php echo number_format( $financials['total_revenue'], 2 ); ?></div>
						</td>
						<td class="card" style="width: 25%;">
							<div class="card-label">Net Daily Income</div>
							<div class="card-value" style="color: #10b981;"><?php echo number_format( $financials['net_daily_income'], 2 ); ?></div>
						</td>
						<td class="card" style="width: 25%;">
							<div class="card-label">Closing Cash</div>
							<div class="card-value"><?php echo number_format( $financials['closing_cash'], 2 ); ?></div>
						</td>
					</tr>
				</table>

				<table class="data-table">
					<thead>
						<tr>
							<th>Revenue Component</th>
							<th style="text-align: right;">Amount (<?php echo esc_html( $currency ); ?>)</th>
							<th>Deduction / Tax / Expense</th>
							<th style="text-align: right;">Amount (<?php echo esc_html( $currency ); ?>)</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Cash Collections</td>
							<td style="text-align: right;"><?php echo number_format( $financials['cash_collections'], 2 ); ?></td>
							<td>Discounts Applied</td>
							<td style="text-align: right;"><?php echo number_format( $financials['discounts'], 2 ); ?></td>
						</tr>
						<tr>
							<td>Bank & Electronic Payments</td>
							<td style="text-align: right;"><?php echo number_format( $financials['bank_electronic'], 2 ); ?></td>
							<td>Refunds Issued</td>
							<td style="text-align: right;"><?php echo number_format( $financials['refunds'], 2 ); ?></td>
						</tr>
						<tr>
							<td>Other Income</td>
							<td style="text-align: right;"><?php echo number_format( $financials['other_income'], 2 ); ?></td>
							<td>Daily Operational Expenses</td>
							<td style="text-align: right;"><?php echo number_format( $financials['expenses'], 2 ); ?></td>
						</tr>
						<tr style="font-weight: bold; background: #f9fafb;">
							<td>Gross Revenue (Collections - Discounts)</td>
							<td style="text-align: right;"><?php echo number_format( $financials['total_revenue'], 2 ); ?></td>
							<td>VAT Amount (<?php echo number_format( $financials['vat_rate'], 1 ); ?>%)</td>
							<td style="text-align: right;"><?php echo number_format( $financials['vat_amount'], 2 ); ?></td>
						</tr>
					</tbody>
				</table>

				<?php if ( ! empty( $financials['financial_notes'] ) ) : ?>
					<p style="font-size: 10px; color: #4b5563;"><strong>Financial Notes:</strong> <?php echo esc_html( $financials['financial_notes'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $retail['cap_qty'] ) || ! empty( $retail['uniform_qty'] ) ) : ?>
			<div class="section">
				<div class="section-title">Retail Product Sales Summary</div>
				<table class="data-table">
					<thead>
						<tr>
							<th>Item Description</th>
							<th style="text-align: center;">Quantity Sold</th>
							<th style="text-align: right;">Total Amount (<?php echo esc_html( $currency ); ?>)</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $retail['cap_qty'] ) ) : ?>
							<tr>
								<td>Swimming Caps</td>
								<td style="text-align: center;"><?php echo (int) $retail['cap_qty']; ?></td>
								<td style="text-align: right;"><?php echo number_format( (float) $retail['cap_total'], 2 ); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ( ! empty( $retail['uniform_qty'] ) ) : ?>
							<tr>
								<td>Academy Uniforms</td>
								<td style="text-align: center;"><?php echo (int) $retail['uniform_qty']; ?></td>
								<td style="text-align: right;"><?php echo number_format( (float) $retail['uniform_total'], 2 ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<div class="page-break"></div>

		<!-- PAGE 2: STAFF ATTENDANCE -->
		<div class="section">
			<div class="section-title">Section C: Staff Attendance Record</div>
			<?php if ( empty( $staff_records ) ) : ?>
				<p style="color: #6b7280; font-style: italic;">No individual staff records were entered for this operational date.</p>
			<?php else : ?>
				<table class="data-table">
					<thead>
						<tr>
							<th style="width: 25%;">Staff Member</th>
							<th style="width: 20%;">Role / Job Title</th>
							<th style="width: 15%;">Status</th>
							<th style="width: 15%;">Timing (Arr - Dep)</th>
							<th style="width: 25%;">Notes / Remarks</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $staff_records as $st ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $st['staff_name'] ); ?></strong></td>
								<td><?php echo esc_html( $st['job_title'] ? $st['job_title'] : '-' ); ?></td>
								<td>
									<?php
									$st_lower = strtolower( $st['status'] );
									$badge_cls = 'badge-present';
									if ( strpos( $st_lower, 'absent' ) !== false ) $badge_cls = 'badge-absent';
									elseif ( strpos( $st_lower, 'late' ) !== false ) $badge_cls = 'badge-late';
									?>
									<span class="badge <?php echo $badge_cls; ?>"><?php echo esc_html( $st['status'] ); ?></span>
								</td>
								<td><?php echo esc_html( ( $st['arrival_time'] ? $st['arrival_time'] : '-' ) . ' to ' . ( $st['departure_time'] ? $st['departure_time'] : '-' ) ); ?></td>
								<td><?php echo esc_html( $st['notes'] ? $st['notes'] : '-' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<!-- PAGE 3: SPORTS PROGRAM ATTENDANCE -->
		<div class="section">
			<div class="section-title">Section D: Sports Program & Athlete Attendance</div>
			<?php if ( empty( $sports_records ) ) : ?>
				<p style="color: #6b7280; font-style: italic;">No active sports programs were recorded for this operational date.</p>
			<?php else : ?>
				<?php foreach ( $sports_records as $sp ) : ?>
					<div style="margin-bottom: 14px; border: 1px solid #e5e7eb; padding: 10px; border-radius: 4px;">
						<div style="font-size: 11px; font-weight: bold; color: #111827; margin-bottom: 6px;">
							PROGRAM: <?php echo esc_html( strtoupper( $sp['sport_name'] ) ); ?>
							<?php if ( ! empty( $sp['coach_name'] ) ) : ?>| Coach: <?php echo esc_html( $sp['coach_name'] ); ?><?php endif; ?>
							<?php if ( ! empty( $sp['session_time'] ) ) : ?>| Time: <?php echo esc_html( $sp['session_time'] ); ?><?php endif; ?>
						</div>

						<!-- Attendance Statistics Bar -->
						<table class="grid-cards" style="margin-bottom: 8px;">
							<tr>
								<td class="card"><div class="card-label">Expected</div><div class="card-value"><?php echo (int) $sp['expected_count']; ?></div></td>
								<td class="card"><div class="card-label">Present</div><div class="card-value" style="color: #10b981;"><?php echo (int) $sp['present_count']; ?></div></td>
								<td class="card"><div class="card-label">Absent</div><div class="card-value" style="color: #ef4444;"><?php echo (int) $sp['absent_count']; ?></div></td>
								<td class="card"><div class="card-label">Late</div><div class="card-value" style="color: #f59e0b;"><?php echo (int) $sp['late_count']; ?></div></td>
							</tr>
						</table>

						<?php if ( 'individual' === $sp['entry_mode'] && ! empty( $sp['athletes'] ) ) : ?>
							<table class="data-table" style="margin-top: 6px;">
								<thead>
									<tr>
										<th>Athlete Name</th>
										<th>Attendance Status</th>
										<th>Reason / Remarks</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $sp['athletes'] as $ath ) : ?>
										<tr>
											<td><?php echo esc_html( $ath['athlete_name'] ); ?></td>
											<td>
												<?php
												$ath_lower = strtolower( $ath['status'] );
												$ath_badge = 'badge-present';
												if ( 'absent' === $ath_lower ) $ath_badge = 'badge-absent';
												elseif ( 'late' === $ath_lower ) $ath_badge = 'badge-late';
												?>
												<span class="badge <?php echo $ath_badge; ?>"><?php echo esc_html( $ath['status'] ); ?></span>
											</td>
											<td><?php echo esc_html( $ath['absence_reason'] ? $ath['absence_reason'] : '-' ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>

						<?php if ( ! empty( $sp['program_notes'] ) ) : ?>
							<div style="font-size: 9px; color: #4b5563; margin-top: 4px;"><strong>Program Notes:</strong> <?php echo esc_html( $sp['program_notes'] ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<!-- PAGE 4: DAILY ACTIVITIES & NOTES -->
		<?php if ( ! empty( $activities ) ) : ?>
			<div class="section">
				<div class="section-title">Section E: Daily Operational Activities & Events</div>
				<table class="data-table">
					<thead>
						<tr>
							<th style="width: 25%;">Activity Title</th>
							<th style="width: 35%;">Description</th>
							<th style="width: 20%;">Staff Involved</th>
							<th style="width: 20%;">Outcome & Follow-up</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $activities as $act ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $act['activity_title'] ); ?></strong><?php if ( $act['related_sport'] ) echo '<br><small>(' . esc_html( $act['related_sport'] ) . ')</small>'; ?></td>
								<td><?php echo nl2br( esc_html( $act['description'] ) ); ?></td>
								<td><?php echo esc_html( $act['staff_involved'] ? $act['staff_involved'] : '-' ); ?></td>
								<td>
									<?php if ( $act['outcome'] ) echo '<strong>Outcome:</strong> ' . esc_html( $act['outcome'] ) . '<br>'; ?>
									<?php if ( $act['follow_up'] ) echo '<strong>Follow-up:</strong> ' . esc_html( $act['follow_up'] ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $comms ) ) : ?>
			<div class="section">
				<div class="section-title">Parent Communication & Inquiries Log</div>
				<table class="data-table">
					<thead>
						<tr>
							<th>Parent Name</th>
							<th>Athlete Name</th>
							<th>Sport</th>
							<th>Channel</th>
							<th>Discussion Summary</th>
							<th>Action Taken</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $comms as $cm ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $cm['parent_name'] ); ?></strong></td>
								<td><?php echo esc_html( $cm['athlete_name'] ? $cm['athlete_name'] : '-' ); ?></td>
								<td><?php echo esc_html( $cm['sport'] ? $cm['sport'] : '-' ); ?></td>
								<td><?php echo esc_html( $cm['channel'] ? $cm['channel'] : '-' ); ?></td>
								<td><?php echo nl2br( esc_html( $cm['summary'] ) ); ?></td>
								<td><?php echo esc_html( $cm['action'] ? $cm['action'] : '-' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $pre_notes ) || ! empty( $cust_notes ) ) : ?>
			<div class="section">
				<div class="section-title">Section F: General Notes & Operational Recommendations</div>
				<?php if ( ! empty( $pre_notes ) ) : ?>
					<div style="margin-bottom: 8px;">
						<strong>Selected Observations:</strong>
						<ul style="margin: 4px 0 0 16px; padding: 0;">
							<?php foreach ( $pre_notes as $p_note ) : ?>
								<li style="font-size: 10px; color: #374151;"><?php echo esc_html( $p_note ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cust_notes ) ) : ?>
					<p style="font-size: 10px; color: #374151; margin-top: 6px;">
						<strong>Additional Operational Remarks:</strong><br>
						<?php echo nl2br( esc_html( $cust_notes ) ); ?>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</main>
</body>
</html>
