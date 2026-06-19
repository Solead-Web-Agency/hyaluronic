{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Prestasmart)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="block-header payment-method-header">{l s='Payment method' d='Shop.Theme.Checkout'}</div>
<div class="inner-wrapper">
  <div class="error-msg">{l s='Please select a payment method' mod='thecheckout'}</div>
  {if isset($shipping_payment_blocks_wait_for_selection) && $shipping_payment_blocks_wait_for_selection}
    <div class="dummy-block-container disallowed">
      <span>{l s='Please choose delivery country to see payment options' mod='thecheckout'}</span></div>
  {elseif isset($payment_block_wait_for_address) && $payment_block_wait_for_address|count}
    <div class="dummy-block-container disallowed">
        <span>{l s='First, please enter your: ' mod='thecheckout'}
          <ul>
            {foreach $payment_block_wait_for_address as $field_name}
              <li>{$field_name|escape:'htmlall':'UTF-8'}</li>
            {/foreach}
          </ul>
        </span>
    </div>
  {elseif isset($force_email_wait_for_enter) && $force_email_wait_for_enter}
    <div class="dummy-block-container disallowed">
      <span>{l s='Please enter your email to see payment options' mod='thecheckout'}</span></div>
  {elseif isset($wait_for_account) && $wait_for_account}
    <div class="dummy-block-container disallowed">
      <span>{l s='Please Save your Personal Info to see payment options' mod='thecheckout'}</span></div>
  {else}
    {block name='payment_options'}
      {hook h='displayPaymentTop'}

      {if $is_free}
        <p>{l s='No payment needed for this order' d='Shop.Theme.Checkout'}</p>
      {/if}
      {if isset($payment_data)}
        <div id="payment_data">
          {foreach from=$payment_data key="data_key" item="data_item"}
            <input type="hidden" id="payment_data_{$data_key|escape:'javascript':'UTF-8'}" value="{$data_item|escape:'javascript':'UTF-8'}">
          {/foreach}
        </div>
      {/if}
      <div class="payment-options {if $is_free}hidden-xs-up{/if}">
        {foreach from=$payment_options key="module_name" item="module_options"}
          {foreach from=$module_options item="option" name="multioptions"}
            <div
                    id="{$option.id|escape:'javascript':'UTF-8'}-main-title"
                    class="tc-main-title"
                    data-payment-module="{$module_name|escape:'javascript':'UTF-8'}"
            >
              <div id="{$option.id|escape:'javascript':'UTF-8'}-container" class="payment-option clearfix">
                <span class="custom-radio float-xs-left">
                  {assign var="actual_option_name" value=$option.module_name|default:$module_name}
                    {assign var="sanitized_module_name" value=$actual_option_name|regex_replace:"/[^a-zA-Z-_0-9]/":""}
                    {assign var="data_module_name" value="{$sanitized_module_name}{$option.call_to_action_text_md5}"}

                  <input
                          class="ps-shown-by-js {if $option.binary}binary{/if}"
                          id="{$option.id|escape:'javascript':'UTF-8'}"
                          data-module-name="{$data_module_name}"
                          name="payment-option"
                          type="radio"
                          required
                          {if $selected_payment_option == $data_module_name
                            || $is_free
                            || ($payment_options|@count === 1 && $module_options|@count === 1)}checked{/if}
                  >
                  <span></span>
                </span>

                <label for="{$option.id|escape:'javascript':'UTF-8'}">
                  <div class="payment-logo">
                      {if isset($option.logo) && $option.logo}
                        <img alt="" src="{$option.logo|escape:'javascript':'UTF-8'}">
                      {/if}
                  </div>
                  <div class="payment-call-to-action-and-logo">
                    <span class="h6">{$option.call_to_action_text|escape:'javascript':'UTF-8'}</span>
                  </div>
                </label>
              </div>
              {if isset($option.additionalInformation) && $option.additionalInformation}
                <div
                        id="{$option.id|escape:'javascript':'UTF-8'}-additional-information"
                        class="js-additional-information definition-list additional-information {$module_name|escape:'javascript':'UTF-8'}{if $option.id != $selected_payment_option} ps-hidden{/if}"
                >
                  {$option.additionalInformation nofilter}
                </div>
              {/if}
              <div
                      id="pay-with-{$option.id|escape:'javascript':'UTF-8'}-form"
                      class="js-payment-option-form {if $option.id != $selected_payment_option} ps-hidden {/if}"
              >
                {if $option.form}
                  {$option.form nofilter}
                {else}
                  <form class="payment-form" method="POST" action="{$option.action nofilter}">
                    {foreach from=$option.inputs item=input}
                      <input type="{$input.type|escape:'javascript':'UTF-8'}" name="{$input.name|escape:'javascript':'UTF-8'}" value="{$input.value|escape:'htmlall':'UTF-8'}">
                    {/foreach}
                    <button style="display:none" id="pay-with-{$option.id|escape:'javascript':'UTF-8'}" type="submit"></button>
                  </form>
                {/if}
              </div>
            </div>
          {/foreach}
          {foreachelse}
          <p
                  class="alert alert-danger">{l s='Unfortunately, there are no payment method available.' d='Shop.Theme.Checkout'}</p>
        {/foreach}
      </div>
      {if $payment_options|count > 1}
        <div id="expand_other_payment_options">
          <div class="btn btn-secondary"><span>{l s='Other payment options' mod='thecheckout'}</span></div>
        </div>
      {/if}
      {hook h='displayPaymentByBinaries'}
    {/block}
  {/if}
</div>
