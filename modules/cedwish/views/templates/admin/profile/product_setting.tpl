<!--
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */
-->

<div class="panel">
    <div class="panel-body">
        <div class="row">
            <div class="form-group form-group-lg">
                <label class="form-control-label" for="percent_increment">{l s='Price Markup By Percent' mod='cedwish'}</label>
                <input
                        type="text"
                        name="product_setting[percent_increment]"
                        id="percent_increment"
                        placeholder="{l s='Enter percent amount to be increase' mod='cedwish'}"
                        value="{if isset($product_setting['percent_increment'])}{$product_setting['percent_increment']|escape:'htmlall':'UTF-8'}{/if}"
                />
            </div>
            <div class="form-group form-group-lg">
                <label class="form-control-label" for="fix_increment">{l s='Price Markup By Fix Amount' mod='cedwish'}</label>
                <input
                        type="text"
                        name="product_setting[fix_increment]"
                        id="fix_increment"
                        placeholder="{l s='Enter amount to be increase' mod='cedwish'}"
                        value="{if isset($product_setting['fix_increment'])}{$product_setting['fix_increment']|escape:'htmlall':'UTF-8'}{/if}"
                />
            </div>
            <div class="form-group form-group-lg">
                <label class="form-control-label" for="customs_hs_code">{l s='Custom HS Code' mod='cedwish'}</label>
                <input
                        type="text"
                        name="product_setting[customs_hs_code]"
                        id="customs_hs_code"
                        placeholder="{l s='Enter Custom HS Code' mod='cedwish'}"
                        value="{if isset($product_setting['customs_hs_code'])}{$product_setting['customs_hs_code']|escape:'htmlall':'UTF-8'}{/if}"
                />
            </div>
            <div class="form-group form-group-lg">
                <label class="form-control-label" for="origin_country">{l s='Origin Country' mod='cedwish'}</label>
                <select
                        type="text"
                        name="product_setting[origin_country]"
                        id="origin_country"
                >
                    {foreach $shippableCountries as $iso_code => $name}
                        <option
                                value="{$iso_code|escape:'htmlall':'UTF-8'}"
                                {if isset($product_setting['origin_country'])
                                && ($product_setting['origin_country']==$iso_code)
                                }
                                    selected="selected"
                                {elseif isset($default_origin_country)
                                && ($default_origin_country==$iso_code)}
                                    selected="selected"
                                {/if}
                        >
                            {$name|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group form-group-lg">
                <label class="form-control-label" for="restricted_flags">{l s='Restricted Flags' mod='cedwish'}</label>
                </br>
                <input type="hidden" value="" name="product_setting[restricted_flags][]">
                {if isset($product_setting['restricted_flags'])
                    && $product_setting['restricted_flags']
                    && in_array('HAS_POWDER',$product_setting['restricted_flags'])
                }
                    <input checked="checked" type="checkbox" value="HAS_POWDER" name="product_setting[restricted_flags][]">
                    <b>HAS_POWDER</b> </br>
                {else}
                    <input type="checkbox" value="HAS_POWDER" name="product_setting[restricted_flags][]">
                    <b>HAS_POWDER</b> </br>
                {/if}
                {if isset($product_setting['restricted_flags'])
                && $product_setting['restricted_flags']
                && in_array('HAS_LIQUID',$product_setting['restricted_flags'])
                }
                    <input checked="checked" type="checkbox" value="HAS_LIQUID" name="product_setting[restricted_flags][]">
                    <b>HAS_LIQUID</b> </br>
                {else}
                    <input type="checkbox" value="HAS_LIQUID" name="product_setting[restricted_flags][]">
                    <b>HAS_LIQUID</b> </br>
                {/if}
                {if isset($product_setting['restricted_flags'])
                && $product_setting['restricted_flags']
                && in_array('HAS_BATTERY',$product_setting['restricted_flags'])
                }
                    <input checked="checked" type="checkbox" value="HAS_BATTERY" name="product_setting[restricted_flags][]">
                    <b>HAS_BATTERY</b> </br>
                {else}
                    <input type="checkbox" value="HAS_BATTERY" name="product_setting[restricted_flags][]">
                    <b>HAS_BATTERY</b> </br>
                {/if}
                {if isset($product_setting['restricted_flags'])
                && $product_setting['restricted_flags']
                && in_array('HAS_METAL',$product_setting['restricted_flags'])
                }
                    <input checked="checked" type="checkbox" value="HAS_METAL" name="product_setting[restricted_flags][]">
                    <b>HAS_METAL</b> </br>
                {else}
                    <input type="checkbox" value="HAS_METAL" name="product_setting[restricted_flags][]">
                    <b>HAS_METAL</b> </br>
                {/if}
            </div>
        </div>
    </div>
</div>
