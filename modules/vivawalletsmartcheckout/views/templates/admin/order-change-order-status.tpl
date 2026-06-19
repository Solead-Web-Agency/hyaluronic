{*
 * Copyright since 2007 Viva Wallet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@vivawallet.com so we can send you a copy immediately.
 *
 * @author    Viva Wallet <support@vivawallet.com>
 * @copyright Since 2007 Viva Wallet
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
{if isset($updateOrderStatus_mode) && $updateOrderStatus_mode}
    <div class="panel">
        <div class="panel-heading">
            {l s='Choose an order status' mod='vivawalletsmartcheckout'}
        </div>
        <form action="{$REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
            <div class="radio">
                <label for="id_order_state">
                    <select id="id_order_state" name="id_order_state">
                        {foreach from=$order_statuses item=order_status_name key=id_order_state}
                            <option value="{$id_order_state|intval}">{$order_status_name|escape}</option>
                        {/foreach}
                    </select>
                </label>
            </div>
            {foreach $POST as $key => $value}
                {if is_array($value)}
                    {foreach $value as $val}
                        <input type="hidden" name="{$key|escape:'html':'UTF-8'}[]" value="{$val|escape:'html':'UTF-8'}" />
                    {/foreach}
                {elseif strtolower($key) != 'id_order_state'}
                    <input type="hidden" name="{$key|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" />

                {/if}
            {/foreach}
            <div class="panel-footer">
                <button type="submit" name="cancel" class="btn btn-default">
                    <i class="icon-remove"></i>
                    {l s='Cancel' mod='vivawalletsmartcheckout'}
                </button>
                <button type="submit" class="btn btn-default" name="submitUpdateOrderStatus">
                    <i class="icon-check"></i>
                    {l s='Update Order Status' mod='vivawalletsmartcheckout'}
                </button>
            </div>
        </form>
    </div>
{/if}
