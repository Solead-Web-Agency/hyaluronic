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
{block name="label"}
    {if $key == 'ETS_SEO_SITEMAP_LANG'}
        {if isset($ets_seo_multilang_activated) && $ets_seo_multilang_activated}
            {$smarty.block.parent}
        {else}

        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name="input"}
    {if $key == 'ETS_SEO_SITEMAP_PRIMARY'}
        <div class="col-lg-9">
            <ul class="sitemap-link-list">
                <li class="sitemap-link-item">
                    <a href="{$ets_seo_baseurl|escape:'quotes'}sitemap.xml" target="_blank">
                        {$ets_seo_baseurl|escape:'quotes'}sitemap.xml
                    </a>
                </li>
            </ul>
            <p class="text-muted"><em>{l s='This is dynamic sitemap containing sub sitemaps to all your pages. Just submit this sitemap to Google and other search engines (Bing, Baidu, etc.)' mod='ets_seo'}</em></p>
        </div>
    {elseif $key == 'ETS_SEO_SITEMAP_LANG'}
        <div class="col-lg-9">
            {if isset($ets_seo_multilang_activated) && $ets_seo_multilang_activated}
                <ul class="sitemap-link-list">
                {foreach $ets_seo_languages as $lang}
                    <li class="sitemap-link-item">
                        <a href="{$ets_seo_baseurl|escape:'quotes'}{$lang.iso_code|escape:'html':'UTF-8'}/sitemap.xml" target="_blank">
                            <img class="sitemap-lang-img" src="{$ets_seo_base_uri|escape:'quotes'}/img/l/{$lang.id_lang|escape:'html':'UTF-8'}.jpg">
                            {$ets_seo_baseurl|escape:'quotes'}{$lang.iso_code|escape:'html':'UTF-8'}/sitemap.xml
                        </a>
                    </li>
                {/foreach}
                <ul>
            {/if}
        </div>
    {elseif $key == 'ETS_SEO_SITEMAP_PRIORITY'}
        <div class="col-lg-9">
            <div class="ets-seo-priority-list">
                {foreach $ets_seo_priority_options as $k=>$op}
                    <div class="form-group priority-group row">
                        <span class="col-lg-1 text-right">{$op.label|escape:'html':'UTF-8'}</span>
                        <div class="col-lg-2">
                            <select class="form-control" name="{$op.name|escape:'html':'UTF-8'}">
                                {for $range=0 to 9}
                                <option value="0.{$range|escape:'html':'UTF-8'}" {if $op.value == ($range/10)}selected="selected"{/if}>0.{$range|escape:'html':'UTF-8'}</option>
                                {/for}
                                <option value="1.0" {if $op.value == 1.0}selected="selected"{/if}>1.0</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <select class="form-control" name="{$op.changefreq_name|escape:'html':'UTF-8'}">
                                <option value="always" {if $op.changefreq_value == 'always'}selected="selected"{/if}>{l s='Always' mod='ets_seo'}</option>
                                <option value="hourly" {if $op.changefreq_value == 'hourly'}selected="selected"{/if}>{l s='Hourly' mod='ets_seo'}</option>
                                <option value="daily" {if $op.changefreq_value == 'daily'}selected="selected"{/if}>{l s='Daily' mod='ets_seo'}</option>
                                <option value="weekly" {if $op.changefreq_value == 'weekly' || !$op.changefreq_value}selected="selected"{/if}>{l s='Weekly' mod='ets_seo'}</option>
                                <option value="monthly" {if $op.changefreq_value == 'monthly'}selected="selected"{/if}>{l s='Monthly' mod='ets_seo'}</option>
                                <option value="yearly" {if $op.changefreq_value == 'yearly'}selected="selected"{/if}>{l s='Yearly' mod='ets_seo'}</option>
                                <option value="never" {if $op.changefreq_value == 'never'}selected="selected"{/if}>{l s='Never' mod='ets_seo'}</option>
                            </select>
                        </div>
                    </div>
                {/foreach}

            </div>
        </div>
    {elseif $key == 'ETS_SEO_SITEMAP_OPTION'}
        <div class="col-lg-9">
            <p class="checkbox">
                {strip}
                    <label class="col-lg-3" for="ETS_SEO_SITEMAP_OPTION_all_on">
                        <input type="checkbox" name="ETS_SEO_SITEMAP_OPTION[]" id="ETS_SEO_SITEMAP_OPTION_all_on" class="js-ets-seo-checkall"
                               value="all" {if count($ETS_SEO_SITEMAP_OPTION) == count($ets_seo_priority_options)}checked="checked"{/if}/>
                        {'All'|escape:'html':'UTF-8'}
                    </label>
                {/strip}
            </p>
            {foreach $ets_seo_priority_options as $k=>$op}
                <p class="checkbox">
                    {strip}
                        <label class="col-lg-3" for="ETS_SEO_SITEMAP_OPTION_{$k|escape:'html':'UTF-8'}_on">
                            <input type="checkbox" name="ETS_SEO_SITEMAP_OPTION[]" id="ETS_SEO_SITEMAP_OPTION_{$k|escape:'html':'UTF-8'}_on"
                                   value="{$k|escape:'html':'UTF-8'}" {if in_array($k, $ETS_SEO_SITEMAP_OPTION)}checked="checked"{/if}/>
                            {$op.label|escape:'html':'UTF-8'}
                        </label>
                    {/strip}
                </p>
            {/foreach}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}