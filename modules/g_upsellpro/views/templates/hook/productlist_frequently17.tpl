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
    {if $html_template == 'popup'}
        <div class="popup-product-box">
            <a class="btn btn-default  btn-secondary gupsell-popup gupsellbtn-default" href="#gupsellpropopup-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}" style="display:none;"></a>
            <div class="gpopup-conten gupsellpro_popup gupsellpro-box {if $version17} gps17 {/if}" id="gupsellpropopup-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}" data-gupsell-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}" style="display:none;">
                <div class="upsellpro-overlay-close">
                    <button class="btn btn-default">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
    {elseif $html_template == 'floating'}
        <div class="floating-product-box gnone" id="gupsellprofloating-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}">
            <div class="floating-product-box-content">
                <div class="floating-product-box-button">
                    <div class="floating-button-conten">
                        <div class="floating-button-text1 gnone active" href="#gupsellprofloating-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}" data-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}">
                            <i class="fas fa-gift"></i>
                            <span>
                                {$getConfigFieldsValues['GSELL_SETTING_BUTTON_FLOATING'][$id_lang]|escape:'html':'UTF-8'}
                            </span>
                        </div>
                        <div class="floating-button-text2 gnone" href="#gupsellprofloating-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}" data-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>
    {/if}
    <div class="{if $html_template != 'popup'}gupsellpro-box {if $version17} gps17 {/if}{/if} {if $html_template == 'floating'}floating-product-box-show-product gnone{/if} {if isset($ajaxcal) && $ajaxcal}gupsell_products_ajax{/if}" data-gupsell-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}">
        <section class="page-product-box gupsellpro gupsellpro17  gupsellbox-allproducts gupsellbox-checkproducts" id="gupsellpro-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}" data-gupsell-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}">
            <h2 class="gupsellpro_page-product-heading gupselltext-center">
                {$upsellObj->title|escape:'html':'UTF-8'}
            </h2>
            <p class="upsell-description">
                <span>{$upsellObj->description|escape:'html':'UTF-8'}</span>
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
            <div class="gupsellpro_frequently gupsellbox products">
                {foreach from=$gupsellproducts item='product' name=product}
                    <div class="item {cycle values="odd,even"} col-xs-12 {if $mostpopular == $product.id_product}item_popular{/if}" data-type="frequently" data-product-id="{$product.id_product|escape:'html':'UTF-8'}">

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
                            <div class="warp-upsellcheckbox-cart ">
                                <input type="checkbox" class="upsellcheckbox-sample-overlay" id="checkbox-for-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}-{$product.id_product|escape:'html':'UTF-8'}" name="upsellcheckbox-sample-overlay[]" value="{$product.id_product|escape:'html':'UTF-8'}-{$product.id_product_attribute|escape:'html':'UTF-8'}"  data-gupsellprodc="{$upsellObj->apply_discount|escape:'html':'UTF-8'}" data-idproduct="{$product.id_product|escape:'html':'UTF-8'}" data-idattribute="{$product.id_product_attribute|escape:'html':'UTF-8'}" checked="checked">
                            </div>
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
                                    {if $upsellObj->apply_discount == 1}
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
                            <div class="item-contentaction-content-right">
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="upsellOptionsfrequentlyprice">
                <div class="item_price {cycle values="odd,even"}">
                    <div class="gupsell_products_action_allproducts ">
                        <div class="gupsell-totalprice gupselltext-center">
                            <div class="gupsell-totalprice-box">
                                <span class="gupsell-totalprice-label">
                                    {$getConfigFieldsValues['GSELL_SETTING_BUTTON_TOTAL'][$id_lang]|escape:'html':'UTF-8'} : 
                                </span>
                                <div class="gupsell-Price-text">
                                    <span class="price-product-price"> {Tools::displayPrice($gupselltotalpricenew)|escape:'html':'UTF-8'}</span>
                                    {if $upsellObj->apply_discount == 1}
                                        <span class="content_price_labelold old-price product-price"> {Tools::displayPrice($gupselltotalprice)|escape:'html':'UTF-8'}</span>
                                        <span class="gupselldiscount">
                                            {l s='SAVE ' mod='g_upsellpro'}
                                            <div class="gupselldiscount-amount-action-label">
                                                {Tools::displayPrice($gupselltotalpricediscount)|escape:'html':'UTF-8'}
                                                <small>
                                                </small>
                                            </div>
                                        </span>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        {if $html_template != 'popup'}
            <div class="gupsell-button-addtocart checkout-button" data-id-upsell="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}">
                <button type="button" class="button btn btn-default gupsellbtn-default {if $upsellObj->remove_product == 0}gupsell-addcart-checkout{else}gupsell-updatecart-checkout{/if}">
                {if $upsellObj->remove_product == 0}
                    {$getConfigFieldsValues['GSELL_SETTING_BUTTON_CHECKOUT'][$id_lang]|escape:'html':'UTF-8'}
                {else}
                    {$getConfigFieldsValues['GSELL_SETTING_BUTTON_UPGRADECART'][$id_lang]|escape:'html':'UTF-8'}
                {/if}
                </button>
            </div>
        {/if}
        <div class="clear"></div>
        {if isset($ajaxcal) && $ajaxcal}
            <script type="text/javascript">
                if($('#blockcart-modal').length > 0){
                    $('#blockcart-modal').addClass('hascros');
                    $('#blockcart-modal .modal-body').prepend($('.gupsell_products_ajax'));
                }
            </script>
        {/if}
    </div>
    {if $html_template == 'popup'}
                <div class="gupsellfooter-popup gupselltext-center">
                    <div class="gupsellcart-footer-popup">
                        <div class="usfppHeader">
                            <span class="usfppHeader-title">{l s='Cart' mod='g_upsellpro'}</span>
                            <span class="usfppHeader-btn"><span>↑</span></span>
                        </div>
                        <div class="usfppContent">
                            {if $product_carts}
                                {foreach from=$product_carts item=product_cart}
                                <div class="usfppContent-item">
                                    <div class="usfppContent-img">
                                        <span>
                                            <img src="{$link->getImageLink($product_cart['link_rewrite']|escape:'htmlall':'UTF-8', $product_cart['id_image']|escape:'htmlall':'UTF-8', 'home_default')}"/>
                                        </span>
                                    </div>
                                    <div class="usfppContent-qty">
                                        {$product_cart['cart_quantity']|escape:'html':'UTF-8'}
                                    </div>
                                </div>
                                {/foreach}
                            {/if}
                        </div>
                        <div class="usfppTotal">
                            <span class="usfppTotal-title">{l s='Total' mod='g_upsellpro'}</span>
                            <span class="usfppTotal-price">
                                <span>{$total_cart|escape:'html':'UTF-8'}</span>
                            </span>
                        </div>
                    </div>
                    <div class="gupsellfooter-popup-action">
                        <button class="btn btn-default gupsellbtn gupsell-close-fancybox-btn">{$getConfigFieldsValues['GSELL_SETTING_BUTTON_NOTHANKS'][$id_lang]|escape:'html':'UTF-8'}</button>
                        <button type="button" class="button btn btn-default gupsellbtn-default {if $upsellObj->remove_product == 0}gupsell-addcart-checkout{else}gupsell-updatecart-checkout{/if}">
                            {if $upsellObj->remove_product == 0}
                                {$getConfigFieldsValues['GSELL_SETTING_BUTTON_CHECKOUT'][$id_lang]|escape:'html':'UTF-8'}
                            {else}
                                {$getConfigFieldsValues['GSELL_SETTING_BUTTON_UPGRADECART'][$id_lang]|escape:'html':'UTF-8'}
                            {/if}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {elseif $html_template == 'floating'}
                <div class="upsellfloating-prev-action gnone" data-id="{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}">
                    <div class="upsellfloating-prev-back gnone">
                        <span>
                            <i>←</i> {l s='Previous Offer' mod='g_upsellpro'}
                        </span>
                    </div>
                    <div class="upsellfloating-prev-next gnone">
                        <span>
                            {l s='Next Offer' mod='g_upsellpro'} <i>→</i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/if}
