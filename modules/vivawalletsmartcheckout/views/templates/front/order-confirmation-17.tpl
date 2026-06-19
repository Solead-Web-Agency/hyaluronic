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

{extends file='page.tpl'}

{block name='content'}
    {if !$status|in_array:['ok','successful']}
        <section id="content-hook_order_confirmation" class="card">
            {if $status == 'pending'}
                {include file="module:vivawalletsmartcheckout/views/templates/front/success.tpl"}
            {else}
                {include file="module:vivawalletsmartcheckout/views/templates/front/failure.tpl"}
            {/if}
        </section>
    {/if}
{/block}