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


{extends file="helpers/options/options.tpl"}
{block name="defaultOptions"}
    {if isset($table_bk) && $table_bk == $table}{capture name='table_count'}{counter name='table_count'}{/capture}{/if}
    {assign var='table_bk' value=$table scope='root'}
    <form action="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}" id="{if $table == null}configuration_form{else}{$table nofilter}_form{/if}{if isset($smarty.capture.table_count) && $smarty.capture.table_count}_{$smarty.capture.table_count|intval}{/if}" method="post" enctype="multipart/form-data" class="form-horizontal">
        {foreach $option_list AS $category => $categoryData}
        {if isset($categoryData['top'])}{$categoryData['top'] nofilter}{/if}
        <div class="panel {if isset($categoryData['class'])}{$categoryData['class'] nofilter}{/if}" id="{$table nofilter}_fieldset_{$category nofilter}">
            {* Options category title *}
            <div class="panel-heading">
                {if isset($categoryData['title'])}{$categoryData['title'] nofilter}{else}{l s='Options' mod='ets_seo'}{/if}
            </div>

            {* Category description *}

            {if (isset($categoryData['description']) && $categoryData['description'])}
                <div class="alert alert-info">{$categoryData['description'] nofilter}</div>
            {/if}
            {* Category info *}
            {if (isset($categoryData['info']) && $categoryData['info'])}
                <div>{$categoryData['info'] nofilter}</div>
            {/if}

            <div class="form-wrapper">
                <div class="ets-seo-import-export">
                    <div class="ets-seo-import-export-box">
                        <div class="box-item box-export">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-download"></i> {l s='Export' mod='ets_seo'}</h3>
                            </div>
                            <div class="box-body">
                                <p class="text-mute alert alert-info">{l s='Export SEO settings to XML file that can be restored via "IMPORT" panel.' mod='ets_seo'}</p>

                                <div class="shop-group">
                                    <div class="group-title">{l s='Shop(s) to export:' mod='ets_seo'}</div>
                                    {if isset($ets_seo_shops)}
                                        {foreach $ets_seo_shops as $item}
                                            <div class="form-group">
                                                <input type="checkbox" id="export_shops_{$item.id_shop|escape:'html':'UTF-8'}" name="export_shops[]"
                                                       value="{$item.id_shop|escape:'html':'UTF-8'}"  checked="checked" />
                                                <label for="export_shops_{$item.id_shop|escape:'html':'UTF-8'}">{$item.name|escape:'html':'UTF-8'}</label>
                                            </div>
                                        {/foreach}
                                    {/if}
                                </div>
                                <div class="seo-option-group">
                                    <div class="group-title">{l s='SEO settings to export:' mod='ets_seo'}</div>
                                    <div class="ets-seo-sub-title  ets-seo-general">{l s='General' mod='ets_seo'}</div>
                                    {if isset($ets_seo_options)}
                                        {foreach $ets_seo_options as $k=>$label}
                                            <div class="form-group">
                                                <input type="checkbox" id="export_seo_options_{$k|escape:'html':'UTF-8'}" name="export_seo_options[]"
                                                       value="{$k|escape:'html':'UTF-8'}"  checked="checked" />
                                                <label for="export_seo_options_{$k|escape:'html':'UTF-8'}">{$label|escape:'html':'UTF-8'}</label>
                                            </div>
                                            {if $k=='redirect'}
                                                <div class="ets-seo-sub-title ets-seo-pages">{l s='Pages (including keyphrases, advanced settings such as meta robots, canonical URL, etc. and social settings)' mod='ets_seo'}</div>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="exportData" class="btn btn-default btn-export js-ets-seo-export"><i class="fa fa-download"></i> {l s='Export data' mod='ets_seo'}</button>
                                </div>
                            </div>
                        </div>
                        <div class="box-item box-export">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-compress"></i> {l s='Import' mod='ets_seo'}</h3>
                            </div>
                            <div class="box-body">
                                <p class="text-mute alert alert-info">{l s='Restore a backup of SEO settings.' mod='ets_seo'}</p>

                                <div class="form-group">
                                    <input type="file" name="import_file">
                                </div>
                                <div class="shop-group">
                                    <div class="group-title">{l s='Shop(s) to import:' mod='ets_seo'}</div>
                                    {if isset($ets_seo_shops)}
                                        {foreach $ets_seo_shops as $item}
                                            <div class="form-group">
                                                <input type="checkbox" id="import_shops_{$item.id_shop|escape:'html':'UTF-8'}" name="import_shops[]"
                                                       value="{$item.id_shop|escape:'html':'UTF-8'}" checked="checked" />
                                                <label for="import_shops_{$item.id_shop|escape:'html':'UTF-8'}">{$item.name|escape:'html':'UTF-8'}</label>
                                            </div>
                                        {/foreach}
                                    {/if}
                                </div>
                                <div class="seo-option-group">
                                    <div class="group-title">{l s='SEO settings to import:' mod='ets_seo'}</div>
                                    <div class="ets-seo-sub-title  ets-seo-general">{l s='General' mod='ets_seo'}</div>
                                    {if isset($ets_seo_options)}
                                        {foreach $ets_seo_options as $k=>$label}
                                            <div class="form-group">
                                                <input type="checkbox" id="import_seo_options_{$k|escape:'html':'UTF-8'}" name="import_seo_options[]"
                                                       value="{$k|escape:'html':'UTF-8'}" checked="checked" />
                                                <label for="import_seo_options_{$k|escape:'html':'UTF-8'}">{$label|escape:'html':'UTF-8'}</label>
                                            </div>
                                            {if $k=='redirect'}
                                                <div class="ets-seo-sub-title ets-seo-pages">{l s='Pages (including keyphrases, advanced settings such as meta robots, canonical URL, etc. and social settings)' mod='ets_seo'}</div>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="importData" class="btn btn-default btn-export js-ets-seo-import"><i class="fa fa-compress"></i> {l s='Import data' mod='ets_seo'}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.form-wrapper -->

            </div>
            {/foreach}
            {hook h='displayAdminOptions'}
            {if isset($name_controller)}
                {capture name=hookName assign=hookName}display{$name_controller|ucfirst|escape:'html':'UTF-8'}Options{/capture}
                {hook h=$hookName}
            {elseif isset($smarty.get.controller)}
                {capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|htmlentities|escape:'html':'UTF-8'}Options{/capture}
                {hook h=$hookName}
            {/if}
    </form>
{/block}