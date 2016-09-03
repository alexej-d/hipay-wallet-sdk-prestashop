<?php
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

if (!defined('_PS_VERSION_'))
    exit;

require_once(dirname(__FILE__).'/HipayREST.php');

class HipayUserAccount extends HipayREST
{
    protected $accounts_currencies = array();
    protected $client_url = 'user-account';
    protected $module = false;
    protected static $email_available = null;

    public function __construct($module_instance)
    {
        parent::__construct($module_instance);

        $this->accounts_currencies = array(
            'CHF' => $this->module->l('Swiss Franc', 'HipayUserAccount'),
            'EUR' => $this->module->l('Euro', 'HipayUserAccount'),
            'GBP' => $this->module->l('British Pound', 'HipayUserAccount'),
            'SEK' => $this->module->l('Swedish Krona', 'HipayUserAccount'),
        );
    }

    /**
     * Get ID and image for the security code by CAPTCHA
     */
    public function getCaptcha()
    {
        $params = [];
        $result = $this->sendApiRequest($this->client_url.'/captcha','get', false, $params, false, true);
        return ($result->code == 0) ? $result : false;
    }

    /**
     * Create an account in production
     */
    public function createAccount($params)
    {
        // get currency default
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currency_code = Tools::strtoupper($currency->iso_code);
        // get country code default
        $country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
        $country_code = Tools::strtolower($country->iso_code);
        // get code iso
        $language = new Language(Configuration::get('PS_LANG_DEFAULT'));
        $language_code = Tools::strtoupper($language->iso_code);

        $data = array(
            'email'             => $params['email'],
            'controle_type'     => 'CAPTCHA',
            'captcha'           =>
                [
                    'id'        => $params['captcha_id'],
                    'phrase'    => $params['captcha_code'],
                ],
            'first_name'        => $params['first_name'],
            'last_name'         => $params['last_name'],
            'currency_code'     => $currency_code,
            'local'             => $country_code . '_' . $language_code,
            'activation_type'   => true,
        );

        $result = $this->sendApiRequest($this->client_url.'/create','post', false, $data, false, false);
        return ($result->code === 0) ? $result : false;

    }

    /**
     * get user informations saved in HiPay Direct / Wallet with WSlogin and WSpassword
     */
    public function getAccountInfos($params = [], $needLogin = true, $needSandboxLogin = false)
    {
        $result = $this->sendApiRequest($this->client_url.'/get-infos','post', $needLogin, $params, $needSandboxLogin);
        return ($result->code === 0) ? $result : false;
    }

    /**
     *
     * WAITING FUNCTIONS FOR DEV
     *
     * check if bank info status is validated or not
     */
    public function getBankInfoStatus(){
        $needLogin = true;
        $result = $this->sendApiRequest($this->client_url.'/get-infos', $needLogin);
        return ($data['code'] === 0) ? $data : false;
    }
    public function getTransactions()
    {
        $psp_hipay_date_from = (isset($this->context->cookie->psp_hipay_date_from) ? $this->context->cookie->psp_hipay_date_from : date('Y-m-dT')).'00:00:00';
        $psp_hipay_date_to = (isset($this->context->cookie->psp_hipay_date_to) ? $this->context->cookie->psp_hipay_date_to : date('Y-m-dT')).'23:59:59';

        $params = array(
            'wsSubAccountLogin' => $this->configHipay->user_mail,
            'startDate' => date('Y-m-dTH:i:s', strtotime($psp_hipay_date_from)),
            'endDate' => date('Y-m-dTH:i:s', strtotime($psp_hipay_date_to)),
            'pageNumber' => 1,
        );

        $results = $this->executeQuery('getTransactions', $params);

        if (($results->getTransactionsResult->code === 0) && (isset($results->getTransactionsResult->transactions->item) == true)) {
            if (is_array($results->getTransactionsResult->transactions->item) == true) {
                return (array)$results->getTransactionsResult->transactions->item;
            } else {
                return array($results->getTransactionsResult->transactions->item);
            }
        }
    }
    public function getWebsiteAccountIdByIsoCode($iso_code)
    {
        $account = $this->getAccountInfos();

        if ($iso_code == $account->currency) {
            return $account->userAccountId;
        }

        foreach ($account->subAccounts->item as $sub_account) {
            if ($iso_code == $sub_account->currency) {
                return $sub_account->userAccountId;
            }
        }

        return false;
    }
    public function getWebsiteIdByIsoCode($iso_code)
    {
        $account = $this->getAccountInfos();

        if ($iso_code == $account->currency) {
            if (isset($account->websites->item->websiteId)) {
                return $account->websites->item->websiteId;
            }

            foreach ($account->websites->item as $account) {
                if (strstr($account->websiteURL, Tools::getShopDomain()) !== false) {
                    return $account->websiteId;
                }
            }
        }

        foreach ($account->subAccounts->item as $sub_account) {
            if ($iso_code == $sub_account->currency) {
                return $sub_account->websites->item->websiteId;
            }
        }

        return false;
    }


}
