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

class Hipay_Professional17 extends Hipay_Professional
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

    public function Hipay_PaymentReturn17($params)
    {
        // Payement return for PS 1.7
        if ($this->active == false) {
            return;
        }
        $order = $params['order'];
        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }
        $this->smarty->assign(array(
            'id_order'      => $order->id,
            'reference'     => $order->reference,
            'params'        => $params,
            'total_to_pay'  => Tools::displayPrice($order->total_paid, NULL, false),
            'shop_name'     => $this->context->shop->name,
        ));
        return $this->fetch('module:'.$this->name.'/views/templates/hook/confirmation.tpl');
    }
}
