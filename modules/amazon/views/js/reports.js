/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */

const CS_Amazon = {};

$(document).ready(function () {
    function getSellerPartnerParameters () {
        // Products
        let regionId = '', mkpId = $('#country-selector input[name=amazon_lang]:checked').data('mkpId');
        // Orders
        if (!mkpId) {
            const $spRegion = $('#country-selector input[name=amazon_sp_region]:checked');
            regionId = $spRegion.data('regionId');
            mkpId = $spRegion.data('mkpId');
        }

        return {
            sp_mkp: mkpId || '',
            sp_region: regionId,
        };
    }

    function listReports () {
        const url = $('#reports-url').val(),
            type = $('#reports-type').val(),
            data = Object.assign({}, getSellerPartnerParameters(), {
                action: 'list-reports',
                type: type,
                context_key: $('#context_key').val(),
                instant_token: $('#instant_token').val(),
            }),
            pAjax = {
                type: 'POST',
                url: url,
                data: $('#amazonParams').serialize() + '&' + $.param(data),
                dataType: 'json',
            },
            $divLoader = $('#reports-loader'),
            $divErrors = $('#reports-error'),
            $divWarnings = $('#reports-warning'),
            $divResult = $('#reports-result'),
            $emptyWarning = $('#reports-none-available');

        $.ajax(Object.assign({}, pAjax, {
            beforeSend: function () {
                $emptyWarning.hide();
                $divResult.html('').hide();
                $divErrors.html('').hide();
                $divWarnings.html('').hide();
                $divLoader.show();
            },
            complete: function () {
                $divLoader.hide();
            },
            success: function (data) {
                if (data.output) {
                    $divResult.show();
                    $.each(data.output, function (o, output) {
                        $divResult.append(output + '<br/>');
                    });
                }
                if (data.message) {
                    $divResult.show();
                    $.each(data.messages, function (o, message) {
                        $divResult.append(message + '<br/>');
                    });
                }
                if (data.warning) {
                    $divWarnings.show();
                    $.each(data.warnings, function (w, warning) {
                        $divWarnings.append(warning + '<br/>');
                    });
                }
                if (data.error) {
                    $divErrors.show();
                    $.each(data.errors, function (e, errormsg) {
                        $divErrors.append(errormsg + '<br/>');
                    });
                }

                // July-04-2018: Hide header and remove table content before render result
                const reportTab = $('#amazonReportOptions');
                reportTab.find('.report-table-heading').hide();
                reportTab.find('table.report tbody tr:gt(0)').remove();

                if (data.count) {
                    $('#reports-none-available').hide();
                    DisplayReportList(data.reports);
                } else {
                    $('#reports-none-available').show();
                }

            },
            error: function (data) {
                $divErrors.html('AJAX Error<br><br>' + data.responseText).show();
                ManageAjaxError(pAjax, data, $divErrors);
            }
        }));
    }

    function DisplayReportList (reportset) {
        let rowIndex = 0;

        $.each(reportset, function (r, report) {
            // Clone Line, Append to the table and fill the order data
            const $reportLine = $('#amazonReportOptions .report-model:first').clone().appendTo('#amazonReportOptions table.table.report tbody.reports');
            $reportLine.attr('rel', report.id);
            if (!report.hasFeed) {
                $reportLine.addClass('disabled');
            }

            $reportLine.children('[rel=id]').addClass('submission-feed-id').html(report.id);
            $reportLine.children('[rel=region]').html(report.region);
            $reportLine.children('[rel=type]').html(report.type);
            $reportLine.children('[rel=start]').html(report.timestart);
            $reportLine.children('[rel=stop]').html(report.timestop);
            $reportLine.children('[rel=duration]').html(report.duration);
            $reportLine.children('[rel=items]').html(report.records);

            $reportLine.addClass(rowIndex++ % 2 ? 'alt_row' : '');
            $reportLine.show();
        });

        $('#amazonReportOptions .report-table-heading').show();
    }

    function ManageAjaxError (aCall, data, outdiv) {
        if (window.console) {
            console.log('Ajax Error');
            console.log(aCall);
            console.log(data);
        }
        outdiv.show().html($('#serror').val());

        if (data.output)
            outdiv.append('<br />' + data.output);

        if (data.responseText)
            outdiv.append('<br />' + data.responseText);

        outdiv.append('<hr />');
        outdiv.append($('#sdebug').val() + ':  ');

        outdiv.append('<form method="' + aCall.type + '" action="' + aCall.url + '?debug=1&' + aCall.data + '" target="_blank">' +
            '<input type="submit" class="button" id="send-debug" value="Execute in Debug Mode" /></form>');
    }

    var reportTab = $('#menudiv-report');
    // Don't need to load reports on load, because it will be loaded on tab switching

    // Choose tab Report
    $('#menu-report').click(listReports);

    // Change platform
    $('input[name=amazon_lang], input[name=amazon_sp_region]').on('change', function () {
        getSellerPartnerParameters();
        listReports();
    });

    // Select report
    reportTab.delegate('table.report tbody tr:not(.disabled)', 'click', function () {
        $('#menudiv-report table.report tbody tr').removeClass('report-selected');
        $(this).addClass('report-selected');
    });

    // Display report
    $('#submit-report-display').click(function () {
        const $selectedReport = $('#menudiv-report table.report tbody tr.report-selected'),
            $loading = $('#wait-report'),
            $resultZone = $('#submission-results');

        if (!$selectedReport.length) {
            alert($('#catalog-reports-select-msg').val());
            return;
        }

        const reportId = $selectedReport.attr('rel'),
            reportType = $selectedReport.find('td[rel=type]').text(),
            pAjax = {
                type: 'POST',
                url: $('#feed_result_url').val(),
                dataType: 'json',
                data: Object.assign({}, getSellerPartnerParameters(), {
                    instant_token: $('#instant_token').val(),
                    feed_id: reportId,
                    type: reportType,
                }),
            };

        $.ajax(Object.assign({}, pAjax, {
            beforeSend: function () {
                $loading.show();
                $resultZone.html('').hide();
            },
            complete: function () {
                $loading.hide();
            },
            success: function (data) {
                if (data.status) {
                    $resultZone.html(data.tpl).show();
                } else {
                    $resultZone.html(data.msg).show();
                }
                $('#report-set').slideDown();
            },
            error: function (data) {
                $('#report-set').hide();
                ManageAjaxError(pAjax, data, $('#amazon-report-error'));
            }
        }));
    });

    // Exploit for global usage
    CS_Amazon.listReports = listReports;
});
