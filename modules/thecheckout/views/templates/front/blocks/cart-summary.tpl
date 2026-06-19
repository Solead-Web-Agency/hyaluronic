{**
* NOTICE OF LICENSE
*
* This source file is subject to the Software License Agreement
* that is bundled with this package in the file LICENSE.txt.
*
*  @author    Peter Sliacky (Prestasmart)
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

{if isset($js_custom_vars) && $js_custom_vars|@count}
  <script type="text/javascript">
    {foreach from=$js_custom_vars key=var_name item=var_value}
    if ('object' === typeof {$var_name|escape:'javascript':'UTF-8'}{literal}) {
      {/literal}
        jQuery.extend({$var_name|escape:'javascript':'UTF-8'}, {$var_value|json_encode nofilter});
      {literal}
    } {/literal} else if ('undefined' !== typeof {$var_name|escape:'javascript':'UTF-8'}{literal}) {
      {/literal}
      {$var_name} = {$var_value|json_encode nofilter};
      {literal}
    } else {
      {/literal}
        var {$var_name} = {$var_value|json_encode nofilter};
      {literal}
    }
    {/literal}
    {/foreach}
  </script>
{/if}

<section id="main">
  <div class="block-header shopping-cart-header">{l s='Shopping Cart' d='Shop.Theme.Checkout'}</div>
  <div class="cart-inner-wrapper">
    <div class="cart-summary-top js-cart-summary-top">
        {hook h='displayCheckoutSummaryTop'}
    </div>
    {if $cartQuantityError}
      <div class="error-msg visible">{$cartQuantityError|escape:'htmlall':'UTF-8'}</div>
    {/if}
    {if $otherErrors}
      {foreach $otherErrors as $moduleName => $errorMsg}
        <div class="error-msg visible"><span class="other-error-module-name" style="display: none; padding-right: 5px;">{$moduleName|escape:'htmlall':'UTF-8'}:</span>{$errorMsg|escape:'htmlall':'UTF-8'}</div>
      {/foreach}
    {/if}

    <div class="cart-grid row">

      <div class="card cart-container">
        {block name='cart_overview'}
          {include file='module:thecheckout/views/templates/front/_partials/cart-detailed.tpl' cart=$cart}
        {/block}
      </div>

      {block name='cart_summary'}
        <div class="card cart-summary">

          {block name='cart_totals'}
            {include file='module:thecheckout/views/templates/front/_partials/cart-detailed-totals.tpl' cart=$cart}
          {/block}

          {block name='hook_shopping_cart'}
            {hook h='displayShoppingCart'}
          {/block}

        </div>
      {/block}

      {block name='hook_shopping_cart_footer'}
        {hook h='displayShoppingCartFooter'}
      {/block}

      {* Reassurance is now as separate block - HTML Box no.1
      {block name='hook_reassurance'}
        {hook h='displayReassurance'}
      {/block}
      *}

    </div>
  </div>
</section>
