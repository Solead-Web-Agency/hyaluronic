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

        {if isset($identifier_bk) && $identifier_bk == $identifier}{capture name='identifier_count'}{counter name='identifier_count'}{/capture}{/if}
        {assign var='identifier_bk' value=$identifier scope='parent'}
        {if isset($table_bk) && $table_bk == $table}{capture name='table_count'}{counter name='table_count'}{/capture}{/if}
        {assign var='table_bk' value=$table scope='parent'}
        <form id="{if isset($fields.form.form.id_form)}{$fields.form.form.id_form|escape:'html':'UTF-8'}{else}{if $table == null}configuration_form{else}{$table}_form{/if}{if isset($smarty.capture.table_count) && $smarty.capture.table_count}_{$smarty.capture.table_count|intval}{/if}{/if}" class="defaultForm form-horizontal{if isset($name_controller) && $name_controller} {$name_controller}{/if}"{if isset($current) && $current} action="{$current|escape:'html':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'html':'UTF-8'}{/if}"{/if} method="post" enctype="multipart/form-data"{if isset($style)} style="{$style}"{/if} novalidate>
            {if $form_id}
                <input type="hidden" name="{$identifier}" id="{$identifier}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}" value="{$form_id}" />
            {/if}
            {if !empty($submit_action)}
                <input type="hidden" name="{$submit_action}" value="1" />
            {/if}
            {foreach $fields as $f => $fieldset}

                {block name="fieldset"}

                    {foreach $fieldset.form.input as $input}
                        {if $input.name == 'ean13'}

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                1. {l s='Description' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block1.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                2. {l s='Price' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block2.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                3. {l s='Article' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block3.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                4. {l s='Product' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block4.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                5. {l s='Log' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block5.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                6. {l s='Other' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block6.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                        {/if}
                    {/foreach}

                    {foreach $fieldset.form.input as $input}
                        {if $input.name == 'comb_reference'}

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                1. {l s='Price' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_comb1.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                2. {l s='Article' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_comb2.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                            <div class="panel" id="fieldset_{$f}{if isset($smarty.capture.identifier_count) && $smarty.capture.identifier_count}_{$smarty.capture.identifier_count|intval}{/if}{if $smarty.capture.fieldset_name > 1}_{($smarty.capture.fieldset_name - 1)|intval}{/if}">
                                {foreach $fieldset.form as $key => $field}
                                    {if $key == 'legend'}
                                        {block name="legend"}
                                            <div class="panel-heading">
                                                {if isset($field.image) && isset($field.title)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
                                                {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
                                                3. {l s='Other' mod='dgridproducts'}
                                            </div>
                                        {/block}
                                    {elseif $key == 'description' && $field}
                                        <div class="alert alert-info">{$field}</div>
                                    {elseif $key == 'warning' && $field}
                                        <div class="alert alert-warning">{$field}</div>
                                    {elseif $key == 'success' && $field}
                                        <div class="alert alert-success">{$field}</div>
                                    {elseif $key == 'error' && $field}
                                        <div class="alert alert-danger">{$field}</div>
                                    {elseif $key == 'input'}

                                        {include file='../form/blocks/block_comb3.tpl'}

                                    {elseif $key == 'desc'}
                                        <div class="alert alert-info col-lg-offset-3">
                                            {if is_array($field)}
                                                {foreach $field as $k => $p}
                                                    {if is_array($p)}
                                                        <span{if isset($p.id)} id="{$p.id}"{/if}>{$p.text}</span><br />
                                                    {else}
                                                        {$p}
                                                        {if isset($field[$k+1])}<br />{/if}
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                {$field}
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
                                                <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']}{else}{$table}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']}{else}{$submit_action}{/if}{if isset($fieldset['form']['submit']['stay']) && $fieldset['form']['submit']['stay']}AndStay{/if}" class="btn btn-primary btn-lg pull-right">
                                                    {$fieldset['form']['submit']['title']}
                                                </button>
                                            {/if}
                                            {if isset($show_cancel_button) && $show_cancel_button}
                                                <a href="{$back_url|escape:'html':'UTF-8'}" class="btn btn-default" onclick="window.history.back();">
                                                    <i class="process-icon-cancel"></i> {l s='Cancel' mod='dgridproducts'}
                                                </a>
                                            {/if}
                                            {if isset($fieldset['form']['reset'])}
                                                <button
                                                        type="reset"
                                                        id="{if isset($fieldset['form']['reset']['id'])}{$fieldset['form']['reset']['id']}{else}{$table}_form_reset_btn{/if}"
                                                        class="{if isset($fieldset['form']['reset']['class'])}{$fieldset['form']['reset']['class']}{else}btn btn-default{/if}"
                                                >
                                                    {if isset($fieldset['form']['reset']['icon'])}<i class="{$fieldset['form']['reset']['icon']}"></i> {/if} {$fieldset['form']['reset']['title']}
                                                </button>
                                            {/if}
                                            {if isset($fieldset['form']['buttons'])}
                                                {foreach from=$fieldset['form']['buttons'] item=btn key=k}
                                                    {if isset($btn.href) && trim($btn.href) != ''}
                                                        <a href="{$btn.href}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" {if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</a>
                                                    {else}
                                                        <button type="{if isset($btn['type'])}{$btn['type']}{else}button{/if}" {if isset($btn['id'])}id="{$btn['id']}"{/if} class="btn btn-default{if isset($btn['class'])} {$btn['class']}{/if}" name="{if isset($btn['name'])}{$btn['name']}{else}submitOptions{$table}{/if}"{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>{if isset($btn['icon'])}<i class="{$btn['icon']}" ></i> {/if}{$btn.title}</button>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>
                                    {/if}
                                {/block}
                            </div>

                        {/if}

                    {/foreach}

                {/block}

                {block name="other_fieldsets"}{/block}
            {/foreach}

        </form>

    {else}

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