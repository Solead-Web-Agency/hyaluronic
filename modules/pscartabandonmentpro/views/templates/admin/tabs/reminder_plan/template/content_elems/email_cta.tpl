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

<div id="email_cta" class="row clear cap-email-cta">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Call to action text' mod='pscartabandonmentpro'}</label>
    </div>
    <div class="col-lg-10 col-xs-10">
        <p>{l s='If nothing is written, no CTA will be shown.' mod='pscartabandonmentpro'}</p>
    </div>
    <div class="col-lg-10 col-xs-10">
        <input type="text" autocomplete="off" name="email_cta" placeholder="(optional)" 
        value="{if !empty($template_datas[$lang.id_lang]['email_cta'])}{$template_datas[$lang.id_lang]['email_cta']}{/if}"
        />
        <span class="caract-count">
            <span class="amount">{if isset($template_datas[$lang.id_lang]['email_cta'])}{$template_datas[$lang.id_lang]['email_cta']|strlen}{else}0{/if}</span>/25 
            {l s='characters' mod='pscartabandonmentpro'}
        </span>
    </div>
</div>
