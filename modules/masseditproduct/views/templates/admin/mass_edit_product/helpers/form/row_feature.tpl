{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    SeoSA <885588@bk.ru>
* @copyright 2012-2020 SeoSA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<!-- features row -->
<div class="row form-group">
    <span class="ps-switch prestashop-switch fixed-width-xl">
                {foreach [0,1] as $value}
                    <input type="radio" name="disabled[feature][{$feature.id_feature|intval}]" value="{if $value == 1}{$feature.id_feature}{else}{$value|escape:'quotes':'UTF-8'}{/if}" class="disable_option"
                            {if $value == 1} id="enable_feature_{$feature.id_feature|intval}"  checked="checked"{else} id="disabled_feature_{$feature.id_feature|intval}"  {/if}
                            {if $value == 0} data-feature="{$feature.id_feature|intval}" {/if} />
                    <label {if $value == 1} for="enable_feature_{$feature.id_feature|intval}" {else} for="disabled_feature_{$feature.id_feature|intval}" {/if}>
                        {if $value == 1}{l s='Disabled' mod='masseditproduct'}{else}{l s='Enabled' mod='masseditproduct'}{/if}
                    </label>
                {/foreach}
        <a class="slide-button"></a>
    </span>
    <div class="col-sm-2 clearfix">
        <label class="control-label">{$feature.name|escape:'htmlall':'UTF-8'}</label>
    </div>
    <div class="col-sm-2 clearfix">
        {if is_array($feature.values) && count($feature.values)}
            <select class="custom-select" onchange="$('[class^=custom_{$feature.id_feature|intval}]').val('');" id="feature_{$feature.id_feature|intval}_value" name="feature_{$feature.id_feature|intval}_value">
                <option value="0">-</option>
                {foreach from=$feature.values item=value}
                    <option value="{$value.id_feature_value|intval}">{$value.value|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        {else}
            <label class="control-label">-</label>
            <input type="hidden" name="feature_{$feature.id_feature|intval}_value" value="0">
        {/if}
    </div>
    <div class="col-sm-6 clearfix {if $smarty.const._PS_VERSION_ < 1.6}translatable{/if}">
        {foreach from=$languages key=k item=language}
            {if $languages|count > 1}
                <div class="translatable-field lang-{$language.id_lang|intval} lang_{$language.id_lang|intval}" {if $smarty.const._PS_VERSION_ < 1.6 && !$language.is_default}style="display: none;"{/if}>
            {/if}
            <textarea
                    class="custom_{$feature.id_feature|intval}_{$language.id_lang|intval} fixed-width-lg form-control float-left mr-2"
                    name="custom_{$feature.id_feature|intval}_{$language.id_lang|intval}"
                    cols="40"
                    rows="1"
                    onkeyup="if (isArrowKey(event)) return ;$('#feature_{$feature.id_feature|intval}_value').val(0);" ></textarea>
            {if $languages|count > 1}
                {if !($smarty.const._PS_VERSION_ < 1.6)}
                    <span class="btn-languages">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            {$language.iso_code|escape:'quotes':'UTF-8'}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            {foreach from=$languages item=language}
                                <li>
                                    <a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.iso_code|escape:'quotes':'UTF-8'}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </span>
                {/if}
                </div>
            {/if}
        {/foreach}
    </div>
</div>