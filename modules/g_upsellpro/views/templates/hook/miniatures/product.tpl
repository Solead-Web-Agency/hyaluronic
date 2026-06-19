{*
* Do not edit the file if you want to upgrade in future.
*
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @license   please read license in file license.txt
* @link	     http://www.globosoftware.net
*/
*}

{foreach from=$gupsellproducts item='product' name=product}
    {if $type_template == 'normal'}
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
            <span style="background-image: url('{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}')"></span>
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
                            {convertPrice price=$product.price_new}
                        {else}
                            {convertPrice price=$product.price_tax_exc_new}
                        {/if}
                    </span>
                    {if $upsellObj->apply_discount == 1}
                        <span class="content_price_labelold old-price product-price egular-price">
                            {convertPrice price=$product.price_nonediscount}
                        </span>
                        <span class="gupselldiscount">
                            {l s='SAVE ' mod='g_upsellpro'}
                            <div class="gupselldiscount-amount-action-label">
                                {if $upsellObj->type_discount == 1}
                                    {$amountdiscount|escape:'html':'UTF-8'}%
                                {else}
                                    {convertPrice price=$amountdiscount}
                                    <small>
                                    {*if $upsellObj->amount_discount == 0}
                                        {l s='tax excl.' mod='g_upsellpro'}
                                    {else}
                                        {l s='tax incl.' mod='g_upsellpro'}
                                    {/if*}
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
                                <a class="gupsell_ajax_add_to_cart_button btn btn-default btn-primary gupsellbtn-default" data-ipa="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-gupsellprodc="{$upsellObj->apply_discount|escape:'html':'UTF-8'}" title="{l s='Add to cart' mod='g_upsellpro'}" >
                                    <span>
                                        {if $upsellObj->remove_product == 0}
                                            {$getConfigFieldsValues['GSELL_SETTING_BUTTON_ADCART'][$id_lang]|escape:'html':'UTF-8'}
                                        {else}
                                            {$getConfigFieldsValues['GSELL_SETTING_BUTTON_UPGRADE'][$id_lang]|escape:'html':'UTF-8'}
                                        {/if}
                                    </span>
                                </a>
                                <a class="gupsell_ajax_delete_cart_button btn btn-default btn-primary gupsellbtn-default-red gnone" data-ipa="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-gupsellprodc="{$upsellObj->apply_discount|escape:'html':'UTF-8'}" title="{l s='Delete' mod='g_upsellpro'}" >
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
    {elseif $type_template == 'bundle'}
        <div class="gupsellpro_image">
                <span style="background-image: url('{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}')"></span>
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
                </div>
            </div>
    {elseif $type_template == 'volume'}
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
            <span style="background-image: url('{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}')"></span>
            <div class="warp-upsellcheckbox-cart gnone">
                <input type="checkbox" class="upsellcheckbox-sample-overlay" id="checkbox-for-{$upsellObj->id_g_upsellrule|escape:'html':'UTF-8'}-{$product.id_product|escape:'html':'UTF-8'}" name="upsellcheckbox-sample-overlay[]" value="{$product.id_product|escape:'html':'UTF-8'}-{$product.id_product_attribute|escape:'html':'UTF-8'}"  data-gupsellprodc="{$upsellObj->apply_discount|escape:'html':'UTF-8'}" data-idproduct="{$product.id_product|escape:'html':'UTF-8'}" data-idattribute="{$product.id_product_attribute|escape:'html':'UTF-8'}" {if $numberkey == 0} checked="checked" {/if}>
            </div>
        </div>
        <div class="item-contentaction">
            <div class="item-contentaction-content-left">
                <div class="gupsellpro_des">
                    <div itemprop="name" class="product-name">
                        <a href="#" title="{$product.name|htmlspecialchars}">{if $upsellObj->qty > 0 }{$upsellObj->qty|escape:'html':'UTF-8'}{else}1{/if} {l s='Quantities' mod='g_upsellpro'}{*$product.name|escape:'html':'UTF-8'*}</a>
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
            </div>
        </div>
        <div class="gupsellpro_price content_price product-price-and-shipping">
            {hook h='displayProductPriceBlock' product=$product type="before_price"}
            {if $upsellObj->apply_discount == 1}
                <span class="gupselldiscount">
                    {l s='SAVE ' mod='g_upsellpro'}
                    <div class="gupselldiscount-amount-action-label">
                        {if $upsellObj->type_discount == 1}
                            {$amountdiscount|escape:'html':'UTF-8'}%
                        {else}
                            {convertPrice price=$amountdiscount}
                            <small>
                            </small>
                        {/if}
                    </div>
                </span>
            {/if}
            <div class="bundle_price_box">
                <span itemprop="price" class="content_price_label">
                    {if !$priceDisplay}
                        {convertPrice price=$product.price_new}
                    {else}
                        {convertPrice price=$product.price_tax_exc_new}
                    {/if}
                </span>
                <span class="content_price_labelold old-price product-price egular-price gnone">
                    {convertPrice price=$product.price_nonediscount}
                </span>
                <span class="bundle_price_box_lable">
                    {l s='Total item' mod='g_upsellpro'}
                </span>
            </div>
            {hook h='displayProductPriceBlock' product=$product type='unit_price'}
            {hook h='displayProductPriceBlock' product=$product type='weight'}
        </div>
    {elseif $type_template == 'frequently'}
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
            <span style="background-image: url('{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}')"></span>
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
                            {convertPrice price=$product.price_new}
                        {else}
                            {convertPrice price=$product.price_tax_exc_new}
                        {/if}
                    </span>
                    {if $upsellObj->apply_discount == 1}
                        <span class="content_price_labelold old-price product-price egular-price">
                            {convertPrice price=$product.price_nonediscount}
                        </span>
                        <span class="gupselldiscount">
                            {l s='SAVE ' mod='g_upsellpro'}
                            <div class="gupselldiscount-amount-action-label">
                                {if $upsellObj->type_discount == 1}
                                    {$amountdiscount|escape:'html':'UTF-8'}%
                                {else}
                                    {convertPrice price=$amountdiscount}
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
    {/if}
{/foreach}