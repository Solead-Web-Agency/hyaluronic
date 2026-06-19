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
    {if $key == 'ETS_SEO_RSS_OPTION'}
        <label class="control-label col-lg-3 required">
            {$field['title']|escape:'html':'UTF-8'}
        </label>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}
{block name="input"}
    {if $key == 'ETS_SEO_RSS_LINK'}
        <div class="col-lg-9">
            {if isset($ets_seo_multilang_activated) && $ets_seo_multilang_activated}
                <ul class="rss-link-list">
                {foreach $ets_seo_languages as $lang}
                    <li class="rss-link-item">
                        <a href="{$ets_seo_baseurl|escape:'html':'UTF-8'}{$lang.iso_code|escape:'html':'UTF-8'}/rss" target="_blank">
                            <img class="sitemap-lang-img" src="{$ets_seo_base_uri|escape:'quotes'}/img/l/{$lang.id_lang|escape:'html':'UTF-8'}.jpg">
                            {$ets_seo_baseurl|escape:'html':'UTF-8'}{$lang.iso_code|escape:'html':'UTF-8'}/rss
                        </a>
                    </li>
                {/foreach}
                <ul>
            {else}
                <ul class="rss-link-list">
                    <li class="rss-link-item">
                        <a href="{$ets_seo_baseurl|escape:'html':'UTF-8'}rss">
                            {$ets_seo_baseurl|escape:'html':'UTF-8'}rss
                        </a>
                    </li>
                </ul>
            {/if}
        </div>
    {elseif $key == 'ETS_SEO_RSS_OPTION'}
        <div class="col-lg-9">
            {foreach $ets_seo_rss_options as $k=>$op}
                <p class="checkbox">
                    {strip}
                        <label class="col-lg-3" for="ETS_SEO_RSS_OPTION_{$k|escape:'html':'UTF-8'}_on">
                            <input type="checkbox" name="ETS_SEO_RSS_OPTION[]" id="ETS_SEO_RSS_OPTION_{$k|escape:'html':'UTF-8'}_on"
                                   value="{$k|escape:'html':'UTF-8'}" {if in_array($k, $ETS_SEO_RSS_OPTION)}checked="checked"{/if}/>
                            {$op.label|escape:'html':'UTF-8'}
                        </label>
                    {/strip}
                </p>
            {/foreach}
        </div>
    {elseif $field['type'] == 'textareaLang'}
        <div class="col-lg-9">
            {foreach $field['languages'] AS $id_lang => $value}
                <div class="{if $field['languages']|count > 1}row{/if} translatable-field lang-{$id_lang|escape:'html':'UTF-8'}" {if $id_lang != $current_id_lang}style="display:none;"{/if}>
                    {if $field['languages']|count > 1}
                    <div id="{$key|escape:'html':'UTF-8'}_{$id_lang|escape:'html':'UTF-8'}" class="col-lg-9" >
                    {/if}
                        <textarea class="{if isset($field['autoload_rte']) && $field['autoload_rte']}rte autoload_rte{else}textarea-autosize{/if}" name="{$key|escape:'html':'UTF-8'}_{$id_lang|escape:'html':'UTF-8'}">{$value|replace:'\r\n':"\n"|escape:'UTF-8'}</textarea>

                    {if $field['languages']|count > 1}
                    </div>
                        <div class="col-lg-2">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                {foreach $languages as $language}
                                    {if $language.id_lang == $id_lang}{$language.iso_code|escape:'html':'UTF-8'}{/if}
                                {/foreach}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach $languages as $language}
                                    <li>
                                        <a href="javascript:hideOtherLanguage({$language.id_lang|escape:'html':'UTF-8'});">{$language.name|escape:'html':'UTF-8'}</a>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}


                </div>
            {/foreach}
            <script type="text/javascript">
                $(document).ready(function() {
                    $(".textarea-autosize").autosize();
                });
            </script>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}