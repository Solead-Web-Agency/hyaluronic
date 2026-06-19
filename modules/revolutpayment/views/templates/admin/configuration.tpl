{*
* Copyright since 2007 PrestaShop SA and Contributors
* PrestaShop is an International Registered Trademark & Property of PrestaShop SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.md.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* @author    Revolut
* @copyright Since 2020 Revolut
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

{include './instruction.tpl'}

<form id="configuration_form" class="defaultForm form-horizontal revolutpayment" action="" method="post" enctype="multipart/form-data" novalidate="">
    <input type="hidden" name="submitrevolutpayment" value="1"/>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item {if empty($section ) || $section=='settings'}active{/if}">
            <a class="nav-link {if empty($section ) || $section=='settings'}active{/if}" id="home-tab" data-toggle="tab" href="#settings" role="tab"
               aria-controls="settings" aria-selected="true"><i
                        class="icon-cogs"></i> {l s='Settings' mod='revolutpayment'}</a>
        </li>
        <li class="nav-item {if $section=='adv-settings'}active{/if}">
            <a class="nav-link {if $section=='adv-settings'}active{/if}" id="adv-settings-tab" data-toggle="tab" href="#adv-settings" role="tab"
               aria-controls="adv-settings" aria-selected="false"><i
                        class="icon-cogs"></i> {l s='Advanced settings' mod='revolutpayment'}</a>
        </li>
        <li class="nav-item {if $section=='prb-settings'}active{/if}">
            <a class="nav-link {if $section=='prb-settings'}active{/if}" id="prb-settings-tab" data-toggle="tab" href="#prb-settings" role="tab"
               aria-controls="prb-settings" aria-selected="false"><i
                        class="icon-cogs"></i> {l s='Apple Pay / Google Pay' mod='revolutpayment'}</a>
        </li>
    </ul>
    <div class="tab-content">
        {include './settings.tpl'}
        {include './adv-settings.tpl'}
        {include './prb-settings.tpl'}
    </div>
</form>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    $('#REVOLUT_PRB_HEIGHT').attr('type', 'number').attr('min', 0).attr('max', 50);

    $('#settings button').on('click', function (){
        $('#configuration_form').append('<input type="hidden" name="section" value="settings"/>')
    });

    $('#adv-settings button').on('click', function (){
        $('#configuration_form').append('<input type="hidden" name="section" value="adv-settings"/>')
    });

    $('button[name="submitPRBSettings"]').on('click', function (){
        $('#configuration_form').append('<input type="hidden" name="section" value="prb-settings"/>')
    });
</script>