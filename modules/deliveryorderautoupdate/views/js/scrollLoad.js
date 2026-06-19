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
class ScrollLoad {
    constructor(e) {
        this.element = e;
        this.element.data('scrollLoad', this);
        let initLoad = this.element.data('initload');
        if (initLoad) {
            this.stop = false;
        } else {
            this.stop = true;
        }
        this.loading = false;
        this.page = 0;
        if (this.element.attr('data-loading') !== undefined &&
            parseInt(this.element.attr('data-loading')) == 0)
            this.stop = true;
    }
    visible($t) {
        let $w            = $(window);
        let viewTop       = $w.scrollTop();
        let viewBottom    = viewTop + $w.height();
        let _top          = $t.offset().top;
        let _bottom       = _top + $t.height();
        let compareTop    = _bottom;
        let compareBottom = _top;
        let panel = $($t).closest('[id^="fieldset"]');
        let active = panel.hasClass('active');
        return active && ((compareBottom <= viewBottom) && (compareTop >= viewTop));
    }
    setEvent(callback) {
        $(window).scroll(() => {
            if (this.element.length) {
                if (!this.stop && !this.loading && this.visible(this.element)) {
                    this.loading = true;
                    callback(this);
                }
            }
        })
    }

}
// var loading = false;
// var stop = false;
$(document).ready(function() {
    $loading = $('#loading');
    // if ($loading.attr('data-loading') !== undefined && parseInt($loading.attr('data-loading')) == 0)
    //     stop = true;
    // function visible($t) {
    //     let $w            = $(window);
    //     let viewTop       = $w.scrollTop();
    //     let viewBottom    = viewTop + $w.height();
    //     let _top          = $t.offset().top;
    //     let _bottom       = _top + $t.height();
    //     let compareTop    = _bottom;
    //     let compareBottom = _top;
    //     return ((compareBottom <= viewBottom) && (compareTop >= viewTop));
    // }

    var url_ajax = $("input[name='admin_url']").val();
    var secure_key = $('input[name=token]').val();
    var scrollLoadDelay = new ScrollLoad($('#loadingDelay'));
    var scrollLoadReturn = new ScrollLoad($('#loadingReturn'));
    var scrollLoadIssue = new ScrollLoad($('#loadingIssue'));
    scrollLoadDelay.setEvent(function(obj) {
        delayLoadmore(obj);
    })
    scrollLoadReturn.setEvent(function(obj) {
        returnLoadmore(obj);
    })
    scrollLoadIssue.setEvent(function(obj) {
        issueLoadmore(obj);
    })
    var scrollLoadOrder = new ScrollLoad($loading);
    scrollLoadOrder.setEvent(function(obj) {
        let $loading = obj.element;
        $loading.find('img').show();
        filter = getTrackFilter();
        $.ajax({
            type: 'POST',
            url: url_ajax,
            data: {
                controller : 'AdmindeliveryorderautoupdateAjax',
                action : 'loadMore',
                ajax : true,
                page : obj.page,
                secure_key: secure_key,
                filter: filter
            },
            success: function(data) {
                data = JSON.parse(data);
                obj.page = data.p;
                $loading.find('img').hide();
                if (data.html && data.p <= data.pages_nb) {
                    data.html = $(data.html);
                    $('.del_message').append(data.html);
                    reindex();
                    data.html.find('.parcel_number').track({
                        onSelect: saveTrackingNumber
                    });
                }
                if (data.p >= data.pages_nb) {
                    obj.stop = true;
                }
                obj.loading = false;
            },
        });
    })
})
var setScrollLoad = function($e) {
    $('#track_list, #email_list').scroll(function() {
        $e = $(this);
        if($(this).scrollTop() + $(this).innerHeight() >= $(this).get(0).scrollHeight) {
            if (!$(this).data('loading')) {
                $(this).data('loading', true);
                page = parseInt($e.attr('page')) + 1;
                id_order_carrier = $e.attr('data-id_order_carrier');
                total = $e.attr('total');
                if (page < total) {
                    if ($e.attr('id') == 'track_list') {
                        showHistory(id_order_carrier, page, p => {
                            $(this).data('loading', false);
                        });
                    } else if ($e.attr('id') == 'email_list') {
                        showEmailHistory(id_order_carrier, page, p => {
                            $(this).data('loading', false);
                        });
                    }
                }
            }
        }
    })
}

function returnLoadmore(scrollLoad) {
    let returnPanel = $('#return');
    var id_return = returnPanel.find("input[name=filter_id_return]").val();
    var id_order = returnPanel.find("input[name=filter_id_order]").val();
    var id_order_return = returnPanel.find("select[name='filter_id_order_return']").val();
    var customer = returnPanel.find("input[name=filter_customer]").val().trim();
    var tracking_number = returnPanel.find("input[name='filter_tracking_number']").val().trim();
    var connector = returnPanel.find("select[name='filter_connector']").val();
    var status = returnPanel.find("select[name=filter_status]").val();
    $loading = returnPanel.find('.loading');
    $loading.find('img').show();
    $.ajax({
        type: 'POST',
        url: url_ajax,
        data: {
            controller : 'AdmindeliveryorderautoupdateAjax',
            action : 'loadMoreReturn',
            ajax : true,
            secure_key: secure_key,
            page : scrollLoad.page,
            filter: {
                id_return: id_return,
                id_order: id_order,
                id_order_return: id_order_return,
                customer: customer,
                tracking_number: tracking_number,
                connector: connector,
                status: status,
            }
        },
        success: function(data) {
            data = JSON.parse(data);
            scrollLoad.page = data.p;
            if (data.html) {
                data.html = $(data.html);
                $('.return').append(data.html);
                $('.return').find('.shipping_number').track({
                    onSelect: saveTrackingNumberReturn
                });
                reindex();
            }
            if (data.p >= data.pages_nb) {
                scrollLoad.stop = true;
            } else {
                scrollLoad.stop = false;
            }
            scrollLoad.loading = false;
            updateBulkStatusReturn();
            $loading.find('img').hide();
        }
    })
}
function issueLoadmore(scrollLoad) {
    let issuePanel = $('#issue');
    var id_issue = issuePanel.find("input[name=filter_id_issue]").val();
    var id_order = issuePanel.find("input[name=filter_id_order]").val();
    var customer = issuePanel.find("input[name=filter_customer]").val().trim();
    var service = issuePanel.find("select[name='filter_service']").val();
    var issue_type = issuePanel.find("select[name='filter_issue_type']").val();
    var status = issuePanel.find("select[name=filter_status]").val();
    $loading = issuePanel.find('.loading');
    $loading.find('img').show();
    $.ajax({
        type: 'POST',
        url: url_ajax,
        data: {
            controller : 'AdmindeliveryorderautoupdateAjax',
            action : 'loadMoreIssue',
            ajax : true,
            secure_key: secure_key,
            page : scrollLoad.page,
            filter: {
                id_issue: id_issue,
                id_order: id_order,
                service: service,
                customer: customer,
                issue_type: issue_type,
                status: status,
            }
        },
        success: function(data) {
            data = JSON.parse(data);
            scrollLoad.page = data.p;
            if (data.html) {
                data.html = $(data.html);
                $('.issue').append(data.html);
            }
            if (data.p >= data.pages_nb) {
                scrollLoad.stop = true;
            } else {
                scrollLoad.stop = false;
            }
            scrollLoad.loading = false;
            $loading.find('img').hide();
        }
    })
}