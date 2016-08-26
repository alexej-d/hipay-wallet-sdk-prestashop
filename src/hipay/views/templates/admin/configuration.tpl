{**
* 2016 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2016 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}

<div class="panel">
    <!-- MARKETING START -->
    {* include file marketing.tpl *}
    {include file='./marketing.tpl'}
    <!-- MARKETING END -->
</div>
<!-- ALERTS START -->
{* include file alerts.tpl *}
{include file='./alerts.tpl'}
<!-- ALERTS END -->
<div role="tabpanel">
	<ul class="nav nav-tabs" role="tablist">
		{if ($is_logged == false)}
			<li role="presentation" class="active"><a href="#login_form" aria-controls="login_form" role="tab" data-toggle="tab">
				<span class="icon icon-user"></span> {l s='Login' mod='hipay'}</a>
			</li>
			<li role="presentation" class=""><a href="#register_form" aria-controls="register_form" role="tab" data-toggle="tab">
                <span class="icon icon-plus-sign"></span> {l s='Create a new HiPay Direct account' mod='hipay'}</a>
			</li>
		{else}
			<li role="presentation"{if ((isset($active_tab) == false) || ($active_tab == 'settings'))} class="active"{/if}><a href="#settings_form" aria-controls="settings_form" role="tab" data-toggle="tab">
				<span class="icon icon-cogs"></span> {l s='Settings' mod='hipay'}</a>
			</li>
			<li role="presentation"{if ((isset($active_tab) == true) && ($active_tab == 'button'))} class="active"{/if}><a href="#button_form" aria-controls="button_form" role="tab" data-toggle="tab">
				<span class="icon icon-money"></span> {l s='Payment' mod='hipay'}</a>
			</li>
            <li role="presentation"{if ((isset($active_tab) == true) && ($active_tab == 'faq'))} class="active"{/if}><a href="#faq" aria-controls="faq" role="tab" data-toggle="tab">
                <span class="icon icon-money"></span> {l s='FAQ' mod='hipay'}</a>
            </li>
            <li role="presentation"{if ((isset($active_tab) == true) && ($active_tab == 'logs'))} class="active"{/if}><a href="#logs" aria-controls="logs" role="tab" data-toggle="tab">
                <span class="icon icon-money"></span> {l s='Logs' mod='hipay'}</a>
            </li>
		{/if}
        <li class="pull-right"><a href="https://{if $sandbox == true}test-{/if}www.hipaywallet.com/" role="tab" target="_blank" id="login_hipay_link">
            <span class="icon icon-arrow-right"></span> {l s='Go to HiPay Wallet' mod='hipay'}</a>
        </li>
        <li class="pull-right"><a href="https://{if $sandbox == true}test-{/if}www.hipaydirect.com/" role="tab" target="_blank" id="login_hipay_link">
            <span class="icon icon-arrow-right"></span> {l s='Go to HiPay Direct' mod='hipay'}</a>
        </li>
	</ul>

	<div class="tab-content">
		{if ($is_logged == false)}
			<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == false) || ($active_tab == 'login'))} active{/if}" id="login_form">
                {include file='./login.tpl'}
            </div>
			<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == true) || ($active_tab == 'register'))} active{/if}" id="register_form">
                {include file='./register.tpl'}
            </div>
		{else}
			<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == false) || ($active_tab == 'settings'))} active{/if}" id="settings_form">
                {include file='./settings.tpl'}
            </div>
			<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == true) && ($active_tab == 'button'))} active{/if}" id="button_form">
                {include file='./payment-button.tpl'}
            </div>
            <div role="tabpanel" class="tab-pane{if ((isset($active_tab) == true) && ($active_tab == 'faq'))} active{/if}" id="faq">
                {include file='./faq.tpl'}
            </div>
            <div role="tabpanel" class="tab-pane{if ((isset($active_tab) == true) && ($active_tab == 'logs'))} active{/if}" id="logs">
                {include file='./logs.tpl'}
            </div>
		{/if}
	</div>
</div>
