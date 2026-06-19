/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

var elegantalFormGroupClass = 'form-group';
var elegantalProcessing = false;

jQuery(document).ready(function () {

    // Identify form group class
    if (jQuery('[type="submit"]').parents('.margin-form').length > 0) {
        elegantalFormGroupClass = 'margin-form';
    }

    // Back button fix on < 1.6.1
    jQuery('.panel-footer button[name="submitOptionsmodule"]').click(function () {
        if (jQuery(this).find('.process-icon-back')) {
            var url = window.location.href.replace(/&event=\w+/gi, '');
            window.location.href = url;
        }
    });

    // Import Edit Page
    if (jQuery('[name="submitImportEdit"]').length > 0) {
        elegantalImportFormVisibility(0);
        jQuery('input, select').on('change', function () {
            elegantalImportFormVisibility(250);
        });

        jQuery('[name="is_cron"]').on('change', function () {
            if (jQuery(this).val() == 1) {
                jQuery('[name="product_limit_per_request"]').val('50');
            } else {
                jQuery('[name="product_limit_per_request"]').val('5');
            }
        });

        jQuery('select[name="find_products_by"]').on('change', function () {
            if (jQuery(this).val() === 'id') {
                alert("You should use ID option IF ONLY product IDs in your import file match product IDs in your shop.");
            }
        });
    }

    // Mapping Page
    if (jQuery('[name="submitMapping"]').length > 0) {
        jQuery('#elegantal_header_row').appendTo(jQuery('.elegantalBootstrapWrapper .panel .panel-heading')).show();

        jQuery('body').on('click', '.ignore_all_columns', function (e) {
            e.preventDefault();
            jQuery('[name="submitMapping"]').parents('form').find('select').each(function (index, el) {
                // Don't touch id_reference column and check if first option is "Ignore all" which has value of -1
                if (index > 0 && jQuery(el).find('option:first').val() === '-1') {
                    jQuery(el).find('option:first').prop('selected', true).trigger('change');
                    jQuery(el).parents('.row').find('input[type="text"][name="default_' + jQuery(el).attr('name') + '"]').val("");
                }
            });
        });
    }

    // Manage Category Page
    if (jQuery('[name="submitManageCategory"]').length > 0) {
        jQuery('.cattree.tree').addClass('full_loaded');
        // Add new category mapping
        jQuery('.add_new_category_map').click(function () {
            jQuery(this).parent().find('.elegantal_categories_map:last').clone().insertBefore(this);
        });
    }

    // Import Page
    if (jQuery('.elegantal_import_panel').length > 0) {
        // Prevent accidental page reload
        window.onbeforeunload = function () {
            if (elegantalProcessing) {
                return jQuery('.elegantal_import_panel').data('reloadmsg');
            }
        };
        // Make request to prepare csv rows
        elegantalImportPrepareCsvRows();
    }

    // Export Edit Page
    if (jQuery('[name="submitExportEdit"]').length > 0) {
        // Change file path extension if file format is changed
        jQuery('[name="file_format"]').on('change', function () {
            var file_path = jQuery('[name="file_path"]').val();
            var file_ext = file_path.substr((file_path.lastIndexOf('.') + 1));
            var new_ext = jQuery(this).val();
            if (file_ext !== new_ext && (file_ext.length === 3 || file_ext.length === 4)) {
                jQuery('[name="file_path"]').val(file_path.substr(0, (file_path.lastIndexOf('.'))) + '.' + new_ext);
            }
        });

        elegantalExportFormVisibility(0);
        jQuery('input, select').on('change', function () {
            elegantalExportFormVisibility(250);
        });
    }

    // Export Columns Page
    if (jQuery('[name="submitExportColumns"]').length > 0) {
        jQuery('.elegantalBootstrapWrapper .panel .panel-heading').append('<a href="#" class="checkUncheckAllColumns"><i class="icon-server"></i></a>');

        jQuery('.checkUncheckAllColumns').addClass('checkedAllColumns');
        jQuery('.checkUncheckAllColumns').prop('title', 'Deselect all columns');

        jQuery('body').on('click', '.checkUncheckAllColumns', function (e) {
            e.preventDefault();
            if (jQuery(this).hasClass('checkedAllColumns')) {
                jQuery(this).removeClass('checkedAllColumns').addClass('uncheckedAllColumns').prop('title', 'Select all columns');
                jQuery('[name="submitExportColumns"]').parents('form').find('.switch input[type="radio"][value="0"]').prop('checked', true);
                jQuery('[name="submitExportColumns"]').parents('form').find('input[type="text"][name^="default_"]').val("");
            } else if (jQuery(this).hasClass('uncheckedAllColumns')) {
                jQuery(this).removeClass('uncheckedAllColumns').addClass('checkedAllColumns').prop('title', 'Uncheck all columns');
                jQuery('[name="submitExportColumns"]').parents('form').find('.switch input[type="radio"][value="1"]').prop('checked', true);
            }
        });
    }

    // Export Page
    if (jQuery('.elegantal_export_panel').length > 0) {
        // Prevent accidental page reload
        window.onbeforeunload = function () {
            if (elegantalProcessing) {
                return jQuery('.elegantal_export_panel').data('reloadmsg');
            }
        };
        // Start with export
        elegantalExportRequest();
    }
});

function elegantalImportPrepareCsvRows() {
    elegantalProcessing = true;
    var elegantalAdminUrl = jQuery('.elegantaleasyimportJsDef').data('adminurl');
    var panel = jQuery('.elegantal_import_panel');
    var id = panel.data('id');
    var limit = panel.data('limit');
    var min = 100;
    var max = 100000000;
    var random = Math.floor(Math.random() * (max - min + 1)) + min;

    jQuery.ajax({
        url: elegantalAdminUrl,
        type: 'GET',
        dataType: 'json',
        data: {
            event: 'import',
            ajax: 1,
            prepareCsvRows: 1,
            id_elegantaleasyimport: id,
            elegantal: random
        },
        success: function (result) {
            if (result.success) {
                var total = result.count;
                var requests = 1;

                if (total && total > limit) {
                    requests = Math.ceil(total / limit);
                }

                panel.data('requests', requests);

                // Change status text
                jQuery('.elegantal_import_csv_txt').text(jQuery('.elegantal_import_csv_txt').text().replace('[n]', total));
                jQuery('.elegantal_prepare_csv_txt').hide();
                jQuery('.elegantal_import_csv_txt').fadeIn();

                // Start import with the first request
                elegantalImportRequest(1);
            } else {
                elegantalProcessing = false;
                jQuery('.elegantal_error_txt').text(result.message);
                jQuery('.elegantal_error').fadeIn();
                jQuery('html, body').animate({scrollTop: 0});
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            elegantalProcessing = false;
            jQuery('.elegantal_error_txt').text(errorThrown);
            jQuery('.elegantal_error').fadeIn();
            jQuery('html, body').animate({scrollTop: 0});
        }
    });
}

function elegantalImportRequest(currentRequest) {
    elegantalProcessing = true;
    var elegantalAdminUrl = jQuery('.elegantaleasyimportJsDef').data('adminurl');
    var panel = jQuery('.elegantal_import_panel');
    var progress = panel.find('.elegantal_progress_bar');

    var id = panel.data('id');
    var totalRequests = panel.data('requests');

    // Generate random number for GET request. This is needed to prevent if there is cache for the URL
    var min = 100;
    var max = 100000000;
    var random = Math.floor(Math.random() * (max - min + 1)) + min;

    jQuery.ajax({
        url: elegantalAdminUrl,
        type: 'GET',
        dataType: 'json',
        data: {
            event: 'import',
            ajax: 1,
            id_elegantaleasyimport: id,
            elegantal: random
        },
        success: function (result) {
            if (result.success) {
                var completed = (currentRequest * 100) / totalRequests;
                progress.css({width: completed + '%'});
                progress.text(Math.round(completed) + '%');

                if (currentRequest < totalRequests) {
                    elegantalImportRequest(currentRequest + 1);
                } else {
                    elegantalProcessing = false;
                    jQuery('.elegantal_progress_row').hide();
                    jQuery('.elegantal_result_row').fadeIn();
                }
            } else {
                elegantalProcessing = false;
                jQuery('.elegantal_error_txt').text(result.message);
                jQuery('.elegantal_error').fadeIn();
                jQuery('html, body').animate({scrollTop: 0});
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
            console.log(jqXHR.responseText);

            var completed = (currentRequest * 100) / totalRequests;
            progress.css({width: completed + '%'});
            progress.text(Math.round(completed) + '%');

            if (currentRequest < totalRequests) {
                elegantalImportRequest(currentRequest);
            } else {
                elegantalProcessing = false;
                jQuery('.elegantal_progress_row').hide();
                jQuery('.elegantal_result_row').fadeIn();
            }
        }
    });
}

function elegantalExportRequest() {
    elegantalProcessing = true;
    var elegantalAdminUrl = jQuery('.elegantaleasyimportJsDef').data('adminurl');
    var panel = jQuery('.elegantal_export_panel');
    var id = panel.data('id');
    var min = 100;
    var max = 100000000;
    var random = Math.floor(Math.random() * (max - min + 1)) + min;

    jQuery.ajax({
        url: elegantalAdminUrl,
        type: 'GET',
        dataType: 'json',
        data: {
            event: 'export',
            ajax: 1,
            id_elegantaleasyimport_export: id,
            elegantal: random
        },
        success: function (result) {
            if (result.success) {
                elegantalProcessing = false;
                jQuery('.elegantal_result_txt').text(jQuery('.elegantal_result_txt').text().replace('_count', result.count));
                jQuery('.elegantal_progress_row').hide();
                jQuery('.elegantal_result_row').fadeIn();
            } else {
                elegantalProcessing = false;
                jQuery('.elegantal_progress_row').hide();
                jQuery('.elegantal_error_txt').text(result.message);
                jQuery('.elegantal_error').fadeIn();
                jQuery('html, body').animate({scrollTop: 0});
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            elegantalProcessing = false;
            console.log(errorThrown);
            console.log(jqXHR.responseText);
            jQuery('.elegantal_progress_row').hide();
            jQuery('.elegantal_error_txt').text(errorThrown);
            jQuery('.elegantal_error').fadeIn();
            jQuery('html, body').animate({scrollTop: 0});
        }
    });
}

function elegantalImportFormVisibility(speed) {
    if (jQuery('[name="import_type"]').val() == 1) {
        jQuery('[name="csv_file_upload"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        jQuery('[name="csv_path"], [name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"], [name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).hide();
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="csv_file_upload"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
            jQuery('[name="csv_path"], [name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"], [name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).prev('label').hide();
        }
    } else if (jQuery('[name="import_type"]').val() == 2) {
        jQuery('[name="csv_path"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        jQuery('[name="csv_file_upload"], [name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"], [name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).hide();
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="csv_path"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
            jQuery('[name="csv_file_upload"], [name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"], [name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).prev('label').hide();
        }
    } else if (jQuery('[name="import_type"]').val() == 3) {
        jQuery('[name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        jQuery('[name="csv_file_upload"], [name="csv_path"], [name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).hide();
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
            jQuery('[name="csv_file_upload"], [name="csv_path"], [name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).prev('label').hide();
        }
    } else if (jQuery('[name="import_type"]').val() == 4 || jQuery('[name="import_type"]').val() == 5) {
        jQuery('[name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        jQuery('[name="csv_file_upload"], [name="csv_path"], [name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"]').parents('.' + elegantalFormGroupClass).hide();
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
            jQuery('[name="csv_file_upload"], [name="csv_path"], [name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"]').parents('.' + elegantalFormGroupClass).prev('label').hide();
        }
    } else {
        jQuery('[name="csv_file_upload"], [name="csv_path"], [name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"], [name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="csv_file_upload"], [name="csv_path"], [name="csv_url"], [name="csv_url_username"], [name="csv_url_password"], [name="csv_url_method"], [name="ftp_host"], [name="ftp_port"], [name="ftp_username"], [name="ftp_password"], [name="ftp_file"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
        }
    }

    if (jQuery('[name="import_type"]').val() == 3 && jQuery('[name="csv_url_method"]').val() == 'POST') {
        jQuery('[name="csv_url_post_params"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="csv_url_post_params"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
        }
    } else {
        jQuery('[name="csv_url_post_params"]').parents('.' + elegantalFormGroupClass).hide();
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="csv_url_post_params"]').parents('.' + elegantalFormGroupClass).prev('label').hide();
        }
    }

    var elegantal_form_combination = jQuery('[name="lang_id"], [name="create_new_products"], [name="update_existing_products"], [name="replicate_all_languages"], [name="enable_new_products_by_default"], [name="enable_if_have_stock"], [name="disable_if_no_stock"]').parents('.' + elegantalFormGroupClass);
    if (jQuery('[name="entity"]').val() == 'combination') {
        elegantal_form_combination.hide();
        if (elegantalFormGroupClass == 'margin-form') {
            elegantal_form_combination.prev('label').hide();
        }
        jQuery('[name="delete_old_combinations"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="delete_old_combinations"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
        }
    } else {
        jQuery('[name="delete_old_combinations"]').parents('.' + elegantalFormGroupClass).hide();
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="delete_old_combinations"]').parents('.' + elegantalFormGroupClass).prev('label').hide();
        }
        elegantal_form_combination.fadeIn(speed);
        if (elegantalFormGroupClass == 'margin-form') {
            elegantal_form_combination.prev('label').fadeIn(speed);
        }
    }

    var elegantal_form_is_cron = jQuery('[name="email_to_send_notification"]').parents('.' + elegantalFormGroupClass);
    if (jQuery('[name="is_cron"]:checked').val() == 1) {
        elegantal_form_is_cron.fadeIn(speed);
        if (elegantalFormGroupClass == 'margin-form') {
            elegantal_form_is_cron.prev('label').fadeIn(speed);
        }
    } else {
        elegantal_form_is_cron.hide();
        if (elegantalFormGroupClass == 'margin-form') {
            elegantal_form_is_cron.prev('label').hide();
        }
    }

    var elegantal_form_create_new_products = jQuery('[name="enable_new_products_by_default"]').parents('.' + elegantalFormGroupClass);
    if (jQuery('[name="create_new_products"]:checked').val() == 1 && jQuery('[name="entity"]').val() == 'product') {
        elegantal_form_create_new_products.fadeIn(speed);
        if (elegantalFormGroupClass == 'margin-form') {
            elegantal_form_create_new_products.prev('label').fadeIn(speed);
        }
    } else {
        elegantal_form_create_new_products.hide();
        if (elegantalFormGroupClass == 'margin-form') {
            elegantal_form_create_new_products.prev('label').hide();
        }
    }
}

function elegantalExportFormVisibility(speed) {
    if (jQuery('[name="entity"]').val() === 'product') {
        jQuery('[name="multiple_subcategory_separator"], [name="currency_id"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="multiple_subcategory_separator"], [name="currency_id"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
        }
    } else {
        jQuery('[name="multiple_subcategory_separator"], [name="currency_id"]').parents('.' + elegantalFormGroupClass).hide();
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="multiple_subcategory_separator"], [name="currency_id"]').parents('.' + elegantalFormGroupClass).prev('label').hide();
        }
    }
}