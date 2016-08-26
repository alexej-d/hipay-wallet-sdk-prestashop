<?php
/**
* 2016 HiPay
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

require_once(dirname(__FILE__).'/classes/forms/HipayForm.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayUserAccount.php');
//require_once(dirname(__FILE__).'/classes/webservice/HipayLogs.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayREST.php');

class Hipay extends PaymentModule
{
    protected $config_form = false;

    public $_errors = [];
    protected $_successes = [];
    protected $_warnings = [];

    public $currencies_titles = [];
    public $limited_countries = [];
    public $limited_currencies = [];

    public $configHipay;

    public $hipay_rating = [];

    const PAYMENT_FEED_BASE_LINK = 'https://www.prestashop.com/download/pdf/pspayments/Fees_PSpayments_';

    public static $available_rates_links = [
        'EN', 'FR', 'ES', 'DE',
        'IT', 'NL', 'PL', 'PT'
    ];

    public static $refund_available = ['CB', 'VISA', 'MASTERCARD'];

    public function __construct()
    {
        $this->name = 'hipay';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.0';
        $this->module_key = 'ab188f639335535838c7ee492a2e89f8';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->controllers = array('validation');
        $this->author = 'HiPay';

        $this->bootstrap = true;
        $this->display = 'view';

        parent::__construct();

        $this->displayName = $this->l('Payments 2.0 by HiPay');
        $this->description = $this->l('Accept payments by credit card and other local methods with HiPay payment solution. Very competitive rates, no configuration required!');

        // Compliancy
        $this->limited_countries = [
            'AT', 'BE', 'CH', 'CY', 'CZ', 'DE', 'DK',
            'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HK',
            'HR', 'HU', 'IE', 'IT', 'LI', 'LT', 'LU',
            'LV', 'MC', 'MT', 'NL', 'NO', 'PL', 'PT',
            'RO', 'RU', 'SE', 'SI', 'SK', 'TR'
        ];

        $this->currencies_titles = [
            'AUD' => $this->l('Australian dollar'),
            'CAD' => $this->l('Canadian dollar'),
            'CHF' => $this->l('Swiss franc'),
            'EUR' => $this->l('Euro'),
            'GBP' => $this->l('Pound sterling'),
            'PLN' => $this->l('Polish złoty'),
            'SEK' => $this->l('Swedish krona'),
            'USD' => $this->l('United States dollar'),
        ];

        $this->hipay_rating = [
            ['key' => 'ALL', 'name' => $this->l('For all ages')],
            ['key' => '+12', 'name' => $this->l('For ages 12 and over')],
            ['key' => '+16', 'name' => $this->l('For ages 16 and over')],
            ['key' => '+18', 'name' => $this->l('For ages 18 and over')],
        ];

        $this->limited_currencies = array_keys($this->currencies_titles);

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];

        if (!Configuration::get('HIPAY_CONFIG')) {
            $this->warning = $this->l('Please, do not forget to configure your module');
        }
        $this->configHipay = $this->getConfigHiPay();     
    }

    public function install()
    {
        if (extension_loaded('soap') == false) {
            $this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module cannot work in your country');
            return false;
        }

        return parent::install() &&
        		$this->installHipay();
    }

    public function uninstall()
    {
        return $this->uninstallAdminTab() &&
            parent::uninstall();

    }

    public function installAdminTab()
    {
        $class_name = 'AdminHiPayRefund';

        $tab = new Tab();

        $tab->active = 1;
        $tab->module = $this->name;
        $tab->class_name = $class_name;
        $tab->id_parent = -1;

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->name;
        }

        return $tab->add();
    }

    public function uninstallAdminTab()
    {
        $class_name = 'AdminHiPayRefund';

        $id_tab = (int)Tab::getIdFromClassName($class_name);

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return false;
    }

    public function installHipay()
    {
		$return = $this->setCurrencies() &&
                    $this->insertConfigHiPay() &&
		            $this->installAdminTab() &&
		            $this->updateHiPayOrderStates() &&
		            $this->registerHook('header') &&
		            $this->registerHook('paymentReturn') &&
		            $this->registerHook('paymentTop') &&
		            $this->registerHook('backOfficeHeader') &&
		            $this->registerHook('displayAdminOrderLeft') &&
                    $this->registerHook('payment') && 
                    $this->registerHook('displayPaymentEU');

		return $return;
    }

    public function updateHiPayOrderStates()
    {
        $waiting_state_config   = 'HIPAY_OS_WAITING';
        $waiting_state_color    = '#4169E1';
        $waiting_state_names    = [];

        $setup = [
            'delivery'      => false,
            'hidden'        => false,
            'invoice'       => false,
            'logable'       => false,
            'module_name'	=> $this->name,
            'send_email'	=> true,
        ];

        foreach (Language::getLanguages(false) as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $waiting_state_names[(int)$language['id_lang']] = 'En attente d\'autorisation';
            } else {
                $waiting_state_names[(int)$language['id_lang']] = 'Waiting for authorization';
            }
        }

        $this->saveOrderState($waiting_state_config, $waiting_state_color, $waiting_state_names, $setup);

        $partial_state_config   = 'HIPAY_OS_PARTIALLY_REFUNDED';
        $partial_state_color    = '#EC2E15';
        $partial_state_names    = [];

        foreach (Language::getLanguages(false) as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $partial_state_names[(int)$language['id_lang']] = 'Partiellement remboursé';
            } else {
                $partial_state_names[(int)$language['id_lang']] = 'Partially refunded';
            }
        }

        $this->saveOrderState($partial_state_config, $partial_state_color, $partial_state_names, $setup);

        $total_state_config   = 'HIPAY_OS_TOTALLY_REFUNDED';
        $total_state_color    = '#EC2E15';
        $total_state_names    = [];

        foreach (Language::getLanguages(false) as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $total_state_names[(int)$language['id_lang']] = 'Totalement remboursé';
            } else {
                $total_state_names[(int)$language['id_lang']] = 'Totally refunded';
            }
        }

        $this->saveOrderState($total_state_config, $total_state_color, $total_state_names, $setup);

        return true;
    }

    /**
     * Load configuration page
     * @return string
     */
    public function getContent()
    {
        $form = new HipayForm($this);
        $user_account = new HipayUserAccount($this);

        $config_hipay = $this->configHipay;

        $this->postProcess($user_account);

        // Generate configuration forms
        if (!empty($this->configHipay->user_mail)) {
            $amount_limit = 1000;

            // get currencies
            $selectedCurrencies = $this->getCurrencies();

            $this->context->smarty->assign(array(
                'is_logged'             => true,
                'amount_limit'          => Tools::displayPrice($amount_limit, $this->context->currency),
                'button_form'           => 'button form',//$form->getCustomersServiceForm($user_account),
                'logs'                  => 'logs',//$form->getTransactionsForm($user_account),
                'rating'                => $this->hipay_rating,
                'config_hipay'          => $this->object_to_array($config_hipay),
                'selectedCurrencies'    => $selectedCurrencies,
            ));

        } else {
            $complete_form = $this->shouldDisplayCompleteLoginForm($user_account);

            $this->context->smarty->assign(array(
                'is_logged' => false,
                'login_form' => $form->getLoginForm($complete_form),
                'register_form' => $form->getRegisterForm(),
            ));
        }

        // Set alert messages
        $this->context->smarty->assign(array(
            'form_errors' => $this->_errors,
            'form_successes' => $this->_successes,
            'form_infos' => $this->_warnings,
        ));

        // Define templates paths
        $alerts = $this->local_path.'views/templates/admin/alerts.tpl';
        $configuration = $this->local_path.'views/templates/admin/configuration.tpl';

        $this->context->smarty->assign(array(
            'alerts' => $this->context->smarty->fetch($alerts),
            'module_dir' => $this->_path,
            'localized_rates_pdf_link' => $this->getLocalizedRatesPDFLink()
        ));

        return $this->context->smarty->fetch($configuration);
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') != 'hipay')
            return false;

        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');

        return '<script type="text/javascript">
            var email_error_message = "'.$this->l('Please, enter a valid email address').'.";
        </script>';
    }

    public function hookDisplayAdminOrderLeft($params)
    {
        $order = new Order((int)$params['id_order']);

        if ((! $order->id) || ($order->module != $this->name)) {
            return false;
        }

        $details = $this->getAdminOrderRefundBlockDetails($order);

        $this->context->controller->addCSS($this->_path.'views/css/refund.css');

        if ($this->orderAlreadyRefunded($order)) {
            return $this->display(dirname(__FILE__), 'views/templates/hook/already_refunded.tpl');
        } elseif (! $this->isRefundAvailable($details)) {
            return $this->display(dirname(__FILE__), 'views/templates/hook/cannot_be_refunded.tpl');
        } elseif ($this->isProductionOrder($details)) {
            $min_date = date('Y-m-d H:i:s', strtotime($order->date_add . ' +1 day'));

            if ($min_date > date('Y-m-d H:i:s')) {
                return $this->display(dirname(__FILE__), 'views/templates/hook/cannot_refund_yet.tpl');
            }
        }

        $this->context->controller->addJS($this->_path.'views/js/order.js');

        return $this->display(dirname(__FILE__), 'views/templates/hook/refund.tpl');
    }

    public function hookHeader()
    {
        return $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * Display a payment button
     * @param array $params
     * @return string
     */
    public function hookPayment($params)
    {

    	if ($this->configHipay->production_user_account_id || $this->configHipay->sandbox_user_account_id) {

            $currency_id = $params['cart']->id_currency;
            $currency = new Currency((int)$currency_id);

            if (in_array($currency->iso_code, $this->limited_currencies) == false) {
                return false;
            }
            
            $this->smarty->assign(array(
                'domain' => Tools::getShopDomainSSL(true),
                'module_dir' => $this->_path,
                'payment_button' => $this->getPaymentButton(),
            ));

            $this->smarty->assign('hipay_prod', !(bool)$this->configHipay->sandbox_mode);


            return $this->display(dirname(__FILE__), 'views/templates/hook/payment.tpl');
        }

        return false;
    }

    /**
     * Display the payment confirmation page
     * @param array $params
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(dirname(__FILE__), 'views/templates/hook/confirmation.tpl');
    }

    public function hookPaymentTop()
    {
        $this->context->controller->addJS($this->_path.'views/js/front.js');
    }

    /**
     * Check if the given currency is supported by the provider
     * @param string $iso_code currency iso code
     * @return boolean
     */
    public function isSupportedCurrency($iso_code)
    {
        return in_array(Tools::strtoupper($iso_code), $this->limited_currencies);
    }

    protected function postProcess($user_account)
    {
        if (Tools::isSubmit('submitReset')) {
            return $this->clearAccountData();
        } elseif (Tools::isSubmit('submitLogin')) {
            return $this->login($user_account);
        } elseif (Tools::isSubmit('submitSettings')) {
            return $this->saveSettingsConfiguration();
        } elseif (Tools::isSubmit('submitCancel')) {
            return true;
        }
}

    public function getLocalizedRatesPDFLink()
    {
        $shop_iso_country_id = Configuration::get('PS_COUNTRY_DEFAULT');
        $shop_iso_country = Country::getIsoById((int)$shop_iso_country_id);
        $shop_iso_country = Tools::strtoupper($shop_iso_country);

        if (!$shop_iso_country || !in_array($shop_iso_country, Hipay::$available_rates_links)) {
            $shop_iso_country = 'EN';
        }

        $localized_link = Hipay::PAYMENT_FEED_BASE_LINK.$shop_iso_country.'.pdf';

        return $localized_link;
    }

    public function getAdminOrderRefundBlockDetails($order)
    {
        $currency       = new Currency($order->id_currency);
        $messages       = Message::getMessagesByOrderId($order->id, true);
        $message        = array_pop($messages);
        $details        = json_decode($message['message']);
        $id_transaction = $this->getTransactionId($details);

        $form = new HipayForm($this);

        $params = http_build_query([
            'id_order'          => $order->id,
            'id_transaction'    => $id_transaction,
            'sandbox'           => (isset($details->Environment) && ($details->Environment != 'PRODUCTION')),
        ]);

        $this->smarty->assign([
            'currency'          => $currency,
            'details'           => $details,
            'order'             => $order,
            'transaction_id'    => $id_transaction,
            'refund_link'       => $this->context->link->getAdminLink('AdminHiPayRefund&' . $params, true),
        ]);

        return $details;
    }

    /**
     * Add waiting order state in database
     * If it does not already exists
     * @return boolean
     */
    protected function saveOrderState($config, $color, $names, $setup)
    {
        $state_id = Configuration::get($config);

        if ((bool)$state_id == true) {
            $order_state = new OrderState($state_id);
        } else {
            $order_state = new OrderState();
        }

        $order_state->name	= $names;
        $order_state->color = $color;

        foreach ($setup as $param => $value) {
            $order_state->{$param} = $value;
        }

        if ((bool)$state_id == true) {
            return $order_state->save();
        } elseif ($order_state->add() == true) {
            Configuration::updateValue($config, $order_state->id);
            @copy($this->local_path.'logo.gif', _PS_ORDER_STATE_IMG_DIR_.(int)$order_state->id.'.gif');

            return true;
        }

        return false;
    }

    /**
    * Clear every single merchant account data
    * @return boolean
    */
    protected function clearAccountData()
    {
        Configuration::deleteByName('HIPAY_CONFIG');
        return true;
    }

    protected function createMerchantAccount($email, $first_name, $last_name)
    {
        $is_valid_name  = (bool)Validate::isName($first_name);
        $is_valid_name  &= (bool)Validate::isName($last_name);

        if ($is_valid_name) {
            $user_account = new HipayUserAccount($this);

            // Live mode
            if ($user_account->isEmailAvailable($email, false) == true) {
                $user_account->createAccount($email, $first_name, $last_name, false);
            }

            // Sandbox mode
            if ($user_account->isEmailAvailable($email, true) == true) {
                $user_account->createAccount($email, $first_name, $last_name, true);
            }
        }
    }

    /**
     * Get the appropriate payment button's image
     * @return string
     */
    protected function getPaymentButton()
    {
        $id_address = $this->context->cart->id_address_invoice;

        if ($id_address) {
            $address = new Address((int)$id_address);
            $country = new Country((int)$address->id_country);
            $iso_code = Tools::strtolower($country->iso_code);

            if (file_exists(dirname(__FILE__).'/views/img/payment_buttons/'.$iso_code.'.png')) {
                return $this->_path.'views/img/payment_buttons/'.$iso_code.'.png';
            }
        }

        return $this->_path.'views/img/payment_buttons/default.png';
    }

    protected function getTransactionId($details)
    {
        foreach ($details as $key => $value) {
            $tmp_key = strtolower(str_replace(' ', false, $key));

            if (in_array($tmp_key, ['transactionid', 'idtransaction'])) {
                return $value;
            }
        }

        return false;
    }

    protected function isProductionOrder($details)
    {
        return (isset($details->Environment) && ($details->Environment == 'PRODUCTION'));
    }

    protected function isRefundAvailable($details)
    {
        $stack = array_values((array)$details);
        $refund_available   = array_intersect($stack, static::$refund_available);

        return ! empty($refund_available);
    }

    protected function login($user_account)
    {
        // get values login and password
        $ws_login = Tools::getValue('install_ws_login');
        $ws_password = Tools::getValue('install_ws_password');

        if ($ws_login && $ws_password) {
            try {
                // ctrl if login and password are crypted to md5
                $is_valid_login = (bool)Validate::isMd5($ws_login);
                $is_valid_password = (bool)Validate::isMd5($ws_password);

                if ($is_valid_login && $is_valid_password) {
                    $params = [
                        'ws_login'      => $ws_login,
                        'ws_password'   => $ws_password,
                    ];
                    $user_account = new HipayUserAccount($this);
                    $account = $user_account->getAccountInfos($params, false);
                    if (isset($account->code) && ($account->code == 0)) {
                        $this->setConfigHiPay('sandbox_mode', false);
                        $this->setConfigHiPay('welcome_message_shown',true);
                        return $this->registerExistingAccount($account,$params);
                    } else {
                        $this->_errors[] = $this->l('Authentication failed!');
                        $this->clearAccountData();
                        return false;
                    }
                }
            } catch (Exception $e) {
                // TODO LOGS
                $this->_errors[] = $this->l($e->getMessage());
            }
            $this->_warnings[] = $this->l('The credentials you have entered are invalid. Please try again.');
            $this->_warnings[] = $this->l('If you have lost these details, please log in to your HiPay account to retrieve it');

            return false;
        }
        /*
        if ($user_account->isEmailAvailable($email)) {
            // Email available
            $this->_warnings[] = $this->l('To create your PrestaShop Payments by Hipay account, please enter your name and click on Subscribe');
        } else {
            // Email not available
            $this->_warnings[] = $this->l('You already have an account, please fill the fields below');
        }
        */
        return true;
    }

    protected function orderAlreadyRefunded($order)
    {
        $history_states = $order->getHistory($this->context->language->id);

        $states = Configuration::getMultiple([
            'HIPAY_OS_PARTIALLY_REFUNDED',
            'HIPAY_OS_TOTALLY_REFUNDED',
        ]);

        foreach ($history_states as $state) {
            if ($key = array_search($state['id_order_state'], $states)) {
                $this->smarty->assign('state', $key);
                return $state;
            }
        }

        return false;
    }

    protected function registerExistingAccount($account, $params = [], $sandbox = false)
    {
        $prefix             = $sandbox ? 'sandbox' : 'production';
        $user_mail          = '';
        $website_id         = '';
        $user_account_id    = '';

        // init array config values by currency
        foreach($account->websites as $websiteDefault){
            $user_mail[$account->currency][$websiteDefault->website_id]         = $websiteDefault->website_email;
            $website_id[$account->currency][$websiteDefault->website_id]        = $websiteDefault->website_id;
            $user_account_id[$account->currency][$websiteDefault->website_id]   = $account->user_account_id;
        }
        foreach($account->sub_accounts as $sub_account){
            foreach($sub_account->websites as $website){
                $user_mail[$sub_account->currency][$website->website_id]        = $website->website_email;
                $website_id[$sub_account->currency][$website->website_id]       = $website->website_id;
                $user_account_id[$sub_account->currency][$website->website_id]  = $sub_account->user_account_id;
            }
        }

        $details = [
            'user_mail'                 => $user_mail,
            $prefix.'_user_account_id'  => $user_account_id,
            $prefix.'_website_id'       => $website_id,
            $prefix.'_ws_login'         => $params['ws_login'],
            $prefix.'_ws_password'      => $params['ws_password'],
        ];

        if(!$this->saveConfigurationDetails($details)){
            $this->clearAccountData();
            return false;
        }

        return true;
    }

    protected function saveConfigurationDetails($details)
    {
        foreach ($details as $name => $value) {
            $this->configHipay->$name = $value;
        }
        return $this->setAllConfigHiPay();
    }

    protected function saveTransactionsDateRange()
    {
        if (Tools::isSubmit('date_from') && Tools::isSubmit('date_to')) {
            $this->context->cookie->hipay_date_from = Tools::getValue('date_from');
            $this->context->cookie->hipay_date_to = Tools::getValue('date_to');
        }
    }

    /**
     * Store the currencies list the module should work with
     * @return boolean
     */
    protected function setCurrencies()
    {
        $shops = Shop::getShops(true, null, true);

        foreach ($shops as $shop) {
            $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` (`id_module`, `id_shop`, `id_currency`)
                    SELECT '.(int)$this->id.', "'.(int)$shop.'", `id_currency`
                    FROM `'._DB_PREFIX_.'currency`
                    WHERE `deleted` = \'0\' AND `iso_code` IN (\''.implode($this->limited_currencies, '\',\'').'\')';

            return (bool)Db::getInstance()->execute($sql);
        }

        return true;
    }
    protected function getCurrencies(){
        // get currencies
        $currencies = Currency::getCurrenciesByIdShop((int)$this->context->shop->id);
        $selectedCurrencies = [];
        foreach($currencies as $currency){
            $selectedCurrencies[$currency['iso_code']] = '';
        }
        return $selectedCurrencies;
    }
    protected function shouldDisplayCompleteLoginForm($user_account)
    {
        // If merchant tries to login / subscribe
        if (Tools::isSubmit('submitLogin') == true) {
            $email = Tools::getValue('install_user_email');

            if (Validate::isEmail($email)) {
                return $user_account->isEmailAvailable($email) ? 'new_account' : 'existing_account';
            }

            $this->module->_errors[] = $this->l('Invalid email address');
        }

        return false;
    }

    /*
     * Save the merchant configuration
     * @return boolean
     */
    protected function saveSettingsConfiguration()
    {
        /*
         * GET VALUES FORM
         */
        try{
            $sandbox_mode           = Tools::getValue('settings_switchmode');
            $selected_rating        = Tools::getValue('settings_production_rating');
            $selected_config        = '';

            // get currencies
            $getCurrencies = $this->getCurrencies();

            // init dynamic values by currency
            $selectedCurrenciesProd = '';
            $selectedCurrenciesSandbox = '';
            foreach($getCurrencies as $key=>$value)
            {
                // production
                $getProductionAccountId = Tools::getValue('settings_production_'.$key.'_user_account_id');
                $getProductionWebsiteId = Tools::getValue('settings_production_'.$key.'_website_id');
                $selectedCurrenciesProd[$key] = [
                    'accountID' => (int)$getProductionAccountId,
                    'websiteID' => (int)$getProductionWebsiteId,
                ];

                if(Tools::getValue('settings_sandbox_'.$key.'_user_account_id'))
                {
                    // sandbox
                    $getSandboxAccountId = Tools::getValue('settings_sandbox_'.$key.'_user_account_id');
                    $getSandboxWebsiteId = Tools::getValue('settings_sandbox_'.$key.'_website_id');
                    $selectedCurrenciesSandbox[$key] = [
                        'accountID' => (int)$getSandboxAccountId,
                        'websiteID' => (int)$getSandboxWebsiteId,
                    ];
                }
            }

            // init array with all selected informations
            $selected_config = [
                'rating'        => $selected_rating,
                'currencies'    => [
                    'production' => $selectedCurrenciesProd,
                    'sandbox'    => $selectedCurrenciesSandbox,
                ]
            ];

            // save configuration sandbox mode and select informations
            $this->setConfigHiPay('sandbox_mode', ($sandbox_mode ? true:false));
            $this->setConfigHiPay('selected', $selected_config);

            $this->_successes[] = $this->l('configuration saved successfully.');

            return true;

        }catch (Exception $e){
            // TODO LOGS
            $this->_errors[] = $this->l($e->getMessage());
        }
        return false;
    }

    /*
     * Function to get the module configuration
 	 * @user_mail array
     * @sandbox_mode boolean
     * @sandox_user_account_id array
	 * @sandbox_website_id array
	 * @sandbox_ws_login varchar
	 * @sandbox_ws_password varchar
     * 
     * @production_user_account_id array
	 * @production_website_id array
	 * @production_ws_login varchar
	 * @production_ws_password varchar
	 *
	 * @welcome_message_shown boolean
     *
     * @entity
     *
     * @proxyUrl
     * @proxyLogin
     * @proxyPassword
     */
    public function getConfigHiPay()
    {
        // init multistore
        $id_shop        = (int)$this->context->shop->id;
        $id_shop_group  = (int)Shop::getContextShopGroupID();
        $confHipay = Configuration::get('HIPAY_CONFIG',null, $id_shop_group, $id_shop);
        // if config exist but empty, init new object for configHipay
        if(!$confHipay || empty($confHipay)){
            $this->insertConfigHiPay();
        }
        // not empty in bdd and the config is stacked in JSON
        $result = json_decode(Configuration::get('HIPAY_CONFIG',null, $id_shop_group, $id_shop));
        return (object)$result;
    }
    public function setConfigHiPay($key, $value)
    {
        // init multistore
        $id_shop        = (int)$this->context->shop->id;
        $id_shop_group  = (int)Shop::getContextShopGroupID();
    	// the config is stacked in JSON
    	$this->configHipay->$key =$value;
    	if(Configuration::updateValue('HIPAY_CONFIG', json_encode($this->configHipay),false,$id_shop_group,$id_shop)){
    	    return true;
        }else{
            throw new Exception($this->l('Update failed, try again.'));
        }
    }
    public function setAllConfigHiPay($objHipay = null)
    {
        if($objHipay != null){
            $for_json_hipay = $objHipay;
        }else{
            $for_json_hipay = $this->configHipay;
        }
        // init multistore
        $id_shop        = (int)$this->context->shop->id;
        $id_shop_group  = (int)Shop::getContextShopGroupID();
    	// the config is stacked in JSON
    	return Configuration::updateValue('HIPAY_CONFIG', json_encode($for_json_hipay),false,$id_shop_group,$id_shop);
    }
    public function insertConfigHiPay()
    {
        // init objet config for HiPay
        $objHipay = new StdClass();
        $objHipay->user_mail                   = '';
        $objHipay->sandbox_mode                = false;
        $objHipay->sandbox_user_account_id     = '';
        $objHipay->sandbox_website_id          = '';
        $objHipay->sandbox_ws_login            = '';
        $objHipay->sandbox_ws_password         = '';
        $objHipay->production_user_account_id  = '';
        $objHipay->production_website_id       = '';
        $objHipay->production_ws_login         = '';
        $objHipay->production_ws_password      = '';
        $objHipay->welcome_message_shown       = false;
        $objHipay->entity                      = '';
        $objHipay->proxyUrl                    = '';
        $objHipay->proxyLogin                  = '';
        $objHipay->proxyPassword               = '';
        $objHipay->selected                    = '';

        return $this->setAllConfigHiPay($objHipay);
    }
    public function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = $this->object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
}
