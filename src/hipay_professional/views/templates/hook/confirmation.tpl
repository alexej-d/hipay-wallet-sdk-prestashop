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

{if (isset($status) == true) && ($status == 'ok')}
<h3>{l s='Your order on %s is complete.' sprintf=$shop_name mod='hipay_professional'}</h3>
<p>
	<br />- {l s='Amount' mod='hipay_professional'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
	<br />- {l s='Reference' mod='hipay_professional'} : <span class="reference"><strong>{$reference|escape:'htmlall':'UTF-8'}</strong></span>
	<br /><br />{l s='An email has been sent with this information.' mod='hipay_professional'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='hipay_professional'} <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team.' mod='hipay_professional'}</a>
</p>
{else}
<h3>{l s='Your order on %s has not been accepted.' sprintf=$shop_name mod='hipay_professional'}</h3>
<p>
	<br />- {l s='Reference' mod='hipay_professional'} <span class="reference"> <strong>{$reference|escape:'htmlall':'UTF-8'}</strong></span>
	<br /><br />{l s='Please, try to order again.' mod='hipay_professional'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='hipay_professional'} <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team.' mod='hipay_professional'}</a>
</p>
{/if}
<hr />
