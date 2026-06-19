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
<div class="card">
    <div class="card-block pt_0 pb_0">
        <div class="input_key_phase">
            <div class="form-group">
                <label>{$seo_data.focus_keyphrase.label|escape:'html':'UTF-8'}</label>
                <div class="input-group locale-input-group d-flex">
                    {foreach from=$ets_seo_languages item='lang'}
                        <div class="key-phrase-lang multilang-field lang-{$lang.iso_code|escape:'html':'UTF-8'} lang-{$lang.id_lang|escape:'html':'UTF-8'}
                    {if $lang.id_lang != $current_lang.id}
                        hide
                    {/if}">
                            <input type="text"
                                   class="form-control input-key-phrase input-key-phrase-il-{$lang.id_lang|escape:'html':'UTF-8'} input-key-phrase-ic-{$lang.iso_code|escape:'html':'UTF-8'}"
                                   data-idlang="{$lang.id_lang|escape:'html':'UTF-8'}"
                                   name="ets_seo_key_phrase[{$lang.id_lang|escape:'html':'UTF-8'}]"
                                   value="{if isset($seo_data.focus_keyphrase.value[$lang.id_lang])}{$seo_data.focus_keyphrase.value[$lang.id_lang]|escape:'html':'UTF-8'}{/if}">
                            <div class="help-block">{l s='Phrase (or keyword) that you want your page to be found for on search engines' mod='ets_seo'}</div>
                        </div>
                        {if !$is_new_theme && count($ets_seo_languages) > 1}
                            {include file="./_btn_multilang.tpl"}
                        {/if}
                    {/foreach}
                    {if $is_new_theme && count($ets_seo_languages) > 1}
                        {include file="./_btn_multilang.tpl"}
                    {/if}

                </div>

            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
{include file="./_minor_keyphrase.tpl"}