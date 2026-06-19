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
    {if $key == 'ETS_SEO_RATING_PAGES' && isset($ets_seo_rating_pages) && isset($ETS_SEO_RATING_PAGES)}
        <div class="">
            <div class="col-lg-9">
                {foreach $ets_seo_rating_pages as $k=>$op}
                    <p class="checkbox">
                        {strip}
                            <label class="col-lg-3" for="ETS_SEO_RATING_PAGES_{$op.value|escape:'html':'UTF-8'}_on">
                                <input type="checkbox" name="ETS_SEO_RATING_PAGES[]" id="ETS_SEO_RATING_PAGES_{$op.value|escape:'html':'UTF-8'}_on"
                                       value="{$op.value|escape:'html':'UTF-8'}" {if in_array($op.value, $ETS_SEO_RATING_PAGES)}checked="checked"{/if}/>
                                {$op.title|escape:'html':'UTF-8'}
                            </label>
                        {/strip}
                        {if isset($op.desc) && $op.desc}
                            <p class="text-muted">{$op.desc|escape:'UTF-8'}</p>
                        {/if}
                    </p>
                {/foreach}
            </div>
        </div>
    {elseif $key != 'ETS_SEO_RATING_ENABLED'}
        <div class="js-ets-seo-rating-field">
            {$smarty.block.parent}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}