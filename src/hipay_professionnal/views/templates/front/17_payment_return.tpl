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
{extends "$layout"}

{block name="content"}
  <section>
    <p>{l s='You have successfully submitted your payment form.' mod='hipay_professionnal'}</p>
    <p>{l s='Here are the params:' mod='hipay_professionnal'}</p>
    <ul>
      {foreach from=$params key=name item=value}
        <li>{$name|escape:'htmlall':'UTF-8'}: {$value|escape:'htmlall':'UTF-8'}</li>
      {/foreach}
    </ul>
    <p>{l s='Now, you just need to proceed the payment and do what you need to do.' mod='hipay_professionnal'}</p>
  </section>
{/block}
