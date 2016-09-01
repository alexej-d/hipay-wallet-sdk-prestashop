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

class AdminHiPayRefundController extends ModuleAdminController
{
    protected $amount			= false;
    protected $sandbox			= false;

    protected $id_transaction	= false;
    protected $id_order         = false;

    public function __construct()
    {
        parent::__construct();

        if (!$this->module->active) {
            $this->sendErrorRequest('Invalid request.');
        }

        require_once _PS_ROOT_DIR_._MODULE_DIR_.$this->module->name.'/classes/webservice/HipayRefund.php';
    }

    public function init()
    {
        $this->getRefundValues();

        if ($this->amount == 0) {
            $this->sendErrorRequest('Invalid parameters.');
        }

        $params = [
            'amount'                => $this->amount,
            'transactionPublicId'   => $this->id_transaction,
        ];

        $refund = new HipayRefund($this->module);
        $result = $refund->process($params, $this->sandbox);

        if ($result->cardResult->code != 0) {
            $this->sendErrorRequest($result->cardResult->description);
        } else {
            $this->saveRefundDetails($result);
            $this->sendSuccessRequest($result);
        }

        $this->sendErrorRequest('Invalid request.');
    }

    public function getRefundValues()
    {
        $this->sandbox			= Tools::getValue('sandbox');

        $this->id_order			= Tools::getValue('id_order');
        $this->id_transaction	= Tools::getValue('id_transaction');

        $order = new Order($this->id_order);

        if ($order->id && $this->id_transaction) {
            $this->amount = Tools::getValue('amount', $order->getTotalPaid());

            return true;
        }

        $this->sendErrorRequest('Invalid parameters.');

        return false;
    }

    private function saveRefundDetails($result)
    {
        $details = json_encode($result->cardResult);
        $state = Tools::getIsset('amount') ? 'PARTIALLY' : 'TOTALLY';
        $id_order_state	= (int)Configuration::get('HIPAY_OS_'.$state.'_REFUNDED');

        $this->addRefundMessage($details);

        $order_history = new OrderHistory();
        $order_history->id_order = (int)$this->id_order;
        $order_history->id_employee = (int)$this->context->employee->id;
        $order_history->id_order_state = $id_order_state;
        $order_history->add();
    }

    protected function addRefundMessage($details)
    {
        $message = new Message();

        $message->message = $details;
        $message->id_order = (int)$this->id_order;
        $message->private = 1;

        $status = $message->add();
    }

    protected function sendSuccessRequest($result)
    {
        $output = json_encode($result->cardResult);

        die($output);
    }

    protected function sendErrorRequest($response)
    {
        http_response_code(406);

        $output = json_encode($response);

        die($output);
    }
}
