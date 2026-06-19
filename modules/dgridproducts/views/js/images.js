/**
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
 * @copyright 2012-2022 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

function initTableImages() {
    var originalOrder = false;
    $("#imageTable").tableDnD({
        dragHandle: 'dragHandle',
        onDragClass: 'myDragClass',
        onDragStart: function(table, row) {
            originalOrder = $.tableDnD.serialize();
            reOrder = ':even';
            if (table.tBodies[0].rows[1] && $('#' + table.tBodies[0].rows[1].id).hasClass('alt_row'))
                reOrder = ':odd';
            $(table).find('#' + row.id).parent('tr').addClass('myDragClass');
        },
        onDrop: function(table, row) {
            if (originalOrder != $.tableDnD.serialize()) {
                current = $(row).attr("id");
                stop = false;
                image_up = "{";
                $("#imageList").find("tr").each(function(i) {
                    $("#td_" +  $(this).attr("id")).html('<div class="dragGroup"><div class="positions">'+(i + 1)+'</div></div>');
                    if (!stop || (i + 1) == 2)
                        image_up += '"' + $(this).attr("id") + '" : ' + (i + 1) + ',';
                });
                image_up = image_up.slice(0, -1);
                image_up += "}";
                updateImagePosition(image_up);
            }
        }
    });
}

function updateImagePosition(json)
{
    doUpdateAjax(
        {
            "action":"updateImagePosition",
            "json":json,
            "ajax" : 1
        });
}

function doUpdateAjax(data, success_func, error_func)
{
    $.ajax({
        url : ajax_url,
        data : data,
        type : 'POST',
        success : function(data){
            if (success_func)
                return success_func(data);

            data = $.parseJSON(data);
            if (data.confirmations.length != 0)
                showSuccessMessage(data.confirmations);
            else
                showErrorMessage(data.error);
        },
        error : function(data){
            if (error_func)
                return error_func(data);

            $.alert("[TECHNICAL ERROR]");
            setTimeout(function() {
                $('body').find('.jconfirm').addClass('bootstrap');
            }, 1);
        }
    });
}