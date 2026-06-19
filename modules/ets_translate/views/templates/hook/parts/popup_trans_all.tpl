{*
* 2007-2020 ETS-Soft
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
*  @copyright  2007-2020 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="modal fade ets-trans-modal" id="etsTransModalTrans" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
    <div class="ets_table ets_trans_table">
    <div class="ets_table-cell">
        <div class="modal-content">
            <form id="etsTransFormTransPages">
                <div class="panel_header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        <i class="fa fa-language"></i>
                        {if isset($wdConfig) && !$isInterTrans}
                            {l s='1-Click translate' mod='ets_translate'}
                        {else}
                            {l s='Translate' mod='ets_translate'}
                        {/if}
                    </h4>
                </div>
                <div class="panel_body">
                    <div class="ets-trans-content">
                        <div class="form-errors"></div>
                    {if $totalTranslate <= 0 && $pageType != 'email' && $pageType != 'module' && $pageType != 'theme' && $pageType != 'megamenu' && $pageType != 'blog' && $pageType != 'pc' && $pageType != 'inter' && $pageType != 'all'}
                        <div class="alert alert-warning">
                            {if $pageType == 'product'}
                                {l s='No product to translate' mod='ets_translate'}
                            {elseif $pageType == 'category'}
                                {l s='No category to translate' mod='ets_translate'}
                            {elseif $pageType == 'cms'}
                                {l s='No CMS to translate' mod='ets_translate'}
                            {elseif $pageType == 'cms_category'}
                                {l s='No CMS category to translate' mod='ets_translate'}
                            {elseif $pageType == 'manufacturer'}
                                {l s='No manufacturer to translate' mod='ets_translate'}
                            {elseif $pageType == 'supplier'}
                                {l s='No supplier to translate' mod='ets_translate'}
                            {elseif $pageType == 'email'}
                                {l s='No email to translate' mod='ets_translate'}
                            {elseif $pageType == 'attribute_group'}
                                {l s='No attribute group to translate' mod='ets_translate'}
                            {elseif $pageType == 'attribute'}
                                {l s='No attribute to translate' mod='ets_translate'}
                            {elseif $pageType == 'feature'}
                                {l s='No feature to translate' mod='ets_translate'}
                            {elseif $pageType == 'feature_value'}
                                {l s='No feature values to translate' mod='ets_translate'}
                            {elseif $pageType == 'blockreassurance'}
                                {l s='No block reassurance values to translate' mod='ets_translate'}
                            {elseif $pageType == 'ps_linklist'}
                                {l s='No block link to translate' mod='ets_translate'}
                            {elseif $pageType == 'ps_mainmenu'}
                                {l s='No menu to translate' mod='ets_translate'}
                            {elseif $pageType == 'all'}
                                {l s='No things to translate' mod='ets_translate'}
                            {else}
                                {l s='No text to translate' mod='ets_translate'}
                            {/if}
                        </div>
                    {elseif ((!$isInterTrans && (count($allLanguages) > 1 || (count($allLanguages) == 1 && $isLocalize && $allLanguages[0].id_lang !== 'en'))) || ($isInterTrans && count($allLanguages) > 1)) && $hasGoogleApiKey}
                        <div class="form-trans">
                            <div class="trans-data-info">
                                {if $configAutoEnable}
                                    <div class="row form-group">
                                        <label class="col-md-3">{l s='Translate from' mod='ets_translate'}:</label>
                                        <div class="col-md-9">
                                            {if $pageType == 'email' || $pageType == 'module' || $pageType == 'theme'}
                                                <span class="title_lang_source">
                                                <img src="{$imgDir|escape:'html':'UTF-8'}l/{$langSourceDefault.id_lang|escape:'html':'UTF-8'}.jpg" />
                                                {$langSourceDefault.name|escape:'html':'UTF-8'}
                                            </span>
                                            {else}
                                                <span class="title_lang_source">
                                                <img src="{$imgDir|escape:'html':'UTF-8'}l/{$langSource.id_lang|escape:'html':'UTF-8'}.jpg" />
                                                {$langSource.name|escape:'html':'UTF-8'}
                                            </span>
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-md-3">{l s='Translate to' mod='ets_translate'}:</label>
                                        <div class="col-md-9">
                                            {if $pageType == 'email' || $pageType == 'module' || $pageType == 'theme'}
                                                {if $langTargetDefault}
                                                    <span class="title_lang_target">
                                                        <img src="{$imgDir|escape:'html':'UTF-8'}l/{$langTargetDefault.id_lang|escape:'html':'UTF-8'}.jpg" />
                                                        {$langTargetDefault.name|escape:'html':'UTF-8'}
                                                    </span>
                                                {/if}
                                            {else}
                                                <span class="title_lang_target">
                                                    {if $langTarget}
                                                        {foreach $langTarget as $k=>$item}
                                                            {if $k < count($langTarget) - 1}
                                                                <img src="{$imgDir|escape:'html':'UTF-8'}l/{$item.id_lang|escape:'html':'UTF-8'}.jpg" />
                                                                        {$item.name|escape:'html':'UTF-8'},
                                                                {else}
                                                                    <img src="{$imgDir|escape:'html':'UTF-8'}l/{$item.id_lang|escape:'html':'UTF-8'}.jpg" />
                                                                {$item.name|escape:'html':'UTF-8'}
                                                            {/if}
                                                        {/foreach}
                                                    {else}
                                                        --
                                                    {/if}
                                                </span>
                                            {/if}
                                        </div>
                                    </div>

                                    {if $fieldTranslate}
                                        <div class="row form-group">
                                            <label class="col-md-3">{l s='How to translate' mod='ets_translate'}:</label>
                                            <div class="col-md-9">
                                                <span class="field_option_text">{$transOptions[$fieldTranslate].title|escape:'html':'UTF-8'}</span>
                                            </div>
                                        </div>
                                    {/if}

                                    {if $configAutoEnable}
                                    <div class="data-to-translate">
                                        <div class="row form-group">
                                            <label class="col-md-3">{l s='Data to translate' mod='ets_translate'}:</label>
                                            <div class="col-md-9">
                                        {if $pageType == 'all' && !$isInterTrans}
                                            {if isset($wdConfig) && $wdConfig}
                                                {include './tree_trans_all.tpl' treeWebTranslations=$treeWebTranslations wdConfig=$wdConfig}
                                                <input type="hidden" name="trans_wd" value="{$wdConfig|escape:'html':'UTF-8'}"/>
                                            {else}
                                                {l s='No data to translate' mod='ets_translate'}
                                            {/if}
                                        {else}
                                            <span>
                                                {if $pageType == 'theme'}
                                                    {if $sfType == 'themes'}
                                                        {l s='theme' mod='ets_translate'} {$selectedTheme|escape:'html':'UTF-8'}
                                                    {elseif $sfType == 'modules'}
                                                        {l s='module' mod='ets_translate'} {$selectedTheme|escape:'html':'UTF-8'}
                                                    {elseif $sfType == 'back'}
                                                        {l s='Back office' mod='ets_translate'}
                                                    {elseif $sfType == 'mails'}
                                                        {l s='Email subjects' mod='ets_translate'}
                                                    {elseif $sfType == 'others'}
                                                        {l s='Others' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'module'}
                                                    {l s='module' mod='ets_translate'} {$moduleName|escape:'html':'UTF-8'}
                                                {elseif $pageType == 'megamenu'}
                                                    {l s='Module mega menu' mod='ets_translate'}
                                                {elseif $pageType == 'pc'}
                                                    {l s='product comments' mod='ets_translate'}
                                                {elseif $pageType == 'blog'}
                                                    {if $blogType == 'category'}
                                                        {l s='Blog categories' mod='ets_translate'}
                                                    {elseif $blogType == 'post'}
                                                        {l s='Blog posts' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'all'}

                                                {elseif $pageType == 'inter'}
                                                    {if isset($isInterTrans) && $isInterTrans && isset($treeWebPageOption)}
                                                        <div class="tree-trans-wd-option {if !$configAutoEnable}always-show{/if}">
                                                            {include './tree_trans_option.tpl'}
                                                        </div>
                                                    {else}
                                                        {if isset($wdConfig) && $wdConfig}
                                                        <input type="hidden" name="trans_wd" value="{$wdConfig|escape:'html':'UTF-8'}"/>
                                                        {/if}
                                                    {/if}
                                                {else}<span class="total_items">
                                                    {$totalTranslate|escape:'html':'UTF-8'}</span>
                                                {/if}
                                                {if $pageType == 'product'}
                                                    {if $totalTranslate > 1}
                                                        {l s='products' mod='ets_translate'}
                                                    {else}
                                                        {l s='product' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'category'}
                                                    {if $totalTranslate > 1}
                                                        {l s='categories' mod='ets_translate'}
                                                    {else}
                                                        {l s='category' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'cms'}
                                                    {if $totalTranslate > 1}
                                                        {l s='CMSs' mod='ets_translate'}
                                                    {else}
                                                        {l s='CMS' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'cms_category'}
                                                    {if $totalTranslate > 1}
                                                        {l s='CMS categories' mod='ets_translate'}
                                                    {else}
                                                        {l s='CMS category' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'manufacturer'}
                                                    {if $totalTranslate > 1}
                                                        {l s='manufacturers' mod='ets_translate'}
                                                    {else}
                                                        {l s='manufacturer' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'supplier'}
                                                    {if $totalTranslate > 1}
                                                        {l s='suppliers' mod='ets_translate'}
                                                    {else}
                                                        {l s='supplier' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'attribute_group'}
                                                    {if $totalTranslate > 1}
                                                        {l s='attribute groups' mod='ets_translate'}
                                                    {else}
                                                        {l s='attribute group' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'attribute'}
                                                    {if $totalTranslate > 1}
                                                        {l s='attributes' mod='ets_translate'}
                                                    {else}
                                                        {l s='attribute' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'feature'}
                                                    {if $totalTranslate > 1}
                                                        {l s='features' mod='ets_translate'}
                                                    {else}
                                                        {l s='feature' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'feature_value'}
                                                    {if $totalTranslate > 1}
                                                        {l s='feature values' mod='ets_translate'}
                                                    {else}
                                                        {l s='feature value' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'blockreassurance'}
                                                    {if $totalTranslate > 1}
                                                        {l s='block reassurances' mod='ets_translate'}
                                                    {else}
                                                        {l s='block reassurance' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'ps_linklist'}
                                                    {if $totalTranslate > 1}
                                                        {l s='blocks' mod='ets_translate'}
                                                    {else}
                                                        {l s='block' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'ps_mainmenu'}
                                                    {if $totalTranslate > 1}
                                                        {l s='menu items' mod='ets_translate'}
                                                    {else}
                                                        {l s='menu item' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'ps_imageslider'}
                                                    {if $totalTranslate > 1}
                                                        {l s='slides' mod='ets_translate'}
                                                    {else}
                                                        {l s='slide' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'ps_customtext'}
                                                    {if $totalTranslate > 1}
                                                        {l s='text blocks' mod='ets_translate'}
                                                    {else}
                                                        {l s='text block' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'ets_extraproducttabs'}
                                                    {if $totalTranslate > 1}
                                                        {l s='tabs' mod='ets_translate'}
                                                    {else}
                                                        {l s='tab' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'email'}
                                                    {if $totalTranslate > 1}
                                                        {l s='emails' mod='ets_translate'}
                                                    {else}
                                                        {l s='email' mod='ets_translate'}
                                                    {/if}
                                                {elseif $pageType == 'theme' || $pageType == 'module' || $pageType == 'blog' || $pageType == 'megamenu' || $pageType == 'all'|| $pageType == 'inter'|| $pageType == 'pc'}
                                                {else}
                                                    {if $totalTranslate > 1}
                                                        {l s='text' mod='ets_translate'}
                                                    {else}
                                                        {l s='texts' mod='ets_translate'}
                                                    {/if}
                                                {/if}
                                            </span>
                                        {/if}
                                        </div>
                                        </div>
                                    </div>
                                    {/if}
                                    <div class="row form-group">
                                        <label class="col-md-3">&nbsp;</label>
                                        <div class="col-md-9 xs-hide">
                                            <a href="javascript:void(0)"
                                               class="btn btn-default btn-outline-secondary js-ets-trans-modify-settings"><i
                                                        class="fa fa-cogs"></i> {l s='Modify translation settings' mod='ets_translate'}
                                            </a>
                                        </div>
                                    </div>
                                {/if}
                            </div>
                            <div class="modify-setting {if $configAutoEnable}hide{/if}">
                                {if isset($pageType) && $pageType !== 'theme' && $pageType !== 'email' && $pageType !== 'module'}
                                    <div class="row form-group">
                                        <label class="col-md-3 ets_mt_6">{l s='Translate from' mod='ets_translate'}</label>
                                        <div class="col-md-9">
                                            <div class="group-fields">
                                                <div class="trans-lang-options b-none">
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle js-ets-trans-btn-lang-source"
                                                                type="button"
                                                                id="etsTransSelectLangSource" data-toggle="dropdown"
                                                                aria-haspopup="true"
                                                                aria-expanded="false">
                                                            <span class="text-html">
                                                                {if $configAutoEnable && $langSource}
                                                                    <img src="{$imgDir|escape:'html':'UTF-8'}l/{$langSource.id_lang|escape:'html':'UTF-8'}.jpg" /><span>{$langSource.name|escape:'html':'UTF-8'}</span>
    
                                                                {else}
                                                                    {foreach $allLanguages as $lang}
                                                                        {if $lang.id_lang == $idLangDefault}
                                                                            <img src="{$lang.flag|escape:'quotes':'UTF-8'}" />
                                                                            <span>{$lang.name|escape:'html':'UTF-8'}</span>
                                                                        {/if}
                                                                    {/foreach}
                                                                {/if}
                                                            </span>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu"
                                                             aria-labelledby="etsTransSelectLangSource">
                                                            {foreach $allLanguages as $lang}
                                                                <a class="dropdown-item js-ets-trans-lang-source"
                                                                   href="#"
                                                                   data-lang-id="{$lang.id_lang|escape:'html':'UTF-8'}">
                                                                    <img src="{$lang.flag|escape:'quotes':'UTF-8'}" />
                                                                    <span>{$lang.name|escape:'html':'UTF-8'}</span>
                                                                </a>
                                                            {/foreach}
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="trans_source"
                                                           value="{if $configAutoEnable && $langSource}{$langSource.id_lang|escape:'html':'UTF-8'}{else}{$idLangDefault|escape:'html':'UTF-8'}{/if}"/>
                                                    <input type="hidden" name="page_id"
                                                           value="{$pageId|escape:'html':'UTF-8'}"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-md-3 choose_list_lang ets_mt_6">{l s='Translate to' mod='ets_translate'}</label>
                                        <div class="col-md-9">
                                            <div class="group-fields group-fields-to">
                                                <div class="trans-lang-options pd-0">
                                                    <div class="ets_dropdown trans-lang-options_dropdown_content">
                                                        <button type="button" id="langTargetDropdown"
                                                                class="btn btn-default ">
                                                            <span class="text-html {if count($langTargetIds) == 1}single-lang{/if}">
                                                                {if !$langTargetIds}
                                                                    --
                                                                {else}
                                                                    {foreach $allLanguages as $lang}
                                                                        {if $configAutoEnable && in_array($lang.id_lang, $langTargetIds)}
                                                                            <img src="{$lang.flag|escape:'quotes':'UTF-8'}"/><span>{if count($langTargetIds) == 1}{$lang.name|escape:'html':'UTF-8'}{else}{$lang.iso_code|escape:'html':'UTF-8'}{/if}</span>{if $lang.id_lang != $langTargetIds[count($langTargetIds)-1]}, {/if}
                                                                        {/if}
                                                                    {/foreach}
                                                                {/if}
                                                            </span>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="langTargetDropdown">
                                                            <li>
                                                                <div class="form-check form-check-inline js-ets-trans-lang-target-all">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           id="etsTransSelectLangTarget_all"
                                                                           value="all"
                                                                           {if $configAutoEnable && count($langTargetIds) == count($allLanguages)-1}checked="checked"{/if}
                                                                    >
                                                                    <label for="etsTransSelectLangTarget_all">
                                                                        {l s='All languages' mod='ets_translate'}
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            {foreach $allLanguages as $lang}
                                                                <li>
                                                                    <div class="form-check form-check-inline js-ets-trans-lang-target lang-{$lang.id_lang|escape:'html':'UTF-8'} {if $configAutoEnable && $langSource && $langSource.id_lang == $lang.id_lang}hide{elseif !$configAutoEnable && $lang.id_lang == $idLangDefault}hide{/if}"
                                                                         data-isocode="{$lang.iso_code|escape:'html':'UTF-8'}">
                                                                        <input class="form-check-input js-ets-trans-lang-target-input"
                                                                               type="checkbox"
                                                                               id="etsTransSelectLangTarget_{$lang.id_lang|escape:'html':'UTF-8'}"
                                                                               value="{$lang.id_lang|escape:'html':'UTF-8'}"
                                                                               name="trans_target[]"
                                                                               {if $configAutoEnable && in_array($lang.id_lang, $langTargetIds)}checked="checked"{/if} />
                                                                        <label for="etsTransSelectLangTarget_{$lang.id_lang|escape:'html':'UTF-8'}">
                                                                            <img src="{$lang.flag|escape:'quotes':'UTF-8'}"/>
                                                                            <span>{$lang.name|escape:'html':'UTF-8'}</span>
                                                                        </label>
                                                                    </div>

                                                                </li>
                                                            {/foreach}
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                                {if isset($optionMailTrans) && $optionMailTrans}
                                    <div class="section-mail-options mt-3">
                                        <div class="row">
                                            <label class="col-lg-3">{l s='Select mails to translate' mod='ets_translate'}</label>
                                            
                                            <div class="col-lg-9">
                                                <div class="mail-options-selector">
                                                    <div class="form-check form-check-inline ets-trans-mail-option">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="etsTransMailOption_all"
                                                               value="all"
                                                               checked="checked">
                                                        <label class="form-check-label"
                                                               for="etsTransMailOption_all">
                                                            {l s='All' mod='ets_translate'}
                                                        </label>
                                                    </div>
                                                    {foreach $optionMailTrans as $key=>$option}
                                                        <div class="form-check form-check-inline ets-trans-mail-option">
                                                            <input class="form-check-input js-ets-trans-mail-option-item"
                                                                   type="checkbox"
                                                                   name="mail_option[]"
                                                                   id="etsTransMailOption_{$key|escape:'html':'UTF-8'}"
                                                                   checked="checked"
                                                                   value="{$option.key|escape:'html':'UTF-8'}"
                                                            >
                                                            <label class="form-check-label"
                                                                   for="etsTransMailOption_{$key|escape:'html':'UTF-8'}">
                                                                ({if $option.type == 'core_email'}{l s='Core email' mod='ets_translate'}{else}{l s='Module: ' mod='ets_translate'}{$option.name|escape:'html':'UTF-8'}{/if}
                                                                ) {$option.file|escape:'html':'UTF-8'}
                                                            </label>
                                                        </div>
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                                <div class="trans-options-type">
                                    <div class="row form-group">
                                        <label class="col-lg-3 col-md-3 mb_text_left">{l s='How to translate' mod='ets_translate'}</label>
                                        
                                        <div class="col-lg-9 col-md-9">
                                            {foreach $transOptions as $key=>$option}
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="trans_option"
                                                           id="etsTransOption_{$key|escape:'html':'UTF-8'}"
                                                           value="{$key|escape:'html':'UTF-8'}"
                                                            {if $configAutoEnable}
                                                                {if $fieldTranslate == $key}checked="checked"{/if}
                                                            {else}
                                                                {if isset($option.default) && $option.default}checked="checked"{/if}
                                                            {/if}
                                                    >
                                                    <label class="form-check-label"
                                                           for="etsTransOption_{$key|escape:'html':'UTF-8'}">{$option.title|escape:'html':'UTF-8'}</label>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {if !$configAutoEnable && (!isset($hideDataToTrans) || !$hideDataToTrans)}
                            <div class="data-to-translate">
                                <div class="row form-group">
                                    <label class="col-md-3">{l s='Data to translate' mod='ets_translate'}:</label>
                                    <div class="col-md-9">
                                    {if $pageType == 'all' && !$isInterTrans}
                                        {if isset($wdConfig)}
                                            {include './tree_trans_all.tpl' treeWebTranslations=$treeWebTranslations wdConfig=$wdConfig}
                                            <input type="hidden" name="trans_wd" value="{$wdConfig|escape:'html':'UTF-8'}"/>
                                        {else}
                                            {l s='No data to translate' mod='ets_translate'}
                                        {/if}
                                    {else}
                                        <span>
                                        {if $pageType != 'theme' && $pageType != 'all' && $pageType != 'inter' && $pageType != 'megamenu' && $pageType != 'blog'  && $pageType != 'inter'}
                                            {$totalTranslate|escape:'html':'UTF-8'}
                                            {if $pageType == 'product'}
                                                {if $totalTranslate > 1}
                                                    {l s='products' mod='ets_translate'}
                                                {else}
                                                    {l s='product' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'category'}
                                                {if $totalTranslate > 1}
                                                    {l s='categories' mod='ets_translate'}
                                                {else}
                                                    {l s='category' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'cms'}
                                                {if $totalTranslate > 1}
                                                    {l s='CMSs' mod='ets_translate'}
                                                {else}
                                                    {l s='CMS' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'cms_category'}
                                                {if $totalTranslate > 1}
                                                    {l s='CMS categories' mod='ets_translate'}
                                                {else}
                                                    {l s='CMS category' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'manufacturer'}
                                                {if $totalTranslate > 1}
                                                    {l s='manufacturers' mod='ets_translate'}
                                                {else}
                                                    {l s='manufacturer' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'supplier'}
                                                {if $totalTranslate > 1}
                                                    {l s='suppliers' mod='ets_translate'}
                                                {else}
                                                    {l s='supplier' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'attribute_group'}
                                                {if $totalTranslate > 1}
                                                    {l s='attribute groups' mod='ets_translate'}
                                                {else}
                                                    {l s='attribute group' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'attribute'}
                                                {if $totalTranslate > 1}
                                                    {l s='attributes' mod='ets_translate'}
                                                {else}
                                                    {l s='attribute' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'feature'}
                                                {if $totalTranslate > 1}
                                                    {l s='features' mod='ets_translate'}
                                                {else}
                                                    {l s='feature' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'feature_value'}
                                                {if $totalTranslate > 1}
                                                    {l s='feature values' mod='ets_translate'}
                                                {else}
                                                    {l s='feature value' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'blockreassurance'}
                                                {if $totalTranslate > 1}
                                                    {l s='block reassurances' mod='ets_translate'}
                                                {else}
                                                    {l s='block reassurance' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'ps_linklist'}
                                                {if $totalTranslate > 1}
                                                    {l s='blocks' mod='ets_translate'}
                                                {else}
                                                    {l s='block' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'ps_mainmenu'}
                                                {if $totalTranslate > 1}
                                                    {l s='menu items' mod='ets_translate'}
                                                {else}
                                                    {l s='menu item' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'ps_mainmenu'}
                                                {if $totalTranslate > 1}
                                                    {l s='slides' mod='ets_translate'}
                                                {else}
                                                    {l s='slide' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'ps_customtext'}
                                                {if $totalTranslate > 1}
                                                    {l s='text blocks' mod='ets_translate'}
                                                {else}
                                                    {l s='text block' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'ets_extraproducttabs'}
                                                {if $totalTranslate > 1}
                                                    {l s='tabs' mod='ets_translate'}
                                                {else}
                                                    {l s='tab' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'email'}
                                                {if $totalTranslate > 1}
                                                    {l s='emails' mod='ets_translate'}
                                                {else}
                                                    {l s='email' mod='ets_translate'}
                                                {/if}
                                            {elseif $pageType == 'pc'}
                                                {if $totalTranslate > 1}
                                                    {if $pcType == 'question'}
                                                        {l s='questions' mod='ets_translate'}
                                                    {else}
                                                        {l s='reviews' mod='ets_translate'}
                                                    {/if}
                                                {else}
                                                    {if $pcType == 'question'}
                                                        {l s='question' mod='ets_translate'}
                                                    {else}
                                                        {l s='review' mod='ets_translate'}
                                                    {/if}
                                                {/if}
                                            {else}
                                                {if $totalTranslate > 1}
                                                    {l s='texts' mod='ets_translate'}
                                                {else}
                                                    {l s='text' mod='ets_translate'}
                                                {/if}
                                            {/if}
                                        {elseif $pageType == 'all'}
                                            {l s='Some pages' mod='ets_translate'}
                                        {elseif $pageType == 'inter'}
                                            {l s='International / Translations' mod='ets_translate'}
                                        {elseif $pageType == 'megamenu'}
                                            {l s='Module mega menu' mod='ets_translate'}
                                        {elseif $pageType == 'pc'}
                                            {l s='product comments' mod='ets_translate'}
                                        {elseif $pageType == 'blog'}
                                            {if $blogType == 'category'}
                                                {l s='Blog categories' mod='ets_translate'}
                                            {elseif $blogType == 'post'}
                                                {l s='Blog posts' mod='ets_translate'}
                                            {/if}
                                        {else}
                                            {if $sfType == 'themes'}
                                                {l s='The theme' mod='ets_translate'} {$selectedTheme|escape:'html':'UTF-8'}
                                            {elseif $sfType == 'modules'}
                                                {l s='The module' mod='ets_translate'} {$selectedTheme|escape:'html':'UTF-8'}
                                            {elseif $sfType == 'mails'}
                                                {l s='The email subject' mod='ets_translate'}
                                            {elseif $sfType == 'back'}
                                                {l s='The Back office' mod='ets_translate'}
                                            {elseif $sfType == 'others'}
                                                {l s='Others' mod='ets_translate'}
                                            {/if}
                                        {/if}
                                    </span>
                                    {/if}
                                </div>
                                </div>
                            </div>
                            {/if}
                        </div>
                    {elseif !$hasGoogleApiKey}
                        <div class="alert alert-warning">
                            {l s='The Google translate API key is not configured. Please configure it by clicking' mod='ets_translate'}
                            <a
                                    href="{$linkConfigApi|escape:'quotes':'UTF-8'}"
                                    style="padding: 0;">{l s='here' mod='ets_translate'}</a>
                        </div>
                    {else}
                        <div class="alert alert-info">
                            {l s='Your shop has only one language and you do not need to translate' mod='ets_translate'}
                        </div>
                    {/if}
                    </div>
                    {include './popup_translating.tpl'}
                </div>
                <div class="panel_footer">
                    <div class="btn-group-trans btn-group-translate">
                        <a href="javascript:void(0)" class="btn-ets-trans-hide-modify-setting js-btn-ets-trans-hide-modify-setting btn btn-default btn-outline-secondary pull-left">{l s='Back to translate' mod='ets_translate'}</a>
                        {if $configAutoEnable}
                            <button type="button" class="btn btn-default btn-outline-secondary pull-left btn-group-translate-close" data-close="close">{l s='Cancel' mod='ets_translate'}</button>
                            {if $hasGoogleApiKey && ( ($isInterTrans && count($allLanguages) > 1) || (!$isInterTrans && (count($allLanguages) > 1 || (count($allLanguages) == 1 && $isLocalize  && $allLanguages[0].iso_code != 'en'))))}
                            <button type="button"
                                        class="btn btn-primary trans-multiple {if $enableAnalysis && $isTransAll}js-ets-trans-analysis-text{else}{if isset($pageType) && ($pageType == 'theme' || $pageType == 'email' || $pageType == 'module')}js-ets-trans-btn-inter-trans{else}js-ets-trans-btn-translate-page{/if}{/if}"
                                        data-total-item="{$totalTranslate|escape:'html':'UTF-8'}"
                                        data-page-type="{if isset($pageType)}{$pageType|escape:'html':'UTF-8'}{/if}"
                                        data-trans-all="{if $isTransAll}1{else}0{/if}"
                                        data-field="{if $fieldTrans}{$fieldTrans|escape:'html':'UTF-8'}{/if}"
                                        data-blog-type="{if isset($blogType)}{$blogType|escape:'html':'UTF-8'}{/if}"
                                >{l s='Translate' mod='ets_translate'}</button>
                            {/if}
                        {/if}
                        {if !$configAutoEnable}
                            <div class="trans-actions text-right">
                                <button type="button" class="btn btn-default btn-outline-secondary pull-left btn-group-translate-close" data-close="close">{l s='Cancel' mod='ets_translate'}</button>
                                {if $hasGoogleApiKey && ( ($isInterTrans && count($allLanguages) > 1) || (!$isInterTrans && (count($allLanguages) > 1 || (count($allLanguages) == 1 && $isLocalize  && $allLanguages[0].iso_code != 'en'))))}
                                <button type="button"
                                        class="btn btn-primary trans-multiple {if $enableAnalysis && $isTransAll}js-ets-trans-analysis-text{else}{if isset($pageType) && ($pageType == 'theme' || $pageType == 'email' || $pageType == 'module')}js-ets-trans-btn-inter-trans{else}js-ets-trans-btn-translate-page{/if}{/if}"
                                        data-total-item="{$totalTranslate|escape:'html':'UTF-8'}"
                                        data-page-type="{if isset($pageType)}{$pageType|escape:'html':'UTF-8'}{/if}"
                                        data-trans-all="{if $isTransAll}1{else}0{/if}"
                                        data-field="{if $fieldTrans}{$fieldTrans|escape:'html':'UTF-8'}{/if}"
                                        data-blog-type="{if isset($blogType)}{$blogType|escape:'html':'UTF-8'}{/if}" >
                                <span class="text-btn-translate">{l s='Translate' mod='ets_translate'}</span>
                                </button>
                                {/if}
                                <span class="append-btn-stop"></span>
                            </div>
                        {/if}
                    </div>

                    <div class="btn-group-trans btn-group-analysis-completed hide">
                        <button class="btn btn-default btn-outline-secondary js-ets-trans-analysis-cancel-trans pull-left">{l s='Cancel' mod='ets_translate'}</button>
                        <button class="btn btn-primary trans-multiple js-ets-trans-analysis-accept" data-total-item="" data-page-type="" data-trans-all="" data-field="">{l s='Confirm & translate now' mod='ets_translate'}</button>
                    </div>
                    <div class="btn-group-trans btn-group-translating hide">
                        <button type="button" class="btn btn-info js-ets-tran-btn-pause-translate"
                                data-page-type=""
                                data-nb-translated=""
                                data-nb-char=""
                                data-lang-source=""
                                data-lang-target=""
                                data-field-option=""
                        >{l s='Pause' mod='ets_translate'}</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-content -->
        </div>
        </div>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
