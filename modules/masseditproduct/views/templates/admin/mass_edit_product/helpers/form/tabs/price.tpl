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
    <!-- price tab -->
{literal}
    <script>
        $(document).ready(function () {
            $('input.search_category:first').focus({el: $('.tree_categories.tree_root:first input.tree_input')}, function (eventObj) {
                eventObj.data.el.each(function () {
                    $(this).attr('data-search', $(this).data('name').toLowerCase());
                });
            });

            $('.tabs_content').on('change', '[name="action_for_sp"]', function () {
                var disabled = $('' +
                    '[name="sp_from_quantity"], ' +
                    '[name="sp_reduction"], ' +
                    '[name="price"], ' +
                    '[name="leave_base_price"], ' +
                    '[name="sp_reduction_type"], ' +
                    '[name="delete_old_discount"]');
                var enabled = disabled;
                if ($('[name="leave_base_price"]').prop("checked"))
                    enabled = enabled.not('[name="price"]');

                var disabled2 = $('[name="delete_old_discount"]');
                var enabled2 = disabled2;

                if ($(this).val() == 1) {
                    disabled.attr('disabled', true);
                } else if ($(this).val() == 2) {
                    disabled2.attr('disabled', true)
                } else {
                    enabled.attr('disabled', false);
                    enabled2.attr('disabled', false);
                }
            });

            $('input[name="change_for"]').on('change', {
                product: change_product,
                combination: change_combination
            }, function (event) {
                var value = event.data.product;
                if ($('#change_for_combination').prop('checked'))
                    value = event.data.combination;

                var row = $(this).closest('.row');
                row.find('.control-label').text(value.title);
                row.find('label[for="type_price_base"]').find('span').text(value.base);
                row.find('label[for="type_price_final"]').find('span').text(value.final);
            });
        });
    </script>
{/literal}
    <div class="row disabled_option_stage form-group">
        <input checked type="checkbox" name="disabled[]" value="price" class="disable_option">
        <div class="col-sm-12">
            <div class="row form-group">
                <div class="col-sm-12">
                    <label class="apply_change margin-right control-label float-left">{l s='Apply change for' mod='masseditproduct'}:</label>
                    <span class="ps-switch prestashop-switch fixed-width-xxl switch-product-combination margin-right-lg float-left">
                        {foreach [0,1] as $value}
                            <input type="radio" name="change_for" value="{$value|escape:'quotes':'UTF-8'}"
                                    {if $value == 1} id="change_for_product" {else} id="change_for_combination" {/if}
                                    {if $value == 0} checked="checked" {/if}
                            />
                            <label {if $value == 1} for="change_for_product" {else} for="change_for_combination" {/if}>
                                {if $value == 0}{l s='Product' mod='masseditproduct'}{else}{l s='Combination' mod='masseditproduct'}{/if}
                            </label>
                        {/foreach}
                        <a class="slide-button"></a>
                    </span>
                    <label class="control-label margin-right float-left">{l s='Apply change for price' mod='masseditproduct'}:</label>
                    <div class="btn-group btn-group-radio float-left">
                        <label for="type_price_base">
                            <input type="radio" checked name="type_price" value="0" id="type_price_base"/>
                            <span>{l s='Base' mod='masseditproduct'}</span>
                        </label>
                        <label for="type_price_final">
                            <input type="radio" name="type_price" value="1" id="type_price_final"/>
                            <span>{l s='Final' mod='masseditproduct'}</span>
                        </label>
                        <label for="type_price_wholesale">
                            <input type="radio" name="type_price" value="2" id="type_price_wholesale"/>
                            <span>{l s='Wholesale' mod='masseditproduct'}</span>
                        </label>
                    </div>

                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-12">
                    <label class="control-label margin-right float-left">{l s='What to do with price?' mod='masseditproduct'}:</label>
                    <div class="btn-group btn-group-radio float-left">
                        <label for="action_price_increase_percent">
                            <input type="radio" checked name="action_price" value="1"
                                   id="action_price_increase_percent"/>
                            <span>{l s='Increase on %' mod='masseditproduct'}</span>
                        </label>
                        <label for="action_price_increase">
                            <input type="radio" name="action_price" value="2" id="action_price_increase"/>
                            <span>{l s='Increase on value' mod='masseditproduct'}</span>
                        </label>
                        <label for="action_price_reduce_percent">
                            <input type="radio" name="action_price" value="3" id="action_price_reduce_percent"/>
                            <span>{l s='Reduce on %' mod='masseditproduct'}</span>
                        </label>
                        <label for="action_price_reduce">
                            <input type="radio" name="action_price" value="4" id="action_price_reduce"/>
                            <span>{l s='Reduce on value' mod='masseditproduct'}</span>
                        </label>
                        <label for="action_price_rewrite">
                            <input type="radio" name="action_price" value="5" id="action_price_rewrite"/>
                            <span>{l s='Rewrite' mod='masseditproduct'}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 clearfix">
                    <label class="control-label margin-right">{l s='Write value' mod='masseditproduct'}:</label>
                    <input class="fixed-width-sm form-control" type="text" name="price_value"/>
                </div>
            </div>
        </div>
    </div>
    <div class="row disabled_option_stage form-group">
        <input checked type="checkbox" name="disabled[]" value="tax_rule_group" class="disable_option">
        <div class="col-sm-12 clearfix">
            <label class="control-label tax_rule margin-right">{l s='Tax rule group' mod='masseditproduct'}:</label>
            <select class="fixed-width-xl margin-right custom-select" name="id_tax_rules_group"
                    {if $tax_exclude_taxe_option}disabled="disabled"{/if} >
                <option value="0">{l s='No Tax' mod='masseditproduct'}</option>
                {foreach from=$tax_rules_groups item=tax_rules_group}
                    <option value="{$tax_rules_group.id_tax_rules_group|escape:'quotes':'UTF-8'}">
                        {$tax_rules_group['name']|htmlentitiesUTF8|escape:'quotes':'UTF-8'}
                    </option>
                {/foreach}
            </select>
            <label class="control-label margin-right">
                <input type="checkbox"
                       name="not_change_final_price">{l s='Not to change the final price' mod='masseditproduct'}
            </label>
        </div>
    </div>
    <div class="row disabled_option_stage form-group">
        <input checked type="checkbox" name="disabled[]" value="unity" class="disable_option">

        <div class="col-sm-12 clearfix">
            <label class="control-label float-left margin-right" for="unit_price"
                   title="{l s='When selling a pack of items, you can indicate the unit price for each item of the pack. For instance, "per bottle" or "per pound".' mod='masseditproduct'}">
                {l s='Unit price (tax excl.)' mod='masseditproduct'}
            </label>

            <div class="input-group float-left fixed-width-xl">
                <span class="input-group-addon">{$variables.currency->sign|no_escape}</span>
                <input id="unity_price"
                       name="unity_price"
                       class="form-control"
                       type="text"
                       value=""
                       maxlength="27" style="margin-top: 0px !important;"
                />
            </div>

            <div class="input-group float-left fixed-width-xl">
                <span class="input-group-addon">{l s='per' mod='masseditproduct'}</span>
                <input id="unity"
                       name="unity"
                       class="form-control"
                       type="text"
                       value=""
                       maxlength="255" style="margin-top: 0px !important;"
                />
            </div>
        </div>
    </div>
    <!---- round ---->
    <div class="row disabled_option_stage form-group" id="price_round">
        <input checked type="checkbox" name="disabled[]" value="price_round" class="disable_option">
        <div class="col-sm-12">

            <div class="row form-group">
                <div class="col-sm-12">
                    <label class="control-label margin-right float-left">{l s='Rounding only for products without combinations' mod='masseditproduct'}   </label>
                </div>
            </div>

            <div class="row form-group">
                <div class="col-sm-12">

                    <label class="control-label margin-right float-left">{l s='Rounding for' mod='masseditproduct'}:</label>

                    <div class="btn-group btn-group-radio float-left">
                        <label for="type_price_base_r">
                            <input type="radio" checked name="type_price_r" value="0" id="type_price_base_r"/>
                            <span>{l s='Base' mod='masseditproduct'}</span>
                        </label>
                        <label for="type_price_final_r">
                            <input type="radio" name="type_price_r" value="1" id="type_price_final_r"/>
                            <span>{l s='Final' mod='masseditproduct'}</span>
                        </label>
                        <label for="type_price_wholesale_r">
                            <input type="radio" name="type_price_r" value="2" id="type_price_wholesale_r"/>
                            <span>{l s='Wholesale' mod='masseditproduct'}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row form-group">
                <div class="col-sm-12" id="action_direction">
                    <label class="control-label margin-right float-left">{l s='Rounding direction' mod='masseditproduct'}:</label>
                    <div class="btn-group btn-group-radio float-left">
                        <label for="action_in_large">
                            <input type="radio" checked name="action_direction" value="0" id="action_in_large"/>
                            <span>{l s='in large' mod='masseditproduct'}</span>
                        </label>
                        <label for="action_to_a_lesser">
                            <input type="radio" name="action_direction" value="1" id="action_to_a_lesser"/>
                            <span>{l s='To a lesser' mod='masseditproduct'}</span>
                        </label>
                        <label for="action_automatically">
                            <input type="radio" name="action_direction" value="2" id="action_automatically"/>
                            <span>{l s='Automatically' mod='masseditproduct'}</span>
                        </label>
                    </div>
                </div>
            </div>


            <div class="row form-group">
                <div class="col-sm-12">

                    <label class="control-label margin-right float-left">{l s='Rounding value' mod='masseditproduct'}:</label>

                    <div class="btn-group btn-group-radio float-left">
                        <label for="action_thousand">
                            <input type="radio" checked name="action_rounding_value" value="1000" id="action_thousand"/>
                            <span>1000</span>
                        </label>
                        <label for="action_hundred">
                            <input type="radio" name="action_rounding_value" value="100" id="action_hundred"/>
                            <span>100</span>
                        </label>
                        <label for="action_ten">
                            <input type="radio" name="action_rounding_value" value="10" id="action_ten"/>
                            <span>10</span>
                        </label>
                        <label for="action_one">
                            <input type="radio" name="action_rounding_value" value="1" id="action_one"/>
                            <span>1</span>
                        </label>
                        <label for="action_one_tenth">
                            <input type="radio" name="action_rounding_value" value="0.1" id="action_one_tenth"/>
                            <span>0.1</span>
                        </label>
                        <label for="action_one_cell">
                            <input type="radio" name="action_rounding_value" value="0.01" id="action_one_cell"/>
                            <span>0.01</span>
                        </label>
                    </div>
                </div>
            </div>

            <h3 id="title_edit_tabs" class="panel-heading text-center" style="display: block;">{l s='Rounding Examples' mod='masseditproduct'}</h3>

            <div class="row form-group">
                <div class="col-sm-12">
                    <table class="table-new rounding-table" id="in_large">
                        <thead>
                        <tr class="table_head">
                            <th>{l s='Price' mod='masseditproduct'}</th>
                            <th>{l s='Rounding' mod='masseditproduct'}</th>
                            <th>{l s='Result' mod='masseditproduct'}</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr class="tr_thousand">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 1000</td>
                            <td>1000</td>
                        </tr>
                        <tr class="tr_hundred">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 100</td>
                            <td>900</td>
                        </tr>
                        <tr class="tr_ten">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 10</td>
                            <td>830</td>
                        </tr>
                        <tr class="tr_one">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 1</td>
                            <td>824</td>
                        </tr>
                        <tr class="tr_one_tenth">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 0.1</td>
                            <td>823.8</td>
                        </tr>
                        <tr class="tr_one_cell">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 0.01</td>
                            <td>823.79</td>
                        </tr>
                        </tbody>
                    </table>
                    <table class="table-new rounding-table" id="to_a_lesser" style="display: none">

                        <thead>
                        <tr>
                            <th>{l s='Price' mod='masseditproduct'}</th>
                            <th>{l s='Rounding' mod='masseditproduct'}</th>
                            <th>{l s='Result' mod='masseditproduct'}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="tr_thousand">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 1000</td>
                            <td>1000</td>
                        </tr>
                        <tr class="tr_hundred">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 100</td>
                            <td>800</td>
                        </tr>
                        <tr class="tr_ten">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 10</td>
                            <td>820</td>
                        </tr>
                        <tr class="tr_one">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 1</td>
                            <td>823</td>
                        </tr>
                        <tr class="tr_one_tenth">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 0.1</td>
                            <td>823.7</td>
                        </tr>
                        <tr class="tr_one_cell">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 0.01</td>
                            <td>823.78</td>
                        </tr>
                        </tbody>
                    </table>
                    <table class="table-new rounding-table" id="automatically" style="display: none">
                        <thead>
                        <tr>
                            <th>{l s='Price' mod='masseditproduct'}</th>
                            <th>{l s='Rounding' mod='masseditproduct'}</th>
                            <th>{l s='Result' mod='masseditproduct'}</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr class="tr_thousand">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 1000</td>
                            <td>8 > 5 {l s='rounding up' mod='masseditproduct'} 1000</td>
                        </tr>
                        <tr class="tr_hundred">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 100</td>
                            <td>2 < 5 {l s='rounding down' mod='masseditproduct'} 800</td>
                        </tr>
                        <tr class="tr_ten">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 10</td>
                            <td>3 < 5 {l s='rounding down' mod='masseditproduct'} 820</td>
                        </tr>
                        <tr class="tr_one">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 1</td>
                            <td>7 > 5 {l s='rounding down' mod='masseditproduct'} 824</td>
                        </tr>
                        <tr class="tr_one_tenth">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 0.1</td>
                            <td>8 > 5 {l s='rounding down' mod='masseditproduct'} 823.8</td>
                        </tr>
                        <tr class="tr_one_cell">
                            <td>823.783</td>
                            <td>{l s='rounding' mod='masseditproduct'} 0.01</td>
                            <td>3 < 5 {l s='rounding down' mod='masseditproduct'} 823.78</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>


    </div>
    <style>
        #price_round td {
            padding-right: 20px;
        }
    </style>
    <script>
        $('document').ready(function () {
            $('#action_direction input').on('click', function () {
                console.log($(this).val());
                var index = $(this).val();
                if (index == 0) {
                    $('#in_large').show();
                    $('#to_a_lesser').hide();
                    $('#automatically').hide();
                } else if (index == 1) {
                    $('#in_large').hide();
                    $('#to_a_lesser').show();
                    $('#automatically').hide();
                } else {
                    $('#in_large').hide();
                    $('#to_a_lesser').hide();
                    $('#automatically').show();
                }
            });
        });
    </script>
    <!---- end round ---->
{/block}