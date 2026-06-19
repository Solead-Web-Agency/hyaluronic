/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*/

$(document).ready(function() {
    if ($('body').attr('id') == 'product') {
        if (typeof bpbc_blocked !== 'undefined' && bpbc_blocked && bpbc_blocked_text) {
            if ($(document).find('.product-add-to-cart .add-to-cart').length) {
                if (!$(document).find('.product-add-to-cart .add-to-cart').attr('data-has-tooltip')) {
                    $(document).find('.product-add-to-cart .add-to-cart').tooltipster({
                        animation: 'fade',
                        delay: 200,
                        theme: 'tooltipster-borderless',
                        content: bpbc_blocked_text,
                        maxWidth: 350,
                    });
                }
                $(document).find('.product-add-to-cart .add-to-cart').tooltipster('open');
                $(document).find('.product-add-to-cart .add-to-cart').attr('data-has-tooltip', true);
            }
            if ($(document).find('.box-cart-bottom').length) {
                setTimeout(function() {
                    if (!$(document).find('.box-cart-bottom').attr('data-has-tooltip')) {
                        $(document).find('.box-cart-bottom').tooltipster({
                            animation: 'fade',
                            delay: 200,
                            theme: 'tooltipster-borderless',
                            content: bpbc_blocked_text,
                            maxWidth: 350,
                        });
                    }
                    $(document).find('.box-cart-bottom').tooltipster('open');
                    $(document).find('.box-cart-bottom').attr('data-has-tooltip', true);
                }, 500);
            }
        }
    }
    $(document).on('hover', '.ajax_block_product', function(e) {
        var $this = $(this);
        if ($this.find('.bpbc_product_list[data-message]').length && !$this.find('.product-image-container').attr('data-has-tooltip')) {
            $this.find('.product-image-container').tooltipster({
                animation: 'fade',
                delay: 200,
                theme: 'tooltipster-borderless',
                content: '',
                maxWidth: 350,
                functionBefore: function(instance, helper) {
                    instance.content($(helper.origin).closest('.ajax_block_product').find('.bpbc_product_list[data-message]').data('message'));
                }
            });
            $this.find('.product-image-container').tooltipster('open');
            $this.find('.product-image-container').attr('data-has-tooltip', true);
        }
    });
    $(document).on('mouseleave', '.ajax_block_product', function(e) {
        var $this = $(this);
        if ($this.find('.bpbc_product_list[data-message]').length) {
            $this.find('.product-image-container').tooltipster('close');
        }
    });
    $(document).on('mouseover', '.modal-content .product-add-to-cart', function(e) {
        var $this = $(this);
        if ($this.closest('.modal-content').find('.bpbc_product_list[data-message]').length) {
            if (!$this.closest('.modal-content').find('.add-to-cart').attr('data-has-tooltip')) {
                $this.closest('.modal-content').find('.add-to-cart').tooltipster({
                    animation: 'fade',
                    delay: 200,
                    theme: 'tooltipster-borderless',
                    content: '',
                    maxWidth: 350,
                    functionBefore: function(instance, helper) {
                        instance.content($(helper.origin).closest('.modal-content').find('.bpbc_product_list[data-message]').data('message'));
                    }
                });
                $this.closest('.modal-content').find('.add-to-cart').tooltipster('open');
                $this.closest('.modal-content').find('.add-to-cart').attr('data-has-tooltip', true);
            } else {
                $this.closest('.modal-content').find('.add-to-cart').tooltipster('open');
            }
        }
    });
    $(document).on('mouseleave', '.modal-content .product-add-to-cart', function(e) {
        var $this = $(this);
        if ($this.closest('.modal-content').find('.bpbc_product_list[data-message]').length) {
            $this.closest('.modal-content').find('.add-to-cart').tooltipster('close');
        }
    });
});