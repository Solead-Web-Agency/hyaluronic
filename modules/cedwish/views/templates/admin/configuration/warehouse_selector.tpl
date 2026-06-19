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

{if $warehouses_list}
    {foreach $warehouses_list as $warehouse}
        {if $selected_warehouses && in_array($warehouse['id'], $selected_warehouses)}
            <input
                    checked="checked"
                    type="checkbox"
                    value="{$warehouse['id']|escape:'htmlall':'UTF-8'}"
                    name="CED_WISH_SELECTED_WAREHOUSES[]"
            >
            <b>{$warehouse['name']|escape:'htmlall':'UTF-8'}</b>
        {else}
            <input
                    type="checkbox"
                    value="{$warehouse['id']|escape:'htmlall':'UTF-8'}"
                    name="CED_WISH_SELECTED_WAREHOUSES[]"
            >
            <b>{$warehouse['name']|escape:'htmlall':'UTF-8'}</b>
        {/if}
        </br>
    {/foreach}
{/if}
</br>
<button class="btn btn-primary" type="button" onclick="getWarehouses();">
    <i class="icon-refresh"></i>&nbsp;{l s='Refresh Warehouse List' mod='cedwish'}
</button>
</br>
</br>
<button class="btn btn-primary" type="button" onclick="getCarriersList();">
    <i class="icon-refresh"></i>&nbsp;{l s='Refresh Carriers List' mod='cedwish'}
</button>
</br>
</br>
<button class="btn btn-primary" type="button" onclick="getColorsList();">
    <i class="icon-refresh"></i>&nbsp;{l s='Refresh Colors List' mod='cedwish'}
</button>
</br>
