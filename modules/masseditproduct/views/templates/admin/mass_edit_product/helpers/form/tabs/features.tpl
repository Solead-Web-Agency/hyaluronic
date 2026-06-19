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
    {if isset($feature_tab_html) && $feature_tab_html}
        {*{$feature_tab_html}*}
        <iframe id="seosaextendedfeatures"
                onload="$(this).parent().removeClass('loading'); initFeatures()"
                src="{$link->getAdminLink('AdminSeoSaExtendedFeatures')|no_escape}&id_product=-1&updateproduct">
        </iframe>
        <script>
            (function () {
                $('#seosaextendedfeatures').parent().addClass('loading');
                var features = {$features|json_encode};

                function initFeatures() {
                    var new_feature = [];
                    for (var key in features) {
                        new_feature[features[key]['name']] = features[key]['id_feature'];
                    }
                    var table_features = $('.table-features', window.parent.frames['seosaextendedfeatures'].contentWindow.document);
                    // console.log(table_features);
                    table_features.find('td').addClass('disabled_option_stage');
                    table_features.find('tr').each(function(){
                        var name = $(this).find('[ng-bind="feature.name"]').first().text();
                        var id_feature = new_feature[name];
                        $('<input>').attr('type', 'checkbox').attr('name', 'disabled[feature]['+ id_feature +']')
                            .addClass('disable_option_feature')
                            .prop('checked', true)
                            .val(new_feature[name])
                            .prependTo($(this).find('td').last());

                        $('<label>').addClass('string_delete_old control-label margin-right').text("{l s='Delete old' mod='masseditproduct' js=true}")
                            .prependTo($(this).find('td').last());

                        $('<input>').attr('type', 'checkbox').attr('name', 'delete_old[feature]['+ id_feature +']')
                            .addClass('delete_option_feature')
                            .val(0)
                            .prependTo($(this).find('td').last().find('.string_delete_old'));

                    });
                    $('.delete_option_feature', window.parent.frames['seosaextendedfeatures'].contentWindow.document).trigger('change');
                }
                window.initFeatures = initFeatures;
            })();
        </script>
    {elseif isset($form_multi_features) && $form_multi_features}
        <div id="features" class="mb-3">
            <div id="features-content" class="content">
                {*<h2 class="col-sm-10 col-md-5">{l s='Features' mod='masseditproduct'}</h2>*}

                <div class="form_switch_language form-group float-left">
                    <select id="form_switch_language" class="custom-select">
                        {foreach from=$languages item='language'}
                            <option value="{$language.iso_code|no_escape}" {if Context::getContext()->language->iso_code == $language.iso_code}selected="selected"{/if}>{$language.iso_code|no_escape}</option>
                        {/foreach}
                    </select>
                </div>

                <div style="clear:both;"></div>
                <div class="feature-collection nostyle" data-prototype='{$form_multi_features|no_escape}'></div>
            </div>
            <div class="row form-group">
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-primary sensitive add" id="add_feature_button"><i class="material-icons">add_circle</i> {l s='Add a feature' mod='masseditproduct'}</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label>
                        <input type="checkbox" name="old_feature_delete">{l s='To remove old features' mod='masseditproduct'}
                    </label>
                </div>
            </div>
        </div>
        <script>featuresCollection.init();</script>
    {else}
        <div class="row header_table form-group">
            <div class="col-sm-2">{l s='Feature' mod='masseditproduct'}:</div>
            <div class="col-sm-2">{l s='Available values' mod='masseditproduct'}:</div>
            <div class="col-sm-2">{l s='Other value' mod='masseditproduct'}:</div>
        </div>
        <div class="list_features">
            {foreach from=$features item=feature}
                {renderTemplate file="admin/mass_edit_product/helpers/form/row_feature.tpl" v=['feature' => $feature, 'languages' => $languages]}
            {/foreach}
        </div>
        {if $total_features > $count_feature_view}
            <a class="view_more_features" href="#">
                {l s='More' mod='masseditproduct'}
                (<span class="counter">{($total_features - $count_feature_view)|intval}</span>)
            </a>
        {/if}

        <div class="row">
            <div class="col-md-4">
                <label>
                    <input type="checkbox" name="old_feature_delete">{l s='To remove old features' mod='masseditproduct'}
                </label>
            </div>
        </div>
    {/if}
{/block}

{block name="submit"}

    <script type="text/javascript">
        initLanguages();
        var feature_pages = [];
        var total_features = {$total_features|intval};
        var count_feature_view = {$count_feature_view|intval};
        for (var i = 2; i <= Math.ceil(total_features/count_feature_view); i++)
            feature_pages.push(i);
    </script>
    {$smarty.block.parent}
{/block}