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
{if $prestashop_version neq '1.6'}
    <div class="card-block">
        {if $status == 'pending'}
            <h3>{l s='Your payment on %s is in process.' sprintf=[$shop.name] mod='vivawalletsmartcheckout'}</h3>
        {else}
            <h3>{l s='Your payment on %s is complete.' sprintf=[$shop.name] mod='vivawalletsmartcheckout'}</h3>
        {/if}
        <p>
            - {l s='Viva.com | Smart Checkout' mod='vivawalletsmartcheckout'} {l s='Reference' mod='vivawalletsmartcheckout'} : <span
                    class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
        </p>
    </div>
{else}
    <p class="alert alert-success">
        {if $status == 'pending'}
            {l s='Your payment on %s is in process.' sprintf=$shop_name mod='vivawalletsmartcheckout'}
        {else}
            {l s='Your payment on %s is complete.' sprintf=$shop_name mod='vivawalletsmartcheckout'}
        {/if}
    </p>
    <div class="box">
        - {l s='Viva.com | Smart Checkout' mod='vivawalletsmartcheckout'} {l s='Reference' mod='vivawalletsmartcheckout'} : <span
                class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
    </div>
{/if}