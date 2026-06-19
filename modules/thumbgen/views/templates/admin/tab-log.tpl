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
<h3><i class="icon-list"></i> {l s='Log' mod='thumbgen'}</h3>

{if !empty($thumbgen.log)}
<div class="tg-log-controls">
  <button id="tg-log-download" class="btn btn-default" type="button"><i class="icon icon-download"></i> <span>{l s='Download log' mod='thumbgen'}</span></button>
  <button id="tg-log-clear" class="btn btn-default" type="button"><i class="icon icon-remove"></i> <span>{l s='Clear log' mod='thumbgen'}</span></button>
</div>

<p class="tg-block">{l s='Below are the last 100 lines from the log. Click "Download log" to get the full log file.' mod='thumbgen'}</p>

<ul id="tg-log-contents" class="tg-log-contents">
  {foreach $thumbgen.log as $line}
  <li>{$line|escape:'html':'UTF-8'}</li>
  {/foreach}
</ul>
{else}
<p>{l s='Thumbnail generation log is empty' mod='thumbgen'}</p>
{/if}

