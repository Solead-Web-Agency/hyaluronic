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
{if $thumbgen.promo_mode == 'top-logo' || $thumbgen.promo_mode == 'top-no-logo'}
  {include file="./promo.tpl"}
{/if}

<div class="row">
  <div class="col-sm-3 col-md-3 col-lg-2">
    <nav class="list-group">
      <a class="list-group-item active" href="#ppro-thumbgen" data-toggle="tab"><i class="icon-image"></i> {l s='Thumbnails' mod='thumbgen'}</a>
      <a class="list-group-item" href="#ppro-settings" data-toggle="tab"><i class="icon-wrench"></i> {l s='Configuration' mod='thumbgen'}</a>
      <a class="list-group-item" href="#ppro-log" data-toggle="tab"><i class="icon-list"></i> {l s='Log' mod='thumbgen'}</a>
      <a class="list-group-item" href="#ppro-changelog" data-toggle="tab"><i class="icon-info-circle"></i> {l s='Changelog' mod='thumbgen'}</a>
      <a class="list-group-item" href="#ppro-contact" data-toggle="tab"><i class="icon-envelope"></i> {l s='Contact us' mod='thumbgen'}</a>
    </nav>

    <div class="list-group">
      <div class="list-group-item"><i class="icon-info-circle"></i> {l s='Version' mod='thumbgen'} {$thumbgen.version|escape:'htmlall':'UTF-8'}</div>
    </div>

    {if $thumbgen.promo_mode != 'top-logo' && $thumbgen.promo_mode != 'bottom-logo' && $thumbgen.promo_mode != 'bottom-links'}
    <div class="panel">
      {include file="./promo-developer.tpl"}
    </div>
    {/if}
  </div>

  <div class="ppro-content tab-content col-sm-9 col-md-9 col-lg-10">
    <div id="ppro-thumbgen" class="tab-pane panel active">
      {include file="./tab-thumbgen.tpl"}
    </div>

    <div id="ppro-settings" class="tab-pane panel">
      {include file="./tab-settings.tpl"}
    </div>

    <div id="ppro-log" class="tab-pane panel">
      {include file="./tab-log.tpl"}
    </div>

    <div id="ppro-changelog" class="tab-pane panel">
      {include file="./tab-changelog.tpl"}
    </div>

    <div id="ppro-contact" class="tab-pane panel">
      {include file="./tab-contact.tpl"}
    </div>
  </div>
</div>

{if $thumbgen.promo_mode != 'top-logo' && $thumbgen.promo_mode != 'top-no-logo'}
  {include file="./promo.tpl"}
{/if}

{* this button is dynamically moved to top *}
<a id="module-documentation" class="toolbar_btn hidden" href="{$thumbgen.documentation_link|escape:'html':'UTF-8'}" target="_blank" title="{l s='Documentation' mod='thumbgen'}">
  <i class="process-icon-t icon-file-text"></i>
  <div>{l s='Documentation' mod='thumbgen'}</div>
</a>
<script type="text/javascript">
$(document).ready(function(){
  $('ul.nav.nav-pills').prepend('<li class="li-docs"></li>');
  $('#module-documentation').prependTo('.li-docs').removeClass('hidden');
});
</script>
