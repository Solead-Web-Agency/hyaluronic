<!--
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   cedwish
 */
 -->

<div class="panel" id="wrapper_element">
    <div class="panel-body">
        <div class="table-responsive">
            <div id="content table-responsive-row clearfix">
                <table id="carrier_mapping_container" class="table list">
                    <thead>
                    <tr>
                        <td class="text-center" >{l s='Country' mod='cedwish'}  </td>
                        <td class="text-center" >{l s='Store Carrier' mod='cedwish'}  </td>
                        <td class="text-center" >{l s='Marketplace Carrier' mod='cedwish'}  </td>
                        <td class="text-center" >{l s='Action' mod='cedwish'}  </td>
                    </tr>
                    </thead>
                    <tbody>
                    {if $carrier_mappings}
                        {foreach $carrier_mappings as $index => $carrier_mapping}
                            <tr id="option-value-row{$index|escape:'htmlall':'UTF-8'}">
                                <td class="text-left">
                                    <select
                                            name="CED_WISH_CARRIER_MAPPING[{$index|escape:'htmlall':'UTF-8'}][country]"
                                            class="form-control"
                                    >
                                        {if $carrier_mapping['country']==""}
                                            <option value="" selected="selected">All Countries</option>
                                        {/if}
                                        {foreach $countries as $iso_code => $country_name}
                                            <option
                                                    {if $carrier_mapping['country']==$iso_code}
                                                        selected="selected"
                                                    {/if}
                                                    value="{$iso_code|escape:'htmlall':'UTF-8'}"
                                            >
                                                {$country_name|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td class="text-left">
                                    <select
                                            name="CED_WISH_CARRIER_MAPPING[{$index|escape:'htmlall':'UTF-8'}][id_carrier]"
                                            class="form-control"
                                    >
                                        <option value=""></option>
                                        {foreach $order_carriers as $order_carrier}
                                            <option
                                                    {if $carrier_mapping['id_carrier']==$order_carrier['id_carrier']}
                                                        selected="selected"
                                                    {/if}
                                                    value="{$order_carrier['id_carrier']|escape:'htmlall':'UTF-8'}"
                                            >
                                                {$order_carrier['name']|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td class="text-left">
                                    <select
                                            name="CED_WISH_CARRIER_MAPPING[{$index|escape:'htmlall':'UTF-8'}][id_marketplace_carrier]"
                                            class="form-control"
                                    >
                                        <option value=""></option>
                                        {foreach $marketplace_carriers as $marketplace_carrier}
                                            <option
                                                    {if $carrier_mapping['id_marketplace_carrier']==$marketplace_carrier["name"]}
                                                        selected="selected"
                                                    {/if}
                                                    value="{$marketplace_carrier["name"]|escape:'htmlall':'UTF-8'}"
                                            >
                                                {$marketplace_carrier["name"]|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td class="text-left">
                                    <button
                                            type="button"
                                            onclick="$('#option-value-row{$index|escape:'htmlall':'UTF-8'}').remove();"
                                            data-toggle="tooltip"
                                            rel="tooltip"
                                            class="btn btn-outline-primary"
                                            title="Remove"
                                    >
                                        <i class="icon-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">
                            <button
                                    type="button"
                                    onclick="addCarrierMapping();"
                                    class="btn btn-outline-primary add"
                            >
                                {l s='Add Carrier Mapping' mod='cedwish'}
                            </button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    {if isset($index)}
        var carrier_mapping_row = {$index+1|escape:'htmlall':'UTF-8'};
    {else}
        var carrier_mapping_row = 1;
    {/if}

    function addCarrierMapping() {
        html  = '<tr id="option-value-row'+carrier_mapping_row+'">';
        html += '<td class="text-left">';
        html += '<select name="CED_WISH_CARRIER_MAPPING[' + carrier_mapping_row + '][country]" class="form-control" >';
        html += '<option value="">All Countries</option>';
        {foreach $countries as $iso_code => $country_name}
        html += '<option value="{$iso_code|escape:'htmlall':'UTF-8'}">';
        html +='{$country_name|escape:'htmlall':'UTF-8'}';
        html +='</option>';
        {/foreach}
        html += '  </select></td>';
        html += '<td class="text-left">';
        html += '<select name="CED_WISH_CARRIER_MAPPING[' + carrier_mapping_row + '][id_carrier]" class="form-control" >';
        html += '<option value=""></option>';
        {foreach $order_carriers as $order_carrier}
        html += '<option value="{$order_carrier["id_carrier"]|escape:'htmlall':'UTF-8'}">';
        html +='{$order_carrier["name"]|escape:'htmlall':'UTF-8'}';
        html +='</option>';
        {/foreach}
        html += '  </select></td>';
        html += '<td class="text-left claroshop-options-avaible" >';
        html += '<select name="CED_WISH_CARRIER_MAPPING[' + carrier_mapping_row + '][id_marketplace_carrier]" class="form-control" >';
        html += '<option value=""></option>';
        {foreach $marketplace_carriers as $order_carrier}
        html += '<option value="{$order_carrier["name"]|escape:'htmlall':'UTF-8'}">';
        html +='{$order_carrier["name"]|escape:'htmlall':'UTF-8'}';
        html +='</option>';
        {/foreach}
        html += '</select>';
        html += '</td>';
        html += '<td class="text-left"><button type="button" onclick="$(\'#option-value-row' + carrier_mapping_row + '\').remove();" data-toggle="tooltip" rel="tooltip" class="btn btn-outline-primary" title="Remove">' ;
        html += '<i class="icon-trash"></i>';
        html += '</button>';
        html += '</td>';
        html += '</tr>';
        $('#carrier_mapping_container' + ' tbody').append(html);
        carrier_mapping_row++;
    }
</script>
