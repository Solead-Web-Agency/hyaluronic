{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Zelarg)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="block-header address-name-header">{l s='Shipping address' mod='thecheckout'}</div>
<a style="display: none;" href="javascript:void(0);" class="amazonpay-change-address">{l s='Change address' mod='thecheckout'}</a>
<form class="address-fields" data-address-type="delivery">
  {include file='module:thecheckout/views/templates/front/_partials/customer-addresses-dropdown.tpl' addressType='delivery'}
  {block name="address_delivery_form_fields"}
    <section class="form-fields">
      {block name='form_fields'}
        {foreach from=$formFieldsDelivery item="field"}
          {block name='form_field'}
            {include file='module:thecheckout/views/templates/front/_partials/checkout-form-fields.tpl' checkoutSection='delivery'}
          {/block}
        {/foreach}
      {/block}
    </section>
  {/block}
</form>
{if !$isInvoiceAddressPrimary}
  <div class="second-address">
    <span class="custom-checkbox">
    <input type="checkbox" data-link-action="x-bill-to-different-address"
           id="bill-to-different-address"{if $showBillToDifferentAddress} checked{/if}>
    <span><i class="material-icons rtl-no-flip checkbox-checked check-icon">&#xE5CA;</i></span>
    <label for="bill-to-different-address">{l s='Bill to a different address' mod='thecheckout'}</label>
    </span>
  </div>
{/if}
