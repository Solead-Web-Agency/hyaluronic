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

    $('input[name="GLS_EXPORT_AUTOMATION"]:checked, input[name="GLS_IMPORT_AUTOMATION"]:checked').each(function() {
        if($(this).val() == '1') {
            $(this).closest('.form-wrapper').next('.alert').show();
            $(this).closest('.form-wrapper').next('.alert').find('.exec-button').removeAttr('title');
        } else {
            $(this).closest('.form-wrapper').next('.alert').hide();
            $(this).closest('.form-wrapper').next('.alert').find('.exec-button').attr('data-disabled', true).tooltip();
        }
    });

    $('input[name="GLS_EXPORT_AUTOMATION"], input[name="GLS_IMPORT_AUTOMATION"]').on('change', function() {
        if($(this).val() == '1') {
            $(this).closest('.form-wrapper').next('.alert').slideDown();
        } else {
            $(this).closest('.form-wrapper').next('.alert').slideUp();
        }
    });

    $('#exportOrderStep2').on('click', function(e) {
        e.preventDefault();
        $(this).prop('disabled', true).find('i').removeClass('process-icon-next').addClass('process-icon-loading');
        $.ajax({
            type: 'POST',
            url: ajax_uri,
            cache: false,
            dataType: 'json',
            context: this,
            data: $('#configuration_form').serialize()
        }).done(function(response) {
            $('#modalExportOrderStep2').modal({keyboard: false, closable: false, backdrop: 'static'});
            $('#modalExportOrderStep2').find('.modal-body').html(response);
            $('#modalExportOrderStep2').modal('show');
            $('.bulk-actions .dropdown-menu li:first > a').trigger('click');
            $(this).prop('disabled', false).find('i').removeClass('process-icon-loading').addClass('process-icon-next');
        }).fail(function() {
            $(this).prop('disabled', false).find('i').removeClass('process-icon-loading').addClass('process-icon-next');
            return false;
        });
    });

    $(document).on('click', '.close-export-modal', function(e) {
        e.preventDefault();
        $('#modalExportOrderStep2').modal('hide');
    });

    $('#form-order').on('submit', function(e) {
        $('#modalExportOrderStep2').modal('hide');
    });

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

});