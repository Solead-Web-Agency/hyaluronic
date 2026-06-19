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
*  @author Musaffar Patel <musaffar.patel@gmail.com>
*  @copyright  2015-2016 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<div id="pbp-bundle_existing" class="form-wrapper pbp-form-wrapper">
    <div style="padding: 40px;">

        <div class="row">
            <div class="col-xs-12">
                <h2 style="padding-top: 0px;">{l s='Add Existing Bundle' mod='productbundlespro'}</h2>
            </div>
        </div>

        <div class="row">
            {*  left hand side *}
            <div class="col-xs-6">

                <div class="form-group">
                    <label class="form-full-label">
                        {l s='Choose a tab' mod='productbundlespro'}<br>
                    </label>

                    <select id="id_tab" name="id_tab" class="form-control">
                        {foreach from=$tabs_collection item=tab}
                            {if isset($bundle) && $bundle->id_tab eq $tab->id_tab}
                                <option value="{$tab->id_tab|escape:'htmlall':'UTF-8'}"
                                        selected>{$tab->title|escape:'htmlall':'UTF-8'}
                                </option>
                            {else}
                                <option value="{$tab->id_tab|escape:'htmlall':'UTF-8'}">{$tab->title|escape:'htmlall':'UTF-8'}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>

                <div class="form-group form-group-search">
                    <label class="form-full-label">
                        {l s='Type the name of the product to copy bundle from' mod='productbundlespro'}<br>
                    </label>
                    {$product_search nofilter}
                </div>
            </div>
            {* / left hand side *}


            {* right hand side *}
            <div class="col-xs-12 col-sm-6">
                <div id="pbp-bundles-existing-list"></div>
            </div>
            {* / right hand side *}
        </div>

        <div class="row">
            <div class="panel-footer">
                <a id="pbp-edit-product-cancel" href="#close" class="btn btn-default">
                    {l s='Cancel' mod='productbundlespro'}
                </a>
                <button type="submit" id="pbp-existing-bundle-add" class="btn btn-default pull-right" disabled="disabled">
                    {l s='Add' mod='productbundlespro'}
                </button>
            </div>
        </div>
    </div>
</div>


