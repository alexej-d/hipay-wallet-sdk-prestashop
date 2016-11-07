<?php
/**
 * 2016 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.wallet@hipay.com>
 * @copyright 2016 HiPay
 * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
 */

class HipayConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return $this->displayConfirmationError();
        }

        $failure	= Tools::getIsset('failure');

        $cart_id	= Tools::getValue('cart_id');
        $secure_key	= Tools::getValue('secure_key');

        $cart = new Cart((int)$cart_id);
        $order_id = Order::getOrderByCartId((int)$cart->id);

        if ($failure) {
            return $this->displayConfirmationError();
        } elseif ($order_id) {
            return $this->displayConfirmation($cart, $order_id, $secure_key);
        } else {
            return $this->waitForConfirmation($cart_id, $secure_key);
        }
    }

    protected function displayConfirmation($cart, $order_id, $secure_key)
    {
        $customer = new Customer((int)$cart->id_customer);

        if (($order_id) && ($secure_key == $customer->secure_key)) {
            $params = http_build_query([
                'id_cart'	=> $cart->id,
                'id_module'	=> $this->module->id,
                'id_order'	=> $order_id,
                'key'		=> $customer->secure_key,
            ]);

            return Tools::redirect('index.php?controller=order-confirmation&'.$params);
        }
    }

    protected function displayConfirmationError()
    {
        $this->errors[] = $this->module->l('An error occurred. Please contact the merchant for more details.', 'HipayConfig');

        return $this->setTemplate('error.tpl');
    }

    protected function waitForConfirmation($cart_id, $secure_key)
    {
        $params = ['cart_id' => $cart_id, 'secure_key' => $secure_key];

        $this->context->controller->addJS(_MODULE_DIR_.'/'.$this->module->name.'/views/js/confirmation.js');

        $this->context->smarty->assign([
            'img_dir' 	=> _MODULE_DIR_.'/'.$this->module->name.'/views/img',
            'ajax_url'	=> $this->context->link->getModuleLink('hipay', 'check', $params, true),
        ]);

        return $this->setTemplate('waiting_validation.tpl');
    }
}
