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

<div id="reminder_discount" class="steps_panel col-lg-12 col-xs-12">

    <section class="col-lg-12 col-xs-12">
        <div class="intern-section col-lg-12 col-xs-12">
            <div class="col-lg-6 col-xs-6 text-right control-label">
                <strong>{l s='Enable cart value ranges to define discount' mod='pscartabandonmentpro'}</strong>
            </div>
            <div class="col-lg-6 col-xs-6">
                <span id="cart_discount_specific" class="switch prestashop-switch input-group col-sm-12 col-md-8 col-lg-8 col-xs-8">
                    <input type="radio" name="cart_discount_specific" id="cart_discount_specific_yes" value="1"  {if (isset($multiple_discount) && $multiple_discount > 1)}checked{/if}/>
                    <label for="cart_discount_specific_yes" class="radioCheck">
                        <i class="color_success"></i> {l s=' Yes ' mod='pscartabandonmentpro'}
                    </label>
                    <input type="radio" class="switch_off" name="cart_discount_specific" id="cart_discount_specific_no" value="0" {if !isset($multiple_discount) || $multiple_discount == 1}checked{/if}/>
                    <label for="cart_discount_specific_no" class="radioCheck">
                        <i class="color_success"></i> {l s=' No ' mod='pscartabandonmentpro'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
    </section>

    <section id="discount_list">
        {* Specific discount *}
        <section id="discount_is_specific" class="col-lg-12 col-xs-12 
            {if (isset($multiple_discount) && $multiple_discount == 1) || !isset($multiple_discount)}display_none{/if}
        ">
            <div class="discount_full_list">
                {foreach from=$discount_infos item=discount key=key}
                {include 
                        file='./discount/discount_multiple.tpl'
                        discount=$discount
                        key=($key+1)
                    } 
                {/foreach}
                {* if there is only 1 discount, we must show 2 lines in multiples discount *}
                {if count($discount_infos) == 1}
                    {include file='./discount/discount_multiple.tpl' key=2}
                {/if}
            </div>
            <div class="add_more nopadding-left">
                <div class="col-lg-10 col-xs-10 col-lg-offset col-xs-offset-1 nopadding-left">
                    <div class="col-lg-2 col-xs-2 nopadding-left">
                        <button name="add_one_discount">+</button>
                    </div>
                </div>
            </div>

        </section>


        {* Not specific discount *}
        <section id="discount_not_specific" class="col-lg-12 col-xs-12 
            {if isset($multiple_discount) && $multiple_discount > 1}display_none{/if}
        ">
            {include 
                file='./discount/discount_unique.tpl'
                discount=$discount_infos[0]
            } 
        </section>
    </section>
</div>
