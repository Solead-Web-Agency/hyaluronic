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
 * @package   CedWish
 */
 -->
<form method="post">
<div class="panel">
    <div class="panel-heading">
        {l s='Wish Options Mapping' mod='cedwish'}
    </div>
    <div class="panel-body">
        <div class="table-responsive">
                <div id="content table-responsive-row clearfix">
                    <table id="attribute" class="table list">
                        <thead>
                        <tr>
                            <td class="text-center" > {l s='Prestashop Option' mod='cedwish'} </td>
                            <td class="text-center" > {l s='Wish Option' mod='cedwish'} </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="text-center" >
                                <select
                                        class="form-control store-option-change"
                                        id="store_option_id"
                                        name="store_option_id"
                                >
                                    <option value="0">{l s='-- Please Select Store Attribute --' mod='cedwish'} </option>
                                    {foreach $features as $option}
                                        {if isset($already_mapped_attributes['store_option_id'])
                                        && $option['id_attribute'] == $already_mapped_attributes['store_option_id']
                                        }
                                            <option
                                                    selected="selected"
                                                    value="{$option['id_attribute']|escape:'htmlall':'UTF-8'}"
                                            >
                                                {$option['name']|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {else}
                                            <option value="{$option['id_attribute']|escape:'htmlall':'UTF-8'}">
                                                {$option['name']|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/if}
                                    {/foreach}
                                </select>
                            </td>
                            <td class="text-center" >
                                <select
                                        class="form-control wish-option-change"
                                        id="cedwish_option_id"
                                        name="cedwish_option_id"
                                >
                                    <option value="0">{l s='-- Please Select Wish Attribute --' mod='cedwish'}</option>

                                    {foreach $wish_attributes as $key => $option}
                                            <option selected="selected" value="{$option|escape:'htmlall':'UTF-8'}">
                                                {$option|escape:'htmlall':'UTF-8'}
                                            </option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive">
                    <table id="mapping-values" class="list table table-bordered table-hover">
                        <thead>
                        <tr>
                            <td class="text-center" >{l s='Prestshop Values' mod='cedwish'} </td>
                            <td class="text-center" >{l s='Wish Values' mod='cedwish'} </td>
                            <td class="text-center" >{l s='Action' mod='cedwish'}</td>
                        </tr>
                        </thead>
                        <tbody>
                        {assign var="option_value_row" value=0}
                        {if isset($already_mapped_attributes['mapped_options'])}
                            {$mapped_options = json_decode($already_mapped_attributes['mapped_options'], true)}
                            {if count($mapped_options)}
                                {foreach $mapped_options as $k => $mapped_option}
                                    <tr  id="option-value-row{$option_value_row|escape:'htmlall':'UTF-8'}" >
                                        <td class="text-left">
                                            <select
                                                    name="cedwish_color_mapping[{$option_value_row|escape:'htmlall':'UTF-8'}][store_option_value]"
                                                    class="form-control store-options"
                                            >
                                                <option value="0"> -- Please Select Store Option -- </option>
                                                {foreach $option_values[$already_mapped_attributes['store_option_id']] as $key => $option}
                                                    {if $mapped_option['store_option_value'] == $key}
                                                        <option selected="selected" value="{$key|escape:'htmlall':'UTF-8'}">{$option|escape:'htmlall':'UTF-8'}</option>
                                                    {else}
                                                        <option value="{$key|escape:'htmlall':'UTF-8'}">{$option|escape:'htmlall':'UTF-8'}</option>
                                                    {/if}
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td class="text-left jet-options-avaible">
                                            <select name="cedwish_color_mapping[{$option_value_row|escape:'htmlall':'UTF-8'}][cedwish_option_value]" class="form-control attr-value" id="cedwish_color_mapping[{$option_value_row|escape:'htmlall':'UTF-8'}][cedwish_option_value]" >
                                                <option value="0">--Please select options--</option>

                                                {if count($wish_attributes_values[$already_mapped_attributes['wish_option_id']])}
                                                    {foreach $wish_attributes_values[$already_mapped_attributes['wish_option_id']] as $option}
                                                        {if $mapped_option['cedwish_option_value'] == $option["name"]}
                                                            <option selected="selected" value="{$option["name"]|escape:'htmlall':'UTF-8'}">{$option["name"]|escape:'htmlall':'UTF-8'}</option>
                                                        {else}
                                                            <option value="{$option["name"]|escape:'htmlall':'UTF-8'}">{$option["name"]|escape:'htmlall':'UTF-8'}</option>
                                                        {/if}
                                                    {/foreach}
                                                {/if}
                                            </select>
                                        <td class="text-left"><button type="button" onclick="$('#option-value-row{$option_value_row|escape:'htmlall':'UTF-8'}').remove();" data-toggle="tooltip" rel="tooltip" title="Remove" class="btn btn-danger">Delete</button></td>
                                    </tr>
                                    {assign var="option_value_row" value=$option_value_row+1}
                                {/foreach}
                            {/if}
                        {/if}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3"></td>
                            <td class="button text-left">
                                <button
                                        type="button"
                                        onclick="addOptionValue();"
                                        data-toggle="tooltip"
                                        title=""
                                        class="button btn btn-primary"
                                        id="add-btn-id">
                                    {l s='Add' mod='cedwish'}
                                </button>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
        </div>
    </div>
    <div class="panel-footer">
        <button type="submit" value="1" id="test_form_submit_btn" name="savemapping"
                class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Save' mod='cedwish'}
        </button>
        <a
                class="btn btn-default"
                id="back-option-controller"
                data-token="{$token|escape:'htmlall':'UTF-8'}"
                href="{$controllerUrl|escape:'htmlall':'UTF-8'}"
        >
            <i class="process-icon-cancel"></i> {l s='Cancel' mod='cedwish'}
        </a>
    </div>
</div>
</form>
<script type="text/javascript">
    var options='{json_encode($option_values)|escape:'quotes':'UTF-8'}';
    var option_value_row = '{$option_value_row|escape:'htmlall':'UTF-8'}';
    var value_json = '{(json_encode($wish_attributes_values))|escape:'quotes':'UTF-8'}';
    value_json = JSON.parse(value_json);

    function addOptionValue() {
        html  = '<tr id="option-value-row'+option_value_row+'">';
        html += '  <td class="text-left"><select ' +
            'name="cedwish_color_mapping[' + option_value_row + '][store_option_value]" ' +
            'class="form-control store-options" >';
        html += $('#option-values').html();
        html += '  </select></td>';
        html += '  <td class="text-left jet-options-avaible" >';
        html += '  </td>';
        html += '  <td class="text-left"><button ' +
            'type="button" onclick="$(\'#option-value-row' + option_value_row + '\').remove();" ' +
            'data-toggle="tooltip" rel="tooltip" title="Remove" class="btn btn-danger button">Remove</button></td>';
        html += '</tr>';

        $('#mapping-values' + ' tbody').append(html);
        addWishOptionValue($('#cedwish_option_id').val(),option_value_row);
        getValues(option_value_row);
        option_value_row++;
    }

    $("#cedwish_option_id").change(function(){
        if($("#store_option_id").val()==0 || $("#cedwish_option_id").val()==0)
            $("#add-btn-id").prop( "disabled", true );
        else
            $("#add-btn-id").prop( "disabled", false );
    });
    $(".store-option-change").change(function(){
        if($("#store_option_id").val()==0 || $("#cedwish_option_id").val()==0)
            $("#add-btn-id").prop( "disabled", true );
        else
            $("#add-btn-id").prop( "disabled", false );
        $(".store-options").empty();
        $(".store-options").append('<option value="">Please select Value </option>');
        var result = $.parseJSON(options);

        var option_id=$("#store_option_id").val();
        console.log(result);
        if(result){
            for (var event in result[option_id]) {
                $(".store-options").append('<option value=' + event + '>' + result[option_id][event] + '</option>');
            }
        }

    });
    $("document").ready(function(){
        if($("#store_option_id").val()==0 || $("#cedwish_option_id").val()==0)
            $("#add-btn-id").prop( "disabled", true );
        else
            $("#add-btn-id").prop( "disabled", false );
    });
    function getValues(option_value_row){
        var option_id=$("#store_option_id").val();

        $("#option-value-row"+option_value_row).children('td').children('select.store-options').empty();
        $("#option-value-row"+option_value_row).children('td').children('select.store-options').append(
            '<option value="">Please select Value </option>'
        );
        var result = $.parseJSON(options);
        if(result){
            for (var event in result[option_id]) {

                $("#option-value-row"+option_value_row).children('td').children('select.store-options').append(
                    '<option value=' + event + '>' + result[option_id][event] + '</option>'
                );
            }
        }
    }

    function addWishOptionValue(attrValue,option_value_row){
        var name='cedwish_color_mapping['+option_value_row+'][cedwish_option_value]';
        var newdivselect = document.createElement('select');
        newdivselect.setAttribute('name',name);
        newdivselect.setAttribute('name',name);
        newdivselect.setAttribute('class','form-control attr-value');
        newdivselect.setAttribute('id',name);
        $("#option-value-row"+option_value_row).children('td.jet-options-avaible').html(newdivselect);
        getAttributeValue(attrValue,option_value_row);
    }

    function getAttributeValue(attrValue,option_value_row){
        $("#option-value-row"+option_value_row).children('td.jet-options-avaible').children('.attr-value').html('');
        switch(attrValue) {
            case "color":
                cedwishoptions = value_json.color;
                break;
        }
        $.each(cedwishoptions, function(key, val){
            if(val)
                $("#option-value-row"+option_value_row).children('td.jet-options-avaible').children('.attr-value')
                    .append('<option value="' + val.name + '">' + val.name + '</option>');
        });
    }
</script>
