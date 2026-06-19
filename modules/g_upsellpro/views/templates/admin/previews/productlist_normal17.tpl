{*
* Do not edit the file if you want to upgrade in future.
*
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @license   please read license in file license.txt
* @link	     http://www.globosoftware.net
*/
*}

{if $gupsellproducts}
    {if $html_template == 'popup' || $html_template == 'floating'}
        <div class="preview_demo {if $html_template == 'floating'}preview_floating{/if}">
    {/if}
    <section class="gupsellpro-box row page-product-box gupsellpro gupsellpro17 gps17 {if isset($ajaxcal) && $ajaxcal}gupsell_products_ajax{/if} gupsellbox-allproducts gupsellbox-checkproducts" id="gupsellpro-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}" data-gupsell-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}">
        <h2 class="gupsellpro_page-product-heading gupselltext-center text-uppercase">
            {$upsellObj->title[$id_lang]|escape:'html':'UTF-8'}
        </h2>
        <p class="upsell-description">
            <span>{$upsellObj->description[$id_lang]|escape:'html':'UTF-8'}</span>
        </p>
        <div class="gnone" style="display: none">
            <input type="hidden" class="gupsell_cart_token" value="{$cart_token|escape:'html':'UTF-8'}" />
            <input type="hidden" class="gupsell_action_link" value="{$link->getPageLink('cart', true)|escape:'html':'UTF-8'}" />
            <input class="gupsell-urlajax" value="{$urlajaxmodule|escape:'html':'UTF-8'}" type="hidden"/>
            <input class="gupsell-idupsell-rule" value="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}" type="hidden"/>
            <input class="gupsell-link-show" value="{$cart_show|escape:'html':'UTF-8'}" type="hidden"  />
            <input class="gupsell-remove-cart-product" value="{$upsellObj->remove_product|escape:'html':'UTF-8'}" type="hidden"  />
            <input class="gupsell-remove-product-upsell" value="{$upsellObj->remove_product_upsell|escape:'html':'UTF-8'}" type="hidden"  />
        </div>
        <div style="display:none;" class="alert alert-danger" role="alert">
            <p class="alert-text">{l s='No product chosen' mod='g_upsellpro'}</p>
        </div>
        <div class="gupsellpro_normal gupsellbox products">
            {foreach from=$gupsellproducts item='product' name=product}
                <div class="item {cycle values="odd,even"} col-xs-12 {if $count_product == 1} item_onlynone{/if} {if $mostpopular == $product.id_product}item_popular{/if}" data-type="normal" data-product-id="{$product.id_product|escape:'html':'UTF-8'}">
                    {if $mostpopular == $product.id_product}
                        <div class="upsellpro-most-popular">
                            <span class="upsellpro-most-popular-label">
                                {$getConfigFieldsValues['GSELL_SETTING_MOST_POPULAR'][$id_lang]|escape:'html':'UTF-8'}
                            </span>
                            <span class="upsellpro-most-popular-svg">
                                <svg aria-label="Remove" class="upsellpro-most-popular-svg-svg" fill="{$getConfigFieldsValues['GSELL_MAIN_MOSTPOPOLAR_BACKGROUND']|escape:'html':'UTF-8'}" height="24" viewBox="0 0 48 48" width="24"><path d="M43.5 48c-.4 0-.8-.2-1.1-.4L24 28.9 5.6 47.6c-.4.4-1.1.6-1.6.3-.6-.2-1-.8-1-1.4v-45C3 .7 3.7 0 4.5 0h39c.8 0 1.5.7 1.5 1.5v45c0 .6-.4 1.2-.9 1.4-.2.1-.4.1-.6.1z"></path></svg>
                            </span>
                        </div>
                    {/if}
                    <div class="gupsellpro_image">
                        <span style="background-image: url('{$product.cover.bySize.home_default.url|escape:'html':'UTF-8'}')"></span>
                    </div>
                    <div class="item-contentaction">
                        <div class="item-contentaction-content-left">
                            <div class="gupsellpro_des">
                                <div itemprop="name" class="product-name">
                                    <a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category, $product.ean13, null, $id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}" title="{$product.name|htmlspecialchars}">{$product.name|escape:'html':'UTF-8'}</a>
                                </div>
                            </div>
                            <div class="gupsellpro-variants">
                                {if !empty($product['AttributesGroups'])}
                                    <select class="form-control form-control-select" id="group_attribute" name="group_attribute">
                                        {foreach from=$product['AttributesGroups'] item=combination}
                                            <option value="{$combination['id_product_attribute']|escape:'html':'UTF-8'}" {$combination['selected']|escape:'html':'UTF-8'}>
                                                {$combination['attributes']|escape:'html':'UTF-8'}
                                            </option> 
                                        {/foreach}
                                    </select>
                                {/if}
                            </div>
                            <div class="content_qty  {if $upsellObj->display_customqty != 1}gnone{/if}">
                                <div class="gupsell-qty">
                                    <div class="input-group extra-touchspin">
                                        <span class="input-group-btn-vertical">
                                            <a href="#"
                                                data-field-qty="gupsellproqty" class="btn btn-default button-minus product_quantity_down"
                                                data-gupsell-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}"
                                                data-field-minqty="{if $upsellObj->qty > 0 }{$upsellObj->qty|escape:'html':'UTF-8'}{else}1{/if}"
                                                data-field-idproduct="{$product.id_product|escape:'html':'UTF-8'}"
                                                data-field-idattribute="{$product.id_product_attribute|escape:'html':'UTF-8'}">
                                                -
                                            </a>
                                        </span>
                                        <input type="number" min="{if $upsellObj->qty > 0 }{$upsellObj->qty|escape:'html':'UTF-8'}{else}1{/if}" name="gupsellproqty"
                                            class="input-group form-control gupsell_quantity_wanted" value="{if $upsellObj->qty > 0 }{$upsellObj->qty|escape:'html':'UTF-8'}{else}1{/if}"
                                            style="border: 1px solid rgb(189, 194, 201);" data-field-minqty="{if $upsellObj->qty > 0 }{$upsellObj->qty|escape:'html':'UTF-8'}{else}1{/if}"
                                            data-gupsell-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}"
                                            data-field-maxqty="100000000"
                                            data-field-idproduct="{$product.id_product|escape:'html':'UTF-8'}"
                                            data-field-idattribute="{$product.id_product_attribute|escape:'html':'UTF-8'}"/>
                                        <span class="input-group-btn-vertical">
                                            <a href="#"
                                                data-field-qty="gupsellproqty"
                                                class="btn btn-default button-plus product_quantity_up"
                                                data-gupsell-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}"
                                                data-field-minqty="{if $upsellObj->qty > 0 }{$upsellObj->qty|escape:'html':'UTF-8'}{else}1{/if}"
                                                data-field-maxqty="100000000"
                                                data-field-idproduct="{$product.id_product|escape:'html':'UTF-8'}"
                                                data-field-idattribute="{$product.id_product_attribute|escape:'html':'UTF-8'}">
                                                +
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="gupsellpro_price content_price product-price-and-shipping">
                                {hook h='displayProductPriceBlock' product=$product type="before_price"}
                                <span itemprop="price" class="content_price_label">
                                    {if !$priceDisplay}
                                        {Tools::displayPrice($product.price_new)|escape:'html':'UTF-8'}
                                    {else}
                                        {Tools::displayPrice($product.price_tax_exc_new)|escape:'html':'UTF-8'}
                                    {/if}
                                </span>
                                {if $upsellObj->apply_discount == 1 && $amountdiscount > 0}
                                    <span class="content_price_labelold old-price product-price egular-price">
                                        {Tools::displayPrice($product.price_nonediscount)|escape:'html':'UTF-8'}
                                    </span>
                                    <span class="gupselldiscount">
                                        {l s='SAVE ' mod='g_upsellpro'}
                                        <div class="gupselldiscount-amount-action-label">
                                            {if $upsellObj->type_discount == 1}
                                                {$amountdiscount|escape:'html':'UTF-8'}%
                                            {else}
                                                {Tools::displayPrice($amountdiscount)|escape:'html':'UTF-8'}
                                                <small>
                                                </small>
                                            {/if}
                                        </div>
                                    </span>
                                {/if}
                                {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                                {hook h='displayProductPriceBlock' product=$product type='weight'}
                            </div>
                        </div>
                        {if $count_product > 1} 
                            <div class="item-contentaction-content-right">
                                <div class="gupsell-option-box">
                                    <div class="gupsell_products_action">
                                        <div class="no-print">
                                            <a class="gupsell_ajax_add_to_cart_button btn btn-default btn-primary gupsellbtn-default" data-ipa="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-gupsellprodc="{$upsellObj->apply_discount|escape:'html':'UTF-8'}" title="{l s='Add to cart' mod='g_upsellpro'}" disabled>
                                                <span>
                                                    {if $upsellObj->remove_product == 0}
                                                        {$getConfigFieldsValues['GSELL_SETTING_BUTTON_ADCART'][$id_lang]|escape:'html':'UTF-8'}
                                                    {else}
                                                        {$getConfigFieldsValues['GSELL_SETTING_BUTTON_UPGRADE'][$id_lang]|escape:'html':'UTF-8'}
                                                    {/if}
                                                </span>
                                            </a>
                                            <a class="gupsell_ajax_delete_cart_button btn btn-default btn-primary gupsellbtn-default-red gnone" data-ipa="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-gupsellprodc="{$upsellObj->apply_discount|escape:'html':'UTF-8'}" title="{l s='Delete' mod='g_upsellpro'}" disabled>
                                                <i class="fas fa-trash-alt"></i>
                                                <span>
                                                    {l s='Delete' mod='g_upsellpro'}
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
        {if $html_template != 'popup' && $html_template != 'floating'}
            {if $count_product == 1} 
                <div class="gupsellfooter-popup-action gupsellonly_action">
                    <button type="button" class="button btn btn-default gupsellbtn-default {if $upsellObj->remove_product == 0}gupsell-addcart-checkout{else}gupsell-updatecart-checkout{/if}" disabled>
                        {if $upsellObj->remove_product == 0}
                            {$getConfigFieldsValues['GSELL_SETTING_BUTTON_CHECKOUT'][$id_lang]|escape:'html':'UTF-8'}
                        {else}
                            {$getConfigFieldsValues['GSELL_SETTING_BUTTON_UPGRADECART'][$id_lang]|escape:'html':'UTF-8'}
                        {/if}
                    </button>
                </div>
            {/if}
        {/if}
    </section>
    <div class="clear"></div>
    {if isset($ajaxcal) && $ajaxcal}
        <script type="text/javascript">
            if($('#blockcart-modal').length > 0){
                $('#blockcart-modal').addClass('hascros');
                $('.gupsell_products_ajax').appendTo($('#blockcart-modal .modal-body'));
            }
        </script>
    {/if}
    {if $html_template == 'popup' || $html_template == 'floating'}
            <div class="gupsellfooter-popup-action">
                {if $html_template == 'popup'}
                    <button class="btn btn-default gupsellbtn gupsell-close-fancybox-btn" disabled>{$getConfigFieldsValues['GSELL_SETTING_BUTTON_NOTHANKS'][$id_lang]|escape:'html':'UTF-8'}</button>
                {/if}
                {if $count_product == 1} 
                    <button type="button" class="button btn btn-default gupsellbtn-default {if $upsellObj->remove_product == 0}gupsell-addcart-checkout{else}gupsell-updatecart-checkout{/if}" disabled>
                        {if $upsellObj->remove_product == 0}
                            {$getConfigFieldsValues['GSELL_SETTING_BUTTON_CHECKOUT'][$id_lang]|escape:'html':'UTF-8'}
                        {else}
                            {$getConfigFieldsValues['GSELL_SETTING_BUTTON_UPGRADECART'][$id_lang]|escape:'html':'UTF-8'}
                        {/if}
                    </button>
                {/if}
            </div>
        </div>
    {/if}
{/if}
