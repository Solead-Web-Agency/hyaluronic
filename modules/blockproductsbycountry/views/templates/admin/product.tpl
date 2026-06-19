{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*}

<div class="form-wrapper">
  <div class="form-group">
    <label class="control-label col-lg-3">{l s='Blocked countries' mod='blockproductsbycountry'}</label>
    <select name="blocked_countries[]" multiple="multiple" class="bpbc_multiselect">
      {foreach from=$countries item=country}
      <option value="{$country.id_country|escape:'htmlall':'utf-8'}"{if isset($country.selected) && $country.selected} selected="selected"{/if}>{$country.name|escape:'htmlall':'utf-8'}</option>
      {/foreach}
    </select>
  </div>
  <div class="clearfix"></div>
</div>