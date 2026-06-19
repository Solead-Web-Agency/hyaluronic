{*
* 2007-2016 PrestaShop
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
*  @author    SeoSA <885588@bk.ru>
*  @copyright 2012-2022 SeoSA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


<script>
    var delete_title = '{l s='Delete' mod='dgridproducts'}';
    var delete_desc = '{l s='Delete?' mod='dgridproducts'}';
    var delete_success = '{l s='Delete success' mod='dgridproducts'}';
    var delete_cancel = '{l s='Delete cancel' mod='dgridproducts'}';

    var duplicate_title = '{l s='Duplicate' mod='dgridproducts'}';
    var duplicate_desc = '{l s='Copy images?' mod='dgridproducts'}';
    var duplicate_success = '{l s='Images copied' mod='dgridproducts'}';
    var duplicate_cancel = '{l s='Images not copied' mod='dgridproducts'}';

    var action_enabled_title = '{l s='Active enabled title' mod='dgridproducts'}';
    var action_enabled_desc = '{l s='Active enabled desc?' mod='dgridproducts'}';
    var action_disabled_title = '{l s='Active disabled title' mod='dgridproducts'}';
    var action_disabled_desc = '{l s='Action disabled desc?' mod='dgridproducts'}';
    var action_disabled_success = '{l s='Active disabled success' mod='dgridproducts'}';
    var action_enabled_success = '{l s='Active enabled success' mod='dgridproducts'}';
    var active_cancel = '{l s='Active cancel' mod='dgridproducts'}';
</script>

<script type="text/javascript">
    function actionDelete(url_delete) {

        $.confirm({
            title: delete_title,
            content: delete_desc,
            buttons: {
                Yes: function () {
                    $.alert(delete_success);
                    setTimeout(function() {
                        $('body').find('.jconfirm').addClass('bootstrap');
                    }, 1);

                    document.location = url_delete;
                },
                No: function () {
                    $.alert(delete_cancel);
                    setTimeout(function() {
                        $('body').find('.jconfirm').addClass('bootstrap');
                    }, 1);

                }
            }
        });

        setTimeout(function() {
            $('body').find('.jconfirm').addClass('bootstrap');
        }, 1);
    }

    function actionDuplicate(url_duplicate,url_duplicate2) {

        $.confirm({
            title: duplicate_title,
            content: duplicate_desc,
            buttons: {
                Yes: function () {
                    $.alert(duplicate_success);
                    setTimeout(function() {
                        $('body').find('.jconfirm').addClass('bootstrap');
                    }, 1);

                    document.location = url_duplicate;
                },
                No: function () {
                    $.alert(duplicate_cancel);
                    setTimeout(function() {
                        $('body').find('.jconfirm').addClass('bootstrap');
                    }, 1);

                    document.location = url_duplicate2;
                }
            }
        });

        setTimeout(function() {
            $('body').find('.jconfirm').addClass('bootstrap');
        }, 1);
    }
</script>

<div class="custom_bootstrap grid_products_wrap">
<ul class="tabs">
    <li class="active">
        <a class="grid_prod_search" href="#grid_prod_search" data-toggle="tab">{l s='Search Products' mod='dgridproducts'}</a>
    </li>
    <li>
        <a class="grid_prod_doc" href="{$link_on_tab_module|escape:'quotes':'UTF-8'}">{l s='Setting grid' mod='dgridproducts'}</a>
    </li>
    <li>
        <a class="grid_prod_doc" href="{$link_on_tab_module|escape:'quotes':'UTF-8'}">{l s='Documentation' mod='dgridproducts'}</a>
    </li>
    <li>
        <a id="seosa_manager_btn" href="#">{l s='Our modules' mod='dgridproducts'}</a>
    </li>
</ul>

<script src='https://seosaps.com/ru/module/seosamanager/manager?ajax=1&action=script&iso_code={Context::getContext()->language->iso_code|escape:'quotes':'UTF-8'}'></script>

