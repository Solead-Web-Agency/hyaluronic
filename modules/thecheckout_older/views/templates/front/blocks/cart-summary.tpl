{**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{if isset($js_custom_vars) && $js_custom_vars|@count}
  <script type="text/javascript">
    {foreach from=$js_custom_vars key=var_name item=var_value}
    if ('object' === typeof {$var_name}{literal}) {
      {/literal}
        jQuery.extend({$var_name}, {$var_value|json_encode nofilter});
      {literal}
    } {/literal} else if ('undefined' !== typeof {$var_name}{literal}) {
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

  {if $cartQuantityError}
    <div class="error-msg visible">{$cartQuantityError}</div>
  {/if}

  <div class="cart-grid row">

    <div class="card cart-container">
      <div class="block-header shopping-cart-header">{l s='Shopping Cart' d='Shop.Theme.Checkout'}</div>
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
</section>
