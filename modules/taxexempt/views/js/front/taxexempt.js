/*
 * 2007-2023 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

// setInterval(function(){ 
//   jQuery('span.label,p,td').each(function(){
//     var replaced = $(this).html().replace("("+taxexcl_text+")",'');
//     $(this).html(replaced);
//     var replaced = $(this).html().replace("("+taxincl_text+")",'');
//     $(this).html(replaced);
//     var replaced = $(this).html().replace(taxexcl_text,'');
//     $(this).html(replaced);
//     var replaced = $(this).html().replace(taxincl_text,'');
//     $(this).html(replaced);
//   });
// }, 1000);

$(window).bind("load", function() { 
  jQuery('span.label,p,td').each(function(){
    var replaced = $(this).html().replace("("+taxexcl_text+")",'');
    $(this).html(replaced);
    var replaced = $(this).html().replace("("+taxincl_text+")",'');
    $(this).html(replaced);
    var replaced = $(this).html().replace(taxexcl_text,'');
    $(this).html(replaced);
    var replaced = $(this).html().replace(taxincl_text,'');
    $(this).html(replaced);
  });  
});

jQuery(document).ready(checkModal);

function checkModal () {
  if(($('#layer_cart').is(':visible')) || ($('#blockcart-modal').is(':visible'))) { //if the container is visible on the page
    jQuery('span.label,p,td').each(function(){
      var replaced = $(this).html().replace("("+taxexcl_text+")",'');
      $(this).html(replaced);
      var replaced = $(this).html().replace("("+taxincl_text+")",'');
      $(this).html(replaced);
      var replaced = $(this).html().replace(taxexcl_text,'');
      $(this).html(replaced);
      var replaced = $(this).html().replace(taxincl_text,'');
      $(this).html(replaced);
    });
  } else {
    setTimeout(checkModal, 50); //wait 50 ms, then try again
  }
}