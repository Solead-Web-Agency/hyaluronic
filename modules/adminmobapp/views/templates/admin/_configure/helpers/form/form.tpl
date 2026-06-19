{*
* 2007-2025 PrestaShop
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
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}

{block name="field"}

{if isset($input.type) && $input.type == "connection"}

<div class="col-lg-5">
    <div class="input-group">
        <span class="input-group-addon">
          <i class="icon-unlock-alt"></i>
      </span>
      <input type="text" name="ADMINMOBAPP_CONNECTION_KEY" value="{$configValues.ADMINMOBAPP_CONNECTION_KEY|escape:'html':'UTF-8'}" class="form-control" placeholder="{l s='H3aK8oP6' mod='adminmobapp'}" minlength="6" maxlength="8">

      <span class="input-group-btn">
        <button type="button" class="btn btn-default" id="generateKeyButton">{l s='Generate Key' mod='adminmobapp'}</button>
    </span>

</div>
<p class="help-block">
{l s='Enter a valid Connection Key: H3aK8oP6' mod='adminmobapp'}</p>
</div>

{elseif isset($input.type) && $input.type == "qrcode"}

<div class="col-lg-5">
    <div class="input-group">
        <img src="{$qrcode_url|escape:'html':'UTF-8'}" alt="QR Code" />
    </div>
    <p class="help-block">
        {l s='It helps to connect to the store automatically without entering your settings, credentials, and store URL every time.' mod='adminmobapp'}
    </p>
</div>
{elseif isset($input.type) && $input.type == "auth_token"}

<div class="col-lg-5">
    <div class="input-group">
        <span class="input-group-addon">
          <i class="icon-unlock-alt"></i>
      </span>
      <input type="text" name="ADMINMOBAPP_AUTH_TOKEN" value="{$configValues.ADMINMOBAPP_AUTH_TOKEN|escape:'html':'UTF-8'}" class="form-control" placeholder="{l s='H3aK8oP6qR2sX9tY56A1bF' mod='adminmobapp'}" minlength="6" maxlength="30">

      <span class="input-group-btn">
        <button type="button" class="btn btn-default" id="generateToken">{l s='Generate Key' mod='adminmobapp'}</button>
    </span>

</div>
<p class="help-block">
{l s='Enter a valid Auth Bearer Token: H3aK8oP6qR2sX9tY56A1bF' mod='adminmobapp'}</p>
</div>

{elseif isset($input.type) && $input.type == "employee_list"}
<div class="col-lg-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                {l s='Select employees who can access the admin app' mod='adminmobapp'}
            </h5>
            <div class="choice-table">
                <table class="table table-bordered mb-0">
                    
                    <tbody>
                        {foreach $employeeOptions as $employee}
                        <tr>
                            <td>
                                <div class="checkbox">
                                    <div class="md-checkbox md-checkbox-inline">
                                        <label>
                                            <input type="checkbox" id="employee{$employee.id_employee|escape:'html':'UTF-8'}" name="module_settings[employee_ids][]" value="{$employee.id_employee|escape:'html':'UTF-8'}" {if in_array($employee.id_employee, $selected_employee)} checked="checked"{/if}>
                                            <i class="md-checkbox-control"></i>
                                            {$employee.name|escape:'html':'UTF-8'}
                                        </label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{else}
{$smarty.block.parent}
{/if}
{/block}
