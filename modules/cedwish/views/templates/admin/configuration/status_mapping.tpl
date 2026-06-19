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
                <table id="status_mapping_container" class="table list">
                    <thead>
                    <tr>
                        <td class="text-center" >{l s='Store Status' mod='cedwish'}  </td>
                        <td class="text-center" >{l s='Marketplace Status' mod='cedwish'}  </td>
                        <td class="text-center" >{l s='Action' mod='cedwish'}  </td>
                    </tr>
                    </thead>
                    <tbody>
                    {if $status_mappings}
                        {foreach $status_mappings as $index => $status_mapping}
                            <tr id="option-value-row{$index|escape:'htmlall':'UTF-8'}">
                                <td class="text-left">
                                    <select name="CED_WISH_STATUS_MAPPING[{$index|escape:'htmlall':'UTF-8'}][order_status]" class="form-control" >
                                        <option value=""></option>
                                        {foreach $order_statuses as $order_status}
                                            <option
                                                    {if $status_mapping['order_status']==$order_status['id_order_state']}
                                                        selected="selected"
                                                    {/if}
                                                    value="{$order_status['id_order_state']|escape:'htmlall':'UTF-8'}"
                                            >
                                                {$order_status['name']|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td class="text-left">
                                    <select name="CED_WISH_STATUS_MAPPING[{$index|escape:'htmlall':'UTF-8'}][marketplace_status]" class="form-control" >
                                        {foreach $wish_statuses as $wish_status}
                                            <option
                                                    {if $status_mapping['marketplace_status']==$wish_status}
                                                        selected="selected"
                                                    {/if}
                                                    value="{$wish_status|escape:'htmlall':'UTF-8'}"
                                            >
                                                {$wish_status|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td class="text-left"><button type="button" onclick="$('#option-value-row{$index|escape:'htmlall':'UTF-8'}').remove();" data-toggle="tooltip" rel="tooltip" class="btn btn-outline-primary" title="Remove">
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
                            <button type="button" onclick="addStatusMapping();" class="btn btn-outline-primary add">{l s='Add Status Mapping' mod='cedwish'}</button>
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
        var option_value_row = {$index+1|escape:'htmlall':'UTF-8'};
    {else}
        var option_value_row = 1;
    {/if}

    function addStatusMapping() {
        html  = '<tr id="option-value-row'+option_value_row+'">';
        html += '<td class="text-left">';
        html += '<select name="CED_WISH_STATUS_MAPPING[' + option_value_row + '][order_status]" class="form-control" >';
        html += '<option value=""></option>';
        {foreach $order_statuses as $order_status}
        html += '<option value="{$order_status["id_order_state"]|escape:'htmlall':'UTF-8'}">';
        html +='{$order_status["name"]|escape:'htmlall':'UTF-8'}';
        html +='</option>';
        {/foreach}
        html += '  </select></td>';
        html += '<td class="text-left">';
        html += '<select name="CED_WISH_STATUS_MAPPING[' + option_value_row + '][marketplace_status]" class="form-control" >';
        html += '<option value=""></option>';
        {foreach $wish_statuses as $wish_status}
        html += '<option value="{$wish_status|escape:'htmlall':'UTF-8'}">';
        html +='{$wish_status|escape:'htmlall':'UTF-8'}';
        html +='</option>';
        {/foreach}
        html += '  </select></td>';
        html += '<td class="text-left"><button type="button" onclick="$(\'#option-value-row' + option_value_row + '\').remove();" data-toggle="tooltip" rel="tooltip" class="btn btn-outline-primary" title="Remove">' ;
        html += '<i class="icon-trash"></i>';
        html += '</button>';
        html += '</td>';
        html += '</tr>';
        $('#status_mapping_container' + ' tbody').append(html);
        option_value_row++;
    }
</script>
