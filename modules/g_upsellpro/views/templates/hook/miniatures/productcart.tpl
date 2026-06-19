{*
* Do not edit the file if you want to upgrade in future.
*
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @license   please read license in file license.txt
* @link	     http://www.globosoftware.net
*/
*}

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