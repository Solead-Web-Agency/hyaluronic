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

<div id="email_content" class="row clear">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Email content' mod='pscartabandonmentpro'}</label>
    </div>
    <div class="col-lg-10 col-xs-10">
        <div class="cap-lang-form">
            <textarea name="email_content_{$lang.id_lang|intval}"  class="cap-editor email_content {if isset($template_datas[$lang.id_lang]['email_content'])}has_content{/if}">
                {if isset($template_datas[$lang.id_lang]['email_content'])}
                    {$template_datas[$lang.id_lang]['email_content'] nofilter}
                {else}
                    <p>{$email_content_default[0]} {ldelim}first_name{rdelim} {ldelim}last_name{rdelim},<br></p><p><br></p>
                    <p>{$email_content_default[1]}&nbsp;</p><p> {ldelim}cart{rdelim}<br></p>
                {/if}
            </textarea>
        </div>
    </div>
    <div class="col-lg-10 col-xs-10">
        <p>{l s='Add the following tags to customize your message' mod='pscartabandonmentpro'}</p>
    </div>
    <div class="col-lg-10 col-xs-10">
        {foreach from=$custom_content key=name item=content}
            <button class="email_content_custom" data-content="{$content}" data-type="content">
                <i class="material-icons">add_circle</i>
                {$name}
            </button>
        {/foreach}
    </div>
</div>
