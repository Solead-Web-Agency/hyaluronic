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
    var stack = [];
    var length_stack = 0;
    var callbacks = {
        ready: null
    };

    function show() {
        if (!$('body .loading_popup').length) {
            $('body').append('<div class="loading_popup">' +
                '<div class="title_block">'+Translator().l('Please wait, loading', 'mep')+':</div>' +
                '<div class="progress_stack"></div>' +
                '<div class="current_action"></div>' +
            '</div>')
        }
    }

    function hide() {
        $('body .loading_popup').remove();
    }

    function $popup() {
        var popup = $('body .loading_popup');

        var functions = {
            setProgressStack: function (length, leave) {
                var text = (length - leave) + '/' + length;
                popup.find('.progress_stack').html(text);
                return functions;
            },
            setCurrentAction: function (text) {
                popup.find('.current_action').html(text);
                return functions;
            }
        };
        return functions;
    }

    window.modulePreloader = function () {
        var functions = {
            add: function (callback, text, tab_name) {
                stack.push({
                    ajax: callback,
                    text: text,
                    tab_name: (typeof tab_name != 'undefined' ? tab_name : null)
                });
                length_stack++;
            },
            init: function () {
                if (!stack.length) {
                    hide();
                    length_stack = 0;
                    stack = [];
                    functions.ready();
                    return false;
                }
                var tab = stack.shift();

                function loadTab(tab) {
                    var tab_name = tab.tab_name;
                    show();
                    $popup().setProgressStack(
                        length_stack,
                        stack.length
                    ).setCurrentAction(tab.text);
                    tab.ajax(function () {
                        functions.stepStack(tab_name);
                        if (!stack.length) {
                            hide();
                            length_stack = 0;
                            stack = [];
                            functions.ready();
                            return false;
                        }
                        var tab = stack.shift();
                        loadTab(tab);
                    });
                }
                loadTab(tab);
            },
            ready: function () {
                if (callbacks.ready != null) {
                    return callbacks.ready();
                }
            },
            stepStack: function (tab_name)
            {
                if (tab_name != null && callbacks.step_stack != null) {
                    return callbacks.step_stack(tab_name);
                }
            },
            onReady: function (callback) {
                callbacks.ready = callback;
            },
            onStepStack: function (callback) {
                callbacks.step_stack = callback;
            }
        };

        return functions;
    };
})();