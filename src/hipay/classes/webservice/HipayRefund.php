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

class HipayRefund extends HipayWS
{
    protected $client_url = '/soap/refund-v2';

    /* SOAP method: card */
    public function process($params, $sandbox = false)
    {
        // $params = [
        //     'amount'                => 4,
        //     'transactionPublicId'   => $id_transaction,
        // ];

        return $this->executeQuery('card', $params, $sandbox);
    }
}
