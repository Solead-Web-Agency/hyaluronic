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

<label class="col-lg-12" id="reminder_active_{$key}" for="reminder_active_{$key}" data-cart_reminder_id='{$id}'>
    <section class="switch-input {if $active}-checked{/if}">
        <input data-toggle="switch" class="switch-new" data-inverse="true" type="checkbox" name="reminder_active_{$key}" checked="">
    </section> 
    <span class="switch_text switch-on" style="{if !$active}display:none;{/if}">{l s='Activated' mod='pscartabandonmentpro'}</span>
    <span class="switch_text switch-off" style="{if $active}display:none;{/if}">{l s='Disabled' mod='pscartabandonmentpro'}</span>
</label>
