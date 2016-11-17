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


use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Hipay_Professional17 extends PaymentModule
{
    /*
    * VERSION PS 1.7
    *
    */
    public function Hipay_PaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $payment_options = [
            $this->Hipay_ExternalPaymentOption(),
        ];
        return $payment_options;
    }
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
    public function Hipay_ExternalPaymentOption()
    {
        $lang = Tools::strtolower($this->context->language->iso_code);

        $newOption = new PaymentOption();
        $newOption->setCallToActionText(($lang == 'fr' ? $this->configHipay->button_text_fr : $this->configHipay->button_text_en))
            ->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true))
            ->setLogo(Media::getMediaPath($this->getPaymentButton()));

        return $newOption;
    }
}
