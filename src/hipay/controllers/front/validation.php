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

class HipayValidationModuleFrontController extends ModuleFrontController
{
    public $configHipay;

    public function postProcess()
    {
        if ($this->module->active == false) {
            die;
        }

        if (Tools::getValue('xml')) {
            $xml = Tools::getValue('xml');
            $order = Tools::jsonDecode(Tools::jsonEncode((array)simplexml_load_string($xml)), 1);

            $cart_id = (int)$order['result']['merchantDatas']['_aKey_cart_id'];
            $customer_id = (int)$order['result']['merchantDatas']['_aKey_customer_id'];
            $secure_key = $order['result']['merchantDatas']['_aKey_secure_key'];

            $amount = (float)$order['result']['origAmount'];

            $this->context->cart = new Cart((int)$cart_id);
            $this->context->customer = new Customer((int)$customer_id);
            $this->context->currency = new Currency((int)$this->context->cart->id_currency);
            $this->context->language = new Language((int)$this->context->customer->id_lang);

            return $this->registerOrder($order, $cart_id, $amount, $secure_key);
        }

        return $this->displayError('An error occurred while processing payment');
    }

    protected function displayError($message)
    {
        $this->context->smarty->assign('path',
            '<a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Order').'</a>
            <span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));

        $this->errors[] = $this->module->l($message);

        return $this->setTemplate('error.tpl');
    }

    protected function registerOrder($order, $cart_id, $amount, $secure_key)
    {
        if ($this->isValidOrder($order) === true) {
            $status = trim(Tools::strtolower($order['result']['status']));
            $currency = $this->context->currency;

            switch ($status)
            {
                case 'ok':
                    $id_order_state = (int)Configuration::get('PS_OS_PAYMENT');
                    break;
                case 'nok':
                    $id_order_state = (int)Configuration::get('PS_OS_ERROR');
                    break;
                case 'cancel':
                    $id_order_state = (int)Configuration::get('PS_OS_CANCELED');
                    break;
                case 'waiting':
                    $id_order_state = (int)Configuration::get('HIPAY_OS_WAITING');
                    break;
                default:
                    $id_order_state = (int)Configuration::get('PS_OS_ERROR');
                    break;
            }

            return $this->placeOrder($order, $id_order_state, $cart_id, $currency, $amount, $secure_key);
        }
    }

    protected function isValidOrder($order)
    {
        if (isset($order['result']) == false) {
            return false;
        } elseif ((isset($order['result']['status']) == false) || (isset($order['result']['merchantDatas']) == false)) {
            return false;
        }

        // init config HiPay
        $configHipay = $this->module->configHipay;

        $sandbox_mode = (bool)$configHipay['sandbox_mode'];

        if ($sandbox_mode) {
            $ws_login = (int)$configHipay['sandbox_ws_login'];
        } else {
            $ws_login = (int)$configHipay['production_ws_login'];
        }

        $valid_secure_key = ($this->context->customer->secure_key == $order['result']['merchantDatas']['_aKey_secure_key']);
        $valid_token = (Tools::encrypt($ws_login.$order['result']['merchantDatas']['_aKey_cart_id']) == $order['result']['merchantDatas']['_aKey_token']);

        return $valid_secure_key && $valid_token;
    }

    protected function placeOrder($order, $id_order_state, $cart_id, $currency, $amount, $secure_key)
    {
        $order_id = (int)Order::getOrderByCartId($cart_id);

        if ((bool)$order_id != false) {
            $order = new Order($order_id);

            if ((int)$order->getCurrentState() == (int)Configuration::get('HIPAY_OS_WAITING')) {
                $order_history = new OrderHistory();
                
                $order_history->id_order = $order_id;
                $order_history->id_order_state = $id_order_state;
                $order_history->changeIdOrderState($id_order_state, $order_id);

                return $order_history->addWithemail();
            }

            return $this->displayError('An error occurred while saving transaction details');
        } else {
            if ($id_order_state != (int)Configuration::get('PS_OS_ERROR')) {
                $payment_method = $order['result']['paymentMethod'];
                $transaction_id = $order['result']['transid'];

                $extra_vars = ['transaction_id' => Tools::safeOutput($transaction_id)];

                 // init config HiPay
                $configHipay = $this->module->configHipay;

                $sandbox_mode = (bool)$configHipay['sandbox_mode'];

                $message = Tools::jsonEncode([
                    "Environment"       => $sandbox_mode ? 'SANDBOX' : 'PRODUCTION',
                    "Payment method"    => Tools::safeOutput($payment_method),
                    "Transaction ID"    => Tools::safeOutput($transaction_id),
                ]);

            } else {
                $extra_vars     = [];
                $error_details  = Tools::safeOutput(print_r($order['result'], true));
                $message        = Tools::jsonEncode(["Error" => $error_details]);
            }

            return $this->module->validateOrder($cart_id, $id_order_state, $amount, $this->module->displayName, $message, $extra_vars, (int)$currency->id, false, $secure_key);
        }
    }
}
