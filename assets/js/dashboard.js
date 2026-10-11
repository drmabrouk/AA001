jQuery(document).ready(function ($) {
	'use strict';

	// Handle Sidebar & Main Navigation Switching
	$('.csa-main-tab-btn').on('click', function () {
		var targetTab = $(this).data('tab');
		$('.csa-main-tab-btn').removeClass('active');
		$('.csa-main-tab-btn[data-tab="' + targetTab + '"]').addClass('active');
		$('.csa-main-tab-content').removeClass('active');
		$('#' + targetTab).addClass('active');
	});

	// Handle Sub Section Tab Switching inside Form
	$('.csa-sub-tab-btn').on('click', function () {
		var targetTab = $(this).data('tab');
		$('.csa-sub-tab-btn').removeClass('active');
		$(this).addClass('active');
		$('.csa-sub-tab-content').removeClass('active');
		$('#' + targetTab).addClass('active');
	});

	// Floating label class handler for inputs
	function updateFloatingLabel(input) {
		var $input = $(input);
		var val = $input.val();
		if (val && val.toString().trim() !== '') {
			$input.addClass('has-value');
			$input.closest('.csa-floating-group').addClass('is-active');
		} else {
			$input.removeClass('has-value');
			$input.closest('.csa-floating-group').removeClass('is-active');
		}
	}

	$(document).on('focus blur change input', '.csa-form-control', function () {
		updateFloatingLabel(this);
	});

	// Initialize all inputs on load
	$('.csa-form-control').each(function () {
		updateFloatingLabel(this);
	});

	// Auto Calculations for Financials
	function calculateFinancials() {
		var openingCash = parseFloat($('#csa_opening_cash').val()) || 0;
		var cashCollections = parseFloat($('#csa_cash_collections').val()) || 0;
		var bankElectronic = parseFloat($('#csa_bank_electronic').val()) || 0;
		var otherIncome = parseFloat($('#csa_other_income').val()) || 0;
		var discounts = parseFloat($('#csa_discounts').val()) || 0;
		var refunds = parseFloat($('#csa_refunds').val()) || 0;
		var expenses = parseFloat($('#csa_expenses').val()) || 0;
		var vatRate = parseFloat($('#csa_vat_rate').val()) || csa_vars.default_vat_rate || 5.0;

		var grossCollections = cashCollections + bankElectronic + otherIncome;
		var totalRevenue = Math.max(0, grossCollections - discounts);
		var vatAmount = Math.round(totalRevenue * (vatRate / 100.0) * 100) / 100;
		var netDailyIncome = totalRevenue - refunds - expenses;
		var closingCash = openingCash + cashCollections - refunds - expenses;

		$('#csa_calc_total_revenue').val(totalRevenue.toFixed(2)).trigger('change');
		$('#csa_calc_vat_amount').val(vatAmount.toFixed(2)).trigger('change');
		$('#csa_calc_net_daily_income').val(netDailyIncome.toFixed(2)).trigger('change');
		$('#csa_calc_closing_cash').val(closingCash.toFixed(2)).trigger('change');
	}

	$(document).on('input change', '.csa-fin-calc-trigger', function () {
		calculateFinancials();
	});

	// Sales Calculations
	function calculateSales() {
		var capQty = parseFloat($('#csa_cap_qty').val()) || 0;
		var capPrice = parseFloat($('#csa_cap_price').val()) || 0;
		var capDisc = parseFloat($('#csa_cap_discount').val()) || 0;
		var capTotal = Math.max(0, (capQty * capPrice) - capDisc);
		$('#csa_cap_total').val(capTotal.toFixed(2)).trigger('change');

		var uniQty = parseFloat($('#csa_uniform_qty').val()) || 0;
		var uniPrice = parseFloat($('#csa_uniform_price').val()) || 0;
		var uniDisc = parseFloat($('#csa_uniform_discount').val()) || 0;
		var uniTotal = Math.max(0, (uniQty * uniPrice) - uniDisc);
		$('#csa_uniform_total').val(uniTotal.toFixed(2)).trigger('change');
	}

	$(document).on('input change', '.csa-sale-calc', function () {
		calculateSales();
	});

	// Dynamic Staff Rows
	$('#csa_add_staff_btn').on('click', function () {
		var index = $('#csa_staff_tbody tr').length;
		var rowHtml = '<tr>' +
			'<td><input type="text" name="staff[' + index + '][name]" class="csa-form-control" placeholder="Staff Name" required></td>' +
			'<td><input type="text" name="staff[' + index + '][job_title]" class="csa-form-control" placeholder="Job Title"></td>' +
			'<td><select name="staff[' + index + '][status]" class="csa-form-control">' +
				'<option value="Present">Present</option>' +
				'<option value="Late">Late</option>' +
				'<option value="Absent">Absent</option>' +
				'<option value="On Leave">On Leave</option>' +
				'<option value="Excused Absence">Excused Absence</option>' +
			'</select></td>' +
			'<td><input type="time" name="staff[' + index + '][arrival_time]" class="csa-form-control"></td>' +
			'<td><input type="time" name="staff[' + index + '][departure_time]" class="csa-form-control"></td>' +
			'<td><input type="text" name="staff[' + index + '][notes]" class="csa-form-control" placeholder="Predefined or custom notes"></td>' +
			'<td><button type="button" class="csa-btn csa-btn-danger csa-btn-sm csa-remove-row-btn">&times;</button></td>' +
		'</tr>';
		$('#csa_staff_tbody').append(rowHtml);
	});

	$(document).on('click', '.csa-remove-row-btn', function () {
		$(this).closest('tr').remove();
	});

	// Toggle Sports Mode (Individual vs Numerical Summary)
	$(document).on('change', '.csa-sport-mode-select', function () {
		var mode = $(this).val();
		var $card = $(this).closest('.csa-sport-block-card');
		if (mode === 'individual') {
			$card.find('.csa-individual-athletes-wrapper').slideDown();
			$card.find('.csa-numerical-summary-wrapper').slideUp();
		} else {
			$card.find('.csa-individual-athletes-wrapper').slideUp();
			$card.find('.csa-numerical-summary-wrapper').slideDown();
		}
	});

	// Dynamic Athlete Row Addition inside a Sport Block
	$(document).on('click', '.csa-add-athlete-btn', function () {
		var $wrapper = $(this).closest('.csa-individual-athletes-wrapper').find('.csa-athletes-tbody');
		var sportIndex = $(this).data('sport-index');
		var athleteIndex = $wrapper.find('tr').length;

		var row = '<tr>' +
			'<td><input type="text" name="sports[' + sportIndex + '][athletes][' + athleteIndex + '][name]" class="csa-form-control" placeholder="Athlete Name" required></td>' +
			'<td><select name="sports[' + sportIndex + '][athletes][' + athleteIndex + '][status]" class="csa-form-control">' +
				'<option value="Present">Present</option>' +
				'<option value="Absent">Absent</option>' +
				'<option value="Late">Late</option>' +
			'</select></td>' +
			'<td><input type="text" name="sports[' + sportIndex + '][athletes][' + athleteIndex + '][reason]" class="csa-form-control" placeholder="Reason if absent/late"></td>' +
			'<td><button type="button" class="csa-btn csa-btn-danger csa-btn-sm csa-remove-row-btn">&times;</button></td>' +
		'</tr>';
		$wrapper.append(row);
	});

	// Dynamic Activity Addition
	$('#csa_add_activity_btn').on('click', function () {
		var index = $('#csa_activities_container .csa-activity-item').length;
		var html = '<div class="csa-activity-item csa-section-card" style="background:#f9fafb; margin-bottom:16px;">' +
			'<div style="display:flex; justify-between; align-items:center; margin-bottom:12px;">' +
				'<strong>Activity #' + (index + 1) + '</strong>' +
				'<button type="button" class="csa-btn csa-btn-danger csa-btn-sm csa-remove-activity-btn">Remove Activity</button>' +
			'</div>' +
			'<div class="csa-grid-2">' +
				'<div class="csa-floating-group"><input type="text" name="activities[' + index + '][title]" class="csa-form-control" placeholder=" " required><label>Activity Title</label></div>' +
				'<div class="csa-floating-group"><input type="text" name="activities[' + index + '][related_sport]" class="csa-form-control" placeholder=" "><label>Related Sport (Optional)</label></div>' +
			'</div>' +
			'<div class="csa-grid-2">' +
				'<div class="csa-floating-group"><input type="time" name="activities[' + index + '][time]" class="csa-form-control" placeholder=" "><label>Time</label></div>' +
				'<div class="csa-floating-group"><input type="text" name="activities[' + index + '][staff_involved]" class="csa-form-control" placeholder=" "><label>Staff Involved</label></div>' +
			'</div>' +
			'<div class="csa-floating-group"><textarea name="activities[' + index + '][description]" class="csa-form-control" rows="2" placeholder=" "></textarea><label>Description</label></div>' +
			'<div class="csa-grid-2">' +
				'<div class="csa-floating-group"><input type="text" name="activities[' + index + '][outcome]" class="csa-form-control" placeholder=" "><label>Outcome</label></div>' +
				'<div class="csa-floating-group"><input type="text" name="activities[' + index + '][follow_up]" class="csa-form-control" placeholder=" "><label>Follow-up Action</label></div>' +
			'</div>' +
		'</div>';
		$('#csa_activities_container').append(html);
	});

	$(document).on('click', '.csa-remove-activity-btn', function () {
		$(this).closest('.csa-activity-item').remove();
	});

	// Section G: Review & Confirmation Step
	$('#csa_review_report_btn').on('click', function () {
		calculateFinancials();
		var reportDate = $('#csa_report_date').val();
		var branchName = $('#csa_branch_name').val();
		var netIncome = $('#csa_calc_net_daily_income').val();
		var staffCount = $('#csa_staff_tbody tr').length;

		var reviewSummary = '<div class="csa-alert csa-alert-success">' +
			'<strong>Report Summary:</strong> Date: ' + (reportDate || 'N/A') + ' | Branch: ' + (branchName || 'Main Branch') + ' | Net Daily Income: AED ' + netIncome + ' | Staff Entries: ' + staffCount +
		'</div>';

		$('#csa_review_summary_box').html(reviewSummary);
		$('.csa-sub-tab-btn[data-tab="csa_section_g"]').trigger('click');
	});

	// AJAX Form Submission for Report Creation/Editing
	$('#csa_daily_report_form').on('submit', function (e) {
		e.preventDefault();
		var $form = $(this);
		var formData = new FormData(this);
		formData.append('action', 'csa_save_daily_report');
		formData.append('nonce', csa_vars.nonce);

		$('#csa_save_status_msg').html('<div class="csa-alert">Saving daily report, please wait...</div>');

		$.ajax({
			url: csa_vars.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (res) {
				if (res.success) {
					$('#csa_save_status_msg').html('<div class="csa-alert csa-alert-success">' + res.data.message + '</div>');
					if (res.data.report_id) {
						setTimeout(function () {
							window.location.reload();
						}, 1200);
					}
				} else {
					$('#csa_save_status_msg').html('<div class="csa-alert csa-alert-danger">' + (res.data.message || 'Error saving report.') + '</div>');
				}
			},
			error: function () {
				$('#csa_save_status_msg').html('<div class="csa-alert csa-alert-danger">Server communication error.</div>');
			}
		});
	});

	// AJAX Branding Settings Form Submission
	$('#csa_branding_settings_form').on('submit', function (e) {
		e.preventDefault();
		var formData = new FormData(this);
		formData.append('action', 'csa_save_branding_settings');
		formData.append('nonce', csa_vars.nonce);

		$('#csa_branding_status_msg').html('<div class="csa-alert">Saving preferences...</div>');

		$.ajax({
			url: csa_vars.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (res) {
				if (res.success) {
					$('#csa_branding_status_msg').html('<div class="csa-alert csa-alert-success">' + res.data.message + '</div>');
				} else {
					$('#csa_branding_status_msg').html('<div class="csa-alert csa-alert-danger">' + (res.data.message || 'Error saving settings.') + '</div>');
				}
			},
			error: function () {
				$('#csa_branding_status_msg').html('<div class="csa-alert csa-alert-danger">Server communication error.</div>');
			}
		});
	});

	// Archive Search & Date Filtering
	$('#csa_archive_search_btn').on('click', function () {
		var dateFilter = $('#csa_archive_date_filter').val();
		var branchFilter = $('#csa_archive_branch_filter').val();

		$('.csa-report-row').each(function () {
			var rowDate = $(this).data('date');
			var rowBranch = $(this).data('branch').toString().toLowerCase();

			var matchDate = !dateFilter || rowDate === dateFilter;
			var matchBranch = !branchFilter || rowBranch.indexOf(branchFilter.toLowerCase()) !== -1;

			if (matchDate && matchBranch) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});

	// Edit Report Handler
	$(document).on('click', '.csa-edit-report-btn', function () {
		var reportId = $(this).data('report-id');
		if (!reportId) return;

		$('#csa_save_status_msg').html('<div class="csa-alert">Loading report details for editing...</div>');
		$('.csa-main-tab-btn[data-tab="csa_tab_create"]').trigger('click');

		$.ajax({
			url: csa_vars.ajax_url,
			type: 'GET',
			data: {
				action: 'csa_get_report_details',
				report_id: reportId,
				nonce: csa_vars.nonce
			},
			success: function (res) {
				if (res.success) {
					var data = res.data;
					var r = data.report;
					var f = data.financials;

					$('#csa_editing_report_id').val(r.id);
					$('#csa_report_date').val(r.report_date).trigger('change');
					$('#csa_academy_name').val(r.academy_name).trigger('change');
					$('#csa_branch_name').val(r.branch_name).trigger('change');
					$('#csa_prepared_by').val(r.prepared_by).trigger('change');
					$('#csa_reporting_period').val(r.reporting_period).trigger('change');
					$('#csa_operational_summary').val(r.operational_summary).trigger('change');
					$('#csa_overall_remarks').val(r.overall_remarks).trigger('change');

					if (f) {
						$('#csa_opening_cash').val(f.opening_cash).trigger('change');
						$('#csa_cash_collections').val(f.cash_collections).trigger('change');
						$('#csa_bank_electronic').val(f.bank_electronic).trigger('change');
						$('#csa_other_income').val(f.other_income).trigger('change');
						$('#csa_discounts').val(f.discounts).trigger('change');
						$('#csa_refunds').val(f.refunds).trigger('change');
						$('#csa_expenses').val(f.expenses).trigger('change');
						$('#csa_vat_rate').val(f.vat_rate).trigger('change');
						$('#csa_outstanding_payments').val(f.outstanding_payments).trigger('change');
						$('#csa_financial_notes').val(f.financial_notes).trigger('change');
						calculateFinancials();
					}

					$('#csa_save_status_msg').html('<div class="csa-alert csa-alert-success">Report #' + r.report_code + ' loaded for editing. Make changes and submit to update.</div>');
				} else {
					$('#csa_save_status_msg').html('<div class="csa-alert csa-alert-danger">' + (res.data.message || 'Error loading report.') + '</div>');
				}
			}
		});
	});

	// Delete Report Handler
	$(document).on('click', '.csa-delete-report-btn', function () {
		var reportId = $(this).data('report-id');
		if (!reportId || !confirm('Are you sure you want to delete this daily report?')) return;

		var $row = $(this).closest('tr');

		$.ajax({
			url: csa_vars.ajax_url,
			type: 'POST',
			data: {
				action: 'csa_delete_report',
				report_id: reportId,
				nonce: csa_vars.nonce
			},
			success: function (res) {
				if (res.success) {
					$row.fadeOut(function () {
						$(this).remove();
					});
				} else {
					alert(res.data.message || 'Error deleting report.');
				}
			}
		});
	});
});
