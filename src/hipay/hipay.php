<?php
if (_PS_VERSION_ < '1.7') {
	// version 1.4
	require_once(_PS_ROOT_DIR_.'/modules/hipay/hipay-16.php');
} elseif (_PS_VERSION_ >= '1.7') {
	// Version 1.5 or above
	require_once(_PS_ROOT_DIR_.'/modules/hipay/hipay-17.php');
} else {
	Tools::displayError('The module HiPay is not compatible with your PrestaShop');
}