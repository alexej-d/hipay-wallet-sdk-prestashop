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
<div class="row" id="hipay-header">
    <div class="col-xs-12 col-sm-12 col-md-6 text-center">
        <img src="{$module_dir|escape:'html':'UTF-8'}/views/img/logo.png" id="payment-logo" />
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6 text-center">
        <h4>{l s='A complete and easy to use solution' mod='hipay'}</h4>
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
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <p class="text-center">
                    <a class="btn btn-primary" data-toggle="collapse" href="#hipay-marketing-content" aria-expanded="false" aria-controls="hipay-marketing-content">
                        {l s='More info' mod='hipay'}
                    </a>
                </p>
                <div class="collapse in" id="hipay-marketing-content">
                    <div class="row">
                        <hr />
                        <div class="col-md-6">
                            <h4>{l s='From 1% + €0.25 per transaction!' mod='hipay'}</h4>
                            <ul class="ul-spaced">
                                <li>{l s='A rate that adapts to your volume of activity' mod='hipay'}</li>
                                <li>{l s='15% less expensive than leading solutions in the market*' mod='hipay'}</li>
                                <li>{l s='No registration, installation or monthly fee' mod='hipay'}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h4>{l s='A complete and easy to use solution' mod='hipay'}</h4>
                            <ul class="ul-spaced">
                                <li>{l s='Start now, no contract required' mod='hipay'}</li>
                                <li>{l s='Accept 8 currencies with 15+ local payment solutions in Europe' mod='hipay'}</li>
                                <li>{l s='Anti-fraud system and full-time monitoring of high-risk behavior' mod='hipay'}</li>
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
                                <li>{l s='Download the HiPay free module' mod='hipay'}</li>
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