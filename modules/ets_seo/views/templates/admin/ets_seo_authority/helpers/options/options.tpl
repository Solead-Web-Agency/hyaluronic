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
    {if $field['type'] == 'file'}
        <div class="ets-seo-file-input-bo" data-value="{$field['value']|escape:'html':'UTF-8'}">
            {$smarty.block.parent}
            {if $field['name'] == 'ETS_SEO_SITE_ORIG_LOGO' || $field['name'] == 'ETS_SEO_SITE_PERSON_AVATAR'}
                <div class="col-lg-9 col-lg-offset-3 offset-lg-3">
                    <div class="ets-seo-img-logo">
                        {if $field['value']}
                            <span class="remove-logo" data-name="{$field['name']|escape:'html':'UTF-8'}" title="{l s='Delete' mod='ets_seo'}"><i class="fa fa-close"></i></span>
                            <img src="{$ets_seo_link_img|escape:'html':'UTF-8'}{$field['value']|escape:'html':'UTF-8'}" class="ets-seo-img-logo-load" onerror="etsSeoLogoError(this)" data-name="{$field['name']|escape:'html':'UTF-8'}">
                        {/if}
                    </div>
                </div>
            {/if}
        </div>

    {else}
        {$smarty.block.parent}
    {/if}
{/block}