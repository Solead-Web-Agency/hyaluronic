{*
* 2007-2017 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $ps_version == 0}
    <div class="bootstrap">
    <!-- Beautiful header -->
    {include file="./header.tpl"}
{/if}
    <!-- Module content -->
    <div id="modulecontent" class="clearfix">
        {if $submit}
            <div class="alert alert-success alert-dismissable">
                {l s='You successfully updated the settings' mod='worldline'}
            </div>
        {/if}
        <!-- Nav tabs -->
        <div class="col-lg-2">

            <div class="list-group">
                <a href="#documentation" class="list-group-item{if !$submit} active{/if}" data-toggle="tab"><i class="icon-book"></i> {l s='Documentation' mod='worldline'}</a>
                <a href="#config" class="list-group-item{if $submit} active{/if}" data-toggle="tab"><i class="icon-briefcase"></i> {l s='Configuration' mod='worldline'}</a>
                {if !empty($apifaq)}
                <a href="#help" class="list-group-item{if $submit} active{/if}" data-toggle="tab"><i class="icon-question-circle"></i> {l s='FAQ' mod='worldline'}</a>
                {/if}
                <a href="#contacts" class="list-group-item" data-toggle="tab"><i class="icon-envelope"></i> {l s='Contact' mod='worldline'}</a>
            </div>
        </div>
        <!-- Tab panes -->
        <div class="tab-content col-lg-10">
          <!-- Info configutation -->
      		<div class="alert alert-info">
      			{l s='Make sure your payment module is working correctly : Let our expert team handle your module installation and configuration !' mod='worldline'}<br>
      			<a target="_blank" href="https://addons.prestashop.com/fr/support/186-installation-et-configuration-de-module-de-paiement-par-prestashop.html{$tracking_url_install|escape:'quotes':'UTF-8'}"> {l s='Learn more here >' mod='worldline'}</a>
      		</div>
            <div class="tab-pane panel {if !$submit}active{/if}" id="documentation">
                {include file="./tabs/documentation.tpl"}
            </div>

            <div class="tab-pane panel{if $submit} active{/if}" id="config">
                {include file="./tabs/config.tpl"}
            </div>

            {if !empty($apifaq)}
            <div class="tab-pane panel {if $submit}active{/if}" id="help">
                {include file="./tabs/help.tpl"}
            </div>
            {/if}

            {include file="./addons.tpl"}
        </div>
    </div>

{if $ps_version == 0}
    <!-- Manage translations -->
    {include file="./translations.tpl"}
</div>
{/if}
