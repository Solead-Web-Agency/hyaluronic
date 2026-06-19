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
    {if $key == 'ETS_SEO_PINTEREST_CONFIRM'}
        <div class="col-lg-9">
            <input class="form-control {if isset($field['class'])}{$field['class'] nofilter}{/if}" type="{$field['type'] nofilter}"{if isset($field['id'])} id="{$field['id']|escape:'html':'UTF-8'}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key|escape:'html':'UTF-8'}" value="{if isset($field['no_escape']) && $field['no_escape']}{$field['value']|escape:'UTF-8'}{else}{$field['value']|escape:'html':'UTF-8'}{/if}" {if isset($field['autocomplete']) && !$field['autocomplete']}autocomplete="off"{/if}/>
            <div class="help-block"><em>{l s='How to claim your website:' mod='ets_seo'} <a href="https://www.pinterest.com/settings/claim/" rel="noreferrer noopener">https://www.pinterest.com/settings/claim/</a></em></div>
        </div>

    {else}
        {$smarty.block.parent}
    {/if}
{/block}