<?php
/**
* 2016 HiPay
*
* NOTICE OF LICENSE
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2016 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*/

if (_PS_VERSION_ >= '1.7') {
    // version 1.7
    require_once(_PS_ROOT_DIR_.'/modules/hipay_professional/hipay_professional-17.php');
} elseif (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
    // Version 1.6
    require_once(_PS_ROOT_DIR_.'/modules/hipay_professional/hipay_professional-16.php');
} else {
    // Version < 1.6
    Tools::displayError('The module HiPay Professional is not compatible with your PrestaShop');
}
