/**
 * OrderEdit
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2022 silbersaiten
 * @license   See joined file licence.txt
 * @support   silbersaiten <support@silbersaiten.de>
 * @category  Module
 * @version   2.0.34
 * @link      https://www.silbersaiten.de
 */

var orderEditor = {
    currentProduct: {},
    field: {
        'PRICE_TAX_EXCL': 0,
        'PRICE_TAX_INCL': 1,
        'QUANTITY': 2
    },
    init: function () {
        this.listeners();
    },
    listeners: function () {
        $(document).on('click', 'button.js-order-product-edit-btn', function () {
            orderEditor.disableSaveButton();
            orderEditor.fillProductFields($(this));

            var orderDetailId = $(this).data('order-detail-id');
            var $rowContainer = $('#orderProductsTable tr#editOrderProduct_' + orderDetailId);

            /** Set our own events handlers */
            $('input.editProductPriceTaxExcl').off('change keyup').on('change keyup',  function (e) {
                orderEditor.recalculateProductRow($rowContainer, orderEditor.field.PRICE_TAX_EXCL);
            });
            $('input.editProductPriceTaxIncl').off('change keyup').on('change keyup',  function (e) {
                orderEditor.recalculateProductRow($rowContainer, orderEditor.field.PRICE_TAX_INCL);
            });
            $('input.editProductQuantity').off('change keyup').on('change keyup',  function (e) {
                orderEditor.recalculateProductRow($rowContainer, orderEditor.field.QUANTITY);
            });
        });
        $(document).on('click', 'button.productEditCancelBtn ', function () {
            orderEditor.currentProduct = {};
        });
        $(document).on('change keyup', '[name^=edit_product_row]', function () {
            orderEditor.enableSaveButton();
        });
        $(document).on('click', 'button.productEditSaveBtn_custom', function () {
            orderEditor.saveProductDetail($(this));
        });
        $(document).on('change keyup', '.reduction_percent', function () {
            orderEditor.recalculateProductTax($(this).closest('tr.editProductRow'));
        });
        $(document).on('change', '.tax_rate', function () {
            orderEditor.recalculateProductTax($(this).closest('tr.editProductRow'));
        });
        $(document).on('click', 'a.deleteOrderHistory', function (e) {
            e.preventDefault();
            orderEditor.deleteOrderHistory($(this));
        });
        $(document).on('click', 'a.deleteOrderDocument', function (e) {
            e.preventDefault();
            orderEditor.deleteOrderDocument($(this));
        });
        $(document).on('click', 'a#update_order_document', function (e) {
            e.preventDefault();
            orderEditor.updateOrderDocument($(this));
        });
        $(document).on('click', 'a.deleteOrderPayment', function (e) {
            e.preventDefault();
            orderEditor.deleteOrderPayment($(this));
        });
        $(document).on('click', 'a.deleteOrderDetail', function (e) {
            e.preventDefault();
            orderEditor.deleteOrderDetail($(this));
        });
        $(document).on('click', '#reSendCustomerEmail', function (e) {
            e.preventDefault();
            orderEditor.reSendCustomerEmail($(this));
        });
        $(document).on('click', 'a.order_shipping_update', function (e) {
            e.preventDefault();
            orderEditor.updateShipping($(this));
        });
        $(document).on('change', '[id^=update_order_shipping_cost]', function () {
            orderEditor.changeShippingCostsRecalculate($(this));
        });
        $(document).on('change', '#update_order_shipping_new_carrier_id', function () {
            orderEditor.changeShippingMethod(this);
        });
        $(document).on('click', '#send_order_change_message', function (e) {
            e.preventDefault();
            orderEditor.sendOrderChangeMessage($(this));
        });
        $(document).on('click', '#update_wrapping', function (e) {
            e.preventDefault();
            orderEditor.updateWrapping($(this));
        });
        $(document).on('click', '#update_order_main_data', function (e) {
            e.preventDefault();
            orderEditor.updateOrderMainData($(this));
        });
        $(document).on('click', 'a.deleteShipping', function (e) {
            e.preventDefault();
            orderEditor.deleteShipping($(this));
        });
        $(document).on('click', '#add_payment', function (e) {
            e.preventDefault();
            orderEditor.addPayment($(this));
        });
        $(document).on('click', '#update_order_status', function (e) {
            e.preventDefault();
            orderEditor.updateOrderStatusHistory($(this));
        });
        $(document).on('click', '#update_order_payment', function (e) {
            e.preventDefault();
            orderEditor.updateOrderPayment($(this));
        });
    },
    recalculateProductRow: function ($row, field) {
        var taxRate = parseFloat($row.find('.tax_rate').val());
        var quantity = parseInt($row.find('.editProductQuantity').val());

        if (isNaN(taxRate)) taxRate = 0;

        var $taxIncl = $row.find('.editProductPriceTaxIncl');
        var $taxExcl = $row.find('.editProductPriceTaxExcl');

        var product_price = 0;

        switch (field) {
            case 0:
                product_price = parseFloat($taxExcl.val()) * (1 + taxRate / 100);
                $taxIncl.val(product_price.toFixed(6));
                break;
            case 1:
                product_price = parseFloat($taxIncl.val());
                $taxExcl.val((product_price - (product_price * taxRate / (100 + taxRate))).toFixed(6));
                break;
            case 2:
                product_price = parseFloat($taxIncl.val());
                break;
        }

        orderEditor.currentProduct.originalTaxIncl = product_price;
        this.updateTotalProduct($row, product_price, quantity);
    },
    addPayment: function ($el) {
        var line = $el.closest('tr');
        var payment_method = line.find('#order_payment_payment_method').val();
        var payment_module = line.find('#order_payment_payment_method_datalist option[value="' + payment_method + '"]').data('value_index');
        var data = {
            id_order: $el.data('current_order_id'),
            date_add: line.find('#order_payment_date').val(),
            payment_method: payment_method,
            payment_module: payment_module,
            transaction_id: line.find('#order_payment_transaction_id').val(),
            amount: line.find('#order_payment_amount').val(),
            id_currency: line.find('#order_payment_id_currency').val(),
            id_order_invoice: line.find('#order_payment_id_invoice').val(),
            action: 'addPayment'
        };

        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    deleteShipping: function ($el) {
        var result = true;

        if ($el.data('delete-message')) {
            result = window.confirm($el.data('delete-message'));
        }

        if (!result) return false;

        var data = {
            id_order: $el.data('order_id'),
            id_order_carrier: $el.data('order_carrier_id'),
            action: 'deleteShipping'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    updateOrderMainData: function ($el) {
        var parent = $el.closest('form');
        var data = {
            id_order: parent.find('[name=id_order]').val(),
            date_add: parent.find('[name=order_date_add]').val(),
            date_upd: parent.find('[name=order_date_upd]').val(),
            reference: parent.find('[name=order_reference]').val(),
            id_customer: parent.find('[name=order_customer_id]').val(),
            action: 'updateOrderMainData'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    $('#editOrderMainDataModal').modal('toggle');
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    updateWrapping: function ($el) {
        var parent = $el.closest('form');
        var data = {
            id_order: parent.find('[name=id_order]').val(),
            total_wrapping_tax_excl: parent.find('[name=total_wrapping_tax_excl]').val(),
            total_wrapping_tax_incl: parent.find('[name=total_wrapping_tax_incl]').val(),
            action: 'updateWrapping'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    $('#updateWrappingModal').modal('toggle');
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    sendOrderChangeMessage: function($el){
        var parent = $el.closest('form');
        var data = {
            id_order: parent.find('[name=id_order]').val(),
            msg: parent.find('textarea[name=order_change_message_msg]').val(),
            action: 'sendOrderChangeMessage'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    $('#afterOrderChangeMessage').modal('toggle');
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    changeShippingCostsRecalculate: function ($el) {
        var tax = parseFloat($("[name=shipping_cost_tax_rate]").val());
        var val_incl = parseFloat($("[name=shipping_cost_tax_incl]").val());
        var val_excl = parseFloat($("[name=shipping_cost_tax_excl]").val());

        if ($el.prop("name") === 'shipping_cost_tax_incl') {
            val_excl = val_incl - (val_incl * tax / (100 + tax));
        } else if ($el.prop("name") === 'shipping_cost_tax_excl') {
            val_incl = val_excl * (1 + (tax / 100));
        } else if ($el.prop("name") === 'shipping_cost_tax_rate') {
            val_incl = val_excl * (1 + (tax / 100));
        }

        var $container = $el.closest('.update_order_shipping_container');
        $container.find('[name=shipping_cost_tax_excl]').val(val_excl);
        $container.find('[name=shipping_cost_tax_incl]').val(val_incl);
        $container.find('[name=shipping_cost_tax_rate]').val(tax);
    },
    updateShipping: function ($el) {
        var $container = $el.closest('.update_order_shipping_container');
        var data = {
            id_order: $container.find('[name=id_order]').val(),
            id_carrier: $container.find('[name=shipping_new_carrier_id]').find('option:selected').val(),
            id_order_carrier: $container.find('[name="update_order_shipping[current_order_carrier_id]"]').val(),
            weight: $container.find('[name=shipping_weight]').val(),
            shipping_cost_tax_incl: $container.find('[name=shipping_cost_tax_incl]').val(),
            shipping_cost_tax_excl: $container.find('[name=shipping_cost_tax_excl]').val(),
            tracking_number: $container.find('#update_order_shipping_tracking_number').val(),
            current_tax: $container.find('[name=shipping_cost_tax_rate]').val(),
            date_add: $container.find('[name=shipping_date_add]').val(),
            action: 'updateShipping'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    changeShippingMethod: function ($_this) {
        var $container = $('.update_order_shipping_container');
        var optionSelected = $("option:selected", $_this);
        $container.find('[name=shipping_cost_tax_incl]').val(optionSelected.data('carrier_price_wt'));
        $container.find('[name=shipping_cost_tax_excl]').val(optionSelected.data('carrier_price'));
        $container.find('[name=shipping_cost_tax_rate]').val(optionSelected.data('carrier_tax_rate'));
    },
    updateOrderStatusHistory: function ($el) {
        var $form = $el.closest('.modal');
        var data = {
            id_history_state: $form.find('[name=id_history_state]').val(),
            status_date: $form.find('[name=status_date]').val(),
            employee: $form.find('[name=employee]').val(),
            order_state: $form.find('[name=order_state]').val(),
            action: 'updateOrderStatusHistory'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    updateOrderPayment: function ($el) {
        var $form = $el.closest('.modal');
        var data = {
            id_payment: $form.find('[name=id_payment]').val(),
            payment_date: $form.find('[name=payment_date]').val(),
            transaction: $form.find('[name=transaction]').val(),
            action: 'updateOrderPayment'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    reSendCustomerEmail: function ($el) {
        var result = true;

        if ($el.data('delete-message')) {
            result = window.confirm($el.data('delete-message'));
        }

        if (!result) return false;

        var data = {
            id_order: $el.data('order_id'),
            action: 'reSendCustomerEmail'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    deleteOrderDetail: function ($el) {
        var result = true;

        if ($el.data('delete-message')) {
            result = window.confirm($el.data('delete-message'));
        }

        if (!result) return false;

        var data = {
            id_order: $el.data('order-id'),
            id_order_detail: $el.data('order-detail-id'),
            action: 'deleteOrderDetail'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    deleteOrderPayment: function ($el) {
        var result = true;

        if ($el.data('delete-message')) {
            result = window.confirm($el.data('delete-message'));
        }

        if (!result) return false;

        var data = {
            id_order_payment: $el.data('order_payment_id'),
            action: 'deleteOrderPayment'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    deleteOrderDocument: function ($el) {
        var result = true;

        if ($el.data('delete-message')) {
            result = window.confirm($el.data('delete-message'));
        }

        if (!result) return false;

        var data = {
            id_order: $el.data('order_id'),
            id_order_document: $el.data('order_document_id'),
            order_document_type: $el.data('order_document_type'),
            action: 'deleteOrderDocument'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    updateOrderDocument: function ($el) {
        var $form = $el.closest('.modal');
        var data = {
            id_order: $form.find('[name=id_order]').val(),
            id_order_document: $form.find('[name=document_id]').val(),
            order_document_type: $form.find('[name=document_type]').val(),
            document_date: $form.find('[name=document_date]').val(),
            action: 'updateOrderDocument'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    deleteOrderHistory: function ($el) {
        var result = true;

        if ($el.data('delete-message')) {
            result = window.confirm($el.data('delete-message'));
        }

        if (!result) return false;

        var data = {
            id_order: $el.data('order_id'),
            id_order_history: $el.data('order_history_id'),
            action: 'deleteOrderHistory'
        };
        $.post(
            admin_module_controller,
            data,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    recalculateProductTax: function ($row) {
        // #FIXME We need to add the Round mode!!!
        var $taxIncl = $row.find('.editProductPriceTaxIncl');
        var $taxExcl = $row.find('.editProductPriceTaxExcl');
        var quantity = parseInt($row.find('.editProductQuantity').val());
        var taxInclFromCache = orderEditor.currentProduct.originalTaxIncl;
        var taxRate = parseFloat($row.find('.tax_rate').val());
        var reductionPercent = parseFloat($row.find('.reduction_percent').val());
        var originalReductionPercent = orderEditor.currentProduct.originalReductionPercent;

        if (isNaN(reductionPercent)) reductionPercent = 0;
        if (isNaN(taxRate)) taxRate = 0;
        /** get the new price from current */
        var originalPrice = 100 * taxInclFromCache / (100 - originalReductionPercent);
        var newPrice = originalPrice * (100 - reductionPercent) / 100;

        $taxIncl.val(newPrice.toFixed(order_edit_compute_precision));
        $taxExcl.val((newPrice - (newPrice * taxRate / (100 + taxRate))).toFixed(6));

        this.updateTotalProduct($row, newPrice, quantity);
    },
    updateTotalProduct: function ($row, calculateTaxIncl, quantity) {
        var $totalProduct = $row.find('.editProductTotalPrice');
        $totalProduct.text((calculateTaxIncl * quantity).toFixed(order_edit_compute_precision));
    },
    saveProductDetail: function ($el) {
        var row = $el.closest('tr.editProductRow');
        var params = {
            id_order: $el.data('order-id'),
            id_order_detail: $el.data('order-detail-id'),
            product_name: row.find('.product_name').val(),
            product_reference: row.find('.product_reference').val(),
            product_supplier_reference: row.find('.product_supplier_reference').val(),
            product_weight: row.find('.product_weight').val(),
            reduction_percent: row.find('.reduction_percent').val(),
            tax_rate: row.find('.tax_rate').val(),
            price_tax_incl: row.find('.editProductPriceTaxIncl').val(),
            price_tax_excl: row.find('.editProductPriceTaxExcl').val(),
            quantity: row.find('.editProductQuantity').val(),
            invoice: row.find('.editProductInvoice').val(),
            action: 'saveProductDetail'
        };

        $.post(
            admin_module_controller,
            params,
            function (data, status, xhr) {
                if (data.success) {
                    $.growl.notice({message: data.success_msg});
                    location.reload();
                } else {
                    $.growl.error({message: data.error_msg});
                }
            }, 'json');
    },
    disableSaveButton: function () {
        $('.productEditSaveBtn_custom').prop('disabled', true);
    },
    enableSaveButton: function () {
        $('.productEditSaveBtn_custom').prop('disabled', false);
    },
    fillProductFields: function ($el) {
        var id_order_detail = $el.data('order-detail-id');
        var id_order = $el.data('order-id');
        var current_row = $('tr#editOrderProduct_' + id_order_detail);
        current_row.find('.product_name').val($el.data('product_name'));
        current_row.find('.product_reference').val($el.data('product_reference'));
        current_row.find('.product_supplier_reference').val($el.data('product_supplier_reference'));
        current_row.find('.product_weight').val($el.data('product_weight'));
        current_row.find('.reduction_percent').val($el.data('reduction_percent'));
        current_row.find('.tax_rate').val($el.data('tax_rate'));
        current_row.find('.productEditSaveBtn_custom').data({'order-detail-id': id_order_detail, 'order-id': id_order});

        // Caching the original values
        this.currentProduct = {
            originalTaxIncl: parseFloat(current_row.find('.editProductPriceTaxIncl').val()),
            originalReductionPercent: parseFloat(current_row.find('.reduction_percent').val())
        }
    }
};

document.addEventListener('DOMContentLoaded', function () {
    orderEditor.init();
});
