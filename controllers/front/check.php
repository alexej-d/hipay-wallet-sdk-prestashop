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

class HipayCheckModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ($this->module->active == false) {
            die;
        }

		$cart_id	= Tools::getValue('cart_id');
		$secure_key	= Tools::getValue('secure_key');
        
        $order_id = (int)Order::getOrderByCartId($cart_id);
        
        if ((! (bool)$order_id) || ($secure_key != $this->context->customer->secure_key)) {
        	return $this->sendErrorRequest('No order found.');
        }
        
        return $this->sendSuccessRequest($order_id);
    }

    protected function sendSuccessRequest($result)
    {
        $output = json_encode($result);

        die($output);
    }

    protected function sendErrorRequest($response)
    {
        http_response_code(204);

        $output = json_encode($response);

        die($output);
    }
}
