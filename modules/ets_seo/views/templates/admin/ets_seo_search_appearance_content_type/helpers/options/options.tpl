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
{block name="defaultOptions"}
    <div class="alert alert-info">{l s='If not defined specifically for each item (product, category, page, etc.) in Edit pages, the meta title and meta description will be displayed according to the meta templates here. In the case meta template is empty, meta title and meta description will display as the default of PrestaShop' mod='ets_seo'}</div>
    {$smarty.block.parent}
{/block}
{block name="input"}
    {if $field['type'] == 'textLang'}
        <div class="col-lg-9">
            <div class="row">
            {foreach $field['languages'] AS $id_lang => $value}
                {if $field['languages']|count > 1}
                <div class="translatable-field lang-{$id_lang|escape:'html':'UTF-8'}" {if $id_lang != $current_id_lang}style="display:none;"{/if}>
                    <div class="col-lg-9">
                {else}
                <div class="col-lg-12">
                {/if}
                        <input type="text"
                            name="{$key|escape:'html':'UTF-8'}_{$id_lang|escape:'html':'UTF-8'}"
                            value="{$value|escape:'html':'UTF-8'}"
                            placeholder="{if isset($field['placeholder']) && $field['placeholder']}{$field['placeholder']|escape:'UTF-8'}{/if}"
                            {if isset($input.class)}class="{$input.class|escape:'html':'UTF-8'}"{/if}
                        />
                    {if isset($categoryData['short_code']['title'])}
                        {$categoryData['short_code']['title'] nofilter}
                    {/if}
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
                </div>
                {else}
                </div>
                {/if}
            {/foreach}
            </div>
        </div>
    {elseif $field['type'] == 'textareaLang'}
        <div class="col-lg-9">
            {foreach $field['languages'] AS $id_lang => $value}
                <div class="row translatable-field lang-{$id_lang|escape:'html':'UTF-8'}" {if $id_lang != $current_id_lang}style="display:none;"{/if}>
                {if $field['languages']|count > 1}
                    <div id="{$key|escape:'html':'UTF-8'}_{$id_lang|escape:'html':'UTF-8'}" class="col-lg-9" >
                {else}
                    <div class="col-lg-12">
                {/if}
                        <textarea class="{if isset($field['autoload_rte']) && $field['autoload_rte']}rte autoload_rte{else}textarea-autosize{/if}" name="{$key|escape:'html':'UTF-8'}_{$id_lang|escape:'html':'UTF-8'}" placeholder="{if isset($field['placeholder']) && $field['placeholder']}{$field['placeholder']|escape:'UTF-8'}{/if}">{$value|replace:"\r\n":"\n"}</textarea>
                        {if isset($categoryData['short_code']['desc'])}
                            {$categoryData['short_code']['desc'] nofilter}
                        {/if}
                    </div>
                {if $field['languages']|count > 1}
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
    {if isset($field['desc']) && !empty($field['desc'])}
        <div class="col-lg-9 col-lg-offset-3">
            <div class="help-block">
                {if is_array($field['desc'])}
                    {foreach $field['desc'] as $p}
                        {if is_array($p)}
                            <span id="{$p.id|escape:'UTF-8'}">{$p.text|escape:'UTF-8'}</span><br />
                        {else}
                            {$p|escape:'UTF-8'}<br />
                        {/if}
                    {/foreach}
                {else}
                    {$field['desc']|escape:'UTF-8'}
                {/if}
            </div>
        </div>
    {/if}
{/block}