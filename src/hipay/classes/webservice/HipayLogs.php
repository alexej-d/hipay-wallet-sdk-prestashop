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

class HipayLogs
{
    public $enable = true;

    public function __construct($module_instance)
    {
        $this->context      = Context::getContext();
        $this->module       = $module_instance;
        // init config hipay
        $this->configHipay  = $module_instance->configHipay;
        $this->enable      = (isset($this->configHipay->mode_debug) ? $this->configHipay->mode_debug : true);
    }

    /**
     *
     * LOG Errors
     *
     */
    public function errorLogsHipay($msg)
    {
        if($this->enable)
        {
            $fp = fopen(_PS_MODULE_DIR_.'/hipay/logs/'.date('Y-m-d').'-error-logs.txt','a+');
            fseek($fp,SEEK_END);
            fputs($fp,$msg . PHP_EOL);
            fclose($fp);
        }

    }

    /**
     *
     * LOG APP
     *
     */
    public function logsHipay($msg)
    {
        if($this->enable)
        {
            $fp = fopen(_PS_MODULE_DIR_.'/hipay/logs/'.date('Y-m-d').'-logs.txt','a+');
            fseek($fp,SEEK_END);
            fputs($fp,$msg . PHP_EOL );
            fclose($fp);
        }

    }

}
