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
    protected $currency         = false;

    protected $logs;

    public function __construct()
    {
        parent::__construct();

        if (!$this->module->active) {
            $this->sendErrorRequest('Invalid request.');
        }

        // init logs
        $this->logs = new HipayLogs($this->module);

        require_once _PS_ROOT_DIR_._MODULE_DIR_.$this->module->name.'/classes/webservice/HipayRefund.php';
    }

    public function init()
    {
        $this->logs->refundLogs('##################');
        $this->logs->refundLogs('# START Refund');
        $this->logs->refundLogs('##################');

        $this->logs->refundLogs('# Start getRefundValues');
        $this->getRefundValues();
        $this->logs->refundLogs('# End getRefundValues');

        if ($this->amount == 0) {
            $this->logs->errorLogsHipay('..:: ! ::.. ----- Invalid parameters. amount = 0 ----- ..:: ! ::.. ');
            $this->sendErrorRequest('Invalid parameters.');
        }

        // init currency
        $iso_code   = $this->currency->iso_code;
        $accountID  = $this->module->configHipay->selected->currencies->production->$iso_code->accountID;

        $params = [
            'amount'                => $this->amount,
            'transactionPublicId'   => $this->id_transaction,
            'wsSubAccountId'        => $accountID,
        ];

        $this->logs->refundLogs('---- Params');
        $this->logs->refundLogs(print_r($params,true));

        $refund = new HipayRefund($this->module);

        $this->logs->refundLogs('# Start process');
        $result = $refund->process($params, $this->sandbox);
        $this->logs->refundLogs('# End process');

        if ($result->cardResult->code != 0) {
            $this->logs->errorLogsHipay($result->cardResult->description);
            $this->sendErrorRequest($result->cardResult->description);
        } else if ( $result->cardResult == null ){
            $this->logs->errorLogsHipay('The webservice is unavailable');
            $this->sendErrorRequest('The webservice is unavailable, please try again.');
        } else {
            $this->logs->refundLogs('# Start saveRefundDetails');
            $this->saveRefundDetails($result);
            $this->logs->refundLogs('# End saveRefundDetails');

            $this->logs->refundLogs('# Start sendSuccessRequest');
            $this->sendSuccessRequest($result);
            $this->logs->refundLogs('# End sendSuccessRequest');
        }

        $this->sendErrorRequest('Invalid request.');
    }

    public function getRefundValues()
    {
        $this->sandbox			= Tools::getValue('sandbox');

        $this->id_order			= Tools::getValue('id_order');
        $this->id_transaction	= Tools::getValue('id_transaction');

        $this->logs->refundLogs('---- sandbox        = ' . $this->sandbox);
        $this->logs->refundLogs('---- id_order       = ' . $this->id_order);
        $this->logs->refundLogs('---- id_transaction = ' . $this->id_transaction);

        $order = new Order($this->id_order);


        if ($order->id && $this->id_transaction) {
            $this->amount = Tools::getValue('amount', $order->getTotalPaid());
            $this->currency = new Currency($order->id_currency);

            $this->logs->refundLogs('---- amount        = ' . $this->amount);

            return true;
        }

        $this->logs->refundLogs('..:: ! ::..  ----- Invalid parameters. ----- ..:: ! ::..  ');

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

        $this->logs->refundLogs('Message added = ' . $details);
    }

    protected function sendSuccessRequest($result)
    {
        $output = json_encode($result->cardResult);

        $this->logs->refundLogs('output = ' . $output);

        die($output);
    }

    protected function sendErrorRequest($response)
    {
        http_response_code(406);

        $output = json_encode($response);

        die($output);
    }
}
