/**
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
 * @author    SeoSA <885588@bk.ru>
 * @copyright 2012-2020 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

function TabContainer(tab_container_selector)
{
    var _this = this;
    this.tab = $(tab_container_selector);
    this.init = function () {
        $('#title_create_tabs').hide();
        _this.tab.find('ul.tabs > li').live('click', function () {
            $('.tabs_content > .panel-heading').hide();
            if ($(this).data('action') == 'create_products') {
                $('#title_create_tabs').show();
            } else {
                $('#title_edit_tabs').show();
            }
            _this.tab.find('.tabs > li').removeClass('active');
            $(this).addClass('active');
            _this.tab.find('.tabs_content > div').hide();
            var id_tab = $(this).data('tab').replace('tab', '');
            if (id_tab == 'price' || id_tab == 'quantity' || id_tab || id_tab == 'discount' || id_tab == 'image' || id_tab == 'delivery')
                $('.table_selected_products [data-combinations]').show();
            else
                $('[data-combinations]').hide();
            _this.tab.find('[id="'+$(this).data('tab')+'"]').show();
            _this.supportTab($(this));
        });
        _this.tab.find('.tabs_content > div').hide();
        _this.tab.find('.tabs_content > div:first').show();
        _this.tab.find('ul.tabs > li:first').addClass('active');
    }

    this.supportTab = function (tab) {
        var id_tab = tab.data('tab').replace('tab', '');

        if (id_tab == 'image') {
            createListPositionsForImageCaption(window.popup_form, true);
        }
    }
}