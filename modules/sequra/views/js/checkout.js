/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    SeQura Tech <prestashop@sequra.com>
 * @copyright Since 2013 SeQura WorldWide SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
SequraIdentificationPopupLoader = {
    url: '',
    loadForm: function () {
        var params = {
            ajax:true,
            random:Math.random(),
        };
        if(this.product){
            params.product = this.product;
        }
        if(this.campaign){
            params.campaign = this.campaign;
        }
        jQuery.ajax({
            context: this,
            url: this.url,
            data: params,
            method: 'POST',
            beforeSend: function (xhr) {
                this.showLoadingAnimation()
            },
            success: this.loadFormsuccess,
            error: function () {
                alert("Lo sentimos, método de pago no disponible. Por favor, seleccione otro.");
            }
        });
    },
    loadFormsuccess: function (response) {
        jQuery('#sq-identification-' + this.product).remove();
        jQuery('body').append(response);
        this.showFor(this.product);
    },
    showForm: function () {
        this.removeForm();
        this.loadForm();
    },
    showFor: function () {
        if (window.SequraFormInstance) {
            var self = this;
            window.SequraFormInstance.setCloseCallback(function(){
                if(typeof hide_progress === "function"){
                    hide_progress();
                }
                if(typeof self.closeCallback === "function"){
                    self.closeCallback();
                }
                window.SequraFormInstance.defaultCloseCallback();
            });
            window.SequraFormInstance.setElement("sq-identification-" + this.product);
            window.SequraFormInstance.show();
            this.hideLoadingAnimation();
        } else {
            var context = this;
            window.setTimeout(function () {
                context.showFor()
            }, 100);
        }
    },
    removeForm: function () {
        jQuery('#sq-identification-' + this.product).remove();
    },

    opcShowForm: function (url , product, campaign) {
        SequraIdentificationPopupLoader.url = url;
        SequraIdentificationPopupLoader.product = product;
        SequraIdentificationPopupLoader.campaign = campaign;
        SequraIdentificationPopupLoader.closeCallback = function() {
            window.location.reload();
        };
        SequraIdentificationPopupLoader.showForm();
    },

    showLoadingAnimation: function(){
        jQuery('body').append('<div id="lds-sequra-container"><div><div class="lds-sequra"><div></div><div></div><div></div></div></div></div>');
    },

    hideLoadingAnimation: function(){
        jQuery('#lds-sequra-container').remove();
    }
};
