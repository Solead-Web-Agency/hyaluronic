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
{if !empty($thumbgen.products)}
<ul class="ppro-product-showcase">
  {foreach from=$thumbgen.products item=product}
  <li class="ppro-product">
    <a href="https://addons.prestashop.com/en/{$product.id|intval}-.html">
      <div class="ppro-product-image"><img src="{$thumbgen.module_path|escape:'html':'UTF-8'}views/img/{$product.icon|escape:'html':'UTF-8'}.svg" alt="{$product.name|escape:'html':'UTF-8'}"></div>
      <h5 class="ppro-product-name">{$product.name|escape:'html':'UTF-8'}</h5>
      <p class="ppro-product-description">{$product.description|escape:'html':'UTF-8'}</p>
    </a>
  </li>
  {/foreach}
</ul>
{else}
  {l s='No products available' mod='thumbgen'}
{/if}

<p class="ppro-products-footer">
  <i class="icon-external-link"></i> <a href="https://addons.prestashop.com/en/2_community-developer?contributor=46184">{l s='Check out all our modules and themes on the Addons Marketplace' mod='thumbgen'}</a>
</p>
