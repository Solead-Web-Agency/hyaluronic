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

<div class="revolut-instruction">
    <div class="ri-image">
        <img src="{$module_dir|escape:'htmlall':'UTF-8'|addslashes}views/img/admin_logo.png" class="img-responsive"
             alt=""/>
    </div>
    <div class="ri-text">
        <p>{l s='Welcome to the Revolut Gateway for Prestashop plugin!' mod='revolutpayment'}</p>
        <p>{l s='To start accepting payments from your customers at great rates, you\'ll need to follow three simple steps:' mod='revolutpayment'}</p>
        <ul>
            <li><a href="https://link.revolut.com/e/prestashop"
                   target="_blank">{l s='Sign up for Revolut Business' mod='revolutpayment'}</a> {l s='if you don\'t have an account already' mod='revolutpayment'}
                .
            </li>
            <li>{l s='Once your Revolut Business account has been approved' mod='revolutpayment'}, <a
                        href="https://business.revolut.com/merchant"
                        target="_blank">{l s='apply for a Merchant Account' mod='revolutpayment'}</a></li>
            <li><a href="https://business.revolut.com/settings/merchant-api"
                   target="_blank">{l s='Get your Production API key' mod='revolutpayment'}</a> {l s='and paste it in the corresponding field below' mod='revolutpayment'}
            </li>
        </ul>
        <p><a href="https://www.revolut.com/business/online-payments"
              target="_blank">{l s='Find out more' mod='revolutpayment'}</a> {l s='about why accepting payments through Revolut is the right decision for your business' mod='revolutpayment'}
            .</p>
        <p>{l s='If you\'d like to know more about how to configure this plugin for your needs' mod='revolutpayment'},
            <a href="https://developer.revolut.com/docs/accept-payments/plugins/prestashop/configuration"
               target="_blank">{l s='check out our documentation' mod='revolutpayment'}</a>.</p>
    </div>
</div>