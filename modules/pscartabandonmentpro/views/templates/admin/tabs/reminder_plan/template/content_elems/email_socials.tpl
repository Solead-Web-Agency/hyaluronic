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

<div class="row clear cap-email-socials">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Social networks links' mod='pscartabandonmentpro'}</label>
    </div>
    <div id="email_link_facebook" class="input-group col-lg-10 col-xs-10">
        <span class="input-group-addon"><img src="{$img_url}/templates/social/facebook_revert.png"/></span>
        <input type="text" autocomplete="off" data-social="facebook" class="form-control cap-lang-form" name="email_link_facebook"  placeholder="URL to your Facebook page (optional)" 
            value="{if isset($template_datas[$lang.id_lang]['email_link_facebook'])}{$template_datas[$lang.id_lang]['email_link_facebook']}{/if}"
        />
    </div>
    <div id="email_link_twitter"  class="input-group col-lg-10 col-xs-10">
        <span class="input-group-addon"><img src="{$img_url}/templates/social/twitter_revert.png"/></span>
        <input type="text" autocomplete="off" data-social="twitter" class="form-control cap-lang-form" name="email_link_twitter"  placeholder="URL to your Twitter page (optional)" 
            value="{if isset($template_datas[$lang.id_lang]['email_link_twitter'])}{$template_datas[$lang.id_lang]['email_link_twitter']}{/if}"
        />
    </div>
    <div id="email_link_instagram" class="input-group col-lg-10 col-xs-10">
        <span class="input-group-addon"><img src="{$img_url}/templates/social/instagram_revert.png"/></span>
        <input type="text" autocomplete="off" data-social="instagram" class="form-control cap-lang-form" name="email_link_instagram"  placeholder="URL to your Instagram page (optional)" 
            value="{if isset($template_datas[$lang.id_lang]['email_link_instagram'])}{$template_datas[$lang.id_lang]['email_link_instagram']}{/if}"
        />
    </div>
</div>