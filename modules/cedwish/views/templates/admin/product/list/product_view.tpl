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
 * @package   CedClaroshop
 */
 -->

{if !empty($data)}
    <div class="row">
        <div class="col-md-12 text-center">
            <img src="{$data['main_image']['url']|escape:'htmlall':'UTF-8'}" style="width:95%;"/>
        </div>
        <div class="col-md-12">
            <div class="table-responsive table">
                <table class="table">
                    <tr>
                        <th>
                            {l s='Field' mod='cedwish'}
                        </th>
                        <th>
                            {l s='Value' mod='cedwish'}
                        </th>
                    </tr>
                    {foreach $data as $field => $value}
                        {if is_array($value)}
                            <tr>
                                <td>
                                    <b>
                                        {$field|escape:'htmlall':'UTF-8'}
                                    </b>
                                </td>
                                <td>
                                    <div>
                                        {if $field =='default_shipping_prices'}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    {l s='Warehouse Id' mod='cedwish'}
                                                </div>
                                                <div class="col-md-6">
                                                    {l s='Price' mod='cedwish'}
                                                </div>
                                            </div>
                                            <div class="row">
                                                {foreach $value as $v}
                                                    <div class="col-md-6">
                                                        {$v['warehouse_id']|escape:'htmlall':'UTF-8'}
                                                    </div>
                                                    <div class="col-md-6">
                                                        {$v['default_shipping_price']['amount']|escape:'htmlall':'UTF-8'}
                                                        {$v['default_shipping_price']['currency_code']|escape:'htmlall':'UTF-8'}
                                                    </div>
                                                {/foreach}
                                            </div>
                                        {/if}
                                        {if $field =='variations'}
                                            <div class="row">
                                                <div class="col-md-8">
                                                    {l s='SKU' mod='cedwish'}
                                                </div>
                                                <div class="col-md-3">
                                                    {l s='Status' mod='cedwish'}
                                                </div>
                                                <div class="col-md-1">
                                                    {l s='Qty' mod='cedwish'}
                                                </div>
                                            </div>
                                            <div class="row">
                                                {foreach $value as $v}
                                                    <div class="col-md-8">
                                                        {$v['sku']|escape:'htmlall':'UTF-8'}
                                                    </div>
                                                    <div class="col-md-3">
                                                        {$v['status']|escape:'htmlall':'UTF-8'}
                                                    </div>
                                                    <div class="col-md-1">
                                                        {$v['quantity_value']|escape:'htmlall':'UTF-8'}
                                                    </div>
                                                {/foreach}
                                            </div>
                                        {/if}
                                        {if $field =='warehouse_to_shippings'}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    {l s='Warehouse ID' mod='cedwish'}
                                                </div>
                                                <div class="col-md-5">
                                                    {l s='Country' mod='cedwish'}
                                                </div>
                                                <div class="col-md-1">
                                                    {l s='Status' mod='cedwish'}
                                                </div>
                                            </div>
                                            <div class="row">
                                                {foreach $value as $v}
                                                    {foreach $v['shipping_details'] as $s}
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                {$v['warehouse_id']|escape:'htmlall':'UTF-8'}
                                                            </div>
                                                            <div class="col-md-5">
                                                                {if isset($countries[$s['destination']])}
                                                                    {$countries[$s['destination']]|escape:'htmlall':'UTF-8'}
                                                                {else}
                                                                    {$s['destination']|escape:'htmlall':'UTF-8'}
                                                                {/if}
                                                            </div>
                                                            <div class="col-md-1 text-center">
                                                                {$s['is_enabled']|escape:'htmlall':'UTF-8'}
                                                            </div>
                                                        </div>
                                                        {if $s['overrides']}
                                                            <p><b>{l s='Overrides' mod='cedwish'}</b></p>
                                                            <div class="row">
                                                                <div class="col-md-9">
                                                                    {l s='Region' mod='cedwish'}
                                                                </div>
                                                                <div class="col-md-3">
                                                                    {l s='Enabled' mod='cedwish'}
                                                                </div>
                                                            </div>
                                                            <div style="min-height: 100px;height: 100px;overflow-y: auto;">
                                                                {foreach $s['overrides'] as $o}
                                                                    {assign var="r_c" value="{$s['destination']}_{$o['destination']}"}
                                                                    <div class="row">
                                                                        <div class="col-md-9">
                                                                            {if isset($regions[$s['destination']])
                                                                            && isset($regions[$s['destination']][$r_c])
                                                                            }
                                                                                {$regions[$s['destination']][$r_c]['1']|escape:'htmlall':'UTF-8'}
                                                                            {else}
                                                                                {$o['destination']|escape:'htmlall':'UTF-8'}
                                                                            {/if}
                                                                        </div>
                                                                        <div class="col-md-3 text-center">
                                                                            {$o['is_enabled']|escape:'htmlall':'UTF-8'}
                                                                        </div>
                                                                    </div>
                                                                {/foreach}
                                                            </div>
                                                        {/if}
                                                    {/foreach}
                                                {/foreach}
                                            </div>
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                        {else}
                            <tr>
                                <td>
                                    <b>
                                        {$field|escape:'htmlall':'UTF-8'}
                                    </b>
                                </td>
                                <td>
                                    {$value|escape:'htmlall':'UTF-8'}
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                </table>
            </div>
        </div>
    </div>
{else}
    <div>{l s='No Error Found.' mod='cedwish'}</div>
{/if}
