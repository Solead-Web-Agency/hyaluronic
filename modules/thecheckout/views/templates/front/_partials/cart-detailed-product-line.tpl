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
{*<pre>{$product|print_r}</pre>*}
<div class="product-line">
  <!--  product left content: image-->
  <div class="product-line-image">
    <span class="product-image media-middle">
      {if isset($product.default_image) && $product.default_image}
        <img src="{$product.default_image.bySize.cart_default.url|escape:'javascript':'UTF-8'}" alt="{$product.name|escape:'quotes'}" loading="lazy"
             {if isset($product.default_image.bySize.cart_default.width)}
               width="{$product.default_image.bySize.cart_default.width}"
             {/if}
             {if isset($product.default_image.bySize.cart_default.height)}
               height="{$product.default_image.bySize.cart_default.height}"
             {/if}
      {else}
        {if $product.cover}
          <img src="{$product.cover.bySize.cart_default.url|escape:'javascript':'UTF-8'}" alt="{$product.name|escape:'quotes'}">
        {else}
          <img src="{$urls.no_picture_image.bySize.cart_default.url|escape:'javascript':'UTF-8'}" />
        {/if}
      {/if}
    </span>
  </div>

  <!--  product left body: description -->
  <div class="product-line-body">
    <div class="product-line-desc">
      <div class="product-line-info product-title">
        <a class="label" href="{$product.url|escape:'javascript':'UTF-8'}"
           data-id_customization="{$product.id_customization|intval}">{$product.name|escape:'htmlall':'UTF-8'}</a>
      </div>

      <div class="product-line-info product-price h5 {if $product.has_discount}has-discount{/if}">
        {if $product.has_discount|escape:'javascript':'UTF-8'}
          <div class="product-discount">
            <span class="regular-price">{$product.regular_price|escape:'javascript':'UTF-8'}</span>
            {if $product.discount_type === 'percentage'}
              <span class="discount discount-percentage">
                -{$product.discount_percentage_absolute|escape:'javascript':'UTF-8'}
              </span>
            {else}
              <span class="discount discount-amount">
                -{$product.discount_to_display|escape:'javascript':'UTF-8'}
              </span>
            {/if}
          </div>
        {/if}
        <div class="current-price">
          <span class="price">{$product.price|escape:'javascript':'UTF-8'}</span>
          {if $product.unit_price_full}
            <div class="unit-price-cart">{$product.unit_price_full|escape:'javascript':'UTF-8'}</div>
          {/if}
        </div>
        {hook h='displayProductPriceBlock' product=$product type="unit_price"}
      </div>

      <br/>
      <div class="product-reference">{$product.reference|escape:'javascript':'UTF-8'}</div>

      {foreach from=$product.attributes key="attribute" item="value"}
        <div class="product-line-info product-attribute">
          <span class="label">{$attribute|escape:'htmlall':'UTF-8'}:</span>
          <span class="value">{$value|escape:'htmlall':'UTF-8'}</span>
        </div>
      {/foreach}

        {if $z_tc_config->show_product_stock_info} {*tc module config*}
            <div class="product-line-info quantity-info">
            <span class="{if $product.quantity_available <= 0 && !$product.allow_oosp}qty-label label-warning{else}qty-label label-success{/if}
            {if $product.quantity_available <= 0} label-later{/if}">
                {if $product.quantity_available <= 0}
                    {if $product.allow_oosp}
                        {if isset($product.available_later) && $product.available_later}
                            {$product.available_later|escape:'htmlall':'UTF-8'}
                        {else}
                            {*{$product.availability_message}*}
                            {l s='In supplier stock' mod='thecheckout'}
                        {/if}
                    {else}
                        {l s='Out of stock' mod='thecheckout'}
                    {/if}
                {else}
                    {if isset($product.available_now) && $product.available_now}
                        {$product.available_now|escape:'htmlall':'UTF-8'}
                    {else}
                        {l s='In stock' d='Shop.Theme.Catalog'}
                    {/if}
                    <span class="stock-quantity">{$product.quantity_available}</span><span class="stock-quantity-label">{l s='pcs.' mod='thecheckout'}</span>
                {/if}
            </span>
            <div class='qty-insufficient-stock{if $product.quantity_available>=$product.quantity || $product.quantity_available<=0} hidden{/if}'>
                <span class='qty-in-stock-only'>{l s='In stock only' mod='thecheckout'} {$product.quantity_available|escape:'htmlall':'UTF-8'} {l s='pcs.' mod='thecheckout'}</span>
                {if $product.allow_oosp}
                    <span class='qty-remaining-on'>{l s='Remaining pcs. in' mod='thecheckout'} {$product.available_later|escape:'htmlall':'UTF-8'}</span>
                {else}
                    <span class='qty-remaining-on no-longer-available'>{l s='Please adjust quantity' mod='thecheckout'}</span>
                {/if}
            </div>{*hook h="displayProductDeliveryTime" product=$product*}</div>
        {/if}

      {if $product.customizations|count}
        {block name='cart_detailed_product_line_customization'}
          {foreach from=$product.customizations item="customization"}
            <div class="summary-customization details-shown"
                 id="product-customizations-modal-{$customization.id_customization|escape:'javascript':'UTF-8'}" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <a href="#" class="expand-customization">{l s='Product customization' d='Shop.Theme.Catalog'}</a>
                <div class="customization-details">
                      {foreach from=$customization.fields item="field"}
                        <div class="product-customization-line">
                          <span class="customization-label">{$field.label|escape:'htmlall':'UTF-8'}:</span>
                          <span class="customization-value">
                              {if $field.type == 'text'}
                                  {if (int)$field.id_module}
                                      {$field.text nofilter}
                                  {else}
                                      {$field.text|escape:'htmlall':'UTF-8'}
                                  {/if}
                              {elseif $field.type == 'image'}
                                <img src="{$field.image.small.url|escape:'javascript':'UTF-8'}">
                              {/if}
                          </span>
                        </div>
                      {/foreach}
                </div>
            </div>
            {* modal dialog implementation BEGIN *}
            {*
            <a href="#" data-toggle="modal"
               data-target="#product-customizations-modal-{$customization.id_customization|escape:'javascript':'UTF-8'}">{l s='Product customization' d='Shop.Theme.Catalog'}</a>
            <div class="modal fade customization-modal"
                 id="product-customizations-modal-{$customization.id_customization|escape:'javascript':'UTF-8'}" tabindex="-1" role="dialog"
                 aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">{l s='Product customization' d='Shop.Theme.Catalog'}</h4>
                  </div>
                  <div class="modal-body">
                    {foreach from=$customization.fields item="field"}
                      <div class="product-customization-line row">
                        <div class="col-sm-3 col-xs-4 label">
                          {$field.label|escape:'htmlall':'UTF-8'}
                        </div>
                        <div class="col-sm-9 col-xs-8 value">
                          {if $field.type == 'text'}
                            {if (int)$field.id_module}
                              {$field.text nofilter}
                            {else}
                              {$field.text|escape:'htmlall':'UTF-8'}
                            {/if}
                          {elseif $field.type == 'image'}
                            <img src="{$field.image.small.url|escape:'javascript':'UTF-8'}">
                          {/if}
                        </div>
                      </div>
                    {/foreach}
                  </div>
                </div>
              </div>
            </div>
            *}
            {* modal dialog implementation END *}
          {/foreach}
        {/block}
      {/if}
    </div>

    <!--  product left body: description -->
    <div class="product-line-actions">

      <div class="product-line-qty"
           data-qty-control="{$product.id_product|escape:'javascript':'UTF-8'}-{$product.id_product_attribute|escape:'javascript':'UTF-8'}-{$product.id_customization|escape:'javascript':'UTF-8'}">
        <div class="qty-container">
          <div class="qty-box">
            {if isset($product.is_gift) && $product.is_gift}
              <span class="gift-quantity">{$product.quantity}</span>
            {else}
              <input
                class="cart-line-product-quantity"
                data-link-action="x-update-cart-quantity"
                data-update-url="{$product.update_quantity_url}"
                data-id-product="{$product.id_product|escape:'javascript':'UTF-8'}"
                data-id-product-attribute="{$product.id_product_attribute|escape:'javascript':'UTF-8'}"
                data-id-customization="{$product.id_customization|escape:'javascript':'UTF-8'}"
                data-qty-orig="{$product.quantity|escape:'javascript':'UTF-8'}"
{*                data-step="{$product.minimal_quantity|escape:'javascript':'UTF-8'}"*}
                type="text"
                value="{$product.quantity}"
                name="product-quantity-spin"
                min="{$product.minimal_quantity}"
                aria-label="{l s='%productName% product quantity field' sprintf=['%productName%' => $product.name] d='Shop.Theme.Checkout'}"
              />
              <a class="cart-line-product-quantity-up"
                 href="{$product.up_quantity_url}"
                 data-link-action="x-update-cart-quantity-up" aria-label="Plus">{*Up*}</a>
              <a class="cart-line-product-quantity-down"
                 href="{$product.down_quantity_url}"
                 data-link-action="x-update-cart-quantity-down" aria-label="Minus">{*Down*}</a>
            {/if}
          </div>
        </div>
      </div>
      <div class="product-line-price">
            <span class="product-price">
              <strong>
                {if isset($product.is_gift) && $product.is_gift}
                  <span class="gift">{l s='Gift' d='Shop.Theme.Checkout'}</span>
                {else}
                  {$product.total}
                {/if}
              </strong>
            </span>
      </div>
      <div class="product-line-delete">
        <a
          class="remove-from-cart"
          rel="nofollow"
          href="{$product.remove_from_cart_url}"
          data-link-action="x-delete-from-cart"
          data-id-product="{$product.id_product|escape:'javascript':'UTF-8'}"
          data-id-product-attribute="{$product.id_product_attribute|escape:'javascript':'UTF-8'}"
          data-id-customization="{$product.id_customization|escape:'javascript':'UTF-8'}"
          title="{l s='Delete' d='Shop.Theme.Actions'}"
        >
          {if !isset($product.is_gift) || !$product.is_gift}
            <i class="material-icons delete-from-cart float-xs-left">delete</i>
            <span class="non-material-icon delete-from-cart"></span>
          {/if}
        </a>
      </div>
    </div>

    {block name='hook_cart_extra_product_actions'}
      {hook h='displayCartExtraProductActions' product=$product}
    {/block}

  </div>
</div>
