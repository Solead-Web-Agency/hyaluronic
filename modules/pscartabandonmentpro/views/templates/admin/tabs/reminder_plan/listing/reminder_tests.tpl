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

{if !empty($reminderList)}
<div id="reminder_tests" class="reminder_listing panel panel-default col-xl-10 col-xl-offset-1 col-lg-12 col-xs-12 col-lg-offset-0" >
    <div class="panel-heading">
        <i class="material-icons">send</i>{l s='Test your reminders' mod='pscartabandonmentpro'}
    </div>
    <div class="panel-body">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="col-lg-12 top">
                <p>{l s='By clicking on "send emails", you will receive instantly every reminder. That way, you can check if they match with your expectations depending on the cart content.' mod='pscartabandonmentpro'}</p>
            </div>
            <div class="col-lg-3">
                <input class="form-control col-lg-6" type="email" name="reminder_test_email" value="{$reminder_test_email}" placeholder="test@email_domain.com"/>
            </div> 
            <div class="col-lg-12 alerts">
                {* Manage Ajax return msg*}
                <div class="col-lg-12 ajax_return false alert alert-warning"><p>{l s='Invalid Email.' mod='pscartabandonmentpro'}</p></div>
                <div class="col-lg-12 ajax_return true alert alert-success"><p>{l s='All emails have been sent' mod='pscartabandonmentpro'}</p></div>
            </div>
        </div>      
    </div>
    <div class="panel-footer">
        <div class="d-flex justify-content-end">
            <button name="submit_email_test" id="submit_email_test" type="submit" class="btn btn-primary">
                {l s='Send emails' mod='pscartabandonmentpro'}
            </button>
        </div>
    </div>
</div>
{/if}
