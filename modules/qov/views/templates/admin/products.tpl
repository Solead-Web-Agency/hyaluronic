{**
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
* @copyright 2010-9999 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

<div class="col-md-8">
    {if Configuration::get('QOV_PRODUCTS')==1}
        <h4>{l s='Products' mod='qov'} <span class="badge">{$products|@count nofilter}</span></h4>
        {capture "TaxMethod"}
            {if ($order->getTaxCalculationMethod() == $smarty.const.PS_TAX_EXC)}
                {l s='tax excluded.' mod='qov'}
            {else}
                {l s='tax included.' mod='qov'}
            {/if}
        {/capture}
        {if ($order->getTaxCalculationMethod() == $smarty.const.PS_TAX_EXC)}
            <input type="hidden" name="TaxMethod" value="0">
        {else}
            <input type="hidden" name="TaxMethod" value="1">
        {/if}
        <div class="well table-responsive">
            <table class="table" id="orderProducts">
                <thead>
                <tr>
                    <th>
                    </th>
                    <th>
                        <span class="title_box ">{l s='Product' mod='qov'}</span>
                        <small class="text-muted">{l s='Name + Selected combination + reference number' mod='qov'}</small>
                        {if Configuration::get('QOV_PRODUCTS_FEAT') == 1}
                            <br/>
                            <small class="text-muted">{l s='Product Features' mod='qov'}</small>
                        {/if}
                    </th>
                    {if (Configuration::get('QOV_PRODUCTS_CAT') == 1 || Configuration::get('QOV_PRODUCTS_CATS') == 1)}
                        <th>
                            <span class="title_box ">{l s='Category' mod='qov'}</span>
                            {if Configuration::get('QOV_PRODUCTS_CAT') == 1}
                                <small class="text-muted">{l s='Product main category' mod='qov'}</small><br/>
                            {/if}
                            {if Configuration::get('QOV_PRODUCTS_CATS') == 1}
                                <small class="text-muted">{l s='All associations' mod='qov'}</small>
                            {/if}
                        </th>
                    {/if}
                    <th>
                        <span class="title_box ">{l s='Unit Price' mod='qov'}</span>
                        <small class="text-muted">{$smarty.capture.TaxMethod nofilter}</small>
                    </th>
                    <th class="text-center">
                        <span class="title_box ">{l s='Qty' mod='qov'}</span>
                    </th>
                    {if Configuration::get('QOV_CURRENT_STOCK')==1}
                        <th class="text-center">
                            <span class="title_box ">{l s='In stock' mod='qov'}</span>
                            <small class="text-muted">{l s='Current stock' mod='qov'}</small>
                        </th>
                    {/if}
                    {if Configuration::get('QOV_PARCEL_WEIGHT')==1}
                        <th>
                            <span class="title_box">{l s='Unit weight' mod='qov'}</span>
                        </th>
                    {/if}
                    {if Configuration::get('QOV_PRODUCTS_UPC')==1}
                        <th>
                            <span class="title_box">{l s='UPC code' mod='qov'}</span>
                        </th>
                    {/if}
                    <th>
                        <span class="title_box ">{l s='Total' mod='qov'}</span>
                        <small class="text-muted">{$smarty.capture.TaxMethod nofilter}</small>
                    </th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$products item=product key=k}
                    {if ($order->getTaxCalculationMethod() == $smarty.const.PS_TAX_EXC)}
                        {assign var=product_price value=($product['unit_price_tax_excl'] + $product['ecotax'])}
                    {else}
                        {assign var=product_price value=$product['unit_price_tax_incl']}
                    {/if}
                    <tr class="product-line-row">
                        <td>{if isset($product.image) && $product.image->id}{$product.image_tag nofilter}{/if}</td>
                        <td>
                            <span class="productName">{$product['product_name'] nofilter}</span><br/>
                            {if $product.product_reference}{l s='Reference number:' mod='qov'} {$product.product_reference nofilter}
                                <br/>
                            {/if}
                            {if $product.product_supplier_reference}{l s='Supplier reference:' mod='qov'} {$product.product_supplier_reference nofilter}{/if}
                            {if $product['customizedDatas']}
                                <br/>
                                <span class="title_box ">{l s='Customization' mod='qov'}</span>
                                <br/>
                                {foreach $product['customizedDatas'] as $customizationPerAddress}
                                    {foreach $customizationPerAddress as $customizationId => $customization}
                                        {foreach $customization.datas as $type => $datas}
                                            {if ($type == Product::CUSTOMIZE_FILE)}
                                            {elseif ($type == Product::CUSTOMIZE_TEXTFIELD)}
                                                {foreach from=$datas item=data}
                                                    <strong>{if $data['name']}{l s='%s' sprintf=$data['name']}{else}{l s='Text #%s' sprintf=$data@iteration}{/if}</strong>
                                                    : {$data['value']}
                                                    <br/>
                                                {/foreach}
                                            {/if}
                                        {/foreach}

                                    {/foreach}
                                {/foreach}
                            {/if}
                            {if Configuration::get('QOV_PRODUCTS_FEAT') == 1}
                                {if isset($product_features[$product.product_id])}
                                    {$product_features[$product.product_id] nofilter}
                                {/if}
                            {/if}
                        </td>
                        {if (Configuration::get('QOV_PRODUCTS_CAT') == 1 || Configuration::get('QOV_PRODUCTS_CATS') == 1)}
                            <td>
                                {if Configuration::get('QOV_PRODUCTS_CAT') == 1}
                                    {$product_main_category[$product.product_id]}
                                {/if}
                                {if Configuration::get('QOV_PRODUCTS_CAT') == 1 && Configuration::get('QOV_PRODUCTS_CATS') == 1}
                                    <br/>
                                {/if}
                                {if Configuration::get('QOV_PRODUCTS_CATS') == 1}
                                    <small class="text-muted">{$product_categories[$product.product_id]}</small>
                                {/if}
                            </td>
                        {/if}
                        <td>
                            <span class="product_price_show">{Tools::displayPrice($product_price)}</span>
                        </td>
                        <td class="productQuantity text-center">
                            <span class="product_quantity_show">{$product['product_quantity'] nofilter}</span>
                            <span class="product_quantity_edit" style="display:none;">
                                <input type="text" name="product_quantity" class="edit_product_quantity"
                                       value="{$product['product_quantity'] nofilter}"/>
                            </span>
                        </td>
                        {if Configuration::get('QOV_CURRENT_STOCK')==1}
                            <td class="productQuantity text-center">
                                {if $product['product_quantity']>StockAvailable::getQuantityAvailableByProduct($product.id_product, $product.product_attribute_id)}
                                    <span class='label label-danger'>{StockAvailable::getQuantityAvailableByProduct($product.id_product, $product.product_attribute_id)}</span>
                                {else}
                                    <span class='label label-success'>{StockAvailable::getQuantityAvailableByProduct($product.id_product, $product.product_attribute_id)}</span>
                                {/if}
                            </td>
                        {/if}
                        {if Configuration::get('QOV_PARCEL_WEIGHT')==1}
                            <td>
                                {$product.product_weight|string_format:"%.3f"} {Configuration::get('PS_WEIGHT_UNIT')}
                            </td>
                        {/if}
                        {if Configuration::get('QOV_PRODUCTS_UPC')==1}
                            <td>
                                {$product.product_upc}
                            </td>
                        {/if}
                        <td class="total_product">
                            {Tools::displayPrice(Tools::ps_round($product_price, 2) * ($product['product_quantity'] - $product['customizationQuantityTotal']))}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    {/if}

    {if Configuration::get('QOV_HISTORY')==1}
        <div class="clearfix">
            <div class="col-md-4"><h4 class="">{l s='Order status history' mod='qov'} <span class="badge">{$history|@count}</span>
                </h4></div>
            {if Configuration::get('QOV_HISTORY_UPDATE')==1}
                <div class="col-md-8  pull-right">
                    <div class="row">
                        <div class="col-lg-10">
                            <select id="id_order_state{$order->id}" class="chosen form-control"
                                    name="id_order_state{$order->id}">
                                {foreach from=$states item=state}
                                    <option value="{$state['id_order_state']|intval}"{if isset($currentState) && $state['id_order_state'] == $currentState->id} selected="selected" disabled="disabled"{/if}>{$state['name']|escape}</option>
                                {/foreach}
                            </select>
                            <input type="hidden" name="id_order" value="{$order->id}"/>
                        </div>
                        <div class="col-lg-2">
                            <div class="btn btn-default"
                                 onclick="ChangeOrderStatus({$order->id}, $('#id_order_state{$order->id}').val());"><i
                                        class="icon-save" id="OrderStatusSaveIcon{$order->id}"></i> {l s='save' mod='qov'}
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
        <div class="well table-responsive" id="OrderStatusHistoryDiv{$order->id}">
            <div class="table-responsive">
                <table class="table history-status row-margin-bottom">
                    <tbody>
                    <th></th>
                    <th>{l s='Order status' mod='qov'}</th>
                    <th>{l s='Employee' mod='qov'}</th>
                    <th>{l s='Date of change' mod='qov'}</th>
                    {foreach from=$history item=row key=key}
                        <tr>
                            <td><img src="../img/os/{$row['id_order_state']|intval}.gif" width="16" height="16"/></td>
                            <td>{$row['ostate_name']|stripslashes}</td>
                            <td>{if $row['employee_lastname']}{$row['employee_firstname']|stripslashes} {$row['employee_lastname']|stripslashes}{else}&nbsp;{/if}</td>
                            <td>{dateFormat date=$row['date_add'] full=true}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    {/if}


    {if Configuration::get('QOV_CUSTOMERM')==1}
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-envelope"></i> {l s='Messages' mod='qov'} <span class="badge">{count($messages)}</span>
            </div>
            {if (sizeof($messages))}
                <div class="panel panel-highlighted">
                    <div class="message-item">
                        {foreach from=$messages item=message}
                            <div class="col-md-12">
                                    <span class="message-date">&nbsp;<i class="icon-calendar"></i>
                                        {dateFormat date=$message.date_add} -
                                    </span>
                                <h4 class="message-item-heading">
                                    {if $message.id_employee==0}
                                        {$message.email}
                                    {else}
                                        {l s='You' mod='qov'}
                                    {/if}
                                    {if ($message.private == 1)}
                                        <span class="badge badge-info">{l s='Private' mod='qov'}</span>
                                    {/if}
                                </h4>
                                <p class="message-item-text">
                                    {$message.message|escape:'html':'UTF-8'|nl2br}
                                </p>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}
        </div>
    {/if}



</div>