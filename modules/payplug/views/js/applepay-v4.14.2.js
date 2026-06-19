(()=>{
/**
 * 2013 - 2024 Payplug SAS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to contact@window[module_name+'Module'].com so we can send you a copy immediately.
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
var e=null;window[module_name+"ModuleApplePay"]={init:function(){$("apple-pay-button").click((function(){if(null!=e)return;var t="";$.ajax({method:"POST",url:applePayPaymentRequestAjaxURL,async:!1,success:function(e){t=JSON.parse(e)},error:function(){$("#apple-pay-button").css("pointer-events","auto"),e=null,payplugModule.popup.set(payplug_transaction_error_message)}});const a=t.applePayPaymentRequest;e=new ApplePaySession(3,a);var n=null;$.ajax({method:"POST",url:applePayMerchantSessionAjaxURL,data:{method:"applepay",id_cart:applePayIdCart},beforeSend:function(){$("#apple-pay-button").css("pointer-events","none")},success:function(t){if(!(t=JSON.parse(t)).result)return console.log(t.error_message),$("#apple-pay-button").css("pointer-events","auto"),e=null,void payplugModule.popup.set(payplug_transaction_error_message);try{t.apiResponse.merchant_session;n=t.idPayment;var a=t.idCart}catch(e){return console.error(e),void payplugModule.popup.set(payplug_transaction_error_message)}e.onvalidatemerchant=async a=>{try{e.completeMerchantValidation(t.apiResponse.merchant_session)}catch(e){console.error(e)}},e.onshippingmethodselected=t=>{e.completeShippingMethodSelection({})},e.onshippingcontactselected=t=>{e.completeShippingContactSelection({})},e.onpaymentauthorized=t=>{$.ajax({method:"POST",url:payplug_ajax_url,data:{_ajax:1,token:t.payment.token,pay_id:n,cart_id:a,patchPayment:1},success:function(t){if(!(t=JSON.parse(t)).result)return e.completePayment({status:ApplePaySession.STATUS_FAILURE}),$("#apple-pay-button").css("pointer-events","auto"),e=null,void payplugModule.popup.set(payplug_transaction_error_message);e.completePayment({status:ApplePaySession.STATUS_SUCCESS}),window.location.replace(t.return_url)},error:function(){$("#apple-pay-button").css("pointer-events","auto"),e=null,payplugModule.popup.set(payplug_transaction_error_message)}})},e.oncancel=t=>{$("#apple-pay-button").css("pointer-events","auto"),e=null,console.log("payment cancel")},e.begin()},error:function(){$("#apple-pay-button").css("pointer-events","auto"),e=null,payplugModule.popup.set(payplug_transaction_error_message)}})}))}},$(document).ready((function(){$("#payment-confirmation button").click((function(){var e=$('.js-current-step .custom-radio input[type="radio"]:checked').attr("id");"applepay"==$("#pay-with-"+e+'-form input[name="method"]').val()&&$("apple-pay-button").click()}))}))})();