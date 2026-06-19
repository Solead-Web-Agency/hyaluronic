/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

'use strict';
var SdevAtosApp = angular.module('SdevAtosApp', ['angular-bind-html-compile']).config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
});

function getGlobalFunctionVars (loading = false) {
    return {
        'loading': loading,
        'is_success': false,
        'has_error': false,
        'front_errors': false,
        'back_errors': false
    };
};
var global_function_vars = {
    'general': getGlobalFunctionVars(),
    'contract': getGlobalFunctionVars(),
    'payment_method': getGlobalFunctionVars()
};

var global_default_configuration = {
    'debug_mode': '0',
    'ip_filtering' : '',
    'redirection': 'shop',
    'payment_errors': 'save'
};

var global_default_contract = {
    'sips_version': '2.0',
    'is_test_mode': '1',
    'exe_mode': 'automatic',
    'exe_version': '0',
    'bank': '0',
    'merchant_id': '',
    'secrete_key': '',
    'key_version': '',
    'transaction_reference': 'reference',
    'has_3d_secure': '0'
};

var global_default_payment_method = {
    'id_contract': '0',
    'name': '',
    'method': '0',
    'authentication_key': '',
    'settlement_mode': '',
    'settlement_mode_version': '',
    'payment_options': [],
    'is_enabled': '1',
    'min_amount': '0.000000',
    'max_amount': '1000000.000000',
    'has_3d_secure_from': '0.000000',
    'cashing_mode': 'AUTHOR_CAPTURE',
    'delay': '0',
    'first_cashing_percentage': '100.000000',
    'delay_first_payment': '0'
};

SdevAtosApp.controller('SdevAtosController', function ($scope, $http, $rootScope, $parse) {
    $scope.is_js_translations_loaded = false;
    $scope.js_translations = {
        'error_message': ''
    };
    $scope.ps15 = false;

    /**
     * Get JavaScript translations.
     *
     * @param {string} link - URL to call.
     */
    $scope.getJsDefL = function (link) {
        $http({
            method: 'POST',
            url: link,
            params: {
                ajax: '1',
                action: 'getJsDefL'
            }
        }).then(function successCallback(response) {
            var data = response.data;
            if (data.is_js_translations_loaded) {
                $scope.is_js_translations_loaded = data.is_js_translations_loaded;
                $scope.js_translations = data.js_translations;
                $scope.ps15 = data.ps15;
            }
        }, function errorCallback(response) {
            alert('Error on translations loading. Please contact us if the error persists.')
        });
    };

    $scope.is_tabs_loaded = false;
    $scope.tabs = {};

    /**
     * Get tabs.
     */
    $scope.getTabs = function () {
        $http({
            method: 'GET',
            url: location.href,
            params: {
                ajax: '1',
                action: 'getTabs'
            }
        }).then(function successCallback(response) {
            $scope.is_tabs_loaded = true;
            $scope.tabs = response.data.tabs;
            angular.forEach($scope.tabs, function (tab, class_name) {
                $http({
                    method: 'GET',
                    url: tab.link + '&ajax=1&action=display'
                }).then(function successCallback(response) {
                    $scope.tabs[class_name].template = response.data;
                });
            });
        }, function errorCallback(response) {
            alert($scope.js_translations['ErrorMessage']);
        });
    };

    $scope.current_tab = null;

    /**
     * Select tab.
     *
     * @param {bool} is_first - Define if it is the first tab.
     * @param {string} current_tab - Current tab name.
     * @return {bool}
     */
    $scope.selectTab = function (is_first, current_tab) {
        if (is_first && !$scope.current_tab) {
            $scope.current_tab = current_tab;
            return true;
        } else if (current_tab == $scope.current_tab) {
            return true;
        } else {
            return false;
        }
    };

    /**
     * Change tab.
     *
     * @param {string} new_current_tab - New current tab name.
     */
    $scope.changeTab = function (new_current_tab) {
        $scope.current_tab = new_current_tab;
    };

    $scope.configuration = {
        'general': false,
        'contract': false,
        'payment_method': false
    };

    $scope.read_configuration = angular.copy(global_function_vars);
    $scope.update_configuration = angular.copy(global_function_vars);
    $scope.delete_configuration = angular.copy(global_function_vars);
    $scope.get_configuration = angular.copy(global_function_vars);

    /**
     * Reset all alert messages.
     */
    $scope.resetAllAlerts = function () {
        $scope.read_configuration = angular.copy(global_function_vars);
        $scope.update_configuration = angular.copy(global_function_vars);
        $scope.delete_configuration = angular.copy(global_function_vars);
        $scope.get_configuration = angular.copy(global_function_vars);
    };

    /**
     * Init all parameters (configuration, contracts and payment methods).
     */
    $scope.initParameters = function () {
        $scope.is_parameters_loading = true;
        $scope.getConfiguration();
        $scope.readContract();
        $scope.readPaymentMethod();
        $scope.checkParametersLoading();
    };

    $scope.is_parameters_loading = false;
    $scope.checkParametersLoading = function () {
        if ($scope.get_configuration.general.loading
            || $scope.read_configuration.contract.loading
            || $scope.read_configuration.payment_method.loading
        ) {
            setTimeout(function () {
                $scope.checkParametersLoading();
            }, 250);
        } else {
            $scope.is_parameters_loading = false;
            $scope.$apply();
        }
    };

    /**
     * Get configuration.
     */
    $scope.getConfiguration = function () {
        $scope.get_configuration.general = getGlobalFunctionVars(true);
        $http({
            method: 'POST',
            url: $scope.tabs['AdminConfigsdevatos'].link,
            params: {
                ajax: '1',
                action: 'getConfiguration'
            }
        }).then(function successCallback(response) {
            var data = response.data;
            $scope.get_configuration.general.is_success = data.is_success;
            $scope.get_configuration.general.has_error = data.has_error;
            $scope.get_configuration.general.back_errors = data.errors;
            if (data.default_configuration) {
                $scope.configuration.general = angular.copy(global_default_configuration);
                $scope.configuration.general.binary_path = data.binary_path;
                $scope.configuration.general.pathfile_path = data.pathfile_path;
            } else {
                $scope.configuration.general = data.configuration;
            }
        }, function errorCallback(response) {
            alert($scope.js_translations['ErrorMessage'])
        }).finally(function () {
            $scope.get_configuration.general.loading = false;
        });
    };

    /**
     * Update configuration.
     */
    $scope.updateConfiguration = function () {
        $scope.update_configuration.general = getGlobalFunctionVars(true);
        setTimeout(function () {
            $http({
                method: 'POST',
                url: $scope.tabs['AdminConfigsdevatos'].link,
                params: {
                    ajax: '1',
                    action: 'updateConfiguration'
                },
                data: {
                    configuration: $scope.configuration.general
                }
            }).then(function successCallback(response) {
                var data = response.data;
                $scope.update_configuration.general.is_success = data.is_success;
                $scope.update_configuration.general.has_error = data.has_error;
                $scope.update_configuration.general.back_errors = data.errors;
            }, function errorCallback(response) {
                alert($scope.js_translations['ErrorMessage'])
            }).finally(function () {
                $scope.update_configuration.general.loading = false;
            });
        }, 250);
    };

    /**
     * Adds the current IP address to the list for IP filtering.
     */
    $scope.addIpAddress = function () {
        $scope.add_ip_address = getGlobalFunctionVars(true);
        setTimeout(function () {
            $http({
                method: 'POST',
                url: $scope.tabs['AdminConfigsdevatos'].link,
                params: {ajax: 1, action: 'getIpAddress'},
            }).then(function successCallback(response) {
                var data = response.data;
                if (typeof $scope.configuration.general['ip_filtering'] === 'undefined') {
                    $scope.configuration.general['ip_filtering'] = '';
                }
                if (!$scope.configuration.general['ip_filtering']) {
                    $scope.configuration.general['ip_filtering'] = data['ip_address'];
                } else if (!$scope.configuration.general['ip_filtering'].includes(data['ip_address'])) {
                    // Includes the current IP address if it is not already into the list.
                    var ipAddressesList = $scope.configuration.general['ip_filtering'].split(',');
                    ipAddressesList.push(data['ip_address']);
                    $scope.configuration.general['ip_filtering'] = ipAddressesList.join(', ');
                }
            }, function errorCallback(response) {
                alert($scope.js_translations['ErrorMessage']);
            }).finally(function () {
                $scope.add_ip_address.loading = false;
            });
        }, 250);
    };

    $scope.contracts_number = 0;

    /**
     * Read all contracts.
     */
    $scope.readContract = function () {
        $scope.read_configuration.contract = getGlobalFunctionVars(true);
        $http({
            method: 'POST',
            url: $scope.tabs['AdminConfigsdevatos'].link,
            params: {
                ajax: '1',
                action: 'readContract'
            }
        }).then(function successCallback(response) {
            $scope.contracts_number = response.data.contracts_number;
            $scope.contracts = response.data.contracts;
        }, function errorCallback(response) {
            alert($scope.js_translations['ErrorMessage']);
        }).finally(function () {
            $scope.read_configuration.contract.loading = false;
        });
    };

    $scope.is_contract_edition_displayed = false;
    $scope.openContractEdition = function (contract = false) {
        $scope.resetAllAlerts();
        $scope.is_contract_edition_displayed = true;
        $scope.is_contract_edition_minimized = false;
        if (contract) {
            $scope.configuration.contract = angular.copy(contract);
        } else {
            $scope.configuration.contract = angular.copy(global_default_contract);
        }
    };
    $scope.closeContractEdition = function () {
        $scope.is_contract_edition_displayed = false;
        $scope.configuration.contract = false;
        $scope.update_configuration.contract = getGlobalFunctionVars();
    }

    $scope.is_contract_edition_minimized = false;
    $scope.minimizeContractEdition = function () {
        $scope.is_contract_edition_minimized = true;
    };
    $scope.maximizeContractEdition = function () {
        $scope.is_contract_edition_minimized = false;
    };

    $scope.has_certificate_file_error = false;
    $scope.has_certificate_format_error = false;
    $scope.initMerchantId = function () {
        $scope.has_certificate_file_error = false;
        $scope.has_certificate_format_error = false;
        var file = angular.element('[file="configuration.contract.certificate"]')[0].files[0];
        if (typeof file !== 'undefined') {
            var certificate_number = file.name.split('.').pop();
            if (certificate_number.length == 15 && certificate_number.match(/[0-9]/g)) {
                $scope.configuration.contract.merchant_id = certificate_number;
            } else {
                $scope.has_certificate_format_error = true;
                angular.element('[file="configuration.contract.certificate"]').val(null);
            }
        } else if (typeof $scope.configuration.contract.id === 'undefined') {
            $scope.has_certificate_file_error = true
        }
    };

    /**
     * Update a contract.
     */
    $scope.updateContract = function () {
        $scope.update_configuration.contract = getGlobalFunctionVars();
        var errors = {};

        if ($scope.configuration.contract.bank == '0') {
            $scope.update_configuration.contract.has_error = true;
            errors.bank = true;
        }

        if ($scope.configuration.contract.sips_version == '2.0') {
            if (typeof $scope.configuration.contract.merchant_id === 'undefined'
                || !$scope.configuration.contract.merchant_id.match(/[0-9]{15}/g)
                || $scope.configuration.contract.merchant_id.length != 15
            ) {
                $scope.update_configuration.contract.has_error = true;
                errors.merchant_id = true;
            }
            if (!$scope.configuration.contract.secrete_key) {
                $scope.update_configuration.contract.has_error = true;
                errors.secrete_key = true;
            }
            if (typeof $scope.configuration.contract.key_version === 'undefined'
                || !$scope.configuration.contract.key_version.match(/[0-9]/g)
                || $scope.configuration.contract.key_version.length > 10
            ) {
                $scope.update_configuration.contract.has_error = true;
                errors.key_version = true;
            }
        } else {
            if ($scope.configuration.contract.exe_mode == 'manual' && $scope.configuration.contract.exe_version == '') {
                $scope.update_configuration.contract.has_error = true;
                errors.exe_version = true;
            }

            $scope.initMerchantId();
            if (typeof $scope.configuration.contract.id_contract === 'undefined') {
                if ($scope.has_certificate_file_error) {
                    $scope.update_configuration.contract.has_error = true;
                    errors.has_certificate_file_error = true;
                }
            }
            if ($scope.has_certificate_format_error) {
                $scope.update_configuration.contract.has_error = true;
                errors.has_certificate_format_error = true;
            }
        }

        if ($scope.update_configuration.contract.has_error) {
            $scope.update_configuration.contract.front_errors = errors;
        } else {
            $scope.update_configuration.contract = getGlobalFunctionVars(true);

            var form_data = new FormData();
            form_data.append('contract', JSON.stringify($scope.configuration.contract));
            form_data.append('file', $scope.configuration.contract.certificate);

            setTimeout(function () {
                $http({
                    url: $scope.tabs['AdminConfigsdevatos'].link,
                    method: 'POST',
                    headers: {
                        'Content-Type': undefined
                    },
                    transformRequest: function () {
                        return form_data;
                    },
                    params: {
                        ajax: '1',
                        action: 'updateContract'
                    }
                }).then(function successCallback(response) {
                    var data = response.data;
                    $scope.update_configuration.contract.is_success = data.is_success;
                    $scope.update_configuration.contract.has_error = data.has_error;
                    $scope.update_configuration.contract.back_errors = data.errors;
                    if (data.is_success) {
                        $scope.readContract();
                        $scope.is_contract_edition_displayed = false;
                        $scope.configuration.contract = false;
                    }
                }, function errorCallback(response) {
                    alert($scope.js_translations['ErrorMessage']);
                }).finally(function () {
                    $scope.update_configuration.contract.loading = false;
                });
            }, 250);
        }
    };

    /**
     * Delete a contract.
     */
    $scope.deleteContract = function (contract) {
        if (confirm($scope.js_translations['DeletingContract'] + ' (#' + contract.id_contract + ')')) {
            $scope.delete_configuration.contract = getGlobalFunctionVars(true);
            setTimeout(function () {
                $http({
                    method: 'POST',
                    url: $scope.tabs['AdminConfigsdevatos'].link,
                    params: {
                        ajax: '1',
                        action: 'deleteContract'
                    },
                    data: {
                        id_contract: contract.id_contract
                    }
                }).then(function successCallback(response) {
                    var data = response.data;
                    $scope.delete_configuration.contract.is_success = data.is_success;
                    $scope.delete_configuration.contract.has_error = data.has_error;
                    if (data.is_success) {
                        $scope.closeContractEdition();
                        $scope.readContract();
                        if (data.has_payment_method_deleted) {
                            $scope.readPaymentMethod();
                        }
                    }
                }, function errorCallback(response) {
                    alert($scope.js_translations['ErrorMessage']);
                }).finally(function () {
                    $scope.delete_configuration.contract.loading = false;
                });
            }, 250);
        }
    };

    $scope.correct_exe_rights = getGlobalFunctionVars();
    $scope.correctExeRights = function () {
        $scope.correct_exe_rights = getGlobalFunctionVars(true);
        setTimeout(function () {
            $http({
                method: 'POST',
                url: $scope.tabs['AdminConfigsdevatos'].link,
                params: {
                    ajax: '1',
                    action: 'correctExeRights'
                }
            }).then(function successCallback(response) {
                var data = response.data;
                $scope.correct_exe_rights.is_success = data.is_success;
                $scope.correct_exe_rights.has_error = data.has_error;
                $scope.correct_exe_rights.back_errors = data.errors;
            }, function errorCallback(response) {
                alert($scope.js_translations['ErrorMessage']);
            }).finally(function () {
                $scope.correct_exe_rights.loading = false;
            });
        }, 250);
    };

    $scope.payment_methods_number = 0;

    /**
     * Read all payment methods.
     */
    $scope.readPaymentMethod = function () {
        $scope.read_configuration.payment_method = getGlobalFunctionVars(true);
        $http({
            method: 'POST',
            url: $scope.tabs['AdminConfigsdevatos'].link,
            params: {
                ajax: '1',
                action: 'readPaymentMethod'
            }
        }).then(function successCallback(response) {
            $scope.payment_methods_number = response.data.payment_methods_number;
            $scope.payment_methods = response.data.payment_methods;
        }, function errorCallback(response) {
            alert($scope.js_translations['ErrorMessage']);
        }).finally(function () {
            $scope.read_configuration.payment_method.loading = false;
        });
    };

    $scope.is_payment_method_edition_displayed = false;
    $scope.openPaymentMethodEdition = function (payment_method = false) {
        $scope.resetAllAlerts();

        if ($scope.ps15) {
            $(".method-shop-select").find("option").each(
                function()
                {
                    $(this).attr('selected', false);
                }
            );
        } else {
            $("#method-shop-tree").find(":input[type=checkbox]").each(
                function()
                {
                    $(this).prop("checked", false);
                    $(this).parent().removeClass("tree-selected");
                }
            );
        }

        $scope.is_payment_method_edition_displayed = true;
        if (payment_method) {
            $scope.configuration.payment_method = angular.copy(payment_method);

            angular.forEach($scope.configuration.payment_method.shops, function (shop_id, shop) {
                if ($scope.ps15) {
                    $(".method-shop-select option[value='"+shop_id+"']").attr('selected', 'selected');
                } else {
                    $("#method-shop-tree input[type=checkbox][name^='checkBoxShopAsso'][value='"+shop_id+"']").prop("checked", true);
                    $("#method-shop-tree input[type=checkbox][name^='checkBoxShopAsso'][value='"+shop_id+"']").parent().addClass("tree-selected");
                }
            });
        } else {
            $scope.configuration.payment_method = angular.copy(global_default_payment_method);

            if ($scope.ps15) {
                $(".method-shop-select").find("option").each(
                    function()
                    {
                        $(this).attr('selected', 'selected');
                    }
                );
            } else {
                $("#method-shop-tree").find(":input[type=checkbox]").each(
        			function()
        			{
        				$(this).prop("checked", true);
        				$(this).parent().addClass("tree-selected");
        			}
        		);
            }
        }
        $scope.checkPaymentMethodMethod(true);
    };
    $scope.closePaymentMethodEdition = function () {
        $scope.is_payment_method_edition_displayed = false;
        $scope.configuration.payment_method = false;
        $scope.update_configuration.payment_method = getGlobalFunctionVars();
    };

    $scope.is_payment_method_edition_minimized = false;
    $scope.minimizePaymentMethodEdition = function () {
        $scope.is_payment_method_edition_minimized = true;
    };
    $scope.maximizePaymentMethodEdition = function () {
        $scope.is_payment_method_edition_minimized = false;
    };

    $scope.payment_method_list = {};

    /**
     * Check the contract linked to the payment method and get the payment method list with the contract ID.
     */
    $scope.checkPaymentMethodContract = function () {
        if ($scope.configuration.payment_method.id_contract
            && $scope.update_configuration.payment_method.front_errors.id_contract
        ) {
            $scope.update_configuration.payment_method.front_errors.id_contract = false;
        }

        var sips_version = false;
        if (typeof $scope.contracts[$scope.configuration.payment_method.id_contract] !== 'undefined') {
            sips_version = $scope.contracts[$scope.configuration.payment_method.id_contract].sips_version;
        }

        $http({
            method: 'POST',
            url: $scope.tabs['AdminConfigsdevatos'].link,
            params: {
                ajax: '1',
                action: 'getPaymentMethodList'
            },
            data: {
                sips_version: sips_version
            }
        }).then(function successCallback(response) {
            $scope.payment_method_list = response.data.payment_method_list;
            if ($scope.configuration.payment_method && !Object.keys($scope.payment_method_list).includes($scope.configuration.payment_method.method)) {
                $scope.configuration.payment_method.method = '0';
            }
        }, function errorCallback(response) {
            alert($scope.js_translations['ErrorMessage']);
        });
    };

    /**
     * Check the payment method name.
     */
    $scope.checkPaymentMethodName = function () {
        if ($scope.configuration.payment_method.name
            && $scope.update_configuration.payment_method.front_errors.name
        ) {
            $scope.update_configuration.payment_method.front_errors.name = false;
        }
    }

    $scope.is_first_cashing_percentage_displayed = false;

    /**
     * Check the payment method and the number of payments.
     *
     * @param bool init - Define if this function is launched by ng-init directive.
     */
    $scope.checkPaymentMethodMethod = function (init = false) {
        if ($scope.configuration.payment_method.method
            && $scope.update_configuration.payment_method.front_errors.method
        ) {
            $scope.update_configuration.payment_method.front_errors.method = false;
        }

        var reg = /([2-9]{1}x)/g;
        if ($scope.configuration.payment_method.method.match(reg)) {
            $scope.is_first_cashing_percentage_displayed = true;
            if (!init || (init && !$scope.configuration.payment_method.id_payment_method)) {
                var match = reg.exec($scope.configuration.payment_method.method);
                $scope.configuration.payment_method.first_cashing_percentage = parseFloat(100 / parseInt(match[0].replace(/[(_)(x)]/g, ''))).toFixed(6);
            }
        } else {
            $scope.is_first_cashing_percentage_displayed = false;
            $scope.configuration.payment_method.first_cashing_percentage = parseFloat(100).toFixed(6);
        }
    };

    /**
     * Check if the payment method is Franfinance (3X or 4X).
     *
     * @return bool
     */
    $scope.isPaymentMethodFranfinance = function () {
        if ($scope.configuration.payment_method.method.match(/franfinance/)) {
            return true;
        } else {
            return false;
        }
    };

    /**
     * Check if the payment method is Facilypay (1X, 3X or 4X).
     *
     * @return bool
     */
    $scope.isPaymentMethodFacilypay = function () {
        if ($scope.configuration.payment_method.method.match(/facilypay/)) {
            return true;
        } else {
            return false;
        }
    };

    /**
     * Check if the payment method is Franfinance (3X or 4X).
     *
     * @return bool
     */
    $scope.isPaymentMethodCofinoga = function () {
        if ($scope.configuration.payment_method.method.match(/cofinoga/)) {
            return true;
        } else {
            return false;
        }
    };

    /**
     * Check the payment method authentication key.
     */
    $scope.checkPaymentMethodAuthenticationKey = function () {
        if (typeof $scope.configuration.payment_method.authentication_key !== 'undefined' && $scope.configuration.payment_method.authentication_key) {
            $scope.update_configuration.payment_method.front_errors.authentication_key = false;
        }
    };

    /**
     * Check the payment method settlement mode.
     */
    $scope.checkPaymentMethodSettlementMode = function () {
        if ($scope.update_configuration.payment_method.front_errors
            && typeof $scope.configuration.payment_method.settlement_mode !== 'undefined'
            && $scope.configuration.payment_method.settlement_mode
            && $scope.configuration.payment_method.settlement_mode.length <= 20
        ) {
            $scope.update_configuration.payment_method.front_errors.settlement_mode = false;
        }
    };

    /**
     * Check the payment method settlement mode version.
     */
    $scope.checkPaymentMethodSettlementModeVersion = function () {
        if ($scope.update_configuration.payment_method.front_errors
            && typeof $scope.configuration.payment_method.settlement_mode_version !== 'undefined'
            && $scope.configuration.payment_method.settlement_mode_version
            && $scope.configuration.payment_method.settlement_mode_version.match(/[0-9]/)
            && $scope.configuration.payment_method.settlement_mode_version.length <= 3
        ) {
            $scope.update_configuration.payment_method.front_errors.settlement_mode_version = false;
        }
    };

    /**
     * Check the payment method min amount.
     */
    $scope.checkPaymentMethodMinAmount = function () {
        if ($scope.configuration.payment_method.min_amount
            && $scope.update_configuration.payment_method.front_errors.min_amount
        ) {
            $scope.update_configuration.payment_method.front_errors.min_amount = false;
        }
    }

    /**
     * Check the payment method max amount.
     */
    $scope.checkPaymentMethodMaxAmount = function () {
        if ($scope.configuration.payment_method.max_amount
            && $scope.update_configuration.payment_method.front_errors.max_amount
        ) {
            $scope.update_configuration.payment_method.front_errors.max_amount = false;
        }
    }

    /**
     * Check the payment method 3D-Secure min amount.
     */
    $scope.checkPaymentMethodHas3dSecureFrom = function () {
        if ($scope.configuration.payment_method.has_3d_secure_from
            && $scope.update_configuration.payment_method.front_errors.has_3d_secure_from
        ) {
            $scope.update_configuration.payment_method.front_errors.has_3d_secure_from = false;
        }
    }

    /**
     * Check the payment method delay.
     */
    $scope.checkPaymentMethodDelay = function () {
        if ($scope.configuration.payment_method.delay
            && $scope.update_configuration.payment_method.front_errors.delay
        ) {
            $scope.update_configuration.payment_method.front_errors.delay = false;
        }
    }

    /**
     * Check the payment method first cashing percentage.
     */
    $scope.checkPaymentMethodFirstCashingPercentage = function () {
        if ($scope.configuration.payment_method.first_cashing_percentage
            && $scope.update_configuration.payment_method.front_errors.first_cashing_percentage
        ) {
            $scope.update_configuration.payment_method.front_errors.first_cashing_percentage = false;
        }
    }

    /**
     * Update a payment method.
     */
    $scope.updatePaymentMethod = function () {
        $scope.update_configuration.payment_method = getGlobalFunctionVars();
        var reg = /[0-9]+([\.][0-9]+)?/g;
        var errors = {};

        if ($scope.configuration.payment_method.id_contract == '0') {
            $scope.update_configuration.payment_method.has_error = true;
            errors.id_contract = true;
        }
        if ($scope.configuration.payment_method.name == '') {
            $scope.update_configuration.payment_method.has_error = true;
            errors.name = true;
        }
        if ($scope.configuration.payment_method.id_contract > 0 && $scope.configuration.payment_method.method == '0') {
            $scope.update_configuration.payment_method.has_error = true;
            errors.method = true;
        }
        if (($scope.configuration.payment_method.method.match(/franfinance/))
            && (typeof $scope.configuration.payment_method.authentication_key === 'undefined'
                || !$scope.configuration.payment_method.authentication_key
            )
        ) {
            $scope.update_configuration.payment_method.has_error = true;
            errors.authentication_key = true;
        }
        if ($scope.configuration.payment_method.method.match(/facilypay/)) {
            if (typeof $scope.configuration.payment_method.settlement_mode === 'undefined'
                || !$scope.configuration.payment_method.settlement_mode
                || $scope.configuration.payment_method.settlement_mode.length > 20
            ) {
                $scope.update_configuration.payment_method.has_error = true;
                errors.settlement_mode = true;
            }
            if (typeof $scope.configuration.payment_method.settlement_mode_version === 'undefined'
                || !$scope.configuration.payment_method.settlement_mode_version
                || !$scope.configuration.payment_method.settlement_mode_version.match(/[0-9]/)
                || $scope.configuration.payment_method.settlement_mode_version.length > 3
            ) {
                $scope.update_configuration.payment_method.has_error = true;
                errors.settlement_mode_version = true;
            }
        }
        if ($scope.configuration.payment_method.method.match(/cofinoga/)) {
            if (typeof $scope.configuration.payment_method.payment_options === 'undefined'
                || !$scope.configuration.payment_method.payment_options
                || $scope.configuration.payment_method.payment_options.length == 0
            ) {
                $scope.update_configuration.payment_method.has_error = true;
                errors.payment_options = true;
            }
        }
        if (typeof $scope.configuration.payment_method.min_amount === 'undefined'
            || !$scope.configuration.payment_method.min_amount.match(reg)
        ) {
            $scope.update_configuration.payment_method.has_error = true;
            errors.min_amount = true;
        }
        if (typeof $scope.configuration.payment_method.max_amount === 'undefined'
            || !$scope.configuration.payment_method.max_amount.match(reg)
        ) {
            $scope.update_configuration.payment_method.has_error = true;
            errors.max_amount = true;
        }
        if (typeof $scope.configuration.payment_method.has_3d_secure_from === 'undefined'
            || !$scope.configuration.payment_method.has_3d_secure_from.match(reg)
        ) {
            $scope.update_configuration.payment_method.has_error = true;
            errors.has_3d_secure_from = true;
        }
        if (typeof $scope.configuration.payment_method.delay === 'undefined'
            || !$scope.configuration.payment_method.delay.match(/[0-9]/g)
        ) {
            $scope.update_configuration.payment_method.has_error = true;
            errors.delay = true;
        }
        if (typeof $scope.configuration.payment_method.first_cashing_percentage === 'undefined'
            || !$scope.configuration.payment_method.first_cashing_percentage.match(reg)
        ) {
            $scope.update_configuration.payment_method.has_error = true;
            errors.first_cashing_percentage = true;
        }
        if ($scope.update_configuration.payment_method.has_error) {
            $scope.update_configuration.payment_method.front_errors = errors;
        } else {
            $scope.update_configuration.payment_method = getGlobalFunctionVars(true);

            $scope.configuration.payment_method.shops = [];
            if ($scope.ps15) {
                $scope.configuration.payment_method.shops = $(".method-shop-select").val();
            } else {
                var shops = $("#method-shop-tree input[type=checkbox][name^='checkBoxShopAsso']:checked");
                shops.each(
                    function()
                    {
                        $scope.configuration.payment_method.shops.push($(this).val());
                    }
                );
            }

            setTimeout(function () {
                $http({
                    method: 'POST',
                    url: $scope.tabs['AdminConfigsdevatos'].link,
                    params: {
                        ajax: '1',
                        action: 'updatePaymentMethod'
                    },
                    data: {
                        payment_method: $scope.configuration.payment_method
                    }
                }).then(function successCallback(response) {
                    var result = response.data;
                    $scope.update_configuration.payment_method.is_success = result.is_success;
                    $scope.update_configuration.payment_method.has_error = result.has_error;
                    if (result.is_success) {
                        $scope.readPaymentMethod();
                        $scope.is_payment_method_edition_displayed = false;
                        $scope.configuration.payment_method = false;
                    }
                }, function errorCallback(response) {
                    alert($scope.js_translations['ErrorMessage']);
                }).finally(function () {
                    $scope.update_configuration.payment_method.loading = false;
                });
            }, 250);
        }
    };

    /**
     * Delete a payment method.
     */
    $scope.deletePaymentMethod = function (payment_method) {
        if (confirm($scope.js_translations['DeletingPaymentMethod'] + ' (#' + payment_method.id + ')')) {
            $scope.delete_configuration.payment_method = getGlobalFunctionVars(true);
            setTimeout(function () {
                $http({
                    method: 'POST',
                    url: $scope.tabs['AdminConfigsdevatos'].link,
                    params: {
                        ajax: '1',
                        action: 'deletePaymentMethod'
                    },
                    data: {
                        id_payment_method: payment_method.id_payment_method
                    }
                }).then(function successCallback(response) {
                    var data = response.data;
                    $scope.delete_configuration.payment_method.is_success = data.is_success;
                    $scope.delete_configuration.payment_method.has_error = data.has_error;
                    if (data.is_success) {
                        $scope.closePaymentMethodEdition();
                        $scope.readPaymentMethod();
                    }
                }, function errorCallback(response) {
                    alert($scope.js_translations['ErrorMessage']);
                }).finally(function () {
                    $scope.delete_configuration.payment_method.loading = false;
                });
            }, 250);
        }
    };

    $scope.action_payment = getGlobalFunctionVars();

    /**
     * Action payment.
     *
     * @param int id_payment_method - Payment method ID.
     * @param string link - Method link.
     */
    $scope.actionPayment = function (id_payment_method, link) {
        $scope.action_payment = getGlobalFunctionVars(true);
        $http({
            method: 'POST',
            url: link,
            params: {
                ajax: '1',
                action: 'actionPayment'
            },
            data: {
                id_payment_method: id_payment_method
            }
        }).then(function successCallback(response) {
            var data = response.data;
            $scope.action_payment.is_success = data.is_success;
            $scope.action_payment.has_error = data.has_error;
            if (data.has_error) {
                if (typeof data.error !== 'undefined') {
                    if (data.error == 'AccessDenied') {
                        alert($scope.js_translations['AccessDenied']);
                    } else if (data.error == 'UnavailablePaymentMethod') {
                        alert($scope.js_translations['UnavailablePaymentMethod']);
                    } else if (data.error == 'ApiError' && typeof data.error_message !== 'undefined') {
                        angular.element('#sdevatos-response').html(data.error_message);
                        angular.element('#sdevatos-response-link').fancybox().click();
                    } else {
                        alert($scope.js_translations['ErrorMessage']);
                    }
                } else {
                    alert($scope.js_translations['ErrorMessage']);
                }
            } else {
                if (data.redirect) {
                    angular.element('#sdevatos-response').html(data.template);
                    angular.element('#sdevatos-form').submit();
                } else {
                    angular.element('#sdevatos-response').html(data.template);
                    angular.element('#sdevatos-response-link').fancybox().click();
                }
            }
        }, function errorCallback(response) {
            alert($scope.js_translations['ErrorMessage']);
        }).finally(function () {
            $scope.action_payment.loading = false;
        });
    }
});

SdevAtosApp.directive('file', function () {
    return {
        scope: {
            file: '='
        },
        link: function (scope, element, attrs) {
            element.bind('change', function (event) {
                var files = event.target.files;
                var file = files[0];
                scope.file = file ? file : undefined;
                scope.$apply();
            });
        }
    };
});

(function (angular) {
    'use strict';
    var module = angular.module('angular-bind-html-compile', []);
    module.directive('bindHtmlCompile', ['$compile', function ($compile) {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                scope.$watch(function () {
                    return scope.$eval(attrs.bindHtmlCompile);
                }, function (value) {
                    element.html(value && value.toString());
                    var compileScope = scope;
                    if (attrs.bindHtmlScope) {
                        compileScope = scope.$eval(attrs.bindHtmlScope);
                    }
                    $compile(element.contents())(compileScope);
                });
            }
        };
    }]);
}(window.angular));
