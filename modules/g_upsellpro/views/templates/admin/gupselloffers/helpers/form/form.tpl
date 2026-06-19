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
    {if $input.type == 'gupsell_open'}
        </div>
    </div>
    {elseif $input.type == 'gupsell_dashboard'}
        <script type="text/javascript">
            var ad  = "";
            var iso = "{$gupselllangisocode|escape:'html':'UTF-8'}";
            var gupselldelete_product_text_conf ="{l s='Delete Item ?' mod='g_upsellpro'}";
            var gupsellstautsactive ="{l s='Enable' mod='g_upsellpro'}";
            var gupsellstautsdisactive ="{l s='Disable' mod='g_upsellpro'}";
            var gupsellstautsedit ="{l s='Edit' mod='g_upsellpro'}";
            var gupsellstautsdelete ="{l s='Delete' mod='g_upsellpro'}";
            var gupsellstautsdeleteitemlabel ="{l s='Do you want to delete this item ?' mod='g_upsellpro'}";
            var gupsellurlpage = "{$link->getAdminLink('AdminGupselloffers')|escape:'html':'UTF-8'}";
            var gupselltype1 = "{l s='Normal upsell' mod='g_upsellpro'}";
            var gupselltype2 = "{l s='Bundle upsell' mod='g_upsellpro'}";
            var gupselltype3 = "{l s='Volume discount' mod='g_upsellpro'}";
            var gupselltype4 = "{l s='Frequently bought together' mod='g_upsellpro'}";
            var copyToClipboard_success = "{l s='Copy to clipboard successfully' mod='g_upsellpro'}";
            var hide_variants = "{l s='Hide Variants' mod='g_upsellpro'}";
            var hide_showvariants = "{l s='Show Variants' mod='g_upsellpro'}";
            var variants_selected = "{l s='variants selected' mod='g_upsellpro'}";
            var noproduct_search = "{l s='No products exists' mod='g_upsellpro'}";
            var nocollection_search = "{l s='No collection exists' mod='g_upsellpro'}";
            var title_selectitem = "{l s='Selected Product Items' mod='g_upsellpro'}";
            var title_productitem = "{l s='Selected Product Items' mod='g_upsellpro'}";
            var title_collectionitem = "{l s='Selected Collection Items' mod='g_upsellpro'}";
            var title_allproduct = "{l s='All Product' mod='g_upsellpro'}";
        </script>
        {*close panel- helper form*}
        <div class="form-group">
            <input type="hidden" class="title_volume_discount_setting" value="{l s='To "Volume discount", a random product will be displayed based on the configured quantity and product in "Additional" setting.' mod='g_upsellpro'}"/>
            <input type="hidden" class="id_upselloffers" id="id_upselloffers" value="{$upselloffersObj->id_g_upsellrule|escape:'html':'UTF-8'}"/>
        </div>
        <div class="form-group">
            <div class="col-lg-12">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <div class="form-group">
                    </div>
                    {if !isset($management) || empty($management)}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-12">
                                    <p class="gupsell-textanalytic">{l s='Your upsell performance in the last 30 days. See' mod='g_upsellpro'} <a href="{$link->getAdminLink('AdminGupsellanalytics')|escape:'html':'UTF-8'}" target="_blank">{l s='detailed analytics.' mod='g_upsellpro'}</a></p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <section>
                                    <div class="gupsell-dashboard-analytics-box">
                                        <div class="dashboard-analytics-box-left">
                                            <h2>{l s='VIEWS' mod='g_upsellpro'}</h2>
                                            <div class="dashboard-analytics-box-content">
                                                <div class="dashboard-analytics-box-content-left">
                                                    {$total_views|escape:'html':'UTF-8'}
                                                </div>
                                                <div class="dashboard-analytics-box-content-right highcharts">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="dashboard-analytics-box-center">
                                            <h2>{l s='SALES' mod='g_upsellpro'}</h2>
                                            <div class="dashboard-analytics-box-content">
                                                <div class="dashboard-analytics-box-content-left">
                                                    {$sales|escape:'html':'UTF-8'}
                                                </div>
                                                <div class="dashboard-analytics-box-content-right highcharts">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="dashboard-analytics-box-right">
                                            <h2>{l s='TAKE RATE' mod='g_upsellpro'}</h2>
                                            <div class="dashboard-analytics-box-content">
                                                <div class="dashboard-analytics-box-content-left">
                                                    {$take_rate|escape:'html':'UTF-8'}
                                                </div>
                                                <div class="dashboard-analytics-box-content-right highcharts">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="panel col-lg-12 gupdell-table-listsetting">
                                    <div class="gupsell_overlay"><div class="container"><div class="content"><div class="circle"></div></div></div></div>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{l s='Id' mod='g_upsellpro'}</th>
                                                <th>{l s='Name' mod='g_upsellpro'}</th>
                                                <th>{l s='Product' mod='g_upsellpro'}</th>
                                                <th>{l s='Show on' mod='g_upsellpro'}</th>
                                                <th>{l s='Display For' mod='g_upsellpro'}</th>
                                                <th>{l s='Last edited' mod='g_upsellpro'}</th>
                                                <th class="text-right">{l s='Action' mod='g_upsellpro'}</th>
                                            </tr> 
                                        </thead>
                                        <tbody>
                                            <td class="list-empty" colspan="7">
                                                <div class="list-empty-msg">
                                                    <i class="icon-warning-sign list-empty-icon"></i>
                                                    {l s='No records found' mod='g_upsellpro'}
                                                </div>
                                            </td>
                                        </tbody>
                                    </table>
                                    <div class="row">
                                        <div class="col-lg-4"></div>
                                        <div class="col-lg-4 text-center">
                                            <div class="pagination">
                                                {l s='Display' mod='g_upsellpro'}
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                    <span class="page_show_items_active">20</span>
                                                <i class="icon-caret-down"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a href="javascript:void(0);" class="pagination-items-page" data-items="20" data-list-id="gupsell">20</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="pagination-items-page" data-items="50" data-list-id="gupsell">50</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="pagination-items-page" data-items="100" data-list-id="gupsell">100</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="pagination-items-page" data-items="300" data-list-id="gupsell">300</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="pagination-items-page" data-items="1000" data-list-id="gupsell">1000</a>
                                                    </li>
                                                </ul>
                                                / <span class="page_show_items">20</span> {l s='result(s)' mod='g_upsellpro'}
                                                <input type="hidden" id="gupsell-pagination-items-page" name="gupsell_pagination" value="20">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 pagination-active">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group pull-center">
                            <a type="button" class="gbtn-default btn btn-default" href="{$link->getAdminLink('AdminGupselloffers')|escape:'html':'UTF-8'}&management=helperform">{l s='New Offer' mod='g_upsellpro'}</a>
                        </div>
                    {else}
                        <div class="form-group">
                            <h2>
                                {if $id_upselloffers > 0}
                                    {foreach from=$languages item=language}
                                        {if $languages|count > 1}
                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                        {/if}
                                            {$upselloffersObj->name[$language.id_lang]|escape:'html':'UTF-8'}
                                            {*if $upselloffersObj->active == 1}
                                                <span class="badge ghaeding-badge badge-success">{l s='Enable' mod='g_upsellpro'}</span>
                                            {else}
                                                <span class="badge ghaeding-badge badge-default">{l s='Disable' mod='g_upsellpro'}</span>
                                            {/if*}
                                        {if $languages|count > 1}
                                            </div>
                                        {/if}
                                    {/foreach}
                                {else}
                                    {l s='Offer' mod='g_upsellpro'}
                                {/if}
                            </h2>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="col-lg-6" id="upsell_box_setting">
                                        <div class="panel">
                                            <div class="form-group hidden">
                                                <input type="hidden" name="id_upselloffers" class="id_upselloffers" value="{$id_upselloffers|escape:'html':'UTF-8'}">
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label required">{l s='Offer name' mod='g_upsellpro'}</label>
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input data-type="text" type="text" name="name[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$upselloffersObj->name[$language.id_lang]|escape:'html':'UTF-8'}" class="form-control {if $language.id_lang == $defaultFormLanguage}settings-val{/if}" />
                                                                <p class="help-block gcolor-red"> <i class="icon-exclamation-sign"></i> {l s='This field is required.' mod='g_upsellpro'}</p>
                                                                <p class="help-block">{l s='For your own internal reference. Only you can see it.' mod='g_upsellpro'}</p>
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default gbtn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
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
                                            <div class="form-group">
                                                <label class="control-label required">{l s='Offer title' mod='g_upsellpro'}</label>
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input data-type="text" type="text" name="title[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$upselloffersObj->title[$language.id_lang]|escape:'html':'UTF-8'}" class="form-control {if $language.id_lang == $defaultFormLanguage}settings-val{/if}" />
                                                                <p class="help-block gcolor-red"> <i class="icon-exclamation-sign"></i> {l s='This field is required.' mod='g_upsellpro'}</p>
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default gbtn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
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
                                            <div class="form-group">
                                                <label class="control-label">{l s='Offer description' mod='g_upsellpro'}</label>
                                                <div class="form-group">
                                                    {foreach from=$languages item=language}
                                                        {if $languages|count > 1}
                                                            <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                        {/if}
                                                            <div class="col-lg-{if $languages|count > 1}10{else}12{/if}">
                                                                <input type="text" name="description[{$language.id_lang|escape:'html':'UTF-8'}]" value="{$upselloffersObj->description[$language.id_lang]|escape:'html':'UTF-8'}" class="form-control" />
                                                            </div>
                                                        {if $languages|count > 1}
                                                            <div class="col-lg-2">
                                                                <button type="button" class="btn btn-default gbtn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
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
                                        <div class="panel">
                                            <div clas="form-group">
                                                <h3>
                                                    {l s='Show on' mod='g_upsellpro'}
                                                </h3>
                                            </div>
                                            <div class="form-group">
                                                <div class="form-group">
                                                    <ul class="tab nav nav-tabs" id="upsellpro_showin_page">
                                                        {foreach from=$extra_setting_shows key=extra_setting_show item=extra_setting_show_text}
                                                            <li class="tabs-row disable">
                                                                <div class="gshowin_item">
                                                                    <div class="gshowin_item_container">
                                                                        <div class="gshowin_item_left">
                                                                            <span class="switch prestashop-switch fixed-width-lg">
                                                                                <input type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}" id="{$extra_setting_show|escape:'html':'UTF-8'}_on" value="1" {if $upselloffersObj->$extra_setting_show == 1} checked="checked"{/if}>
                                                                                <label for="{$extra_setting_show|escape:'html':'UTF-8'}_on">
                                                                                    {l s='ON' mod='g_upsellpro'}
                                                                                </label>
                                                                                <input type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}" id="{$extra_setting_show|escape:'html':'UTF-8'}_off" value="0" {if $upselloffersObj->$extra_setting_show == 0} checked="checked"{/if}>
                                                                                <label for="{$extra_setting_show|escape:'html':'UTF-8'}_off">{l s='OFF' mod='g_upsellpro'}</label>
                                                                                <a class="slide-button btn"></a>
                                                                            </span>
                                                                        </div>
                                                                        <div class="gshowin_item_center">
                                                                            {$extra_setting_show_text|escape:'html':'UTF-8'}
                                                                        </div>
                                                                        <div class="gshowin_item_right gbuttonconfig">
                                                                            <a class="btn btn-default box_setting_showin" href="#box_setting_showin" data-item-show="{$extra_setting_show|escape:'html':'UTF-8'}" title="{l s='Setting' mod='g_upsellpro'}">
                                                                                <i class="icon-cog"></i>
                                                                            </a>
                                                                            <a class="btn btn-default box_setting_preview" href="#box_setting_preview" data-item-show="{$extra_setting_show|escape:'html':'UTF-8'}" title="{l s='Preview' mod='g_upsellpro'}">
                                                                                <i class="icon-eye-open"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div style="display:none">
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_normal" type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}_type" value="normal" {if $_show_fields["{$extra_setting_show}_type"] == 'normal'}checked="checked"{/if} />
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_bundle" type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}_type" value="bundle" {if $_show_fields["{$extra_setting_show}_type"] == 'bundle'}checked="checked"{/if}/>
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_volume" type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}_type" value="volume" {if $_show_fields["{$extra_setting_show}_type"] == 'volume'}checked="checked"{/if}/>
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_frequently" type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}_type" value="frequently" {if $_show_fields["{$extra_setting_show}_type"] == 'frequently'}checked="checked"{/if}/>
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_popup" type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}_stype" value="popup" {if $_show_fields["{$extra_setting_show}_stype"] == 'popup'}checked="checked"{/if}/>
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_floating" type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}_stype" value="floating" {if $_show_fields["{$extra_setting_show}_stype"] == 'floating'}checked="checked"{/if}/>
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_inpage" type="radio" name="{$extra_setting_show|escape:'html':'UTF-8'}_stype" value="inpage" {if $_show_fields["{$extra_setting_show}_stype"] == 'inpage'}checked="checked"{/if}/>
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_position" type="text" name="{$extra_setting_show|escape:'html':'UTF-8'}_position" value="{$_show_fields["{$extra_setting_show}_position"]|escape:'html':'UTF-8'}"/>
                                                                    <input id="{$extra_setting_show|escape:'html':'UTF-8'}_addition" type="text" name="{$extra_setting_show|escape:'html':'UTF-8'}_addition" value="{$_show_fields["{$extra_setting_show}_addition"]|escape:'html':'UTF-8'}"/>
                                                                </div>
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box_setting_showin_box">
                                            <div class="box_setting_showin_top box_setting_showinclose"></div>
                                            <div class="" id="box_setting_showin">
                                                <div class="showin-heading-box">{l s='Edit Widget' mod='g_upsellpro'}</div>
                                                <div id="drop_conten">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            {l s='Widget Type' mod='g_upsellpro'}
                                                            <span class="glabel-tooltip"  data-toggle="tooltip" data-original-title="{l s='Type in which upsell should be displayed' mod='g_upsellpro'}"> 
                                                                <i class="icon-info-circle"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-lg-12 checktoltipwidth1 setuplable_for">
                                                            <div class="checkbox_item_wp col-lg-6 gupsell-nonepadding-left">
                                                                <label for="gupsell_normal" data-name="normal"> 
                                                                    <div class="gupsell_item_wp active" data-showtoltip="normal">
                                                                        <div class="gupsell_item_content">
                                                                            <span class="gupsellicon-img">
                                                                                <img class="imgm img-thumbnail" src="{$base_url|escape:'html':'UTF-8'}modules/g_upsellpro/views/img/Normal_upsell.png"/>
                                                                            </span>
                                                                            {l s='Normal upsell' mod='g_upsellpro'}
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                            <div class="checkbox_item_wp col-lg-6 gupsell-nonepadding-left">
                                                                <label for="gupsell_bundle" data-name="bundle">
                                                                    <div class="gupsell_item_wp" data-showtoltip="bundle">
                                                                        <div class="gupsell_item_content">
                                                                            <span class="gupsellicon-img">
                                                                                <img class="imgm img-thumbnail" src="{$base_url|escape:'html':'UTF-8'}modules/g_upsellpro/views/img/Bundle_upsell.png"/>
                                                                            </span>
                                                                            {l s='Bundle upsell' mod='g_upsellpro'}
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 checktoltipwidth2 setuplable_for">
                                                            <div class="checkbox_item_wp col-lg-6 gupsell-nonepadding-left">
                                                                <label for="gupsell_volume" data-name="volume">
                                                                    <div class="gupsell_item_wp" data-showtoltip="volume">
                                                                        <div class="gupsell_item_content">
                                                                            <span class="gupsellicon-img">
                                                                                <img class="imgm img-thumbnail" src="{$base_url|escape:'html':'UTF-8'}modules/g_upsellpro/views/img/Volume_discount.png"/>
                                                                            </span>
                                                                            {l s='Volume discount' mod='g_upsellpro'}
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                            <div class="checkbox_item_wp col-lg-6 gupsell-nonepadding-left">
                                                                <label for="gupsell_frequently" data-name="frequently">
                                                                    <div class="gupsell_item_wp" data-showtoltip="frequently">
                                                                        <div class="gupsell_item_content">
                                                                            <span class="gupsellicon-img">
                                                                                <img class="imgm img-thumbnail" src="{$base_url|escape:'html':'UTF-8'}modules/g_upsellpro/views/img/Frequently_bought_together.png"/>
                                                                            </span>
                                                                            {l s='Frequently bought together' mod='g_upsellpro'}
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="totip_extra_upsell">
                                                                <div class="totip_extra_content">
                                                                    <div class="totip_extra_content_arrow"></div>
                                                                    <div class="totip_extra_content_arrow_inner" role="tooltip">
                                                                        <div class="totip_extra_content_arrow_inner_toltip">
                                                                            <div class="totip_extra-text-container totip_extra__title">
                                                                                <div class="normal-totip gnone">
                                                                                    {l s='Create discounted offers' mod='g_upsellpro'}
                                                                                </div>
                                                                                <div class="bundle-totip gnone">
                                                                                    {l s='Create bundled offers' mod='g_upsellpro'}
                                                                                </div>
                                                                                <div class="volume-totip gnone">
                                                                                    {l s='Quantity based offers' mod='g_upsellpro'}
                                                                                </div>
                                                                                <div class="frequently-totip gnone">
                                                                                    {l s='Bought together Offer' mod='g_upsellpro'}
                                                                                </div>
                                                                            </div>
                                                                            <div class="totip_extra-text-container totip_extra__desc">
                                                                                <div class="normal-totip gnone">
                                                                                    <div class="totip_extra_ant-row">
                                                                                        {l s='Use Cases:' mod='g_upsellpro'}
                                                                                    </div>
                                                                                    <ul>
                                                                                        <li>{l s='Buy Product A and get Product B with 10% Off' mod='g_upsellpro'}</li>
                                                                                        <li>{l s='Buy Product A from Collection A and get Product B with $10 Off' mod='g_upsellpro'}</li>
                                                                                        <li>{l s='Purchase product A and get 100% FREE Product' mod='g_upsellpro'}</li>
                                                                                    </ul>
                                                                                </div>
                                                                                <div class="bundle-totip gnone">
                                                                                    <div class="totip_extra_ant-row">
                                                                                        {l s='Use Cases:' mod='g_upsellpro'}
                                                                                    </div>
                                                                                    <ul>
                                                                                        <li>{l s='Buy Product A and Product B for 10% Off' mod='g_upsellpro'}</li>
                                                                                        <li>{l s='Buy any product from Collection A and Product B with $10 Off' mod='g_upsellpro'}</li>
                                                                                        <li>{l s='Buy Product A & get Product B and C for 50% Off' mod='g_upsellpro'}</li>
                                                                                    </ul>
                                                                                </div>
                                                                                <div class="volume-totip gnone">
                                                                                    <div class="totip_extra_ant-row">
                                                                                        {l s='Use Cases:' mod='g_upsellpro'}
                                                                                    </div>
                                                                                    <ul>
                                                                                        <li>{l s='Buy 2 quantities of Product A for 10% Off and 3 quantities for 20% Off' mod='g_upsellpro'}</li>
                                                                                        <li>{l s='Buy any quantity greater than 10 (also mix and match different variants) and get 50% Off' mod='g_upsellpro'}</li>
                                                                                    </ul>
                                                                                </div>
                                                                                <div class="frequently-totip gnone">
                                                                                    <div class="totip_extra_ant-row">
                                                                                        {l s='Use Cases:' mod='g_upsellpro'}
                                                                                    </div>
                                                                                    <ul>
                                                                                        <li>{l s='Buy any Product A + B + C for 10% Off' mod='g_upsellpro'}</li>
                                                                                        <li>{l s='Buy Product A & get Product B and C for 50% Off' mod='g_upsellpro'}</li>
                                                                                    </ul>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">{l s='Widget Style' mod='g_upsellpro'} <span class="glabel-tooltip"  data-toggle="tooltip" data-original-title="{l s='Style in which upsell should be displayed' mod='g_upsellpro'}"> <i class="icon-info-circle"></i></span></label>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-lg-12 setuplable_for">
                                                            <div class="checkbox_item_wp gupsell-nonepadding-left none_cartpopup col-lg-4">
                                                                <label for="gupsell_popup" data-name="popup">
                                                                    <div class="gupsell_item_template active" data-showtoltip="popup">
                                                                        <div class="gupsell_item_content">
                                                                            <span class="gupsellicon-img">
                                                                                <img class="imgm img-thumbnail" src="{$base_url|escape:'html':'UTF-8'}modules/g_upsellpro/views/img/Popup.png"/>
                                                                            </span>
                                                                            {l s='Popup' mod='g_upsellpro'}
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <label for="gupsell_inpage" data-name="inpage">
                                                                    <div class="gupsell_item_template" data-showtoltip="inpage">
                                                                        <div class="gupsell_item_content">
                                                                            <span class="gupsellicon-img"><img class="imgm img-thumbnail" src="{$base_url|escape:'html':'UTF-8'}modules/g_upsellpro/views/img/In_page.png"/></span>
                                                                            {l s='In page' mod='g_upsellpro'}
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                            <div class="col-lg-4 none_cartpopup">
                                                                <label for="gupsell_floating" data-name="floating">
                                                                    <div class="gupsell_item_template" data-showtoltip="floating">
                                                                        <div class="gupsell_item_content">
                                                                            <span class="gupsellicon-img"><img class="imgm img-thumbnail" src="{$base_url|escape:'html':'UTF-8'}modules/g_upsellpro/views/img/Floating.png"/></span>
                                                                            {l s='Floating' mod='g_upsellpro'}
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group volume_select_show gnone ">
                                                        <label class="control-label">{l s='Additional' mod='g_upsellpro'} <span class="glabel-tooltip"  data-toggle="tooltip" data-original-title=""> <i class="icon-info-circle"></i></span></label>
                                                    </div>
                                                    <div class="form-group volume_select_show gnone ">
                                                        <div class="form-group-showextra-volume">
                                                            <div class="col-lg-12">
                                                                <table class="table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th><label class="control-label">{l s='Min Qty' mod='g_upsellpro'}</label></th>
                                                                            <th><label class="control-label">{l s='Discount Type' mod='g_upsellpro'}</label></th>
                                                                            <th><label class="control-label">{l s='Discount Value' mod='g_upsellpro'}</label></th>
                                                                            <th><label class="control-label">{l s='Most Popular' mod='g_upsellpro'}</label></th>
                                                                            <th><label class="control-label">{l s='' mod='g_upsellpro'}</label></th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        
                                                                    </tbody>
                                                                </table>
                                                                <div class="form-group">
                                                                    <button class="btn btn-default addnew_upsell_row" type="button" ><i class="icon-plus-sign"></i> {l s='New upsell row' mod='g_upsellpro'}</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="hr-gupsellpro-line"></p>
                                                    </div>
                                                </div>
                                                <div class="drop_footer">
                                                    <div class="drop_footer_row">
                                                        <button type="button" class="btn btn-default gbtn-default" id="box_setting_showinsave">{l s='Save' mod='g_upsellpro'}</button>
                                                        <button type="button" class="btn btn-default box_setting_showinclose">{l s='Cancel' mod='g_upsellpro'}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel">
                                            <div class="form-group">
                                                <h3>{l s='UPSELL PRODUCT' mod='g_upsellpro'}</h3>
                                            </div>
                                            <div class="form-group gupsell-checkrequired-input" id="specific_product">
                                                <div class="form-group">
                                                    <label class="control-label">{l s='Search for product' mod='g_upsellpro'}</label>
                                                    <div class="input-group">
                                                        <input placeholder="{l s='Searchable by product.' mod='g_upsellpro'}" class="gsearch_product" type="text" id="" name="">
                                                        <span class="input-group-addon"><i class="icon-search"></i></span>
                                                        <textarea style="display:none;" type="hidden" name="productids" id="gupsell-product-ids" data-type="text" class="form-control">{$productids|escape:'html':'UTF-8'}</textarea>
                                                        <textarea style="display:none;" type="hidden" name="gupsell-combin-ids" id="gupsell-combin-ids" data-type="text" class="form-control">{$upselloffersObj->product_combin_ids|escape:'html':'UTF-8'}</textarea>
                                                    </div>
                                                    <p class="help-block gcolor-red"> <i class="icon-exclamation-sign"></i> {l s='This Product is required.' mod='g_upsellpro'}</p>
                                                </div>
                                                <div class="form-group">
                                                    <a class="btn gbtn-default btn-default gupsellpopup_customproduct" href="#gupsellpopup_customproduct"><i class="icon-gift"></i> {l s='Custom Gift Products' mod='g_upsellpro'}</a>
                                                    <div id="gupsellpopup_customproduct" style="display: none">
                                                        <div class="panel gupsell-customproduct-box">
                                                            <div class="panel-heading">
                                                                {l s='Add product' mod='g_upsellpro'}
                                                            </div>
                                                            <div class="form-wrapper">
                                                                <div class="form-group">
                                                                    <label class="control-label required">{l s='Product Name' mod='g_upsellpro'}</label>
                                                                    <div class="form-group">
                                                                        {foreach from=$languages item=language}
                                                                            {if $languages|count > 1}
                                                                                <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                                            {/if}
                                                                                <div class="col-lg-{if $languages|count > 1}10{else}12{/if} gupsell-nonepadding-left">
                                                                                    <input data-type="text" type="text" name="gupselladdpro[name][{$language.id_lang|escape:'html':'UTF-8'}]" value="" class="form-control settings-val formgupsellpopup_customproduct" />
                                                                                    <p class="help-block gcolor-red"> <i class="icon-exclamation-sign"></i> {l s='This field is required.' mod='g_upsellpro'}</p>
                                                                                    <p class="help-block">{l s='The public name for this product. Invalid characters <>;=#{}' mod='g_upsellpro'}</p>
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
                                                                <div class="form-group gupsell-clickimg">
                                                                    <label class="control-label col-lg-12">{l s='Product Img' mod='g_upsellpro'}</label>
                                                                    <input type="file" class="hidden gupsellimg_productadd formgupsellpopup_customproduct" id="image_file" name="image_file">
                                                                    <div class="icon-default-custom">
                                                                        <span class="icon-default-content">
                                                                            <img src="">
                                                                            <i class="icon-plus"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label">{l s='Description' mod='g_upsellpro'}</label>
                                                                    <div class="form-group">
                                                                        {foreach from=$languages item=language}
                                                                            {if $languages|count > 1}
                                                                                <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                                            {/if}
                                                                                <div class="col-lg-{if $languages|count > 1}10{else}12{/if} gupsell-nonepadding-left">
                                                                                    <textarea class="rte autoload_rte gupsellautoload_rte formgupsellpopup_customproduct" name="gupselladdpro[description][{$language.id_lang|escape:'html':'UTF-8'}]" ></textarea>
                                                                                    <p class="help-block">{l s='Appears in the body of the product page.' mod='g_upsellpro'}</p>
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
                                                                <div class="form-group">
                                                                    <label class="control-label">{l s='Short description' mod='g_upsellpro'}</label>
                                                                    <div class="form-group">
                                                                        {foreach from=$languages item=language}
                                                                            {if $languages|count > 1}
                                                                                <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                                                                            {/if}
                                                                                <div class="col-lg-{if $languages|count > 1}10{else}12{/if} gupsell-nonepadding-left">
                                                                                    <textarea class="rte autoload_rte gupsellautoload_rte formgupsellpopup_customproduct" name="gupselladdpro[shortdescription][{$language.id_lang|escape:'html':'UTF-8'}]" ></textarea>
                                                                                    <p class="help-block">{l s='Appears in the product list(s), and at the top of the product page.' mod='g_upsellpro'}</p>
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
                                                                <div class="form-group">
                                                                    <label class="control-label">{l s='Product Price' mod='g_upsellpro'}</label>
                                                                    <div class="input-group">
                                                                        <input type="number" maxlength="27" size="11" name="gupselladdpro[price]" class="form-control formgupsellpopup_customproduct" value=""/>
                                                                        <span class="input-group-addon">{$gupsellid_currency->iso_code|escape:'html':'UTF-8'}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="panel-footer">
                                                                <button class="btn btn-default pull-right gbtn-default-cancel gupsell-cancel-fancybox">{l s='Cancel' mod='g_upsellpro'}</button>
                                                                <button class="btn gbtn-default btn-default pull-right gupsell-add-fancybox" id="submitAddproductUpsell">
                                                                    <i class="icon-refresh icon-spin icon-fw" style="display: none;"></i>
                                                                    {l s='Add product' mod='g_upsellpro'}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="box_setting_product_collection">
                                                        <div class="box_setting_showin_top box_setting_displayclose"></div>
                                                        <div class="" id="box_setting_display">
                                                            <div class="showin-heading-box">{l s='Select Upsell Products' mod='g_upsellpro'}</div>
                                                            <div id="drop_conten_display">
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <input placeholder="{l s='Searchable by product  name, id, Reference code.' mod='g_upsellpro'}" class="ac_input url_product" type="text" id="gupsell_search_product" autocomplete="off" name="">
                                                                        <span class="input-group-addon"><i class="icon-search"></i></span>
                                                                    </div>
                                                                    <div clas="gnone">
                                                                        <input type="hidden" class="itempage_product_search" value="0"/>
                                                                        <textarea style="display:none;" type="hidden" id="gupsell-product-ids-new" data-type="text" class="form-control"></textarea>
                                                                        <textarea style="display:none;" type="hidden" id="gupsell-combin-ids-new" data-type="text" class="form-control"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group show_selectproduct">
                                                                </div>
                                                                <div class="form-group show_htmlsearch">
                                                                </div>
                                                            </div>
                                                            <div class="drop_footer">
                                                                <div class="drop_footer_row">
                                                                    <button type="button" class="btn btn-default gbtn-default" id="box_setting_showinsavedisplay">{l s='Save' mod='g_upsellpro'}</button>
                                                                    <button type="button" class="btn btn-default box_setting_displayclose">{l s='Cancel' mod='g_upsellpro'}</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="gupsell-maxheight500px-scoll">
                                                        <table class="table" id="gupsell_static_products">
                                                            <thead>
                                                                <tr>
                                                                    <th>{l s='Id' mod='g_upsellpro'}</th>
                                                                    <th>{l s='Image' mod='g_upsellpro'}</th>
                                                                    <th>{l s='Name' mod='g_upsellpro'}</th>
                                                                    <th>{l s='Most Popular' mod='g_upsellpro'}</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {$productidshtml|escape:'quotes':'UTF-8'}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <p class="ghr-line"></p>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">{l s='Min Qty' mod='g_upsellpro'}</label>
                                                <input type="number" min="1" placeholder="0" class="form-control" id="gupsell_min_qty" autocomplete="off" name="gupsell_min_qty" value="{$upselloffersObj->qty|escape:'html':'UTF-8'}"/>
                                            </div>
                                            <div class="form-group">
                                                <p class="ghr-line"></p>
                                            </div>
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="gupsell_applydiscount_for">
                                                        <input type="checkbox" name="gupsell_applydiscount" id="gupsell_applydiscount_for" value="1" {if $upselloffersObj->apply_discount == 1} checked="checked"{/if}>
                                                        {l s='Apply discount' mod='g_upsellpro'}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group gupsell-applydiscount gnone {if $upselloffersObj->apply_discount == 1} active{/if}">
                                                <label class="control-label">{l s='Discount Type' mod='g_upsellpro'}</label>
                                                <div class="">
                                                    <div class="radio t">
                                                        <label class="col-lg-6">
                                                            <input type="radio" name="gupsell_applydiscounttype" id="gupsell_applydiscount1" value="1" {if $upselloffersObj->type_discount == 1} checked="checked"{/if}>
                                                            {l s='Percentage (%)' mod='g_upsellpro'}
                                                        </label>
                                                        <label class="col-lg-6">
                                                            <input type="radio" name="gupsell_applydiscounttype" id="gupsell_applydiscount0" value="0" {if $upselloffersObj->type_discount == 0} checked="checked"{/if}>
                                                            {l s='Amount' mod='g_upsellpro'}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group gupsell-applydiscount gnone  {if $upselloffersObj->apply_discount == 1}active{/if}">
                                                <label class="control-label">{l s='Discount Value' mod='g_upsellpro'}</label>
                                                <div class="select">
                                                    <div class="col-lg-4 gupsell-nonepadding-left">
                                                        <input class="form-control" type="number" min="0" placeholder="0" name="amount_discount" id="amount_discount" value="{$upselloffersObj->amount_discount|escape:'html':'UTF-8'}">
                                                    </div>
                                                    <div class="col-lg-4 gupsell-applydiscount-amount gnone {if $upselloffersObj->type_discount == 0}active{/if}">
                                                        <select name="id_currency_discount" id="id_currency_discount">
                                                            {foreach from=$gupsellcurrencies item=curren}
                                                                <option value="{$curren['id_currency']|escape:'html':'UTF-8'}" {if $upselloffersObj->id_currency_discount == $curren['id_currency']} selected="selected"{/if}>{$curren['name']|escape:'html':'UTF-8'}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-4 gupsell-applydiscount-amount gnone {if $upselloffersObj->type_discount == 0}active{/if}">
                                                        <select name="tax_discount" id="tax_discount">
                                                            <option value="0" {if $upselloffersObj->tax_discount == 0}selected="selected"{/if}>{l s='Tax excluded' mod='g_upsellpro'}</option>
                                                            <option value="1" {if $upselloffersObj->tax_discount == 1}selected="selected"{/if}>{l s='Tax included' mod='g_upsellpro'}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel">
                                            <div class="form-group">
                                                <h3>{l s='DISPLAYS FOR' mod='g_upsellpro'}</h3>
                                            </div>
                                            <div class="form-group">
                                                <div class="">
                                                    <ul class="nav">
                                                        <li>
                                                            <div class="radio">
                                                                <label class="col-lg-12">
                                                                    <input type="radio" name="gupsell_display" id="gupsell_display0" value="all_product" {if $upselloffersObj->display_product == 'all_product'}checked="checked"{/if}>
                                                                    {l s='All products' mod='g_upsellpro'}
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="radio">
                                                                <label class="col-lg-12">
                                                                    <input type="radio" name="gupsell_display" id="gupsell_display1" value="specific_product" {if $upselloffersObj->display_product == 'specific_product'}checked="checked"{/if}>
                                                                    {l s='Specific products' mod='g_upsellpro'}
                                                                </label>
                                                            </div> 
                                                        </li>
                                                        <li>
                                                            <div class="radio">
                                                                <label class="col-lg-12">
                                                                    <input type="radio" name="gupsell_display" id="gupsell_display2" value="collections_product" {if $upselloffersObj->display_product == 'collections_product'}checked="checked"{/if}>
                                                                    {l s='Specific collections' mod='g_upsellpro'}
                                                                </label>
                                                            </div> 
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="form-group gnone {if $upselloffersObj->display_product == 'specific_product'}active{/if}" id="specific_product_display">
                                                <div class="form-group gupsell-checkrequired-input-display">
                                                    <label class="control-label">{l s='Search Product' mod='g_upsellpro'}</label>
                                                    <div class="input-group">
                                                        <input placeholder="{l s='Searchable by product.' mod='g_upsellpro'}" class="gsearch_displayproduct" type="text">
                                                        <span class="input-group-addon"><i class="icon-search"></i></span>
                                                        <textarea style="display:none;" type="hidden" name="gupsell-displayforproduct-ids" id="gupsell-displayforproduct-ids" data-type="text" class="form-control">{$upselloffersObj->product_displayids|escape:'html':'UTF-8'}</textarea>
                                                        <textarea style="display:none;" type="hidden" name="gupsell-displayforcombin-ids" id="gupsell-displayforcombin-ids" data-type="text" class="form-control">{$upselloffersObj->product_displaycombin_ids|escape:'html':'UTF-8'}</textarea>
                                                    </div>
                                                    <p class="help-block gcolor-red"> <i class="icon-exclamation-sign"></i> {l s='This Product Display is required.' mod='g_upsellpro'}</p>
                                                </div>
                                                <div class="form-group">
                                                    <div class="gupsell-maxheight500px-scoll">
                                                        <table class="table" id="gupsell_static_productsdisplayfor">
                                                            <thead>
                                                                <tr>
                                                                    <th>{l s='Id' mod='g_upsellpro'}</th>
                                                                    <th>{l s='Image' mod='g_upsellpro'}</th>
                                                                    <th>{l s='Name' mod='g_upsellpro'}</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {$productidshtmldisplay|escape:'quotes':'UTF-8'}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="box_setting_displayforproduct">
                                                    <div class="box_setting_showin_top box_setting_displayforclose"></div>
                                                    <div class="" id="box_displayforsetting_display">
                                                        <div class="showin-heading-box">{l s='Select Upsell Products Trigger' mod='g_upsellpro'}</div>
                                                        <div id="drop_displayforconten">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <input placeholder="{l s='Searchable by product  name, id, Reference code.' mod='g_upsellpro'}" class="ac_input url_product" type="text" id="gupsell_productforsearch" autocomplete="off" name="">
                                                                    <span class="input-group-addon"><i class="icon-search"></i></span>
                                                                </div>
                                                                <div clas="gnone">
                                                                    <input type="hidden" class="itempage_displayfor_search" value="0"/>
                                                                    <textarea style="display:none;" type="hidden" id="gupsell-displayforproduct-ids-new" data-type="text" class="form-control"></textarea>
                                                                    <textarea style="display:none;" type="hidden" id="gupsell-displayforcombin-ids-new" data-type="text" class="form-control"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group show_displayforselectproduct">
                                                            </div>
                                                            <div class="form-group show_displayforhtmlsearch">
                                                            </div>
                                                        </div>
                                                        <div class="drop_footer">
                                                            <div class="drop_footer_row">
                                                                <button type="button" class="btn btn-default gbtn-default" id="box_displayforsetting_showinsave">{l s='Save' mod='g_upsellpro'}</button>
                                                                <button type="button" class="btn btn-default box_setting_displayforclose">{l s='Cancel' mod='g_upsellpro'}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group gupsell-checkrequired-chosen-display gnone {if $upselloffersObj->display_product == 'collections_product'}active{/if}" id="collections_product_display">
                                                <label class="control-label">{l s='Search for collection' mod='g_upsellpro'}</label>
                                                <div class="input-group">
                                                    <input placeholder="{l s='Searchable by Collections.' mod='g_upsellpro'}" class="gsearch_collections" type="text">
                                                    <span class="input-group-addon"><i class="icon-search"></i></span>
                                                    <textarea style="display:none;" type="hidden" name="gupsell-collections-ids" id="gupsell-collections-ids" data-type="text" class="form-control">{$upselloffersObj->display_cateids|escape:'html':'UTF-8'}</textarea>
                                                </div>
                                                <p class="help-block gcolor-red"> <i class="icon-exclamation-sign"></i> {l s='This Category Display is required.' mod='g_upsellpro'}</p>
                                            </div>
                                            <div class="form-group gupsell-checkrequired-chosen-display gnone {if $upselloffersObj->display_product == 'collections_product'}active{/if}" id="collections_producttable_display">
                                                <div class="gupsell-maxheight500px-scoll">
                                                    <table class="table" id="gupsell_static_collections">
                                                        <thead>
                                                            <tr>
                                                                <th>{l s='Id' mod='g_upsellpro'}</th>
                                                                <th>{l s='Image' mod='g_upsellpro'}</th>
                                                                <th>{l s='Name' mod='g_upsellpro'}</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {$gupsell_catsdisplay|escape:'quotes':'UTF-8'}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="box_collections">
                                                    <div class="box_collections_showin_top box_collections_close"></div>
                                                    <div class="" id="box_collections_display">
                                                        <div class="showin-heading-box">{l s='Select Upsell Collections Trigger' mod='g_upsellpro'}</div>
                                                        <div id="drop_collections">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <input placeholder="{l s='Searchable by collection name, id, Reference code.' mod='g_upsellpro'}" class="ac_input url_product" type="text" id="gupsell_collectionsearch" autocomplete="off" name="">
                                                                    <span class="input-group-addon"><i class="icon-search"></i></span>
                                                                </div>
                                                                <div clas="gnone">
                                                                    <input type="hidden" class="itempage_collection_search" value="0"/>
                                                                    <textarea style="display:none;" type="hidden" id="gupsell-collections-ids-new" data-type="text" class="form-control"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group show_selectcollection">
                                                            </div>
                                                            <div class="form-group show_htmlcollection">
                                                            </div>
                                                        </div>
                                                        <div class="drop_footer">
                                                            <div class="drop_footer_row">
                                                                <button type="button" class="btn btn-default gbtn-default" id="box_collection_showinsave">{l s='Save' mod='g_upsellpro'}</button>
                                                                <button type="button" class="btn btn-default box_collections_close">{l s='Cancel' mod='g_upsellpro'}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel upsell-panellabel">
                                            <div class="form-group">
                                                <h3>{l s='ADDITIONAL SETTINGS' mod='g_upsellpro'}</h3>
                                            </div>
                                            <div class="form-group upsell-panellabel-additional">
                                                <label class="control-label">{l s='Offer order' mod='g_upsellpro'}</label>
                                                <div class="select">
                                                    <select name="gupsellposition" id="gupsellposition">
                                                        <option value="1" {if $upselloffersObj->position == '1'}selected="selected"{/if}>{l s='1 st' mod='g_upsellpro'}</option>
                                                        <option value="2" {if $upselloffersObj->position == '2'}selected="selected"{/if}>{l s='2 nd' mod='g_upsellpro'}</option>
                                                        <option value="3" {if $upselloffersObj->position == '3'}selected="selected"{/if}>{l s='3 rd' mod='g_upsellpro'}</option>
                                                        <option value="4" {if $upselloffersObj->position == '4'}selected="selected"{/if}>{l s='4 th' mod='g_upsellpro'}</option>
                                                        <option value="5" {if $upselloffersObj->position == '5'}selected="selected"{/if}>{l s='5 th' mod='g_upsellpro'}</option>
                                                        <option value="6" {if $upselloffersObj->position == '6'}selected="selected"{/if}>{l s='6 th' mod='g_upsellpro'}</option>
                                                        <option value="7" {if $upselloffersObj->position == '7'}selected="selected"{/if}>{l s='7 th' mod='g_upsellpro'}</option>
                                                        <option value="8" {if $upselloffersObj->position == '8'}selected="selected"{/if}>{l s='8 th' mod='g_upsellpro'}</option>
                                                        <option value="9" {if $upselloffersObj->position == '9'}selected="selected"{/if}>{l s='9 th' mod='g_upsellpro'}</option>
                                                        <option value="10" {if $upselloffersObj->position == '10'}selected="selected"{/if}>{l s='10 th' mod='g_upsellpro'}</option>
                                                    </select>
                                                    <p class="help-block">{l s='The offer with the lowest order number will be displayed first.' mod='g_upsellpro'}</p>
                                                </div>
                                            </div>
                                            <div class="form-group upsell-panellabel-additional">
                                                <div class="checkbox">
                                                    <label for="showoffers_for">
                                                        <input type="checkbox" name="showoffers" id="showoffers_for" value="1" {if $upselloffersObj->showoffers == 1}checked="checked"{/if}>
                                                        {l s='Offer products already in cart/order' mod='g_upsellpro'}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group upsell-panellabel-additional">
                                                <div class="checkbox">
                                                    <label for="gupsell_upsellremoved_for">
                                                        <input type="checkbox" name="gupsell_upsellremoved" id="gupsell_upsellremoved_for" value="1" {if $upselloffersObj->remove_product == 1}checked="checked"{/if}>
                                                        {l s='True upsell (upgrade): Remove parent product while upsell product(s) is added' mod='g_upsellpro'}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group upsell-panellabel-additional">
                                                <div class="checkbox">
                                                    <label for="gupsell_productremoved_upsell_for">
                                                        <input type="checkbox" name="gupsell_productremoved_upsell" id="gupsell_productremoved_upsell_for" value="1" {if $upselloffersObj->remove_product_upsell == 1}checked="checked"{/if}  {if $upselloffersObj->remove_product == 1}disabled{/if}>
                                                        {l s='Remove upsell product when parent product is removed' mod='g_upsellpro'}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group upsell-panellabel-additional">
                                                <div class="checkbox">
                                                    <label for="gupsell_customqty_for">
                                                        <input type="checkbox" name="gupsell_customqty" id="gupsell_customqty_for" value="1" {if $upselloffersObj->display_customqty == 1}checked="checked"{/if}>
                                                        {l s='Display custom product quantity' mod='g_upsellpro'}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group upsell-panellabel-additional">
                                                <label class="control-label">{l s='Displays for specified price range' mod='g_upsellpro'}</label>
                                                <div class="">
                                                    <ul class="nav">
                                                        <li>
                                                            <div class="radio">
                                                                <label class="col-lg-12">
                                                                    <input type="radio" name="gupsell_specified_range" id="gupsell_specified_range0" value="0" {if $upselloffersObj->type_price == 0}checked="checked"{/if}>
                                                                    {l s='All product prices' mod='g_upsellpro'}
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="radio">
                                                                <label class="col-lg-12">
                                                                    <input type="radio" name="gupsell_specified_range" id="gupsell_specified_range1" value="1" {if $upselloffersObj->type_price == 1}checked="checked"{/if}>
                                                                    {l s='Products in specific price range' mod='g_upsellpro'}
                                                                </label>
                                                            </div> 
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="form-group upsell-panellabel-additional">
                                                <div class=" gupsell-specified-range gnone {if $upselloffersObj->type_price == 1}active{/if}">
                                                    <div class="input-group">
                                                        {foreach from=$gupsellcurrencies item=gupsellcurrencie}
                                                            <div class="currencie-field curen-{$gupsellcurrencie['id_currency']|escape:'html':'UTF-8'}" {if $gupsellcurrencie['id_currency'] != $gupsellid_currency->id}style="display:none"{/if}>
                                                                <div class="col-lg-5">
                                                                    <label class="control-label">{l s='Minimum price' mod='g_upsellpro'}</label>
                                                                    <input type="text" class="mincartamount_{$gupsellcurrencie['id_currency']|escape:'html':'UTF-8'}" name="minamount[{$gupsellcurrencie['id_currency']|escape:'html':'UTF-8'}]" value="{if isset($upsellminamount) && !empty($upsellminamount)}{$upsellminamount[$gupsellcurrencie['id_currency']|escape:'html':'UTF-8']}{/if}" onchange="this.value = this.value.replace(/,/g, '.');" onkeypress="return isNumberKey(event)"/>
                                                                </div>
                                                                <div class="col-lg-5">
                                                                    <label class="control-label">{l s='Maximum price' mod='g_upsellpro'}</label>
                                                                    <input type="text" class="mincartamount_{$gupsellcurrencie['id_currency']|escape:'html':'UTF-8'}" placeholder="{l s='No limit' mod='g_upsellpro'}" name="maxamount[{$gupsellcurrencie['id_currency']|escape:'html':'UTF-8'}]" value="{if isset($upsellmaxamount) && !empty($upsellmaxamount)}{$upsellmaxamount[$gupsellcurrencie['id_currency']|escape:'html':'UTF-8']}{/if}" onchange="this.value = this.value.replace(/,/g, '.');" onkeypress="return isNumberKey(event)"/>
                                                                </div>
                                                                <div class="col-lg-2">
                                                                    <label class="control-label col-lg-12 text-left">{l s='Currency' mod='g_upsellpro'}</label>
                                                                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                                                        {$gupsellcurrencie['sign']|escape:'html':'UTF-8'}
                                                                        <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        {foreach from=$gupsellcurrencies item=curren}
                                                                            <li><a href="javascript:hideOtherCurreny({$curren['id_currency']|escape:'html':'UTF-8'});" tabindex="-1">{$curren['name']|escape:'html':'UTF-8'}</a></li>
                                                                        {/foreach}
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        {/foreach}
                                                    </div>
                                                    <p class="help-block">{l s='Upsell only shows when product in specified price range is added to the cart.' mod='g_upsellpro'}</p>
                                                </div>
                                            </div>
                    {/if}

    {elseif $input.type == 'gupsell_end'}
                {if isset($management) || !empty($management)}
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group gupsell-formrelacted">
                                    <div class="panel">
                                        <h3> {l s='PREVIEW' mod='g_upsellpro'} <span class="pull-right icon-refresh-extra label-tooltip" title="" data-toggle="tooltip" data-original-title="{l s='Refresh preview' mod='g_upsellpro'}" data-html="true" data-placement="top"><i class="icon-refresh "></i></span></h3>
                                        <div class="preview-extra-header">
                                            <div class="preview-extra-header-content"><h3 class="preview-extra-header-title" style="width: 38%;"></h3>
                                            <ul class="apreview-extra-header-paragraph"><li></li><li></li><li></li></ul>
                                            </div>
                                            <div class="preview-html"></div>
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
                    <div class="col-lg-12">
                        <a class="btn btn-default" href="{$link->getAdminLink('AdminGupselloffers')|escape:'html':'UTF-8'}{if !$id_upselloffers}&management=helperform{/if}"> {l s='Cancel' mod='g_upsellpro'}</a>
                        <button type="button" data-save="saveandstay" class="btn btn-default gbtn-default pull-right margin-left10" id="gupsellSubmitSave" name="gupsellSubmitSave"><i class="icon-save"></i> {l s='Save' mod='g_upsellpro'}</button>
                        <button type="button" data-save="save" class="btn btn-default gbtn-default pull-right margin-left10" id="gupsellSubmitSaveAndstay" name="gupsellSubmitSave"><i class="icon-save"></i>  {l s='Save And Stay' mod='g_upsellpro'}</button>
                    </div>
                    </div>
                {/if}
            </div>
            <div class="col-lg-2"></div>
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}