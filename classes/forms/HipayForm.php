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

require_once(dirname(__FILE__).'/HipayFormInputs.php');

class HipayForm extends HipayFormInputs {

    protected $context = false;
    protected $helper = false;
    protected $module = false;

    public $name = false;
    public $configHipay;

    public function __construct($module_instance)
    {
        // Requirements
        $this->context = Context::getContext();
        $this->module = $module_instance;
        $this->name = $module_instance->name;
        // init config hipay
        $this->configHipay = $module_instance->configHipay;

        // Form
        $this->helper = new HelperForm();

        $this->helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false);
        $this->helper->currentIndex .= '&'.http_build_query(array(
            'configure' => 'hipay',
            'tab_module' => 'payments_gateways',
            'module_name' => 'hipay',
        ));

        $this->helper->module = $this;
        $this->helper->show_toolbar = false;
        $this->helper->token = Tools::getAdminTokenLite('AdminModules');

        $this->helper->tpl_vars = array(
            'id_language' => $this->context->language->id,
            'languages' => $this->context->controller->getLanguages()
        );

        return $this->helper;
    }

    public function generateForm($form)
    {
        return $this->helper->generateForm($form);
    }

    /**
     * Customer's service form
     */
    public function getCustomersServiceForm($user_account)
    {
        $this->context->smarty->assign('localized_rates_pdf_link', '<a href="' . $this->module->getLocalizedRatesPDFLink() . '" target="_blank">');
        $this->helper->tpl_vars['fields_value'] = $this->getCustomersServiceFormValues($user_account);

        $form = array('form' => array(
            'input' => array(
                $this->generateInputFree('info_sandbox_mode', false, array('col' => 12, 'offset' => 0)),

                $this->generateInputFree('customers_service_q_a', false, array('col' => 12, 'offset' => 0)),

                $this->generateInputFree('customers_service_contact_details', false, array('col' => 12, 'offset' => 0)),
                $this->generateInputFree('customers_service_contact_form', $this->module->l('By email', 'HipayForm')),
                $this->generateInputFree('customers_service_address', $this->module->l('Address', 'HipayForm')),
                $this->generateInputFree('customers_service_address_2', $this->module->l('or', 'HipayForm')),

                $this->generateInputFree('customers_service_contact_info', false, array('col' => 12, 'offset' => 0)),
                $this->generateInputFree('customers_service_email', $this->module->l('Email', 'HipayForm')),
                $this->generateInputFree('customers_service_shop_name', $this->module->l('Shop name', 'HipayForm')),
                $this->generateInputFree('customers_service_account_id', $this->module->l('Account number', 'HipayForm')),
            ),
        ));

        return $this->helper->generateForm(array($form));
    }

    /**
     * Login form
     */
    public function getLoginForm($complete_form = false)
    {
        $this->helper->tpl_vars['fields_value'] = $this->getLoginFormValues($complete_form);

        $email = Tools::getValue('install_user_email');
        $is_email = (bool)Validate::isEmail($email);
        $email_description = $is_email ? null : $this->module->l('Please, enter your email address in the field above', 'HipayForm');

        $form = array('form' => array(
            'input' => array(
                $this->generateInputEmail('install_user_email', $this->module->l('Email', 'HipayForm'), $email_description),
            ),
        ));

        if ($complete_form == 'new_account') {
            $form['form']['input'][] = $this->generateInputText('install_user_first_name', $this->module->l('First name', 'HipayForm'), array(
                'class' => 'fixed-width-xxl',
                'required' => true,
            ));
            $form['form']['input'][] = $this->generateInputText('install_user_last_name', $this->module->l('Last name', 'HipayForm'), array(
                'class' => 'fixed-width-xxl',
                'required' => true,
            ));

            $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Reset', 'HipayForm'), array(
                'class' => 'pull-left',
                'name' => 'submitReset',
                'icon' => 'process-icon-eraser',
            ));
            $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Subscribe', 'HipayForm'), array(
                'name' => 'submitLogin',
                'icon' => 'process-icon-next',
            ));
        } elseif ($complete_form == 'existing_account') {
            $form['form']['input'][] = $this->generateInputText('install_website_id', $this->module->l('Website ID', 'HipayForm'), array(
                'class' => 'fixed-width-lg',
                'hint' => $this->module->l('You can find it on your HiPay account, section "Creating a payment button" under the URL of your website', 'HipayForm'),
                'required' => true,
            ));
            $form['form']['input'][] = $this->generateInputText('install_ws_login', $this->module->l('WS Login', 'HipayForm'), array(
                'class' => 'fixed-width-xxl',
                'hint' => $this->module->l('You can find it on your HiPay account, section "Integration > API", under "Webservice access"', 'HipayForm'),
                'required' => true,
            ));
            $form['form']['input'][] = $this->generateInputText('install_ws_password', $this->module->l('WS Password', 'HipayForm'), array(
                'class' => 'fixed-width-xxl',
                'hint' => $this->module->l('You can find it on your HiPay account, section "Integration > API", under "Webservice access"', 'HipayForm'),
                'required' => true,
            ));

            $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Reset', 'HipayForm'), array(
                'class' => 'pull-left',
                'name' => 'submitReset',
                'icon' => 'process-icon-eraser',
            ));
            $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Log in', 'HipayForm'), array(
                'name' => 'submitLogin',
                'icon' => 'process-icon-next',
            ));
        } else {
            $form['form']['input'][] = $this->generateInputFree('install_user_info', false, array('col' => 12, 'offset' => 0));

            $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Log in / Subscribe', 'HipayForm'), array(
                'name' => 'submitLogin',
                'icon' => 'process-icon-next',
            ));
        }

        return $this->helper->generateForm(array($form));
    }

    /**
     * Settings form
     */
    public function getSettingsForm($user_account)
    {
        $this->helper->tpl_vars['fields_value'] = $this->getSettingsFormValues($user_account);

        $form = array('form' => array(
            'input' => array(
                $this->generateInputFree('info_sandbox_mode', false, array('col' => 12, 'offset' => 0)),

                $this->generateInputFree('main_account_details', false, array('col' => 12, 'offset' => 0)),
                $this->generateInputFree('main_account_email', $this->module->l('Email', 'HipayForm')),
                $this->generateInputFree('main_account_shop_name', $this->module->l('Shop name', 'HipayForm')),
                $this->generateInputFree('main_account_id', $this->module->l('Account ID', 'HipayForm')),
                $this->generateInputFree('main_account_balance', $this->module->l('Balance', 'HipayForm'), array(
                    'hint' => $this->module->l('Your account balance is automatically updated after each new transaction', 'HipayForm'),
                )),

                $this->generateInputFree('sub_accounts_details', false, array('col' => 12, 'offset' => 0)),
                $this->generateInputFree('sub_accounts_description', false, array('col' => 12, 'offset' => 0)),
                $this->generateInputFree('sub_accounts_values', false, array('col' => 12, 'offset' => 0)),
            ),
            'buttons' => array(
                $this->generateSubmitButton($this->module->l('Log out', 'HipayForm'), array(
                    'name' => 'submitReset',
                    'icon' => 'process-icon-power',
                    'js' => 'return confirm(\''.$this->module->l('Are you sure you want to log out?', 'HipayForm').'\');',
                )),
            ),
        ));

        return $this->helper->generateForm(array($form));
    }

    /**
     * Settings form
     */
    public function getSandboxForm()
    {
        $this->helper->tpl_vars['fields_value'] = $this->getSandboxFormValues();

        $form = array(
            array(
                'form' => array(
                    'input' => array(
                        $this->generateSwitchButton('sandbox_account_mode', $this->module->l('Test mode', 'HipayForm')),

                        $this->generateFormSplit(),

                        $this->generateInputText('website_id', $this->module->l('Website ID', 'HipayForm'), array(
                            'class' => 'fixed-width-md',
                            'hint' => $this->module->l('You can find it on your HiPay account, section "Creating a payment button" under the URL of your website', 'HipayForm'),
                            'required' => true,
                        )),
                        $this->generateInputText('ws_login', $this->module->l('WS Login', 'HipayForm'), array(
                            'class' => 'fixed-width-xxl',
                            'hint' => $this->module->l('You can find it on your HiPay account, section "Integration > API", under "Webservice access"', 'HipayForm'),
                            'required' => true,
                        )),
                        $this->generateInputText('ws_password', $this->module->l('WS Password', 'HipayForm'), array(
                            'class' => 'fixed-width-xxl',
                            'hint' => $this->module->l('You can find it on your HiPay account, section "Integration > API", under "Webservice access"', 'HipayForm'),
                            'required' => true,
                        )),

                        $this->generateFormSplit(),
                    ),
                    'buttons' => array(
                        $this->generateSubmitButton($this->module->l('Save', 'HipayForm'), array(
                            'name' => 'submitSandboxMode',
                            'icon' => 'process-icon-save',
                        )),
                    ),
                ),
            ),
            array(
                'form' => array(
                    'input' => array(
                        $this->generateInputFree('sandbox_mode_info', false, array('col' => 12, 'offset' => 0)),
                    ),
                ),
            ),
        );

        if ($this->configHipay->sandox_mode) {
            $form[0]['form']['input'][] = $this->generateInputText('sandbox_website_id', $this->module->l('Website ID (Sandbox)', 'HipayForm'), array(
                'class' => 'fixed-width-md',
                'hint' => $this->module->l('You can find it on your HiPay test account, section "Creating a payment button" under the URL of your website', 'HipayForm'),
                'required' => true,
            ));

            $form[0]['form']['input'][] = $this->generateInputText('sandbox_ws_login', $this->module->l('WS Login (Sandbox)', 'HipayForm'), array(
                'class' => 'fixed-width-xxl',
                'hint' => $this->module->l('You can find it on your HiPay test account, section "Integration > API", under "Webservice access"', 'HipayForm'),
                'required' => true,
            ));

            $form[0]['form']['input'][] = $this->generateInputText('sandbox_ws_password', $this->module->l('WS Password (Sandbox)', 'HipayForm'), array(
                'class' => 'fixed-width-xxl',
                'hint' => $this->module->l('You can find it on your HiPay test account, section "Integration > API", under "Webservice access"', 'HipayForm'),
                'required' => true,
            ));

            $form[0]['form']['input'][] = $this->generateFormSplit();
        }

        $form[0]['form']['input'][] = $this->generateInputFree('sandbox_mode_description', false, array('col' => 12, 'offset' => 0));

        return $this->helper->generateForm($form);
    }

    /**
     * Transactions form
     */
    public function getTransactionsForm($user_account)
    {
        $this->helper->tpl_vars['fields_value'] = $this->getTransactionsFormValues($user_account);

        $form = array('form' => array(
            'input' => array(
                $this->generateInputFree('info_sandbox_mode', false, array('col' => 12, 'offset' => 0)),

                $this->generateInputFree('transactions_account_id', $this->module->l('Account ID', 'HipayForm')),
                $this->generateInputFree('transactions_current_date', $this->module->l('Date', 'HipayForm')),
                $this->generateInputFree('transactions_dates_range', $this->module->l('Range', 'HipayForm')),
                $this->generateInputFree('transactions_details', $this->module->l('Transactions', 'HipayForm'), array('col' => 9, 'offset' => 0)),
            ),
            'buttons' => array(
                $this->generateSubmitButton($this->module->l('Refresh', 'HipayForm'), array(
                    'name' => 'submitDateRange',
                    'icon' => 'process-icon-refresh',
                    'value' => 'refresh',
                )),
            ),
        ));

        return $this->helper->generateForm(array($form));
    }

    /**
     * Customer's service form values
     */
    public function getCustomersServiceFormValues()
    {
        $sandbox_mode = $this->configHipay->sandox_mod;
        $user_account_id = $sandbox_mode ? $this->configHipay->sandox_user_account_id : $this->configHipay->production_user_account_id;

        $template_path = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/faq.tpl';

        return array(
            'info_sandbox_mode' => $sandbox_mode ? '<div class="alert alert-warning">'.$this->module->l('The module is running in test mode.', 'HipayForm').'</div>' : null,

            'customers_service_q_a' => $this->context->smarty->fetch($template_path),

            'customers_service_contact_details' =>  '<h4 class="form-control-static"><i class="icon icon-question-circle"></i> '.$this->module->l('I need some help, who should I contact?', 'HipayForm').'</h4>',
            'customers_service_contact_form' => '<p class="form-control-static"><a href="mailto:prestashop@hipay.com" target="_blank">'.$this->module->l('prestashop@hipay.com', 'HipayForm').'</a></strong></p>',
            'customers_service_address' => '<p class="form-control-static">'.sprintf($this->module->l('HiPay / Société HPME%1$s19 Avenue des Volontaires%1$s1160 Bruxelles - Belgium', 'HipayForm'), '<br />').'</strong></p>',
            'customers_service_address_2' => '<p class="form-control-static">'.sprintf($this->module->l('HiPay%1$s6 place du Colonel Bourgoin%1$s75012 Paris - France', 'HipayForm'), '<br />').'</strong></p>',

            'customers_service_contact_info' =>  '<h4 class="form-control-static"><i class="icon icon-info-circle"></i> '.$this->module->l('Please remind them your account details', 'HipayForm').'</h4>',
            'customers_service_email' => '<p class="form-control-static"><strong>'.$this->configHipay->user_mail.'</strong></p>',
            'customers_service_shop_name' => '<p class="form-control-static"><strong>'.Configuration::get('PS_SHOP_NAME').'</strong></p>',
            'customers_service_account_id' => '<p class="form-control-static"><strong>'.$user_account_id.'</strong></p>',
        );
    }

    /**
     * Login form values
     * @param string $complete_form
     */
    public function getLoginFormValues($complete_form = false)
    {
        $email = $this->configHipay->user_mail;
        if (! $email) {
          $email = Configuration::get('PS_SHOP_EMAIL');
        }

        $values = array(
            'install_user_email' => Tools::getValue('install_user_email', $email),
            'install_user_info' => $this->module->l('If you have any questions or need help creating a PrestaShop Payments by HiPay account, contact us at prestashop@hipay.com', 'HipayForm'),
        );

        if ($complete_form == 'new_account') {
            $values['install_user_first_name'] = Tools::getValue('install_user_first_name');
            $values['install_user_last_name'] = Tools::getValue('install_user_last_name');
        } elseif ($complete_form == 'existing_account') {
            $values['install_website_id'] = Tools::getValue('install_website_id', $this->configHipay->production_website_id);
            $values['install_ws_login'] = Tools::getValue('install_ws_login', $this->configHipay->production_ws_login);
            $values['install_ws_password'] = Tools::getValue('install_ws_password', $this->configHipay->production_ws_password);
        }

        return $values;
    }

    /**
    * Sandbox form values
    */
    public function getSandboxFormValues()
    {
        $template_path = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/sandbox.tpl';

        return array(
            'input_split' => '<br/>',
            'sandbox_account_mode' => Tools::getValue('sandbox_account_mode', $this->configHipay->sandbox_mode),
            'website_id' => Tools::getValue('website_id', $this->configHipay->production_website_id),
            'ws_login' => Tools::getValue('ws_login', $this->configHipay->production_ws_login),
            'ws_password' => Tools::getValue('ws_password', $this->configHipay->production_ws_password),
            'sandbox_website_id' => Tools::getValue('sandbox_website_id', $this->configHipay->sandbox_website_id),
            'sandbox_ws_login' => Tools::getValue('sandbox_ws_login', $this->configHipay->sandbox_ws_login),
            'sandbox_ws_password' => Tools::getValue('sandbox_ws_password', $this->configHipay->sandbox_ws_password),
            'sandbox_mode_description' => '<p class="form-control-static">'.
                $this->module->l('The test mode allows you to check if payments are well processed by the system, without spending a dime.', 'HipayForm').'<br />'.
                $this->module->l('It works with a dedicated test account: you have received an email to finalize it.', 'HipayForm').
            '</p>',
            'sandbox_mode_info' => $this->context->smarty->fetch($template_path),
        );
    }

    /**
     * Settings form values
     */
    public function getSettingsFormValues($user_account)
    {
        $sandbox_mode = $this->configHipay->sandbox_mode;
        $user_account_id = $sandbox_mode ? $this->configHipay->sandox_user_account_id : $this->configHipay->production_user_account_id;

        $accounts = $user_account->getBalances();
        $account = $user_account->getMainAccountBalance($accounts);

        if (isset($account->balance) == false) {
            $account = new stdClass;
            $account->balance = 0;
            $account->currency = $this->context->currency->iso_code;
        }

        $main_account_values = array(
            'info_sandbox_mode' => $sandbox_mode ? '<div class="alert alert-warning">'.$this->module->l('The module is running in test mode.', 'HipayForm').'</div>' : null,

            'main_account_details' => '<h4 class="form-control-static">'.$this->module->l('Your main account', 'HipayForm').'</h4>',
            'main_account_email' => '<p class="form-control-static"><strong>'.$this->configHipay->user_mail.'</strong></p>',
            'main_account_shop_name' => '<p class="form-control-static"><strong>'.Configuration::get('PS_SHOP_NAME').'</strong></p>',
            'main_account_id' => '<p class="form-control-static"><strong>'.$user_account_id.'</strong></p>',
            'main_account_balance' => '<p class="form-control-static"><strong>'.number_format($account->balance, 2).' '.(string)$account->currency.'</strong></p>',
        );

        $details = null;

        if ((is_array($accounts->balances->item) == true) && (count($accounts->balances->item) > 0)) {
            foreach ($accounts->balances->item as $sub_account) {
                if ($sub_account->userAccountId != $account->userAccountId) {
                    $details .= '<tr>
                        <td>'.$sub_account->userAccountId.'</td>
                        <td>'.$this->module->l($this->module->currencies_titles[(string)$sub_account->currency], 'HipayForm').'</td>
                        <td>'.number_format($sub_account->balance, 2).' '.(string)$sub_account->currency.'</td>
                    </tr>';
                }
            }
        } else {
            $details = '<tr><td colspan="4" class="text-center"><em>'.$this->module->l('You have no sub-accounts', 'HipayForm').'.</em></td></tr>';
        }

        $sub_accounts_values = array(
            'sub_accounts_details' => '<h4 class="form-control-static">'.$this->module->l('Sub-accounts', 'HipayForm').'</h4>',
            'sub_accounts_description' => '<p class="form-control-static">'.
                $this->module->l('Thanks to the below sub-accounts, you can accept payments in several currencies on your store.', 'HipayForm').'<br />'.
                $this->module->l('To withdraw money from your sub-accounts, you should transfer their respective balances to your main account first.', 'HipayForm').' '.
                sprintf($this->module->l('Some fees might apply, please %1$sclick here for more info%2$s.', 'HipayForm'), '<a href="http://www.prestashop.com/download/pdf/pspayments/PrestaShop_Payments-detalles_de_precios.pdf" target="_blank">', '</a>').
            '</p>',
            'sub_accounts_values' => '<table class="form-control-static table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th><strong>'.$this->module->l('Account ID', 'HipayForm').'</strong></th>
                    <th><strong>'.$this->module->l('Currency', 'HipayForm').'</strong></th>
                    <th><strong>'.$this->module->l('Balance', 'HipayForm').'</strong></th>
                </tr>
            </thead>
            <tbody>'.$details.'</tbody>
            </table>',
        );

        return array_merge($main_account_values, $sub_accounts_values);
    }

    /**
     * Transactions form values
     */
    public function getTransactionsFormValues($user_account)
    {
        $calendar_helper = new HelperCalendar();

        $hipay_date_from = isset($this->context->cookie->hipay_date_from)? $this->context->cookie->hipay_date_from : date('Y-m-dT').'00:00:00';
        $hipay_date_to = isset($this->context->cookie->hipay_date_to) ? $this->context->cookie->hipay_date_to : date('Y-m-dT').'23:59:59';

        $calendar_helper->setDateFrom(Tools::getValue('date_from', date('Y-m-d', strtotime($hipay_date_from))));
        $calendar_helper->setDateTo(Tools::getValue('date_to', date('Y-m-d', strtotime($hipay_date_to))));

        $user_account_id = $this->configHipay->sandbox_mode ? $this->configHipay->sandox_user_account_id : $this->configHipay->production_user_account_id;

        $template_path = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/transactions.tpl';

        $this->context->smarty->assign(array(
            'date_from' => date('Y-m-d 00:00', strtotime($hipay_date_from)),
            'date_to' => date('Y-m-d 23:59', strtotime($hipay_date_to)),
            'transactions_dates_form' => $calendar_helper->generate(),
        ));

        $calendar_template = $this->context->smarty->fetch($template_path);

        $transactions_values = array(
            'info_sandbox_mode' => $this->configHipay->sandbox_mode ? '<div class="alert alert-warning">'.$this->module->l('The module is running in test mode.', 'HipayForm').'</div>' : null,

            'transactions_account_id' => '<p class="form-control-static">N&deg;'.$user_account_id.'</p>',
            'transactions_current_date' => '<p class="form-control-static">'.date('Y-m-d H:i:s').'</p>',
            'transactions_dates_range' => $calendar_template,
        );

        $details = null;
        $transactions = $user_account->getTransactions();

        if ((is_array($transactions) == true) && (count($transactions) > 0)) {
            foreach ($transactions as $transaction) {
                switch ($transaction->transactionStatus) {
                    case 'CAPTURED':
                        $icon = 'check';
                        break;
                    case 'UNAUTHED':
                        $icon = 'remove';
                        break;
                    default:
                        $icon = 'clock-o';
                        break;
                }

                $details .= '<tr>
                    <td>'.date('Y-m-d H:i', strtotime($transaction->createdAt)).'</td>
                    <td>'.number_format($transaction->amount, 2).' '.(string)$transaction->currency.'</td>
                    <td>'.number_format($transaction->fees, 2).' '.(string)$transaction->currencyFees.'</td>
                    <td class="text-center"><i class="icon icon-'.$icon.'"></i></td>
                </tr>';
            }
        } else {
            $details = '<tr><td colspan="4" class="text-center"><em>'.$this->module->l('You have no transaction for the selected period', 'HipayForm').'.</em></td></tr>';
        }

        $transactions_values['transactions_details'] = '<table class="form-control-static table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th><strong>'.$this->module->l('Created at', 'HipayForm').'</strong></th>
                    <th><strong>'.$this->module->l('Amount', 'HipayForm').'</strong></th>
                    <th><strong>'.$this->module->l('Fees', 'HipayForm').'</strong></th>
                    <th><strong>'.$this->module->l('Status', 'HipayForm').'</strong></th>
                </tr>
            </thead>
            <tbody>'.$details.'</tbody>
        </table>';

        return $transactions_values;
    }

    /**
     * Global refund form
     */
    public function getRefundForm($order)
    {
        $this->helper->tpl_vars['fields_value'] = $this->getRefundFormValues($order);

        $form = [
            'form' => [
                'buttons' => [
                    $this->generateSubmitButton($this->module->l('Refund', 'HipayForm'), [
                        'name' => 'submitTotalRefund',
                        'icon' => 'process-icon-undo',
                        'value' => 'refresh',
                    ]),
                ],
            ]
        ];

        return $this->helper->generateForm([$form]);
    }


    /**
     * Total refund form values
     */
    public function getRefundFormValues($order)
    {
        return [];
    }

}
