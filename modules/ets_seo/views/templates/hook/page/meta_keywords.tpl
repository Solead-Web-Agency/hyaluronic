{*
* 2007-2021 ETS-Soft
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
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}

<div class="row">
    <div class="col-md-9">
        <fieldset class="form-group">
            <label class="px-0 col-sm-2 control-label">
                {l s='Meta keywords' mod='ets_seo'}
            </label>
            <div class="translations tabbable" id="form_step5_meta_description">
                <div class="translationsFields tab-content">
                    {foreach $ets_seo_languages as $lang}
                    <div data-locale="{$lang.iso_code|escape:'html':'UTF-8'}" class="translationsFields-meta_keyword_{$lang.id_lang|escape:'html':'UTF-8'} tab-pane translation-field {if $current_lang.id == $lang.id_lang}show active{/if}  translation-label-{$lang.iso_code|escape:'html':'UTF-8'}">
                        <input type="text" id="ets_seo_advanced_meta_keyword_{$lang.id_lang|escape:'html':'UTF-8'}" name="ets_seo_meta_keywords[{$lang.id_lang|escape:'html':'UTF-8'}]" data-idlang="{$lang.id_lang|escape:'html':'UTF-8'}" class="ets-seo-tagify js-ets-seo-tagify form-control" placeholder="{l s='Add keyword' mod='ets_seo'}" value="{if $product_meta_keywords && isset($product_meta_keywords[$lang.id_lang])}{$product_meta_keywords[$lang.id_lang].meta_keywords|escape:'html':'UTF-8'}{/if}" />
                        <small class="form-text">
                            {l s='To add tags, click in the field, write something, and then press the "Enter" key. Invalid characters: <>;=#{}' mod='ets_seo'}
                        </small>
                    </div>
                    {/foreach}
                </div>
            </div>
        </fieldset>
    </div>
</div>
