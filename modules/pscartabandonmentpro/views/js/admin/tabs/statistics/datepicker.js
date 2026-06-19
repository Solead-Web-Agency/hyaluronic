/**
* 2007-2019 PrestaShop
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

$(() => {
    $("#from").datepicker({
        defaultDate: "+0w",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onSelect: function() { 
            let dateFrom = $(this).datepicker({ dateFormat: 'yy-mm-dd' }).val();
            let dateTo = $("#to").datepicker({ dateFormat: 'yy-mm-dd' }).val();
            ajaxLoadDatasByDateFromTo(dateFrom, dateTo);
        }
    });
    
    $("#to").datepicker({
        defaultDate: "+0w",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onSelect: function() { 
            let dateFrom = $('#from').datepicker({ dateFormat: 'yy-mm-dd' }).val();
            let dateTo = $(this).datepicker({ dateFormat: 'yy-mm-dd' }).val();
            ajaxLoadDatasByDateFromTo(dateFrom, dateTo);
        }
    });

});