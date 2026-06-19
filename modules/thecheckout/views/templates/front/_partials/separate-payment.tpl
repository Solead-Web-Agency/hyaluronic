{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Prestasmart)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}
<style>
  /* BEGIN Custom CSS styles from config page */
  {$z_tc_config->custom_css nofilter}
  /* END Custom CSS styles from config page */
</style>
<script>
    /* BEGIN Custom JS code from config page */
    {$z_tc_config->custom_js nofilter}
    /* END Custom JS code from config page */

    var amazon_ongoing_session = ("{$amazon_ongoing_session|escape:'javascript':'UTF-8'}" == "1");
</script>

{function step}
    {if $z_tc_config->step_label_{$stepId|escape:'javascript':'UTF-8'}|trim}
        {*          {assign number_of_steps $stepId scope='root'}*}
      <div class="checkout-step-btn" data-step-id="{$stepId|escape:'javascript':'UTF-8'}">{$z_tc_config->step_label_{$stepId|escape:'javascript':'UTF-8'}|escape:'javascript':'UTF-8'}</div>
        {assign var=blocksArr value=","|explode:$z_tc_config->step_blocks_{$stepId|escape:'javascript':'UTF-8'}}
        {*          Blocks: {$z_tc_config->step_blocks_{$stepId}}*}
        {*          Blocks array: {$blocksArr|print_r}*}
      <style>
        {assign var=blocksSelector value=''}
        {foreach ","|explode:$z_tc_config->step_blocks_{$stepId} as $blockName}
        {assign var=blocksSelector value="{$blocksSelector|escape:'javascript':'UTF-8'}:not(#thecheckout-{$blockName|trim|escape:'javascript':'UTF-8'})"}
        {/foreach}
        .checkout-step-{$stepId|escape:'javascript':'UTF-8'} .checkout-block{$blocksSelector|escape:'javascript':'UTF-8'} {literal} {
        {/literal}
            visibility: hidden;
            position: absolute;
            z-index: -1;
        {literal}
        }

        {/literal}
      </style>
    {/if}
{/function}
{function steps}
    {if $z_tc_config->checkout_steps}
      <script type="text/javascript">
          var tc_steps = [];
      </script>
      <div id="checkout-step-btn-container">
          {step stepId=1}
          {step stepId=2}
          {step stepId=3}
          {step stepId=4}
      </div>
    {/if}
{/function}
{function steps_prev_next}
    {if $z_tc_config->checkout_steps}
      <div class="prev-next-container">
        <button class="btn step-back back" type="button">
            {l s='Back' mod='thecheckout'}
        </button>
        <button class="btn step-back continue-shopping" type="button">
            {l s='Continue shopping' mod='thecheckout'}
        </button>
        <button class="btn btn-primary step-continue" type="button" class="form-control-submit">
            {l s='Next step' mod='thecheckout'}
        </button>
      </div>
    {/if}
{/function}

<template id="separate-payment-step-hidden-container">
  {* Inner container will be taken out by JS in separate-payment.js *}
  <section class="checkout-step" id="separate-payment-order-review">
      {steps}
    <div class="review-container">
      <div class="customer-block-container">
        <div id="customer-block">
          {$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'} - {$customer.email|escape:'htmlall':'UTF-8'}
        </div>
      </div>

      <div class="address-block-container">
        <div class="address-block" id="invoice_address">
          <span class="address-block-header">{l s='Your Invoice Address' d='Shop.Theme.Checkout'}</span>
          {$formatted_addresses.invoice nofilter}
        </div>
      </div>
      <div class="address-block-container">
        <div class="address-block" id="delivery_address">
          <span class="address-block-header">{l s='Your Delivery Address' d='Shop.Theme.Checkout'}</span>
          {$formatted_addresses.delivery nofilter}
        </div>
      </div>

      <div class="shipping-method-container">
        <div id="shipping-method">
          <span class="shipping-method-header">{l s='Shipping Method' d='Shop.Theme.Checkout'}</span>
          {if $shipping_logo}
            <img src="{$shipping_logo|escape:'javascript':'UTF-8'}" />
          {/if}
          {$shipping_method->name|escape:'htmlall':'UTF-8'} - {$shipping_method->delay[$language.id]|escape:'htmlall':'UTF-8'}
        </div>
        <div id="hook-display-after-carrier">{$hookDisplayAfterCarrier nofilter}</div>
        <div id="extra_carrier"></div>
        {if $delivery_message}
          <div id="delivery-message">
            <span class="delivery-message-header">{l s='Message' d='Shop.Forms.Labels'}</span>
            {$delivery_message|escape:'htmlall':'UTF-8'}
          </div>
        {/if}
      </div>

      <div id="edit-button-block">
        <a id="x-checkout-edit" href="javascript:void(0);" data-href="{$urls.pages.order|escape:'javascript':'UTF-8'}" class="">{l s='Edit' d='Shop.Theme.Actions'}</a>
      </div>
      <div class="layout-right html_box_3" style="display: none;">
          {$z_tc_config->html_box_3 nofilter}
      </div>
      <div class="layout-right html_box_4" style="display: none;">
          {$z_tc_config->html_box_4 nofilter}
      </div>
    </div>
  </section>

</template>
