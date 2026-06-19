{*
* 2007-2020 ETS-Soft
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
*  @copyright  2007-2020 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

{extends file="helpers/form/form.tpl"}
{block name="fieldset"}
    {if !isset($isMultipleLanguage) || !$isMultipleLanguage}
        <div class="alert alert-warning">
            {l s='Translation is not available because your website has not supported multi-language yet.' mod='ets_translate'} <a href="{$linkToConfigLang|escape:'quotes':'UTF-8'}">{l s='Install new language' mod='ets_translate'}</a>
        </div>
    {/if}
    {$smarty.block.parent}
{/block}
{block name="legend"}
    <div class="panel-heading">
        {if isset($field.image) && isset($field.title)}<img src="{$field.image|escape:'quotes':'UTF-8'}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
        {if isset($field.icon)}<i class="{$field.icon|escape:'html':'UTF-8'}"></i>{/if}
        {$field.title|escape:'html':'UTF-8'}
        {if isset($fieldset) && isset($fieldset['form']['submit']['name']) && $fieldset['form']['submit']['name'] == 'saveEtstransSettings'}
            <a href="{if isset($linkToConfigWd)}{$linkToConfigWd|escape:'quotes':'UTF-8'}{/if}" class="btn btn-default btn-sm ets-trans-link-to-config-wd pull-right">
                <i class="fa fa-language"></i>
                {l s='1-Click translate' mod='ets_translate'}
            </a>
        {/if}
    </div>
{/block}
{block name='input_row'}
    {if $input.name == 'ETS_TRANS_WD_CONFIG'}
        <div class="form-group">
            <div class="col-lg-12">
                <div class="alert alert-info">
                    {l s='Here you can quickly do bulk translation for any kind of data you want or 1-click to translate the whole website.' mod='ets_translate'}
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3"><b>{l s='Data to translate' mod='ets_translate'}: </b></label>
            <div class="col-lg-9">
                <ul class="ets-trans-tree-web-page">
                    {foreach $treeWebPageOption as $op}
                        <li>
                            <input type="checkbox" id="{$op.name|escape:'html':'UTF-8'}" name="ETS_TRANS_WD_CONFIG[]"
                                   value="{$op.name|escape:'html':'UTF-8'}"
                                   {if in_array($op.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if}/>
                            <label for="{$op.name|escape:'html':'UTF-8'}">{$op.title|escape:'html':'UTF-8'}</label>
                            {if isset($op.items) && $op.items}
                                <a data-toggle="collapse" class="collapsed"  href="#cp_{$op.name|escape:'html':'UTF-8'}"><i
                                            class="fa fa-angle-down"></i></a>
                                <ul class="sub-tree collapse" id="cp_{$op.name|escape:'html':'UTF-8'}">
                                    {foreach $op.items as $op2}
                                        <li>
                                            <input type="checkbox" id="{$op2.name|escape:'html':'UTF-8'}"
                                                   name="ETS_TRANS_WD_CONFIG[]"
                                                   value="{$op2.name|escape:'html':'UTF-8'}"
                                                   {if in_array($op2.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if}/>
                                            <label for="{$op2.name|escape:'html':'UTF-8'}">{$op2.title|escape:'html':'UTF-8'}</label>
                                            {if isset($op2.items) && $op2.items}
                                                <a data-toggle="collapse" class="collapsed"
                                                   href="#cp_{$op2.name|escape:'html':'UTF-8'}"><i
                                                            class="fa fa-angle-down"></i></a>
                                                <ul class="sub-tree collapse" id="cp_{$op2.name|escape:'html':'UTF-8'}">
                                                    {foreach $op2.items as $op3}
                                                        <li>
                                                            <input type="checkbox"
                                                                   id="{$op3.name|escape:'html':'UTF-8'}"
                                                                   name="ETS_TRANS_WD_CONFIG[]"
                                                                   value="{$op3.name|escape:'html':'UTF-8'}"
                                                                   {if in_array($op3.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                            <label for="{$op3.name|escape:'html':'UTF-8'}">{$op3.title|escape:'html':'UTF-8'}</label>
                                                            {if isset($op3.items) && $op3.items}
                                                                <a data-toggle="collapse" class="collapsed"
                                                                   href="#cp_{$op3.name|escape:'html':'UTF-8'}"><i
                                                                            class="fa fa-angle-down"></i></a>
                                                                <ul class="sub-tree collapse"
                                                                    id="cp_{$op3.name|escape:'html':'UTF-8'}">
                                                                    {foreach $op3.items as $op4}
                                                                        <li>
                                                                            <input type="checkbox"
                                                                                   id="{$op4.name|escape:'html':'UTF-8'}"
                                                                                   name="ETS_TRANS_WD_CONFIG[]"
                                                                                   value="{$op4.name|escape:'html':'UTF-8'}"
                                                                                   {if in_array($op4.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                                            <label for="{$op4.name|escape:'html':'UTF-8'}">{$op4.title|escape:'html':'UTF-8'}</label>
                                                                            {if isset($op4.items) && $op4.items}
                                                                                <a data-toggle="collapse" class="collapsed"
                                                                                   href="#cp_{$op4.name|escape:'html':'UTF-8'}"><i
                                                                                            class="fa fa-angle-down"></i></a>
                                                                                <ul class="sub-tree collapse"
                                                                                    id="cp_{$op4.name|escape:'html':'UTF-8'}">
                                                                                    {foreach $op4.items as $op5}
                                                                                        <li>
                                                                                            <input type="checkbox"
                                                                                                   id="{$op5.name|escape:'html':'UTF-8'}"
                                                                                                   name="ETS_TRANS_WD_CONFIG[]"
                                                                                                   value="{$op5.name|escape:'html':'UTF-8'}"
                                                                                                   {if in_array($op5.name, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                                                            <label for="{$op5.name|escape:'html':'UTF-8'}">{$op5.title|escape:'html':'UTF-8'}</label>
                                                                                            {if isset($op5.emails) && $op5.emails}
                                                                                                <a data-toggle="collapse" class="collapsed"
                                                                                                   href="#cp_{$op5.name|escape:'html':'UTF-8'}"><i
                                                                                                            class="fa fa-angle-down"></i></a>
                                                                                                <ul class="sub-tree collapse"
                                                                                                    id="cp_{$op5.name|escape:'html':'UTF-8'}">
                                                                                                    {foreach $op5.emails as $mailItem}
                                                                                                        <li>
                                                                                                            <input type="checkbox"
                                                                                                                   id="{$mailItem.val|escape:'html':'UTF-8'}"
                                                                                                                   name="ETS_TRANS_WD_CONFIG[]"
                                                                                                                   value="{$mailItem.val|escape:'html':'UTF-8'}"
                                                                                                                   {if in_array($mailItem.val, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                                                                            <label for="{$mailItem.val|escape:'html':'UTF-8'}">
                                                                                                                ({if $mailItem.type == 'core_email'}{l s='Core email' mod='ets_translate'}{else}{l s='Module:' mod='ets_translate'} {$mailItem.name|escape:'html':'UTF-8'}{/if}
                                                                                                                ) {$mailItem.file|escape:'html':'UTF-8'}
                                                                                                            </label>
                                                                                                        </li>
                                                                                                    {/foreach}
                                                                                                </ul>
                                                                                            {/if}
                                                                                        </li>
                                                                                    {/foreach}
                                                                                </ul>
                                                                            {/if}
                                                                            {if isset($op4.emails) && $op4.emails}
                                                                                <a data-toggle="collapse" class="collapsed"
                                                                                   href="#em_{$op4.name|escape:'html':'UTF-8'}"><i
                                                                                            class="fa fa-angle-down"></i></a>
                                                                                <ul class="sub-tree collapse"
                                                                                    id="em_{$op4.name|escape:'html':'UTF-8'}">
                                                                                    {foreach $op4.emails as $mailItem}
                                                                                        <li>
                                                                                            <input type="checkbox"
                                                                                                   id="{$mailItem.val|escape:'html':'UTF-8'}"
                                                                                                   name="ETS_TRANS_WD_CONFIG[]"
                                                                                                   value="{$mailItem.val|escape:'html':'UTF-8'}"
                                                                                                   {if in_array($mailItem.val, $ETS_TRANS_WD_CONFIG)}checked="checked"{/if} />
                                                                                            <label for="{$mailItem.val|escape:'html':'UTF-8'}">
                                                                                                ({if $mailItem.type == 'core_email'}{l s='Core email' mod='ets_translate'}{else}{l s='Module:' mod='ets_translate'} {$mailItem.name|escape:'html':'UTF-8'}{/if}
                                                                                                ) {$mailItem.file|escape:'html':'UTF-8'}
                                                                                            </label>
                                                                                        </li>
                                                                                    {/foreach}
                                                                                </ul>
                                                                            {/if}
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            {/if}
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            {/if}
                                        </li>
                                    {/foreach}
                                </ul>
                            {/if}
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {elseif $input.name == 'ETS_TRANS_SUFFIX_RATE_GOOGLE'}
{*        Do nothing*}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name='input'}
    {if $input.name == 'ETS_TRANS_LANG_SOURCE'}
        <input type="hidden" name="ETS_TRANS_LANG_SOURCE" id="ETS_TRANS_LANG_SOURCE"
               value="{$fields_value['ETS_TRANS_LANG_SOURCE']|escape:'html':'UTF-8'}"/>
        <div class="dropdown" id="dropdown_ETS_TRANS_LANG_SOURCE">
            <button class="btn btn-default dropdown-toggle" type="button" id="btn_ETS_TRANS_LANG_SOURCE"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <span class="content-lang">
                    {foreach $langWithFlag as $op}
                        {if $op.id_lang == $fields_value['ETS_TRANS_LANG_SOURCE']}
                            <img src="{$op.flag|escape:'quotes':'UTF-8'}" class="lang-flag"/>
                            {$op.name|escape:'html':'UTF-8'}
                        {/if}
                    {/foreach}
                </span>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="btn_ETS_TRANS_LANG_SOURCE">
                {foreach $langWithFlag as $op}
                    <li>
                        <a href="#" class="js-ets-trans-choose-lang-source-item"
                           data-lang="{$op.id_lang|escape:'html':'UTF-8'}">
                            <img src="{$op.flag|escape:'quotes':'UTF-8'}" class="lang-flag"/> {$op.name|escape:'html':'UTF-8'}
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    {elseif $input.name == 'ETS_TRANS_LANG_TARGET'}
        <div class="ets-trans-checkbox-dropdown">
            <div class="ets_dropdown">
                <button type="button" id="langTargetDropdownBo" class="btn btn-default">
                    <span class="text-html {if count($ETS_TRANS_LANG_TARGET) == 1}single-lang{/if}">
                        {if !$ETS_TRANS_LANG_TARGET || count($langWithFlag) <=1}
                            --
                        {else}
                            {foreach $langWithFlag as $op}
                                {if in_array($op.id_lang, $ETS_TRANS_LANG_TARGET) && $op.flag}
                                    <img src="{$op.flag|escape:'quotes':'UTF-8'}"/><span>{if count($ETS_TRANS_LANG_TARGET) == 1}{$op.name|escape:'html':'UTF-8'}{else}{$op.iso_code|escape:'html':'UTF-8'}{/if}</span>{if $op.id_lang != $ETS_TRANS_LANG_TARGET[count($ETS_TRANS_LANG_TARGET)-1]}, {/if}
                                {/if}
                            {/foreach}
                        {/if}
                    </span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="langTargetDropdownBo">
                    <li>
                        <div class="checkbox-item checkbox-all">
                            <input type="checkbox" name="ETS_TRANS_LANG_TARGET_ALL" id="ETS_TRANS_LANG_TARGET_all"
                                   value="1" {if count($ETS_TRANS_LANG_TARGET) == count($langTarget)-1}checked="checked"{/if} />
                            <label for="ETS_TRANS_LANG_TARGET_all">{l s='All languages' mod='ets_translate'}</label>
                        </div>
                    </li>
                    {foreach $langWithFlag as $op}
                        <li class="{if $fields_value['ETS_TRANS_LANG_SOURCE'] == $op.id_lang}hide{/if}">
                            <div class="checkbox-item checkbox-option">
                                <input type="checkbox" name="ETS_TRANS_LANG_TARGET[]"
                                       id="ETS_TRANS_LANG_TARGET_{$op.id_lang|escape:'html':'UTF-8'}"
                                       value="{$op.id_lang|escape:'html':'UTF-8'}"
                                       {if in_array($op.id_lang, $ETS_TRANS_LANG_TARGET)}checked="checked"{/if} data-isocode="{$op.iso_code|escape:'html':'UTF-8'}" />
                                <label for="ETS_TRANS_LANG_TARGET_{$op.id_lang|escape:'html':'UTF-8'}">
                                    <img src="{$op.flag|escape:'quotes':'UTF-8'}"/>{$op.name|escape:'html':'UTF-8'}
                                </label>
                            </div>
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {elseif $input.name == 'ETS_TRANS_PAGE_APPEND_CONTEXT_WORD'}
        <p class="checkbox">
            <label>
                <input type="checkbox" name="" id="ETS_TRANS_PAGE_APPEND_CONTEXT_WORD_all" value="all" {if count($ETS_TRANS_PAGE_APPEND_CONTEXT_WORD) == count($pageAppendContextWords)}checked="checked"{/if} />
                {l s='All pages' mod='ets_translate'}
            </label>
        </p>
        {foreach $pageAppendContextWords as $pageItem}
            <p class="checkbox">
                <label>
                    <input type="checkbox" name="ETS_TRANS_PAGE_APPEND_CONTEXT_WORD[]" value="{$pageItem.value|escape:'html':'UTF-8'}" {if in_array($pageItem.value, $ETS_TRANS_PAGE_APPEND_CONTEXT_WORD)}checked="checked"{/if} />
                    {$pageItem.title|escape:'html':'UTF-8'}
                </label>
            </p>
        {/foreach}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}