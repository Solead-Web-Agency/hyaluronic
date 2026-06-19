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
<div class="amazon-supplier-prices-rules" rel="amazon-supplier-prices-rules">
    <div class="form-group margin-form">
        <label class="profile-obj-title control-label col-lg-3"
               rel="supplier_price_rule"><span>{l s='Supplier Price Rule' mod='amazon'}</span></label>

        <select name="amz_price_rules[supplier_price_rule][_key_][{$id_lang|intval}][supplier]" class="supplier-price-rule-value"
                style="display: inline; max-width:150px;vertical-align: top;">
            <option></option>
            {foreach from=$profile.suppliers key=index item=value}
                <option value="{$value.id_supplier|escape:'htmlall':'UTF-8'}"
                        {if ($profile.supplier_price_rule.supplier == $value.id_supplier)}selected{/if}>{l s=$value.name mod='amazon'}</option>
            {/foreach}
        </select>

        <select name="amz_price_rules[supplier_price_rule][_key_][{$id_lang|intval}][type]" class="supplier-price-rule-type"
                style="display: inline; max-width:150px;vertical-align: top;">
            <option value="percent"
                    {if ($profile.supplier_price_rule.type == 'percent')}selected{/if}>{l s='Percentage' mod='amazon'}</option>
            {if isset($profile.supplier_price_rule.currency_sign)}
                <option value="value"
                        {if ($profile.supplier_price_rule.type == 'value')}selected{/if}>{l s='Value' mod='amazon'}</option>
            {/if}
        </select>

        <div id="default-supplier-price-rule-{$id_lang|intval}" class="default-price-rule" style="display: inline-block;">
            {foreach from=$profile.supplier_price_rule.rule.from key=index item=value}
                <div class="supplier-price-rule price-rule">
                    <input type="text" name="amz_price_rules[supplier_price_rule][_key_][{$id_lang|intval}][rule][from][]" rel="from"
                           style="width:50px" value="{$profile.supplier_price_rule.rule.from[$index]|escape:'htmlall':'UTF-8'}"/>&nbsp;&nbsp;{$profile.supplier_price_rule.currency_sign|escape:'htmlall':'UTF-8'}
                    <span>&nbsp;&nbsp;<img src="{$profiles.images_url|escape:'quotes':'UTF-8'}slash.png"
                                           class="price-rule-slash" alt=""/>&nbsp;&nbsp;</span>
                    <input type="text" name="amz_price_rules[supplier_price_rule][_key_][{$id_lang|intval}][rule][to][]" rel="to"
                           style="width:50px" value="{$profile.supplier_price_rule.rule.to[$index]|escape:'htmlall':'UTF-8'}"/>&nbsp;&nbsp;{$profile.supplier_price_rule.currency_sign|escape:'htmlall':'UTF-8'}
                    <span>&nbsp;&nbsp;<img src="{$profiles.images_url|escape:'quotes':'UTF-8'}next.png"
                                           class="price-rule-next" alt=""/>&nbsp;&nbsp;</span>
                    {*<select name="amz_price_rules[supplier_price_rule][_key_][{$id_lang|intval}][rule][percent][]" rel="percent"
                            style="width:100px;{if ($profile.supplier_price_rule.type != 'percent')}display:none;{/if}">
                        <option></option>
                        {section name=supplier_price_rule_percent loop=99}
                            <option value="{$smarty.section.supplier_price_rule_percent.iteration|escape:'htmlall':'UTF-8'}"
                                    {if $profile.supplier_price_rule.rule.percent[$index] == $smarty.section.supplier_price_rule_percent.iteration}selected{/if}>{$smarty.section.supplier_price_rule_percent.iteration|escape:'htmlall':'UTF-8'}
                                &#37;</option>
                        {/section}
                        <option disabled>--</option>
                        {section name=supplier_price_rule_percent loop=99}
                            <option value="-{$smarty.section.supplier_price_rule_percent.iteration|escape:'htmlall':'UTF-8'}"
                                    {if $profile.supplier_price_rule.rule.percent[$index] == ($smarty.section.supplier_price_rule_percent.iteration * -1)}selected{/if}>
                                -{$smarty.section.supplier_price_rule_percent.iteration|escape:'htmlall':'UTF-8'} &#37;</option>
                        {/section}
                    </select>
                    {if isset($profile.supplier_price_rule.currency_sign)}
                        <select name="amz_price_rules[supplier_price_rule][_key_][{$id_lang|intval}][rule][value][]" rel="value" style="width:100px;{if ($profile.supplier_price_rule.type != 'value')}display:none;{/if}">
                            <option></option>
                            {section name=supplier_price_rule_value loop=99}
                                <option value="{$smarty.section.supplier_price_rule_value.iteration|escape:'htmlall':'UTF-8'}" {if $profile.supplier_price_rule.rule.value[$index] == $smarty.section.supplier_price_rule_value.iteration}selected{/if}>{$smarty.section.supplier_price_rule_value.iteration|escape:'htmlall':'UTF-8'} {$profile.supplier_price_rule.currency_sign|escape:'htmlall':'UTF-8'}</option>
                            {/section}
                            <option disabled>--</option>
                            {section name=supplier_price_rule_value loop=99}
                                <option value="-{$smarty.section.supplier_price_rule_value.iteration|escape:'htmlall':'UTF-8'}" {if $profile.supplier_price_rule.rule.value[$index] == ($smarty.section.supplier_price_rule_value.iteration * -1)}selected{/if}>-{$smarty.section.supplier_price_rule_value.iteration|escape:'htmlall':'UTF-8'} {$profile.supplier_price_rule.currency_sign|escape:'htmlall':'UTF-8'}</option>
                            {/section}
                        </select>
                    {/if}*}
                    <input type="number" name="amz_price_rules[supplier_price_rule][_key_][{$id_lang|intval}][rule][percent][]" rel="percent"
                           min="-99" max="99"
                           step=".01"
                           style="width:60px;{if ($profile.supplier_price_rule.type != 'percent')}display:none;{/if}"
                           class="form-input"
                           value="{$profile.supplier_price_rule.rule.percent[$index]|escape:'htmlall':'UTF-8'}"/>

                    <input type="number" name="amz_price_rules[supplier_price_rule][_key_][{$id_lang|intval}][rule][value][]" rel="value"
                           min="-99" max="99"
                           step=".01"
                           style="width:60px;{if ($profile.supplier_price_rule.type != 'value')}display:none;{/if}"
                           class="form-input"
                           value="{$profile.supplier_price_rule.rule.value[$index]|escape:'htmlall':'UTF-8'}"/>

                    <span style="margin-right: 4px;{if ($profile.supplier_price_rule.type != 'percent')}display:none;{/if}" rel="percent">%</span>
                    <span style="margin-right: 4px;{if ($profile.supplier_price_rule.type != 'value')}display:none;{/if}" rel="value">{$profile.supplier_price_rule.currency_sign|escape:'htmlall':'UTF-8'}</span>

                    <span class="supplier-price-rule-add" {if $index > 0}style="display:none;"{/if}><img
                                src="{$profiles.images_url|escape:'quotes':'UTF-8'}plus.png"
                                alt="{l s='Add a rule' mod='amazon'}"/></span>
                    <span class="supplier-price-rule-remove" {if $index == 0}style="display:none;"{/if}><img
                                src="{$profiles.images_url|escape:'quotes':'UTF-8'}minus.png"
                                alt="{l s='Remove a rule' mod='amazon'}"/></span>
                </div>
            {/foreach}
        </div>
        <div class="margin-form col-lg-9">
            <hr style="width:30%;">
        </div>
    </div>
</div>