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

<div id="sendy" class="show_template"
    {if (isset($template_appearance['model_name']) && $template_appearance['model_name'] != 'sendy')}
    style="display: none;"
    {/if}
>
    {include file="../../../emails/sendy.tpl"}
</div>
<div id="boxy" class="show_template" 
    {if (isset($template_appearance['model_name']) && $template_appearance['model_name'] != 'boxy') || !isset($template_appearance['model_name'])}
    style="display: none;"
    {/if}
>
    {include file="../../../emails/boxy.tpl"}
</div>
<div id="puffy" class="show_template" 
    {if (isset($template_appearance['model_name']) && $template_appearance['model_name'] != 'puffy') || !isset($template_appearance['model_name'])}
    style="display: none;"
    {/if}
>
    {include file="../../../emails/puffy.tpl"}
</div>
<div class="responsive form-control-label">
    <span class="device" onclick="openTemplateInDeviceWindow('smartphone')">
        <i class="material-icons">smartphone</i> {l s='Mobile view' mod='pscartabandonmentpro'}
    </span>
    <span class="device" onclick="openTemplateInDeviceWindow('tablet')">
        <i class="material-icons">tablet</i> {l s='Tablet view' mod='pscartabandonmentpro'}
    </span>
</div>