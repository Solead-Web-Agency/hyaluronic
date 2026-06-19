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

<div class="tab-pane fade {if $section=='prb-settings'}active in{/if}" id="prb-settings" role="tabpanel" aria-labelledby="prb-settings-tab">
    <input type="hidden" class="REVOLUT_PRB_SELECTED_LOCATIONS" value='{$REVOLUT_PRB_LOCATIONS|escape:'htmlall':'UTF-8'}'>
    {html_entity_decode($prb_settings_form|escape:'htmlall':'UTF-8')}
</div>