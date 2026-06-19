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
* @copyright 2012-2020 SeoSA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

{extends file="../tab_layout.tpl"}

{block name="form"}
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="disable_image" class="disable_option">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-12">

                    <div class="float-left">
                        <label class="control-label margin-right float-left">{l s='Apply change for' mod='masseditproduct'}:</label>
                        <span class="ps-switch prestashop-switch fixed-width-xxl switch-product-combination margin-right float-left">
                            {foreach [0,1] as $value}
                                <input type="radio" name="change_for_img" value="{$value|escape:'quotes':'UTF-8'}"
                                        {if $value == 1} id="change_for_product_image" {else} id="change_for_combination_image" {/if}
                                        {if $value == 0} checked="checked" {/if}
                                />
                                <label {if $value == 1} for="change_for_product_image" {else} for="change_for_combination_image" {/if}>
                                    {if $value == 0}{l s='Product' mod='masseditproduct'}{else}{l s='Combination' mod='masseditproduct'}{/if}
                                </label>
                            {/foreach}
                            <a class="slide-button"></a>
                        </span>
                    </div>
                    <div class="float-left">
                        <button class="add_image btn btn-default margin-right">
                            <i class="icon-plus"></i>
                            {l s='Add image' mod='masseditproduct'}
                        </button>
                        <label class="control-label">
                            <input type="checkbox" name="delete_images">{l s='Delete old images about products' mod='masseditproduct'}
                        </label>
                    </div>

                </div>
            </div>
            <div class="images">
            </div>

        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="disable_image_caption" class="disable_option">
        <div class="col-lg-12">

            <div class="row form-group">
                <div class="col-lg-12">
                    <label class="control-label margin-right">
                    <span class="" data-toggle="tooltip" mod='masseditproduct'
                          title="{l s='Update all captions at once, or select the position of the image whose caption you wish to edit. Invalid characters: %s'|sprintf:'<>;=#{}' mod='masseditproduct'}">
                        {l s='Caption' mod='masseditproduct'}:
                    </span>
                    </label>
                    {foreach from=$languages item=language}
                        {if $languages|count > 1}
                            <div class="translatable-field lang-{$language.id_lang|intval}">
                        {/if}
                        <input type="text" id="legend_{$language.id_lang|intval}" class="form-control fixed-width-xxxl {if isset($input_class)}{$input_class|escape:'html':'UTF-8'}{/if}" name="legend_{$language.id_lang|intval}" data-lang="{$language.id_lang|intval}" value=""/>
                        {if $languages|count > 1}
                            <span class="btn-languages margin-right">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                                    {$language.iso_code|escape:'html':'UTF-8'}
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    {foreach from=$languages item=language}
                                        <li>
                                            <a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'html':'UTF-8'}</a>
                                        </li>
                                    {/foreach}
                                </ul>
                            </span>
                        {/if}
                        <span class="white-space-nowrap">{l s='If other language an empty caption for him will be removed' mod='masseditproduct'}</span>
                        {if $languages|count > 1}
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                        <span id="caption_selection">
                <select name="id_caption" class="fixed-width-xl custom-select">
                    <option value="0">{l s='All captions' mod='masseditproduct'}</option>
                </select>
                        </span>
                    <span class="checkbox-delete" style="line-height: 36px;padding: 0 10px;padding-top: 0 !important">
                        <label class="control-label">
                    <input type="checkbox" name="delete_captions">{l s='Delete old captions' mod='masseditproduct'}
                        </label>
                    </span>

                    <button type="button" class="btn btn-default" onclick="$('[name^=legend]:visible').insertAtCaret('{literal}{name}{/literal}');">
                        name product
                    </button>
                    <button type="button" class="btn btn-default" onclick="$('[name^=legend]:visible').insertAtCaret('{literal}{manufacturer}{/literal}');">
                        manufacturer
                    </button>
                    <button type="button" class="btn btn-default" onclick="$('[name^=legend]:visible').insertAtCaret('{literal}{category}{/literal}');">
                        default category
                    </button>
                </div>
            </div>
        </div>
    </div>
{/block}