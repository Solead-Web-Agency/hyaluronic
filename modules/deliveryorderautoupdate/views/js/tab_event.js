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

function setTabEvent() {
    $('.pick_ui a[data-toggle="tab"]').on('click', function (e) {
        let tab = $(e.target).attr('href');
        let load = parseInt($(e.target).attr("data-loading"));
        if (!load) {
            let id = $(this).attr('id');
            if (id == 'pick_ui_progress') {
                filterCarrier(1);
                checkUpdate();
                return;
            }
            let panel = $($(this).attr('href'));
            panel.find('tbody').empty();
            panel.find('.loading').html(loadingTable);
            $.ajax({
                type: 'POST',
                url: url_ajax,
                async: true,
                dataType: 'json',
                data: {
                    controller : 'AdmindeliveryorderautoupdateAjax',
                    action : 'loadTab',
                    ajax : true,
                    id: id,
                    secure_key: secure_key,
                },
                success: function(data)
                {
                    // if (id != 'pick_ui_issue') {
                    //     $(e.target).attr("data-loading", 1);
                    // }
                    setContent(tab, data);

                }
            });
        }
    });
}
function setDelayContent(data) {
    let panel = $('[id^=fieldset_delivered]');
    panel.find('.return').append(data.html);
    panel.find('.loading').empty();
    $('.preparation .time').html(data.avg.preparation);
    $('.transit .time').html(data.avg.transit);
    $('.total .time').html(data.avg.total);
    $('input[name="total[preparation]"]').val(data.total.preparation);
    $('input[name="total[transit]"]').val(data.total.transit);
    $('input[name="total[total]"]').val(data.total.total);
    $('input[name="count[preparation]"]').val(data.count.preparation);
    $('input[name="count[transit]"]').val(data.count.transit);
    $('input[name="count[total]"]').val(data.count.total);
}

function setContent(tab, data) {
    let panel = $(tab);
    panel.find('.table tbody').append(data.html);
    panel.find('.loading').empty();
    panel.find('.total').html(data.total);
    loading = panel.find('.loading');
    if (loading.data('scrollLoad')) {
        scrollLoad = loading.data('scrollLoad');
        if (data.p >= data.pages_nb) {
            scrollLoad.stop = true;
        } else {
            scrollLoad.stop = false;
        }
    } else {
        if (data.p >= data.pages_nb) {
            loading.data('initLoad', false);
        } else {
            loading.data('initLoad', true);
        }
    }
    if (tab.startsWith('#fieldset_delivered')) {
        $('.preparation .time').html(data.avg.preparation);
        $('.transit .time').html(data.avg.transit);
        $('.total .time').html(data.avg.total);
        $('input[name="total[preparation]"]').val(data.total.preparation);
        $('input[name="total[transit]"]').val(data.total.transit);
        $('input[name="total[total]"]').val(data.total.total);
        $('input[name="count[preparation]"]').val(data.count.preparation);
        $('input[name="count[transit]"]').val(data.count.transit);
        $('input[name="count[total]"]').val(data.count.total);
    } else if (tab.startsWith('#fieldset_return')) {
        panel.find('.shipping_number').track({
            onSelect: saveTrackingNumberReturn
        });
        updateBulkStatusReturn();
    }
}