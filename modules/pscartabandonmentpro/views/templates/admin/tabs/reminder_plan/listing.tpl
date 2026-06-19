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

<div id="reminder_listing" class="reminder_listing panel panel-default col-xl-10 col-xl-offset-1 col-lg-12 col-xs-12 col-lg-offset-0" >
    <div class="panel-heading">
        <i class="material-icons">email</i>{l s='Reminder list' mod='pscartabandonmentpro'} ({$reminderList|count})
    </div>
    {* 
        If $reminderList is not empty we show a table 
        else, we show an alert to explain that they must create a reminder
    *}
    {if !empty($reminderList)}
    <div class="panel-body" id="cap-email">
        <div class="clearfix">
            <div class="listing-table col-lg- xsg-12">
                <div class="listing-head col-lg-12 col-xs-12">
                    <div class="col-lg-2 col-xs-2">{l s='Send after' mod='pscartabandonmentpro'}</div>
                    <div class="col-lg-4 col-xs-4">{l s='Email Subject' mod='pscartabandonmentpro'}</div>
                    <div class="col-lg-2 col-xs-2">{l s='Discount' mod='pscartabandonmentpro'}</div>
                    <div class="col-lg-1 col-xs-1">{l s='Languages' mod='pscartabandonmentpro'}</div>
                    <div class="col-lg-2 col-xs-2">{l s='Status' mod='pscartabandonmentpro'}</div>
                    <div class="col-lg-1 col-xs-1">{l s='Actions' mod='pscartabandonmentpro'}</div>
                </div>
                <div class="listing-body col-lg-12 col-xs-12">
                    {foreach from=$reminderList key=key item=reminder}
                    <div  data-id='{$reminder.id_cart_abandonment}' class="listing-general-rol col-lg-12 col-xs-12">
                        <div class="listing-row col-lg-12 col-xs-12">
                            <div class="col-lg-2 col-xs-2">
                                <i class="show-more material-icons">keyboard_arrow_right</i> 
                                {$reminder.cart_frequency_number} {$reminder.cart_frequency_type}
                            </div>
                            <div class="col-lg-4 col-xs-4">{$reminder.email_subject}</div>
                            <div class="col-lg-2 col-xs-2">
                                {if intval($reminder.discount_nb) > 1 }
                                    <u>{l s='Depending on ranges' mod='pscartabandonmentpro'}</u>
                                {else if $reminder.discount_value_type == 'no_discount'}
                                    {l s='No discount' mod='pscartabandonmentpro'}
                                {else}
                                    {$reminder.discount_value_type}
                                {/if}
                            </div>
                            <div class="col-lg-1 col-xs-1">{$reminder.template_langs}</div>
                            <div class="col-lg-2 col-xs-2">
                                {include 
                                    file='./listing/reminder_activate.tpl' 
                                    id=$reminder.id_cart_abandonment 
                                    key=key 
                                    active=$reminder.active
                                }
                            </div>
                            <div class="col-lg-1 col-xs-1">
                                <span class="listing-edit col-lg-4 col-xs-4" data-id='{$reminder.id_cart_abandonment}'>
                                    <i class="material-icons">edit</i>
                                </span>
                                <span class="listing-actions col-lg-4 col-xs-4">
                                    <i class="material-icons">more_vert</i>
                                    {include 
                                        file='./listing/reminder_actions.tpl' 
                                        id=$reminder.id_cart_abandonment
                                    }
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-12 col-xs-12">
                            <div id="{$reminder.id_cart_abandonment}_more_info" class="col-lg-12 col-xs-12 more_info ajax_return"></div>
                        </div>
                    </div>
                    {/foreach}
                </div>
            </div>
        </div>
        <div class="clearfix">
            <div class="d-flex justify-content-end">
                {if {$reminderList|count} < 5}
                <button name="createNewReminder" id="createNewReminder" type="text" class="btn btn-primary">{l s='Create new Reminder' mod='pscartabandonmentpro'}</button>
                {else}
                <button name="limitreached" type="text" class="btn btn-secondary">{l s='Reminder limit has been reached' mod='pscartabandonmentpro'}</button>
                {/if}
            </div>
        </div>
    </div>
    {else}
    <div class="panel-body first_reminder" id="cap-email">
        <button name="createNewReminder" id="createNewReminder" type="text" class="btn btn-primary">{l s='Create your first Reminder !' mod='pscartabandonmentpro'}</button>
    </div>
    {/if}
</div>

{include file='./listing/reminder_tests.tpl'}
{include file='./listing/reminder_delete_popin.tpl'}
{include file='./listing/reminder_preview_popin.tpl'}