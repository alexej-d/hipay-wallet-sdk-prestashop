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
    <form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall'}" id="payment_button_form" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <!-- PAYMENT WORKFLOW INTEGRATION START -->
                <h3 class="space-button2">{l s='Production configuration' mod='hipay'}</h3>
                <h4 class="space-button2">{l s='Choose the payment form integration below:' mod='hipay'}</h4>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="radio t ">
                            <label>
                                <input type="radio" name="payment_form_type" id="payment_form_type_on" value="1" {if isset($config_hipay.payment_form_type) && $config_hipay.payment_form_type} checked="checked"{/if}>
                                {l s='Redirection (HiPay\'s hosted payment page)' mod='hipay'}
                            </label>
                        </div>
                        <p class="help-block">
                            {l s='With this option, when the user clicks on the "pay" button, he is redirected to the HiPay payment page. Once the transaction is completed, the user is redirected back to your store.' mod='hipay'}
                        </p>
                        <div class="radio t ">
                            <label><input type="radio" name="payment_form_type" id="payment_form_type_off" value="0" {if isset($config_hipay.payment_form_type) && !$config_hipay.payment_form_type} checked="checked"{/if}>{l s='iFrame (transparent payment form display)' mod='hipay'}</label>
                        </div>
                        <p class="help-block">
                            {l s='With this option, when the user clicks on the "pay" button, the payment form is displayed in an iFrame. That means that the user doesn\'t leave your store.' mod='hipay'}
                        </p>
                    </div>
                </div>
                <!-- PAYMENT WORKFLOW INTEGRATION END -->
            </div>
            <div class="col-md-12 col-xs-12">
                <!-- MANUAL CAPTURE START -->
                <h3 class="space-button2">{l s='Manual capture' mod='hipay'}</h3>
                <h4 class="space-button2">{l s='Choose whether you want to enable manual capture:' mod='hipay'}</h4>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="radio t ">
                            <label>
                                <input type="radio" name="manual_capture" id="manual_capture_off" value="0" {if isset($config_hipay.manual_capture) && !$config_hipay.manual_capture} checked="checked"{/if}>
                                {l s='Automatic capture' mod='hipay'}
                            </label>
                        </div>
                        <p class="help-block">
                            {l s='With this mode, your customer will immediately be charged / debited once the purchase is made.' mod='hipay'}
                        </p>
                        <div class="radio t ">
                            <label>
                                <input type="radio" name="manual_capture" id="manual_capture_on" value="1" {if isset($config_hipay.manual_capture) && $config_hipay.manual_capture} checked="checked"{/if}>
                                {l s='Manual capture' mod='hipay'}
                            </label>
                        </div>
                        <p class="help-block">
                            {l s='With this mode, your customer will not be debited immediately. Instead, an authorization will be made, allowing you to confirm the debit later. For example, this mode is appropriate when you want the charge your customer only when the items have been shipped.' mod='hipay'}
                        </p>
                    </div>
                </div>
                <!-- MANUAL CAPTURE END -->
            </div>
            <div class="col-md-12 col-xs-12">
                <!-- PAYMENT BUTTON START -->
                <h3 class="space-button2">{l s='Payment button' mod='hipay'}</h3>
                <h4 class="space-button2">{l s='The payment button will be displayed on the checkout page:' mod='hipay'}</h4>
                <div class="row">
                    <label class="control-label col-lg-2">
                        <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Button text (French)' mod='hipay'}">
                            {l s='Button text (French)' mod='hipay'}
                        </span>
                    </label>
                    <div class="col-lg-10">
                        <input type="text" name="button_text_fr" id="button_text_fr" value="{if isset($config_hipay.button_text_fr)}{$config_hipay.button_text_fr}{/if}" class="lg">
                    </div>
                </div>
                <div class="row">
                    <label class="control-label col-lg-2">
                        <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Button text (English)' mod='hipay'}">
                            {l s='Button text (English)' mod='hipay'}
                        </span>
                    </label>
                    <div class="col-lg-10">
                        <input type="text" name="button_text_en" id="button_text_en" value="{if isset($config_hipay.button_text_en)}{$config_hipay.button_text_en}{/if}" class="lg">
                        <p class="help-block">
                            {l s='This text will be displayed next to the button.' mod='hipay'}
                        </p>
                    </div>
                </div>
                <h4 class="space-button">{l s='Choose your button image or upload your own image:' mod='hipay'}</h4>
                <div class="row">
                    <ul id="list-image-buttons" class="list-inline">
                        {if isset($button_images) }
                            {foreach from=$button_images key=index item=image}
                                <li>
                                    <input type="radio" name="button_images" id="payment_button_{$index}" value="{$image}" {if isset($config_hipay.button_images) && $config_hipay.button_images == {$image}} checked="checked"{/if} />
                                    <label style="width: auto" for="payment_button_{$index}"><img src="{$url_images}{$image}" /></label>
                                </li>
                            {/foreach}
                        {/if}
                    </ul>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-4">
                        <label for="specific_button">{l s='Upload your payment button:' mod='hipay'}</label>
                        <input type="file" id="specific_button" name="specific_button">
                    </div>
                    <div class="col-lg-7">
                        <button type="button" class="btn btn-primary" id="add-image">{l s='Add a new payment button' mod='hipay'}</button>
                    </div>
                </div>
                <div class="row">
                    <p class="help-block">
                        {l s='Dimension max 400x400 pixels and 300ko max.' mod='hipay'}
                    </p>
                    <div id="image-error" class="img-error"></div>
                    <div id="image-success" class="img-success"></div>
                </div>
                <!-- PAYMENT BUTTON END -->
                <hr />
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <button type="submit" class="btn btn-default btn btn-default pull-right" name="submitPaymentbutton"><i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay'}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $( document ).ready(function() {
        $('#image-error').hide();
        $('#image-success').hide();

        $("#add-image").on('click', function() {
            var file_data = $('#specific_button').prop('files')[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            form_data.append('controller', 'AdminHiPayConfig');
            form_data.append('action', 'ImageButtons');
            form_data.append('ajax', true);

            $.ajax({
                url: '{$ajax_url}', // point to server-side PHP script
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(jsonData)
                {
                    if(jsonData.status == true){
                        if(jsonData.image != ''){
                            var html = '<li>';
                            html += '<input type="radio" name="button_images" id="payment_button_'+jsonData.image+'" value="'+jsonData.image+'" />';
                            html += '<label style="width: auto" for="payment_button_'+jsonData.image+'"><img src="{$url_images}'+jsonData.image+'" /></label>';
                            $('#list-image-buttons').append(html);
                            $('#image-success').html("{l s='Your payment button is available.' mod='hipay'}");
                            $('#image-success').show();
                            $('#image-error').hide();
                        }
                    }else{
                        $('#image-error').html(jsonData.message);
                        $('#image-error').show();
                        $('#image-success').hide();
                    }
                }
            });
        });
    });
</script>