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
        <div class="col-sm-12 clearfix">
            <label class="control-label margin-right float-left">{l s='Select language' mod='masseditproduct'}:</label>
            <span class="btn-group btn-group-radio float-left">
                <label for="all_language_meta">
                    <input type="radio" checked name="language_meta" value="0" id="all_language_meta"/>
                    <span class="">{l s='For all' mod='masseditproduct'}</span>
                </label>
                {foreach from=$languages item=language}
                    <label for="{$language.id_lang|intval}_language_meta">
                        <input type="radio" name="language_meta" value="{$language.id_lang|intval}" id="{$language.id_lang|intval}_language_meta"/>
                        <span class="">{$language.name|escape:'quotes':'UTF-8'}</span>
                    </label>
                {/foreach}
            </span>
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="meta_title" class="disable_option">

        <div class="col-sm-12">
            <label class="control-label label-meta margin-right">{l s='Meta title' mod='masseditproduct'}:</label>
            <input class="form-control fixed-width-xxxxl" name="meta_title">
        </div>
        <div class="col-sm-12">
            {renderTemplate file="admin/mass_edit_product/helpers/form/row_variables.tpl" v=['name'=>'meta_title']}
        </div>
    </div>

    {if !version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '>=')}
        <div class="row form-group">
            <input checked type="checkbox" name="disabled[]" value="meta_keywords" class="disable_option">
            <div class="col-sm-12">
                <label class="control-label label-meta margin-right">{l s='Meta keywords' mod='masseditproduct'}:</label>
                <input class="form-control fixed-width-xxxxl" name="meta_keywords">
            </div>
            <div class="col-sm-12">
                {renderTemplate file="admin/mass_edit_product/helpers/form/row_variables.tpl" v=['name'=>'meta_keywords']}
            </div>
        </div>
    {/if}

    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="meta_description" class="disable_option">
        <div class="col-xs-12">
            <label class="control-label label-meta margin-right">{l s='Meta description' mod='masseditproduct'}:</label>
            <input class="form-control fixed-width-xxxxl" name="meta_description">
        </div>
        <div class="col-sm-12">
            {renderTemplate file="admin/mass_edit_product/helpers/form/row_variables.tpl" v=['name'=>'meta_description']}
        </div>
    </div>
    <div class="row form-group">
        <input checked type="checkbox" name="disabled[]" value="tags" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label label-metat margin-right">{l s='Tags' mod='masseditproduct'}:</label>
            <span class="btn-group btn-group-radio">
                <label for="add_tags">
                    <input type="radio" checked name="edit_tags" value="0" id="add_tags"/>
                    <span class="">{l s='Add tags' mod='masseditproduct'}</span>
                </label>
                <label for="add_del_tags">
                    <input type="radio" name="edit_tags" value="1" id="add_del_tags"/>
                    <span class="">{l s='Add and delete old tags' mod='masseditproduct'}</span>
                </label>
                <label for="del_tags">
                    <input type="radio" name="edit_tags" value="2" id="del_tags"/>
                    <span class="">{l s='Delete tags' mod='masseditproduct'}</span>
                </label>
            </span>
        </div>
        <div class="col-xs-6">
            <input id="tags" class="form-control" name="tags">
        </div>
    </div>
  <div class="row form-group">
    <input checked type="checkbox" name="disabled[]" value="meta_chpu" class="disable_option">
    <div class="col-xs-12 form-group">
      <label class="control-label label-meta margin-right">{l s='Chpu' mod='masseditproduct'}:</label>
      <input class="form-control fixed-width-xxxxl js-translit" name="meta_chpu">
    </div>
    <div class="col-sm-12">
      {renderTemplate file="admin/mass_edit_product/helpers/form/row_chpu.tpl" v=['name'=>'meta_chpu']}
    </div>
  </div>
  <div class="row form-group">
    <input checked type="checkbox" name="disabled[]" value="redirection_page" class="disable_option">
    <div class="col-xs-12">
      <label class="control-label label-meta margin-right">{l s='Redirection page' mod='masseditproduct'}:</label>
    </div>
    <div class="col-sm-12">
      {renderTemplate file="admin/mass_edit_product/helpers/form/row_redirect.tpl" v=['name'=>'redirection_page']}
    </div>
  </div>

{/block}