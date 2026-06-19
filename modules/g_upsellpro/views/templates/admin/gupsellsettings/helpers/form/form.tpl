{*
* Do not edit the file if you want to upgrade the module in future.
* 
* @author    Globo Jsc <contact@globosoftware.net>
* @copyright 2020 Globo., Jsc
* @link	     http://www.globosoftware.net/
* @license   please read license in file license.txt
*/
*}
{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'gupsell_settings'}
                </div>
            </div>
        </div>
        {*close panel - helper form*}
        <div class="form-group">
        </div>
        <div class="form-group">
            <div class="col-lg-12">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-lg-4">
                                    <p></p>
                                    <div class="gupsell-title-box-setting box-heading">
                                        {l s='General' mod='g_upsellpro'}
                                    </div>
                                    <div class="gupsell-subtitle-box-setting">
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="panel">
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Label position' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <Select class="" name="GSELL_MAIN_LABEL">
                                                                <option value="Left" {if $fields_value['GSELL_MAIN_LABEL'] == 'left'}selected ="selected"{/if}>{l s='Left' mod='g_upsellpro'}</option>
                                                                <option value="center" {if $fields_value['GSELL_MAIN_LABEL'] == 'center'}selected ="selected"{/if}>{l s='Center' mod='g_upsellpro'}</option>
                                                                <option value="right" {if $fields_value['GSELL_MAIN_LABEL'] == 'right'}selected ="selected"{/if}>{l s='Right' mod='g_upsellpro'}</option>
                                                            </Select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Main button background color' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="input-group">
                                                                <input class="form-control" data-hex="true" type="color" id="GSELL_MAIN_BUTTON_BACKGROUND" name="GSELL_MAIN_BUTTON_BACKGROUND" value="{$fields_value['GSELL_MAIN_BUTTON_BACKGROUND']|escape:'html':'UTF-8'}"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Main button text color' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="input-group">
                                                                <input class="form-control" data-hex="true" type="color" id="GSELL_MAIN_BUTTON_COLOR" name="GSELL_MAIN_BUTTON_COLOR" value="{$fields_value['GSELL_MAIN_BUTTON_COLOR']|escape:'html':'UTF-8'}"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Floating position' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <Select class="" name="GSELL_MAIN_FLOATING_POSITION">
                                                                <option value="right" {if $fields_value['GSELL_MAIN_FLOATING_POSITION'] == 'right'}selected ="selected"{/if}>{l s='Bottom Right' mod='g_upsellpro'}</option>
                                                                <option value="left" {if $fields_value['GSELL_MAIN_FLOATING_POSITION'] == 'left'}selected ="selected"{/if}>{l s='Bottom Left' mod='g_upsellpro'}</option>
                                                            </Select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Popup time delay' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input placeholder="0" type="text" id="" name="GSELL_MAIN_POPUP_DELAY" value="{$fields_value['GSELL_MAIN_POPUP_DELAY']|escape:'html':'UTF-8'}" onchange="this.value = this.value.replace(/,/g, '.');" onkeypress="return isNumberKey(event)">
                                                        <span class="input-group-addon">{l s='Seconds' mod='g_upsellpro'}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Most Popular Background Color' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="input-group">
                                                                <input class="form-control" data-hex="true" type="color" id="GSELL_MAIN_MOSTPOPOLAR_BACKGROUND" name="GSELL_MAIN_MOSTPOPOLAR_BACKGROUND" value="{$fields_value['GSELL_MAIN_MOSTPOPOLAR_BACKGROUND']|escape:'html':'UTF-8'}"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Most Popular Color' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="input-group">
                                                                <input class="form-control" data-hex="true" type="color" id="GSELL_MAIN_MOSTPOPOLAR_COLOR" name="GSELL_MAIN_MOSTPOPOLAR_COLOR" value="{$fields_value['GSELL_MAIN_MOSTPOPOLAR_COLOR']|escape:'html':'UTF-8'}"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Most Popular Background Item Color' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="input-group">
                                                                <input class="form-control" data-hex="true" type="color" id="GSELL_MAIN_MOSTPOPOLAR_BACKGROUND_ITEM" name="GSELL_MAIN_MOSTPOPOLAR_BACKGROUND_ITEM" value="{$fields_value['GSELL_MAIN_MOSTPOPOLAR_BACKGROUND_ITEM']|escape:'html':'UTF-8'}"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <p class="ghr-line"></p>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-lg-4">
                                    <p></p>
                                    <div class="gupsell-title-box-setting box-heading">
                                        {l s='Custom Wording & Translation' mod='g_upsellpro'}
                                    </div>
                                    <div class="gupsell-subtitle-box-setting">
                                        <p class="help-block">{l s='Customize or translate the wording of the Offer box.' mod='g_upsellpro'}</p>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="panel">
                                        <div class="form-group">
                                            <label class="control-label">{l s='Total price' mod='g_upsellpro'}</label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" name="GSELL_SETTING_BUTTON_TOTAL[{$language.id_lang|escape:'html':'UTF-8'}]" placeholder="{l s='Total' mod='g_upsellpro'}" value="{$fields_value['GSELL_SETTING_BUTTON_TOTAL'][$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                                <p class="help-block">{l s='Total product offer price' mod='g_upsellpro'}</p>
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                    {$language.iso_code|escape:'html':'UTF-8'}
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {foreach from=$languages item=lang}
                                                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $languages|count > 1}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{l s='Button — Add to Cart' mod='g_upsellpro'}</label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" placeholder="{l s='Add to cart' mod='g_upsellpro'}" name="GSELL_SETTING_BUTTON_ADCART[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$fields_value['GSELL_SETTING_BUTTON_ADCART'][$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                    {$language.iso_code|escape:'html':'UTF-8'}
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {foreach from=$languages item=lang}
                                                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $languages|count > 1}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{l s='Button — Update to cart' mod='g_upsellpro'}</label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" placeholder="{l s='Update to cart' mod='g_upsellpro'}" name="GSELL_SETTING_BUTTON_UPGRADE[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$fields_value['GSELL_SETTING_BUTTON_UPGRADE'][$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                                <p class="help-block">{l s='Remove parent product while adding an upsell product to cart' mod='g_upsellpro'}</p>
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                    {$language.iso_code|escape:'html':'UTF-8'}
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {foreach from=$languages item=lang}
                                                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $languages|count > 1}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{l s='Button — Checkout' mod='g_upsellpro'}</label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" placeholder="{l s='Checkout' mod='g_upsellpro'}" name="GSELL_SETTING_BUTTON_CHECKOUT[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$fields_value['GSELL_SETTING_BUTTON_CHECKOUT'][$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                    {$language.iso_code|escape:'html':'UTF-8'}
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {foreach from=$languages item=lang}
                                                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $languages|count > 1}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{l s='Button — Upgrade' mod='g_upsellpro'}</label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" placeholder="{l s='Upgrade' mod='g_upsellpro'}" name="GSELL_SETTING_BUTTON_UPGRADECART[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$fields_value['GSELL_SETTING_BUTTON_UPGRADECART'][$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                                <p class="help-block">{l s='Remove parent product while adding upsell product bundle to cart' mod='g_upsellpro'}</p>
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                    {$language.iso_code|escape:'html':'UTF-8'}
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {foreach from=$languages item=lang}
                                                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $languages|count > 1}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{l s='Button — No, Thanks' mod='g_upsellpro'}</label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" placeholder="{l s='No, Thanks' mod='g_upsellpro'}" name="GSELL_SETTING_BUTTON_NOTHANKS[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$fields_value['GSELL_SETTING_BUTTON_NOTHANKS'][$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                                <p class="help-block">{l s='Back button displayed on the popup offer.' mod='g_upsellpro'}</p>
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                    {$language.iso_code|escape:'html':'UTF-8'}
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {foreach from=$languages item=lang}
                                                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $languages|count > 1}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{l s='Button — Floating' mod='g_upsellpro'}</label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" placeholder="{l s='Reveal Offers' mod='g_upsellpro'}" name="GSELL_SETTING_BUTTON_FLOATING[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$fields_value['GSELL_SETTING_BUTTON_FLOATING'][$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                                <p class="help-block">{l s='Button displayed on the Floating offer.' mod='g_upsellpro'}</p>
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                    {$language.iso_code|escape:'html':'UTF-8'}
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {foreach from=$languages item=lang}
                                                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $languages|count > 1}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">{l s='Label - Most Popular' mod='g_upsellpro'}</label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" placeholder="{l s='Most Popular' mod='g_upsellpro'}" name="GSELL_SETTING_MOST_POPULAR[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$fields_value['GSELL_SETTING_MOST_POPULAR'][$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                    {$language.iso_code|escape:'html':'UTF-8'}
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {foreach from=$languages item=lang}
                                                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $languages|count > 1}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <p class="ghr-line"></p>
                    </div>
                    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-lg-4">
                                    <p></p>
                                    <div class="gupsell-title-box-setting box-heading">
                                        {l s='Custom Appearance' mod='g_upsellpro'}
                                    </div>
                                    <div class="gupsell-subtitle-box-setting">
                                        <p class="help-block">{l s='Override our styles to make the Offers match the rest of your site.' mod='g_upsellpro'}</p>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="panel">
                                        <div class="form-group">
                                            <label class="control-label">
                                                {l s='Custom CSS' mod='g_upsellpro'}
                                            </label>
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control textarea-autosize " name="GSELL_SETTING_CUSTOM_CSS" rows="10" aria-multiline="true">{$fields_value['GSELL_SETTING_CUSTOM_CSS']|escape:'html':'UTF-8'}</textarea>
                                                        <p class="help-block">{l s='Additional CSS Styles for this upsell.' mod='g_upsellpro'}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <p class="ghr-line"></p>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="gbtn-default btn btn-default pull-right" name="saveGupsellConfig" id="saveGupsellConfig">{l s='Save' mod='g_upsellpro'}</button>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}