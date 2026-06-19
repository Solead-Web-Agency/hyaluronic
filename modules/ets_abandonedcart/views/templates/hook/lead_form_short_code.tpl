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
{if $reminderType == 'email' || $reminderType == 'customer' || $reminderType == 'bar'|| $reminderType == 'browser'}
    <a href="{$lead_form.link nofilter}" rel="noreferrer noopener" class="ets-ac-lead-form-field-link">{$lead_form.name|escape:'html':'UTF-8'}</a>
{else}
    {if isset($lead_form.fields) && $lead_form.fields && isset($field_types)}
        {if isset($addTagForm) && $addTagForm}
            <form>
                <input name="id_form" type="hidden" value="{$lead_form.id_ets_abancart_form|escape:'html':'UTF-8'}" />
                {if isset($idReminder) && $idReminder}
                    <input name="id_reminder" type="hidden" value="{$idReminder|escape:'html':'UTF-8'}" />
                {/if}
                {if isset($idCart) && $idCart}
                    <input name="id_cart" type="hidden" value="{$idCart|escape:'html':'UTF-8'}" />
                {/if}

        {/if}
        <div class="ets-ac-lead-form-field-shortcode ets-ac-lead-form-field-shortcode_{$lead_form.id_ets_abancart_form|escape:'html':'UTF-8'}">
            <h3 class="lf-title">{$lead_form.name|escape:'html':'UTF-8'}</h3>
            <div class="lf-desc">{$lead_form.description nofilter}</div>
            <div class="form-errors"></div>
            {foreach $lead_form.fields as $field}
                <div class="form-group">
                    <label class="{if $field.required}required{/if}">{$field.name|escape:'html':'UTF-8'}</label>
                    <div class="ets-ac-lead-field_content">
                        {if $field_types.text.key == $field.type || $field_types.phone.key == $field.type}
                            <input class="form-control" type="text" name="field[{$field.id|escape:'html':'UTF-8'}]"
                                   value="{if isset($field.value)}{$field.value|escape:'html':'UTF-8'}{/if}" placeholder="{$field.placeholder|escape:'html':'UTF-8'}" />
                        {elseif $field_types.email.key == $field.type}
                            <input class="form-control" type="email" name="field[{$field.id|escape:'html':'UTF-8'}]"
                                   value="{if isset($field.value)}{$field.value|escape:'html':'UTF-8'}{/if}" placeholder="{$field.placeholder|escape:'html':'UTF-8'}" />
                        {elseif $field_types.number.key == $field.type}
                            <input class="form-control" type="number" name="field[{$field.id|escape:'html':'UTF-8'}]"
                                   value="{if isset($field.value)}{$field.value|escape:'html':'UTF-8'}{/if}" placeholder="{$field.placeholder|escape:'html':'UTF-8'}" />
                        {elseif $field_types.file.key == $field.type}
                            <input class="form-control" type="file" name="field[{$field.id|escape:'html':'UTF-8'}]"  />
                            {if isset($maxSizeUpload)}
                                <p class="field-file-desc">{l s='Accepted formats: jpg, png, gif, pdf, zip. Limit: %sMb' sprintf=[$maxSizeUpload] mod='ets_abandonedcart'}</p>
                            {/if}
                        {elseif $field_types.date.key == $field.type}
                            <div class="input_group">
                                <input class="form-control ets_ac_datepicker" type="text" name="field[{$field.id|escape:'html':'UTF-8'}]" autocomplete="off"
                                       value="{if isset($field.value)}{$field.value|escape:'html':'UTF-8'}{/if}" placeholder="{$field.placeholder|escape:'html':'UTF-8'}" />
                                <span class="input-group-addon">
                                    <svg style="width:14px;height:14px;color:#777777;fill:#777777;" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M192 1664h288v-288h-288v288zm352 0h320v-288h-320v288zm-352-352h288v-320h-288v320zm352 0h320v-320h-320v320zm-352-384h288v-288h-288v288zm736 736h320v-288h-320v288zm-384-736h320v-288h-320v288zm768 736h288v-288h-288v288zm-384-352h320v-320h-320v320zm-352-864v-288q0-13-9.5-22.5t-22.5-9.5h-64q-13 0-22.5 9.5t-9.5 22.5v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm736 864h288v-320h-288v320zm-384-384h320v-288h-320v288zm384 0h288v-288h-288v288zm32-480v-288q0-13-9.5-22.5t-22.5-9.5h-64q-13 0-22.5 9.5t-9.5 22.5v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm384-64v1280q0 52-38 90t-90 38h-1408q-52 0-90-38t-38-90v-1280q0-52 38-90t90-38h128v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h384v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h128q52 0 90 38t38 90z"/></svg>
                                </span>
                            </div>
                        {elseif $field_types.datetime.key == $field.type}
                            <div class="input_group">
                                <input class="form-control ets_ac_datetimepicker" id="ets_ac_datetimepicker_{$field.id|escape:'html':'UTF-8'}" autocomplete="off" type="text"
                                       name="field[{$field.id|escape:'html':'UTF-8'}]" value="{if isset($field.value)}{$field.value|escape:'html':'UTF-8'}{/if}" placeholder="{$field.placeholder|escape:'html':'UTF-8'}" />
                                <span class="input-group-addon">
                                    <svg style="width:14px;height:14px;color:#777777;fill:#777777;" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M192 1664h288v-288h-288v288zm352 0h320v-288h-320v288zm-352-352h288v-320h-288v320zm352 0h320v-320h-320v320zm-352-384h288v-288h-288v288zm736 736h320v-288h-320v288zm-384-736h320v-288h-320v288zm768 736h288v-288h-288v288zm-384-352h320v-320h-320v320zm-352-864v-288q0-13-9.5-22.5t-22.5-9.5h-64q-13 0-22.5 9.5t-9.5 22.5v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm736 864h288v-320h-288v320zm-384-384h320v-288h-320v288zm384 0h288v-288h-288v288zm32-480v-288q0-13-9.5-22.5t-22.5-9.5h-64q-13 0-22.5 9.5t-9.5 22.5v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm384-64v1280q0 52-38 90t-90 38h-1408q-52 0-90-38t-38-90v-1280q0-52 38-90t90-38h128v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h384v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h128q52 0 90 38t38 90z"/></svg>
                                </span>
                            </div>
                        {elseif $field_types.textarea.key == $field.type}
                            <textarea class="form-control" name="field[{$field.id|escape:'html':'UTF-8'}]" rows="5" placeholder="{$field.placeholder|escape:'html':'UTF-8'}">{if isset($field.value)}{$field.value|escape:'html':'UTF-8'}{/if}</textarea>
                        {elseif $field_types.select.key == $field.type}
                            <select class="form-control" name="field[{$field.id|escape:'html':'UTF-8'}]">
                                {if $field.options}
                                    {foreach $field.options as $op}
                                        <option value="{$op.value|escape:'html':'UTF-8'}" {if $op.default}selected="selected"{/if}>{$op.label|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                {/if}
                            </select>
                        {elseif $field_types.checkbox.key == $field.type}
                            <div class="checkbox-values">
                                {if $field.options}
                                    {foreach $field.options as $op}
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="field[{$field.id|escape:'html':'UTF-8'}][]" value="{$op.value|escape:'html':'UTF-8'}" {if $op.default}checked="checked"{/if} />
                                                {$op.label|escape:'html':'UTF-8'}
                                            </label>
                                        </div>
                                    {/foreach}
                                {/if}
                            </div>
                        {elseif $field_types.radio.key == $field.type}
                            <div class="radio-values">
                                {if $field.options}
                                    {foreach $field.options as $op}
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="field[{$field.id|escape:'html':'UTF-8'}]" value="{$op.value|escape:'html':'UTF-8'}" {if $op.default}checked="checked"{/if} />
                                                {$op.label|escape:'html':'UTF-8'}
                                            </label>
                                        </div>
                                    {/foreach}
                                {/if}
                            </div>
                        {/if}
                        <p class="field-desc">{$field.description|nl2br nofilter}</p>
                    </div>
                </div>
            {/foreach}
            {if $lead_form.enable_captcha}
                {if (isset($customerIsLogged) && $customerIsLogged && !$lead_form.disable_captcha_lic) || !isset($customerIsLogged) || !$customerIsLogged}
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="ets-ac-lead-field_content">
                            <div class="ets-ac-lead-form-captcha {if $lead_form.captcha_type == 'v2'}ets_ac_captchav2{else}ets_ac_captchav3{/if}" id="etAcCaptchaItem{$lead_form.id_ets_abancart_form|escape:'html':'UTF-8'}">
                                {if isset($isAdmin) && $isAdmin}
                                    {if $lead_form.captcha_type == 'v3'}
                                        <img src="{if isset($baseUri)}{$baseUri|escape:'html':'UTF-8'}{else}/{/if}modules/ets_abandonedcart/views/img/recaptchav3.png" class="img-recaptchav3" alt="image reacaptcha v3" />
                                    {else}
                                        <img src="{if isset($baseUri)}{$baseUri|escape:'html':'UTF-8'}{else}/{/if}modules/ets_abandonedcart/views/img/recaptchav2.png" class="img-recaptchav2" alt="image reacaptcha v2" />
                                    {/if}
                                {/if}
                            </div>
                            {if $lead_form.captcha_type}
                                <input type="hidden" name="captcha_type" value="{$lead_form.captcha_type|escape:'html':'UTF-8'}" />
                                <input type="hidden" name="captcha_site_key" value="{if $lead_form.captcha_type == 'v2'}{$lead_form.captcha_site_key_v2|escape:'html':'UTF-8'}{else}{$lead_form.captcha_site_key_v3|escape:'html':'UTF-8'}{/if}" />
                                {if $lead_form.captcha_type == 'v3'}
                                    <input type="hidden" name="captcha_v3_response" value="">
                                {/if}
                            {/if}
                        </div>
                    </div>
                {/if}
            {/if}
            <div class="form-group text-right mb_0">
                <button type="{if isset($formSubmit) && $formSubmit}submit{else}button{/if}" name="submitFormLead" class="ets-ac-btn-submit-lead-form {if isset($formSubmit) && $formSubmit}{else}js-ets-ac-btn-submit-lead-form{/if}">{$lead_form.btn_title|escape:'html':'UTF-8'}</button>
            </div>
            <style type="text/css">
                .ets-ac-lead-form-field-shortcode h3.lf-title {
                    margin-bottom: 8px!important;
                    border: none!important;
                    text-align: left;
                    text-transform: none!important;
                    height: auto!important;
                }
                .form-group label.required:before{
                    display: none!important;
                }
                .form-group label.required:after {
                    content: "*";
                    color: red;
                    margin-left: 5px;
                }
                .ets-ac-lead-form-field-shortcode .form-group > label {
                    color: #666;
                    width: 160px;
                    display: inline-block;
                    padding-right: 10px;
                    vertical-align: top;
                    padding-top: 8px;
                    max-width: 30%;
                    text-align: left;
                    font-weight: normal;
                }
                .ets-ac-lead-form-captcha.ets_ac_captchav2 {
                    text-align: left;
                }
                .ets-ac-lead-form-captcha {
                    text-align: right;
                }
                img{
                    max-width: 100%;
                    height: auto;
                }
                .field-desc {
                    margin: 2px;
                    color: #999999;
                    font-style: italic;
                }
                .ets-ac-lead-field_content span.input-group-addon {
                    display: table-cell;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-left: none;
                    min-width: 30px;
                    text-align: center;
                    border-top-right-radius: 3px;
                    border-bottom-right-radius: 3px;
                    vertical-align: middle;
                    padding: 0 2px;
                }
                .ets-ac-lead-field_content .ets_ac_datepicker {
                    display: table-cell;
                }
                .ets-ac-lead-field_content .input_group {
                    display: table;
                }
                .ets-ac-lead-field_content .input_group input {
                    border-top-right-radius: 0!important;
                    border-bottom-right-radius: 0!important;
                }
                .ets-ac-lead-form-field-shortcode .form-group label ~ .ets-ac-lead-field_content{
                    width: calc(100% - 170px);
                    width: -webkit-calc(100% - 170px);
                    display: inline-block;
                }
                .ets-ac-lead-form-field-shortcode input[type="text"], .ets-ac-lead-form-field-shortcode input[type="email"],
                .ets-ac-lead-form-field-shortcode input[type="file"],
                .ets-ac-lead-form-field-shortcode input[type="number"],
                .ets-ac-lead-form-field-shortcode textarea {
                    border: 1px solid #ddd;
                    border-radius: 3px;
                    min-height: 30px;
                    outline: none!important;
                    padding: 0 10px;
                    width: 100%;
                    max-width: 100%;
                    background: #ffffff;
                    outline:none!important;
                }
                .ets-ac-lead-form-field-shortcode input[type="file"]{
                    padding: 5px 10px;
                }
                .ets-ac-lead-form-field-shortcode button.ets-ac-btn-submit-lead-form {
                    border: none;
                    padding: 8px 15px;
                    border-radius: 3px;
                    margin-top: 10px;
                    text-transform: uppercase;
                }
                .ets-ac-lead-form-field-shortcode select.form-control {
                    min-height: 30px;
                    border: 1px solid #ddd;
                    width: auto;
                    min-width: 200px;
                    outline: none!important;
                    max-width: 100%;
                }
                .ets-ac-lead-form-field-shortcode .form-group {
                    margin-bottom: 15px;
                }
                .ets-ac-lead-form-field-shortcode textarea{
                    padding: 10px 15px;
                }
                .ets-ac-lead-form-field-shortcode .radio-values .radio,
                .ets-ac-lead-form-field-shortcode .checkbox-values .checkbox {
                    display: inline-block;
                    margin: 2px 5px 2px 0;
                }
                .ets-ac-lead-form-field-shortcode .checkbox-values,
                .ets-ac-lead-form-field-shortcode .radio-values {
                    display: inline-block;
                    vertical-align: middle;
                }
                .ets-ac-lead-form-field-shortcode_{$lead_form.id_ets_abancart_form|escape:'html':'UTF-8'} button.ets-ac-btn-submit-lead-form{
                    background-color: {$lead_form.btn_bg_color|escape:'html':'UTF-8'};
                    color: {$lead_form.btn_text_color|escape:'html':'UTF-8'};
                    border: 1px solid {$lead_form.btn_bg_color|escape:'html':'UTF-8'};
                }
                .ets-ac-lead-form-field-shortcode_{$lead_form.id_ets_abancart_form|escape:'html':'UTF-8'}  button.ets-ac-btn-submit-lead-form:hover,
                .ets-ac-lead-form-field-shortcode_{$lead_form.id_ets_abancart_form|escape:'html':'UTF-8'}  button.ets-ac-btn-submit-lead-form:focus{
                    background-color: {$lead_form.btn_bg_hover_color|escape:'html':'UTF-8'};
                    color: {$lead_form.btn_text_hover_color|escape:'html':'UTF-8'};
                    border: 1px solid {$lead_form.btn_bg_hover_color|escape:'html':'UTF-8'};
                    outline: none;
                }
            </style>
        </div>
        {if isset($addTagForm) && $addTagForm}
            </form>
        {/if}
    {/if}
{/if}