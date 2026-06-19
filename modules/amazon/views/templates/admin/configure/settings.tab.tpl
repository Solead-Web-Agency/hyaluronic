{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 *}

{if isset($settings.marketplace.config)}

    {foreach from=$settings.locales.config key=id_lang item=locale}
        <!-- marketplace configuration section -->
        {assign var="marketplace" value=$settings.marketplace.config[$id_lang]}

        <!-- div menudiv - lang -->
        <div id="menudiv-{$locale.iso_code|escape:'htmlall':'UTF-8'}"
             class="tabItem {if ($locale.iso_code == $settings.locales.selected_tab)}selected{/if} panel form-horizontal amz_marketplace_settings">

            <h3>{$locale.name|escape:'htmlall':'UTF-8'}
                &nbsp;{if $locale.region}&nbsp;&gt;&nbsp;Amazon - {$locale.region|escape:'htmlall':'UTF-8'}{/if}</h3>
            <input type="hidden" name="id_lang" id="lang-{$id_lang|intval}"
                   value="{$locale.iso_code|escape:'htmlall':'UTF-8'}"/>
            <input type="hidden" id="id-lang-{$locale.iso_code|escape:'htmlall':'UTF-8'}" class="margin-form col-lg-9"
                   value="{$id_lang|intval}"/>

            {if !$amazon.is_lite}
            <div class="form-group">
                <div class="margin-form">
                    <div class="amz-info-level-info {if $psIsGt15}alert alert-info col-lg-offset-3{/if}"
                         style="font-size:1.1em">
                        <ul>
                            <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                            <li>{$settings.tutorial_1|escape:'quotes':'UTF-8'}</li>
                        </ul>
                    </div>
                </div>
            </div>
            {/if}

            <div class="form-group">
                <label class="control-label col-lg-3" style="color:grey;">{l s='Active' mod='amazon'}</label>

                <div class="margin-form col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="actives[{$id_lang|intval}]"
                               id="active-{$locale.iso_code|escape:'htmlall':'UTF-8'}" rel="1" value="1"
                               {if ($marketplace.active)}checked{/if} /><label
                                for="active-{$locale.iso_code|escape:'htmlall':'UTF-8'}"
                                class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                        <input type="radio" name="actives[{$id_lang|intval}]"
                               id="active2-{$locale.iso_code|escape:'htmlall':'UTF-8'}" rel="1" value="0"
                               {if !($marketplace.active)}checked{/if} /><label
                                for="active2-{$locale.iso_code|escape:'htmlall':'UTF-8'}"
                                class="label-checkbox">{l s='No' mod='amazon'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
                {if $psIsGt15}<br/><br/>{/if}
                <label class="control-label col-lg-3">{l s='Platform' mod='amazon'}</label>

                <div class="margin-form col-lg-9">
                    <select name="marketPlaceRegion[{$id_lang|intval}]" style="width:340px; display: inline;" class="marketPlaceRegion">
                        <option disabled="disabled">{l s='Choose the platform for this region' mod='amazon'}</option>
                        <option value=""></option>
                        {foreach from=$settings.locales.platforms key=iso_code item=platform}
                            <option value="{$iso_code|escape:'htmlall':'UTF-8'}"
                                    {if ($iso_code == $locale.platform_selected)}selected{/if}>{$platform|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    {if $locale.platform_selected_required}<span
                            class="mandatory">{l s='Required' mod='amazon'}</span>{/if}

                </div>
                {if $psIsGt15}<br/><br/>{/if}
                <label class="control-label col-lg-3">{l s='Currency' mod='amazon'}</label>

                <div class="margin-form col-lg-9">

                    <select name="marketPlaceCurrency[{$id_lang|intval}]" style="width:340px; display: inline;" class="marketPlaceCurrency">
                        <option disabled="disabled">{l s='Choose the currency for this region' mod='amazon'}</option>
                        <option value=""></option>
                        {foreach from=$settings.locales.currencies item=currency}
                            <option value="{$currency.iso_code|escape:'htmlall':'UTF-8'}"
                                    {if ($currency.iso_code == $locale.currency)}selected{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    {if $locale.currency_required}<span class="mandatory">{l s='Required' mod='amazon'}</span>{/if}

                    <div class="change-locales">

                    </div>
                </div>

                {if $parameters.settings.inactive_languages}
                    {if $psIsGt15}<br/><br/>{/if}
                    <label class="control-label col-lg-3">{l s='Alternative language' mod='amazon'}</label>

                    <div class="margin-form col-lg-9">

                        <select name="alternative_languages[{$id_lang|intval}]" style="width:340px; display: inline;" class="marketPlaceAlternativeLang">
                            <option disabled="disabled">{l s='Choose the alternative language for this region' mod='amazon'}</option>
                            <option value=""></option>
                            {foreach from=$parameters.settings.languages item=language}
                                <option value="{$language.id_lang|intval}"
                                        {if ($language.id_lang|intval == $settings.alternative_languages[$id_lang]|intval)}selected{/if}>{$language.name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                {/if}
            </div>

            {include file=$module_path|cat:"views/templates/admin/configure/separator.tpl" big=false}

            <div {if (!$marketplace.active)}style="display:none"{/if}>

                {if isset($marketplace.sp_api) && $marketplace.sp_api.enable}
                    <div class="form-group sp_api_authorization">
                        <div class="row">
                            <label class="control-label col-lg-3" style="color:grey;">
                                <span>{l s='Seller Partner API' mod='amazon'}</span></label>
                            <div class="margin-form col-lg-9">
                                &nbsp;{if $psIsGt15}<br><br>{/if}
                            </div>
                        </div>
                        <div class="row">
                            <label class="control-label col-lg-3">{l s='Country/Language' mod='amazon'}</label>
                            <div class="margin-form col-lg-9">
                                <span>{$marketplace.flag|escape:'quotes':'UTF-8'}</span>
                                {if $psIsGt15}<br><br>{/if}
                            </div>
                        </div>
                        {* todo: Marketplace ID as well *}
                        {if $marketplace.sp_api.is_authorized}
                            <div class="row">
                                <label class="control-label col-lg-3">{l s='Authorization Status' mod='amazon'}</label>
                                <div class="margin-form col-lg-9">
                                <span>
                                    <img src="{$images_url|cat:'checked.png'}" alt="Authorized" title="Authorized"/>
                                    <a class="btn btn-default" href="{$marketplace.sp_api.oauth_uri|escape:'htmlall':'UTF-8'}">Reauthorize</a>
                                </span>
                                {if $psIsGt15}<br><br>{/if}
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-lg-3">{l s='Seller ID' mod='amazon'}</label>
                                <div class="margin-form col-lg-9">
                                    <span>{$marketplace.sp_api.seller_id|escape:'quotes':'UTF-8'}</span>
                                    {if $psIsGt15}<br><br>{/if}
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-lg-3">{l s='API Check' mod='amazon'}</label>
                                <div class="margin-form col-lg-9">
                                    <button type="button" class="btn btn-default sp_check_connection"
                                            data-marketplace-id="{$marketplace.sp_api.marketplace_id|escape:'quotes':'UTF-8'}"
                                            data-lang-id="{$id_lang|intval}">
                                        {l s='Check Connectivity' mod='amazon'}
                                    </button>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                                         alt="{l s='Check Connectivity' mod='amazon'}" class="check-loader"
                                         style="display: none;"/>
                                    {if $psIsGt15}<br><br>{/if}
                                </div>
                            </div>
                        {else}
                            <div class="row">
                                <label class="control-label col-lg-3">{l s='Authorization Status' mod='amazon'}</label>
                                <div class="margin-form col-lg-9">
                                <span>
                                    <img src="{$images_url|cat:'cross.png'}" alt="Unauthorized" title="Unauthorized"/>
                                    <a class="btn btn-default" href="{$marketplace.sp_api.oauth_uri|escape:'htmlall':'UTF-8'}">Authorize</a>
                                </span>
                                {if $psIsGt15}<br><br>{/if}
                                </div>
                            </div>
                        {/if}
                        <!-- START Custom Authorize -->
                        {if $settings.general.dev_mode} {* only allow dev mode *}
                        <div class="row form-group">
                            <label class="control-label col-lg-3">{l s='Enter Authorize' mod='amazon'}</label>
                            <div class="margin-form col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg" style="margin-top: 0;">
                                    <input type="radio" name="is_override_auth[{$id_lang|intval}]" class="authorize_switch" data-lang_id="{$id_lang|intval}"
                                           id="active-{$marketplace.sp_api.marketplace_id|escape:'htmlall':'UTF-8'}" rel="1" value="1"/>
                                    <label for="active-{$marketplace.sp_api.marketplace_id|escape:'htmlall':'UTF-8'}"
                                           class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                                    <input type="radio" name="is_override_auth[{$id_lang|intval}]" class="authorize_switch" data-lang_id="{$id_lang|intval}"
                                           id="active2-{$marketplace.sp_api.marketplace_id|escape:'htmlall':'UTF-8'}" rel="1" value="0" checked/>
                                    <label for="active2-{$marketplace.sp_api.marketplace_id|escape:'htmlall':'UTF-8'}"
                                           class="label-checkbox">{l s='No' mod='amazon'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <input type="text" name="override_mkp_id[{$id_lang|intval}]" style="display: none;" value="{$marketplace.sp_api.marketplace_id}" hidden />

                        <div class="custom-authorize-{$id_lang|intval}" style="display: none;">
                            <div class="row form-group">
                                <label class="control-label col-lg-3">{l s='Seller Id' mod='amazon'}</label>
                                <div class="margin-form col-lg-9">
                                    <input type="text" class="seller_id" name="override_seller_id[{$id_lang|intval}]" style="width:300px" value="{$marketplace.sp_api.seller_id}" />
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="control-label col-lg-3">{l s='Refresh Token' mod='amazon'}</label>
                                <div class="margin-form col-lg-9">
                                    <input type="text" class="refresh_token" name="override_refresh_token[{$id_lang|intval}]" style="width:300px" value="{$marketplace.sp_api.refresh_token}" />
                                </div>
                            </div>
                            {if !$marketplace.sp_api.is_authorized}
                                <div class="row form-group">
                                    <label class="control-label col-lg-3"></label>
                                    <div class="margin-form col-lg-9">
                                        <button type="button" class="btn btn-default sp_check_connection"
                                                data-marketplace-id="{$marketplace.sp_api.marketplace_id|escape:'quotes':'UTF-8'}"
                                                data-lang-id="{$id_lang|intval}">
                                            {l s='Check Connectivity' mod='amazon'}
                                        </button>
                                        <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                                             alt="{l s='Check Connectivity' mod='amazon'}" class="check-loader"
                                             style="display: none;"/>
                                    </div>
                                </div>
                            {/if}
                        </div>
                        {/if}
                        <!-- END Custom Authorize -->
                        <div class="row sp-check-response-success {$class_success|escape:'htmlall':'UTF-8'} col-lg-offset-3" style="display: none;"></div>
                        <div class="row sp-check-response-error {$class_error|escape:'htmlall':'UTF-8'} col-lg-offset-3" style="display: none;"></div>
                    </div>
                {/if}

                {include file=$module_path|cat:"views/templates/admin/configure/separator.tpl" big=false}
                <!-- detailed configuration section -->
                {assign var="general" value=$settings.general.config[$id_lang]}


                <div class="form-group">
                    <label class="control-label col-lg-3"
                           style="color:grey;">{l s='General Settings' mod='amazon'}</label>

                    <div class="col-lg-9">&nbsp;<br/><br/></div>

                    <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;">
                        <label class="control-label col-lg-3" rel="outstock"><span>{l s='Stock Break-even' mod='amazon'}</span><sup class="expert">{l s='Expert' mod='amazon'}</sup></label>

                        <div class="margin-form col-lg-9">
                            <input type="text" name="outofstock[{$id_lang|intval}]"
                                   value="{$general.out_of_stock|escape:'htmlall':'UTF-8'}" style="width:50px;"/>
                        </div>

                        <div class="form-group">
                            <div class="margin-form col-lg-12">
                                <hr class="amz-separator" style="width:30%; {if !$psIsGt15}margin-top: 40px;{/if}"/>
                            </div>
                        </div>
                    </div>

                    {* START: Product country VAT *}
                    {if isset($parameters.settings.product_country_vat) && $parameters.settings.product_country_vat}
                        <div class="form-group">
                            <label class="control-label col-lg-3" rel="product_country_vat_rate"><span>{l s='Product Country VAT Rate' mod='amazon'}</span></label>

                            <div class="margin-form col-lg-9 default_tax_rule">
                                <select name="product_country_vat_rate[{$id_lang|intval}]" id="product_country_vat_rate[{$id_lang|intval}]" style="width: 500px;">
                                    <option value="">{l s='Exempted' mod='amazon'}</option>
                                    {if isset($general.default_tax_rule) && is_array($general.default_tax_rule)}
                                        {foreach from=$general.default_tax_rule item=default_tax_rule}
                                            <option value="{$default_tax_rule.id_tax_rules_group|escape:'htmlall':'UTF-8'}"
                                                    {if $general.product_country_vat_rate_selected == $default_tax_rule.id_tax_rules_group}selected{/if}>
                                                {$default_tax_rule.name|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>
                        </div>
                    {/if}
                    {* END: Product country VAT *}
                    <div class="amazon-prices-rules" rel="amazon-prices-rules" style="display:none;">
                        <label class="control-label col-lg-3"
                               rel="price_rule"><span>{l s='Default Price Rule' mod='amazon'}</span></label>

                        <div class="margin-form col-lg-9">
                            <select name="price_rule[{$id_lang|intval}][type]" class="price-rule-type"
                                    style="display: inline; max-width:150px;vertical-align: top;">
                            <option value="percent"
                                    {if ($general.price_rule.type == 'percent')}selected{/if}>{l s='Percentage' mod='amazon'}</option>
                            {if isset($general.price_rule.currency_sign)}
                                <option value="value"
                                        {if ($general.price_rule.type == 'value')}selected{/if}>{l s='Value' mod='amazon'}</option>
                            {/if}
                            </select>

                            &nbsp;&nbsp;
                            <div id="default-price-rule-{$id_lang|intval}" class="default-price-rule"
                                 style="display: inline-block;">
                                {if isset($general.price_rule.rule.from)}

                                    {foreach from=$general.price_rule.rule.from key=index item=value}
                                        <div class="price-rule">
                                            <input type="text" name="price_rule[{$id_lang|intval}][rule][from][]"
                                               rel="from"
                                               style="width:50px"
                                               value="{$general.price_rule.rule.from[$index]|escape:'htmlall':'UTF-8'}"/>&nbsp;&nbsp;{$general.price_rule.currency_sign|escape:'htmlall':'UTF-8'}
                                            <span>
                                                <img src="{$settings.images_url|escape:'quotes':'UTF-8'}slash.png"
                                                    class="price-rule-slash" alt=""/>&nbsp;&nbsp;
                                            </span>
                                            <input type="text" name="price_rule[{$id_lang|intval}][rule][to][]" rel="to"
                                               style="width:50px"
                                               value="{$general.price_rule.rule.to[$index]|escape:'htmlall':'UTF-8'}"/>&nbsp;&nbsp;{$general.price_rule.currency_sign|escape:'htmlall':'UTF-8'}
                                            <span>
                                            <img src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png"
                                               class="price-rule-next" alt=""/>&nbsp;&nbsp;
                                            </span>
                                            <input type="number" name="price_rule[{$id_lang|intval}][rule][percent][]" rel="percent"
                                               min="-99" max="99"
                                               step=".01"
                                               style="width:50px;{if ($general.price_rule.type != 'percent')}display:none;{/if}"
                                               class="form-input"
                                               value="{$general.price_rule.rule.percent[$index]}"/>

                                            <input type="number" name="price_rule[{$id_lang|intval}][rule][value][]" rel="value"
                                               min="-99" max="99"
                                               step=".01"
                                               style="width:50px;{if ($general.price_rule.type != 'value')}display:none;{/if}"
                                               class="form-input"
                                               value="{$general.price_rule.rule.value[$index]}"/>

                                            <span style="margin-right: 4px;{if ($general.price_rule.type != 'percent')}display:none;{/if}" rel="percent">%</span>
                                            <span style="margin-right: 4px;{if ($general.price_rule.type != 'value')}display:none;{/if}" rel="value">{$general.price_rule.currency_sign}</span>

                                            <span class="price-rule-add" {if $index > 0}style="display:none;"{/if}><img
                                                src="{$settings.images_url|escape:'quotes':'UTF-8'}plus.png"
                                                alt="{l s='Add a rule' mod='amazon'}"/></span>
                                            <span class="price-rule-remove" {if $index == 0}style="display:none;"{/if}><img
                                                src="{$settings.images_url|escape:'quotes':'UTF-8'}minus.png"
                                                alt="{l s='Remove a rule' mod='amazon'}"/></span>
                                        </div>
                                    {/foreach}

                                {/if}
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="margin-form col-lg-12">
                                <hr class="amz-separator" style="width:30%; margin-top: 40px;"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3" rel="rounding"><span>{l s='Rounding' mod='amazon'}</span></label>

                        <div class="margin-form col-lg-9 rounding">
                            <input type="radio" name="rounding[{$id_lang|intval}]" value="1" {$general.rounding_1|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='One Digit' mod='amazon'}</span>
                            <input type="radio" name="rounding[{$id_lang|intval}]" value="2" {$general.rounding_2|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='Two Digits' mod='amazon'}</span>
                            <input type="radio" name="rounding[{$id_lang|intval}]" value="3" {$general.rounding_3|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='Smart Rounding' mod='amazon'}</span>
                            <input type="radio" name="rounding[{$id_lang|intval}]" value="4" {$general.rounding_4|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='None' mod='amazon'}</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="margin-form col-lg-12">
                            <hr class="amz-separator"
                                style="width:30%; {if !$psIsGt15}margin-top: 40px;{else}margin-top: 40px;{/if}"/>
                        </div>
                    </div>

                    <!-- Default product tax code -->
                    <div class="form-group">
                        <label class="control-label col-lg-3" rel="ptc"><span>{l s='Default Product Tax Code' mod='amazon'}</span></label>

                        <div class="margin-form col-lg-9 ptc">
                            <select name="ptc[{$id_lang|intval}]" id="ptc[{$id_lang|intval}]">
                                <option value="">{l s='N/A' mod='amazon'}</option>
                                {if isset($general.ptc) && is_array($general.ptc)}
                                    {foreach from=$general.ptc item=ptc}
                                        <option value="{$ptc.ptc|escape:'htmlall':'UTF-8'}" {if $general.ptc_selected == $ptc.ptc}selected{/if}>{$ptc.description|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                    </div>
                    <!-- End Default product tax code -->

                    <!-- Default tax rule -->
                    <div class="form-group tax-rule">
                        <label class="control-label col-lg-3" rel="default_tax_rule">
                            <span>{l s='Default Tax Rule' mod='amazon'}</span>
                        </label>
                        <div class="margin-form col-lg-9 default_tax_rule">
                            <select name="default_tax_rule[{$id_lang|intval}]" id="default_tax_rule[{$id_lang|intval}]">
                                <option value="">{l s='Exempted' mod='amazon'}</option>
                                {if isset($general.default_tax_rule) && is_array($general.default_tax_rule)}
                                    {foreach from=$general.default_tax_rule item=default_tax_rule}
                                        <option value="{$default_tax_rule.id_tax_rules_group|escape:'htmlall':'UTF-8'}"
                                                {if $general.default_tax_rule_selected == $default_tax_rule.id_tax_rules_group}selected{/if}>
                                            {$default_tax_rule.name|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                    </div>
                    {include file=$module_path|cat:"views/templates/admin/configure/separator.tpl" big=true}
                    <!-- End Default tax rule -->

                    <!-- Group (Only for EU) -->
                    {include file=$module_path|cat:"views/templates/admin/configure/marketplace/group.tpl"}

                    <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;">

                        <div class="form-group">
                            <label class="control-label col-lg-3" rel="sort_order"><span>{l s='Sort Order' mod='amazon'}</span><sup
                                        class="expert">{l s='Expert' mod='amazon'}</sup></label>

                            <div class="margin-form col-lg-9 sort_order">
                                <input type="radio" name="sort_order[{$id_lang|intval}]" value="1" {$general.sort_order_1|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='First Name, Last Name' mod='amazon'}</span>
                                <input type="radio" name="sort_order[{$id_lang|intval}]" value="2" {$general.sort_order_2|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='Last Name, First Name' mod='amazon'}</span>
                            </div>
                        </div>

                        {include file=$module_path|cat:"views/templates/admin/configure/separator.tpl" big=true}

                        <div class="form-group">

                            <label class="control-label col-lg-3"
                                   rel="synch_field"><span>{l s='Synchronization Field' mod='amazon'}</span><sup
                                        class="expert">{l s='Expert' mod='amazon'}</sup></label>

                            <div class="margin-form col-lg-9">
                                <select name="synch_field[{$id_lang|intval}]" style="width:250px;">
                                    <option value="" disabled="disabled">
                                    {l s='Choose one of the following' mod='amazon'}</option>
                                    <option value="ean13" {$general.synch_field_ean13|escape:'htmlall':'UTF-8'}>EAN13
                                        (Europe)
                                    </option>
                                    <option value="upc" {$general.synch_field_upc|escape:'htmlall':'UTF-8'}>UPC (United
                                        States)
                                    </option>
                                    <option value="both" {$general.synch_field_both|escape:'htmlall':'UTF-8'}>Both
                                        (EAN13 then UPC)
                                    </option>
                                    {if ($settings.general.expert_mode)}
                                        <option value="reference" {$general.synch_field_reference|escape:'htmlall':'UTF-8'}>
                                            SKU
                                        </option>
                                    {/if}
                                </select>
                                <input type="hidden" name="change_synch_field[{$id_lang|intval}]"
                                       value="{l s='You changed the Synchronization Field and this is not recommended. Please refer to the Amazon documentation prior changing this value.' mod='amazon'}"/>
                            </div>
                        </div>
                    </div>

                    <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;">
                        <div class="form-group">
                            <label class="control-label col-lg-3"
                                   rel="asin"><span>{l s='ASIN has the Priority' mod='amazon'}</span><sup
                                        class="expert">{l s='Expert' mod='amazon'}</sup></label>

                            <div class="margin-form col-lg-9">
                                <input type="checkbox" name="use_asin[{$id_lang|intval}]"
                                       {if ($general.asin_has_priority)}checked{/if}
                                       value="1" rel="1"/>&nbsp;<span class="span_text">{l s='Yes' mod='amazon'}</span>
                            </div>
                            {include file=$module_path|cat:"views/templates/admin/configure/separator.tpl" big=false}
                        </div>
                    </div>
                </div>

                <!-- Associate ID -->
                <div class="form-group associate-id">
                    <label class="control-label col-lg-3" rel="associate_id">
                        <span>{l s='Merchant Associate ID' mod='amazon'}</span>
                    </label>
                    <div class="margin-form col-lg-9 associate_id">
                        <input type="text" name="associate_id[{$id_lang|intval}]"
                               value="{$general.associate_id|escape:'htmlall':'UTF-8'}"
                               style="width: 300px; display: inline">
                    </div>
                </div>
                {include file=$module_path|cat:"views/templates/admin/configure/separator.tpl" big=true}
                <!-- End Associate ID -->

                <!-- carriers configuration section -->

                <div class="amazon-orders" style="display:none;">

                    {assign var="incoming_carrier" value=$settings.carriers.config.incoming[$id_lang]}
                    {assign var="carrier_list" value=$settings.carriers.config.prestashop_carrier}
                    {assign var="amazon_carrier_list" value=$settings.carriers.config.amazon_carrier}

                    <div class="form-group">
                        <label class="control-label col-lg-3"
                               style="color:grey">{l s='Carrier Mapping' mod='amazon'}</label><br/><br/>

                        <label class="control-label col-lg-3"
                               rel="incoming_order"><span>{l s='For incoming orders' mod='amazon'}</span></label>

                        <!-- incoming order carriers -->
                        <div id="incoming-{$id_lang|intval}" class="margin-form col-lg-9">
                            {foreach from=$incoming_carrier key=index item=carrier}
                                <div id="carrier-group-{$id_lang|intval}-{$index|escape:'htmlall':'UTF-8'}"
                                     class="carrier-group" style="display: flex; align-items: center;">
                                    <select class="amazon_carrier" name="incoming_carrier[{$id_lang|intval}][{$index}][amazon_carrier]"
                                            style="width:230px; display: inline;">
                                        <option disabled="disabled">{l s='Choose the associated carrier on Amazon' mod='amazon'}</option>
                                        <option value=""></option>
                                        {if isset($amazon_carrier_list) && is_array($amazon_carrier_list)}
                                            {foreach from=$amazon_carrier_list key=key item=amazon_carrier}
                                                <option value="{md5($amazon_carrier)|escape:'htmlall':'UTF-8'}"
                                                        {if (md5($amazon_carrier) === $carrier.amazon_carrier)}selected{/if}>{$amazon_carrier|escape:'quotes':'UTF-8'}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                <span style="position:relative;top:-4px;">&nbsp;&nbsp;<img
                                            src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png"
                                            style="max-height:16px;opacity:0.5"
                                            alt=""/>&nbsp;&nbsp;</span>
                                    <select class="carrier" name="incoming_carrier[{$id_lang|intval}][{$index}][carrier]" style="width:230px; display: inline;">
                                        <option disabled="disabled">{l s='Choose an appropriate carrier for Amazon Orders' mod='amazon'}</option>
                                        <option value="0"></option>
                                        {if isset($carrier_list) && is_array($carrier_list)}
                                            {foreach from=$carrier_list item=prestashop_carrier}
                                                <option value="{$prestashop_carrier.id_carrier|intval}"
                                                        {if ($carrier.ps_carrier|intval === $prestashop_carrier.id_carrier|intval)}selected{/if}>{$prestashop_carrier.name|escape:'htmlall':'UTF-8'}{if $prestashop_carrier.is_module}&nbsp;({l s='Module' mod='amazon'}){/if}</option>
                                            {/foreach}
                                        {/if}
                                    </select>

                                    {* Pickup point *}
                                    {if $parameters.settings.carrier_pickup_point}
                                        <span style="padding: 0 14px;">
                                            <input type="checkbox" class="pickup_point" name="incoming_carrier[{$id_lang|intval}][{$index}][pickup_point]" value="1"
                                                {if $carrier.pickup_point}checked{/if}/>&nbsp;
                                            <span style="font-size: 1em; margin: unset;">{l s='Is pickup point' mod='amazon'}</span>
                                        </span>
                                    {/if}
                                    &nbsp;&nbsp;
                                <span class="add-carrier addnewcarrier"
                                      rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_add|escape:'quotes':'UTF-8'}>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}plus.png"
                                         alt="{l s='Add a new carrier' mod='amazon'}"/></span>
                                <span class="remove-carrier removecarrier"
                                      rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_del|escape:'quotes':'UTF-8'}>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}minus.png"
                                         alt="{l s='Add a new carrier' mod='amazon'}"/></span>
                                    <br/>
                                </div>
                                <!-- eof div carrier group -->
                            {/foreach}
                            <div id="new-carriers-{$id_lang|intval}"></div>

                            <br/>
                        </div>
                        <!-- eof incoming order carriers -->

                        <!-- outgoing order carriers -->
                        {include file=$module_path|cat:"views/templates/admin/configure/marketplace/carriers_outgoing.tpl"
                        id_lang=$id_lang images_url=$settings.images_url
                        ps_carriers=$settings.carriers.ps_carriers
                        outgoing=$settings.carriers.config.outgoing[$id_lang]}
                        <!-- eof outgoing order carriers -->

                        {if $features.config.prime}
                            <!-- prime order carriers -->
                            {include file=$module_path|cat:"views/templates/admin/configure/marketplace/carriers_prime.tpl"
                            id_lang=$id_lang images_url=$settings.images_url
                            ps_carriers=$settings.carriers.ps_carriers
                            prime_carriers=$settings.carriers.config.prime_carriers[$id_lang]}
                            <!-- end prime order carriers -->
                        {/if}

                        <div class="clearfix"></div>

                        {if !$amazon.is_lite}
                        <div class="form-group" style="margin-top:15px;">
                            {if $settings.carriers.config.has_carrier_modules}
                                <div class="margin-form">
                                    <div class="amz-info-level-warning {if $psIsGt15}alert alert-warning col-lg-offset-3{/if}"
                                         style="font-size:1.1em">
                                        <p>
                                            {l s='Some carriers/modules have been detected' mod='amazon'}...<br/>
                                            {l s='Please read our online tutorial' mod='amazon'}:<br/>
                                            {$settings.carriers.carrier_modules_tutorial|escape:'quotes':'UTF-8'}<br/>
                                        </p>
                                    </div>
                                </div>
                            {/if}
                            <hr class="amz-separator" style="width:30%"/>
                        </div>
                        {/if}
                    </div>


                    {if $settings.carriers.fba_multichannel}
                        {assign var="fba_multichannel_carrier" value=$settings.carriers.config.fba_multichannel[$id_lang]}
                        <!-- fba_multichannel order carriers -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">{l s='For FBA Multi Channel Orders' mod='amazon'}</label>

                            <div class="margin-form col-lg-9">
                                {foreach from=$fba_multichannel_carrier key=index item=carrier}
                                    <div id="multichannel-carrier-group-{$id_lang|intval}-{$index|escape:'htmlall':'UTF-8'}" class="carrier-group">
                                        <select style="width:250px" name="carrier_multichannel[{$id_lang|intval}][prestashop][]">
                                            <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                            <option value="0"></option>
                                            {foreach from=$carrier.prestashop_carrier key=id_carrier item=prestashop_carrier}
                                                <option value="{$id_carrier|intval}"
                                                        {if $prestashop_carrier.selected}selected{/if}>{$prestashop_carrier.name|escape:'htmlall':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                <span style="position:relative;top:-4px;">&nbsp;&nbsp;<img src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png" style="max-height:16px;opacity:0.5" alt=""/>&nbsp;&nbsp;</span>
                                        <select name="carrier_multichannel[{$id_lang|intval}][amazon][]" style="width:250px;">
                                            <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                            <option value=""></option>
                                            {foreach from=$carrier.amazon_carrier key=key item=amazon_carrier}
                                                <option value="{$key|escape:'htmlall':'UTF-8'}"
                                                        {if $amazon_carrier.selected}selected{/if}>{$amazon_carrier.name|escape:'htmlall':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                        &nbsp;&nbsp;
                                        <span class="add-carrier addnew-multichannel-carrier" rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_add|escape:'quotes':'UTF-8'}><img src="{$settings.images_url|escape:'quotes':'UTF-8'}plus.png" alt="{l s='Add a new carrier' mod='amazon'}"/></span>
                                        <span class="remove-carrier remove-multichannel-carrier" rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_del|escape:'quotes':'UTF-8'}><img src="{$settings.images_url|escape:'quotes':'UTF-8'}minus.png" alt=""/></span>
                                    </div>
                                    <!-- eof fba_multichannel carrier group -->
                                {/foreach}
                                <div id="multichannel-new-carriers-{$id_lang|intval}"></div>

                                <p>
                                    {l s='Associate as relevant as possible your Store\'s carrier with the Amazon carrier for FBA Multi-Channel' mod='amazon'}
                                    <br/>
                                </p>
                            </div>
                        </div>
                        <!-- eof fba_multichannel order carriers -->
                        <div class="margin-form col-lg-12">
                            <hr class="amz-separator" style="width:30%"/>
                        </div>
						<div class="clearfix"></div>
                    {/if}


                </div><!-- div rel=amazon orders -->

                {if $settings.overrides.allow}
                    <div class="form-group">
                        <label class="control-label col-lg-3"
                               style="color:grey">{l s='Shipping Overrides' mod='amazon'}</label>

                        <div class="margin-form clearfix">&nbsp;</div>

                        <label class="control-label col-lg-3">{l s='Standard' mod='amazon'}</label>

                        <div class="margin-form col-lg-9">
                            <select name="overrides_std[{$id_lang|intval}]" style="width:250px; display: inline;">
                                <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                <option value=""></option>
                                {foreach from=$settings.overrides.standard.$id_lang key=key item=override}
                                    <option value="{$key|escape:'htmlall':'UTF-8'}"
                                            {if $override.selected}selected{/if}>{$override.name|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            </select><br/>
                            {if $psIsGt15}<br>{/if}
                        </div>

                        <label class="control-label col-lg-3">{l s='Express' mod='amazon'}</label>

                        <div class="margin-form col-lg-9">
                            <select name="overrides_exp[{$id_lang|intval}]" style="width:250px;">
                                <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                <option value=""></option>
                                {foreach from=$settings.overrides.express.$id_lang key=key item=override}
                                    <option value="{$key|escape:'htmlall':'UTF-8'}"
                                            {if $override.selected}selected{/if}>{$override.name|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            </select><br/>
                        </div>
                        <div class="margin-form col-lg-9 col-lg-offset-3">
                            <p>{l s='Amazon Shipping Method to override the default shipping charges when specified in product sheet' mod='amazon'}
                                <br/>
                                {l s='Please refer to the documentation to choose the appropriate value' mod='amazon'}
                                <br/>
                            </p>
                        </div>
                    </div>
                {/if}

                <div rel="amazon-smart-shipping" style="display:none">
                    {if isset($settings.shipping_methods.$id_lang)}
                        <div class="form-group">
                            <label class="control-label col-lg-3"
                                   rel="shipping_mode"><span>{l s='Default Shipping Method' mod='amazon'}</span></label>

                            <div class="margin-form col-lg-9">
                                <select name="shipping_method[{$id_lang|intval}]" style="width:250px;">
                                    <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                    <option value=""></option>
                                    {foreach from=$settings.shipping_methods.$id_lang key=key item=shipping_method}
                                        <option value="{$key|escape:'htmlall':'UTF-8'}"
                                                {if $shipping_method.selected}selected{/if}>{$shipping_method.name|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select><br/>


                            </div>
                        </div>
                    {/if}
                </div>

                <!-- Upload invoice - For Italy marketplace -->
                {if $marketplace.marketPlaceId == AmazonSellerPartnerConstant::MKP_IT}
                    <div class="form-group upload-invoice-italy">
                        <label class="control-label col-lg-3" rel="upload_invoice_italy">
                            <span>{l s='VCS - Upload non-business invoice' mod='amazon'}</span>
                        </label>
                        <div class="margin-form col-lg-9 upload_invoice_italy">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="allow_it_upload_invoice"
                                   id="upload_invoice_italy" rel="1" value="1"
                                   {if ($marketplace.allowUploadInv)}checked{/if} /><label
                                    for="upload_invoice_italy"
                                    class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                                <input type="radio" name="allow_it_upload_invoice"
                                   id="upload_invoice_italy2" rel="1" value="0"
                                   {if !($marketplace.allowUploadInv)}checked{/if} /><label
                                    for="upload_invoice_italy2"
                                    class="label-checkbox">{l s='No' mod='amazon'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>
                    {include file=$module_path|cat:"views/templates/admin/configure/separator.tpl" big=true}
                {/if}
            </div>

            <!-- validation button -->
            {$settings.validate.$id_lang|escape:'quotes':'UTF-8'}
        </div>
        <!-- eof div menudiv lang -->
    {/foreach}
{/if}
