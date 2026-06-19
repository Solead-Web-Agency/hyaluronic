{*
* 2007-2022 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

<div class="ets_abancart_product_grid">
    <div class="cleafix" style="width: 100%;display:block;clear: both;"></div>
    {foreach $product_grid as $item}
    <div class="product-item" style="float: left;width: 33.33%;padding:15px;">
        <div class="product-wrapper">
            <img src="{$item.image nofilter}" alt="{$item.name|truncate:20:'...':true|escape:'html':'UTF-8'}" style="width: 100%;">
            <div class="ets_abancart_product_info" style="text-align:center;">
                <div class="product-line-info">
                    <a href="{$item.link|escape:'html':'UTF-8'}" title="{$item.name|truncate:80:'...':true|escape:'html':'UTF-8'}" style="text-decoration: none;line-height:1.3;display:block;margin-bottom:5px;color: #37474f;font-weight: 600;font-size: 14px;">
                        <span class="product_name" style="line-height:1.3;display:block;">{$item.name|truncate:80:'...':true|escape:'html':'UTF-8'}</span>
                    </a>
                </div>
                {if isset($item.attributes) && $item.attributes}
                    {assign var='ik2' value=0}
                    <div class="product_combination" style="font-size:11px;">
                        {foreach from=$item.attributes item='attribute'}
                            {assign var='ik2' value=$ik2+1}
                            {$attribute.group_name|truncate:80:'...':true|escape:'html':'UTF-8'}-{$attribute.attribute_name|truncate:80:'...':true|escape:'html':'UTF-8'}
                            {if $ik2 < count($item.attributes)}, {/if}
                        {/foreach}
                    </div>
                {/if}
            </div>
            <div class="product-line-info product-price has-discount" >
                <span class="p_price" style="display:inline-block;color:#00AFF0;">
                    <span class="price" style="color:#00AFF0;">{$item.price|escape:'html':'UTF-8'}</span>
                    {if !empty($item.old_price)}
                        <span class="regular-price" style="text-decoration: line-through;color: #999;margin-left:12px;">
                            {$item.old_price|escape:'html':'UTF-8'}
                        </span>
                    {/if}
                </span>
            </div>
        </div>
    </div>
    {/foreach}
    <div class="cleafix" style="width: 100%;display:block;clear: both;"></div>
</div>