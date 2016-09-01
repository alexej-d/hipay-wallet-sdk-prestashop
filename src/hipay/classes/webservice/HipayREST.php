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

abstract class HipayREST
{
    protected $context = false;
    protected $module = false;

    protected $rest_url = 'https://qa-merchant.hipaywallet.com/api';
    protected $rest_test_url = 'https://test-merchant.hipaywallet.com/api';

    public $configHipay;
    protected $RestLogin =false;
    protected $RestPassword;

    public function __construct($module_instance)
    {
        $this->context = Context::getContext();
        $this->module = $module_instance;
        // init config hipay
        $this->configHipay = $module_instance->configHipay;
    }

    public function getRestClientURL()
    {
        if ((bool)$this->configHipay->sandbox_mode == false) {
            return $this->rest_url;
        }
        return $this->rest_test_url;
    }

    // function Request by cURL
    public function sendApiRequest($function, $needLogin = true, $params = [], $needSandboxLogin = false, $captcha = false)
    {
        try {
            $url = $this->getRestClientURL();
            if ( $needSandboxLogin ) {
                $url = $this->rest_test_url;
            }
            if((bool)$needLogin) {
                if ((bool)$this->configHipay->sandbox_mode) {
                    $this->RestLogin = $this->configHipay->sandbox_ws_login;
                    $this->RestPassword = $this->configHipay->sandbox_ws_password;
                } else {
                    $this->RestLogin = $this->configHipay->production_ws_login;
                    $this->RestPassword = $this->configHipay->production_ws_password;
                }
            } else {
                if(isset($params['ws_login']) && !empty($params['ws_login'])){
                    $this->RestLogin    = $params['ws_login'];
                    $this->RestPassword = $params['ws_password'];
                    $params = [];
                }
                if($captcha){
                    $this->RestLogin    = false;
                    $this->RestPassword = '';
                    $params = [];
                }
            }

            //1 build http headers
            $header = array(
                "Content-Type: application/json;charset=UTF-8",
                "Accept: gzip,deflate",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
            );
            if($this->RestLogin){
                array_push($header,"php-auth-user:".$this->RestLogin,"php-auth-pw:".$this->RestPassword);
            }

            $ch = curl_init();

            //2 generic parameters
            curl_setopt($ch, CURLOPT_URL, $url.'/'.$function);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 60000);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            if(!$captcha){
                curl_setopt($ch, CURLOPT_POST, true );
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }else{
                curl_setopt($ch, CURLOPT_POST, false );
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            //3 proxy settings
            //conf proxy
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            if($this->configHipay->proxyUrl !== null){
                // Activation proxy server proxy
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);

                // Init proxy address
                curl_setopt($ch, CURLOPT_PROXY, $this->configHipay->proxyUrl);

                // Init proxy login/password
                $login = $this->configHipay->proxyLogin;
                $password = $this->configHipay->proxyPassword;
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$login:$password");
            }

            $result = curl_exec($ch);
            curl_close($ch);

            return json_decode($result);
        } catch (Exception $exception) {
            $this->module->_errors[] = $this->module->l('An error occurred while trying to contact the web service', 'HipayREST');
            return false;
        }
    }

}
