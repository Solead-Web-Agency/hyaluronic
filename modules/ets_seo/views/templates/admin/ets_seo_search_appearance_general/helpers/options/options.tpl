{*
* 2007-2021 ETS-Soft
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
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

{extends file="helpers/options/options.tpl"}

{block name="input"}
    {if $key == 'ETS_SEO_TITLE_SEPARATOR'}
        <div class="col-lg-9">
            <div class="ets_seo_radio_separator">
                {foreach $field['choices'] AS $k => $v}
                    {strip}
                    <input type="radio" name="{$key|escape:'html':'UTF-8'}" id="{$key|escape:'html':'UTF-8'}_{$k|escape:'html':'UTF-8'}" value="{$k|escape:'html':'UTF-8'}"
                        class="radio-separator"
                        {if $k == $field['value']} checked="checked"{/if}
                        {if isset($field['js'][$k])} {$field['js'][$k]|escape:'html':'UTF-8'}{/if}/>
                    <label for="{$key|escape:'html':'UTF-8'}_{$k|escape:'html':'UTF-8'}" class="label-separator">{$v|escape:'html':'UTF-8'}</label>
                    {/strip}
                {/foreach}
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}