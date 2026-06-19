{*
* 2007-2022 ETS-Soft
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
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

<div class="tab-body">
    <div class="ets-ac-lead-list-fields" data-form-id="{$idForm|escape:'html':'UTF-8'}">
        {foreach $fields as $key=>$field}
            <div id="lead_{$field.id|escape:'html':'UTF-8'}" class="form-group form-field lead-field-item" data-id="{$field.id|escape:'html':'UTF-8'}">
                    <div class="header-field sortable">
                        <span data-toggle="collapse" href="#lead_field_{$key|escape:'html':'UTF-8'}" class="btn-pmf-collapse {if !isset($field.error)} collapsed{/if}">{$field['name'][$default_lang]|escape:'html':'UTF-8'} {if $field['required']}<small class="text-muted">({l s='Required' mod='ets_abandonedcart'})</small>{/if}
                            <span class="ets_icon_svg">
                                <svg class="svg_plus" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1600 736v192q0 40-28 68t-68 28h-416v416q0 40-28 68t-68 28h-192q-40 0-68-28t-28-68v-416h-416q-40 0-68-28t-28-68v-192q0-40 28-68t68-28h416v-416q0-40 28-68t68-28h192q40 0 68 28t28 68v416h416q40 0 68 28t28 68z"></path></svg>
                                <svg class="svg_minus" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1600 736v192q0 40-28 68t-68 28h-1216q-40 0-68-28t-28-68v-192q0-40 28-68t68-28h1216q40 0 68 28t28 68z"></path></svg>
                            </span>
                        </span>
                        <a class="btn btn-default btn-sm btn-delete-lead-field js-ets-ac-btn-delete-lead-field" data-id="{$field.id|escape:'html':'UTF-8'}" href="javascript:void(0)">
                            <i class="ets_svg_fill_gray ets_svg_hover_fill_white">
                                <svg width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M704 736v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm128 724v-948h-896v948q0 22 7 40.5t14.5 27 10.5 8.5h832q3 0 10.5-8.5t14.5-27 7-40.5zm-672-1076h448l-48-117q-7-9-17-11h-317q-10 2-17 11zm928 32v64q0 14-9 23t-23 9h-96v948q0 83-47 143.5t-113 60.5h-832q-66 0-113-58.5t-47-141.5v-952h-96q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h309l70-167q15-37 54-63t79-26h320q40 0 79 26t54 63l70 167h309q14 0 23 9t9 23z"/></svg>
                            </i> {l s='Delete' mod='ets_abandonedcart'}</a>
                    </div>
                    <div class="group-fields{if !isset($field.error)} collapse{else} in{/if}" id="lead_field_{$key|escape:'html':'UTF-8'}">
                        <div class="form-group row">
                            <label class="control-label required col-lg-3">{l s='Field title' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-5">
                                {foreach $languages as $k=>$lang}
                                    <div class="form-group row trans_field trans_field_{$lang.id_lang|escape:'html':'UTF-8'} {if $lang.id_lang != $default_lang}hidden{/if}">
                                        <div class="col-lg-9">
                                            <input type="text" name="lead_field[{$key|escape:'html':'UTF-8'}][name][{$lang.id_lang|escape:'html':'UTF-8'}]" value="{if isset($field['name'][$lang.id_lang])}{$field['name'][$lang.id_lang]|escape:'html':'UTF-8'}{/if}" class="form-control required" data-error="{l s='Field title is required' mod='ets_abandonedcart'}" />
                                        </div>
                                        {if count($languages) > 1}
                                            <div class="col-lg-2">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">{$lang.iso_code|escape:'html':'UTF-8'} <span class="caret"></span></button>
                                                <ul class="dropdown-menu">
                                                    {foreach $languages as $lg}
                                                        <li><a href="javascript:etsAcHideOtherLanguage({$lg.id_lang|escape:'html':'UTF-8'})" title="">{$lg.name|escape:'html':'UTF-8'}</a></li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="control-label col-lg-3">{l s='Description' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-5">
                                {foreach $languages as $k=>$lang}
                                    <div class="form-group row trans_field trans_field_{$lang.id_lang|escape:'html':'UTF-8'} {if $lang.id_lang != $default_lang}hidden{/if}">
                                        <div class="col-lg-9">
                                            <textarea name="lead_field[{$key|escape:'html':'UTF-8'}][description][{$lang.id_lang|escape:'html':'UTF-8'}]" class="form-control">{if isset($field['description'][$lang.id_lang])}{$field['description'][$lang.id_lang]|escape:'html':'UTF-8'}{/if}</textarea>
                                        </div>
                                        {if count($languages) > 1}
                                            <div class="col-lg-2">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">{$lang.iso_code|escape:'html':'UTF-8'} <span class="caret"></span></button>
                                                <ul class="dropdown-menu">
                                                    {foreach $languages as $lg}
                                                        <li><a href="javascript:etsAcHideOtherLanguage({$lg.id_lang|escape:'html':'UTF-8'})" title="">{$lg.name|escape:'html':'UTF-8'}</a></li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        <div class="form-group row field_type">
                            <label class="control-label col-lg-3">{l s='Field type' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-3">
                                <select name="lead_field[{$key|escape:'html':'UTF-8'}][type]" class="form-control js-ets-ac-field-type-input">
                                    {if isset($field_types)}
                                        {foreach $field_types as $k=>$item}
                                            <option value="{$item.key|escape:'html':'UTF-8'}" data-type="{$k|escape:'html':'UTF-8'}" {if $item.key == $field.type}selected="selected"{/if} >{$item.title|escape:'html':'UTF-8'}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>
                        </div>
                        <div class="form-group row placeholder {if $field.type == $field_types['file'].key || $field.type == $field_types['select'].key || $field.type == $field_types['checkbox'].key || $field.type == $field_types['radio'].key}hide{/if}">
                            <label class="control-label col-lg-3">{l s='Place holder' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-5">
                                {foreach $languages as $k=>$lang}
                                    <div class="form-group row trans_field trans_field_{$lang.id_lang|escape:'html':'UTF-8'} {if $lang.id_lang != $default_lang}hidden{/if}">
                                        <div class="col-lg-9">
                                            <input type="text" name="lead_field[{$key|escape:'html':'UTF-8'}][placeholder][{$lang.id_lang|escape:'html':'UTF-8'}]" value="{if isset($field['placeholder'][$lang.id_lang])}{$field['placeholder'][$lang.id_lang]|escape:'html':'UTF-8'}{/if}" class="form-control" data-error="" />
                                        </div>
                                        {if count($languages) > 1}
                                            <div class="col-lg-2">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">{$lang.iso_code|escape:'html':'UTF-8'} <span class="caret"></span></button>
                                                <ul class="dropdown-menu">
                                                    {foreach $languages as $lg}
                                                        <li><a href="javascript:etsAcHideOtherLanguage({$lg.id_lang|escape:'html':'UTF-8'})" title="">{$lg.name|escape:'html':'UTF-8'}</a></li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        <div class="form-group row options {if $field.type == $field_types.radio.key || $field.type == $field_types.checkbox.key || $field.type == $field_types.select.key} {else}hide{/if}" >
                            <label class="control-label required col-lg-3">{l s='Options' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-5">
                                {foreach $languages as $k=>$lang}
                                    <div class="form-group row trans_field trans_field_{$lang.id_lang|escape:'html':'UTF-8'} {if $lang.id_lang != $default_lang}hidden{/if}">
                                        <div class="col-lg-9">
                                            <textarea name="lead_field[{$key|escape:'html':'UTF-8'}][content][{$lang.id_lang|escape:'html':'UTF-8'}]" class="form-control">{if isset($field['content'][$lang.id_lang])}{$field['content'][$lang.id_lang]|escape:'html':'UTF-8'}{/if}</textarea>
                                            <p class="help-block"> {l s='Each value on 1 line. It also allows to set custom label, custom value and default value following this structure: label|value:default' mod='ets_abandonedcart'} </p>
                                        </div>
                                        {if count($languages) > 1}
                                            <div class="col-lg-2">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">{$lang.iso_code|escape:'html':'UTF-8'} <span class="caret"></span></button>
                                                <ul class="dropdown-menu">
                                                    {foreach $languages as $lg}
                                                        <li><a href="javascript:etsAcHideOtherLanguage({$lg.id_lang|escape:'html':'UTF-8'})" title="">{$lg.name|escape:'html':'UTF-8'}</a></li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        <div class="form-group row ets_ac_is_contact_name {if $field.type !== $field_types.text.key}hide{/if}">
                            <label class="control-label col-lg-3">{l s='Is contact name' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-3">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][is_contact_name]" id="lead_field_{$key|escape:'html':'UTF-8'}_is_contact_name_on" value="1" class="lead_field_is_contact_name" {if $field.is_contact_name == 1}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_is_contact_name_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][is_contact_name]" id="lead_field_{$key|escape:'html':'UTF-8'}_is_contact_name_off" class="lead_field_is_contact_name" value="0" {if $field.is_contact_name == 0}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_is_contact_name_off">{l s='No' mod='ets_abandonedcart'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>

                        <div class="form-group row ets_ac_is_contact_email {if $field.type != $field_types.email.key}hide{/if}">
                            <label class="control-label col-lg-3">{l s='Is contact email' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-3">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][is_contact_email]" id="lead_field_{$key|escape:'html':'UTF-8'}_is_contact_email_on" value="1" class="lead_field_is_contact_email" {if $field.is_contact_email == 1}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_is_contact_email_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][is_contact_email]" id="lead_field_{$key|escape:'html':'UTF-8'}_is_contact_email_off" class="lead_field_is_contact_email" value="0" {if $field.is_contact_email == 0}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_is_contact_email_off">{l s='No' mod='ets_abandonedcart'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row form_group_required">
                            <label class="control-label col-lg-3">{l s='Required' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-3">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][required]" id="lead_field_{$key|escape:'html':'UTF-8'}_required_on" value="1" class="lead_field_required" {if $field.required == 1}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_required_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][required]" id="lead_field_{$key|escape:'html':'UTF-8'}_required_off" class="lead_field_required" value="0" {if $field.required == 0}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_required_off">{l s='No' mod='ets_abandonedcart'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row ">
                            <label class="control-label col-lg-3">{l s='Display column' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][display_column]" id="lead_field_{$key|escape:'html':'UTF-8'}_display_column_on" value="1" class="lead_field_display_column" {if $field.display_column == 1}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_display_column_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][display_column]" id="lead_field_{$key|escape:'html':'UTF-8'}_display_column_off" class="lead_field_display_column" value="0" {if $field.display_column == 0}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_display_column_off">{l s='No' mod='ets_abandonedcart'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row ">
                            <label class="control-label col-lg-3">{l s='Enabled' mod='ets_abandonedcart'}</label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][enable]" id="lead_field_{$key|escape:'html':'UTF-8'}_enable_on" value="1" class="lead_field_enable" {if $field.enable == 1}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_enable_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                                    <input type="radio" name="lead_field[{$key|escape:'html':'UTF-8'}][enable]" id="lead_field_{$key|escape:'html':'UTF-8'}_enable_off" class="lead_field_enable" value="0" {if $field.enable == 0}checked="checked"{/if} />
                                    <label for="lead_field_{$key|escape:'html':'UTF-8'}_enable_off">{l s='No' mod='ets_abandonedcart'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <input type="hidden" name="lead_field[{$key|escape:'html':'UTF-8'}][id]" value="{$field.id|escape:'html':'UTF-8'}" />
                    </div>
                </div>
        {/foreach}
    </div>
    <div class="form-group">
        <button class="btn btn-default ets-ac-btn-add-field js-ets-ac-btn-add-field">
            <i class="ets_svg_icon ets_svg_fill_gray ets_svg_hover_fill_white lh_16">
                <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1600 736v192q0 40-28 68t-68 28h-416v416q0 40-28 68t-68 28h-192q-40 0-68-28t-28-68v-416h-416q-40 0-68-28t-28-68v-192q0-40 28-68t68-28h416v-416q0-40 28-68t68-28h192q40 0 68 28t28 68v416h416q40 0 68 28t28 68z"/></svg>
            </i>
            {l s='Add new field' mod='ets_abandonedcart'}
        </button>
    </div>
</div>