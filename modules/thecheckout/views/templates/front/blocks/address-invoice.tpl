{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Prestasmart)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

{if $z_tc_config->show_i_am_business}
    <style>
        {if $hideBusinessFields}
        {literal}
        #thecheckout-address-invoice .form-group.business-field:not(.need-dni) {
            display: none;
        }

        {/literal}
        {else}
        {literal}
        #thecheckout-address-invoice .form-group.business-disabled-field {
            display: none;
        }
        #thecheckout-address-invoice .business-fields-separator {
            display: block;
        }

        {/literal}
        {/if}
    </style>
{/if}
{if $z_tc_config->show_i_am_private}
  <style>
    {if $hidePrivateFields}
    {literal}
    #thecheckout-address-invoice .form-group.private-field:not(.need-dni):not(.business-field) {
        display: none;
    }

    {/literal}
    {else}
    {literal}
    #thecheckout-address-invoice .private-fields-separator {
        display: block;
    }

    {/literal}
    {/if}
  </style>
{/if}
<div class="block-header address-name-header">{l s='Billing address' mod='thecheckout'}</div>
<div class="inner-wrapper" data-address-id="{$address_invoice_id|escape:'javascript':'UTF-8'}" data-address-is-used="{if $z_tc_config->restrict_editing_used_address}{$address_invoice_is_used|escape:'javascript':'UTF-8'}{else}0{/if}">
  {if $z_tc_config->show_i_am_business || $z_tc_config->show_i_am_private}
    <div class="business-private-checkboxes form-group">
      {if $z_tc_config->show_i_am_business}
          <div class="business-customer">
          <span class="custom-checkbox">
            <input id="i_am_business" type="checkbox" data-link-action="x-i-am-business"
                   {if !$hideBusinessFields}checked="checked"{/if} disabled="disabled">
            <span><i class="material-icons rtl-no-flip checkbox-checked check-icon">&#xE5CA;</i></span>
            <label for="i_am_business">{l s='I am a business customer' mod='thecheckout'}</label>
          </span>
          </div>
      {/if}
      {if $z_tc_config->show_i_am_private}
          <div class="private-customer">
          <span class="custom-checkbox">
            <input id="i_am_private" type="checkbox" data-link-action="x-i-am-private"
                   {if !$hidePrivateFields}checked="checked"{/if} disabled="disabled">
            <span><i class="material-icons rtl-no-flip checkbox-checked check-icon">&#xE5CA;</i></span>
            <label for="i_am_private">{l s='I am a private customer' mod='thecheckout'}</label>
          </span>
          </div>
      {/if}
    </div>
  {/if}

  <form class="address-fields" data-address-type="invoice" id="invoice-address">
      {include file='module:thecheckout/views/templates/front/_partials/customer-addresses-dropdown.tpl' addressType='invoice'}
      {block name="address_invoice_form_fields"}
          <section class="form-fields">
              {block name='form_fields'}
                  {if $z_tc_config->show_i_am_business}
                      <div class="business-fields-container"><div class="business-fields-separator"></div></div>
                  {/if}
                  {if $z_tc_config->show_i_am_private}
                    <div class="private-fields-container"><div class="private-fields-separator"></div></div>
                  {/if}
                  {foreach from=$formFieldsInvoice item="field"}
                      {block name='form_field'}
                          {include file='module:thecheckout/views/templates/front/_partials/checkout-form-fields.tpl' checkoutSection='invoice'}
                      {/block}
                  {/foreach}
              {/block}
          </section>
      {/block}
    <div class="address-is-used"><span class="asterisk">*</span>{l s='Address used in past orders' mod='thecheckout'} - <a href="{url entity=address id=$address_invoice_id}">{l s='Edit' d='Shop.Theme.Actions'}</a></div>
  </form>
  {if $isInvoiceAddressPrimary}
      <div class="second-address">
      <span class="custom-checkbox">
      <input type="checkbox" data-link-action="x-ship-to-different-address"
             id="ship-to-different-address"{if $showShipToDifferentAddress} checked{/if}>
      <span><i class="material-icons rtl-no-flip checkbox-checked check-icon">&#xE5CA;</i></span>
      <label for="ship-to-different-address">{l s='Ship to a different address' mod='thecheckout'}</label>
      </span>
      </div>
  {/if}
</div>
