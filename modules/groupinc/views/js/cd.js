/**
* Price increment/discount by groups, categories and prices
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*/

function displayCountdown(countdown, countdown_selector, today, day_txt, hour_txt, minute_txt, second_txt)
{
    let countDownDate = Date.parse(countdown.replace(/ /g,"T")+'Z');
    let now = Date.parse(today.replace(/ /g,"T")+'Z');

    var x = setInterval(function() {
      var distance = countDownDate - now;
      let days = Math.floor(distance / (1000 * 60 * 60 * 24));
      let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      let seconds = Math.floor((distance % (1000 * 60)) / 1000);

      $(countdown_selector).each(function() {
        $(this).html(days + day_txt + ' ' + hours + hour_txt + ' ' + minutes + minute_txt + ' ' + seconds + second_txt);
      });

      if (distance < 0) {
          clearInterval(x);
          $(countdown_selector).html("");
        }
        now += 1000;
      }
      , 1000);
}