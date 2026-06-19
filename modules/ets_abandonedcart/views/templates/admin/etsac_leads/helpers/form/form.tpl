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

{extends file="helpers/form/form.tpl"}
{block name="legend"}
    <div class="panel-heading" style="display: flex; justify-content: space-between;">
        <h4>{$field.title|escape:'html':'UTF-8'}</h4>
        <ul class="nav nav-tabs ets-ac-lead-tabs">
            <li class="active"><a href="#info" class="ets-ac-lead-tab-item js-ets-ac-lead-tab-item" data-tab="info">{l s='Info' mod='ets_abandonedcart'}</a></li>
            <li><a href="#fields" class="ets-ac-lead-tab-item js-ets-ac-lead-tab-item" data-tab="fields">{l s='Field list' mod='ets_abandonedcart'}</a></li>
            <li><a href="#thankyoupage" class="ets-ac-lead-tab-item js-ets-ac-lead-tab-item" data-tab="thankyoupage">{l s='"Thank you" page' mod='ets_abandonedcart'}</a></li>
        </ul>
    </div>
{/block}
{block name="input_row"}
    {if $input.type == 'field_list'}
        <div class="ets_ac_tab_lead_content_item ets_ac_tab_lead_item_field_list">
            {if isset($content_field_list)}
                {$content_field_list nofilter}
            {/if}
        </div>
    {elseif isset($input.class) && $input.class == 'on_thankyou_page'}
        <div class="ets_ac_tab_lead_content_item ets_ac_tab_lead_item_thankyou_page">
            {$smarty.block.parent}
        </div>
    {else}
        <div class="ets_ac_tab_lead_content_item ets_ac_tab_lead_content_item_info">
            {$smarty.block.parent}
        </div>
    {/if}
{/block}