{**
 * 2017-2022 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2022 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 *}
 
{extends file="helpers/form/form.tpl"}

{block name="defaultForm"}
    <div class="row">
        {block name="tabs"}
            {if isset($vertical_tabs)}
                <div class="vertical-tabs col-lg-2 col-md-3">
                    {foreach $vertical_tabs as $group_name => $group_tabs}
                        <div id="js-{$group_name|escape:'htmlall':'UTF-8'}-tabs" class="list-group js-tabs-list">
                            {foreach $group_tabs as $id_tab => $tab}
                                <a id="{$id_tab|escape:'htmlall':'UTF-8'}" class="js-tab-item list-group-item{if !isset($tab.link)} form-tab-item{/if}{if isset($tab.is_reachable) && !$tab.is_reachable} unreachable disabled{else} reachable{/if}{if isset($tab.is_complete) && $tab.is_complete} complete{/if}{if isset($tab.hidden) && $tab.hidden} hidden{/if}{if isset($tab.active) && $tab.active} active{/if}"
                                        {if isset($tab.link)}
                                            href="{$tab.link|escape:'htmlall':'UTF-8'}"
                                        {/if}
                                        {if isset($tab.target)}target="{$tab.target|escape:'htmlall':'UTF-8'}" {if $tab.target ==='_blank'}rel="noopener noreferrer"{/if}{/if}
                                        {if isset($tab.style)}style="{$tab.style|escape:'htmlall':'UTF-8'}"{/if}
                                >{if isset($tab.is_completed) && $tab.is_completed}<i class="icon-ok-sign"></i>{/if}{if isset($tab.icon)}<i class="{$tab.icon|escape:'htmlall':'UTF-8'}"></i> {/if}{if isset($tab.image)}<img src="{$tab.image|escape:'htmlall':'UTF-8'}" alt="{$tab.name|escape:'htmlall':'UTF-8'}" width="20px" /> {/if}{$tab.name|escape:'htmlall':'UTF-8'}</a>
                            {/foreach}
                        </div>
                    {/foreach}
                </div>
            {/if}
        {/block}
        <form id="{if isset($fields.form.form.id_form)}{$fields.form.form.id_form|escape:'htmlall':'UTF-8'}{else}{if $table == null}configuration_form{else}{$table|escape:'htmlall':'UTF-8'}_form{/if}{if isset($smarty.capture.table_count) && $smarty.capture.table_count}_{$smarty.capture.table_count|intval}{/if}{/if}" class="defaultForm form-horizontal{if isset($vertical_tabs) && $vertical_tabs|count} col-lg-10 col-md-9{/if}{if isset($name_controller) && $name_controller} {$name_controller|escape:'htmlall':'UTF-8'}{/if} advancedvatmanager_form"{if isset($current) && $current} action="{$current|escape:'htmlall':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'htmlall':'UTF-8'}{/if}"{/if} method="post" enctype="multipart/form-data"{if isset($style)} style="{$style|escape:'htmlall':'UTF-8'}"{/if} novalidate>
            {if $form_id}
                <input type="hidden" name="{$identifier|escape:'htmlall':'UTF-8'}" id="{$identifier|escape:'htmlall':'UTF-8'}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}" value="{$form_id|escape:'htmlall':'UTF-8'}" />
            {/if}
            {if !empty($submit_action)}
                <input type="hidden" name="{$submit_action|escape:'htmlall':'UTF-8'}" value="1" />
            {/if}

            {foreach $fields as $f => $fieldset}
                {block name="fieldset"}
                    {if isset($vertical_tabs) && isset($vertical_tabs.form[$f])}
                        <div id="js-tab-content-{$f|escape:'htmlall':'UTF-8'}" class="js-tab-content{if isset($vertical_tabs.form[$f].active) && $vertical_tabs.form[$f].active} active{else} hidden{/if}">
                    {/if}
                    {capture name='fieldset_name'}{counter name='fieldset_name'}{/capture}
                    <div class="panel" id="fieldset_{$f|escape:'htmlall':'UTF-8'}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                        {foreach $fieldset.form as $key => $field}
                            {if $key == 'legend'}
                                {block name="legend"}
                                    <div class="panel-heading">
                                        {if isset($field.image) && isset($field.title)}<img src="{$field.image|escape:'htmlall':'UTF-8'}" alt="{$field.title|escape:'htmlall':'UTF-8'}" />{/if}
                                        {if isset($field.icon)}<i class="{$field.icon|escape:'htmlall':'UTF-8'}"></i> {/if}
                                        {$field.title|escape:'htmlall':'UTF-8'}
                                    </div>
                                {/block}
                            {elseif $key == 'description' && $field}
                                <div class="alert alert-info">{$field|escape:'quotes':'UTF-8'}</div>
                            {elseif $key == 'warning' && $field}
                                <div class="alert alert-warning">{$field|escape:'quotes':'UTF-8'}</div>
                            {elseif $key == 'success' && $field}
                                <div class="alert alert-success">{$field|escape:'quotes':'UTF-8'}</div>
                            {elseif $key == 'error' && $field}
                                <div class="alert alert-danger">{$field|escape:'quotes':'UTF-8'}</div>
                            {elseif $key == 'input'}
                                <div class="form-wrapper">
                                    {foreach $field as $input}
                                        {block name="input_row"}
                                            <div class="form-group{if isset($input.form_group_class)} {$input.form_group_class|escape:'htmlall':'UTF-8'}{/if}{if $input.type == 'hidden'} hide{/if}"{if $input.name == 'id_state'} id="contains_states"{if !$contains_states} style="display:none;"{/if}{/if}>
                                                {if $input.type == 'hidden'}
                                                    <input type="hidden" name="{$input.name|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}" value="{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}" />
                                                {else}
                                                    {block name="label"}
                                                        {if isset($input.label)}
                                                            <label class="control-label col-lg-3{if isset($input.required) && $input.required && $input.type != 'radio'} required{/if}">
                                                                {if isset($input.hint)}
                                                                <span class="label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="
                                                                    {if is_array($input.hint)}
                                                                        {foreach $input.hint as $hint}
                                                                            {if is_array($hint)}
                                                                                {$hint.text|escape:'htmlall':'UTF-8'}
                                                                            {else}
                                                                                {$hint|escape:'htmlall':'UTF-8'}
                                                                            {/if}
                                                                        {/foreach}
                                                                    {else}
                                                                        {$input.hint|escape:'htmlall':'UTF-8'}
                                                                    {/if}">
                                                                {/if}
                                                                    {$input.label|escape:'htmlall':'UTF-8'}
                                                                    {if isset($input.hint)}
                                                                    </span>
                                                                {/if}
                                                            </label>
                                                        {/if}
                                                    {/block}

                                                    {block name="field"}
                                                        <div class="col-lg-{if isset($input.col)}{$input.col|intval}{else}9{/if}{if !isset($input.label)} col-lg-offset-3{/if}">
                                                            {block name="input"}
								{if $input.type == 'text' || $input.type == 'tags'}
									{if isset($input.lang) AND $input.lang}
									{if $languages|count > 1}
									<div class="form-group">
									{/if}
									{foreach $languages as $language}
										{assign var='value_text' value=$fields_value[$input.name][$language.id_lang]}
										{if $languages|count > 1}
										<div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
											<div class="col-lg-9">
										{/if}
											{if $input.type == 'tags'}
													{literal}
													<script type="text/javascript">
														$().ready(function () {
															var input_id = '{/literal}{if isset($input.id)}{$input.id|escape:"htmlall":"UTF-8"}{else}{$input.name|escape:"htmlall":"UTF-8"}{/if}{literal}';
															$('#'+input_id).tagify({delimiters: [13,44], addTagPrompt: '{/literal}{l s='Add element' js=1}{literal}'});
															$({/literal}'#{$table|escape:'htmlall':'UTF-8'}{literal}_form').submit( function() {
																$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
															});
														});
													</script>
												{/literal}
												{/if}
												{if isset($input.maxchar) || isset($input.prefix) || isset($input.suffix)}
												<div class="input-group{if isset($input.class)} {$input.class|escape:'htmlall':'UTF-8'}{/if}">
												{/if}
												{if isset($input.maxchar) && $input.maxchar}
												<span id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{/if}_counter" class="input-group-addon">
													<span class="text-count-down">{$input.maxchar|intval}</span>
												</span>
												{/if}
												{if isset($input.prefix)}
													<span class="input-group-addon">
													  {$input.prefix|escape:'htmlall':'UTF-8'}
													</span>
													{/if}
												<input type="text"
													id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{/if}"
													name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}"
													class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}{if $input.type == 'tags'} tagify{/if}"
													value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'html':'UTF-8'}{else}{$value_text|escape:'html':'UTF-8'}{/if}"
													onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();"
													{if isset($input.size)} size="{$input.size|escape:'htmlall':'UTF-8'}"{/if}
													{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}
													{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}
													{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
													{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
													{if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
													{if isset($input.required) && $input.required} required="required" {/if}
													{if isset($input.placeholder) && $input.placeholder} placeholder="{$input.placeholder|escape:'htmlall':'UTF-8'}"{/if} />
													{if isset($input.suffix)}
													<span class="input-group-addon">
													  {$input.suffix|escape:'htmlall':'UTF-8'}
													</span>
													{/if}
												{if isset($input.maxchar) || isset($input.prefix) || isset($input.suffix)}
												</div>
												{/if}
										{if $languages|count > 1}
											</div>
											<div class="col-lg-2">
												<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
													{$language.iso_code|escape:'htmlall':'UTF-8'}
													<i class="icon-caret-down"></i>
												</button>
												<ul class="dropdown-menu">
													{foreach from=$languages item=language}
													<li><a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
													{/foreach}
												</ul>
											</div>
										</div>
										{/if}
									{/foreach}
									{if isset($input.maxchar) && $input.maxchar}
									<script type="text/javascript">
									$(document).ready(function(){
									{foreach from=$languages item=language}
										countDown($("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{/if}"), $("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{/if}_counter"));
									{/foreach}
									});
									</script>
									{/if}
									{if $languages|count > 1}
									</div>
									{/if}
									{else}
									{if $input.type == 'tags'}
										{literal}
											<script type="text/javascript">
												$().ready(function () {
													var input_id = '{/literal}{if isset($input.id)}{$input.id|escape:"htmlall":"UTF-8"}{else}{$input.name|escape:"htmlall":"UTF-8"}{/if}{literal}';
													$('#'+input_id).tagify({delimiters: [13,44], addTagPrompt: '{/literal}{l s='Add element' js=1}{literal}'});
													$({/literal}'#{$table|escape:'htmlall':'UTF-8'}{literal}_form').submit( function() {
														$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
													});
												});
											</script>
										{/literal}
									{/if}
										{assign var='value_text' value=$fields_value[$input.name]}
										{if isset($input.maxchar) || isset($input.prefix) || isset($input.suffix)}
										<div class="input-group{if isset($input.class)} {$input.class|escape:'htmlall':'UTF-8'}{/if}">
										{/if}
										{if isset($input.maxchar) && $input.maxchar}
										<span id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_counter" class="input-group-addon"><span class="text-count-down">{$input.maxchar|intval}</span></span>
										{/if}
										{if isset($input.prefix)}
										<span class="input-group-addon">
										  {$input.prefix|escape:'htmlall':'UTF-8'}
										</span>
										{/if}
										<input type="text"
											name="{$input.name|escape:'htmlall':'UTF-8'}"
											id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
											value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'html':'UTF-8'}{else}{$value_text|escape:'html':'UTF-8'}{/if}"
											class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}{if $input.type == 'tags'} tagify{/if}"
											{if isset($input.size)} size="{$input.size|escape:'htmlall':'UTF-8'}"{/if}
											{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}
											{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}
											{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
											{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
											{if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
											{if isset($input.required) && $input.required } required="required" {/if}
											{if isset($input.placeholder) && $input.placeholder } placeholder="{$input.placeholder|escape:'htmlall':'UTF-8'}"{/if}
											/>
										{if isset($input.suffix)}
										<span class="input-group-addon">
										  {$input.suffix|escape:'htmlall':'UTF-8'}
										</span>
										{/if}

										{if isset($input.maxchar) || isset($input.prefix) || isset($input.suffix)}
										</div>
										{/if}
										{if isset($input.maxchar) && $input.maxchar}
										<script type="text/javascript">
										$(document).ready(function(){
											countDown($("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"), $("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_counter"));
										});
										</script>
										{/if}
									{/if}
                                                                {elseif $input.type == 'range'}
                                                                    {assign var='value_range' value=$fields_value[$input.name]}
                                                                    <div class="input-group{if isset($input.class)} {$input.class|escape:'htmlall':'UTF-8'}{/if}">
                                                                        <input type="range"
                                                                               name="{$input.name|escape:'htmlall':'UTF-8'}"
                                                                               id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
                                                                               class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"
                                                                               value="{$value_range|intval}"
                                                                                {if isset($input.min)} min="{$input.min|intval}"{/if}
                                                                                {if isset($input.max)} max="{$input.max|intval}"{/if}
                                                                                {if isset($input.step)} step="{$input.step|intval}"{/if}
                                                                                {if isset($input.disabled)} disabled{/if}
                                                                        />
                                                                    </div>
                                                                {elseif $input.type == 'inputwithselect' && isset($input.select)}
                                                                    {assign var='value_text' value=$fields_value[$input.name]}
                                                                    {assign var='value_selected' value=''}
                                                                    {assign var='name_selected' value =''}

                                                                    {if isset($fields_value[$input.select.name])}
                                                                        {assign var='value_selected' value=$fields_value[$input.select.name]}
                                                                    {elseif isset($input.select.default_id)}
                                                                        {assign var='value_selected' value=$input.select.default_id}
                                                                    {/if}

                                                                    {if isset($input.select.options.query[$value_selected][$input.select.options.name])}
                                                                        {assign var='name_selected' value=$input.select.options.query[$value_selected][$input.select.options.name]}
                                                                    {/if}
                                                                    <div class="input-group{if isset($input.class)} {$input.class|escape:'htmlall':'UTF-8'}{/if}">

                                                                        {if !isset($input.select.after) || !$input.select.after}
                                                                            <div class="input-group-btn">

                                                                                    <span class="js-inputwithselect-label">{if $name_selected}{$name_selected|escape:'htmlall':'UTF-8'}{else}{l s='Select' mod='advancedvatmanager'}{/if}</span>
                                                                                    <span class="caret"></span>
                                                                                    <input type="hidden"
                                                                                           name="{$input.select.name|escape:'htmlall':'UTF-8'}"
                                                                                           id="{if isset($input.select.id)}{$input.select.id|escape:'htmlall':'UTF-8'}{else}{$input.select.name|escape:'htmlall':'UTF-8'}{/if}"
                                                                                           value="{$value_selected|escape:'htmlall':'UTF-8'}"
                                                                                    >
                                                                                </button>
                                                                                <ul class="dropdown-menu js-inputwithselect-menu">
                                                                                    {foreach $input.select.options.query as $option}
                                                                                        {if $option === "-"}
                                                                                            <li role="separator" class="divider"></li>
                                                                                        {else}
                                                                                            <li class="js-inputwithselect-option"
                                                                                                data-value="{$option[$input.select.options.id]|intval}"
                                                                                                    {if $option[$input.select.options.id] == $value_selected}
                                                                                                        data-selected="true"
                                                                                                    {/if}
                                                                                            >
                                                                                                <a href="#">{$option[$input.select.options.name]|escape:'htmlall':'UTF-8'}</a>
                                                                                            </li>
                                                                                        {/if}
                                                                                    {/foreach}
                                                                                </ul>
                                                                            </div>
                                                                        {/if}
                                                                        {if isset($input.maxchar) && $input.maxchar}
                                                                            <span id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_counter" class="input-group-addon"><span class="text-count-down">{$input.maxchar|intval}</span></span>
                                                                        {/if}
                                                                        {if isset($input.prefix)}
                                                                            <span class="input-group-addon">{$input.prefix|escape:'quotes':'UTF-8'}</span>
                                                                        {/if}
                                                                        <input {if isset($input.input_type) && $input.input_type === 'number'}type="number"{else}type="text"{/if}
                                                                               name="{$input.name|escape:'htmlall':'UTF-8'}"
                                                                               id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
                                                                               value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'htmlall':'UTF-8'}{else}{$value_text|escape:'htmlall':'UTF-8'}{/if}"
                                                                               class="js-inputwithselect-input form-control{if isset($input.class)} {$input.class|escape:'htmlall':'UTF-8'}{/if}"
                                                                                {if isset($input.step)} step="{$input.step|intval}"{/if}
                                                                                {if isset($input.min)} min="{$input.min|intval}"{/if}
                                                                                {if isset($input.max)} max="{$input.max|intval}"{/if}
                                                                                {if isset($input.size)} size="{$input.size|intval}"{/if}
                                                                                {if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}
                                                                                {if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}
                                                                                {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
                                                                                {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                                                                                {if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
                                                                                {if isset($input.required) && $input.required } required="required" {/if}
                                                                                {if isset($input.placeholder) && $input.placeholder } placeholder="{$input.placeholder|escape:'htmlall':'UTF-8'}"{/if}
                                                                        />
                                                                        {if isset($input.suffix)}
                                                                            <span class="input-group-addon">{$input.suffix|escape:'quotes':'UTF-8'}</span>
                                                                        {/if}
                                                                        {if isset($input.select.after) && $input.select.after}
                                                                            <div class="input-group-btn">
                                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    <span class="js-inputwithselect-label">{if $name_selected}{$name_selected|escape:'htmlall':'UTF-8'}{else}{l s='Select' mod='advancedvatmanager'}{/if}</span>
                                                                                    <span class="caret"></span>
                                                                                    <input type="hidden"
                                                                                           name="{$input.select.name|escape:'htmlall':'UTF-8'}"
                                                                                           id="{if isset($input.select.id)}{$input.select.id|escape:'htmlall':'UTF-8'}{else}{$input.select.name|escape:'htmlall':'UTF-8'}{/if}"
                                                                                           value="{$value_selected|escape:'htmlall':'UTF-8'}"
                                                                                    >
                                                                                </button>
                                                                                <ul class="dropdown-menu js-inputwithselect-menu">
                                                                                    {foreach $input.select.options.query as $option}
                                                                                        {if $option === "-"}
                                                                                            <li role="separator" class="divider"></li>
                                                                                        {else}
                                                                                            <li class="js-inputwithselect-option"
                                                                                                data-value="{$option[$input.select.options.id]|intval}"
                                                                                                    {if $option[$input.select.options.id] == $value_selected}
                                                                                                        data-selected="true"
                                                                                                    {/if}
                                                                                            >
                                                                                                <a href="#">{$option[$input.select.options.name]|escape:'htmlall':'UTF-8'}</a>
                                                                                            </li>
                                                                                        {/if}
                                                                                    {/foreach}
                                                                                </ul>
                                                                            </div>
                                                                        {/if}
                                                                    </div>
                                                                {if isset($input.maxchar) && $input.maxchar}
                                                                    <script type="text/javascript">
                                                                        $(document).ready(function(){
                                                                            countDown($("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"), $("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_counter"));
                                                                        });
                                                                    </script>
                                                                {/if}
                                                                {elseif $input.type == 'select'}
                                                                {if isset($input.options.query) && !$input.options.query && isset($input.empty_message)}
                                                                    {$input.empty_message|escape:'htmlall':'UTF-8'}
                                                                    {$input.required = false}
                                                                    {$input.desc = null}
                                                                {else}
                                                                    <select name="{$input.name|escape:'html':'UTF-8'}"
                                                                            class="{if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if} fixed-width-xl"
                                                                            id="{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}{/if}"
                                                                            {if isset($input.multiple) && $input.multiple} multiple="multiple"{/if}
                                                                            {if isset($input.size)} size="{$input.size|intval}"{/if}
                                                                            {if isset($input.onchange)} onchange="{$input.onchange|escape:'html':'UTF-8'}"{/if}
                                                                            {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                                                                    >
                                                                        {if isset($input.options.default)}
                                                                            <option value="{$input.options.default.value|escape:'html':'UTF-8'}">{$input.options.default.label|escape:'html':'UTF-8'}</option>
                                                                        {/if}
                                                                        {if isset($input.options.optiongroup)}
                                                                            {foreach $input.options.optiongroup.query AS $optiongroup}
                                                                                <optgroup label="{$optiongroup[$input.options.optiongroup.label]|escape:'htmlall':'UTF-8'}">
                                                                                    {foreach $optiongroup[$input.options.options.query] as $option}
                                                                                        <option value="{$option[$input.options.options.id]|escape:'htmlall':'UTF-8'}"
                                                                                                {if isset($input.multiple)}
                                                                                                    {foreach $fields_value[$input.name] as $field_value}
                                                                                                        {if $field_value == $option[$input.options.options.id]}selected="selected"{/if}
                                                                                                    {/foreach}
                                                                                                {else}
                                                                                                    {if $fields_value[$input.name] == $option[$input.options.options.id]}selected="selected"{/if}
                                                                                                {/if}
                                                                                        >{$option[$input.options.options.name]|escape:'htmlall':'UTF-8'}</option>
                                                                                    {/foreach}
                                                                                </optgroup>
                                                                            {/foreach}
                                                                        {else}
                                                                            {foreach $input.options.query AS $option}
                                                                                {if is_object($option)}
                                                                                    <option value="{$option->$input.options.id|escape:'htmlall':'UTF-8'}"
                                                                                            {if isset($input.multiple)}
                                                                                                {foreach $fields_value[$input.name] as $field_value}
                                                                                                    {if $field_value == $option->$input.options.id}
                                                                                                        selected="selected"
                                                                                                    {/if}
                                                                                                {/foreach}
                                                                                            {else}
                                                                                                {if $fields_value[$input.name] == $option->$input.options.id}
                                                                                                    selected="selected"
                                                                                                {/if}
                                                                                            {/if}
                                                                                    >{$option->$input.options.name|escape:'htmlall':'UTF-8'}</option>
                                                                                {elseif $option == "-"}
                                                                                    <option value="">-</option>
                                                                                {else}
                                                                                    <option value="{$option[$input.options.id]|escape:'htmlall':'UTF-8'}"
                                                                                            {if isset($input.multiple)}
                                                                                                {foreach $fields_value[$input.name] as $field_value}
                                                                                                    {if $field_value == $option[$input.options.id]}
                                                                                                        selected="selected"
                                                                                                    {/if}
                                                                                                {/foreach}
                                                                                            {else}
                                                                                                {if $fields_value[$input.name] == $option[$input.options.id]}
                                                                                                    selected="selected"
                                                                                                {/if}
                                                                                            {/if}
                                                                                    >{$option[$input.options.name]|escape:'htmlall':'UTF-8'}</option>

                                                                                {/if}
                                                                            {/foreach}
                                                                        {/if}
                                                                    </select>
                                                                {/if}
                                                                {elseif $input.type == 'radio'}
                                                                {foreach $input.values as $value}
                                                                    <div class="radio {if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}">
                                                                        {strip}
                                                                            <label>
                                                                                <input type="radio"	name="{$input.name|escape:'htmlall':'UTF-8'}" id="{$value.id|escape:'htmlall':'UTF-8'}" value="{$value.value|escape:'htmlall':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                                                                {$value.label|escape:'htmlall':'UTF-8'}
                                                                            </label>
                                                                        {/strip}
                                                                    </div>
                                                                {if isset($value.p) && $value.p}<p class="help-block">{$value.p|escape:'html':'UTF-8'}</p>{/if}
                                                                {/foreach}
                                                                {elseif $input.type == 'switch'}
                                                                    <span class="switch prestashop-switch fixed-width-lg">
                                                                        {foreach $input.values as $value}
                                                                            <input type="radio" name="{$input.name|escape:'htmlall':'UTF-8'}"{if $value.value == 1} id="{$input.name|escape:'htmlall':'UTF-8'}_on"{else} id="{$input.name|escape:'htmlall':'UTF-8'}_off"{/if} value="{$value.value|escape:'htmlall':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                                                                            {strip}
                                                                            <label {if $value.value == 1} for="{$input.name|escape:'htmlall':'UTF-8'}_on"{else} for="{$input.name|escape:'htmlall':'UTF-8'}_off"{/if}>
                                                                                    {if $value.value == 1}
                                                                                        {l s='Yes' mod='advancedvatmanager'}
                                                                                    {else}
                                                                                        {l s='No' mod='advancedvatmanager'}
                                                                                    {/if}
                                                                                </label>
                                                                        {/strip}
                                                                        {/foreach}
                                                                        <a class="slide-button btn"></a>
                                                                    </span>
                                                                {elseif $input.type == 'textbutton'}
                                                                    {assign var='value_text' value=$fields_value[$input.name]}
                                                                    <div class="row">
                                                                        <div class="col-lg-9">
                                                                            {if isset($input.maxchar)}
                                                                            <div class="input-group">
                                                                                    <span id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_counter" class="input-group-addon">
                                                                                        <span class="text-count-down">{$input.maxchar|intval}</span>
                                                                                    </span>
                                                                                {/if}
                                                                                <input type="text"
                                                                                       name="{$input.name|escape:'htmlall':'UTF-8'}"
                                                                                       id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
                                                                                       value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'htmlall':'UTF-8'}{else}{$value_text|escape:'htmlall':'UTF-8'}{/if}"
                                                                                       class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}{if $input.type == 'tags'} tagify{/if}"
                                                                                        {if isset($input.size)} size="{$input.size|intval}"{/if}
                                                                                        {if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}
                                                                                        {if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}
                                                                                        {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
                                                                                        {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                                                                                        {if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
                                                                                        {if isset($input.placeholder) && $input.placeholder } placeholder="{$input.placeholder|escape:'htmlall':'UTF-8'}"{/if}
                                                                                />
                                                                                {if isset($input.suffix)}{$input.suffix|escape:'htmlall':'UTF-8'}{/if}
                                                                            {if isset($input.maxchar) && $input.maxchar}
                                                                            </div>
                                                                            {/if}
                                                                        </div>
                                                                        <div class="col-lg-2">
                                                                            <button type="button" class="btn btn-default{if isset($input.button.attributes['class'])} {$input.button.attributes['class']|escape:'htmlall':'UTF-8'}{/if}{if isset($input.button.class)} {$input.button.class|escape:'htmlall':'UTF-8'}{/if}"
                                                                            {if isset($input.button.attributes)}
                                                                            {foreach from=$input.button.attributes key=name item=value}
                                                                                {if $name|lower != 'class'}
                                                                                    {$name|escape:'htmlall':'UTF-8'}="{$value|escape:'htmlall':'UTF-8'}"
                                                                                {/if}
                                                                            {/foreach}
                                                                            {/if}>
                                                                            {$input.button.label|escape:'htmlall':'UTF-8'}
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                {if isset($input.maxchar) && $input.maxchar}
                                                                    <script type="text/javascript">
                                                                        $(document).ready(function() {
                                                                            countDown($("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"), $("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_counter"));
                                                                        });
                                                                    </script>
                                                                {/if}
                                                                {elseif $input.type == 'textarea'}
                                                                {if isset($input.maxchar) && $input.maxchar}<div class="input-group">{/if}
                                                                    {assign var=use_textarea_autosize value=true}
                                                                    {if isset($input.lang) && $input.lang}
                                                                    {foreach $languages as $language}
                                                                    {if $languages|count > 1}
                                                                        <div class="form-group translatable-field lang-{$language.id_lang|intval}"{if $language.id_lang != $defaultFormLanguage} style="display:none;"{/if}>
                                                                        <div class="col-lg-9">
                                                                    {/if}
                                                                        {if isset($input.maxchar) && $input.maxchar}
                                                                            <span id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}{/if}_counter" class="input-group-addon">
                                                                                        <span class="text-count-down">{$input.maxchar|intval}</span>
                                                                                    </span>
                                                                        {/if}
                                                                        <textarea{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if} name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_{$language.id_lang|intval}" class="{if isset($input.autoload_rte) && $input.autoload_rte}rte autoload_rte{else}textarea-autosize{/if}{if isset($input.class)} {$input.class|escape:'htmlall':'UTF-8'}{/if}"{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}>{$fields_value[$input.name][$language.id_lang]|escape:'htmlall':'UTF-8'}</textarea>
                                                                    {if $languages|count > 1}
                                                                        </div>
                                                                        <div class="col-lg-2">
                                                                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                                {$language.iso_code|escape:'htmlall':'UTF-8'}
                                                                                <span class="caret"></span>
                                                                            </button>
                                                                            <ul class="dropdown-menu">
                                                                                {foreach from=$languages item=language}
                                                                                    <li>
                                                                                        <a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'htmlall':'UTF-8'}</a>
                                                                                    </li>
                                                                                {/foreach}
                                                                            </ul>
                                                                        </div>
                                                                        </div>
                                                                    {/if}
                                                                    {/foreach}
                                                                    {if isset($input.maxchar) && $input.maxchar}
                                                                        <script type="text/javascript">
                                                                            $(document).ready(function(){
                                                                                {foreach from=$languages item=language}
                                                                                countDown($("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}{/if}"), $("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}{/if}_counter"));
                                                                                {/foreach}
                                                                            });
                                                                        </script>
                                                                    {/if}
                                                                    {else}
                                                                    {if isset($input.maxchar) && $input.maxchar}
                                                                        <span id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}{/if}_counter" class="input-group-addon">
                                                                                    <span class="text-count-down">{$input.maxchar|intval}</span>
                                                                                </span>
                                                                    {/if}
                                                                        <textarea{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if} name="{$input.name|escape:'htmlall':'UTF-8'}" id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}" {if isset($input.cols)}cols="{$input.cols|escape:'htmlall':'UTF-8'}"{/if} {if isset($input.rows)}rows="{$input.rows|escape:'htmlall':'UTF-8'}"{/if} class="{if isset($input.autoload_rte) && $input.autoload_rte}rte autoload_rte{else}textarea-autosize{/if}{if isset($input.class)} {$input.class|escape:'htmlall':'UTF-8'}{/if}"{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}>{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}</textarea>
                                                                    {if isset($input.maxchar) && $input.maxchar}
                                                                        <script type="text/javascript">
                                                                            $(document).ready(function(){
                                                                                countDown($("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"), $("#{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_counter"));
                                                                            });
                                                                        </script>
                                                                    {/if}
                                                                    {/if}
                                                                    {if isset($input.maxchar) && $input.maxchar}</div>{/if}
                                                                {elseif $input.type == 'checkbox'}
                                                                {if isset($input.expand)}
                                                                    <a class="btn btn-default show_checkbox{if strtolower($input.expand.default) == 'hide'} hidden{/if}" href="#">
                                                                        <i class="icon-{$input.expand.show.icon|escape:'htmlall':'UTF-8'}"></i>
                                                                        {$input.expand.show.text|escape:'htmlall':'UTF-8'}
                                                                        {if isset($input.expand.print_total) && $input.expand.print_total > 0}
                                                                            <span class="badge">{$input.expand.print_total|intval}</span>
                                                                        {/if}
                                                                    </a>
                                                                    <a class="btn btn-default hide_checkbox{if strtolower($input.expand.default) == 'show'} hidden{/if}" href="#">
                                                                        <i class="icon-{$input.expand.hide.icon|escape:'htmlall':'UTF-8'}"></i>
                                                                        {$input.expand.hide.text|escape:'htmlall':'UTF-8'}
                                                                        {if isset($input.expand.print_total) && $input.expand.print_total > 0}
                                                                            <span class="badge">{$input.expand.print_total|intval}</span>
                                                                        {/if}
                                                                    </a>
                                                                {/if}
                                                                {foreach $input.values.query as $value}
                                                                    {assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
                                                                    <div class="checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">
                                                                        {strip}
                                                                            <label for="{$id_checkbox|escape:'htmlall':'UTF-8'}">
                                                                                <input type="checkbox" name="{$id_checkbox|escape:'htmlall':'UTF-8'}" id="{$id_checkbox|escape:'htmlall':'UTF-8'}" class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"{if isset($value.val)} value="{$value.val|escape:'htmlall':'UTF-8'}"{/if}{if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]} checked="checked"{/if} />
                                                                                {$value[$input.values.name]|escape:'htmlall':'UTF-8'}
                                                                            </label>
                                                                        {/strip}
                                                                    </div>
                                                                {/foreach}
                                                                {elseif $input.type == 'datetime'}
                                                                    <div class="row">
                                                                        <div class="input-group col-lg-4">
                                                                            <input
                                                                                    id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
                                                                                    type="text"
                                                                                    data-hex="true"{if isset($input.class)} class="{$input.class|escape:'htmlall':'UTF-8'}"{else} class="datetimepicker"{/if}
                                                                                    name="{$input.name|escape:'htmlall':'UTF-8'}"
                                                                                    value="{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}"
                                                                            />
                                                                            <span class="input-group-addon">
                                                                                <i class="icon-calendar-empty"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                {/if}
                                                            {/block}{* end block input *}
                                                            {block name="description"}
                                                                {if isset($input.desc) && !empty($input.desc)}
                                                                    <p class="help-block">
                                                                        {if is_array($input.desc)}
                                                                            {foreach $input.desc as $p}
                                                                                {if is_array($p)}
                                                                                    <span id="{$p.id|escape:'html':'UTF-8'}">{$p.text|escape:'htmlall':'UTF-8'}</span><br />
                                                                                {else}
                                                                                    {$p|escape:'htmlall':'UTF-8'}<br />
                                                                                {/if}
                                                                            {/foreach}
                                                                        {else}
                                                                            {$input.desc|escape:'htmlall':'UTF-8'}
                                                                        {/if}
                                                                    </p>
                                                                {/if}
                                                            {/block}
                                                        </div>
                                                    {/block}{* end block field *}
                                                {/if}
                                            </div>
                                        {/block}{* end block input_row *}
                                    {/foreach}
                                    {hook h='displayAdminForm' fieldset=$f}
                                    {if isset($name_controller)}
                                        {capture name=hookName assign=hookName}display{$name_controller|ucfirst|escape:'htmlall':'UTF-8'}Form{/capture}
                                        {hook h=$hookName fieldset=$f}
                                    {elseif isset($smarty.get.controller)}
                                        {capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|htmlentities|escape:'htmlall':'UTF-8'}Form{/capture}
                                        {hook h=$hookName fieldset=$f}
                                    {/if}
                                </div><!-- /.form-wrapper -->
                            {elseif $key == 'desc'}
                                <div class="alert alert-info col-lg-offset-3">
                                    {if is_array($field)}
                                        {foreach $field as $k => $p}
                                            {if is_array($p)}
                                                <span{if isset($p.id)} id="{$p.id|escape:'htmlall':'UTF-8'}"{/if}>{$p.text|escape:'htmlall':'UTF-8'}</span><br />
                                            {else}
                                                {$p|escape:'htmlall':'UTF-8'}
                                                {if isset($field[$k+1])}<br />{/if}
                                            {/if}
                                        {/foreach}
                                    {else}
                                        {$field|escape:'htmlall':'UTF-8'}
                                    {/if}
                                </div>
                            {/if}
                            {block name="other_input"}{/block}
                        {/foreach}
                        {block name="footer"}
                            {capture name='form_submit_btn'}{counter name='form_submit_btn'}{/capture}
                            {if isset($fieldset['form']['submit']) || isset($fieldset['form']['buttons'])}
                                <div class="panel-footer">
                                    {if isset($fieldset['form']['submit']) && !empty($fieldset['form']['submit'])}
                                        <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']|escape:'htmlall':'UTF-8'}{else}{$table|escape:'htmlall':'UTF-8'}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']|escape:'htmlall':'UTF-8'}{else}{$submit_action|escape:'htmlall':'UTF-8'}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="{if isset($fieldset['form']['submit']['class'])}{$fieldset['form']['submit']['class']|escape:'htmlall':'UTF-8'}{else}btn btn-default pull-right{/if}">
                                            <i class="{if isset($fieldset['form']['submit']['icon'])}{$fieldset['form']['submit']['icon']|escape:'htmlall':'UTF-8'}{else}process-icon-save{/if}"></i> {$fieldset['form']['submit']['title']|escape:'htmlall':'UTF-8'}
                                        </button>
                                    {/if}
                                    {if isset($show_cancel_button) && $show_cancel_button}
                                        <a href="{$back_url|escape:'htmlall':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                            <i class="process-icon-cancel"></i> {l s='Cancel' mod='advancedvatmanager'}
                                        </a>
                                    {/if}
                                    {if isset($fieldset['form']['reset'])}
                                        <button
                                                type="reset"
                                                id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']|escape:'htmlall':'UTF-8'}{else}{$table|escape:'htmlall':'UTF-8'}_form_reset_btn{/if}"
                                                class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']|escape:'htmlall':'UTF-8'}{else}btn btn-default{/if}"
                                        >
                                            {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']|escape:'htmlall':'UTF-8'}"></i> {/if} {$fieldset['form']['reset']['title']|escape:'htmlall':'UTF-8'}
                                        </button>
                                    {/if}
                                    {if isset($fieldset['form']['buttons'])}
                                        {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                            {if isset($btn.href) && trim($btn.href) != ''}
                                                <a href="{$btn.href|escape:'htmlall':'UTF-8'}" {if isset($btn['id'])}id="{$btn['id']|escape:'htmlall':'UTF-8'}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']|escape:'htmlall':'UTF-8'}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js|escape:'htmlall':'UTF-8'}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']|escape:'htmlall':'UTF-8'}" ></i> {/if}{$btn.title|escape:'htmlall':'UTF-8'}</a>
                                            {else}
                                                <button type="{if isset($btn['type'])}{$btn['type']|escape:'htmlall':'UTF-8'}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']|escape:'htmlall':'UTF-8'}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']|escape:'htmlall':'UTF-8'}{/if}" name="{if isset($btn['name'])}{$btn['name']|escape:'htmlall':'UTF-8'}{else}submitOptions{$table|escape:'htmlall':'UTF-8'}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js|escape:'htmlall':'UTF-8'}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']|escape:'htmlall':'UTF-8'}" ></i> {/if}{$btn.title|escape:'htmlall':'UTF-8'}</button>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </div>
                            {/if}
                        {/block}{* end block footer *}
                    </div>
                    {if isset($vertical_tabs) && isset($vertical_tabs.form[$f])}
                        </div>
                    {/if}
                {/block}{* end block fieldset *}
                {block name="other_fieldsets"}
                {/block}
            {/foreach}
</form> 
<!-- DOCUMENTATION TAB BEGIN --->     
<form id="documentation_tab" class="defaultForm form-horizontal{if isset($vertical_tabs) && $vertical_tabs|count} col-lg-10 col-md-9{/if}{if isset($name_controller) && $name_controller} {$name_controller|escape:'htmlall':'UTF-8'}{/if}"{if isset($current) && $current} action="{$current|escape:'htmlall':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'htmlall':'UTF-8'}{/if}"{/if} method="post" enctype="multipart/form-data"{if isset($style)} style="{$style|escape:'htmlall':'UTF-8'}"{/if} novalidate>        
{if isset($vertical_tabs) && isset($vertical_tabs.form['documentation_tab'])}
<div id="js-tab-content-documentation_tab" class="js-tab-content{if isset($vertical_tabs.form['documentation_tab'].active) && $vertical_tabs.form['documentation_tab'].active} active{else} hidden{/if}">
   {/if}
   <div class="panel" id="fieldset_documentation_tab{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
<div id="documentation" class="documentation">
    <div class="panel-heading">
        <i class="fal fa-book"></i>&nbsp;{l s='Documentation' mod='advancedvatmanager'}
    </div>
    <h5><span class="subtitle">{l s='You can read the module documentation in several languages. Learn to use the module and know all the information about it.' mod='advancedvatmanager'}</span></h5>
        <br />
    
    <div class="language_flag">
        <a type="button" class="btn btn-default" href="../modules/advancedvatmanager/documentation/es/index.html" target="_blank"><img src="../modules/advancedvatmanager/views/img/es.png" width="80" title="{l s='Spanish' mod='advancedvatmanager'}"/></a>
    </div>
    <div class="language_flag">
        <a type="button" class="btn btn-default" href="../modules/advancedvatmanager/documentation/en/index.html" target="_blank"><img src="../modules/advancedvatmanager/views/img/en.png" width="80" title="{l s='English' mod='advancedvatmanager'}"/></a>
    </div>
</div>
</div>
{if isset($vertical_tabs) && isset($vertical_tabs.form['documentation_tab'])}
</div>
{/if}
</form>
<!-- DOCUMENTATION TAB END --->
<!-- CHANGELOG TAB BEGIN --->     
<form id="changelog_tab" class="defaultForm form-horizontal{if isset($vertical_tabs) && $vertical_tabs|count} col-lg-10 col-md-9{/if}{if isset($name_controller) && $name_controller} {$name_controller|escape:'htmlall':'UTF-8'}{/if}"{if isset($current) && $current} action="{$current|escape:'htmlall':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'htmlall':'UTF-8'}{/if}"{/if} method="post" enctype="multipart/form-data"{if isset($style)} style="{$style|escape:'htmlall':'UTF-8'}"{/if} novalidate>        
{if isset($vertical_tabs) && isset($vertical_tabs.form['changelog_tab'])}
<div id="js-tab-content-changelog_tab" class="js-tab-content{if isset($vertical_tabs.form['changelog_tab'].active) && $vertical_tabs.form['changelog_tab'].active} active{else} hidden{/if}">
   {/if}
   <div class="panel" id="fieldset_changelog_tab{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
<div id="documentation" class="documentation">
    <div class="panel-heading">
        <i class="fal fa-code"></i>&nbsp;{l s='Changelog' mod='advancedvatmanager'}
    </div>
    <div class="legend">
        <h5><strong>{l s='LEGEND' mod='advancedvatmanager'}</strong></h5>
        [DEL] = {l s='Deleted element' mod='advancedvatmanager'}<br />
        [NEW] = {l s='New element' mod='advancedvatmanager'}<br />
        [ADD] = {l s='Add feature' mod='advancedvatmanager'}<br />
        [FIX] = {l s='Bug fixed' mod='advancedvatmanager'}<br />
        [IMP] = {l s='Improvement' mod='advancedvatmanager'}<br />
    </div>
    <br />
    <div class="changelog_content">
            {foreach $changelog as $line}
                {if strpos($line, '#') !== false}
                    <h5><strong>{Tools::str_replace_once('#', '', $line)|escape:'htmlall':'UTF-8'}</strong></h5>
                {else}
                    <span>{$line|escape:'htmlall':'UTF-8'}</span><br />
                {/if}
            {/foreach}
    </div>
</div>
</div>
{if isset($vertical_tabs) && isset($vertical_tabs.form['changelog_tab'])}
</div>
{/if}
</form>
<!-- CHANGELOG TAB END --->
<!-- COMPATIBILITY TAB BEGIN --->     
<form id="compatibility_tab" class="defaultForm form-horizontal{if isset($vertical_tabs) && $vertical_tabs|count} col-lg-10 col-md-9{/if}{if isset($name_controller) && $name_controller} {$name_controller|escape:'htmlall':'UTF-8'}{/if}"{if isset($current) && $current} action="{$current|escape:'htmlall':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'htmlall':'UTF-8'}{/if}"{/if} method="post" enctype="multipart/form-data"{if isset($style)} style="{$style|escape:'htmlall':'UTF-8'}"{/if} novalidate>        
{if isset($vertical_tabs) && isset($vertical_tabs.form['compatibility_tab'])}
<div id="js-tab-content-compatibility_tab" class="js-tab-content{if isset($vertical_tabs.form['compatibility_tab'].active) && $vertical_tabs.form['compatibility_tab'].active} active{else} hidden{/if}">
   {/if}
   <div class="panel" id="fieldset_compatibility_tab{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
<div id="documentation" class="documentation">
    <div class="panel-heading">
        <i class="fal fa-cubes"></i>&nbsp;{l s='Compatibility/Integration with third party modules' mod='advancedvatmanager'}
    </div>
    <div class="compatibility content">
        <p>{l s='Our module is compatible with the vast majority of modules. However, it makes use of several overrides and conflict problems could occur due to the simultaneous use of the same override. In this case, you must contact our technical support service so that we can review and implement compatibility.' mod='advancedvatmanager'}</p>
        <p>{l s='In the case of the modules intended to manage the Prestashop checkout page, there could be incompatibilities with our module, because these modules use their own controllers to manage that section. For this reason, we only recommend using our module with checkout modules that are on this compatibility or integration list.' mod='advancedvatmanager'}</p>
        <p>{l s='The modules listed below have been tested and found to be compatible with this module.' mod='advancedvatmanager'}</p>
        <br />
        <h4 class="compatibility_title"><i class="far fa-cubes"></i> {l s='COMPATIBILITY' mod='advancedvatmanager'}</h4>
        <p>{l s='The following modules have been tested and supported:' mod='advancedvatmanager'}</p>
        <div class="compatibility_list">
            <div class="col-lg-6">
               <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/express-checkout-process/48891-one-page-checkout-fast-intuitive-professional.html" target="_blank">{l s='One Page Checkout module by ETS-Soft' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('ets_onepagecheckout')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('ets_onepagecheckout')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>      
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/other-payment-methods/28289-payment-with-fee-custom-payment-methods.html" target="_blank">{l s='Payment With Fee module by ETS-Soft' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('ets_payment_with_fee')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('ets_payment_with_fee')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>    
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://www.sunnytoo.com/product/easycheckout-one-page-checkout-module-prestashop-1-7" target="_blank">{l s='Easy checkout module by SUNNYTOO.COM' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('steasycheckout')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('steasycheckout')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>  
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/accounting-invoicing/20356-advance-invoice-delivery-credit-pdf-custom-number.html" target="_blank">{l s='Advance Invoice, Delivery, Credit PDF + Custom Number module by buy-addons' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('ba_prestashop_invoice')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('ba_prestashop_invoice')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5> 
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/shipping-costs/8707-free-shipping-by-zone-carrier-price-and-weight.html" target="_blank">{l s='Free Shipping / Delivery by Zone, Carrier, Price and Weight module by Linea Grafica' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('lgfreeshippingzones')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('lgfreeshippingzones')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5> 
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/price-management/22633-price-rounding-5ct-10ct-99-95-swiss-etc.html" target="_blank">{l s='Advanced Rounding (Swiss Round 5ct, 10ct, and more) module by Idnovate' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('pricerounding')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('pricerounding')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/promotions-gifts/9129-promotions-and-discounts-3x2-sales-offers-packs.html" target="_blank">{l s='Promotions and discounts - (3x2, reductions, campaigns) module by Idnovate' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('quantitydiscountpro')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('quantitydiscountpro')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>
            </div>
            <div class="col-lg-6">
               <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://alcalink.com/producto/modulo-manomano-prestashop-sincronizacion/" target="_blank">{l s='ManoMano module by Alcalink' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('manomanomarketplace17')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('manomanomarketplace17')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://github.com/nenes25/eicaptcha/releases" target="_blank">{l s='Ei Captcha free module by hhennes' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('eicaptcha')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('eicaptcha')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/website-security-access/18478-re-captcha-anti-spam-recaptcha-anti-fake-accounts.html" target="_blank">{l s='reCaptcha module by Charlie' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('recaptcha')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('recaptcha')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/price-management/26993-hide-or-show-price-and-disallow-purchase.html" target="_blank">{l s='Hide the product prices and disallow purchases by categories, groups, and more module by Idnovate' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('hideprice')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('hideprice')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>
        
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/registration-ordering-process/27632-minimum-maximum-multiple-units-and-total-cart-limits.html" target="_blank">{l s='Minimum and maximum purchase product quantity module by Idnovate' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('minpurchase')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('minpurchase')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5> 
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/payment-card-wallet/53135-paypal-plus-with-fees-surcharge-card-payment-bnpl.html" target="_blank">{l s='PayPal Plus (multi, fees, refunds, card, buy now / pay later,...) module by Idnovate' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('paypalfeeplus')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('paypalfeeplus')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>
                
                <h5 class="module_compatible_item"><a style="color:#555;text-decoration:none" href="https://www.scaledev.fr/boutique/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/prestashop-addons/38-prestashop-manomano-module.html" target="_blank">{l s='ManoMano module (MonEchelle) by Scale-DEV' mod='advancedvatmanager'}</a>&nbsp;&nbsp;{if Module::isInstalled('sdevmanomano')}<span style="color:#00E10E;" class="mod_installed"><i class="fal fa-check-circle"></i> {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('sdevmanomano')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</h5>           
            </div>  
        </div>
        <br />
        <p>{l s='NOTE: That they are compatible does not ensure that there may be a possibility that some options of our module will not work when some of these modules are activated in the store. Compatibility ensures that both installed and working modules can coexist without generating errors or conflicts between them. Only in the modules that have been fully integrated, the functionalities and all the options of our module work correctly and are perfectly integrated in these modules. We recommend acquiring the modules to which we have developed the integration and that are in the list of integration' mod='advancedvatmanager'}</p>  
        <br />
        <h4 class="compatibility_title"><i class="fal fa-puzzle-piece"></i> {l s='FULL INTEGRATION' mod='advancedvatmanager'}</h4>
        <p>{l s='We are developed a full integration with following modules:' mod='advancedvatmanager'}</p>
        <div class="integration_mod_container">
            <div class="integration_header">
                <span style="font-weight:600;font-size:13px;" ><a style="color:#555;text-decoration:none" href="https://addons.prestashop.com/{Context::getContext()->language->iso_code|escape:'htmlall':'UTF-8'}/express-checkout-process/8503-one-page-checkout-ps-easy-fast-intuitive.html" target="_blank"><img src="../modules/advancedvatmanager/views/img/opc_presteamshop.png" width="35" height="auto" /> {l s='One Page Checkout Prestashop module by Presteamshop' mod='advancedvatmanager'}</a></span>       
            </div>
            <div class="integration_body">
                <p>{if Module::isInstalled('onepagecheckoutps')}<span style="color:{if version_compare(Module::getInstanceByName('onepagecheckoutps')->version, '4.1.5', '>=')}#00E10E{else}#ff8014{/if};" class="mod_installed">{if version_compare(Module::getInstanceByName('onepagecheckoutps')->version, '4.1.5', '>=')}<i class="fal fa-check-circle"></i>{else}<i class="far fa-exclamation-triangle"></i>{/if} {l s='Version installed:' mod='advancedvatmanager'} {Module::getInstanceByName('onepagecheckoutps')->version|escape:'htmlall':'UTF-8'}</span>{else}<span style="color:#f51d1d" class="mod_not_installed"><i class="fal fa-times-circle"></i> {l s='Not installed' mod='advancedvatmanager'}</span>{/if}</p>
                {if Module::isInstalled('onepagecheckoutps')}<p>{if  $opc_presteamshop_enabled}<span style="color:#00E10E;"><i class="fal fa-check-circle"></i> {l s='Checkout interface version 5 enabled' mod='advancedvatmanager'}{else}<span style="color:#ff8014;"><i class="far fa-exclamation-triangle"></i> {l s='Checkout interface version 5 disabled' mod='advancedvatmanager'}{/if}</p>{/if}
            </div>
        </div>
    </div>
    <br /> 
    <div class="compatibility content">
        <h4 class="compatibility_title_2"><i class="fal fa-pencil-ruler"></i> {l s='COMPATIBILITY INSTRUCTIONS' mod='advancedvatmanager'}</h4>
        <p>{l s='This module is designed to make the necessary changes to the override files to ensure compatibility between the modules listed once it is installed, restarted or activated. You do not have to do any action on your part, the whole process is done automatically. When our module is deactivated or uninstalled, the module system returns the original code of the overrides of the compatible modules that are activated at that moment.' mod='advancedvatmanager'}</p>
        <p><strong>{l s='If you have any issues with code for override methods that have been customized before. The module, each time it is installed or activated, saves a backup copy of all the contents of the [override] folder of the shop root directory to the [backup] folder inside the module directory.' mod='advancedvatmanager'}</strong></p> 
        <br />
        <h4 class="installation_note" style="font-weight:900;">{l s='Procedure in installation or activation any of the modules indicated in this compatibility and integration list' mod='advancedvatmanager'}</h4>
        <p>{l s='If you already have our module installed and you want to install any of the modules indicated in this compatibility and integration list, all you have to do is deactivate our module, install the desired module and finally activate our module again. By activating our module, the system will perform compatibility on overrides.' mod='advancedvatmanager'}</p>        
    </div>
</div>
</div>
{if isset($vertical_tabs) && isset($vertical_tabs.form['compatibility_tab'])}
</div>
{/if}
</form>
<!-- COMPATIBILITY TAB END --->
<!-- LICENSE TAB BEGIN --->     
<form id="license_tab" class="defaultForm form-horizontal{if isset($vertical_tabs) && $vertical_tabs|count} col-lg-10 col-md-9{/if}{if isset($name_controller) && $name_controller} {$name_controller|escape:'htmlall':'UTF-8'}{/if}"{if isset($current) && $current} action="{$current|escape:'htmlall':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'htmlall':'UTF-8'}{/if}"{/if} method="post" enctype="multipart/form-data"{if isset($style)} style="{$style|escape:'htmlall':'UTF-8'}"{/if} novalidate>        
{if isset($vertical_tabs) && isset($vertical_tabs.form['license_tab'])}
<div id="js-tab-content-license_tab" class="js-tab-content{if isset($vertical_tabs.form['license_tab'].active) && $vertical_tabs.form['license_tab'].active} active{else} hidden{/if}">
   {/if}
   <div class="panel" id="fieldset_license_tab{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
<div id="documentation" class="documentation">
    <div class="panel-heading">
        <i class="fal fa-gavel"></i>&nbsp;{l s='License' mod='advancedvatmanager'}
    </div>
    <div class="content">  
        {$license_content nofilter}
    </div>
</div>
</div>
{if isset($vertical_tabs) && isset($vertical_tabs.form['license_tab'])}
</div>
{/if}
</form>
<!-- LICENSE TAB END --->
<!-- FAQ TAB BEGIN --->     
<form id="faq_tab" class="defaultForm form-horizontal{if isset($vertical_tabs) && $vertical_tabs|count} col-lg-10 col-md-9{/if}{if isset($name_controller) && $name_controller} {$name_controller|escape:'htmlall':'UTF-8'}{/if}"{if isset($current) && $current} action="{$current|escape:'htmlall':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'htmlall':'UTF-8'}{/if}"{/if} method="post" enctype="multipart/form-data"{if isset($style)} style="{$style|escape:'htmlall':'UTF-8'}"{/if} novalidate>        
{if isset($vertical_tabs) && isset($vertical_tabs.form['faq_tab'])}
<div id="js-tab-content-faq_tab" class="js-tab-content{if isset($vertical_tabs.form['faq_tab'].active) && $vertical_tabs.form['faq_tab'].active} active{else} hidden{/if}">
   {/if}
   <div class="panel" id="fieldset_faq_tab{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
<div id="avm_faq" class="avm_faq">
    <div class="panel-heading">
        <i class="fal fa-question"></i>&nbsp;{l s='FAQ' mod='advancedvatmanager'}
    </div>
    <div class="alert alert-info">
        {l s='In this section you will find the answers to some of the most frequent questions or doubts that users need to clarify in order to better understand how the module works.' mod='advancedvatmanager'}  
    </div>  
    <div class="faq_content">
        {$faq_content nofilter}
    </div>
</div>
</div>
{if isset($vertical_tabs) && isset($vertical_tabs.form['faq_tab'])}
</div>
{/if}
</form>
<!-- FAQ TAB END --->
<!-- TROUBLESHOOTING TAB BEGIN --->     
<form id="troubleshooting_tab" class="defaultForm form-horizontal{if isset($vertical_tabs) && $vertical_tabs|count} col-lg-10 col-md-9{/if}{if isset($name_controller) && $name_controller} {$name_controller|escape:'htmlall':'UTF-8'}{/if}"{if isset($current) && $current} action="{$current|escape:'htmlall':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'htmlall':'UTF-8'}{/if}"{/if} method="post" enctype="multipart/form-data"{if isset($style)} style="{$style|escape:'htmlall':'UTF-8'}"{/if} novalidate>        
{if isset($vertical_tabs) && isset($vertical_tabs.form['troubleshooting_tab'])}
<div id="js-tab-content-troubleshooting_tab" class="js-tab-content{if isset($vertical_tabs.form['troubleshooting_tab'].active) && $vertical_tabs.form['troubleshooting_tab'].active} active{else} hidden{/if}">
   {/if}
   <div class="panel" id="fieldset_troubleshooting_tab{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
<div id="avm_troubleshooting" class="avm_troubleshooting">
    <div class="panel-heading">
        <i class="fal fa-laptop-medical"></i>&nbsp;{l s='Troubleshooting' mod='advancedvatmanager'}
    </div>
    <div class="alert alert-info">
        {l s='In this section you will find the solution to the frequent problems that you can find during the operation of the module. If the instructions provided do not solve the problem, we recommend that you contact our technical support service so that we can verify your problem.' mod='advancedvatmanager'}  
    </div>  
    <div class="troubleshooting_content">
        {$troubleshooting_content nofilter} 
    </div>
</div>
</div>
{if isset($vertical_tabs) && isset($vertical_tabs.form['faq_tab'])}
</div>
{/if}
</form>
<!-- TROUBLESHOOTING TAB END --->
</div>
{/block}