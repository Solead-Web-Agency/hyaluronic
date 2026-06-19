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
*  @author     PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2023 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<table style="width: 100%">
  <tr>
    <td style="width: 50%; font-size: 9pt;"><img src="{$dhl_img_path|escape:'html':'UTF-8'}dhl.png" style="width:90px; height:20px;" /><br /><br />
      {$shop_details.name|escape:'html':'UTF-8'}<br />
      {$shop_details.addr1|escape:'html':'UTF-8'}<br />
      {$shop_details.addr2|escape:'html':'UTF-8'}<br />
      {$shop_details.zipcode|escape:'html':'UTF-8'} {$shop_details.city|escape:'html':'UTF-8'}<br />
      {if $shop_details.state}{$shop_details.state|escape:'html':'UTF-8'}<br />{/if}
      {$shop_details.country|escape:'html':'UTF-8'}<br />
    </td>
    <td style="width: 50%; text-align: left;">
      <table style="width: 100%">
        <tr>
          <td style="font-weight: bold; text-align: right; font-size: 14pt; color: #444; width: 100%;">
            <span style="display: block; width: 100%; font-size: 9pt;">
              {if $manifest_for == 'CA'}
                {l s='Copy for carrier' mod='dhlexpress'}
              {else}
                {l s='Copy for customer' mod='dhlexpress'}
              {/if}
            </span>
          </td>
        </tr>
        <tr>
          <td style="font-weight: bold;">END OF DAY MANIFEST / SHIPPING LIST</td>
        </tr>
        <tr>
          <td><span style="font-weight: bolder;">DATE : </span><span style="font-size: 9pt;">{$date|escape:'html':'UTF-8'}</span></td>
        </tr>
        <tr>
        {if $shop_details.dhlcountryiso == 'FR'}
          <td style="font-size: 9pt;"><br /><br />
            DHL International Express (France) SAS<br />
            Immeuble Le Mermoz,<br />
            53 Avenue Jean Jaurès<br />
            93351 LE BOURGET<br />
            FRANCE<br />
          </td>
        {else}
          <td style="font-size: 9pt;"><br /><br />
            DHL INTERNATIONAL EXPRESS {$shop_details.dhlcountryname|upper|escape:'html':'UTF-8'}
          </td>
        {/if}
        </tr>
      </table>
    </td>
  </tr>
</table>


