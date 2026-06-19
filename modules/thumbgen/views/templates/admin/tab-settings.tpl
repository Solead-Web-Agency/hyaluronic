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
<h3><i class="icon-wrench"></i> {l s='Configuration' mod='thumbgen'}</h3>

<p class="tg-block">{l s='Choose image types to be included in thumbnail regeneration.' mod='thumbgen'}</p>

<div class="tg-controls">
  <div class="tg-checkbox-container">
    <label>
      <input id="tg-select-all" type="checkbox">
      <span class="tg-checkbox-control"></span>
    </label>
  </div>

  <label class="tg-label" for="tg-select-all">{l s='Select all' mod='thumbgen'}</label>
</div>

<ul class="tg-columns">
  {foreach from=$thumbgen.image_types item=type}
  <li>
    <div class="tg-checkbox-container">
      <label>
        <input id="tg-image-type-{$type.id_image_type|intval}" class="tg-checkbox" type="checkbox" name="tg_image_type" value="{$type.id_image_type|intval}"{if in_array($type.id_image_type, $thumbgen.checked)} checked="checked"{/if}>
        <span class="tg-checkbox-control"></span>
      </label>
    </div>

    <label class="tg-label" for="tg-image-type-{$type.id_image_type|intval}">{$type.name|escape:'html':'UTF-8'}</label>
  </li>
  {/foreach}
</ul>

<p class="tg-block">{l s='Choose how many images should be processed at once. Greater values will generally increase thumbnail generation speed but may lead to timeouts on slower servers. Try using a lower value if thumbnail generation stops halfway with an error.' mod='thumbgen'}</p>

<div class="tg-range-block">
  <input id="tg-chunk" class="tg-range" type="range" min="1" max="10" step="1" value="{$thumbgen.chunk|intval}">
  <output id="tg-chunk-value" class="tg-range-output" for="tg-chunk">{$thumbgen.chunk|intval}</output>
</div>


