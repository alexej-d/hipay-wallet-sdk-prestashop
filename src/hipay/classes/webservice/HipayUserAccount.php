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
    protected $accounts_currencies      = array();
    protected $client_url               = 'user-account';
    protected $client_tools             = 'tools';
    protected $module                   = false;
    protected static $email_available   = null;
    protected $business_lines           = 18;
    protected $website_topic            = 175;

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
        $result = $this->sendApiRequest($this->client_tools.'/captcha','get', false, $params, false, true);

        if($result->code == 0) {
            return $result;
        }else{
            throw new Exception(print_r($result,true));
        }
    }

    /**
     * Check code to activate account merchant
     */
    public function checkCodeValidation($code, $currency_code)
    {
        // init val for webservice
        $params = [
            'validation_code'           => $code,
        ];
        $result = $this->sendApiRequest($this->client_url.'/check/code','post', true, $params, false, false);

        if($result->code == 0) {
            return $result;
        }else{
            throw new Exception(print_r($result,true));
        }
    }

    /**
     * Create an account in production
     */
    public function createAccount($params)
    {
        // get currency default
        $currency       = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currency_code  = Tools::strtoupper($currency->iso_code);
        // get country code default
        $country        = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
        $country_code   = Tools::strtolower($country->iso_code);
        // get code iso
        $language       = new Language(Configuration::get('PS_LANG_DEFAULT'));
        $language_code  = Tools::strtoupper($language->iso_code);

        $data = array(
            'email'             => $params['email'],
            'controle_type'     => 'CAPTCHA',
            'captcha'           =>
                [
                    'id'        => $params['captcha_id'],
                    'phrase'    => $params['captcha_code'],
                ],
            'firstname'        => $params['first_name'],
            'lastname'         => $params['last_name'],
            'currency'          => $currency_code,
            'locale'            => $country_code . '_' . $language_code,
            'activation_type'   => true,
        );

        $this->module->logs->logsHipay(print_r($data, true));

        $result = $this->sendApiRequest($this->client_url,'post', false, $data, false, false);

        $this->module->logs->logsHipay(print_r($result, true));

        if($result->code == 0) {
            return $result;
        }else{
            throw new Exception(print_r($result,true));
        }
    }

    /**
     * Create an account in production
     */
    public function createWebsite($currency,$account_id = 0, $parent_id = 0, $parent_currency = '')
    {
        // init params web service
        $email = Configuration::get('PS_SHOP_EMAIL');

        // get infos
        $config_prod    = $this->configHipay->production;

        // object to array fix
        $config_prod    = $this->module->object_to_array($config_prod);

        $this->module->logs->logsHipay('currency en input = '.$currency);
        $this->module->logs->logsHipay('account_id en input = '.$account_id);
        $this->module->logs->logsHipay('parent currency en input = '.$parent_currency);
        $this->module->logs->logsHipay('parent account_id en input = '.$parent_id);

        // subaccount add website
        if((int)$account_id > 0 && (int)$parent_id > 0){
            $objCur         = $config_prod[$parent_currency];

            $this->module->logs->logsHipay('parent_id = '.$parent_id.' account_id != 0 ');

            $objAcc         = $objCur[$parent_id];
            $email          = $objAcc[0]['user_mail'];

            $this->module->logs->logsHipay('treatment subaccount with id = '.$account_id);

        } else {
            // account add website
            $this->module->logs->logsHipay('account_id == 0 ');

            $objCur         = $config_prod[$currency];
            foreach($objCur as $key=>$val)
            {
                $objKey     = $objCur[$key];
                $account_id = $key;
                $email      = $objKey[0]['user_mail'];
                break;
            }
            $this->module->logs->logsHipay('treatment account with id = '.$account_id);
        }

        $params = [
            'name'                      => Configuration::get('PS_SHOP_NAME'),
            'url'                       => Tools::getShopDomainSsl(true),
            'contact_email'             => $email,
            'business_line'             => $this->business_lines,
            'topic'                     => $this->website_topic,
            'php-auth-subaccount-id'    => $account_id,
        ];

        $this->module->logs->logsHipay(print_r($params,true));
        // call api and execute create website
        $result = $this->sendApiRequest($this->client_url.'/website','post', true, $params, false, false);

        if($result->code == 0) {
            return $result;
        }else{
            throw new Exception(print_r($result,true));
        }
    }

    /**
     * Check code to activate account merchant
     */
    public function duplicateByCurrency($currency)
    {
        $params = [
            'currency' => $currency,
        ];
        $result = $this->sendApiRequest($this->client_url.'/duplicate','post', true, $params, false, false);

        if($result->code == 0) {
            return $result;
        }else{
            throw new Exception(print_r($result,true));
        }
    }

    /**
     * get user informations saved in HiPay Direct / Wallet with WSlogin and WSpassword
     */
    public function getAccountInfos($params = [], $needLogin = true, $needSandboxLogin = false)
    {
        $result = $this->sendApiRequest($this->client_url,'get', $needLogin, $params, $needSandboxLogin);

        if($result->code == 0) {
            return $result;
        }else{
            throw new Exception(print_r($result,true));
        }
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
