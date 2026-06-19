{*
* Copyright since 2007 PrestaShop SA and Contributors
* PrestaShop is an International Registered Trademark & Property of PrestaShop SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.md.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* @author    Revolut
* @copyright Since 2020 Revolut
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<div {if !$is_product_page}style="margin-top:20px" {/if}
     id="ps-revolut-payment-request-container" class="ps-revolut-payment-request-instance"
     ps_revolut_payment_request_params='{$ps_revolut_payment_request_params|escape:'htmlall':'UTF-8'}'>
    <div id="revolut-payment-request-button"></div>
</div>

