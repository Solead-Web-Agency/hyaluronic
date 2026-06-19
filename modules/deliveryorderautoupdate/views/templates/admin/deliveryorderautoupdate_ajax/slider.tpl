{*
* 2007-2023 PrestaShop
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
*  @author Helloshop <contact@prestashop.com>
*  @copyright  2007-2023 Helloshop
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Helloshop
*}
{if $slider.min != '' && $slider.max != ''}
<div class="range_container">
    <div class="sliders_control">
       <input id="fromSlider" type="range" value="{$slider.min}" min="{$slider.min}" max="{$slider.max}"/>
       <input id="toSlider" type="range" value="{$slider.max}" min="{$slider.min}" max="{$slider.max}"/>
       <span class="from-value"><span id="fromInput">{$slider.min}</span> {l s='days' mod='deliveryorderautoupdate'}</span>
       <span class="to-value"><span id="toInput">{$slider.max}</span> {l s='days' mod='deliveryorderautoupdate'}</span>
     </div>
</div>
{/if}