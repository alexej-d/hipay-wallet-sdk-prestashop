<?php
/**
* 2015 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2015 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*/

class HipayRedirectModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $currency = $this->context->currency;

        if ($this->module->isSupportedCurrency($currency->iso_code) == false) {
            return $this->displayError('The currency is not supported');
        }

        $this->generatePayment();
    }

    protected function generatePayment()
    {
        require_once(dirname(__FILE__).'/../../classes/webservice/HipayPayment.php');

        $results = null;
        $payment = new HipayPayment($this->module);

        if ($payment->generate($results) == false) {
            $description = $results->generateResult->description;
            $this->displayError('An error occurred while getting transaction informations', $description);
        } else {
            // ctrl if iframe
            if( !$this->module->configHipay->payment_form_type ){
                $this->context->smarty->assign(array(
                    'iframe_url'    => $results->generateResult->redirectUrl,
                    'cart_id'       => $this->context->cart->id,
                    'currency'      => $this->context->currency->iso_code,
                    'amount'        => $this->context->cart->getOrderTotal(true, Cart::BOTH),
                    'nbProducts'    => $this->context->cart->nbProducts(),
                ));
                // show the iframe page in Prestashop
                return $this->setTemplate('16_iframe.tpl');
            }
        }
    }

    protected function displayError($message, $description = false)
    {
        $this->context->smarty->assign('path', '
            <a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Order').'</a>
            <span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));

        $this->errors[] = $this->module->l($message);

        if ($description != false) {
            $this->errors[] = $description;
        }

        return $this->setTemplate('error.tpl');
    }
}
