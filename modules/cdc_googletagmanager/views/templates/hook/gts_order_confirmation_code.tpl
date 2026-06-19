{*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @package   cdc_googletagmanager
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<!-- START Google Trusted Stores Order -->
<div id="gts-order" style="display:none;" translate="no">

  <!-- start order and merchant information -->
  <span id="gts-o-id">{$gts['gts-o-id']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-domain">{$gts['gts-o-domain']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-email">{$gts['gts-o-email']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-country">{$gts['gts-o-country']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-currency">{$gts['gts-o-currency']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-total">{$gts['gts-o-total']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-discounts">{$gts['gts-o-discounts']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-shipping-total">{$gts['gts-o-shipping-total']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-tax-total">{$gts['gts-o-tax-total']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-est-ship-date">{$gts['gts-o-est-ship-date']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-est-delivery-date">{$gts['gts-o-est-delivery-date']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-has-preorder">{$gts['gts-o-has-preorder']|escape:'htmlall':'UTF-8'}</span>
  <span id="gts-o-has-digital">{$gts['gts-o-has-digital']|escape:'htmlall':'UTF-8'}</span>
  <!-- end order and merchant information -->

  <!-- start repeated item specific information -->
{foreach $gts['gts-item'] item=product}
  <span class="gts-item">
    <span class="gts-i-name">{$product['gts-i-name']|escape:'htmlall':'UTF-8'}</span>
    <span class="gts-i-price">{$product['gts-i-price']|escape:'htmlall':'UTF-8'}</span>
    <span class="gts-i-quantity">{$product['gts-i-quantity']|escape:'htmlall':'UTF-8'}</span>
    {* <!--<span class="gts-i-prodsearch-id">ITEM_GOOGLE_SHOPPING_ID</span>
    <span class="gts-i-prodsearch-store-id">ITEM_GOOGLE_SHOPPING_ACCOUNT_ID</span>--> 
  *}</span>
{/foreach}
  <!-- end repeated item specific information -->

</div>
<!-- END Google Trusted Stores Order -->