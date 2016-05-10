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
	<div class="row" id="hipay-header">
		<div class="col-xs-12 col-sm-12 col-md-6 text-center">
			<img src="{$module_dir|escape:'html':'UTF-8'}/views/img/logo.png" id="payment-logo" />
		</div>
		<div class="col-xs-12 col-sm-12 col-md-6 text-center">
			<h4>{l s='PrestaShop\'s Official Payment Solution' mod='hipay'}</h4>
		</div>
	</div>

	<hr />

	<div id="hipay-content">
		{if isset($welcome_message)}
			<div class="row">
				<div class="col-md-12 col-xs-12">
					<p>
						<span id="welcome-message">{l s='Welcome to PrestaShop Payments by HiPay!' mod='hipay'}</span>
						<br />
						{l s='Your store can now accept payments in 8 currencies.' mod='hipay'}<br />
						{l s='You should have received by email your credentials to connect to your HiPay account. You also have received some test credentials to run payment tests before going live.' mod='hipay'}<br />
						{l s='If you have any question, please contact us at prestashop@hipay.com.' mod='hipay'}<br />
						<br />
						{l s='Happy selling!' mod='hipay'}
					</p>
				</div>
			</div>
		{else}
			{if isset($balance_warning) && ($balance_warning == true)}
				<div class="modal fade" id="hipay_warning_modal" tabindex="-1" role="dialog" aria-labelledby="hipay_warning_modal_label" aria-hidden="true" data-show="true" onload="$(this).modal('show')">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="hipay_warning_modal_label">{l s='Warning!' mod='hipay'}</h4>
							</div>
							<div class="modal-body">
								<p>
									{l s='Your account balance is getting close to %1$s. Please make sure your account has been identified to be able to transfer money from your HiPay account to your own bank account.' mod='hipay' sprintf=[$amount_limit]}
								</p>
								<br />
								<a href="#">{l s='Click here for more information.' mod='hipay'}</a>
							</div>
						</div>
					</div>
				</div>
			{/if}

			<div class="row">
				<div class="col-md-12 col-xs-12">
					{if ($is_logged == true)}
						<p class="text-center">
							<a class="btn btn-primary" data-toggle="collapse" href="#hipay-marketing-content" aria-expanded="false" aria-controls="hipay-marketing-content">
								{l s='More info' mod='hipay'}
							</a>
						</p>
					{/if}
					<div {if ($is_logged == true)}class="collapse"{/if} id="hipay-marketing-content">
						<div class="row">
							{if ($is_logged == true)}
								<hr />
							{/if}
							<div class="col-md-6">
								<h4>{l s='A complete and easy to use solution' mod='hipay'}</h4>
								<ul class="ul-spaced">
									<li>{l s='Start now, no contract required' mod='hipay'}</li>
									<li>{l s='Accept 8 currencies with 15+ local payment solutions in Europe' mod='hipay'}</li>
									<li>{l s='Anti-fraud system and full-time monitoring of high-risk behavior' mod='hipay'}</li>
								</ul>
							</div>

							<div class="col-md-6">
								<h4>{l s='From 1% + €0.25 per transaction!' mod='hipay'}</h4>
								<ul class="ul-spaced">
									<li>{l s='A rate that adapts to your volume of activity' mod='hipay'}</li>
									<li>{l s='15% less expensive than leading solutions in the market*' mod='hipay'}</li>
									<li>{l s='No registration, installation or monthly fee' mod='hipay'}</li>
								</ul>
								<br />
								<a href="{$localized_rates_pdf_link}" class="_blank">
									{l s='*See the complete list of rates for PrestaShop Payments by HiPay' mod='hipay'}
								</a>
							</div>
						</div>

						<hr />

						<div class="row">
							<div class="col-md-12 col-xs-12">
								<h4>{l s='Accept payments from all over the world in just a few clicks' mod='hipay'}</h4>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12 col-xs-12 text-center">
								<img src="{$module_dir|escape:'html':'UTF-8'}/views/img/cards.png" id="cards-logo" />
							</div>
						</div>

						<hr />

						<div class="row">
							<div class="col-md-12 col-xs-12">
								<h4>{l s='3 simple steps:' mod='hipay'}</h4>
								<ol>
									<li>{l s='Your account is pre-approved when you create your PrestaShop store so that you can start accepting online payments right away.' mod='hipay'}</li>
									<li>{l s='Finalize your PrestaShop Payments by HiPay registration before you reach €2,500 on your account.' mod='hipay'}</li>
									<li>{l s='Easily collect and transfer your money from your PrestaShop Payments by HiPay account to your own bank account.' mod='hipay'}</li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
</div>

{$alerts}

	<div role="tabpanel">
		<ul class="nav nav-tabs" role="tablist">
			{if ($is_logged == false)}
				<li role="presentation" class="active"><a href="#psp_login_form" aria-controls="psp_login_form" role="tab" data-toggle="tab">
					<span class="icon icon-user"></span> {l s='Login' mod='hipay'}</a>
				</li>
				<li class="pull-right"><a href="https://{if $sandbox == true}test-{/if}www.hipaydirect.com/prestashop-payments/" role="tab" target="_blank" id="login_hipay_link">
					<span class="icon icon-arrow-right"></span> {l s='Go to HiPay' mod='hipay'}</a>
				</li>
			{else}
				<li role="presentation"{if ((isset($active_tab) == false) || ($active_tab == 'settings'))} class="active"{/if}><a href="#psp_settings_form" aria-controls="psp_settings_form" role="tab" data-toggle="tab">
					<span class="icon icon-cogs"></span> {l s='Settings' mod='hipay'}</a>
				</li>
				<li role="presentation"{if ((isset($active_tab) == true) && ($active_tab == 'transactions'))} class="active"{/if}><a href="#psp_transactions_form" aria-controls="psp_transactions_form" role="tab" data-toggle="tab">
					<span class="icon icon-money"></span> {l s='Transactions' mod='hipay'}</a>
				</li>
				<li role="presentation"{if ((isset($active_tab) == true) && ($active_tab == 'sandbox'))} class="active"{/if}><a href="#psp_sandbox_form" aria-controls="psp_sandbox_form" role="tab" data-toggle="tab">
					<span class="icon icon-check-square-o"></span> {l s='Test mode' mod='hipay'}</a>
				</li>
				<li role="presentation"><a href="#psp_services_form" aria-controls="psp_services_form" role="tab" data-toggle="tab">
					<span class="icon icon-users"></span> {l s='FAQ' mod='hipay'}</a>
				</li>
				<li class="pull-right"><a href="https://{if $sandbox == true}test-{/if}www.hipaydirect.com/prestashop-payments/" role="tab" target="_blank">
					<span class="icon icon-arrow-right"></span> {l s='Go to HiPay' mod='hipay'}</a>
				</li>
			{/if}
		</ul>

		<div class="tab-content">
			{if ($is_logged == false)}
				<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == false) || ($active_tab == 'login'))} active{/if}" id="psp_login_form">{$login_form}</div>
			{else}
				<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == false) || ($active_tab == 'settings'))} active{/if}" id="psp_settings_form">{$settings_form}</div>
				<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == true) && ($active_tab == 'transactions'))} active{/if}" id="psp_transactions_form">{$transactions_form}</div>
				<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == true) && ($active_tab == 'sandbox'))} active{/if}" id="psp_sandbox_form">{$sandbox_form}</div>
				<div role="tabpanel" class="tab-pane" id="psp_services_form">{$services_form}</div>
			{/if}
		</div>
	</div>

<p class="text-center">
	<a href="https://www.prestashop.com/download/pdf/pspayments/CGU_PrestaShop_Payments.pdf" target="_blank">{l s='Terms of Use' mod='hipay'}</a>
</p>
