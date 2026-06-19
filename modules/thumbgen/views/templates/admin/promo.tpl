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
*  @author     PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="row">
  <div class="ppro-information panel clearfix">
    <h3><i class="icon-info-circle"></i> {l s='Our products' mod='thumbgen'}</h3>

    {if !empty($thumbgen.promo_mode)}
      {if $thumbgen.promo_mode == 'top-logo' || $thumbgen.promo_mode == 'bottom-logo'}
        <div class="col-lg-2">
          {include file="./promo-developer.tpl"}
        </div>

        <div class="col-lg-10">
          {include file="./promo-product-list.tpl"}
        </div>
      {elseif $thumbgen.promo_mode == 'top-no-logo' || $thumbgen.promo_mode == 'bottom-no-logo'}
        {include file="./promo-product-list.tpl"}
      {elseif $thumbgen.promo_mode == 'bottom-links'}
        <div class="col-lg-4">
          <ul class="ppro-quick-links">
            <li><i class="icon-file-text"></i> <a target="_blank" href="{$thumbgen.documentation_link|escape:'html':'UTF-8'}">{l s='Download module documentation' mod='thumbgen'}</a></li>
            <li><i class="icon-medkit"></i> <a target="_blank" href="https://addons.prestashop.com/en/contact-us?id_product={$thumbgen.module_id|intval}">Contact us if you need help</a></li>
          </ul>

          {include file="./promo-developer.tpl"}
        </div>

        <div class="col-lg-8">
          {include file="./promo-product-list.tpl"}
        </div>
      {/if}
    {else}
      {include file="./promo-product-list.tpl"}
    {/if}
  </div>
</div>
