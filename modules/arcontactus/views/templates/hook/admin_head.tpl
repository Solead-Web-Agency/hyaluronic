{*
* 2018 Areama
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Areama <contact@areama.net>
*  @copyright  2018 Areama
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*}
{if $moduleConfig}
<script type="text/javascript">
    if (document.getElementById('maintab-AdminArCu')){
        document.getElementById('maintab-AdminArCu').classList.add("active");
    }else if(document.getElementById('subtab-AdminArCu')){
        document.getElementById('subtab-AdminArCu').classList.add("active");
        document.getElementById('subtab-AdminArCu').classList.add("-active");
        document.getElementById('subtab-AdminArCu').classList.add("ul-open");
        document.getElementById('subtab-AdminArCu').classList.add("open");
        var arCUIcon = document.getElementById('subtab-AdminArCu').querySelector('a i');
        if (arCUIcon && arCUIcon.innerHTML == ''){
            arCUIcon.innerHTML = 'link';
        }
    }
    if (document.getElementById('maintab-AdminParentModules')){
        document.getElementById('maintab-AdminParentModules').classList.remove("active");
    }
    if (document.getElementById('subtab-AdminParentModulesSf')){
        document.getElementById('subtab-AdminParentModulesSf').classList.remove("active");
        document.getElementById('subtab-AdminParentModulesSf').classList.remove("-active");
        document.getElementById('subtab-AdminParentModulesSf').classList.remove("ul-open");
        document.getElementById('subtab-AdminParentModulesSf').classList.remove("open");
    }
</script>
{/if}

<script type="text/javascript">
    window.addEventListener('load', function() {
        {foreach $orderButtons as $btn}
            {if $is16}
                let $btn = '<a class="btn btn-default" href="{$btn.url}" target="_blank"><i class="{$btn.icon}"></i> {$btn.title}</a>';
                $('.well.hidden-print:first').prepend($btn);
            {/if}
            let $tBtn = '<li><a id="page-header-desc-order-whatsapp" target="_blank" class="toolbar_btn" href="{$btn.url}" title="{$btn.title}"><i class="{$btn.icon} bo"></i><div> {$btn.title}</div></a></li>';
            $('#toolbar-nav').prepend($tBtn);
        {/foreach}
    });
</script>

<style type="text/css">
    .icon-AdminArCu:before{
        content:"";
    }
    .icon-whatsapp.bo {
        display: block;
        width: 30px;
        height: 30px;
        margin: 0 auto;
        font-size: 28px;
        background: transparent;
        background-position-x: 0%;
        background-position-y: 0%;
        background-size: auto;
        background-size: 26px;
        background-position: center;
    }
</style>