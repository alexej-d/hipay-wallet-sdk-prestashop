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

abstract class HipayWS
{
    protected $context = false;
    protected $client = false;
    protected $client_url = false;
    protected $module = false;

    protected $prestashop_api = 'https://payments.prestashop.com/psphipay';

    protected $ws_url = 'https://ws.hipay.com';
    protected $ws_test_url = 'https://test-ws.hipay.com';

    public $configHipay;

    public function __construct($module_instance)
    {
        $this->context = Context::getContext();
        $this->module = $module_instance;
        // init config hipay
        $this->configHipay = $module_instance->configHipay;
    }

    public function getWsId()
    {
        return $this->ws_id;
    }

    public function getWsLogin()
    {
        return $this->ws_login;
    }

    public function getWsPassword()
    {
        return $this->ws_password;
    }

    public function getWsMerchantGroup()
    {
        return $this->ws_merchant_group;
    }

    public function getWsURL()
    {
        return $this->ws_url;
    }

    public function getWsClientURL()
    {
        if ((bool)$this->configHipay->sandbox_mode == false) {
            return $this->ws_url.$this->client_url.'?wsdl';
        }

        return $this->ws_test_url.$this->client_url.'?wsdl';
    }

    public function getClient()
    {
        try {
            $ws_options = array(
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 20,
                'soap_version' => SOAP_1_1,
                'encoding' => 'UTF-8'
            );

            return new SoapClient($this->getWsClientURL(), $ws_options);
        } catch (SoapFault $exception) {
            return false;
        }
    }

    public function executeQuery($function, $params = [], $sandbox = false)
    {
        try {
            if ((isset($this->client) == false) || ($this->client === false))
                $this->client = $this->getClient();

            if ($this->client == false) {
                $this->module->_errors[] = $this->module->l('An error occurred while trying to contact the web service', 'HipayWS');
                return false;
            }

            if ((bool)$this->configHipay->sandbox_mode || $sandbox == true) {
                $params = $params + array(
                    'websiteId'     => $this->configHipay->sandbox_website_id,
                    'wsLogin'       => $this->configHipay->sandbox_ws_login,
                    'wsPassword'    => $this->configHipay->sandbox_ws_password,
                );
            } else {
                $params = $params + array(
                    'websiteId'     => $this->configHipay->production_website_id,
                    'wsLogin'       => $this->configHipay->production_ws_login,    
                    'wsPassword'    => $this->configHipay->production_ws_password,
                );
            }

            $output = $this->client->__call($function, array(array('parameters' => $params)));

            unset($this->client);

            return $output;
        } catch (Exception $exception) {
            $this->module->_errors[] = $this->module->l('An error occurred while trying to contact the web service', 'HipayWS');
            return false;
        }
    }

    public function prestaShopWebservice($method, $data)
    {
        $options = array('http' => array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ));

        $context  = stream_context_create($options);
        $result = Tools::file_get_contents($this->prestashop_api.$method, false, $context);
        $values = Tools::jsonDecode($result);

        return isset($values->data) ? $values->data : false;
    }
}
