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
                    <div class="col-md-12 col-xs-12">
                        <p>
                            {l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay'}
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h4>{l s='Production configuration' mod='hipay'}</h4>
                    </div>
                    <div class="col-md-6">
                        <h4>{l s='Test configuration' mod='hipay'}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
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
                                <select id="settings_production_rating">
                                    <option value="">{l s='--- Select the content rating ---' mod='hipay'}</option>
                                    {foreach from=$rating item=select}
                                        <option value="{$select.key}">{$select.name|escape:html:'UTF-8'}</option>
                                    {foreachelse}
                                        <option value="">{l s="No Content rating" mod='hipay'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label col-lg-3">
                                <span class="label-tooltip"
                                      data-toggle="tooltip"
                                      data-html="true"
                                      title=""
                                      data-original-title="{l s='Content rating' mod='hipay'}">
                                {l s='Content rating' mod='hipay'}
                        </label>
                        <div class="col-lg-9">
                            <select id="settings_sandbox_rating">
                                <option value="">{l s='--- Select the content rating ---' mod='hipay'}</option>
                                {foreach from=$rating item=select}
                                    <option value="{$select.key}">{$select.name|escape:html:'UTF-8'}</option>
                                {foreachelse}
                                    <option value="">{l s="No Content rating" mod='hipay'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h4>{l s='Accounts and currencies' mod='hipay'}</h4>
                    </div>
                    <div class="col-md-6">
                        <h4>{l s='Accounts and currencies' mod='hipay'}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{l s='Currency' mod='hipay'}</th>
                                    <th>{l s='Account ID' mod='hipay'}</th>
                                    <th>{l s='Website' mod='hipay'}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach from=$config_hipay->production_user_account_id key=currency item=options}
                                <tr>
                                {if $currency == "0" }
                                    <td colspan="3">{l s='no data' mod='hipay'}</td>
                                {else}
                                    <td>{$currency}</td>
                                    <td>
                                        <select id="settings_production_{$currency}_user_account_id">
                                            <option value="">{l s='--- Account ID ---' mod='hipay'}</option>
                                            {foreach from=$config_hipay->production_user_account_id.$currency item=select}
                                                <option value="{$select}">{$select}</option>
                                            {foreachelse}
                                                <option value="">{l s="No Account ID" mod='hipay'}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td>
                                        <select id="settings_production_{$currency}_website_id">
                                            <option value="">{l s='--- Website ID ---' mod='hipay'}</option>
                                            {foreach from=$config_hipay->production_website_id.$currency item=select}
                                                <option value="{$select}">{$select}</option>
                                                {foreachelse}
                                                <option value="">{l s="No Website ID" mod='hipay'}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                {/if}
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{l s='Currency' mod='hipay'}</th>
                                    <th>{l s='Account ID' mod='hipay'}</th>
                                    <th>{l s='Website' mod='hipay'}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach from=$config_hipay->sandbox_user_account_id key=currency item=options}
                                <tr>
                                {if $currency == "0" }
                                    <td colspan="3">{l s='no data' mod='hipay'}</td>
                                {else}
                                    <td>{$currency}</td>
                                    <td>
                                        <select id="settings_sandbox_{$currency}_user_account_id">
                                            <option value="">{l s='--- Account ID ---' mod='hipay'}</option>
                                            {foreach from=$config_hipay->sandbox_user_account_id.$currency item=select}
                                                <option value="{$select}">{$select}</option>
                                            {foreachelse}
                                                <option value="">{l s="No Account ID" mod='hipay'}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td>
                                        <select id="settings_sandbox_{$currency}_website_id">
                                            <option value="">{l s='--- Website ID ---' mod='hipay'}</option>
                                            {foreach from=$config_hipay->sandbox_website_id.$currency item=select}
                                                <option value="{$select}">{$select}</option>
                                                {foreachelse}
                                                <option value="">{l s="No Website ID" mod='hipay'}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                {/if}
                                </tr>
                            {foreachelse}
                                <tr>
                                    <td colspan="3">{l s='no data' mod='hipay'}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
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