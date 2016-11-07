/**
 * 2016 HiPay
 *
 * NOTICE OF LICENSE
 *
 *
 * @author    HiPay <support.wallet@hipay.com>
 * @copyright 2016 HiPay
 * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
 *
 */

$(document).ready(function() {
	$(document).on('click', '#transactions_dates_range_button', function() {
		$('#datepicker').removeClass('hide');
		return false;
	});

	if ($('#hipay_warning_modal').length > 0) {
		$('#hipay_warning_modal').modal('show');
	}

	$('#datepicker').removeClass('hide');
	$('#datepicker-cancel').hide();

});
