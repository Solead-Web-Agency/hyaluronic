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

<div class="intern-section nopadding-left col-lg-6 col-xs-6">
    <div class="nopadding-left col-lg-12 col-xs-12 control-label">
        {l s='Discount type' mod='pscartabandonmentpro'}</strong>
    </div>
    <div class="nopadding-left col-lg-12 col-xs-12">
        <select name="discount_value_type" class="nopadding-left col-lg-12 col-xs-12">
            <option value="percentage"
                {if isset($discount['discount_value_type']) && $discount['discount_value_type'] == 'percentage'}selected{/if}
            >{l s='Percentage' mod='pscartabandonmentpro'} ( % )</option>
            <option value="amount" 
                {if isset($discount['discount_value_type']) && $discount['discount_value_type'] == 'amount'}selected{/if}
            >{l s='Amount' mod='pscartabandonmentpro'} ( {$currency} )</option>
            <option value="freeshipping"
                {if isset($discount['discount_value_type']) && $discount['discount_value_type'] == 'freeshipping'}selected{/if}
            >{l s='Freeshipping' mod='pscartabandonmentpro'}</option>
            <option value="no_discount"
                {if isset($discount['discount_value_type']) && $discount['discount_value_type'] == 'no_discount'}selected{/if}
            >{l s='No discount' mod='pscartabandonmentpro'}</option>
        </select>
    </div>
</div>

<div class="intern-section nopadding-left col-lg-3 col-xs-3 discount_value">
    <div class="nopadding-left col-lg-12 col-xs-12 control-label"
        style="{if isset($discount['discount_value_type']) && ($discount['discount_value_type'] == 'freeshipping' || $discount['discount_value_type'] == 'no_discount')}display:none;{/if}"
    >
        {l s='Discount amount' mod='pscartabandonmentpro'}
    </div>
    <div class="nopadding-left col-lg-12 col-xs-12 input-group"
        style="{if isset($discount['discount_value_type']) && ($discount['discount_value_type'] == 'freeshipping' || $discount['discount_value_type'] == 'no_discount')}display:none;{/if}"
    >
        <input name="discount_value" type="number" min="1" aria-describedby="discount_value"
            value="{if isset($discount['discount_value'])}{$discount['discount_value']}{else}10{/if}"
        /> 
        <span class="input-group-addon" id="discount_value">
            <span class="currency"
                style="{if isset($discount['discount_value_type']) && $discount['discount_value_type'] == 'amount'}display: inline;{else}display:none;{/if}"
            >{$currency}</span>
            <span class="percentage" 
                style="{if isset($discount['discount_value_type']) && $discount['discount_value_type'] == 'amount'}display:none;{else}display:inline;{/if}"
            >%</span>
        </span>
    </div>
</div>

<div class="intern-section nopadding-left col-lg-3 col-xs-3 discount_validity"
    style="{if isset($discount['discount_value_type']) && $discount['discount_value_type'] == 'no_discount'}display:none;{/if}"
>
    <div class="nopadding-left col-lg-12 col-xs-12 control-label">
        {l s='Validity' mod='pscartabandonmentpro'}
    </div>
    <div class="nopadding-left col-lg-12 col-xs-12 input-group">
        <input name="discount_validity" type="number" class="form-control"  aria-describedby="discount_validity"
            value="{if isset($discount['discount_validity'])}{$discount['discount_validity']}{else}7{/if}"
        /> 
        <span class="input-group-addon" id="discount_validity">{l s='Days' mod='pscartabandonmentpro'}</span>
    </div>
</div>

<div class="nopadding-left col-lg-12 col-xs-12 checkboxes">
    <div class="discount_cumulate nopadding-left col-lg-6 col-xs-6 customradiodesign"
        style="{if isset($discount['discount_value_type']) && $discount['discount_value_type'] == 'no_discount'}display:none;{/if}"
    >
        <input type="checkbox" name="discount_cumulate" id="discount_cumulate" value="1" 
            {if (isset($discount['discount_cumulate']) && $discount['discount_cumulate'] == 1) || !isset($discount['discount_cumulate'])}checked{/if}
        />
        <label for="discount_cumulate"><span><span></span></span>{l s='Combinable' mod='pscartabandonmentpro'}</label>
    </div>

    <div class="discount_ttc col-lg-6 col-xs-6 customradiodesign"
        style="{if isset($discount['discount_value_type']) && ($discount['discount_value_type'] == 'freeshipping' || $discount['discount_value_type'] == 'no_discount')}display:none;{/if}"
    >
        <input type="checkbox" name="discount_ttc" id="discount_ttc" value="1" 
            {if (isset($discount['discount_ttc']) && $discount['discount_ttc'] == 1) || !isset($discount['discount_ttc'])}checked{/if}
        />
        <label for="discount_ttc"><span><span></span></span>{l s='Apply on price including taxes' mod='pscartabandonmentpro'}</label>
    </div>
</div>