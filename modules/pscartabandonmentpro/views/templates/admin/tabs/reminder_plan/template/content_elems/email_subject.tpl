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

<div class="subject row clear">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Email subject' mod='pscartabandonmentpro'}</label>
    </div>
    <div id="email_subject" class="col-lg-10 col-xs-10">
        <input type="text" autocomplete="off" class="form-control cap-lang-form" name="email_subject"  placeholder="{l s='Subject' mod='pscartabandonmentpro'}" 
            value="{if isset($template_datas[$lang.id_lang]['email_subject'])}{$template_datas[$lang.id_lang]['email_subject']}{/if}"
        />
        <span class="caract-count">
            <span class="amount">{if isset($template_datas[$lang.id_lang]['email_subject'])}{$template_datas[$lang.id_lang]['email_subject']|strlen}{else}0{/if}</span>/100 
            {l s='characters' mod='pscartabandonmentpro'}
        </span>
    </div>
    <div class="col-lg-10 col-xs-10">
        <p>{l s='Add the following tags to customize your subject' mod='pscartabandonmentpro'}</p>
    </div>
    <div class="subject-tags col-lg-10 col-xs-10">
        {foreach from=$custom_content key=name item=content}
            {if $content == '{first_name}' || $content == '{last_name}' || $content == '{gender}'}
            <button class="email_subject_custom" data-content="{$content}" data-type="subject">
                <i class="material-icons">add_circle</i>
                {$name}
            </button>
            {/if}
        {/foreach}
        {foreach from=$discount_content key=name item=content}
            {if $content == '{discount_value}'}
            <button class="email_subject_custom" data-content="{$content}" data-type="subject">
                <i class="material-icons">add_circle</i>
                {$name}
            </button>
            {/if}
        {/foreach}
    </div>
</div>
