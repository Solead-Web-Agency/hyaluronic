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
<h3><i class="icon-image"></i> {l s='Thumbnails' mod='thumbgen'}</h3>

<div class="tg-block">
  <button id="tg-start-all" class="btn btn-default" type="button">{l s='Regenerate thumbnails in all image groups' mod='thumbgen'}</button>
</div>

<p class="tg-block">{l s='Click Start to regenerate all thumbnails in the corresponding image group. Click Stop to pause and Resume to continue regeneration from where you left off.' mod='thumbgen'}</p>

<div id="tg-skipped" class="tg-block tg-warning{if $thumbgen.skipped <= 0} tg-hidden{/if}">
  <p>{l s='Please note that' mod='thumbgen'} <span id="tg-skipped-count">{$thumbgen.skipped|intval}</span> {l s='image(s) have been skipped because PHP does not have enough memory to resize them. You can set the memory limit to at least' mod='thumbgen'} <span id="tg-required-memory">{$thumbgen.required_memory|intval}</span> {l s='MB in the PHP configuration file, restart your server and use the buttons below to generate thumbnails for all previously skipped images.' mod='thumbgen'}</p>

  <table class="tg-table">
    <thead>
      <tr>
        <th class="tg-narrow" scope="col">{l s='Actions' mod='thumbgen'}</th>
        <th class="tg-extra-wide" scope="col">{l s='Status' mod='thumbgen'}</th>
        <th class="tg-tiny tg-centered" scope="col">{l s='Image count' mod='thumbgen'}</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <button class="tg-button tg-start btn btn-default" type="button" title="{l s='Start' mod='thumbgen'}" data-type="skipped"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/><path d="M0 0h24v24H0z" fill="none"/></svg></button>
          <button class="tg-button tg-stop btn btn-default" type="button" title="{l s='Stop' mod='thumbgen'}" data-type="skipped"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M6 6h12v12H6z"/></svg></button>
        </td>
        <td>
          <div class="tg-bar">
            <span class="tg-percent"><span class="tg-percent-number">0</span>%</span>
            <span class="tg-progress"></span>
          </div>
        </td>
        <td class="tg-centered"><span class="tg-processed">0</span> / <span id="tg-skipped-total" class="tg-total">{$thumbgen.skipped|intval}</span></td>
      </tr>
    </tbody>
  </table>
</div>

<table class="tg-table">
  <thead>
    <tr>
      <th class="tg-narrow" scope="col">{l s='Image group' mod='thumbgen'}</th>
      <th class="tg-narrow" scope="col">{l s='Actions' mod='thumbgen'}</th>
      <th class="tg-wide" scope="col">{l s='Status' mod='thumbgen'}</th>
      <th class="tg-tiny tg-centered" scope="col">{l s='Image count' mod='thumbgen'}</th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$thumbgen.groups key=key item=group}
    <tr id="tg-{$group.name|escape:'html':'UTF-8'}">
      <th scope="row">{$group.title|escape:'html':'UTF-8'}</th>
      <td>
        <button class="tg-button tg-start btn btn-default" type="button" title="{l s='Start' mod='thumbgen'}" data-type="{$key|escape:'html':'UTF-8'}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/><path d="M0 0h24v24H0z" fill="none"/></svg></button>
        <button class="tg-button tg-resume btn btn-default" type="button" title="{l s='Resume' mod='thumbgen'}" data-type="{$key|escape:'html':'UTF-8'}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/><path d="M0 0h24v24H0z" fill="none"/></svg></button>
        <button class="tg-button tg-stop btn btn-default" type="button" title="{l s='Stop' mod='thumbgen'}" data-type="{$key|escape:'html':'UTF-8'}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M6 6h12v12H6z"/></svg></button>
      </td>
      <td>
        <div class="tg-bar" data-complete="{$group.progress.percent|intval}">
          <span class="tg-percent"><span class="tg-percent-number">0</span>%</span>
          <span class="tg-progress"></span>
        </div>
      </td>
      <td class="tg-centered"><span class="tg-processed">{$group.progress.processed|intval}</span> / <span class="tg-total">{$group.progress.total|intval}</span></td>
    </tr>
    {/foreach}
  </tbody>
</table>
