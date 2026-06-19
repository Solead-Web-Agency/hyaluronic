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
                        {l s='Translate' mod='ets_translate'}
                    </h4>
                </div>
                <div class="panel_body">
                    <div class="ets-trans-content">
                        <div class="form-errors"></div>
                        {if (count($allLanguages) > 1 || (count($allLanguages) == 1 && $allLanguages[0].id_lang !== 'en')) && $hasGoogleApiKey}
                            <div class="form-trans">
                                    {if isset($pageType) && $pageType !== 'theme' && $pageType !== 'email' && $pageType !== 'module' && $pageType !== 'all'}
                                        <div class="row form-group">
                                            <label class="col-lg-3 col-md-3">{l s='Translate from (source language)' mod='ets_translate'}</label>
                                            
                                            <div class="col-lg-9 col-md-9">
                                                <div class="trans-lang-options">
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle js-ets-trans-btn-lang-source" type="button"
                                                                id="etsTransSelectLangSource" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text-html">
                                                                {foreach $allLanguages as $lang}
                                                                    {if $lang.id_lang == $idLangDefault}
                                                                        <img src="{$lang.flag|escape:'quotes':'UTF-8'}" /><span>{$lang.name|escape:'html':'UTF-8'}</span>
                                                                    {/if}
                                                                {/foreach}
                                                            </span>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="etsTransSelectLangSource">
                                                            {foreach $allLanguages as $lang}
                                                                <a class="dropdown-item js-ets-trans-lang-source" href="#"
                                                                   data-lang-id="{$lang.id_lang|escape:'html':'UTF-8'}">
                                                                    <img src="{$lang.flag|escape:'quotes':'UTF-8'}">
                                                                    <span>{$lang.name|escape:'html':'UTF-8'}</span>
                                                                </a>
                                                            {/foreach}
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="trans_source" value="{$idLangDefault|escape:'html':'UTF-8'}" />
                                                    <input type="hidden" name="page_id" value="{if isset($pageId)}{$pageId|escape:'html':'UTF-8'}{/if}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="choose_list_lang ets_mt_6 col-lg-3 col-md-3">{l s='to (destination languages)' mod='ets_translate'}</label>
                                            
                                            <div class="col-lg-9 col-md-9">
                                                <div class="trans-lang-options">
                                                    <div class="trans-lang-options_dropdown_content ets_dropdown">
                                                        <button type="button" id="langTargetDropdown" class="btn btn-default" >
                                                            <span class="text-html {if count($langTargetIds) == 1}single-lang{/if}">
                                                                {if !$langTargetIds}
                                                                    --
                                                                {else}
                                                                    {foreach $allLanguages as $lang}
                                                                        {if $configAutoEnable && in_array($lang.id_lang, $langTargetIds)}
                                                                            <img src="{$lang.flag|escape:'quotes':'UTF-8'}" /><span>{if count($langTargetIds) == 1}{$lang.name|escape:'html':'UTF-8'}{else}{$lang.iso_code|escape:'html':'UTF-8'}{/if}</span>{if $lang.id_lang != $langTargetIds[count($langTargetIds)-1]}, {/if}
                                                                        {/if}
                                                                    {/foreach}
                                                                {/if}
                                                            </span>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="langTargetDropdown">
                                                            <li>
                                                                <div class="form-check form-check-inline js-ets-trans-lang-target-all">
                                                                    <input class="form-check-input" type="checkbox" id="etsTransSelectLangTarget_all" value="all"
                                                                           {if $configAutoEnable && count($langTargetIds) == count($allLanguages)-1}checked="checked"{/if} >
                                                                    <label for="etsTransSelectLangTarget_all">
                                                                        {l s='All languages' mod='ets_translate'}
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            {foreach $allLanguages as $lang}
                                                                <li>
                                                                    <div class="form-check form-check-inline js-ets-trans-lang-target lang-{$lang.id_lang|escape:'html':'UTF-8'} {if $configAutoEnable && $langSource && $langSource.id_lang == $lang.id_lang}hide{elseif !$configAutoEnable && $lang.id_lang == $idLangDefault}hide{/if}"
                                                                         data-isocode="{$lang.iso_code|escape:'html':'UTF-8'}">
                                                                        <input class="form-check-input js-ets-trans-lang-target-input" type="checkbox"
                                                                               id="etsTransSelectLangTarget_{$lang.id_lang|escape:'html':'UTF-8'}"
                                                                               value="{$lang.id_lang|escape:'html':'UTF-8'}" name="trans_target[]"
                                                                               {if $configAutoEnable && in_array($lang.id_lang, $langTargetIds)}checked="checked"{/if} />
                                                                        <label for="etsTransSelectLangTarget_{$lang.id_lang|escape:'html':'UTF-8'}">
                                                                            <img src="{$lang.flag|escape:'quotes':'UTF-8'}" />
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
                                    {/if}
                                    {if isset($optionMailTrans) && $optionMailTrans}
                                        <div class="section-mail-options mt-3">
                                            <div class="row form-group">
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
                                                                <input class="form-check-input js-ets-trans-mail-option-item" type="checkbox" name="mail_option[]"
                                                                       id="etsTransMailOption_{$key|escape:'html':'UTF-8'}"
                                                                       checked="checked"
                                                                       value="{$option.key|escape:'html':'UTF-8'}"
                                                                >
                                                                <label class="form-check-label"
                                                                       for="etsTransMailOption_{$key|escape:'html':'UTF-8'}">
                                                                    ({if $option.type == 'core_email'}{l s='Core email' mod='ets_translate'}{else}{l s='Module: ' mod='ets_translate'}{$option.name|escape:'html':'UTF-8'}{/if}) {$option.file|escape:'html':'UTF-8'}
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
                                                               {if isset($option.default) && $option.default}checked="checked"{/if}>
                                                        <label class="form-check-label"
                                                               for="etsTransOption_{$key|escape:'html':'UTF-8'}">{$option.title|escape:'html':'UTF-8'}</label>
                                                    </div>
                                                {/foreach}
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        {elseif !$hasGoogleApiKey}
                            <div class="alert alert-warning">
                                {l s='The Google translate API key is not configured. Please configure it by clicking' mod='ets_translate'} <a href="{$linkConfigApi|escape:'quotes':'UTF-8'}" style="padding: 0;">{l s='here' mod='ets_translate'}</a>
                            </div>
                        {else}
                            <div class="alert alert-info">
                                {l s='Your shop has only one language and you do not need to translate' mod='ets_translate'}
                            </div>
                        {/if}
                    </div>
                    {include './popup_translating.tpl'}
                    {include './popup_translating_field.tpl'}
                </div>
                <div class="panel_footer">
                    <div class="btn-group-trans btn-group-translate">
                        <button type="button" class="btn btn-default btn-outline-secondary pull-left btn-group-translate-close"
                                data-close="close">{l s='Cancel' mod='ets_translate'}</button>
                        <button type="button"
                                class="btn btn-primary {if isset($pageType) && ($pageType == 'theme' || $pageType == 'email' || $pageType == 'module')}js-ets-trans-btn-inter-trans{else}js-ets-trans-btn-translate-page{/if}"
                                data-page-type="{if isset($pageType)}{$pageType|escape:'html':'UTF-8'}{/if}"
                                data-trans-all="{if $isTransAll}1{else}0{/if}"
                                data-field="{if $fieldTrans}{$fieldTrans|escape:'html':'UTF-8'}{/if}"
                                data-blog-type="{if isset($blogType)}{$blogType|escape:'html':'UTF-8'}{/if}"
                        >
                            <span class="text-btn-translate">{l s='Translate' mod='ets_translate'}</span>
                        </button>
                        <span class="append-btn-stop"></span>
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
