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
{if !empty($orderCode)}
    <div class="card panel">
        <div class="card-header">
            <h3 class="card-header-title">{l s='Viva.com Transactions' mod='vivawalletsmartcheckout'}</h3>
        </div>
        <div class="card-body vivawalletsmartcheckout-card">
            <h4 class="vivawalletsmartcheckout-section-title">{l s='Order Code' mod='vivawalletsmartcheckout'} - {$orderCode|escape:'htmlall':'UTF-8'}</h4>
            {if !empty($transactions)}
                <table class="table vivawalletsmartcheckout-transactions">
                    <thead>
                    <tr>
                        <th>{l s='Created at' mod='vivawalletsmartcheckout'}</th>
                        <th>{l s='Transaction type' mod='vivawalletsmartcheckout'}</th>
                        <th>{l s='Transaction ID' mod='vivawalletsmartcheckout'}</th>
                        <th>{l s='Amount' mod='vivawalletsmartcheckout'}</th>
                        <th colspan="2" class="text-center">{l s='Actions' mod='vivawalletsmartcheckout'}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $transactions as $transaction}
                        <tr>
                            <td>{$transaction['transaction_date_created']|escape:'html':'UTF-8'}</td>
                            <td>{$transaction['transaction_type']|escape:'html':'UTF-8'}</td>
                            <td>{$transaction['transaction_id']|escape:'html':'UTF-8'}</td>
                            <td>{$transaction['currency']|escape:'html':'UTF-8'} {$transaction['transaction_amount']|escape:'html':'UTF-8'}</td>
                            {if ($refundable_amount > 0 && $transaction['transaction_type']|in_array:['payment','capture'] && ($payments_number eq 1 || $captures_number eq 1))}
                                <td class="text-center">
                                    {if $refunds_number < 1}
                                        <button class="btn btn-sm btn-primary" id="vivawalletsmartcheckout_trigger_full_refund">
                                            Full Refund
                                        </button>
                                    {/if}
                                    {if $prestashop_version neq '1.6'}
                                        <button class="btn btn-sm btn-secondary" id="vivawalletsmartcheckout_trigger_partial_refund" {if $products_refunded}disabled=""{/if}>
                                            Partial Refund
                                        </button>
                                    {/if}
                                </td>
                                <input type="hidden" name="vivawalletsmartcheckout_refundable_amount" value="{$refundable_amount|escape:'html':'UTF-8'}">
                                <input type="hidden" name="vivawalletsmartcheckout_transaction_id" value="{$transaction['transaction_id']|escape:'html':'UTF-8'}">
                                <input type="hidden" name="vivawalletsmartcheckout_transaction_date" value="{$transaction['transaction_gmt_date_created']|escape:'html':'UTF-8'}">
                            {elseif $transaction['transaction_type'] eq 'preauthorization'}
                                {if !$captures_number && !$voids_number}
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary" id="vivawalletsmartcheckout_trigger_capture" {if $captures_number || $voids_number}disabled{/if}>
                                            Capture
                                        </button>
                                        <button class="btn btn-sm btn-secondary" id="vivawalletsmartcheckout_trigger_void" {if $captures_number || $voids_number}disabled{/if}>
                                            Void
                                        </button>
                                    </td>
                                {/if}
                                <input type="hidden" name="vivawalletsmartcheckout_preauthorized_amount" value="{$transaction['transaction_amount']|escape:'html':'UTF-8'}">
                                <input type="hidden" name="vivawalletsmartcheckout_preauthorization_transaction_id" value="{$transaction['transaction_id']|escape:'html':'UTF-8'}">
                                <input type="hidden" name="vivawalletsmartcheckout_preauthorization_transaction_date" value="{$transaction['transaction_gmt_date_created']|escape:'html':'UTF-8'}">
                            {elseif $transaction['transaction_type'] eq 'capture'}
                                <input type="hidden" name="vivawalletsmartcheckout_captured_amount" value="{$transaction['transaction_amount']|escape:'html':'UTF-8'}">
                                <input type="hidden" name="vivawalletsmartcheckout_captured_transaction_id" value="{$transaction['transaction_id']|escape:'html':'UTF-8'}">
                                <input type="hidden" name="vivawalletsmartcheckout_captured_transaction_date" value="{$transaction['transaction_gmt_date_created']|escape:'html':'UTF-8'}">
                            {else}
                                <td></td>
                                <td></td>
                            {/if}
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <div class="error vivawalletsmartcheckout-transaction-error"></div>
            {/if}
        </div>
    </div>
    {if $refundable_amount > 0}
        {if $refunds_number < 1 }
            <div class="card panel vivawalletsmartcheckout-full-refund-card">
                <div class="card-header">
                    <h3 class="card-header-title">Viva.com Full Refund</h3>
                </div>
                <div class="card-body">
                    <form id="vivawalletsmartcheckout_refund_form">
                        <div>
                            <label>Generate credit slip</label>
                            <input type="checkbox" id="vivawalletsmartcheckout_full_generate_slip">
                        </div>
                        <div>
                            <label>Restock products</label>
                            <input type="checkbox" id="vivawalletsmartcheckout_full_restock_products">
                        </div>
                        <div>
                            <input disabled type="number" class="vivawalletsmartcheckout-input-full-amount">
                        </div>
                        <div>
                            <button class="btn btn-sm btn-danger vivawalletsmartcheckout-cancel-full-refund">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary" id="vivawalletsmartcheckout_refund_form_submit">Full Refund</button>
                        </div>
                    </form>
                </div>
            </div>
        {/if}
        {if $prestashop_version neq '1.6'}
            <div class="card panel vivawalletsmartcheckout-partial-refund-card">
                <div class="card-header">
                    <h3 class="card-header-title">Viva Wallet Partial Refund</h3>
                </div>
                <div class="card-body">
                    <form id="vivawalletsmartcheckout_partial_refund_form">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Base price</th>
                                <th colspan="2" class="text-center">Refund</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr></tr>
                            </tbody>
                            {foreach $order_details as $product}
                                {if $product['product_quantity'] > $product['product_quantity_reinjected']}
                                    <tr class="vivawalletsmartcheckout-order-detail">
                                        <td>{$product['product_name']|escape:'html':'UTF-8'}</td>
                                        <td>{$product['product_quantity']|escape:'html':'UTF-8'}</td>
                                        <td>{$product['unit_price_tax_incl']|string_format:'%.2f'} {$order_currency|escape:'html':'UTF-8'}</td>
                                        <input type="hidden" class="vivawalletsmartcheckout-order-detail-id" value="{$product['id_order_detail']|escape:'html':'UTF-8'}">
                                        <td class="vivawalletsmartcheckout-refund-cell">
                                            <input type="number" class="form-control vivawalletsmartcheckout-input-partial-quantity" placeholder="Quantity">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    /
                                                    <span class="vivawalletsmartcheckout-partial-allowed-quantity">{$product['product_quantity'] - $product['product_quantity_reinjected']|escape:'html':'UTF-8'}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="vivawalletsmartcheckout-refund-cell">
                                            <input type="number" class="form-control vivawalletsmartcheckout-input-partial-amount" placeholder="Amount" size="6" min="0" step=".01">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    /<span class="vivawalletsmartcheckout-partial-allowed-amount">{$product['total_price_tax_incl'] - $product['total_refunded_tax_incl']|string_format:'%.2f'|escape:'html':'UTF-8'}</span>
                                                    {$order_currency|escape:'html':'UTF-8'}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                {/if}
                            {/foreach}
                            <tr></tr>
                        </table>
                        <div>
                            <div>
                                <label>Generate credit slip</label>
                                <input type="checkbox" id="vivawalletsmartcheckout_partial_generate_slip">
                            </div>
                            <div>
                                <label>Restock products</label>
                                <input type="checkbox" id="vivawalletsmartcheckout_partial_restock_products">
                            </div>
                            <div class="btn-group-justified">
                                <button class="btn btn-sm btn-danger vivawalletsmartcheckout-cancel-partial-refund">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-secondary" id="vivawalletsmartcheckout_partial_refund_form_submit">Partial Refund</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        {/if}
    {/if}
    {if $captures_number < 1 && $voids_number < 1}
        <div class="card panel vivawalletsmartcheckout-capture-card">
            <div class="card-header">
                <h3 class="card-header-title">Viva.com Capture</h3>
            </div>
            <div class="card-body">
                <form id="vivawalletsmartcheckout_capture_form">
                    <div>
                        <input type="number" class="vivawalletsmartcheckout-input-capture-amount" size="6" min="0" step=".01">
                    </div>
                    <div>
                        <button class="btn btn-sm btn-danger vivawalletsmartcheckout-cancel-capture">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="vivawalletsmartcheckout_capture_form_submit">Capture</button>
                    </div>
                </form>
            </div>
        </div>
    {/if}
    {if $voids_number < 1}
        <div class="card panel vivawalletsmartcheckout-void-card">
            <div class="card-header">
                <h3 class="card-header-title">Viva.com Void</h3>
            </div>
            <div class="card-body">
                <form id="vivawalletsmartcheckout_void_form">
                    <div>
                        <input type="number" class="vivawalletsmartcheckout-input-void-amount" disabled>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-danger vivawalletsmartcheckout-cancel-void">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="vivawalletsmartcheckout_void_form_submit">Void</button>
                    </div>
                </form>
            </div>
        </div>
    {/if}
{/if}
