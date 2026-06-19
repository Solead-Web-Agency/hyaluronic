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

(function () {
    var modules = {};
    window.Translator = function () {
        return {
            addTranslations: function (strings, module_name, module_short_key) {
                modules[module_short_key] = {
                    module_name: module_name,
                    translations: strings,
                    undefined: []
                }
            },
            l: function (string, module_short_key) {
                if (typeof modules[module_short_key] != 'undefined') {
                    if (typeof modules[module_short_key].translations[string] != 'undefined') {
                        return modules[module_short_key].translations[string];
                    } else {
                        modules[module_short_key].undefined.push(string);
                    }
                }
                return string;
            },
            dump: function (module_short_key) {
                if (typeof modules[module_short_key] != 'undefined') {
                    var smarty = 'var translations = {';
                    smarty += "\n";
                    for (var i = 0; i < modules[module_short_key].undefined.length; i++) {
                        var line = modules[module_short_key].undefined[i];
                        smarty += '"'+line+'": "{l s=\''+line+'\' mod=\''+modules[module_short_key].module_name+'\'}"';
                        if (i < modules[module_short_key].undefined.length - 1) {
                            smarty += ',';
                        }
                        smarty += "\n";
                    }
                    smarty += '};';

                    $('.translations-dump').remove();
                    $('#content').append('<pre class="translations-dump">'+smarty+'</pre>');
                }
            },
            getModules: function () {
                return modules;
            }
        };
    };
})();