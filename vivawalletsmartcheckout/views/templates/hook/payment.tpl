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

<p class="payment_module">
	<a class="vivawalletsmartcheckout" href="{$action|escape:'htmlall':'UTF-8'}" title="{$title|escape:'htmlall':'UTF-8'}">
		<img src="{$logo.generic|escape:'htmlall':'UTF-8'}" style="max-width:150px;" alt="{$title|escape:'htmlall':'UTF-8'}"/>{$title|escape:'htmlall':'UTF-8'}
		{if !empty($description)}&nbsp;<span>({$description|escape:'htmlall':'UTF-8'})</span>{/if}
	</a>
</p>