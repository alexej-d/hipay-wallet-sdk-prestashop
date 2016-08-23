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

    public function createAccount($email, $first_name, $last_name, $sandbox_mode = false)
    {
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currency_code = Tools::strtoupper($currency->iso_code);

        $country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
        $country_code = Tools::strtolower($country->iso_code);

        $language = new Language(Configuration::get('PS_LANG_DEFAULT'));
        $language_code = Tools::strtoupper($language->iso_code);

        $data = array(
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'currency_code' => $currency_code,
            'iso_country' => $country_code,
            'iso_lang' => $language_code,
            'remote_addr' => Tools::getRemoteAddr(),
            'sandbox_mode' => (int)$sandbox_mode,
            'shop_email' => Configuration::get('PS_SHOP_EMAIL'),
            'shop_name' => Configuration::get('PS_SHOP_NAME'),
            'shop_domain' => Tools::getShopDomainSsl(true, true),
        );

        $result = $this->prestaShopWebservice('/account/create', $data);

        if (isset($result->code) && ($result->code === 0)) {
            $this->configHipay->user_mail = $email;

            if ($sandbox_mode == false) {
                $this->configHipay->production_user_account_id = $result->userAccountId;
                $this->configHipay->production_website_id      = $result->websiteId;
                $this->configHipay->production_ws_login        = $result->wsLogin;
                $this->configHipay->production_ws_password     = $result->wsPassword;
            } else {
                $this->configHipay->sandbox_user_account_id    = $result->userAccountId;
                $this->configHipay->sandbox_website_id         = $result->websiteId;
                $this->configHipay->sandbox_ws_login           = $result->wsLogin;
                $this->configHipay->sandbox_ws_password        = $result->wsPassword;
            }

            return true;
        }

        return false;
    }
    // check if email is available in HiPay Direct
    public function isEmailAvailable($email)
    {
        $needLogin = false;
        if ( ! is_bool(static::$email_available)) {
            $result = $this->sendApiRequest('/is-available', $needLogin, array(
                'user_email' => $email,
                'entity' => 'direct',
            ));
            $data = json_decode($result);
            if (isset($data['is_available'])) {
                static::$email_available = ! ($data['is_available'] === false);
            } else {
                return false;
            }
        }

        return static::$email_available;
    }

    // get user informations saved in HiPay Direct / Wallet with WSlogin and WSpassword
    public function getAccountInfos($params = [], $needLogin = true)
    {
        $result = $this->sendApiRequest($this->client_url.'/get-infos', $needLogin, $params);
        return ($result->code === 0) ? $result : false;
    }

    // check if bank info status is validated or not
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
