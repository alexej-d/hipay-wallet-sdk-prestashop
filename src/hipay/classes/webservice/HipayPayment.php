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

if (!defined('_PS_VERSION_'))
    exit;

require_once(dirname(__FILE__).'/HipayWS.php');
require_once(dirname(__FILE__).'/HipayLocale.php');

class HipayPayment extends HipayWS
{
    protected $categories_domain = 'https://payment.hipay.com/';
    protected $categories_test_domain = 'https://test-payment.hipay.com/';
    protected $categories_url = 'order/list-categories/id/';

    protected $client_url = '/soap/payment-v2';

    /* SOAP method: codes */
    public function generate(&$results)
    {
        if ($this->configHipay->user_mail == false) {
            die(Tools::displayError('An error occurred while redirecting to the payment processor'));
        }

        $currency_id = $this->context->cart->id_currency;
        $currency = new Currency($currency_id);
        $user = new HipayUserAccount($this->module);

        $wesbite_account_id = $user->getWebsiteAccountIdByIsoCode($currency->iso_code);
        $website_id = $user->getWebsiteIdByIsoCode($currency->iso_code);
        $wesbite_email = $this->configHipay->user_mail;

        if ($website_id == false) {
            die(Tools::displayError('An error occurred while redirecting to the payment processor'));
        }

        $locale = new HipayLocale($this->module);
        $free_data = $this->getFreeData();

        $cart_id = $this->context->cart->id;
        $secure_key = $this->context->customer->secure_key;
        $accept_url = $this->context->link->getModuleLink('hipay', 'confirmation', array('cart_id' => $cart_id, 'secure_key' => $secure_key), true);
        $callback_url = $this->context->link->getModuleLink('hipay', 'validation', array(), true);
        $cancel_url = $this->context->link->getPageLink('order', null, null, array('step' => '3'), true);
        $decline_url = $this->context->link->getModuleLink('hipay', 'confirmation', array('cart_id' => $cart_id, 'secure_key' => $secure_key), true);
        $logo_url = $this->context->link->getMediaLink(_PS_IMG_.Configuration::get('PS_LOGO'));

        $params = array(
            'websiteId' => (int)$website_id,
            'amount' => $this->context->cart->getOrderTotal(),
            'categoryId' => $this->getCategory(),
            'currency' => $this->context->currency->iso_code,
            'customerEmail' => $this->context->customer->email,
            'customerIpAddress' => Tools::getRemoteAddr(),
            'description' => $this->context->cart->id,
            'emailCallback' => $this->configHipay->user_mail,
            'executionDate' => date('Y-m-d\TH:i:s'),
            'locale' => $locale->getCurrentLocaleCode(),
            'manualCapture' => (int)false,
            'rating' => 'ALL',
            'wsSubAccountId' => $wesbite_account_id,
            'wsSubAccountLogin' => $wesbite_email,

            // URLs
            'urlAccept' => $accept_url,
            'urlCallback' => $callback_url,
            'urlCancel' => $cancel_url,
            'urlDecline' => $decline_url,
            'urlLogo' => $logo_url,

            'freeData' => $free_data,
        );

        $results = $this->executeQuery('generate', $params);

        return ($results->generateResult->code === 0) ? Tools::redirect($results->generateResult->redirectUrl) : false;
    }

    protected function getFreeData()
    {
        $sandbox_mode = (bool)$this->configHipay->sandbox_mode;

        if ($sandbox_mode) {
            $ws_login = (int)$this->configHipay->sandbox_ws_login;
        } else {
            $ws_login = (int)$this->configHipay->production_ws_login;
        }

        return array(
            'item' => array(
                array('key' => 'cart_id', 'value' => $this->context->cart->id),
                array('key' => 'customer_id', 'value' => $this->context->customer->id),
                array('key' => 'secure_key', 'value' => $this->context->customer->secure_key),
                array('key' => 'token', 'value' => Tools::encrypt($ws_login.$this->context->cart->id)),
            ),
        );
    }

    protected function getCategory()
    {
        return 0;
    }

}
