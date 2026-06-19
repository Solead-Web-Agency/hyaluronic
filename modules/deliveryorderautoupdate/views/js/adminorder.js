/**
* 2007-2023 Helloshop
*
* Tracking Center
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

$(document).ready(function(){
    if (url_ajax == undefined)
        return;
    $('#order_grid_table').addClass('order');
    tbl = $('table.order tbody');
    orders = tbl.find('tr').get();
    ids = orders.map((e, i) => {
        id = parseFloat($(e).find('td:eq(1)').text());
        $(e).addClass(`hldt_order_${id}`);
        return id;
    });
    if (!ids.length)
        return;
    $.ajax({
         type: 'POST',
         url: url_ajax,
         data: {
            controller : 'AdmindeliveryorderautoupdateAjax',
            action : 'GetOrderTracks',
            ids : ids,
            secure_key: secure_key,
            ajax: 1,
         },
         success: function(data)
         {
            data = JSON.parse(data);
            data.forEach(d => {
                td = $(`tr.hldt_order_${d.id_order}`).find('td:eq(8)');
                td.append(d.track);
                td.css('position', 'relative');
            });
         }
     });
});
