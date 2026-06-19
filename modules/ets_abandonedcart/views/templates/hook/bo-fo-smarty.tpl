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
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{if !empty($option.unsubscribe) && isset($trans)}
    <a class="ets_abancart_link_unsubscribe" href="{$option.unsubscribe nofilter}">
        {$trans.ETS_ABANCART_MAIL_UNSUBSCRIBE|escape:'html':'UTF-8'}
    </a>
{elseif !empty($option.button_add_discount) && isset($trans)}
    {assign var="is_highlight_bar" value=isset($option.campaign_type) && trim($option.campaign_type) == 'bar'}
    <button type="button" class="ets_abancart_add_discount{if $is_highlight_bar} bar{/if}" data-code="{if !empty($option.discount_code)}{$option.discount_code|escape:'html':'UTF-8'}{else}[discount_code]{/if}"{if isset($option.style) && $option.style} style="{$option.style|escape:'quotes':'UTF-8'}"{/if}>
        {if $is_highlight_bar}
            {$trans.ETS_ABANCART_MAIL_HIGHLIGHT_BAR_BUTTON_ADD_DISCOUNT|replace:'%s': $option.reduction|escape:'html':'UTF-8'}
        {else}
            {$trans.ETS_ABANCART_MAIL_BUTTON_ADD_DISCOUNT|escape:'html':'UTF-8'}
        {/if}
    </button>
{elseif !empty($option.show_discount_box) && isset($trans)}
    <span class="ets_abancart_box" data-tooltip="{$trans.ETS_ABANCART_MAIL_SHOW_DISCOUNT_BOX|escape:'html':'UTF-8'}">
        <span class="ets_abancart_box_discount"{if isset($option.style) && $option.style} style="{$option.style|escape:'quotes':'UTF-8'}"{/if}>
            <i class="ets-ab-icon ets-ab-icon-cut">
                <svg width="1792" height="1792" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M960 896q26 0 45 19t19 45-19 45-45 19-45-19-19-45 19-45 45-19zm300 64l507 398q28 20 25 56-5 35-35 51l-128 64q-13 7-29 7-17 0-31-8l-690-387-110 66q-8 4-12 5 14 49 10 97-7 77-56 147.5t-132 123.5q-132 84-277 84-136 0-222-78-90-84-79-207 7-76 56-147t131-124q132-84 278-84 83 0 151 31 9-13 22-22l122-73-122-73q-13-9-22-22-68 31-151 31-146 0-278-84-82-53-131-124t-56-147q-5-59 15.5-113t63.5-93q85-79 222-79 145 0 277 84 83 52 132 123t56 148q4 48-10 97 4 1 12 5l110 66 690-387q14-8 31-8 16 0 29 7l128 64q30 16 35 51 3 36-25 56zm-681-260q46-42 21-108t-106-117q-92-59-192-59-74 0-113 36-46 42-21 108t106 117q92 59 192 59 74 0 113-36zm-85 745q81-51 106-117t-21-108q-39-36-113-36-100 0-192 59-81 51-106 117t21 108q39 36 113 36 100 0 192-59zm178-613l96 58v-11q0-36 33-56l14-8-79-47-26 26q-3 3-10 11t-12 12q-2 2-4 3.5t-3 2.5zm224 224l96 32 736-576-128-64-768 431v113l-160 96 9 8q2 2 7 6 4 4 11 12t11 12l26 26zm704 416l128-64-520-408-177 138q-2 3-13 7z"/></svg>
            </i>
            [discount_code]
        </span>
    </span>
{elseif !empty($option.shop_logo)}
    <img src="{$option.shop_logo|escape:'html':'UTF-8'}" alt="{l s='Shop Logo' mod='ets_abandonedcart'}"/>
{elseif !empty($option.checkout_button) && isset($trans)}
    <a class="ets_abancart_checkout" href="{$option.checkout_button nofilter}" style="background-color: #2FB5DB;border: medium none;text-decoration: none;border-radius: 4px;color: #fff;cursor: pointer;font-size: 14px;margin: 0 auto;padding: 7px 15px;text-align: center;{if isset($option.style) && $option.style}{$option.style|escape:'quotes':'UTF-8'}{/if}">
        {$trans.ETS_ABANCART_MAIL_CHECKOUT_BUTTON|escape:'html':'UTF-8'}
    </a>
{elseif isset($option.discount_count_down_clock) && $option.discount_count_down_clock}
    <span class="ets_abancart_group_clock">
        <span class="ets_abancart_count_down_clock" data-style="{if isset($option.style)}{$option.style nofilter}{/if}" data-date="{strtotime($option.date_to)|intval}"></span>
    </span>
{elseif !empty($option.countdown_clock)}
    <span class="ets_abancart_group_clock2">
        <span class="ets_ac_evt_countdown2" data-style="{if isset($option.style)}{$option.style nofilter}{/if}" data-date="{$option.endtime|intval}"></span>
    </span>
{elseif isset($option.custom_button_text)}
    {if $option.custom_button_text}
        <a href="{if isset($option.custom_button_href)}{$option.custom_button_href nofilter}{/if}" style="{if isset($option.style)}{$option.style nofilter}{/if}" class="ets_ac_custom_button">{$option.custom_button_text|escape:'html':'UTF-8'}</a>
    {/if}
{elseif !empty($option.logo)}
    <a href="{literal}{shop_url}{/literal}" target="_blank"
       style="color: #25B9D7; text-decoration: underline; font-weight: 600;">
        <img height="auto" src="{literal}{shop_logo}{/literal}"
             style="line-height: 100%; -ms-interpolation-mode: bicubic; border: 0; display: inline-block; outline: none; text-decoration: none; height: auto;margin: 0 auto; max-width: 350px; font-size: 13px;"
             border="0">
    </a>
{elseif !empty($option.button_no_thanks) && isset($trans)}
    <a class="ets_abancart_no_thanks" style="text-decoration: underline!important; {$option.style|escape:'quotes':'UTF-8'}" href="#">
        {$trans.ETS_ABANCART_MAIL_BUTTON_NO_THANKS|escape:'html':'UTF-8'}
    </a>
{elseif isset($option.shop_button) && !empty($option.shop_button) && isset($trans)}
    <a class="ets_abancart_shop_button" style="background-color: rgb(55, 71, 79);display: inline-block;border: none;text-decoration: none;border-radius: 7px;color: rgb(255, 255, 255);cursor: pointer;font-size: 22px;margin: 0px;padding: 8px 25px;text-align: center;text-transform: uppercase;{$option.style|escape:'quotes':'UTF-8'}" href="{$option.shop_button nofilter}">
        {$trans.ETS_ABANCART_MAIL_SHOP_BUTTON|escape:'html':'UTF-8'}
    </a>
{elseif isset($option.product_grid)}
    <div class="ets-ac-product-grid-box ets_ac_prd_grid_{$option.grid_id|escape:'html':'UTF-8'}">
        {$option.product_grid nofilter}
        <style rel="stylesheet">
            .ets_ac_prd_grid_{$option.grid_id|escape:'html':'UTF-8'} div,
            .ets_ac_prd_grid_{$option.grid_id|escape:'html':'UTF-8'} a,
            .ets_ac_prd_grid_{$option.grid_id|escape:'html':'UTF-8'} p{
                {$option.style|escape:'quotes':'UTF-8'}
            }
        </style>
    </div>
{elseif isset($option.product_list)}
    {assign var="idProductList" value=""}
    {if isset($option.id_list)}
        {assign var="idProductList" value=$option.id_list}
    {/if}
    <div class="ets_abancart_product_list{$idProductList|escape:'html':'UTF-8'}" {if isset($option.style) && $option.style} style="{$option.style|escape:'quotes':'UTF-8'}"{/if}>
        {$option.product_list nofilter}
        <style rel="stylesheet">
            .ets_abancart_product_list{$idProductList|escape:'html':'UTF-8'} div,
            .ets_abancart_product_list{$idProductList|escape:'html':'UTF-8'} a,
            .ets_abancart_product_list{$idProductList|escape:'html':'UTF-8'} p{
                {$option.style|escape:'quotes':'UTF-8'}
            }
        </style>
    </div>
{elseif isset($option.short_code_content)}
    <span class="ets_abancart_shot_code_content" {if isset($option.style) && $option.style} style="{$option.style|escape:'quotes':'UTF-8'}"{/if}>
        {$option.short_code_content nofilter}
    </span>
{elseif isset($option.lead_form)}
    <div class="ets_abancart_lead_form" {if isset($option.style) && $option.style} style="{$option.style|escape:'quotes':'UTF-8'}"{/if}>
        {$option.lead_form nofilter}
    </div>
{/if}