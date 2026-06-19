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
$(document).ready((function(){var n,e;n=$(".pp_admin_ajax_url").attr("href"),e={_ajax:1,popinRefund:1},$.ajax({type:"POST",url:n,dataType:"json",data:e,error:function(n,e,a){alert("error CALL INFO REFUND"),console.log(n,e,a)},success:function(n){sanitizePopupHtml(n.content)&&($("body").append(n.content),$(".ppclose, span.ppcancel").bind("click",(function(){$("#payplug_popin").remove(),$(".ppoverlay").remove()})))}})}));