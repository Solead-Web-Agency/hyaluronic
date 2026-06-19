{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    SeQura Tech <prestashop@sequra.com>
 * @copyright Since 2013 SeQura WorldWide SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
<script>
    var sequraProducts = [];
    {foreach from=$sequra_products key=k item=p}
        sequraProducts.push("{$p|escape:'javascript':'UTF-8'}");
    {/foreach}
    var sequraConfigParams = {
        merchant: "{$merchant|escape:'javascript':'UTF-8'}",
        assetKey: "{$assetKey|escape:'javascript':'UTF-8'}",
        products: sequraProducts,
        scriptUri: "{$scriptBaseUri|escape:'javascript':'UTF-8'}sequra-checkout.min.js",
        decimalSeparator: '{$decimalSeparator|escape:'javascript':'UTF-8'}',
        thousandSeparator: '{$thousandSeparator|escape:'javascript':'UTF-8'}',
        locale: '{$locale|escape:'javascript':'UTF-8'}',
        currency: 'EUR'
    };

    {literal}
        (function(i, s, o, g, r, a, m) {
            i['SequraConfiguration'] = g;
            i['SequraOnLoad'] = [];
            i[r] = {};
            i[r][a] = function(callback) { i['SequraOnLoad'].push(callback); };
            (a = s.createElement(o)), (m = s.getElementsByTagName(o)[0]);
            a.async = 1;
            a.src = g.scriptUri;
            m.parentNode.insertBefore(a, m);
        })(window, document, 'script', sequraConfigParams, 'Sequra', 'onLoad');

        //Helper
        var SequraHelper = {
                /**
             * The widgets to be drawn in the page
             */
                widgets: [],

                presets: {
                    L: '{"alignment":"left"}',
                    R: '{"alignment":"right"}',
                    legacy: '{"type":"legacy"}',
                    legacyL: '{"type":"legacy","alignment":"left"}',
                    legacyR: '{"type":"legacy","alignment":"right"}',
                    minimal: '{"type":"text","branding":"none","size":"S","starting-text":"as-low-as"}',
                    minimalL: '{"type":"text","branding":"none","size":"S","starting-text":"as-low-as","alignment":"left"}',
                    minimalR: '{"type":"text","branding":"none","size":"S","starting-text":"as-low-as","alignment":"right"}'
                },

                getText: function(selector) {
                    return selector && document.querySelector(selector) ? document.querySelector(selector).innerText :
                        "0";
                },
                nodeToCents: function(node) {
                    return this.textToCents(node ? node.innerText : "0");
                },
                selectorToCents: function(selector) {
                    return this.textToCents(SequraHelper.getText(selector));
                },
                decodeEntities: function(encodedString) {
                    if (!encodedString.match(/&(nbsp|amp|quot|lt|gt|#\d+|#x[0-9A-Fa-f]+);/g)) {
                        return encodedString;
                    }
                    const elem = document.createElement('div');
                    elem.innerHTML = encodedString;
                    return elem.textContent;
                },
                textToCents: function(text) {

                    const thousandSeparator = this.decodeEntities(sequraConfigParams.thousandSeparator);
                    const decimalSeparator = this.decodeEntities(sequraConfigParams.decimalSeparator);

                    text = text.replace(/^\D*/, '').replace(/\D*$/, '');
                    if (text.indexOf(decimalSeparator) < 0) {
                        text += decimalSeparator + '00';
                    }
                    return SequraHelper.floatToCents(
                        parseFloat(
                            text
                            .replace(thousandSeparator, '')
                            .replace(decimalSeparator, '.')
                        )
                    );
                },
                floatToCents: function(value) {
                    return parseInt(value.toFixed(2).replace('.', ''), 10);
                },

                refreshComponents: function() {
                    Sequra.onLoad(
                        function() {
                            Sequra.refreshComponents();
                        }
                    );
                },

            /**
            * Search for child elements in the parentElem that are targets of the widget
            * @param {object} parentElem DOM element that may contains the widget's targets
            * @param {object} widget  Widget object
            * @param {string} observedAt Unique identifier to avoid fetch the same element multiple times
            * @returns {array} Array of objects containing the target elements and a reference to the widget
            */
            getWidgetTargets: function(parentElem, widget, observedAt) {
                const targets = [];
                const isPriceMutation =
                'undefined' !== typeof parentElem.matches && (
                    parentElem.matches(widget.price_src) ||
                    parentElem.querySelector(widget.price_src)
                );
                const children = (isPriceMutation?document:parentElem).querySelectorAll(widget.css_sel);
                const productObservedAttr = 'data-sequra-observed-' + widget.product;
                for (const child of children) {
                    if (child.getAttribute(productObservedAttr) == observedAt) {
                        continue; // skip elements that are already observed in this mutation.
                    }
                    child.setAttribute(productObservedAttr, observedAt);
                    targets.push({ elem: child, widget: widget });
                }
                return targets;
            },

                /**
                * Search for all the targets of the widgets in a parent element
                * @param {object} parentElem DOM element that may contains the widget's targets
                * @param {array} widgets List of widgets to be drawn in the page
                * @param {string} observedAt Unique identifier to avoid fetch the same element multiple times
                * @returns {array} Array of objects containing the target elements and a reference to the widget
                */
                getWidgetsTargets: function(parentElem, widgets, observedAt) {
                    const targets = [];
                    for (const widget of widgets) {
                        const widgetTargets = this.getWidgetTargets(parentElem, widget, observedAt);
                        targets.push(...widgetTargets);
                    }
                    return targets;
                },

                /**
                * Get an unique identifier to avoid fetch the same element multiple times
                * @returns {number} The current timestamp
                */
                getObservedAt: () => Date.now(),

                /**
                 * Paint the widgets in the page and observe the DOM to refresh the widgets when the page changes.
                 * @param parentElem The DOM element that contains the promotion widgets
                 */
                drawWidgetsOnPage: function() {
                    if (!this.widgets.length) {
                        return;
                    }

                    // First, draw the widgets in the page for the first time.
                    const widgetsTargets = this.getWidgetsTargets(document, this.widgets, this.getObservedAt());
                    widgetsTargets.forEach(({ elem, widget }) => {
                        this.drawWidgetOnElement(widget, elem)
                    });

                    // Then, observe the DOM to refresh the widgets when the page changes.
                    const mutationObserver = new MutationObserver((mutations) => {
                        const targets = []; // contains the elements that must be refreshed.
                        const observedAt = this.getObservedAt();

                        for (const mutation of mutations) {
                            if (!['childList', 'subtree'].includes(mutation.type)) {
                                continue; // skip mutations that not are changing the DOM.
                            }

                            const widgetTargets = this.getWidgetsTargets(mutation.target, this.widgets,
                                observedAt)
                            targets.push(...widgetTargets);
                        }

                        mutationObserver
                            .disconnect(); // disable the observer to avoid multiple calls to the same function.

                        targets.forEach(({ elem, widget }) => this.drawWidgetOnElement(widget,
                            elem)); // draw the widgets.

                        mutationObserver.observe(document, {
                            childList: true,
                            subtree: true
                        }); // enable the observer again.
                    });

                    mutationObserver.observe(document, { childList: true, subtree: true });
                },

                drawWidgetOnElement: function(widget, element) {
                    const priceElem = document.querySelector(widget.price_src);
                    if (!priceElem) {
                        console.error(widget.price_src +
                            ' is not a valid css selector to read the price from, for sequra widget.');
                        return;
                    }
                    const cents = SequraHelper.nodeToCents(priceElem);

                    const className = 'sequra-promotion-widget';
                    const modifierClassName = className + '--' + widget.product;

                    const oldWidget = element.parentNode.querySelector('.' + className + '.' + modifierClassName);
                    if (oldWidget) {
                        if (cents == oldWidget.getAttribute('data-amount')) {
                            return; // no need to update the widget, the price is the same.
                        }

                        oldWidget.remove(); // remove the old widget to draw a new one.
                    }

                    const promoWidgetNode = document.createElement('div');
                    promoWidgetNode.className = className + ' ' + modifierClassName;
                    promoWidgetNode.setAttribute('data-amount', cents);
                    promoWidgetNode.setAttribute('data-product', widget.product);
                    const theme = this.presets[widget.theme] ? this.presets[widget.theme] : widget.theme;
                    try {
                        const attributes = JSON.parse(theme);
                        for (let key in attributes) {
                            promoWidgetNode.setAttribute('data-' + key, "" + attributes[key]);
                        }
                    } catch (e) {
                        promoWidgetNode.setAttribute('data-type', 'text');
                    }
                    if (widget.campaign) {
                        promoWidgetNode.setAttribute('data-campaign', widget.campaign);
                    }

                    if (element.nextSibling) {//Insert after
                    element.parentNode.insertBefore(promoWidgetNode, element.nextSibling);
                    this.refreshComponents();
                } else {
                    element.parentNode.appendChild(promoWidgetNode);
                }
            },

            waitForElememt: function(selector) {
                return new Promise(function(resolve) {
                    if (document.querySelector(selector)) {
                        return resolve();
                    }
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (!mutation.addedNodes)
                                return;
                            var found = false;
                            mutation.addedNodes.forEach(function(node) {
                                found = found || (node.matches && node.matches(selector));
                            });
                            if (found) {
                                resolve();
                                observer.disconnect();
                            }
                        });
                    });

                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });
                });
            }
        };
    {/literal}
</script>