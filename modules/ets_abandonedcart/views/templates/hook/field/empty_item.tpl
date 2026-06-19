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
<div class="ets_ac_new_fields hide">
    <div class="group-fields lead-field-item">
        <div class="form-group row">
            <label class="control-label required col-lg-3">{l s='Field title' mod='ets_abandonedcart'}</label>
            <div class="col-lg-5">
                {foreach $languages as $k=>$lang}
                    <div class="form-group row trans_field trans_field_{$lang.id_lang|escape:'html':'UTF-8'} {if $lang.id_lang != $default_lang}hidden{/if}">
                        <div class="col-lg-9">
                            <input type="text" name="lead_field[(new_id_field)][name][{$lang.id_lang|escape:'html':'UTF-8'}]" value="" class="form-control required" data-error="{l s='Field title is required' mod='ets_abandonedcart'}" />
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
                            <textarea name="lead_field[(new_id_field)][description][{$lang.id_lang|escape:'html':'UTF-8'}]" class="form-control"></textarea>
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
                <select name="lead_field[(new_id_field)][type]" class="form-control js-ets-ac-field-type-input">
                    {if isset($field_types)}
                        {foreach $field_types as $k=>$item}
                            <option value="{$item.key|escape:'html':'UTF-8'}" data-type="{$k|escape:'html':'UTF-8'}" >{$item.title|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
        <div class="form-group row placeholder">
            <label class="control-label col-lg-3">{l s='Placeholder' mod='ets_abandonedcart'}</label>
            <div class="col-lg-5">
                {foreach $languages as $k=>$lang}
                    <div class="form-group row trans_field trans_field_{$lang.id_lang|escape:'html':'UTF-8'} {if $lang.id_lang != $default_lang}hidden{/if}">
                        <div class="col-lg-9">
                            <input type="text" name="lead_field[(new_id_field)][placeholder][{$lang.id_lang|escape:'html':'UTF-8'}]" value="" class="form-control required" data-error="" />
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
        <div class="form-group row options hide" >
            <label class="control-label required col-lg-3">{l s='Options' mod='ets_abandonedcart'}</label>
            <div class="col-lg-5">
                {foreach $languages as $k=>$lang}
                    <div class="form-group row trans_field trans_field_{$lang.id_lang|escape:'html':'UTF-8'} {if $lang.id_lang != $default_lang}hidden{/if}">
                        <div class="col-lg-9">
                            <textarea name="lead_field[(new_id_field)][content][{$lang.id_lang|escape:'html':'UTF-8'}]" class="form-control"></textarea>
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
        <div class="form-group row ets_ac_is_contact_name">
            <label class="control-label col-lg-3">{l s='Is contact name' mod='ets_abandonedcart'}</label>
            <div class="col-lg-3">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="lead_field[(new_id_field)][is_contact_name]" id="lead_field_(new_id_field)_is_contact_name_on" value="1" class="lead_field_is_contact_name" />
                    <label for="lead_field_(new_id_field)_is_contact_name_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                    <input type="radio" name="lead_field[(new_id_field)][is_contact_name]" id="lead_field_(new_id_field)_is_contact_name_off" class="lead_field_is_contact_name" value="0" checked="checked" />
                    <label for="lead_field_(new_id_field)_is_contact_name_off">{l s='No' mod='ets_abandonedcart'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="form-group row ets_ac_is_contact_email hide">
            <label class="control-label col-lg-3">{l s='Is contact email' mod='ets_abandonedcart'}</label>
            <div class="col-lg-3">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="lead_field[(new_id_field)][is_contact_email]" id="lead_field_(new_id_field)_is_contact_email_on" value="1" class="lead_field_is_contact_email" />
                    <label for="lead_field_(new_id_field)_is_contact_email_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                    <input type="radio" name="lead_field[(new_id_field)][is_contact_email]" id="lead_field_(new_id_field)_is_contact_email_off" class="lead_field_is_contact_email" value="0" checked="checked"/>
                    <label for="lead_field_(new_id_field)_is_contact_email_off">{l s='No' mod='ets_abandonedcart'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="form-group row ">
            <label class="control-label col-lg-3">{l s='Required' mod='ets_abandonedcart'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="lead_field[(new_id_field)][required]" id="lead_field_(new_id_field)_required_on" value="1" class="lead_field_required"  />
                    <label for="lead_field_(new_id_field)_required_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                    <input type="radio" name="lead_field[(new_id_field)][required]" id="lead_field_(new_id_field)_required_off" class="lead_field_required" checked="checked" value="0" />
                    <label for="lead_field_(new_id_field)_required_off">{l s='No' mod='ets_abandonedcart'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="form-group row ">
            <label class="control-label col-lg-3">{l s='Display column' mod='ets_abandonedcart'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="lead_field[(new_id_field)][display_column]" id="lead_field_(new_id_field)_display_column_on" value="1" class="lead_field_display_column" checked="checked" />
                    <label for="lead_field_(new_id_field)_display_column_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                    <input type="radio" name="lead_field[(new_id_field)][display_column]" id="lead_field_(new_id_field)_display_column_off" class="lead_field_display_column" value="0" />
                    <label for="lead_field_(new_id_field)_display_column_off">{l s='No' mod='ets_abandonedcart'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div><div class="form-group row ">
            <label class="control-label col-lg-3">{l s='Enabled' mod='ets_abandonedcart'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="lead_field[(new_id_field)][enable]" id="lead_field_(new_id_field)_enable_on" value="1" class="lead_field_enable" checked="checked" />
                    <label for="lead_field_(new_id_field)_enable_on">{l s='Yes' mod='ets_abandonedcart'}</label>
                    <input type="radio" name="lead_field[(new_id_field)][enable]" id="lead_field_(new_id_field)_enable_off" class="lead_field_enable" value="0" />
                    <label for="lead_field_(new_id_field)_enable_off">{l s='No' mod='ets_abandonedcart'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
    </div>
</div>