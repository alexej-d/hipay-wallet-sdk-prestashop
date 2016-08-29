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
                <!-- SWITCH MODE START -->
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
                            <input type="radio" name="settings_switchmode" id="settings_switchmode_on" value="1" {if $config_hipay.sandbox_mode }checked="checked"{/if}>
                            <label for="settings_switchmode_on">{l s='Yes'}</label>
                            <input type="radio" name="settings_switchmode" id="settings_switchmode_off" value="0" {if $config_hipay.sandbox_mode == false}checked="checked"{/if}>
                            <label for="settings_switchmode_off">{l s='No'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <p>
                            {l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay'}
                        </p>
                    </div>
                </div>
                <!-- SWITCH MODE END -->
                <div class="row">
                    <!-- PRODUCTION FORM START -->
                    <div class="col-md-6 trait">
                        <h4>{l s='Production configuration' mod='hipay'}</h4>
                        <div class="row">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip"
                                      data-toggle="tooltip"
                                      data-html="true"
                                      title=""
                                      data-original-title="{l s='Content rating' mod='hipay'}">
                                {l s='Content rating' mod='hipay'}
                            </label>
                            <div class="col-lg-9">
                                <select id="settings_production_rating" name="settings_production_rating">
                                    <option value="">{l s='--- Select the content rating ---' mod='hipay'}</option>
                                    {foreach from=$rating item=select}
                                        <option value="{$select.key}" {if $config_hipay.selected.rating_prod == $select.key}selected{/if}>{$select.name|escape:html:'UTF-8'}</option>
                                        {foreachelse}
                                        <option value="">{l s="No Content rating" mod='hipay'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <h4>{l s='Accounts and currencies' mod='hipay'}</h4>
                        <!-- TABLE SELECTION PROD START -->
                        <table class="table" id="accounts-currencies">
                            <thead>
                            <tr>
                                <th>{l s='Currency' mod='hipay'}</th>
                                <th>{l s='Account ID' mod='hipay'}</th>
                                <th>{l s='Website' mod='hipay'}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$selectedCurrencies key=currency item=options}
                                <tr>
                                    {if $currency == "0" }
                                        <td colspan="3">{l s='no data' mod='hipay'}</td>
                                    {else}
                                        <td>{$currency}</td>
                                        {if !isset($config_hipay.production.$currency) || $config_hipay.production.$currency|@count == 0}
                                            <td colspan="2">
                                            <span class="icon icon-warning-sign" aria-hidden="true">
                                                <a href="#">{l s='Currency not activated. Click here to fix.' mod='hipay'}</a>
                                            </span>
                                            </td>
                                        {else}
                                            <td>
                                                <select id="settings_production_{$currency}_user_account_id" name="settings_production_{$currency}_user_account_id">
                                                    <option value="">{l s='--- Account ID ---' mod='hipay'}</option>
                                                    {foreach from=$config_hipay.production.$currency key=aid item=select}
                                                        <option value="{$aid}" {if $config_hipay.selected.currencies.production.$currency.accountID == {$aid}}selected{/if}>{$aid}</option>
                                                    {foreachelse}
                                                        <option value="">{l s="No Account ID" mod='hipay'}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td>
                                                <select id="settings_production_{$currency}_website_id" name="settings_production_{$currency}_website_id">
                                                    <option value="">{l s='--- Website ID ---' mod='hipay'}</option>
                                                    {assign var="prod_account_id" value=$config_hipay.selected.currencies.production.$currency.accountID}
                                                    {if isset($prod_account_id)}
                                                        {foreach from=$config_hipay.production.$currency.$prod_account_id item=select}
                                                            <option value="{$select.website_id}" {if $config_hipay.selected.currencies.production.$currency.websiteID == {$select.website_id}}selected{/if}>{$select.website_id}</option>
                                                            {foreachelse}
                                                            <option value="">{l s="No Website ID" mod='hipay'}</option>
                                                        {/foreach}
                                                    {/if}
                                                </select>
                                            </td>
                                        {/if}
                                    {/if}
                                </tr>
                                {foreachelse}
                                <tr>
                                    <td colspan="3">{l s='no data' mod='hipay'}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                        <!-- TABLE SELECTION PROD END -->
                    </div>
                    <!-- PRODUCTION FORM END -->
                    <!-- SANDBOX FORM START -->
                    <div class="col-md-6">
                        <h4>{l s='Test configuration' mod='hipay'}</h4>
                        {if !empty($config_hipay.sandbox_ws_login)}
                        <div class="row">
                            <label class="control-label col-lg-3">
                                    <span class="label-tooltip"
                                          data-toggle="tooltip"
                                          data-html="true"
                                          title=""
                                          data-original-title="{l s='Content rating' mod='hipay'}">
                                    {l s='Content rating' mod='hipay'}
                            </label>
                            <div class="col-lg-9">
                                <select id="settings_sandbox_rating" name="settings_sandbox_rating">
                                    <option value="">{l s='--- Select the content rating ---' mod='hipay'}</option>
                                    {foreach from=$rating item=select}
                                        <option value="{$select.key}" {if $config_hipay.selected.rating_sandbox == $select.key}selected{/if}>{$select.name|escape:html:'UTF-8'}</option>
                                        {foreachelse}
                                        <option value="">{l s="No Content rating" mod='hipay'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        {else}
                            {l s='Your test account is not connected yet. Enter your test account\'s web service login and password in order to use a test' mod='hipay'}
                        {/if}
                        {if !empty($config_hipay.sandbox_ws_login)}
                            <h4>{l s='Accounts and currencies' mod='hipay'}</h4>
                        {/if}
                        {if empty($config_hipay.sandbox_ws_login)}
                            <div class="row">
                                <button type="button" class="btn btn-primary center-block btn-lg space" data-toggle="modal" data-target="#sandbox-connexion">
                                    {l s='Connect test account' mod='hipay'}
                                </button>
                            </div>
                            <div class="row">
                                <p>
                                    {l s='If you don\'t have a test account yet, you can create one on the' mod='hipay'}&nbsp;
                                    <a href="https://test-www.hipaydirect.com/" target="_blank">
                                        {l s='HiPay Direct test website' mod='hipay'}
                                    </a>
                                </p>
                            </div>
                        {else}
                            <!-- TABLE SELECTION TEST START -->
                            <table class="table"  id="accounts-currencies2">
                                <thead>
                                <tr>
                                    <th>{l s='Currency' mod='hipay'}</th>
                                    <th>{l s='Account ID' mod='hipay'}</th>
                                    <th>{l s='Website' mod='hipay'}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach from=$selectedCurrencies key=currency item=options}
                                    <tr>
                                        {if $currency == "0" }
                                            <td colspan="3">{l s='no data' mod='hipay'}</td>
                                        {else}
                                            <td>{$currency}</td>
                                            {if !isset($config_hipay.sandbox.$currency) || $config_hipay.sandbox.$currency|@count == 0}
                                                <td colspan="2">
                                                <span class="icon icon-warning-sign" aria-hidden="true">
                                                    <a href="#">{l s='Currency not activated. Click here to fix.' mod='hipay'}</a>
                                                </span>
                                                </td>
                                            {else}
                                                <td>
                                                    <select id="settings_sandbox_{$currency}_user_account_id" name="settings_sandbox_{$currency}_user_account_id">
                                                        <option value="">{l s='--- Account ID ---' mod='hipay'}</option>
                                                        {foreach from=$config_hipay.sandbox.$currency key=aid item=select}
                                                            <option value="{$aid}" {if $config_hipay.selected.currencies.sandbox.$currency.accountID == {$aid}}selected{/if}>{$aid}</option>
                                                            {foreachelse}
                                                            <option value="">{l s="No Account ID" mod='hipay'}</option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="settings_sandbox_{$currency}_website_id" name="settings_sandbox_{$currency}_website_id">
                                                        <option value="">{l s='--- Website ID ---' mod='hipay'}</option>
                                                        {assign var="test_account_id" value=$config_hipay.selected.currencies.sandbox.$currency.accountID}
                                                        {if isset($test_account_id)}
                                                            {foreach from=$config_hipay.sandbox.$currency.$test_account_id item=select}
                                                                <option value="{$select.website_id}" {if $config_hipay.selected.currencies.sandbox.$currency.websiteID == {$select.website_id}}selected{/if}>{$select.website_id}</option>
                                                                {foreachelse}
                                                                <option value="">{l s="No Website ID" mod='hipay'}</option>
                                                            {/foreach}
                                                        {/if}
                                                    </select>
                                                </td>
                                            {/if}
                                        {/if}
                                    </tr>
                                    {foreachelse}
                                    <tr>
                                        <td colspan="3">{l s='no data' mod='hipay'}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                            <!-- TABLE SELECTION TEST END -->
                        {/if}
                    </div>
                </div>
                <!-- SANDBOX FORM END -->
                <hr />
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay'}</button>
                        <button type="submit" class="btn btn-default btn btn-default pull-right" name="submitSettings"><i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay'}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{* include file modal-login.tpl *}
{include file='./modal-login.tpl'}

<script type="text/javascript">
    jQuery( document ).ready(function() {

        {*
         * init for dynamic selectbox the json website_id by user_account_id
         *}

        var json_prod = {if isset($config_hipay.production) && is_array($config_hipay.production)}{$config_hipay.production|@json_encode}{else}""{/if};
        var json_test = {if isset($config_hipay.sandbox) && is_array($config_hipay.sandbox)}{$config_hipay.sandbox|@json_encode}{else}""{/if} ;

        {*
         * generate function .change jquery for each currency and selectbox user_account_id
         *}
        {foreach from=$selectedCurrencies key=currency item=options}
        $("#settings_production_{$currency}_user_account_id").change(function () {

            var idSelect = "settings_production_{$currency}_website_id";
            var idAccount = $(this).val();

            $('#' + idSelect).children('option:not(:first)').remove();
            addOptionsWebsiteId(idSelect, json_prod.{$currency},idAccount);
        });
        $("#settings_sandbox_{$currency}_user_account_id").change(function () {
            var idSelect = "settings_sandbox_{$currency}_website_id";
            var idAccount = $(this).val();

            $('#' + idSelect).children('option:not(:first)').remove();
            addOptionsWebsiteId(idSelect, json_test.{$currency}, idAccount);
        });
        {/foreach}

        {*
         * function load website_id by user_account_id and currency in the selectbox website_id
         *}
         function addOptionsWebsiteId(idSelect, config, idAccount) {
            $.each(config[idAccount], function (key, value) {
                $('#' + idSelect)
                        .append($("<option></option>")
                                .attr("value", value.website_id)
                                .text(value.website_id));
            });
         }
    });
</script>