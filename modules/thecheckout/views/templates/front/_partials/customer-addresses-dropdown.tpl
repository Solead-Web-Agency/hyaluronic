{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Prestasmart)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

{if ("invoice" == $addressType) && isset($addressesList.invoice)}
  {assign var='addressesCombobox' value=$addressesList.invoice}
{elseif ("delivery" == $addressType) && isset($addressesList.delivery)}
  {assign var='addressesCombobox' value=$addressesList.delivery}
{/if}
{*otherwise, addressCombobox won't be set and we won't continue*}
{if isset($addressesCombobox) && $addressesCombobox|@count > 0}
  {assign var='hideAddressesSelection' value=($addressesCombobox|@count == 1 &&
  (("invoice" == $addressType && array_key_exists($idAddressInvoice, $addressesCombobox))
  || ("delivery" == $addressType && array_key_exists($idAddressDelivery, $addressesCombobox))))}
  <div class="customer-addresses{if $addressesCombobox|@count == 1} hidden-1{/if}">
    {if $hideAddressesSelection}
      <a class="custom-link" data-link-action="x-add-new-address">{l s='Add a new address' d='Shop.Theme.Actions'}</a>
    {/if}
    <div class="addresses-selection{if $hideAddressesSelection} hidden{/if}">
      <span class="saved-addresses-label">{l s='Saved addresses:' mod='thecheckout'}</span>
      <select class="not-extra-field" data-link-action="x-{$addressType|escape:'javascript':'UTF-8'}-addresses">
{*        <option value="-1">{l s='New' d='Shop.Theme.Catalog'}{l s='...' mod='thecheckout'}</option>*}
        {assign var='invoiceExists' value=false}
        {assign var='deliveryExists' value=false}
        {foreach $addressesCombobox as $address}
          {if $address.id == $idAddressInvoice}
              {assign var='invoiceExists' value=true}
          {/if}
          {if $address.id == $idAddressDelivery}
              {assign var='deliveryExists' value=true}
          {/if}
          <option value="{$address.id|escape:'javascript':'UTF-8'}"
            {if "invoice" == $addressType}
              {if $address.id == $idAddressInvoice && ($isInvoiceAddressPrimary || $idAddressInvoice != $idAddressDelivery )} selected{/if}
              {if $address.id == $lastOrderInvoiceAddressId && $address.id != $idAddressDelivery && (!$isInvoiceAddressPrimary && $idAddressInvoice == $idAddressDelivery )} selected{/if}
{*              {if $address.id == $idAddressDelivery && $idAddressInvoice != $idAddressDelivery} disabled{/if}*}
              {if $address.id == $idAddressDelivery} disabled{/if}
            {else}
              {if $address.id == $idAddressDelivery && (!$isInvoiceAddressPrimary || $idAddressInvoice != $idAddressDelivery )} selected{/if}
              {if $address.id == $lastOrderDeliveryAddressId && $address.id != $idAddressInvoice && ($isInvoiceAddressPrimary && $idAddressInvoice == $idAddressDelivery )} selected{/if}
{*              {if $address.id == $idAddressInvoice && $idAddressInvoice != $idAddressDelivery} disabled{/if}*}
              {if $address.id == $idAddressInvoice} disabled{/if}
            {/if}
          >{$address.alias|escape:'htmlall':'UTF-8'}{* /id-this:{$address.id}-idInv:{$idAddressInvoice}-idDlv:{$idAddressDelivery}*}</option>
        {/foreach}
        <option value="-1"
                {if "invoice" == $addressType && !$invoiceExists} selected{/if}
                {if "delivery" == $addressType && !$deliveryExists} selected{/if}
        >{l s='Add a new address' d='Shop.Theme.Actions'}</option>
      </select>
    </div>
  </div>
{/if}
