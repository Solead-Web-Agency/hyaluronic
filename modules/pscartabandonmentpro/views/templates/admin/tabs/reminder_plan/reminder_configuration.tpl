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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="reminder_configuration" class="panel panel-default col-xl-10 col-xl-offset-1 col-lg-12 col-lg-offset-0" data-step="1">
    <div class="panel-summary-heading">
        <div class="bandeau"></div>
        <ul>
            <li class="active">
                <p><i class="material-icons">people</i></p>
                <p>1 - {l s='Target & Frequency' mod='pscartabandonmentpro'}</p>
            </li>
            <li>
                <p><i class="material-icons">local_offer</i></p>
                <p>2 - {l s='Discount' mod='pscartabandonmentpro'}</p>
            </li>
            <li>
                <p><i class="material-icons">create</i></p>
                <p>3 - {l s='Email template' mod='pscartabandonmentpro'}</p>
            </li>
        </ul>
    </div>
    <div class="panel-body" id="cap-email">
        <div id="reminder_configuration_step" class="col-lg-12 col-xs-12" data-action="add"></div>
    </div>
    <div class="panel-footer">
        <div class="clearfix col-lg-6 col-xs-6">
            <div class="d-flex justify-content-begin">
                <button name="cancelConfiguration" id="cancelConfiguration" type="submit" class="btn btn-back">{l s='Cancel' mod='pscartabandonmentpro'}</button>
            </div>
        </div>
        <div class="clearfix col-lg-6 col-xs-6">
            <div class="d-flex justify-content-end">
                <button name="previous" id="previousConfiguration" type="submit" class="btn btn-primary">{l s='Previous' mod='pscartabandonmentpro'}</button>
                <button name="next" id="nextConfiguration" type="submit" class="btn btn-primary can-go-next">{l s='Next' mod='pscartabandonmentpro'}</button>
                <button name="saveConfiguration" id="saveConfiguration" type="submit" class="btn btn-primary">{l s='Save' mod='pscartabandonmentpro'}</button>
                <button name="updateConfiguration" id="updateConfiguration" type="submit" class="btn btn-primary">{l s='Update' mod='pscartabandonmentpro'}</button>
            </div>
        </div>
    </div>
</div>
{include file='./cancel.tpl'}
 