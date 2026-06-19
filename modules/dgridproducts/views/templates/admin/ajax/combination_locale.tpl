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
*  @author    SeoSA <885588@bk.ru>
*  @copyright 2012-2022 SeoSA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<table class="table">
    <thead>
    <tr>
        <th>№</th>
        <th>{l s='Id' mod='dgridproducts'}</th>
        <th>{l s='Name' mod='dgridproducts'}</th>
        <th>{l s='Stock locale' mod='dgridproducts'}</th>
    </tr>
    </thead>

    {assign var=val value=1}
    {foreach from=$combination item=co}
        <tr>
            <td class="id_a">{$val}</td>
            <td class="id_a">{$co['id_product_attribute']}</td>
            <td class="id_n">{$co['names']}</td>
            <td class="id_l">
                <input type="text" class="locales"
                data-id="{$id_product}" {$r['id']}"
                data-attribut="{$co['id_product_attribute']}"
                value="{$co['location']|escape:'quotes':'UTF-8'}"></td>
        </tr>
        {assign var=val value=$val+1}
    {/foreach}
</table>
<style>
    /*.id_a{*/
        /*min-width: 35px;*/
    /*}*/
    /*.id_n{*/
        /*min-width: 250px;*/
    /*}*/
    /*.id_g{*/
        /*min-width: 250px;*/
    /*}*/
    /*.id_l{*/
        /*min-width: 250px;*/
    /*}*/
    /*.form_location th {*/
        /*text-align: center;*/
        /*font-size: 16px;*/
        /*font-weight: 300;*/
        /*padding: 5px 10px;*/
        /*border-bottom: 2px solid dodgerblue;*/
        /*border-top: 2px solid dodgerblue;*/
        /*color: black;*/
    /*}*/
    /*.form_location td {*/
        /*padding: 5px;*/
    /*}*/
    /*.form_location h1{*/
        /*text-align: left;*/
        /*margin-top: 0;*/
    /*}*/
    /*.form_location .title_location{*/
        /*position: relative;*/
    /*}*/
    /*.title_location a{*/
        /*position: absolute;*/
        /*top: 0;*/
        /*left: 93%;*/
    /*}*/

</style>
<script>
$('.locale').change('click', function (e) {
    e.preventDefault();
    var self = this;
    $.ajax({
        url: ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            ajax: true,
            action: 'get_locale',
            id_product: $(self).data('id'),
            locale_text: $(self).val(),
            id_product_attribute: $(self).data('attribut'),
        },
        success: function (r) {

        }
    });
});

$('.locales').change('click', function (e) {
    e.preventDefault();
    var self = this;
    $.ajax({
        url: ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            ajax: true,
            action: 'get_locale',
            id_product: $(self).data('id'),
            locale_text: $(self).val(),
            id_product_attribute: $(self).data('attribut'),
        },
        success: function (r) {

        }
    });
});
</script>