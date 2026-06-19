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

<div class="form-group">
    <label class="control-label col-lg-3 required">
        {l s='Meta title' mod='ets_seo'}
    </label>
    <div class="col-lg-9">
        <div class="form-group">
            {foreach $ets_seo_languages as $lang}
                <div class="{if $ets_seo_languages|count > 1}row{/if} translatable-field lang-{$lang.id_lang|escape:'html':'UTF-8'}" {if $current_lang.id != $lang.id_lang}style="display:none" {/if}>
                    {if $ets_seo_languages|count > 1}
                    <div class="col-lg-9">
                    {/if}
                        <input type="text" id="ets_seo_meta_title_{$lang.id_lang|escape:'html':'UTF-8'}" name="ets_seo_meta_title[{$lang.id_lang|escape:'html':'UTF-8'}]" class="" value="">
                    {if $ets_seo_languages|count > 1}
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                            {$lang.iso_code|escape:'html':'UTF-8'}
                            <i class="icon-caret-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                            {foreach $ets_seo_languages as $lang}
                                <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
                            {/foreach}
                        </ul>
                    </div>
                    {/if}
                </div>
            {/foreach}
        </div>
    </div>
</div>