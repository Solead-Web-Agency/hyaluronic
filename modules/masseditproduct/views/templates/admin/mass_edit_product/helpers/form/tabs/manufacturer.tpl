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
    <div class="row">
        <div class="col-sm-12">
            <label class="control-label margin-right">
                {l s='Set manufacturer for all products' mod='masseditproduct'}:
            </label>
            <select class="select2 select2manufacturer fixed-width-xl" multiple name="id_manufacturer">
                <option value="0">-</option>
                {foreach from=$manufacturers item=manufacturer}
                    <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
            <script>
                $(document).ready(function() {
                    $('.select2manufacturer ').select2({
                        maximumSelectionLength: 1
                    });
                });
            </script>
        </div>
    </div>
{/block}