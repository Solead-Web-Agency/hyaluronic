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

<div id="reminder_email_template" class="steps_panel form-group col-lg-12">
    <script>
        var color1 = '{$color1}';
        var color2 = '{$color2}';
    </script>
    <script src="{$pickr}"></script>
    <div class="col-lg-5">
        {include file="./template/appearance.tpl"}
        {include file="./template/content.tpl"}
    </div>
    <div id="template_live-edit" class="col-lg-7">
        {include file="./template/live.tpl"}
    </div>
</div>
{include file="./popin/save_error_popin.tpl"}
{include file="./template/reassurance_block.tpl"}
