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

<div id="cap_content_conf" class="cap_content_appearance_conf col-lg-12">
    <div class="inner-template-section form-control-label" data-selected='0'>
        {l s='Text & Content' mod='pscartabandonmentpro'}
        <i class="material-icons">keyboard_arrow_down</i>
    </div>

    <div class="cap_content_conf_elems display_none">
        <div class="row clear">
            <div class="col-lg-12">
                <label>{l s='Language to configure' mod='pscartabandonmentpro'}</label>
            </div>
            <div class="col-lg-7">
                <select name="cap-email-language" class="form-control">
                    {foreach from=$languages item=lang}
                        <option value="{$lang.id_lang}" {if $lang.id_lang == $employeeLangId}selected{/if}>{$lang.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {* For each lang, add a form *}
        {foreach from=$languages key=k item=lang}
            <section class="content_by_lang lang_{$lang.id_lang|intval}" style="{if $lang.id_lang != $employeeLangId && $languages|count > 1}display:none;{/if}">
                <input type="hidden" name="id_lang" value="{$lang.id_lang|intval}"/>

                {* Email subject *}
                {include file="./content_elems/email_subject.tpl"}

                {* CKEDITOR Content *}
                {include file="./content_elems/email_content.tpl"}

                {* CKEDITOR Discount *}
                {include file="./content_elems/email_discount.tpl"}

                {* Call to action *}
                {include file="./content_elems/email_cta.tpl"}

                {* reassurance block *}
                {include file="./content_elems/email_reassurance.tpl"}

                {* Social Networks *}
                {include file="./content_elems/email_socials.tpl"}

                {* CKEDITOR Unsubscribe *}
                {include file="./content_elems/email_unsubscribe.tpl"}
            </section>
        {/foreach}
    </div>
</div>
