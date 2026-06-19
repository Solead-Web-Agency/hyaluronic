/**
 * OrderEdit
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @license   See joined file licence.txt
 * @support   silbersaiten <support@silbersaiten.de>
 * @category  Module
 * @version   2.0.1
 * @link      http://www.silbersaiten.de
 */

var orderEditList = {
    init: function () {
        this.replaceIcon();
    },
    replaceIcon: function () {
        var list = $('#order_filter_form').find('a .material-icons');
        list.each(function () {
            if ($(this).text() === 'zoom_in') {
                $(this).parent().attr('data-original-title', 'Edit');
                $(this).text('edit');
            }
        });
    },
    createOrderEditButton: function (edit_link) {
        return $(document.createElement('a')).attr({'href': edit_link + '&edit_order=1'})
            .addClass('btn tooltip-link dropdown-item inline-dropdown-item')
            .html('<i class="material-icons">edit</i>');
    }
};

document.addEventListener('DOMContentLoaded', function () {
    orderEditList.init();
});
