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
* @copyright 2012-2022 SeoSA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}

{block name="defaultForm"}
    {if $smarty.const._PS_VERSION_|floatval >= 1.6}

    {else}
        <form id="{if isset($fields.form.form.id_form)}{$fields.form.form.id_form|escape:'htmlall':'UTF-8'}{else}{if $table == null}configuration_form{else}{$table}_form{/if}{/if}" class="defaultForm {$name_controller}" action="{$current}&{if !empty($submit_action)}{$submit_action}=1{/if}&token={$token}" method="post" enctype="multipart/form-data" {if isset($style)}style="{$style}"{/if}>
            {if $form_id}
                <input type="hidden" name="{$identifier}" id="{$identifier}" value="{$form_id}" />
            {/if}
            {foreach $fields as $f => $fieldset}

                {foreach $fieldset.form as $key => $field}
                    {foreach $field as $input}
                        {if $input.name == 'ean13'}

                            <fieldset id="fieldset_{$f}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        <legend>
                                            1. {l s='Description' mod='dgridproducts'}
                                        </legend>
                                    {elseif $key == 'description' && $field}
                                        <p class="description">{$field}</p>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_15_1.tpl'}

                                    {elseif $key == 'submit'}
                                        <div class="margin-form">
                                            <input type="submit"
                                                   id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                                                   value="{$field.title}"
                                                   name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                                                   {if isset($field.class)}class="{$field.class}"{/if} />
                                        </div>
                                    {elseif $key == 'desc'}
                                        <p class="clear">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span id="{$p.id}">{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
                                            {/if}
                                        </p>
                                    {/if}
                                    {block name="other_input"}{/block}
                                {/foreach}
                                {if $required_fields}
                                    <div class="small"><sup>*</sup> {l s='Required field' mod='dgridproducts'}</div>
                                {/if}
                            </fieldset>
                            <br>

                            <fieldset id="fieldset_{$f}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        <legend>
                                            2. {l s='Price' mod='dgridproducts'}
                                        </legend>
                                    {elseif $key == 'description' && $field}
                                        <p class="description">{$field}</p>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_15_2.tpl'}

                                    {elseif $key == 'submit'}
                                        <div class="margin-form">
                                            <input type="submit"
                                                   id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                                                   value="{$field.title}"
                                                   name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                                                   {if isset($field.class)}class="{$field.class}"{/if} />
                                        </div>
                                    {elseif $key == 'desc'}
                                        <p class="clear">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span id="{$p.id}">{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
                                            {/if}
                                        </p>
                                    {/if}
                                    {block name="other_input"}{/block}
                                {/foreach}
                                {if $required_fields}
                                    <div class="small"><sup>*</sup> {l s='Required field' mod='dgridproducts'}</div>
                                {/if}
                            </fieldset>
                            <br>

                            <fieldset id="fieldset_{$f}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        <legend>
                                            3. {l s='Article' mod='dgridproducts'}
                                        </legend>
                                    {elseif $key == 'description' && $field}
                                        <p class="description">{$field}</p>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_15_3.tpl'}

                                    {elseif $key == 'submit'}
                                        <div class="margin-form">
                                            <input type="submit"
                                                   id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                                                   value="{$field.title}"
                                                   name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                                                   {if isset($field.class)}class="{$field.class}"{/if} />
                                        </div>
                                    {elseif $key == 'desc'}
                                        <p class="clear">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span id="{$p.id}">{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
                                            {/if}
                                        </p>
                                    {/if}
                                    {block name="other_input"}{/block}
                                {/foreach}
                                {if $required_fields}
                                    <div class="small"><sup>*</sup> {l s='Required field' mod='dgridproducts'}</div>
                                {/if}
                            </fieldset>
                            <br>

                            <fieldset id="fieldset_{$f}">
                                {foreach $fieldset.form as $key => $field}

                                    {if $key == 'legend'}
                                        <legend>
                                            4. {l s='Product' mod='dgridproducts'}
                                        </legend>
                                    {elseif $key == 'description' && $field}
                                        <p class="description">{$field}</p>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_15_4.tpl'}

                                    {elseif $key == 'submit'}
                                        <div class="margin-form">
                                            <input type="submit"
                                                   id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                                                   value="{$field.title}"
                                                   name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                                                   {if isset($field.class)}class="{$field.class}"{/if} />
                                        </div>
                                    {elseif $key == 'desc'}
                                        <p class="clear">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span id="{$p.id}">{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
                                            {/if}
                                        </p>
                                    {/if}
                                    {block name="other_input"}{/block}

                                {/foreach}
                                {if $required_fields}
                                    <div class="small"><sup>*</sup> {l s='Required field' mod='dgridproducts'}</div>
                                {/if}
                            </fieldset>
                            <br>

                            <fieldset id="fieldset_{$f}">
                                {foreach $fieldset.form as $key => $field}

                                    {if $key == 'legend'}
                                        <legend>
                                            5. {l s='Other' mod='dgridproducts'}
                                        </legend>
                                    {elseif $key == 'description' && $field}
                                        <p class="description">{$field}</p>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_15_5.tpl'}

                                    {elseif $key == 'submit'}
                                        <div class="margin-form">
                                            <input type="submit"
                                                   id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                                                   value="{$field.title}"
                                                   name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                                                   {if isset($field.class)}class="{$field.class}"{/if} />
                                        </div>
                                    {elseif $key == 'desc'}
                                        <p class="clear">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span id="{$p.id}">{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
                                            {/if}
                                        </p>
                                    {/if}
                                    {block name="other_input"}{/block}

                                {/foreach}
                                {if $required_fields}
                                    <div class="small"><sup>*</sup> {l s='Required field' mod='dgridproducts'}</div>
                                {/if}
                            </fieldset>
                            <br>

                            {block name="other_fieldsets"}{/block}
                            {if isset($fields[$f+1])}<br />{/if}

                        {/if}
                    {/foreach}
                {/foreach}

                {foreach $fieldset.form as $key => $field}
                    {foreach $field as $input}
                        {if $input.name == 'comb_reference'}

                            <fieldset id="fieldset_{$f}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        <legend>
                                            1. {l s='Price' mod='dgridproducts'}
                                        </legend>
                                    {elseif $key == 'description' && $field}
                                        <p class="description">{$field}</p>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_15_comb1.tpl'}

                                    {elseif $key == 'submit'}
                                        <div class="margin-form">
                                            <input type="submit"
                                                   id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                                                   value="{$field.title}"
                                                   name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                                                   {if isset($field.class)}class="{$field.class}"{/if} />
                                        </div>
                                    {elseif $key == 'desc'}
                                        <p class="clear">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span id="{$p.id}">{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
                                            {/if}
                                        </p>
                                    {/if}
                                    {block name="other_input"}{/block}
                                {/foreach}
                                {if $required_fields}
                                    <div class="small"><sup>*</sup> {l s='Required field' mod='dgridproducts'}</div>
                                {/if}
                            </fieldset>
                            <br>

                            <fieldset id="fieldset_{$f}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        <legend>
                                            2. {l s='Article' mod='dgridproducts'}
                                        </legend>
                                    {elseif $key == 'description' && $field}
                                        <p class="description">{$field}</p>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_15_comb2.tpl'}

                                    {elseif $key == 'submit'}
                                        <div class="margin-form">
                                            <input type="submit"
                                                   id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                                                   value="{$field.title}"
                                                   name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                                                   {if isset($field.class)}class="{$field.class}"{/if} />
                                        </div>
                                    {elseif $key == 'desc'}
                                        <p class="clear">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span id="{$p.id}">{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
                                            {/if}
                                        </p>
                                    {/if}
                                    {block name="other_input"}{/block}
                                {/foreach}
                                {if $required_fields}
                                    <div class="small"><sup>*</sup> {l s='Required field' mod='dgridproducts'}</div>
                                {/if}
                            </fieldset>
                            <br>

                            <fieldset id="fieldset_{$f}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        <legend>
                                            3. {l s='Other' mod='dgridproducts'}
                                        </legend>
                                    {elseif $key == 'description' && $field}
                                        <p class="description">{$field}</p>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_15_comb3.tpl'}

                                    {elseif $key == 'submit'}
                                        <div class="margin-form">
                                            <input type="submit"
                                                   id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
                                                   value="{$field.title}"
                                                   name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
                                                   {if isset($field.class)}class="{$field.class}"{/if} />
                                        </div>
                                    {elseif $key == 'desc'}
                                        <p class="clear">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span id="{$p.id}">{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
                                            {/if}
                                        </p>
                                    {/if}
                                    {block name="other_input"}{/block}
                                {/foreach}
                                {if $required_fields}
                                    <div class="small"><sup>*</sup> {l s='Required field' mod='dgridproducts'}</div>
                                {/if}
                            </fieldset>
                            <br>

                            {block name="other_fieldsets"}{/block}
                            {if isset($fields[$f+1])}<br />{/if}

                        {/if}
                    {/foreach}
                {/foreach}

            {/foreach}
        </form>
    {/if}
{/block}


{block name="field"}
    {block name="input"}
        {if $input.type == 'datetime_scope'}
            <label class="control-label float-left margin-right">{l s='From' mod='dgridproducts'}</label>
            <input class="datetimepicker float-left margin-right-lg fixed-width-lg" type="text" name="{$input.name}_from" value="{$fields_value[$input.name|cat:'_from']|escape:'html':'UTF-8'}">

            <label class="control-label float-left margin-right">{l s='To' mod='dgridproducts'}</label>
            <input class="datetimepicker float-left margin-right fixed-width-lg" type="text" name="{$input.name}_to" value="{$fields_value[$input.name|cat:'_to']|escape:'html':'UTF-8'}">
        {/if}
    {/block}
    {$smarty.block.parent}
    {if $input.name == 'short_description'}
        <label class="control-label col-lg-3 conf_title"  style="margin-top: 10px;">
            {l s='Length of text' mod='dgridproducts'}
        </label>
        <div class="col-lg-9 margin-form" style="margin-top: 10px;">
            <input type="text" name="lenght_short_desc" value="{$fields_value['lenght_short_desc']}" class="fixed-width-xs">
            <p class="help-block">
                {l s='number of characters including spaces' mod='dgridproducts'}
            </p>
        </div>
    {/if}
    {if $input.name == 'description'}
        <label class="control-label col-lg-3 conf_title"  style="margin-top: 10px;">
            {l s='Length of text' mod='dgridproducts'}
        </label>
        <div class="col-lg-9 margin-form" style="margin-top: 10px;">
            <input type="text" name="lenght_desc" value="{$fields_value['lenght_desc']}" class="fixed-width-xs">
            <p class="help-block">
                {l s='number of characters including spaces' mod='dgridproducts'}
            </p>
        </div>
    {/if}
{/block}