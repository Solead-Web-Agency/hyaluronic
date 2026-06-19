/**
 * OrderEdit
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @version   2.0.27
 * @link      https://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

var orderEditSearcher = {
    init: function () {
        this.runPSBugFix();
        if (show_search_field) {
            this.createSearchField();
        }
        this.listeners();
    },
    runPSBugFix: function () {
        if (!$.hasOwnProperty('browser')) {
            $.browser = {};
        }
    },
    listeners: function () {},
    createSearchField: function () {
        var row = $(document.createElement('div'))
            .addClass('find_order_field_container card-header')
            .append($(document.createElement('b')).text(search_field))
            .append(
                $(document.createElement('input')).attr({
                    'type': 'text',
                    'id': 'find_the_order',
                    'placeholder': search_field_placeholder
                }).addClass('find_order_field form-control col-3')
            );

        var insert_before = $('#order_grid_panel').closest('.row');
        row.insertBefore(insert_before);
        this.registerAutocomplete();
    },
    registerAutocomplete: function () {
        $('#find_the_order').autocomplete(order_edit_admin_link, {
            dataType: 'json',
            minChars: 1,
            autoFill: false,
            max: 20,
            matchContains: true,
            mustMatch: true,
            scroll: false,
            cacheLength: 0,
            parse: function (data) {
                var array = new Array();
                if (data != null) {
                    for (var i = 0; i < data.length; i++) {
                        array[array.length] = {
                            data: data[i],
                            value: data[i].id,
                            result: data[i].name
                        };
                    }
                }

                return array;
            },
            formatItem: function (item) {
                return '<a href="' + item.link + $('body').data('token') + '">' +
                    (item.id_order ? '#' + item.id_order + ' ' : '') +
                    (item.reference ? '#' + item.reference + ' ' : '') +
                    (item.invoice_number ? '#' + item.invoice_number + ' ' : '') +
                    (item.name ? '#' + item.name + ' ' : '') +
                    (item.firstname ? '#' + item.firstname + ' ' : '') +
                    (item.lastname ? '#' + item.lastname + ' ' : '') +
                    (item.company ? '#' + item.company : '') +
                    '</a>';
            },
            extraParams: {
                action: 'searchOrder',
                ajax: true
            }
        }).result(function (event, data, formatted) {
            window.location.href = data.link + $('body').data('token');
        });
    }
};

$(function () {
    orderEditSearcher.init();
});
