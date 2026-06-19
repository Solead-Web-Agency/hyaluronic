{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Prestasmart)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="block-header">
    {l s='Order review' mod='thecheckout'}
</div>
<div class="inner-wrapper">
  <div class="review-container">
    <div class="customer-section border{if !$customer.is_guest && $customer.is_logged} static-info{/if}">
      <div class="inner">
        <div class="header">{l s='Personal Information' d='Shop.Theme.Checkout'}</div>
        <div style="display: none;">Static info:
            {if !$customer.is_guest && $customer.is_logged}
                {$customer.firstname} {$customer.lastname} - {$customer.email}
            {/if}
        </div>
        <div class="fields"></div>
      </div>
    </div>
    <div class="addresses-section">
      <div class="invoice-address border">
        <div class="inner">
          <div class="header">{l s='Your Invoice Address' d='Shop.Theme.Checkout'}</div>
          <div class="fields"></div>
        </div>
      </div>
      <div class="delivery-address border">
        <div class="inner">
          <div class="header">{l s='Your Delivery Address' d='Shop.Theme.Checkout'}</div>
          <div class="fields"></div>
        </div>
      </div>
    </div>
    <div class="shipping-section border">
      <div class="inner">
        <div class="header">{l s='Shipping' d='Shop.Theme.Checkout'}</div>
        <div class="fields"></div>
      </div>
    </div>
    <div class="payment-section border">
      <div class="inner">
        <div class="header">{l s='Payment' d='Shop.Theme.Checkout'}</div>
        <div class="fields"></div>
      </div>
    </div>
    <div class="edit-button-container">
      <a href="#" onclick="$('.prev-next-container .step-back.back').click(); return false;" class="">{l s='Edit' d='Shop.Theme.Actions'}</a>
    </div>
  </div>
</div>