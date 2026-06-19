/**
 * 2013 - 2024 Payplug SAS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to contact@payplug.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PayPlug module to newer
 * versions in the future.
 *
 *  @author    PayPlug SAS
 *  @copyright 2013 - 2024 Payplug SAS
 *  @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PayPlug SAS
 */
$(document).ready((function(){if("undefined"!=typeof payplug_errors&&payplug_errors)return!1;var e=$("#payplug_form_js").data("payment-url");if(window.MSInputMethodContext&&document.documentMode)return window.location.href=e,!1;"undefined"!=typeof isIntegratedPayment&&isIntegratedPayment?Payplug.Form.showPayment(e):Payplug.showPayment(e)}));