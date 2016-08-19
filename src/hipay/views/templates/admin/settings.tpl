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
    <form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall'}" id="settings_form">
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div class="row">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip"
                            data-toggle="tooltip"
                            data-html="true"
                            title=""
                            data-original-title="{l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay'}">
                            {l s='Use test mode' mod='hipay'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="settings_switchmode" id="settings_switchmode_on" value="1">
                            <label for="settings_switchmode_on">{l s='Yes'}</label>
                            <input type="radio" name="settings_switchmode" id="settings_switchmode_off" value="0" checked="checked">
                            <label for="settings_switchmode_off">{l s='No'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h4>{l s='Production configuration' mod='hipay'}</h4>
                        ...
                    </div>
                    <div class="col-md-6">
                        <h4>{l s='Test configuration' mod='hipay'}</h4>
                        ...
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        bouton save
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>