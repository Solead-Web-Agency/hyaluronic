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
    <div class="modal-dialog" role="document">
    <div class="ets_table ets_trans_table">
    <div class="ets_table-cell">
        <div class="modal-content">
            <form id="formEtsTransResumeAlert">
                <div class="panel_header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        <i class="fa fa-language"></i>
                        {if isset($page_type) && $page_type == 'all'}
                            {l s='1-Click translate' mod='ets_translate'}
                        {else}
                            {l s='Translate' mod='ets_translate'}
                        {/if}
                    </h4>
                </div>
                <div class="panel_body">
                    <div class="ets-trans-content">
                        <div class="alert alert-info">
                            <p>{l s='Total translated' mod='ets_translate'}: {if $page_type == 'pc' || $page_type == 'blog'}{$nb_path|escape:'html':'UTF-8'}{else}{$nb_translated|escape:'html':'UTF-8'}{/if}
                                {if $page_type == 'product'}
                                    {l s='products' mod='ets_translate'}
                                {elseif $page_type == 'category'}
                                    {l s='categories' mod='ets_translate'}
                                {elseif $page_type == 'cms'}
                                    {l s='CMSs' mod='ets_translate'}
                                {elseif $page_type == 'cms_category'}
                                    {l s='CMS categories' mod='ets_translate'}
                                {elseif $page_type == 'manufacturer'}
                                    {l s='Manufacturers' mod='ets_translate'}
                                {elseif $page_type == 'supplier'}
                                    {l s='suppliers' mod='ets_translate'}
                                {elseif $page_type == 'email'}
                                    {l s='emails' mod='ets_translate'}
                                {elseif $page_type == 'pc'}
                                    {l s='product comments' mod='ets_translate'}
                                {elseif $page_type == 'blog'}
                                    {if $blog_type == 'category'}
                                        {l s='blog categories' mod='ets_translate'}
                                    {else}
                                        {l s='blog posts' mod='ets_translate'}
                                    {/if}

                                {else}
                                    {l s='texts' mod='ets_translate'}
                                {/if}
                                ({$nb_char_translated|escape:'html':'UTF-8'} {l s='charaters' mod='ets_translate'})</p>
                        </div>
                        <div>
                            <input type="hidden" name="nb_translated" value="{$nb_translated|escape:'html':'UTF-8'}">
                            <input type="hidden" name="nb_char_translated" value="{$nb_char_translated|escape:'html':'UTF-8'}">
                            <input type="hidden" name="pageType" value="{$page_type|escape:'html':'UTF-8'}">
                            <input type="hidden" name="trans_source" value="{$lang_source|escape:'html':'UTF-8'}">
                            <input type="hidden" name="trans_target" value="{$lang_target|escape:'html':'UTF-8'}">
                            <input type="hidden" name="trans_option" value="{$field_option|escape:'html':'UTF-8'}">
                            <input type="hidden" name="total_item" value="{$total_translate|escape:'html':'UTF-8'}">
                            <input type="hidden" name="nb_path" value="{if isset($nb_path)}{$nb_path|escape:'html':'UTF-8'}{/if}">
                            {if $page_type == 'email'}
                                <input type="hidden" name="mail_option" value="{if isset($mail_option)}{$mail_option|escape:'html':'UTF-8'}{/if}">
                            {/if}
                            <input type="hidden" name="page_id" value="">
                        </div>
                    </div>
                    {include './popup_translating.tpl'}
                </div>
                <div class="panel_footer">
                    <div class="btn-group-trans btn-group-translating">
                        <button type="button" class="btn btn-primary js-ets-trans-translate-from-resume">{l s='Resume' mod='ets_translate'}</button>
                        <button type="button" class="btn btn-outline-secondary js-ets-trans-translate-from-zero">{l s='Retranslate all' mod='ets_translate'}</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-content -->
        </div>
        </div>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
