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

<section id="delete_reminder" class="cap_popin">
    <div class="content_cancel">
        <h2>{l s='Delete the reminder' mod='pscartabandonmentpro'}</h2>
        <div class="cancel_text col-lg-12">
            <p>{l s='Are you sure you want to delete this reminder ?' mod='pscartabandonmentpro'}</p>
        </div>
        <div class="buttons d-flex justify-content-end col-lg-12">
            <button name="cancel" type="submit" class="btn btn-primary">{l s='Cancel' mod='pscartabandonmentpro'}</button>
            <button name="confirm" type="submit" class="btn btn-back" data-delete_id='0'>{l s='Yes I\'m sure' mod='pscartabandonmentpro'}</button>
        </div>
        <div class="ajax_return d-flex justify-content-end col-lg-12">
            <div class="success">
                <i class="material-icons">done</i>
            </div>
            <div class="error">
                <i class="material-icons">error</i> <span>{l s='An error occured' mod='pscartabandonmentpro'}</span>
            </div>
        </div>
    </div>
</section>