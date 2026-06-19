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

<div id="reminder_target_frequency" class="steps_panel col-lg-12 col-xs-12">
    {* 
     * Targets 
    *}
    <section class="col-lg-12 col-xs-12">
        <h2>
            {l s='Target' mod='pscartabandonmentpro'}
        </h2>

        <div class="intern-section col-lg-12 col-xs-12">
            <div class="col-lg-6 col-xs-6 text-right control-label">
                <strong>{l s='Do you want to exclude customers who did not subscribe to the newsletter in your reminder campaign ?' mod='pscartabandonmentpro'}</strong>
            </div>
            <div class="col-lg-6 col-xs-6">
                <span id="cart_target_newsletter" class="switch prestashop-switch input-group col-sm-12 col-md-8 col-lg-8 col-xs-8">
                    <input type="radio" name="cart_target_newsletter" id="cart_target_newsletter_yes" value="1" 
                        {if (isset($target_infos['cart_target_newsletter']) && $target_infos['cart_target_newsletter'] == 1)}checked{/if} 
                    />
                    <label for="cart_target_newsletter_yes" class="radioCheck">
                        <i class="color_success"></i> {l s=' Yes ' mod='pscartabandonmentpro'}
                    </label>
                    <input type="radio" class="switch_off" name="cart_target_newsletter" id="cart_target_newsletter_no" value="0" 
                        {if (isset($target_infos['cart_target_newsletter']) && $target_infos['cart_target_newsletter'] == 0)  || !isset($target_infos['cart_target_newsletter'])}checked{/if}
                    />
                    <label for="cart_target_newsletter_no" class="radioCheck">
                        <i class="color_success"></i> {l s=' No ' mod='pscartabandonmentpro'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>

        <div class="intern-section col-lg-12 col-xs-12">
            <div class="col-lg-6 col-xs-6 text-right control-label">
                <strong>{l s='Select Customer profiles' mod='pscartabandonmentpro'}</strong>
            </div>
            <div class="col-lg-6 col-xs-6">
                <div class="col-lg-12 col-xs-12 customradiodesign">
                    <input type="checkbox" name="cart_target[]" id="cart_target_active" value="active" 
                        {if (isset($target_infos['cart_target_active']) && $target_infos['cart_target_active'] == 1) || !isset($target_infos['cart_target_active'])}checked{/if}
                    />
                    <label for="cart_target_active"><span><span></span></span>{l s='Active customers (last order in the last 6 months)' mod='pscartabandonmentpro'}</label>
                </div>
                <div class="col-lg-12 col-xs-12 customradiodesign">
                    <input type="checkbox" name="cart_target[]" id="cart_target_inactive" value="inactive" 
                        {if (isset($target_infos['cart_target_inactive']) && $target_infos['cart_target_inactive'] == 1) || !isset($target_infos['cart_target_inactive'])}checked{/if}
                    />
                    <label for="cart_target_inactive"><span><span></span></span>{l s='Inactive customers (last order older than 6 months)' mod='pscartabandonmentpro'}</label>
                </div>
                <div class="col-lg-12 col-xs-12 customradiodesign">
                    <input type="checkbox" name="cart_target[]" id="cart_target_no_orders" value="no_orders" 
                        {if (isset($target_infos['cart_target_no_orders']) && $target_infos['cart_target_no_orders'] == 1) || !isset($target_infos['cart_target_no_orders'])}checked{/if}
                    />
                    <label for="cart_target_no_orders"><span><span></span></span>{l s='Customers without orders' mod='pscartabandonmentpro'}</label>
                </div>
            </div>
        </div>
    </section>
    {* 
     * Frequency 
    *}
    <section class="col-lg-12 col-xs-12">
        <h2>
            {l s='Send' mod='pscartabandonmentpro'}
        </h2>
        <div class="intern-section col-lg-6 col-xs-6 text-right control-label">
            <strong>{l s='Send the reminder e-mail after' mod='pscartabandonmentpro'}</strong>
            <p class="btn btn-secondary tooltipps" data-toggle="tooltip" data-placement="right" title="{l s='We recommend to choose 1 hour for the first reminder, 1 day for the second reminder and 5 days for the third reminder.' mod='pscartabandonmentpro'}">?</p>
        </div>
        <div class="intern-section col-lg-6 col-xs-6">
            <div class="col-lg-2 col-xs-2">
                <input type="number" name="cart_frequency_number" id="cart_frequency_number" placeholder="1" min="1"
                    value="{if isset($target_infos['cart_frequency_number'])}{$target_infos['cart_frequency_number']}{else}1{/if}" 
                />
            </div>
            <label for="cart_frequency_type_hour" class="col-lg-2 col-xs-2 btn-radio {if (isset($target_infos['cart_frequency_type']) && $target_infos['cart_frequency_type'] == 'hour') || !isset($target_infos['cart_frequency_type'])}selected{/if}">
                <input id="cart_frequency_type_hour" name="cart_frequency_type" type="radio" value="hour" 
                    {if (isset($target_infos['cart_frequency_type']) && $target_infos['cart_frequency_type'] == 'hour') || !isset($target_infos['cart_frequency_type'])}checked{/if}
                />
                <label for="cart_frequency_type_hour">{l s='Hours' mod='pscartabandonmentpro'}</label>
            </label>
            <label for="cart_frequency_type_day" class="col-lg-2 col-xs-2 btn-radio {if isset($target_infos['cart_frequency_type']) && $target_infos['cart_frequency_type'] == 'day'}selected{/if}">
                <input id="cart_frequency_type_day" name="cart_frequency_type" type="radio" value="day" 
                    {if isset($target_infos['cart_frequency_type']) && $target_infos['cart_frequency_type'] == 'day'}checked{/if}
                />
                <label for="cart_frequency_type_day">{l s='Days' mod='pscartabandonmentpro'}</label>
            </label>
        </div>
    </section>
</div>

 