{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<section id="global_view">
    <div class="panel-body">
        {* Abandonmed carts *}
        <div class="col-lg-3 col-md-6">
            <div class="title col-lg-12">
                <strong>{l s='Abandoned cart' mod='pscartabandonmentpro'}</strong>
            </div>
            <div class="col-lg-12">
                {include file='./circle_svg.tpl'
                    icon="remove_shopping_cart"
                    percentage={$abandonedCartsByPrestashop['percent']}
                    title="{$abandonedCartsByPrestashop['percent']}%"
                    description="{l s='on the' mod='pscartabandonmentpro'} {$abandonedCartsByPrestashop['all_cart']} {l s='created carts' mod='pscartabandonmentpro'}"
                }
            </div>
        </div>

        {* Carts finalized *}
        <div class="col-lg-3 col-md-6">
            <div class="title col-lg-12">
                <strong>{l s='Finalized cart' mod='pscartabandonmentpro'}</strong>
            </div>
            <div class="col-lg-12">
                {include file='./circle_svg.tpl' 
                    icon="add_shopping_cart"
                    percentage={$finalizedCarts['percent']}
                    title="{$finalizedCarts['percent']}%"
                    description="{l s='of the' mod='pscartabandonmentpro'} {$finalizedCarts['all_cart']} {l s='carts revived by the module' mod='pscartabandonmentpro'}"
                }
            </div>
        </div>

        {* CA generated *}
        <div class="col-lg-3 col-md-6">
            <div class="title col-lg-12">
                <strong>{l s='Revenue generated' mod='pscartabandonmentpro'}</strong>
            </div>
            <div class="col-lg-12">
                {include file='./circle_svg.tpl' 
                    icon="attach_money"
                    percentage={$turnoverGenerated['percent']}
                    title="{$turnoverGenerated['abandoned_price_final']}"
                    description="{l s='out of' mod='pscartabandonmentpro'} {$turnoverGenerated['all_price_final']} {l s='of total abandonned carts' mod='pscartabandonmentpro'}"
                }
            </div>
        </div>

        {* Unsubscribe *}
        <div class="col-lg-3 col-md-6">
            <div class="title col-lg-12">
                <strong>{l s='Unsubscribe' mod='pscartabandonmentpro'}</strong>
            </div>
            <div class="col-lg-12">
                {include file='./circle_svg.tpl' 
                    icon="email"
                    percentage={$unsubscribedDatas['percent']}
                    title="{$unsubscribedDatas['percent']}%"
                    description="{l s='out of the' mod='pscartabandonmentpro'} {$unsubscribedDatas['send_reminder']} {l s='emails sent' mod='pscartabandonmentpro'}"
                }
            </div>
        </div>
   </div>
</section>