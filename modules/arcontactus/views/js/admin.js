/*
* 2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Azelab <support@azelab.com>
*  @copyright  2017 Azelab

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

var arCU = {
    controller: 'AdminArContactUs',
    prevOrder: null,
    ajaxUrl: null,
    addTitle: null,
    editTitle: null,
    successSaveMessage: null,
    successOrderMessage: null,
    successDeleteMessage: null,
    errorMessage: null,
    prompt: {
        ajaxUrl: null,
        controller: 'AdminArContactUsPrompt',
        add: function(){
            arCU.prompt.resetForm();
            jQuery('#arcontactus-prompt-modal-title').html(arCU.addTitle);
            jQuery('#arcontactus-prompt-modal').modal('show');
        },
        populateForm: function(data){
            jQuery.each(data, function(i){
                var fieldId = '#arcontactus_prompt_' + i;
                if (typeof data[i] == 'object'){
                    if (data[i] != null){
                        $.each(data[i], function(id_lang){
                            $(fieldId + '_' + id_lang).val(data[i][id_lang]);
                        });
                    }
                }else{
                    $(fieldId).val(data[i]);
                }
            });
        },
        edit: function(id){
            arCU.prompt.resetForm();
            jQuery('#arcontactus-prompt-modal-title').html(arCU.editTitle);
            arCU.blockUI('#arcontactus-prompt-table');
            jQuery.ajax({
                type: 'GET',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'edit',
                    controller : arCU.prompt.controller,
                    ajax : true,
                    id: id
                },
                success: function(data){
                    jQuery('#arcontactus-prompt-modal').modal('show');
                    arCU.prompt.populateForm(data);
                    arCU.unblockUI('#arcontactus-prompt-table');
                }
            }).fail(function(){
                arCU.unblockUI('#arcontactus-prompt-modal');
                showErrorMessage(arCU.errorMessage);
            });
        },
        toggle: function(id){
            jQuery.ajax({
                type: 'POST',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'switch',
                    controller : arCU.prompt.controller,
                    id: id,
                    ajax : true
                },
                success: function(data){
                    arCU.prompt.reload();
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        _getFormData: function(){
            var params = [];
            jQuery('#arcontactus-prompt-form [data-serializable="true"]').each(function(){
                var val = $(this).val();
                if ($(this).attr('type') == 'checkbox'){
                    val = $(this).is(':checked');
                }
                params.push({
                    name: $(this).attr('name'),
                    value: val
                });
            });
            return params;
        },
        save: function(){
            var params = arCU.prompt._getFormData();
            jQuery.ajax({
                type: 'POST',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'save',
                    controller : arCU.prompt.controller,
                    ajax : true,
                    data: params,
                    id: jQuery('#arcontactus_prompt_id').val()
                },
                success: function(data){
                    if (!arCU.prompt.processErrors(data)){
                        showSuccessMessage(arCU.successSaveMessage);
                        jQuery('#arcontactus-prompt-modal').modal('hide');
                        arCU.prompt.reload();
                    }
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        clearErrors: function(){
            jQuery('#arcontactus-prompt-form .form-group.has-error').removeClass('has-error');
        },
        processErrors: function(data){
            arCU.prompt.clearErrors();
            if (data.success == 0){
                jQuery.each(data.errors, function(index){
                    if (typeof data.errors[index] == 'object'){
                        var errors = [];
                        var cont = null
                        jQuery.each(data.errors[index], function(i){
                            cont = jQuery('#arcontactus_prompt_'+index + '_' + data.errors[index][i]['id_lang']).parents('.form-group');
                            cont.addClass('has-error');
                            errors.push(data.errors[index][i]['error']);
                        });
                        
                        cont.find('.errors').html(errors.join('<br/>'));
                    }else{
                        jQuery('#arcontactus_prompt_'+index).parents('.form-group').addClass('has-error');
                        jQuery('#arcontactus_prompt_'+index).parents('.form-group').find('.errors').text(data.errors[index]);
                    }
                });
                showErrorMessage(arCU.errorMessage);
                return true;
            }
            return false;
        },
        remove: function(id){
            if (!confirm('Delete this item?')){
                return false;
            }
            jQuery.ajax({
                type: 'POST',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'delete',
                    controller : arCU.prompt.controller,
                    ajax : true,
                    id: id
                },
                success: function(data){
                    showSuccessMessage(arCU.successDeleteMessage);
                    arCU.prompt.reload(true);
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        updateOrder: function(table, silent){
            var positions = [];
            jQuery(table).find('tbody tr').each(function(index){
                var order = index + 1;
                var id = jQuery(this).data('id');
                positions.push(id + '_' + order);
            });
            arCU.blockUI(table);
            if (arCU.prevOrder != positions){
                jQuery.ajax({
                    type: 'POST',
                    url: arCU.prompt.ajaxUrl,
                    dataType: 'json',
                    data: {
                        action : 'reorder',
                        controller : arCU.prompt.controller,
                        ajax : true,
                        data: positions
                    },
                    success: function(data){
                        arCU.unblockUI(table);
                        arCU.prevOrder = positions;
                        if (!silent){
                            //arCU.showSuccessMessage(arCU.successOrderMessage);
                        }
                        jQuery(table).find('tbody tr').each(function(index){
                            var order = index + 1;
                            jQuery(this).find('.position').text(order);
                        });
                    }
                }).fail(function(){
                    arCU.unblockUI(table);
                    showErrorMessage(arCU.errorMessage);
                });
            }
        },
        reload: function(reorder){
            jQuery.ajax({
                type: 'POST',
                url: arCU.prompt.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'reload',
                    controller : arCU.prompt.controller,
                    ajax : true,
                },
                success: function(data){
                    jQuery('#arcontactus-prompt-table').replaceWith(data.content);
                    arCU.init();
                    if (reorder){
                        arCU.prompt.updateOrder('#arcontactus-prompt-table', true);
                    }
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        resetForm: function(){
            jQuery('#arcontactus-prompt-form [data-default]').each(function(){
                var attr = jQuery(this).attr('data-default');
                if (typeof attr !== typeof undefined && attr !== false) {
                    jQuery(this).val(jQuery(this).data('default'));
                }
            });
            arCU.prompt.clearErrors();

        },
    },
    callback: {
        _getFormData: function(form, all){
            var params = [];
            var selector = '';
            if (all){
                selector = form + ' input, ' + form + ' select';
            }else{
                selector = form + ' [data-serializable="true"]'  
            }
            $(selector).each(function(){
                var val = $(this).val();
                if ($(this).attr('type') == 'checkbox'){
                    val = $(this).is(':checked')? $(this).val() : 0;
                }
                params.push({
                    name: $(this).attr('name'),
                    value: val
                });
            });
            return params;
        },
        bulk: {
            remove: function(){
                var ids = [];
                $('[name="callbacksBox[]"]:checked').each(function(){
                    ids.push($(this).val());
                });
                if (ids.length) {
                    if (confirm('Delete selected items?')){
                        $.ajax({
                            type: 'POST',
                            url: arCU.ajaxUrl,
                            dataType: 'json',
                            data: {
                                controller : arCU.controller,
                                action : 'deleteSelected',
                                ajax : true,
                                ids: ids,
                                //'sitemap-productsBox': arSEO.sitemap.product.bulk._getSelectedIds()
                            },
                            success: function(data){
                                arCU.unblockUI('#form-arcu-callbacks-container');
                                arCU.callback.reload();
                            }
                        }).fail(function(){
                            arCU.unblockUI('#form-arcu-callbacks-container');
                            showErrorMessage(arCU.errorMessage);
                        });
                    }
                } else {
                    alert('Please select items to perform this action');
                }
            }
        },
        saveComment: function(id, comment){
            arCU.blockUI('#arcontactus-custom-modal .modal-content');
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'saveComment',
                    ajax : true,
                    id: id,
                    comment: comment
                },
                success: function(data){
                    arCU.callback.reload();
                    arCU.unblockUI('#arcontactus-custom-modal .modal-content');
                }
            }).fail(function(){
                arCU.unblockUI('#arcontactus-custom-modal .modal-content');
                showErrorMessage(arCU.errorMessage);
            });
        },
        reload: function(submit){
            var params = arCU.callback._getFormData('#form-callbacks', true);
            if (typeof submit != 'undefined' && submit !== null){
                params.push({
                    name: 'submit',
                    value: submit
                });
            }
            arCU.blockUI('#form-arcu-callbacks-container');
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'reloadCallbacks',
                    ajax : true,
                    data: params,
                    //'sitemap-productsBox': arSEO.sitemap.product.bulk._getSelectedIds()
                },
                success: function(data){
                    $('#form-arcu-callbacks-container').html(data.content);
                    $('#form-arcu-callbacks .pagination-link').off('click');
                    $('#form-arcu-callbacks .pagination-items-page').off('click');
                    arCU.unblockUI('#form-arcu-callbacks-container');
                }
            }).fail(function(){
                arCU.unblockUI('#form-arcu-callbacks-container');
                showErrorMessage(arCU.errorMessage);
            });
        },
        toggle: function(id, status){
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'callbackSwitch',
                    id: id,
                    status: status,
                    ajax : true
                },
                success: function(data)
                {
                    $('.arcu-status-label[data-id="' + id + '"]').removeClass('label-danger').removeClass('label-success').removeClass('label-warning').addClass('label-default');
                    switch(data.status){
                        case 0:
                            $('.arcu-status-label[data-id="' + id + '"][data-status="0"]').removeClass('label-default').addClass('label-danger');
                            break;
                        case 1:
                            $('.arcu-status-label[data-id="' + id + '"][data-status="1"]').removeClass('label-default').addClass('label-success');
                            break;
                        case 2:
                            $('.arcu-status-label[data-id="' + id + '"][data-status="2"]').removeClass('label-default').addClass('label-warning');
                            break;
                    }
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
        remove: function(id){
            if (!confirm('Delete this item?')){
                return false;
            }
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'callbackDelete',
                    ajax : true,
                    id: id
                },
                success: function(data)
                {
                    showSuccessMessage(arCU.successDeleteMessage);
                    arCU.callback.reload();
                }
            }).fail(function(){
                showErrorMessage(arCU.errorMessage);
            });
        },
    },
    init: function(){
        $("#arcontactus-table").tableDnD({	
            dragHandle: 'dragHandle',
            onDragClass: 'myDragClass',
            onDrop: function(table, row) {
                arCU.updateOrder(table, false);
            }
        });
        $("#arcontactus-prompt-table").tableDnD({	
            dragHandle: 'dragHandle',
            onDragClass: 'myDragClass',
            onDrop: function(table, row) {
                arCU.prompt.updateOrder(table, false);
            }
        });
        $('#arcontactus-modal').on('shown.bs.modal', function () {
            $('#fa5-container').scrollTo(0);
            if ($('#fa5 ul li.active').length){
                $('#fa5-container').scrollTo($('#fa5 ul li.active').position().top - $('#fa5 ul li.active').height() - 30);
            }
        });
    },
    reloadQRCode: function(data, title, channel, id){
        arCU.blockUI('#arcontactus-custom-modal .modal-content');
        $.ajax({
            type: 'GET',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                action : 'reloadQRCode',
                ajax : true,
                channel: channel,
                id: id,
                data: data
            },
            success: function(data)
            {
                $('#arcu-qr-code').attr('src', data.qrcodeFile);
                $('#arcontactus-custom-modal .modal-title').text(title);
                $('.arcu-qr-code-buttons .btn').removeClass('btn-success');
                $('.arcu-qr-code-buttons [data-channel="' + channel + '"]').addClass('btn-success');
                arCU.unblockUI('#arcontactus-custom-modal .modal-content');
            }
        }).fail(function(){
            arCU.unblockUI('#arcontactus-custom-modal .modal-content');
            showErrorMessage(arCU.errorMessage);
        });
    },
    clickToQRCode: function(event, link, title, channel, id){
        if (event.ctrlKey) {
            arCU.getQRCode(link, title, channel, id);
            return false;
        }
        return true;
    },
    getQRCode: function(data, title, channel, id){
        arCU.blockUI('#form-arcu-callbacks-container');
        $.ajax({
            type: 'GET',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                action : 'getQRCode',
                ajax : true,
                channel: channel,
                id: id,
                data: data
            },
            success: function(data)
            {
                $('#arcontactus-custom-modal .modal-title').text(title);
                $('#arcontactus-custom-modal .modal-body').html(data.content);
                $('#arcontactus-custom-modal').modal('show');
                arCU.unblockUI('#form-arcu-callbacks-container');
            }
        }).fail(function(){
            arCU.unblockUI('#form-arcu-callbacks-container');
            showErrorMessage(arCU.errorMessage);
        });
    },
    add: function(){
        arCU.resetForm();
        $('#arcontactus-modal-title').html(arCU.addTitle);
        $('#arcontactus-modal').modal('show');
    },
    populateForm: function(data){
        $.each(data, function(i){
            var fieldId = '#arcontactus_' + i;
            if (typeof data[i] == 'object'){
                if (data[i] != null){
                    $.each(data[i], function(id_lang){
                        $(fieldId + '_' + id_lang).val(data[i][id_lang]);
                    });
                }
            }else{
                if ($(fieldId).attr('type') == 'checkbox'){
                    if (data[i] == 1){
                        $(fieldId).prop('checked', 'true');
                    }else{
                        $(fieldId).removeProp('checked');
                    }
                }else{
                    $(fieldId).val(data[i]);
                }
            }
        });
        
        if (data.always == '1'){
            $('#ARCU_ALWAYS_on').click();
        }else{
            $('#ARCU_ALWAYS_off').click();
        }
        if (data.product_page == '1'){
            $('#ARCU_product_page_on').click();
        }else{
            $('#ARCU_product_page_off').click();
        }
        if (data.enable_qr == '1'){
            $('#ARCU_enable_qr_on').click();
        }else{
            $('#ARCU_enable_qr_off').click();
        }
        if (typeof data.data.groups != 'undefined'){
            $.each(data.data.groups, function(index){
                var groupId = data.data.groups[index];
                $('#arcontactus_groups_' + groupId).prop('checked', 'checked');
            });
        }
        $('#arcontactus_icon_type').val(data.icon_type);
        $('#arcontactus_icon_svg').val(data.icon_svg);
        $('#arcontactus_icon_img').val(data.icon_img);
        $('#arcontactus_uploaded_img_list').html('');
        if (data.icon_img) {
            var $img = $('<img>', {
                src: data.icon_img_url,
                width: 120
            });
            $('#arcontactus_uploaded_img_list').append($img);
        }
        if (data.no_container == '1'){
            $('#ARCU_no_container_on').click();
        }else{
            $('#ARCU_no_container_off').click();
        }
        arContactUsSwitchFields();
        $('.arcu-icon-list li.active').removeClass('active');
        $('.arcu-icon-list li[data-id="' + data.icon + '"]').addClass('active');
        $('#arcontactus_color').trigger('keyup');
        arcontactusChangeType();
        arcontactusChangeIconType();
    },
    edit: function(id){
        arCU.resetForm();
        $('#arcontactus-modal-title').html(arCU.editTitle);
        arCU.blockUI('#arcontactus-modal');
        $.ajax({
            type: 'GET',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'edit',
                ajax : true,
                id: id
            },
            success: function(data)
            {
                $('#arcontactus-modal').modal();
                arCU.populateForm(data);
                arCU.unblockUI('#arcontactus-modal');
            }
        }).fail(function(){
            arCU.unblockUI('#arcontactus-modal');
            showErrorMessage(arCU.errorMessage);
        });
    },
    toggle: function(id){
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'switch',
                id: id,
                ajax : true
            },
            success: function(data)
            {
                arCU.reload();
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    toggleProduct: function(id){
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'switchProduct',
                id: id,
                ajax : true
            },
            success: function(data)
            {
                arCU.reload();
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    _getFormData: function(){
        var params = [];
        $('#arcontactus-form [data-serializable="true"]').each(function(){
            var val = $(this).val();
            if ($(this).attr('type') == 'checkbox'){
                val = $(this).is(':checked')? 1 : 0;
            }
            params.push({
                name: $(this).attr('name'),
                value: val
            });
        });
        return params;
    },
    save: function(){
        var params = arCU._getFormData();
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'save',
                ajax : true,
                data: params,
                id_lang: $('#arcontactus_id_lang').val(),
                id: $('#arcontactus_id').val()
            },
            success: function(data)
            {
                if (!arCU.processErrors(data)){
                    showSuccessMessage(arCU.successSaveMessage);
                    $('#arcontactus-modal').modal('hide');
                    arCU.reload();
                }
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    clearErrors: function(){
        $('#arcontactus-form .form-group.has-error').removeClass('has-error');
    },
    processErrors: function(data){
        arCU.clearErrors();
        if (data.success == 0){
            $.each(data.errors, function(index){
                $('#arcontactus_'+index).parents('.form-group').addClass('has-error');
                $('#arcontactus_'+index).parents('.form-group').find('.errors').text(data.errors[index]);
            });
            showErrorMessage(arCU.errorMessage);
            return true;
        }
        return false;
    },
    remove: function(id){
        if (!confirm('Delete this item?')){
            return false;
        }
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'delete',
                ajax : true,
                id: id
            },
            success: function(data)
            {
                showSuccessMessage(arCU.successDeleteMessage);
                arCU.reload(true);
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    updateOrder: function(table, silent){
        var positions = [];
        $(table).find('tbody tr').each(function(index){
            var order = index + 1;
            var id = $(this).data('id');
            positions.push(id + '_' + order);
        });
        arCU.blockUI(table);
        if (arCU.prevOrder != positions){
            $.ajax({
                type: 'POST',
                url: arCU.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arCU.controller,
                    action : 'updateOrder',
                    ajax : true,
                    data: positions
                },
                success: function(data)
                {
                    arCU.unblockUI(table);
                    arCU.prevOrder = positions;
                    if (!silent){
                        showSuccessMessage(arCU.successOrderMessage);
                    }
                    $(table).find('tbody tr').each(function(index){
                        var order = index + 1;
                        $(this).find('.dragGroup .positions').text(order);
                    });
                }
            }).fail(function(){
                arCU.unblockUI(table);
                showErrorMessage(arCU.errorMessage);
            });
        }
    },
    reload: function(reorder){
        $.ajax({
            type: 'POST',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                controller : arCU.controller,
                action : 'reload',
                ajax : true
            },
            success: function(data)
            {
                $('#arcontactus-table').replaceWith(data.content);
                arCU.init();
                if (reorder){
                    arCU.updateOrder('#arcontactus-table', true);
                }
            }
        }).fail(function(){
            showErrorMessage(arCU.errorMessage);
        });
    },
    arCuShowQRCode: function(id){
        arCU.blockUI(false);
        jQuery.ajax({
            type: 'GET',
            url: arCU.ajaxUrl,
            dataType: 'json',
            data: {
                action : 'previewQRCode',
                ajax : true,
                id: id
            },
            success: function(data){
                jQuery('#arcu-qr-modal').remove();
                jQuery('#arcu-qr-modal-backdrop').remove();
                var $modal = $('<div>', {
                    id: 'arcu-qr-modal'
                });
                var $backdrop = $('<div>', {
                    id: 'arcu-qr-modal-backdrop'
                });
                var $img = $('<img>', {
                    src: data.qrcodeFile
                });
                var $title = $('<h4>');
                var $close = $('<button>', {
                    class: 'arcu-close-btn',
                    type: 'button'
                });
                $title.text(data.title);
                $modal.append($close);
                $modal.append($title);
                $modal.append($img);
                jQuery('body').append($backdrop);
                jQuery('body').append($modal);
                $img.on('load', function(){
                    arCU.unblockUI(false);
                    setTimeout(function(){
                        if (jQuery('#page').length){
                            jQuery('#page').addClass('arcu-blur');
                        } else if(jQuery('body>main').length) {
                            jQuery('body>main').addClass('arcu-blur');
                        }
                        jQuery('#arcu-qr-modal').addClass('active');
                        jQuery('#arcu-qr-modal-backdrop').addClass('active');
                    }, 200);
                });
            }
        }).fail(function(){
            arCU.unblockUI(false);
        });
    },
    arCuCloseQRCode: function(){
        jQuery('#arcu-qr-modal').removeClass('active');
        jQuery('#arcu-qr-modal-backdrop').removeClass('active');
        jQuery('.arcu-blur').removeClass('arcu-blur');
        setTimeout(function(){
            jQuery('#arcu-qr-modal').remove();
            jQuery('#arcu-qr-modal-backdrop').remove();
        }, 400);
    },
    resetForm: function(){
        arCU.clearErrors();
        $('#arcontactus-form [data-default]').each(function(){
            var attr = $(this).attr('data-default');
            if (typeof attr !== typeof undefined && attr !== false) {
                if ($(this).attr('type') == 'checkbox'){
                    if ($(this).data('default') == 1){
                        $(this).prop('checked', 'true');
                    }else{
                        $(this).removeProp('checked');
                    }
                }else{
                    $(this).val($(this).data('default'));
                }
            }
        });
        $('#ARCU_ALWAYS_on').click();
        $('#ARCU_product_page_off').click();
        arContactUsSwitchFields();
        $('#fa5 ul li.active').removeClass('active');
        $('#arcontactus_color').trigger('keyup');
        arcontactusFindIcon();
        arcontactusChangeType();
    },
    blockUI: function(selector){
        if (selector){
            $(selector).addClass('ar-blocked');
            $(selector).find('.ar-loading').remove();
            $(selector).append('<div class="ar-loading"><div class="ar-loading-inner">Loading...</div></div>');
        }else{
            $('#ar-static-loader').remove();
            $('body').append('<div id="ar-static-loader"><div class="ar-loading-inner"></div></div>');
        }
    },
    unblockUI: function(selector){
        if (selector){
            $(selector).find('.ar-loading').remove();
            $(selector).removeClass('ar-blocked');
        }else{
            $('#ar-static-loader').remove();
        }
    },
};

$('#form-callbacks .pagination-link').off('click');
$('#form-callbacks .pagination-items-page').off('click');
$(document).on('click', '#form-arcu-callbacks-container .pagination-link', function(){
    $('#form-arcu-callbacks-container input[name="page"]').val($(this).data('page'));
    //arCU.callback.reload();
    return false;
});
$(document).on('submit', '#form-callbacks', function(a){
    setTimeout(function(){
        arCU.callback.reload();
    }, 200);
    return false;
});
$(document).on('click', '#form-callbacks [name="submitResetcallbacks"]', function(a){
    arCU.callback.reload('submitReset');
    return false;
});
$(document).on('click', '#form-arcu-callbacks-container .pagination-items-page', function(){
    $('#form-arcu-callbacks-container input[name="selected_pagination"]').val($(this).data('items'));
    //arCU.callback.reload();
    return false;
});
window.addEventListener('load', function(){
    $('body').on('click', '.arcu-close-btn', function(){
        arCU.arCuCloseQRCode();
    });
    $('body').on('click', '#arcu-qr-modal-backdrop', function(){
        arCU.arCuCloseQRCode();
    });
});
