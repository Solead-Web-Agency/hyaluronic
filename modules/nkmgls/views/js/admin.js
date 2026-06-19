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
function handleShipItCheckbox(isEnabled) {
    var elementsToHide = [
        'GLS_WSLOGIN',
        'GLS_WSPWD',
        'GLS_API_CUSTOMER_ID',
        'GLS_API_DELIVERY_LABEL_FORMAT',
        'GLS_TRACKING_API_ORDER_STATE_PREADVICE',
        'GLS_TRACKING_API_ORDER_STATE_NOTDELIVERED',
        'GLS_TRACKING_API_ORDER_STATE_DELIVEREDPS'
    ];

    for (var selector of elementsToHide) {
        var e = $('#' + selector)
            .closest('.form-group')
        ;

        if (isEnabled) {
            e.hide();
        } else {
            e.show();
        }
    }
}

$(document).ready(function() {

    $('body').addClass('no-smb-reskin');

    $('.module-page .btn.start').on('click', function(e) {
        e.preventDefault();
        $('#form-nav .nav-item:eq(2) a').tab('show');
    });

    $('#form-gls_log').hide();

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if($(e.target).is('a[href="#nkmgls-tab-5"]')) {
            $('#form-gls_log').show();
        } else {
            $('#form-gls_log').hide();
        }
        $('.accordion-link').removeClass('active');
        $(e.target).addClass('active');
        if ($(window).width() < 768) {
            $('html, body').animate({ scrollTop: $(e.target).offset().top }, 'slow');
        }
    });

    $('.module-page .js-tabs .nav-link').each(function(idx) {
        var navlink = $(this).clone(true).addClass('accordion-link');
        if(idx == 0) {
            navlink.addClass('active');
        }
        $(navlink).insertBefore($(this).attr('href'))
    });

    $('.module-page .js-btn-save').on('click', function(e) {
        e.preventDefault();
        $('#module_form_submit_btn_1').trigger('click');
    });

    $('#module_form').on('submit', function(e) {
        $('#module_form').attr('action', $('#module_form').attr('action') + '&tab=' + $('#form-nav .nav-item.active > a').attr('href').substr(1));
    });

    if($('form#form-gls_log').length > 0) {
        $('#form-gls_log').attr('action', $('#form-gls_log').attr('action').replace('#', '&tab=nkmgls-tab-5#'));
    }

    $('.table.gls_log a').each(function() {
        $(this).attr('href', $(this).attr('href') + '&tab=nkmgls-tab-5');
    });

    $('.form-footer .btn.save').tooltip();

    $('input[name="GLS_API_SHOP_RETURN_ADDRESS"]').on('change', function(e) {

        if($(this).is(':checked')) {

            $(this).closest('.form-group').nextUntil('.form-group.panel-heading').hide();

            if($(this).val() == '1') {

                $(this).closest('.form-group').nextUntil('.form-group.panel-heading').filter('.default-address').show();
                $(this).closest('.form-group').nextUntil('.form-group.panel-heading').filter(':not(.default-address)').find('input, select, textarea').prop('disabled', true);

            } else {

                $(this).closest('.form-group').nextUntil('.form-group.panel-heading').filter(':not(.default-address)').find('input, select, textarea').prop('disabled', false);
                $(this).closest('.form-group').nextUntil('.form-group.panel-heading').filter(':not(.default-address)').show();

            }

        }

    });

    $('input[name="GLS_API_SHOP_RETURN_SERVICE"]').on('change', function(e) {

        if($(this).is(':checked')) {

            if($(this).val() == '1') {

                if($('input[name="GLS_API_SHOP_RETURN_ADDRESS"]:checked').val() == '1') {
                    $(this).closest('.form-group').nextUntil('.form-group.panel-heading').stop(true, false).slideDown();
                } else {
                    $(this).closest('.form-group').nextUntil('.form-group.panel-heading').stop(true, false).slideDown();
                }

            } else {

                $(this).closest('.form-group').nextUntil('.form-group.panel-heading').stop(true, false).slideUp();

            }

            $('input[name="GLS_API_SHOP_RETURN_ADDRESS"]').trigger('change');

        }

    });
    $('input[name="GLS_API_SHOP_RETURN_SERVICE"]').trigger('change');

    $('input[name="GLS_ORDER_PREFIX_ENABLE"]').on('change', function(e) {
        if($(this).is(':checked')) {
            if($(this).val() == '1') {
                $(this).closest('.form-group').next().stop(true, false).slideDown();
            } else {
                $(this).closest('.form-group').next().stop(true, false).slideUp();
            }
        }
    });
    $('input[name="GLS_ORDER_PREFIX_ENABLE"]').trigger('change');

    $('input[name="GLS_CUSTOM_EXPORT_PATH_ENABLE"]').on('change', function(e) {
        if($(this).is(':checked')) {
            if($(this).val() == '1') {
                $(this).closest('.form-group').next().stop(true, false).slideDown();
            } else {
                $(this).closest('.form-group').next().stop(true, false).slideUp();
            }
        }
    });
    $('input[name="GLS_CUSTOM_EXPORT_PATH_ENABLE"]').trigger('change');

    $('input[name="GLS_GOOGLE_MAPS_ENABLE"]').on('change', function(e) {
        if($(this).is(':checked')) {
            if($(this).val() == '1') {
                $(this).closest('.form-group').nextAll('.google-maps').stop(true, false).slideDown();
            } else {
                $(this).closest('.form-group').nextAll('.google-maps').stop(true, false).slideUp();
            }
        }
    });
    $('input[name="GLS_GOOGLE_MAPS_ENABLE"]').trigger('change');

    var match = RegExp('[?&]tab=([^&]*)').exec(window.location.search);
    var result = (match && decodeURIComponent(match[1].replace(/\+/g, ' ')));
    if(result !== null) {
        jQuery('#form-nav .nav-item a[href="#' + result + '"]').tab('show');
    }

    $('.copy-button').on('click', function(e) {
        e.preventDefault();
        var input = jQuery(this).parent().prev('.copy-input');
        jQuery(input).prop("disabled", false).select();
        document.execCommand('copy');
        jQuery(input).prop("disabled", true);
    });

    $('.exec-button').on('click', function(e) {

        e.preventDefault();

        if(!$(this).attr('data-disabled')) {
            var wdw = window.open(jQuery(this).parent().prev().children('.copy-input').val(), '_blank');
            wdw.blur();
            window.focus();
            setTimeout(function() {
                wdw.close();
            }, 3000);
            return false;
        }

    });

    var shipiItApiCheckbox = $('input[name="GLS_IS_USING_SHIPIT_API"]');

    shipiItApiCheckbox.on('change', function (e) {
        if(!$(this).is(':checked')) {
            return;
        }

        var isChecked = ($(this).val() == '1');
        handleShipItCheckbox(isChecked);
    });
    shipiItApiCheckbox.trigger('change');

    if (shipiItApiCheckbox.length === 0) {
        handleShipItCheckbox(true);
    }
});
