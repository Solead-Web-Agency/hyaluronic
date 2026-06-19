/*
 *    Module made by Nukium
 *
 *  @author    Nukium
 *  @copyright 2023 Nukium SAS
 *  @license   All rights reserved
 *
 * ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
 * ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
 * ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
 * ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
 * ██   ████  ██████  ██   ██ ██  ██████  ██      ██
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
$(document).ready(function() {

	$('form.AdminGlsLabel label[for^="GLS_LABEL_CARRIER_FILTER_"]:contains("[' + carrier_disabled + ']")').addClass('text-muted');

    $('.bulk-actions .dropdown-menu li:first > a').trigger('click');

    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    if($('input[name="gls_label_single_type"]').val() == 'return') {
        $('.packages .card-footer, .packages .packages-count, .packages .package-title').remove();
    }

    $('#table-labels tr:not(.extra-content)').on('click', function(e) {

        e.preventDefault();
        if($(this).parent('tbody').length > 0) {
            $(this).find('a.toggler').trigger('click');
        }

    });

    $('#table-labels th a.toggler').on('click', function(e) {

        e.preventDefault();

        if($(this).attr('aria-expanded') == 'false') {

            $('#table-labels a.toggler').attr('aria-expanded', 'true');
            $('#table-labels#table-labels a.toggler i').removeClass('icon-angle-down').addClass('icon-angle-up');
            $('.extra-content').each(function() {
                $(this).find('.extra-content-form').stop(true, false).slideDown();
            });

        } else {

            $('#table-labels a.toggler').attr('aria-expanded', 'false');
            $('#table-labels a.toggler i').removeClass('icon-angle-up').addClass('icon-angle-down');
            $('.extra-content').each(function() {
                $(this).find('.extra-content-form').stop(true, false).slideUp();
            });

        }

    });

    $('#table-labels td a.toggler').on('click', function(e) {

        e.preventDefault();
        e.stopImmediatePropagation();
        $(this).attr('aria-expanded', function(_, attr) { return !(attr == 'true') });
        $(this).children('i').toggleClass('icon-angle-down icon-angle-up');
        $(this).closest('tr').next().find('.extra-content-form').stop(true, false).slideToggle();
        return false;

    });

    if($('#table-labels tbody tr:not(.extra-content)').length == 1) {
        $('#table-labels tbody tr td a.toggler').trigger('click');
        $('#table-labels a.toggler').hide();
        $('#table-labels tr:not(.extra-content)').off('click');
    }

    function updatePrintingState(order_id) {

        var total_controls = 0;
        var completed_controls = 0;

        $('#extra-content-form-' + order_id + ' .form-group').each(function() {

            if($(this).css('display') !== 'none') {

                $(this).children('.control-label.required').next().children('.form-control').each(function() {

                    total_controls++;
                    if($(this).val() != '') {
                        completed_controls++;
                    }

                });
            }

        });

        if(total_controls > 0) {

            var progress = (completed_controls / total_controls) * 100;
            $('#printing-progress-' + order_id + ' .progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);

            if(progress == 100) {

                $('#printing-progress-' + order_id + ' .progress-bar').removeClass('bg-primary').addClass('bg-success');
                $('#btn-print-' + order_id).removeClass('btn-disabled').attr('title', print_ready).attr('data-original-title', print_ready).tooltip('fixTitle');

            } else {

                $('#printing-progress-' + order_id + ' .progress-bar').removeClass('bg-success').addClass('bg-primary');
                $('#btn-print-' + order_id).addClass('btn-disabled').attr('title', print_block).attr('data-original-title', print_block).tooltip('fixTitle');

            }

            if($('.btn-print:not(.btn-disabled)').length > 0) {
                $('#gls-btn-print-all').prop('disabled', false);
            } else {
                $('#gls-btn-print-all').prop('disabled', true);
            }

        }

    }

    $.fn.getOrderId = function() {

        if($(this).closest('.extra-content-form').length > 0) {

            return parseInt($(this).closest('.extra-content-form').attr('id').replace('extra-content-form-', ''));

        } else if($(this).closest('tr').next().hasClass('extra-content')) {

            return parseInt($(this).closest('tr').next().find('.extra-content-form').attr('id').replace('extra-content-form-', ''));

        }

    };

    var gls_label_single_type = $('input[name="gls_label_single_type"]').val();

    $('.btn-print').on('click', function(e, deferred) {

        e.preventDefault();
        e.stopImmediatePropagation();

        var toggler = $(this).closest('tr').children('td:first').children('.toggler');

        if($(this).hasClass('btn-disabled')) {

            if($(toggler).attr('aria-expanded') == 'false') {
                $(toggler).trigger('click');
            }

        } else if(!$(this).hasClass('btn-processing')) {

            // Disable double clic
            $(this).addClass('btn-processing');

            $(this).children('i').toggleClass('icon-print icon-spinner icon-spin');

            var order_id = $(this).getOrderId();

            $('input[name="gls_label_single_type"]').val(gls_label_single_type);
            $('local_print_'+order_id).val(0);

            $.ajax({
                type: 'POST',
                url: ajax_uri+'generateLabel',
                cache: false,
                dataType: 'json',
                context: this,
                data: $('#extra-content-form-' + order_id).serialize()
            }).done(function(jsonData) {
                if (jsonData.hasError === true) {
                    $('#btn-print-' + order_id).removeClass('btn-primary').addClass('btn-danger');
                    $('#btn-print-' + order_id).removeClass('btn-processing');
                    $('#btn-print-' + order_id).attr('title', jsonData.errors).tooltip('fixTitle').tooltip('show');
                    $(this).children('i').toggleClass('icon-print icon-spinner icon-spin');
                    if (typeof deferred !== 'undefined') {
                        manageMergePDFSteps('error');
                        deferred.resolve();
                    }
                } else if(jsonData.data) {

                    if(jsonData.data != 'local_print') {
                        for (var i = 0, len = jsonData.data['labels'].length; i < len; i++) {
                            downloadFile(jsonData.data['labels'][i], 'order-' + order_id + '-label-' + (i+1) + '.pdf');
                        }
                    }

                    if(
                        $('#extra-content-form-' + order_id).find('input[name="return_label"]:checked').val() !== undefined &&
                        $('#extra-content-form-' + order_id).find('input[name="return_label"]:checked').val() == '1'
                    ) {

                        $('input[name="gls_label_single_type"]').val('return_shipment');

                        $.ajax({
                            type: 'POST',
                            url: ajax_uri+'generateLabel',
                            cache: false,
                            dataType: 'json',
                            context: this,
                            data: $('#extra-content-form-' + order_id).serialize()
                        }).done(function(jsonData) {

                            if (jsonData.hasError === true) {

                                $('#btn-print-' + order_id).removeClass('btn-primary').addClass('btn-danger');
                                $('#btn-print-' + order_id).removeClass('btn-processing');
                                $('#btn-print-' + order_id).attr('title', jsonData.errors).tooltip('fixTitle').tooltip('show');
                                $(this).children('i').toggleClass('icon-print icon-spinner icon-spin');
                                if (typeof deferred !== 'undefined') {
                                    manageMergePDFSteps('error');
                                    deferred.resolve();
                                }

                            } else if (jsonData.data) {

                                if($(toggler).attr('aria-expanded') == 'true') {
                                    $(toggler).trigger('click');
                                }

                                $(this).closest('tr').next().find('.extra-content-form :input').prop('disabled', true);

                                var parent = $(this).parent();
                                parent.children('.btn-print').tooltip('destroy').remove();
                                parent.append('<span class="icon-stack icon-lg"><i class="icon icon-circle icon-stack-2x text-success"></i><i class="icon icon-check icon-stack-1x icon-inverse"></i></span>');

                                if (jsonData.data == 'local_print') {
                                    manageMergePDFSteps();
                                    if (typeof deferred !== 'undefined' && deferred.state() == "pending") {
                                        if (typeof deferred !== 'undefined') {
                                            deferred.resolve(order_id);
                                        }
                                    }

                                } else {

                                    for (var i = 0, len = jsonData.data['labels'].length; i < len; i++) {
                                        downloadFile(jsonData.data['labels'][i], 'order-' + order_id + '-label-' + (i+1) + '.pdf');
                                    }

                                }
                            }

                        }).fail(function() {

                            $(this).removeClass('btn-processing');
                            $(this).children('i').toggleClass('icon-print icon-spinner icon-spin');
                            // TODO display error
                            if (typeof deferred !== 'undefined') {
                                manageMergePDFSteps('error');
                                deferred.resolve();
                            }
                        });
                    } else {
                        if($(toggler).attr('aria-expanded') == 'true') {
                            $(toggler).trigger('click');
                        }

                        $(this).closest('tr').next().find('.extra-content-form :input').prop('disabled', true);

                        var parent = $(this).parent();
                        parent.children('.btn-print').tooltip('destroy').remove();
                        parent.append('<span class="icon-stack icon-lg"><i class="icon icon-circle icon-stack-2x text-success"></i><i class="icon icon-check icon-stack-1x icon-inverse"></i></span>');

                        if(jsonData.data == 'local_print') {
                            manageMergePDFSteps();
                            if (typeof deferred !== 'undefined' && deferred.state() == "pending") {
                                if (typeof deferred !== 'undefined') {
                                    deferred.resolve(order_id);
                                }
                            }
                        }
                    }
                }
            }).fail(function() {
                $(this).removeClass('btn-processing');
                $(this).children('i').toggleClass('icon-print icon-spinner icon-spin');
                // TODO display error
                if (typeof deferred !== 'undefined') {
                    manageMergePDFSteps('error');
                    deferred.resolve();
                }
            });
        }

    });

    var default_package_item = $('.extra-content-form:first .package:first').clone(true);

    $('.extra-content-form').each(function() {

        $(this).get(0).reset();
        updatePrintingState($(this).children().getOrderId());

    });

    $('body').on('input', '.extra-content-form .form-control', function() {

        updatePrintingState($(this).getOrderId());

    });

    $('.add-package').on('click', function(e) {

        e.preventDefault();

        var order_id = $(this).getOrderId();
        var package_item = default_package_item;
        var nb_packages = $(this).closest('.packages').find('.package').length + 1;
        package_item.find('.package-index').text(nb_packages);
        package_item.find('.remove-package').removeClass('d-none');
        $(this).closest('.packages').find('.card-body').append(package_item.clone(true));

        $(this).closest('.packages').find('.packages-count').text(nb_packages);

        updatePrintingState(order_id);

    });

    $('body').on('click', '.remove-package', function(e) {

        e.preventDefault();

        if($(this).closest('.packages').find('.package').length > 1) {

            var packages = $(this).closest('.packages');
            var order_id = $(this).getOrderId();

            $(this).closest('.package').remove();

            var count = 0;
            $(packages).find('.package').each(function() {
                count++;
                $(this).find('.package-index').text(count);
            });
            packages.find('.packages-count').text(count);

            updatePrintingState(order_id);

        }

    });

    $('.gls-service').on('change', function(e) {

        var mobile = $(this).children('option:selected').attr('data-mobile-required');

        if(typeof mobile !== 'undefined' && mobile === 'true') {

            $(this).closest('.form-group').siblings('.mobile-group').find('.form-control').prop('disabled', false);
            $(this).closest('.form-group').siblings('.mobile-group').stop(true, false).slideDown();

        } else {

            $(this).closest('.form-group').siblings('.mobile-group').stop(true, false).slideUp(function() {
                $(this).find('.form-control').prop('disabled', true);
            });

        }

        updatePrintingState($(this).getOrderId());

    });
    $('.gls-service').trigger('change');

    function downloadFile(base64str, filename) {

        var binary = atob(base64str.replace(/\s/g, ''));
        var blen = binary.length;
        var buffer = new ArrayBuffer(blen);
        var view = new Uint8Array(buffer);
        for (var n = 0; n < blen; n++) {
            view[n] = binary.charCodeAt(n);
        }
        var newBlob = new Blob( [view], { type: "application/pdf" });

        if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(newBlob, filename);
        } else {
            var url = URL.createObjectURL(newBlob);
            var a = document.createElement("a");
            document.body.appendChild(a);
            a.style = "display: none";
            a.href = url;
            a.download = filename;
            a.click();
            setTimeout(function() {
                URL.revokeObjectURL(url);
            }, 100);
        }
    }

    var printAllError;
    function manageMergePDFSteps(state) {

        if (typeof state == 'undefined') {
            state = 'success';
        }

        var loadCounter = parseInt($('#gls-btn-print-all').attr('data-counter'));

        $('#gls-btn-print-all').attr('data-counter', loadCounter - 1);
        $(this).children('i').toggleClass('icon-print icon-spinner icon-spin');

        var currentProgress = parseInt($('#modalMergeLabels .progress-bar').attr('aria-valuenow'));
        var progress = Math.round(currentProgress + ((100 - currentProgress) / loadCounter));

        $('#modalMergeLabels .progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
        if((currentProgress == 0) || (progress >= 100)) {
            if(currentProgress == 0) {
                printAllError = false;
                $('#modalMergeLabels .progress-steps > li:first-child .icon-circle').toggleClass('text-muted text-primary');
                $('#modalMergeLabels .progress-steps > li:first-child .icon-check').removeClass('hidden');
                $('#modalMergeLabels .progress-steps .total-counter').text(parseInt(loadCounter - 2));
            }
            if(progress >= 100) {
                $('#modalMergeLabels .progress-steps > li:last-child .icon-circle').toggleClass('text-muted text-primary');
                $('#modalMergeLabels .progress-steps > li:last-child .icon-check').removeClass('hidden');
                $('#modalMergeLabels .progress-bar').removeClass('bg-primary').addClass('bg-success');
            }
        } else {
            if (state == 'success') {
                $('#modalMergeLabels .progress-steps .counter').text(parseInt($('#modalMergeLabels .progress-steps .counter').text()) + 1);
            } else if (state == 'error') {
                printAllError = true;
            }
            if(loadCounter == 2) {
                if (printAllError) {
                    $('#modalMergeLabels .progress-steps > li:eq(1) .icon-circle').toggleClass('text-muted text-warning');
                    $('#modalMergeLabels .progress-steps > li:eq(1) .icon-check').toggleClass('icon-check icon-minus')
                    if($('#modalMergeLabels .progress-steps .counter').text() != '0') {
                        $('#gls-generate-label-warning').slideDown();
                    }
                } else {
                    $('#modalMergeLabels .progress-steps > li:eq(1) .icon-circle').toggleClass('text-muted text-primary');
                }

                $('#modalMergeLabels .progress-steps > li:eq(1) .icon-stack-1x').removeClass('hidden');
            }
        }

    }

    var modal_content = $('#modalMergeLabels .modal-body').html();
    $('#modalMergeLabels').on('show.bs.modal', function (e) {
        $('#modalMergeLabels .modal-title').html(modal_title);
        $('#modalMergeLabels .modal-body').html(modal_content);
    });

    $('#gls-btn-print-all').on('click', function(e) {

        e.preventDefault();
        // e.stopImmediatePropagation();

        $('#gls-btn-print-all').attr('data-counter', $('.btn-print:not(.btn-disabled)').length + 2);
        $('#modalMergeLabels').modal({keyboard: false, backdrop: 'static'});
        $('#modalMergeLabels').modal('show');

        // Suppression du dossier temporaire
        $.ajax({
            type: 'POST',
            url: ajax_uri+'generateAllLabels',
            cache: false,
            dataType: 'json',
            context: this,
            data: {prepareMerge: 1}
        }).done(function(jsonData) {

            if (jsonData.hasError === true) {
                $('#gls-generate-label-error').slideDown();
            } else {
                manageMergePDFSteps();

                var list = [];
                var orders = [];
                $('.btn-print').each(function() {
                    if(!$(this).hasClass('btn-disabled') && !$(this).hasClass('btn-processing')) {
                        var order_id = $(this).getOrderId();
                        var def = new $.Deferred();
                        orders.push(order_id);
                        list.push(def);
                        $('#local_print_'+order_id).val(1);
                        $(this).trigger('click', def);
                    }
                });

                $.when.apply($, list).then(function() {
                    $.ajax({
                        type: 'POST',
                        url: ajax_uri+'generateAllLabels',
                        cache: false,
                        dataType: 'json',
                        context: this,
                        data: {mergePDF: 1}
                    }).done(function(jsonData2) {
                        if (jsonData2.hasError === true) {
                            $('#modalMergeLabels').modal('hide');
                        } else if(jsonData2.data) {
                            manageMergePDFSteps();
                            $('#gls-btn-print-all').prop('disabled', true);
                            $('#modalMergeLabels .modal-title').html(modal_title_ready);
                            $('#modalMergeLabels .btn').prop('disabled', false).attr('data-file', jsonData2.data.pdf);
                            $('#modalMergeLabels .btn').on('click', function() {
                                window.open(download_labels_url + $(this).attr('data-file'));
                            });
                        }
                    }).fail(function() {
                        $('#gls-generate-label-error').slideDown();
                    });
                }, function () {
                    // generation failed
                    $.each(list, function (index, deferred) {
                        if(deferred.state() == "pending") {
                            deferred.reject();
                        }
                    });
                    $('#gls-generate-label-error').slideDown();
                });
            }
        }).fail(function() {
            $('#gls-generate-label-error').slideDown();
        });
    });
});