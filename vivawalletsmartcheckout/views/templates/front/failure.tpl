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

{if $status eq 'canceled'}
    <div class="card-block vivawalletsmartcheckout">
        <div class="row">
            <div class="col-md-12">
                {l s='Your order has been cancelled.' mod='vivawalletsmartcheckout'}
            </div>
        </div>
    </div>
{else}
    {if $prestashop_version neq '1.6'}
        <div class="card-block h1 alert alert-danger vivawalletsmartcheckout">
            <div class="row">
                <div class="col-md-12">
                    <i class="material-icons rtl-no-flip error">&#xE000;</i>{l s='An error occured during your payment' mod='vivawalletsmartcheckout'}
                </div>
            </div>
        </div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    {if !empty($reference)}
                        - {l s='Reference' mod='vivawalletsmartcheckout'} :
                        <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
                        <br/>
                        <br/>
                    {/if}
                    {l s='Please, try to order again.' mod='vivawalletsmartcheckout'}
                </div>
            </div>
        </div>
    {else}
        <p class="alert alert-danger">
            {l s='An error occured during your payment' mod='vivawalletsmartcheckout'}
        </p>
        <div class="box">
            {if !empty($reference)}
                - {l s='Reference' mod='vivawalletsmartcheckout'} :
                <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
                <br/>
                <br/>
            {/if}
            {l s='Please, try to order again.' mod='vivawalletsmartcheckout'}
        </div>
    {/if}
{/if}
