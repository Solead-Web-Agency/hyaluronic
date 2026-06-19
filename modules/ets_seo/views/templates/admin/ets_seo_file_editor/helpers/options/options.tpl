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
    {if isset($table_bk) && $table_bk == $table}{capture name='table_count'}{counter name='table_count'}{/capture}{/if}
    {assign var='table_bk' value=$table scope='root'}
    <form action="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}" id="{if $table == null}configuration_form{else}{$table nofilter}_form{/if}{if isset($smarty.capture.table_count) && $smarty.capture.table_count}_{$smarty.capture.table_count|intval}{/if}" method="post" enctype="multipart/form-data" class="form-horizontal">
        {foreach $option_list AS $category => $categoryData}
            {if isset($categoryData['top'])}{$categoryData['top'] nofilter}{/if}
            <div class="panel {if isset($categoryData['class'])}{$categoryData['class'] nofilter}{/if}" id="{$table nofilter}_fieldset_{$category nofilter}">
                {* Options category title *}
                <div class="panel-heading">
                    <i class="{if isset($categoryData['icon'])}{$categoryData['icon'] nofilter}{else}icon-cogs{/if}"></i>
                    {if isset($categoryData['title'])}{$categoryData['title'] nofilter}{else}{l s='Options' mod='ets_seo'}{/if}
                </div>

                {* Category description *}

                {if (isset($categoryData['description']) && $categoryData['description'])}
                    <div class="alert alert-info">{$categoryData['description'] nofilter}</div>
                {/if}
                {if (isset($ets_seo_robots_warning) && $ets_seo_robots_warning)}
                    <div class="alert alert-warning">{$ets_seo_robots_warning|escape:'UTF-8'}</div>
                {/if}
                {* Category info *}
                {if (isset($categoryData['info']) && $categoryData['info'])}
                    <div>{$categoryData['info'] nofilter}</div>
                {/if}

                {if !$categoryData['hide_multishop_checkbox'] && $use_multishop}
                    <div class="well clearfix">
                        <label class="control-label col-lg-3">
                            <i class="icon-sitemap"></i> {l s='Multistore' mod='ets_seo'}
                        </label>
                        <div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						{strip}
                            <input type="radio" name="{$table nofilter}_multishop_{$category nofilter}" id="{$table nofilter}_multishop_{$category nofilter}_on" value="1" onclick="toggleAllMultishopDefaultValue($('#{$table nofilter}_fieldset_{$category nofilter}'), true)"/>
                            <label for="{$table nofilter}_multishop_{$category nofilter}_on">
							{l s='Yes' mod='ets_seo'}
						</label>
                            <input type="radio" name="{$table nofilter}_multishop_{$category nofilter}" id="{$table nofilter}_multishop_{$category nofilter}_off" value="0" checked="checked" onclick="toggleAllMultishopDefaultValue($('#{$table nofilter}_fieldset_{$category nofilter}'), false)"/>
                            <label for="{$table nofilter}_multishop_{$category nofilter}_off">
							{l s='No' mod='ets_seo'}
						</label>
                        {/strip}
						<a class="slide-button btn"></a>
					</span>
                            <div class="row">
                                <div class="col-lg-12">
                                    <p class="help-block">
                                        <strong>{l s='Check / Uncheck all'  mod='ets_seo'}</strong><br />
                                        {l s='You are editing this page for a specific shop or group. Click "Yes" to check all fields, "No" to uncheck all.' mod='ets_seo'}<br />
                                        {l s='If you check a field, change its value, and save, the multistore behavior will not apply to this shop (or group), for this particular parameter.' mod='ets_seo'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="form-wrapper">
                    {foreach $categoryData['fields'] AS $key => $field}
                        {if $field['type'] == 'hidden'}
                            <input type="hidden" name="{$key nofilter}" value="{$field['value'] nofilter}" />
                        {else}
                            <div class="form-group{if isset($field.form_group_class)} {$field.form_group_class nofilter}{/if}"{if isset($tabs) && isset($field.tab)} data-tab-id="{$field.tab nofilter}"{/if}>
                                <div id="conf_id_{$key nofilter}"{if $field['is_invisible']} class="isInvisible"{/if}>
                                    {block name="label"}
                                        {if isset($field['title']) && isset($field['hint'])}
                                            <label class="control-label col-lg-3{if isset($field['required']) && $field['required'] && $field['type'] != 'radio'} required{/if}">
                                                {if !$categoryData['hide_multishop_checkbox'] && $field['multishop_default'] && empty($field['no_multishop_checkbox'])}
                                                    <input type="checkbox" name="multishopOverrideOption[{$key nofilter}]" value="1"{if !$field['is_disabled']} checked="checked"{/if} onclick="toggleMultishopDefaultValue(this, '{$key nofilter}')"/>
                                                {/if}
                                                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="
												{if is_array($field['hint'])}
													{foreach $field['hint'] as $hint}
														{if is_array($hint)}
															{$hint.text|escape:'html':'UTF-8'}
														{else}
															{$hint|escape:'html':'UTF-8'}
														{/if}
													{/foreach}
												{else}
													{$field['hint'] nofilter}
												{/if}
											" data-html="true">
												{$field['title'] nofilter}
											</span>
                                            </label>
                                        {elseif isset($field['title'])}
                                            <label class="control-label col-lg-3">
                                                {if !$categoryData['hide_multishop_checkbox'] && $field['multishop_default'] && empty($field['no_multishop_checkbox'])}
                                                    <input type="checkbox" name="multishopOverrideOption[{$key nofilter}]" value="1"{if !$field['is_disabled']} checked="checked"{/if} onclick="checkMultishopDefaultValue(this, '{$key nofilter}')" />
                                                {/if}
                                                {$field['title'] nofilter}
                                            </label>
                                        {/if}
                                    {/block}
                                    {block name="field"}

                                        {block name="input"}
                                            {if $key == 'ETS_SEO_ROBOT_TXT'}
                                                <div class="col-lg-9">
                                                    <textarea class="form-control" rows="20" name="ETS_SEO_ROBOT_TXT">{$ets_seo_robot|escape:'html':'UTF-8'}</textarea>
                                                </div>
                                            {else}
                                                {if $field['type'] == 'select'}
                                                    <div class="col-lg-9">
                                                        {if $field['list']}
                                                            <select class="form-control fixed-width-xxl {if isset($field['class'])}{$field['class'] nofilter}{/if}" name="{$key nofilter}"{if isset($field['js'])} onchange="{$field['js'] nofilter}"{/if} id="{$key nofilter}" {if isset($field['size'])} size="{$field['size'] nofilter}"{/if}>
                                                                {foreach $field['list'] AS $k => $option}
                                                                    <option value="{$option[$field['identifier']] nofilter}"{if $field['value'] == $option[$field['identifier']]} selected="selected"{/if}>{$option['name'] nofilter}</option>
                                                                {/foreach}
                                                            </select>
                                                        {elseif isset($input.empty_message)}
                                                            {$input.empty_message nofilter}
                                                        {/if}
                                                    </div>
                                                {elseif $field['type'] == 'bool'}
                                                    <div class="col-lg-9">
											<span class="switch prestashop-switch fixed-width-lg">
												{strip}
                                                    <input type="radio" name="{$key nofilter}" id="{$key nofilter}_on" value="1" {if $field['value']} checked="checked"{/if}{if isset($field['js']['on'])} {$field['js']['on'] nofilter}{/if}{if isset($field['disabled']) && (bool)$field['disabled']} disabled="disabled"{/if}/>
                                                    <label for="{$key nofilter}_on" class="radioCheck">
													{l s='Yes' mod='ets_seo'}
												</label>
                                                    <input type="radio" name="{$key nofilter}" id="{$key nofilter}_off" value="0" {if !$field['value']} checked="checked"{/if}{if isset($field['js']['off'])} {$field['js']['off'] nofilter}{/if}{if isset($field['disabled']) && (bool)$field['disabled']} disabled="disabled"{/if}/>
                                                    <label for="{$key nofilter}_off" class="radioCheck">
													{l s='No' mod='ets_seo'}
												</label>
                                                {/strip}
												<a class="slide-button btn"></a>
											</span>
                                                    </div>
                                                {elseif $field['type'] == 'radio'}
                                                    <div class="col-lg-9">
                                                        {foreach $field['choices'] AS $k => $v}
                                                            <p class="radio">
                                                                {strip}
                                                                    <label for="{$key nofilter}_{$k nofilter}">
                                                                        <input type="radio" name="{$key nofilter}" id="{$key nofilter}_{$k nofilter}" value="{$k nofilter}"{if $k == $field['value']} checked="checked"{/if}{if isset($field['js'][$k])} {$field['js'][$k] nofilter}{/if}/>
                                                                        {$v nofilter}
                                                                    </label>
                                                                {/strip}
                                                            </p>
                                                        {/foreach}
                                                    </div>
                                                {elseif $field['type'] == 'checkbox'}
                                                    <div class="col-lg-9">
                                                        {foreach $field['choices'] AS $k => $v}
                                                            <p class="checkbox">
                                                                {strip}
                                                                    <label class="col-lg-3" for="{$key nofilter}{$k nofilter}_on">
                                                                        <input type="checkbox" name="{$key nofilter}" id="{$key nofilter}{$k nofilter}_on" value="{$k|intval}"{if $k == $field['value']} checked="checked"{/if}{if isset($field['js'][$k])} {$field['js'][$k] nofilter}{/if}/>
                                                                        {$v nofilter}
                                                                    </label>
                                                                {/strip}
                                                            </p>
                                                        {/foreach}
                                                    </div>
                                                {elseif $field['type'] == 'text'}
                                                    <div class="col-lg-9">{if isset($field['suffix'])}<div class="input-group{if isset($field.class)} {$field.class nofilter}{/if}">{/if}
                                                            <input class="form-control {if isset($field['class'])}{$field['class'] nofilter}{/if}" type="{$field['type'] nofilter}"{if isset($field['id'])} id="{$field['id'] nofilter}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key nofilter}" value="{if isset($field['no_escape']) && $field['no_escape']}{$field['value']|escape:'UTF-8'}{else}{$field['value']|escape:'html':'UTF-8'}{/if}" {if isset($field['autocomplete']) && !$field['autocomplete']}autocomplete="off"{/if}/>
                                                            {if isset($field['suffix'])}
                                                                <span class="input-group-addon">
												{$field['suffix'] nofilter}
											</span>
                                                            {/if}
                                                            {if isset($field['suffix'])}</div>{/if}
                                                    </div>
                                                {elseif $field['type'] == 'password'}
                                                    <div class="col-lg-9">{if isset($field['suffix'])}<div class="input-group{if isset($field.class)} {$field.class nofilter}{/if}">{/if}
                                                            <input type="{$field['type'] nofilter}"{if isset($field['id'])} id="{$field['id'] nofilter}"{/if} size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key nofilter}" value=""{if isset($field['autocomplete']) && !$field['autocomplete']} autocomplete="off"{/if} />
                                                            {if isset($field['suffix'])}
                                                                <span class="input-group-addon">
												{$field['suffix'] nofilter}
											</span>
                                                            {/if}
                                                            {if isset($field['suffix'])}</div>{/if}
                                                    </div>
                                                {elseif $field['type'] == 'textarea'}
                                                    <div class="col-lg-9">
                                                        <textarea class="{if isset($field['autoload_rte']) && $field['autoload_rte']}rte autoload_rte{else}textarea-autosize{/if}" name={$key nofilter}{if isset({$field['cols']|escape:'html':'UTF-8'})} cols="{$field['cols'] nofilter}"{/if}{if isset({$field['rows']|escape:'html':'UTF-8'})} rows="{$field['rows']|escape:'html':'UTF-8'}"{/if}">{$field['value']|escape:'html':'UTF-8'}</textarea>
                                                    </div>
                                                {elseif $field['type'] == 'file'}
                                                    <div class="col-lg-9">{$field['file'] nofilter}</div>
                                                {elseif $field['type'] == 'color'}
                                                    <div class="col-lg-2">
                                                        <div class="input-group">
                                                            <input type="color" size="{$field['size'] nofilter}" data-hex="true" {if isset($input.class)}class="{$field['class'] nofilter}" {else}class="color mColorPickerInput"{/if} name="{$field['name'] nofilter}" class="{if isset($field['class'])}{$field['class'] nofilter}{/if}" value="{$field['value']|escape:'html':'UTF-8'}" />
                                                        </div>
                                                    </div>
                                                {elseif $field['type'] == 'price'}
                                                    <div class="col-lg-9">
                                                        <div class="input-group fixed-width-lg">
                                                            <span class="input-group-addon">{$currency_left_sign nofilter} {l s='(tax excl.)' mod='ets_seo'}</span>
                                                            <input type="text" size="{if isset($field['size'])}{$field['size']|intval}{else}5{/if}" name="{$key nofilter}" value="{$field['value']|escape:'html':'UTF-8'}" />
                                                        </div>
                                                    </div>
                                                {elseif $field['type'] == 'textLang' || $field['type'] == 'textareaLang' || $field['type'] == 'selectLang'}
                                                    {if $field['type'] == 'textLang'}
                                                        <div class="col-lg-9">
                                                            <div class="row">
                                                                {foreach $field['languages'] AS $id_lang => $value}
                                                                    {if $field['languages']|count > 1}
                                                                        <div class="translatable-field lang-{$id_lang nofilter}" {if $id_lang != $current_id_lang}style="display:none;"{/if}>
                                                                        <div class="col-lg-9">
                                                                    {else}
                                                                        <div class="col-lg-12">
                                                                    {/if}
                                                                    <input type="text"
                                                                           name="{$key nofilter}_{$id_lang nofilter}"
                                                                           value="{$value|escape:'html':'UTF-8'}"
                                                                           {if isset($input.class)}class="{$input.class nofilter}"{/if}
                                                                    />
                                                                    {if $field['languages']|count > 1}
                                                                        </div>
                                                                        <div class="col-lg-2">
                                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                                                {foreach $languages as $language}
                                                                                    {if $language.id_lang == $id_lang}{$language.iso_code nofilter}{/if}
                                                                                {/foreach}
                                                                                <span class="caret"></span>
                                                                            </button>
                                                                            <ul class="dropdown-menu">
                                                                                {foreach $languages as $language}
                                                                                    <li>
                                                                                        <a href="javascript:hideOtherLanguage({$language.id_lang nofilter});">{$language.name nofilter}</a>
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
                                                                <div class="row translatable-field lang-{$id_lang nofilter}" {if $id_lang != $current_id_lang}style="display:none;"{/if}>
                                                                    <div id="{$key nofilter}_{$id_lang nofilter}" class="col-lg-9" >
                                                                        <textarea class="{if isset($field['autoload_rte']) && $field['autoload_rte']}rte autoload_rte{else}textarea-autosize{/if}" name="{$key nofilter}_{$id_lang nofilter}">{$value|replace:'\r\n':"\n"|escape:'UTF-8'}</textarea>
                                                                    </div>
                                                                    <div class="col-lg-2">
                                                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                                            {foreach $languages as $language}
                                                                                {if $language.id_lang == $id_lang}{$language.iso_code nofilter}{/if}
                                                                            {/foreach}
                                                                            <span class="caret"></span>
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            {foreach $languages as $language}
                                                                                <li>
                                                                                    <a href="javascript:hideOtherLanguage({$language.id_lang nofilter});">{$language.name nofilter}</a>
                                                                                </li>
                                                                            {/foreach}
                                                                        </ul>
                                                                    </div>

                                                                </div>
                                                            {/foreach}
                                                            <script type="text/javascript">
                                                                $(document).ready(function() {
                                                                    $(".textarea-autosize").autosize();
                                                                });
                                                            </script>
                                                        </div>
                                                    {elseif $field['type'] == 'selectLang'}
                                                        {foreach $languages as $language}
                                                            <div id="{$key nofilter}_{$language.id_lang nofilter}" style="display: {if $language.id_lang == $current_id_lang}block{else}none{/if};" class="col-lg-9">
                                                                <select name="{$key nofilter}_{$language.iso_code|upper|escape:'html':'UTF-8'}">
                                                                    {foreach $field['list'] AS $k => $v}
                                                                        <option value="{if isset($v.cast)}{$v.cast[$v[$field.identifier]] nofilter}{else}{$v[$field.identifier] nofilter}{/if}"
                                                                                {if $field['value'][$language.id_lang] == $v['name']} selected="selected"{/if}>
                                                                            {$v['name'] nofilter}
                                                                        </option>
                                                                    {/foreach}
                                                                </select>
                                                            </div>
                                                        {/foreach}
                                                    {/if}
                                                {/if}
                                                {if isset($field['desc']) && !empty($field['desc'])}
                                                    <div class="col-lg-9 col-lg-offset-3">
                                                        <div class="help-block">
                                                            {if is_array($field['desc'])}
                                                                {foreach $field['desc'] as $p}
                                                                    {if is_array($p)}
                                                                        <span id="{$p.id nofilter}">{$p.text nofilter}</span><br />
                                                                    {else}
                                                                        {$p nofilter}<br />
                                                                    {/if}
                                                                {/foreach}
                                                            {else}
                                                                {$field['desc'] nofilter}
                                                            {/if}
                                                        </div>
                                                    </div>
                                                {/if}
                                            {/if}
                                        {/block}{* end block input *}
                                        {if $field['is_invisible']}
                                            <div class="col-lg-9 col-lg-offset-3">
                                                <p class="alert alert-warning row-margin-top">
                                                    {l s='You can\'t change the value of this configuration field in the context of this shop.' mod='ets_seo'}
                                                </p>
                                            </div>
                                        {/if}
                                    {/block}{* end block field *}
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </div><!-- /.form-wrapper -->

                {if isset($categoryData['bottom'])}{$categoryData['bottom'] nofilter}{/if}
                {block name="footer"}
                    {if isset($categoryData['submit']) || isset($categoryData['buttons'])}
                        <div class="panel-footer">
                            {if isset($categoryData['submit']) && !empty($categoryData['submit'])}
                                <button type="{if isset($categoryData['submit']['type'])}{$categoryData['submit']['type'] nofilter}{else}submit{/if}" {if isset($categoryData['submit']['id'])}id="{$categoryData['submit']['id'] nofilter}"{/if} class="btn btn-default pull-right" name="{if isset($categoryData['submit']['name'])}{$categoryData['submit']['name'] nofilter}{else}submitOptions{$table nofilter}{/if}"><i class="process-icon-{if isset($categoryData['submit']['imgclass'])}{$categoryData['submit']['imgclass'] nofilter}{else}save{/if}"></i> {$categoryData['submit']['title'] nofilter}</button>
                            {/if}
                            {if isset($categoryData['buttons'])}
                                {foreach from=$categoryData['buttons'] item=btn key=k}
                                    {if isset($btn.href) && trim($btn.href) != ''}
                                        <a href="{$btn.href|escape:'html':'UTF-8'}" {if isset($btn['id'])}id="{$btn['id'] nofilter}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class'] nofilter}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js nofilter}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon'] nofilter}" ></i> {/if}{$btn.title nofilter}</a>
                                    {else}
                                        <button type="{if isset($btn['type'])}{$btn['type'] nofilter}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id'] nofilter}"{/if} class="{if isset($btn['class'])}{$btn['class'] nofilter}{else}btn btn-default{/if}" name="{if isset($btn['name'])}{$btn['name'] nofilter}{else}submitOptions{$table nofilter}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js nofilter}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon'] nofilter}" ></i> {/if}{$btn.title nofilter}</button>
                                    {/if}
                                {/foreach}
                            {/if}
                        </div>
                    {/if}
                {/block}
            </div>
        {/foreach}
        {hook h='displayAdminOptions'}
        {if isset($name_controller)}
            {capture name=hookName assign=hookName}display{$name_controller|ucfirst|escape:'html':'UTF-8'}Options{/capture}
            {hook h=$hookName}
        {elseif isset($smarty.get.controller)}
            {capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|htmlentities|escape:'html':'UTF-8'}Options{/capture}
            {hook h=$hookName}
        {/if}
    </form>
{/block}
