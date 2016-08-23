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
     * Login form
     */
    public function getLoginForm($complete_form = false)
    {
        // WS Login
        $form['form']['input'][] = $this->generateInputText('install_ws_login', $this->module->l('WS Login', 'HipayForm'), array(
            'class' => 'fixed-width-xxl',
            'hint' => $this->module->l('You can find it on your HiPay account, section "Integration > API", under "Webservice access"', 'HipayForm'),
            'required' => true,
        ));
        // WS Password
        $form['form']['input'][] = $this->generateInputText('install_ws_password', $this->module->l('WS Password', 'HipayForm'), array(
            'class' => 'fixed-width-xxl',
            'hint' => $this->module->l('You can find it on your HiPay account, section "Integration > API", under "Webservice access"', 'HipayForm'),
            'required' => true,
        ));
        // Button actions
        $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Reset', 'HipayForm'), array(
            'class' => 'pull-left',
            'name' => 'submitReset',
            'icon' => 'process-icon-eraser',
        ));
        $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Log in', 'HipayForm'), array(
            'name' => 'submitLogin',
            'icon' => 'process-icon-next',
        ));

        return $this->helper->generateForm(array($form));
    }

    /**
     * register form
     */
    public function getRegisterForm()
    {
        $this->helper->tpl_vars['fields_value'] = $this->getRegisterFormValues();

        // email
        $form['form']['input'][] = $this->generateInputEmail(
            'register_user_email', 
            $this->module->l('Email', 'HipayForm'), 
            $this->module->l('Please, enter your email address in the field above', 'HipayForm'),
            array(
                'class' => 'fixed-width-xxl',
            )
        );
        // First name
        $form['form']['input'][] = $this->generateInputText('register_firstname', $this->module->l('Firstname', 'HipayForm'), array(
            'class' => 'fixed-width-xxl',
            'hint' => $this->module->l('Please, enter your firstname in the field above', 'HipayForm'),
            'required' => true,
        ));
        // Last name
        $form['form']['input'][] = $this->generateInputText('register_lastname', $this->module->l('Lastname', 'HipayForm'), array(
            'class' => 'fixed-width-xxl',
            'hint' => $this->module->l('Please, enter your lastname in the field above', 'HipayForm'),
            'required' => true,
        ));
        // BUTTON Reset & Save
        $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Reset', 'HipayForm'), array(
            'class' => 'pull-left',
            'name' => 'submitReset',
            'icon' => 'process-icon-eraser',
        ));
        $form['form']['buttons'][] = $this->generateSubmitButton($this->module->l('Sign up', 'HipayForm'), array(
            'name' => 'submitRegister',
            'icon' => 'process-icon-next',
        ));

        return $this->helper->generateForm(array($form));    
    }
    /**
     * Register form values
     */
    public function getRegisterFormValues()
    {
        $email = Configuration::get('PS_SHOP_EMAIL');
        $values = array(
            'register_user_email' => Tools::getValue('register_user_email', $email),
        );
        return $values;
    }
    /**
     * Settings form
     */
    public function getSettingsForm($user_account)
    {
        $this->helper->tpl_vars['fields_value'] = $this->getSettingsFormValues($user_account);

        $options_rating = array(
            array('id_option' => 'ALL','name' => $this->module->l('For all ages', 'HipayForm')),
            array('id_option' => '+12','name' => $this->module->l('For ages 12 and over', 'HipayForm')),
            array('id_option' => '+16','name' => $this->module->l('For ages 16 and over', 'HipayForm')),
            array('id_option' => '+18','name' => $this->module->l('For ages 18 and over', 'HipayForm')),
        );

        $form = array('form' => array(
            'input' => array(
                // button switch mode prod/sandbox
                $this->generateSwitchButton('settings_switchmode', $this->module->l('Use test mode', 'HipayForm'), array(
                    'hint' => $this->module->l('When in test mode, payment cards are not really charged. Activate this options for testing purposes only.', 'HipayForm'),
                )),
            )
        ));

        return $this->helper->generateForm(array($form));
    }

    /**
     * personal infos form
     */
    public function getPersonalInfosForm()
    {
        
        // TODO

        return $this->helper->generateForm($form);
    }

    /**
     * Button form
     */
    public function getButtonForm($user_account)
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
