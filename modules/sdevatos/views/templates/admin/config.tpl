{**
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
 *}

{assign var="_yes" value={l s='Yes' mod='sdevatos'}}
{assign var="_no" value={l s='No' mod='sdevatos'}}
{assign var="_enabled" value={l s='Enabled' mod='sdevatos'}}
{assign var="_disabled" value={l s='Disabled' mod='sdevatos'}}
{assign var="_automatic" value={l s='Automatic' mod='sdevatos'}}
{assign var="_manual" value={l s='Manual' mod='sdevatos'}}

{if $ps15}{assign var="col_lg_9" value="col-lg-8"}
{else}{assign var="col_lg_9" value="col-lg-9"}{/if}

<section ng-init="initParameters()" id="module-{$module_name|escape:'htmlall':'UTF-8'}-config">
    <header>
        <h3>
            <i class="fa fa-cog fa-fw"></i>
            {l s='Parameters' mod='sdevatos'}
        </h3>
    </header>

    {* READ LOADING *}
    <div ng-if="is_parameters_loading">
        <i class="fa fa-refresh fa-spin fa-fw"></i>
        <em class="text-muted">{l s='Parameters loading' mod='sdevatos'}...</em>
    </div>

    <div ng-if="!is_parameters_loading" class="form-horizontal">
        {* GENERAL CONFIGURATION *}
        <div class="panel">
            <div class="panel-heading">
                <i class="fa fa-cogs fa-fw"></i>
                {l s='Generals' mod='sdevatos'}
            </div>

            <div ng-if="get_configuration.loading">
                <i class="fa fa-refresh fa-spin fa-fw"></i>
                <em class="text-muted">{l s='Loading' mod='sdevatos'}...</em>
            </div>

            <div ng-if="configuration.debug_mode == true && get_configuration.has_error" ng-repeat="parameter in get_configuration.parameters_in_error" role="alert" class="warn alert alert-danger">
                {l s='This parameter hasn\'t been getted' mod='sdevatos'} : [[parameter]].
            </div>

            {* UPDATE SUCCESS *}
            <div ng-if="update_configuration.general.is_success" role="alert" class="conf alert alert-success alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                {l s='The configuration has been successfully updated' mod='sdevatos'} !
            </div>

            {* UPDATE ERROR *}
            <div ng-if="update_configuration.general.has_error" role="alert" class="conf alert alert-danger alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                {l s='An error has occured during the configuration update' mod='sdevatos'}.
            </div>

            {* DEBUG MODE - ERRORS *}
            <div ng-if="configuration.general.debug_mode == true && get_configuration.general.has_error" ng-repeat="error in get_configuration.general.back_errors" role="alert" class="warn alert alert-danger alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                {l s='This parameter could not be retrieved' mod='sdevatos'} : [[error]]
            </div>

            <div ng-if="configuration.general.debug_mode == true && update_configuration.general.has_error" ng-repeat="error in update_configuration.general.back_errors" role="alert" class="warn alert alert-danger alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                {l s='An error has occured with this parameter' mod='sdevatos'} : [[error]]
            </div>
            {* END DEBUG MODE - ERRORS *}

            <div ng-if="update_configuration.general.loading">
                <i class="fa fa-refresh fa-spin fa-fw"></i>
                <em class="text-muted">{l s='Save in progress' mod='sdevatos'}...</em>
            </div>

            <div ng-if="!update_configuration.general.loading">
                {* DEBUG MODE *}
                <div class="row">
                    <div class="col-lg-3 col-sm-4 col-xs-12">
                        <h4 class="sdev-no-mt">
                            <i class="fa fa-bug fa-fw"></i>
                            {l s='Debug mode' mod='sdevatos'}
                        </h4>
                        {$configuration.general.debug_mode}
                    </div>
                </div><hr />
                {* END DEBUG MODE *}

                {* START IP FILTERING *}
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <h4 class="sdev-no-mt">
                            <i class="fa fa-cog fa-fw"></i>
                            {l s='IP addresses for the debug mode displaying' mod='sdevatos'}
                        </h4>
                        {$configuration.general.ip_filtering}<br />
                        <button ng-if="!add_ip_address.loading" ng-click="addIpAddress()" type="button" class="btn btn-default">
                            <i class="fa fa-plus fa-fw"></i>
                            {l s='Add my IP address' mod='sdevatos'}
                        </button>
                        <p class="sdev-no-mb" ng-if="add_ip_address.loading">
                            <em class="text-muted">
                                <i class="fa fa-fw fa-refresh fa-spin"></i>
                                {l s='IP address retrieving...' mod='sdevatos'}
                            </em>
                        </p>
                    </div>
                </div><hr />
                {* END IP FILTERING *}

                {* REDIRECTION *}
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <h4 class="sdev-no-mt">
                            <i class="fa fa-retweet fa-fw"></i>
                            {l s='Redirection after payment' mod='sdevatos'}
                        </h4>
                        {$configuration.general.redirection}
                    </div>
                </div><hr />
                {* END REDIRECTION *}

                {* PAYMENT ERRORS *}
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <h4 class="sdev-no-mt">
                            <i class="fa fa-exclamation-triangle fa-fw"></i>
                            {l s='Behavior of payment errors' mod='sdevatos'}
                        </h4>
                        {$configuration.general.payment_errors}
                    </div>
                </div><hr />
                {* END PAYMENT ERRORS *}

                <div role="alert" class="warn alert alert-info">
                    {l s='The binaries path and pathfile path are exclusive parameters of SIPS 1.0' mod='sdevatos'}.
                </div>

                {* BINARY PATH *}
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <h4 class="sdev-no-mt">
                            <i class="fa fa-terminal fa-fw"></i>
                            {l s='Binaries path' mod='sdevatos'}
                        </h4>
                        {$configuration.general.binary_path}
                    </div>
                </div><hr />
                {* END BINARY PATH *}

                {* PATHFILE PATH *}
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <h4 class="sdev-no-mt">
                            <i class="fa fa-link fa-fw"></i>
                            {l s='Pathfile path' mod='sdevatos'}
                        </h4>
                        {$configuration.general.pathfile_path}
                    </div>
                </div>
                {* END PATHFILE PATH *}
            </div>

            {* SAVE *}
            <div ng-if="!update_configuration.general.loading" class="panel-footer">
                <div class="text-right">
                    <button ng-click="updateConfiguration()" class="btn btn-default">
                        <i class="fa fa-save fa-fw"></i>
                        {l s='Save' mod='sdevatos'}
                    </button>
                </div>
            </div>
            {* END SAVE *}
        </div>
        {* END GENERAL CONFIGURATION *}

        {* START CONTRACTS *}
        <div class="panel">
            <div class="sdev-window-btns">
                <button ng-click="openContractEdition()" class="btn{if !$ps15} btn-xs{/if} btn-default" title="{l s='Add a contract' mod='sdevatos'}">
                    <i class="fa fa-plus fa-fw"></i>
                </button>
            </div>
            <div class="panel-heading">
                <i class="fa fa-file-text fa-fw"></i>
                {l s='Contracts' mod='sdevatos'}
                <span class="badge">[[contracts_number]]</span>
            </div>

            {* UPDATE SUCCESS *}
            <div ng-if="update_configuration.contract.is_success" role="alert" class="conf alert alert-success alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                <p>{l s='The contract has been successfully updated' mod='sdevatos'} !</p>
            </div>

            {* DELETE SUCCESS *}
            <div ng-if="delete_configuration.contract.is_success" role="alert" class="conf alert alert-success alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                <p>{l s='The contract has been successfully deleted' mod='sdevatos'} !</p>
            </div>

            {* DELETE ERROR *}
            <div ng-if="delete_configuration.contract.has_error" role="alert" class="conf alert alert-success alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                <p>{l s='An error has occured during the contract deletion' mod='sdevatos'}.</p>
            </div>

            {* READ LOADING *}
            <div ng-if="read_configuration.contract.loading">
                <i class="fa fa-refresh fa-spin fa-fw"></i>
                <em class="text-muted">{l s='Loading' mod='sdevatos'}...</em>
            </div>

            <p ng-if="!read_configuration.contract.loading && contracts_number == 0" class="sdev-no-mb"><em class="text-muted">{l s='You have not yet added any contracts' mod='sdevatos'}.</em></p>

            <table ng-if="!read_configuration.contract.loading && contracts_number > 0" class="table">
                <thead>
                    <tr>
                        <th class="text-center">{l s='ID' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Bank' mod='sdevatos'}</th>
                        <th class="text-center">{l s='SIPS version' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Test mode' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Reference' mod='sdevatos'}</th>
                        <th class="text-center">{l s='3D-Secure' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Creation' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Update' mod='sdevatos'}</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr ng-repeat="contract in contracts">
                        <td class="text-center">#[[contract.id_contract]]</td>
                        <td class="text-center">[[contract.bank_name]]</td>
                        <td class="text-center">
                            <span ng-if="contract.sips_version == '2.0'" class="label label-success">[[contract.sips_version]]</span>
                            <span ng-if="contract.sips_version == '1.0'" class="label label-warning">[[contract.sips_version]]</span>
                        </td>
                        <td class="text-center">
                            <span ng-if="contract.is_test_mode == true" class="label label-success">{$_yes|escape:'htmlall':'UTF-8'}</span>
                            <span ng-if="contract.is_test_mode == false" class="label label-danger">{$_no|escape:'htmlall':'UTF-8'}</span>
                        </td>
                        <td class="text-center">
                            <span ng-if="contract.transaction_reference == 'reference'" class="label label-success">{l s='Reference' mod='sdevatos'}</span>
                            <span ng-if="contract.transaction_reference == 'id'" class="label label-info">{l s='ID' mod='sdevatos'}</span>
                            <span ng-if="contract.transaction_reference == 'auto'" class="label label-warning">{l s='Auto' mod='sdevatos'}</span>
                        </td>
                        <td class="text-center">
                            <span ng-if="contract.has_3d_secure == true" class="label label-success">{$_yes|escape:'htmlall':'UTF-8'}</span>
                            <span ng-if="contract.has_3d_secure == false" class="label label-danger">{$_no|escape:'htmlall':'UTF-8'}</span>
                        </td>
                        <td class="text-center">[[contract.date_add]]</td>
                        <td class="text-center">[[contract.date_upd]]</td>

                        <td class="text-right">
                            <button ng-click="openContractEdition(contract)" class="btn btn-xs btn-warning">
                                <i class="fa fa-pencil"></i>
                            </button>&nbsp;

                            <button ng-click="deleteContract(contract)" class="btn btn-xs btn-danger">
                                <i class="fa fa-times"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {* END CONTRACTS *}

        {* START CONTRACT EDITION *}
        <div ng-if="is_contract_edition_displayed" class="panel sdev-window-to-close">
            <div ng-if="!update_configuration.contract.loading" class="sdev-window-btns">
                {* MINIMIZE *}
                <button ng-click="minimizeContractEdition()" class="btn{if !$ps15} btn-xs{/if} btn-warning">
                    <i class="fa fa-minus"></i>
                </button>&nbsp;

                {* MAXIMIZE *}
                <button ng-click="maximizeContractEdition()" class="btn{if !$ps15} btn-xs{/if} btn-success">
                    <i class="fa fa-plus"></i>
                </button>&nbsp;

                {* CLOSE *}
                <button ng-click="closeContractEdition()" class="btn{if !$ps15} btn-xs{/if} btn-danger">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="panel-heading">
                <i class="fa fa-pencil fa-fw"></i>
                {l s='Contract edition' mod='sdevatos'}
                <span ng-if="configuration.contract.id_contract">(#[[configuration.contract.id_contract]])</span>
                <span ng-if="!configuration.contract.id_contract">({l s='NEW' mod='sdevatos'})</span>
            </div>

            {* DEBUG MODE *}
            <div ng-if="configuration.general.debug_mode == true">
                {* UPDATE ERRORS *}
                <div ng-if="update_configuration.contract.has_error" ng-repeat="error in update_configuration.contract.back_errors" role="alert" class="warn alert alert-danger alert-dismissible">
                    {if !$ps15}
                        <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    {/if}
                    {l s='An error has occured with this parameter' mod='sdevatos'} : [[error]].
                </div>
                {* END UPDATE ERRORS *}

                {* CORRECTION OF EXECUTABLE RIGHTS ERROR *}
                <div ng-if="correct_exe_rights.has_error" ng-repeat="error in correct_exe_rights.back_errors" role="alert" class="warn alert alert-danger alert dismissible">
                    {if !$ps15}
                        <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    {/if}
                    {l s='The rights could not be corrected on this file' mod='sdevatos'} : [[error]].
                </div>
                {* END CORRECTION OF EXECUTABLE RIGHTS ERROR *}
            </div>
            {* END DEBUG MODE *}

            {* UPDATE ERROR *}
            <div ng-if="configuration.general.debug_mode == false && update_configuration.contract.has_error" role="alert" class="warn alert alert-danger alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                {l s='An error has occured during the contract update' mod='sdevatos'}.
            </div>
            {* END UPDATE ERROR *}

            <div ng-if="update_configuration.contract.loading">
                <i class="fa fa-refresh fa-fw fa-spin"></i>
                <em class="text-muted">{l s='Save in progress' mod='sdevatos'}...</em>
            </div>

            <em ng-if="is_contract_edition_minimized && !update_configuration.contract.loading" ng-click="maximizeContractEdition()" class="text-muted">...</em>

            <div ng-if="!is_contract_edition_minimized && !update_configuration.contract.loading">
                {* START ENVIRONMNENT *}
                <div class="well">
                    <h3>
                        <i class="fa fa-globe fa-fw"></i>
                        {l s='Environment' mod='sdevatos'}
                    </h3>

                    {* START SIPS VERSION *}
                    <div class="row">
                        <div class="col-lg-3 col-sm-4 col-xs-12">
                            <h4 class="sdev-no-mt">{l s='SIPS version' mod='sdevatos'}</h4>
                            {$configuration.contract.sips_version}
                        </div>
                    </div><hr />
                    {* END SIPS VERSION *}

                    {* START TEST MODE *}
                    <div ng-if="configuration.contract.sips_version == '2.0'" class="row">
                        <div class="col-lg-3 col-sm-4 col-xs-12">
                            <h4 class="sdev-no-mt">{l s='Test mode' mod='sdevatos'}</h4>
                            {$configuration.contract.test_mode}
                        </div>
                    </div>
                    {* END TEST MODE *}

                    <div ng-if="configuration.contract.sips_version == '1.0'">
                        {* START TEST MODE *}
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Test mode' mod='sdevatos'}</h4>
                                <div class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="warn alert alert-warning sdev-no-mb">
                                            {l s='This mode is not available in 1.0' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><hr />
                        {* END TEST MODE *}

                        {* START EXECUTABLE VERSION *}
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Executable version' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.contract.front_errors.exe_version" class="form-group">
                                    <div class="margin-form col-lg-offset-3 col-lg-3 col-sm-4 col-xs-12">
                                        <div role="alert" class="warn alert alert-danger">
                                            {l s='Please complete this field' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.contract.exe_version}
                            </div>
                        </div><hr />
                        {* END EXECUTABLE VERSION *}

                        {* START CORRECTION OF EXECUTION RIGHTS *}
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="margin-form col-lg-12">
                                        {* START CORRECTION OF EXECUTABLE RIGHTS ERROR *}
                                        <div ng-if="configuration.general.debug_mode == false && correct_exe_rights.has_error" role="alert" class="warn alert alert-danger alert-dismissible">
                                            {if !$ps15}
                                                <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            {/if}
                                            {l s='An error has occured during the rights correction' mod='sdevatos'}.
                                        </div>
                                        {* END CORRECTION OF EXECUTABLE RIGHTS ERROR *}

                                        {* START SUCCESS - RIGHTS CORRECTION *}
                                        <div ng-if="correct_exe_rights.is_success" role="alert" class="conf alert alert-success alert-dismissible">
                                            {if !$ps15}
                                                <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            {/if}
                                            {l s='Rights are successfully corrected' mod='sdevatos'} !
                                        </div>
                                        {* END SUCCESS - RIGHTS CORRECTION *}

                                        <div role="alert" class="warn alert alert-info">
                                            <p>{l s='Click this button if you think you don\'t have the execution rights on the executables' mod='sdevatos'}.</p>
                                        </div>

                                        <em ng-if="correct_exe_rights.loading" class="text-muted">
                                            <i class="fa fa-refresh fa-spin fa-fw"></i>
                                            {l s='Rights correction in progress' mod='sdevatos'}...
                                        </em>

                                        <button ng-if="!correct_exe_rights.loading" ng-click="correctExeRights()" class="btn btn-default">
                                            <i class="fa fa-wrench fa-fw"></i>
                                            {l s='Correct execution rights' mod='sdevatos'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {* END CORRECTION OF EXECUTION RIHTS *}
                    </div>
                </div>
                {* END ENVIRONMNENT *}

                {* START AUTHENTICATION *}
                <div class="well">
                    <h3>
                        <i class="fa fa-user fa-fw"></i>
                        {l s='Authentication' mod='sdevatos'}
                    </h3>

                    {* START BANK *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='Bank' mod='sdevatos'}</h4>

                            <div ng-if="update_configuration.contract.front_errors.bank" class="form-group">
                                <div class="margin-form col-lg-12">
                                    <div role="alert" class="warn alert alert-danger">
                                        {l s='Please select a bank' mod='sdevatos'}.
                                    </div>
                                </div>
                            </div>

                            {$configuration.contract.bank}
                        </div>
                    </div><hr />
                    {* END BANK *}

                    {* START MERCHANT ID *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='Merchant ID' mod='sdevatos'}</h4>

                            <div ng-if="configuration.contract.sips_version == '2.0' && (has_merchant_id_format_error || update_configuration.contract.front_errors.merchant_id)" class="form-group">
                                <div class="margin-form col-lg-12">
                                    <div role="alert" class="warn alert alert-danger">
                                        {l s='Please enter a value of 15 digits' mod='sdevatos'}.
                                    </div>
                                </div>
                            </div>

                            {$configuration.contract.merchant_id_1}
                            {$configuration.contract.merchant_id_2}
                        </div>
                    </div><hr />
                    {* END MERCHANT ID *}

                    {* START CERTIFICATE *}
                    <div ng-if="configuration.contract.sips_version == '1.0'">
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Certificate' mod='sdevatos'}</h4>

                                <div ng-if="has_certificate_file_error || update_configuration.contract.front_errors_has_certificate_file_error" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="warn alert alert-danger">
                                            {l s='Please import a file' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                <div ng-if="has_certificate_format_error || update_configuration.contract.front_errors.has_certificate_format_error" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="warn alert alert-danger">
                                            {l s='Please import a file corresponding to the expected format (example : certif.fr.123456789012345)' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.contract.certificate}
                            </div>
                        </div>
                    </div>
                    {* END CERTIFICATE *}

                    <div ng-if="configuration.contract.sips_version == '2.0'">
                        {* START SECRETE KEY *}
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Secrete key' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.contract.front_errors.secrete_key" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="warn alert alert-danger">
                                            {l s='Please complete this field' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.contract.secrete_key}
                            </div>
                        </div><hr />
                        {* END SECRETE KEY *}

                        {* START KEY VERSION *}
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Key version' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.contract.front_errors.key_version" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="warn alert alert-danger">
                                            {l s='Please enter a value of 10 digits maximum' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.contract.key_version}
                            </div>
                        </div>
                        {* END KEY VERSION *}
                    </div>
                </div>
                {* END AUTHENTICATION *}

                {* START TRANSACTION *}
                <div class="well">
                    <h3>
                        <i class="fa fa-university fa-fw"></i>
                        {l s='Transaction' mod='sdevatos'}
                    </h3>

                    {* START TRANSACTION REFERENCE *}
                    <div ng-if="configuration.contract.sips_version == '2.0'">
                        <div ng-if="configuration.contract.transaction_reference == 'auto'" role="alert" class="warn alert alert-warning">
                            <p>{l s='[1]WARNING![/1] If your bank does not generate your transaction reference automatically, your customers will have an error during the paiement!' mod='sdevatos' tags=['<strong>']}</p>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Transaction reference' mod='sdevatos'}</h4>
                                {$configuration.contract.transaction_reference}
                                <div class="clearfix"></div>
                                <span class="text-muted"><em>{l s='Choose if you want to use TransactionId or TransactionReference. Please refer you to your contract to know which option to use.' mod='sdevatos'}</em></span>
                            </div>
                        </div><hr />
                    </div>
                    {* END TRANSACTION REFERENCE *}

                    {* START TRANSACTION REF ID *}
                    <div class="hidden">
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='Reference or ID' mod='sdevatos'}</h4>
                            {$configuration.contract.transaction_ref_id}
                        </div>
                    </div><hr />
                    </div>
                    {* END TRANSACTION REF ID *}

                    {* START 3D-SECURE *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='3D-Secure' mod='sdevatos'}</h4>
                            {$configuration.contract.has_3d_secure}
                            <div class="clearfix"></div>
                            <span class="text-muted"><em>{l s='Please refer you to your contract to know if you have this option.' mod='sdevatos'}</em></span>
                        </div>
                    </div>
                    {* END 3D-SECURE *}
                </div>
                {* END TRANSACTION *}
            </div>

            {* SAVE *}
            <div class="panel-footer text-right">
                <button ng-if="!update_configuration.contract.loading" ng-click="updateContract()" class="btn btn-default">
                    <i class="fa fa-save fa-fw"></i>
                    {l s='Save' mod='sdevatos'}
                </button>
            </div>
            {* END SAVE *}
        </div>
        {* END CONTRACT EDITION *}

        {* START PAYMENT METHODS *}
        <div class="panel">
            <div class="sdev-window-btns">
                <button ng-click="openPaymentMethodEdition()" class="btn{if !$ps15} btn-xs{/if} btn-default" title="{l s='Add a payment method' mod='sdevatos'}">
                    <i class="fa fa-plus fa-fw"></i>
                </button>
            </div>
            <div class="panel-heading">
                <i class="fa fa-money fa-fw"></i>
                {l s='Payment methods' mod='sdevatos'}
                <span class="badge">[[payment_methods_number]]</span>
            </div>

            {* UPDATE SUCCESS *}
            <div ng-if="update_configuration.payment_method.is_success" role="alert" class="conf alert alert-success alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                <p>{l s='The payment method has been successfully updated' mod='sdevatos'} !</p>
            </div>

            {* DELETE SUCCESS *}
            <div ng-if="delete_configuration.payment_method.is_success" role="alert" class="conf alert alert-success alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                <p>{l s='The payment method has been successfully deleted' mod='sdevatos'} !</p>
            </div>

            {* DELETE ERROR *}
            <div ng-if="delete_configuration.payment_method.has_error" role="alert" class="conf alert alert-success alert-dismissible">
                {if !$ps15}
                    <button type="button" class="close" data-dismiss="alert" aria-label="{l s='Close' mod='sdevatos'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {/if}
                <p>{l s='An error has occured during the payment method deletion' mod='sdevatos'}.</p>
            </div>

            {* READ LOADING *}
            <div ng-if="read_configuration.payment_method.loading">
                <i class="fa fa-refresh fa-spin fa-fw"></i>
                <em class="text-muted">{l s='Loading' mod='sdevatos'}...</em>
            </div>

            <p ng-if="!read_configuration.payment_method.loading && payment_methods_number == 0" class="sdev-no-mb"><em class="text-muted">{l s='You have not yet added any payment methods' mod='sdevatos'}.</em></p>

            <table ng-if="!read_configuration.payment_method.loading && payment_methods_number > 0" class="table">
                <thead>
                    <tr>
                        <th class="text-center">{l s='ID' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Contract' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Name' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Enabled' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Min' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Max' mod='sdevatos'}</th>
                        <th class="text-center">{l s='3D-Secure from' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Mode' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Delay' mod='sdevatos'}</th>
                        <th class="text-center">{l s='First cashing' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Creation' mod='sdevatos'}</th>
                        <th class="text-center">{l s='Update' mod='sdevatos'}</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr ng-repeat="payment_method in payment_methods">
                        <td class="text-center">#[[payment_method.id_payment_method]]</td>
                        <td class="text-center">#[[payment_method.id_contract]]</td>
                        <td class="text-center">[[payment_method.name]]</td>
                        <td class="text-center">
                            <span ng-if="payment_method.is_enabled == true" class="label label-success">{$_yes|escape:'htmlall':'UTF-8'}</span>
                            <span ng-if="payment_method.is_enabled == false" class="label label-danger">{$_no|escape:'htmlall':'UTF-8'}</span>
                        </td>
                        <td class="text-right">[[payment_method.min_amount]]€</td>
                        <td class="text-right">[[payment_method.max_amount]]€</td>
                        <td class="text-right" ng-if="contracts[payment_method.id_contract].has_3d_secure == true">[[payment_method.has_3d_secure_from]]€</td>
                        <td class="text-center" ng-if="contracts[payment_method.id_contract].has_3d_secure == false">
                            <span class="label label-danger">{$_disabled|escape:'htmlall':'UTF-8'}</span>
                        </td>
                        <td class="text-center">
                            <span ng-if="payment_method.cashing_mode == 'AUTHOR_CAPTURE'" class="label label-success">{l s='Automatic' mod='sdevatos'}</span>
                            <span ng-if="payment_method.cashing_mode == 'VALIDATION'" class="label label-danger">{l s='Manual' mod='sdevatos'}</span>
                        </td>
                        <td class="text-center">
                            [[payment_method.delay]]
                            <span ng-if="payment_method.delay < 2">{l s='day' mod='sdevatos'}</span>
                            <span ng-if="payment_method.delay > 1">{l s='days' mod='sdevatos'}</span>
                        </td>
                        <td class="text-right">[[payment_method.first_cashing_percentage]]%</td>
                        <td class="text-center">[[payment_method.date_add]]</td>
                        <td class="text-center">[[payment_method.date_upd]]</td>

                        <td class="text-right">
                            <button ng-click="openPaymentMethodEdition(payment_method)" class="btn btn-xs btn-warning">
                                <i class="fa fa-pencil"></i>
                            </button>&nbsp;

                            <button ng-click="deletePaymentMethod(payment_method)" class="btn btn-xs btn-danger">
                                <i class="fa fa-times"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {* END PAYMENT METHODS *}

        {* START PAYMENT METHOD EDITION *}
        <div ng-show="is_payment_method_edition_displayed" class="panel sdev-window-to-close">
            <div class="sdev-window-btns">
                {* MAXIMIZE *}
                <button ng-click="minimizePaymentMethodEdition()" class="btn{if !$ps15} btn-xs{/if} btn-warning">
                    <i class="fa fa-minus"></i>
                </button>&nbsp;

                {* MINIMIZE *}
                <button ng-click="maximizePaymentMethodEdition()" class="btn{if !$ps15} btn-xs{/if} btn-success">
                    <i class="fa fa-plus"></i>
                </button>&nbsp;

                {* CLOSE *}
                <button ng-click="closePaymentMethodEdition()" class="btn{if !$ps15} btn-xs{/if} btn-danger">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="panel-heading">
                <i class="fa fa-pencil fa-fw"></i>
                {l s='Payment method edition' mod='sdevatos'}
                <span ng-if="configuration.payment_method.id_payment_method">(#[[configuration.payment_method.id_payment_method]])</span>
                <span ng-if="!configuration.payment_method.id_payment_method">({l s='NEW' mod='sdevatos'})</span>
            </div>

            <div ng-if="update_configuration.payment_method.loading">
                <i class="fa fa-refresh fa-spin fa-fw"></i>
                <em class="text-muted">{l s='Loading' mod='sdevatos'}...</em>
            </div>

            <em ng-if="is_payment_method_edition_minimized && !update_configuration.payment_method.loading" ng-click="maximizePaymentMethodEdition()" class="text-muted">...</em>

            <div ng-show="!is_payment_method_edition_minimized && !update_configuration.payment_method.loading">
                {* START IS ENABLED *}
                <div class="row">
                    <div class="col-lg-12">
                        <h4 class="sdev-no-mt">{l s='Enable' mod='sdevatos'}</h4>
                        {$configuration.payment_method.is_enabled}
                    </div>
                </div><hr />
                {* END IS ENABLED *}

                {* START CONTRACT LINKED *}
                <div class="row">
                    <div class="col-lg-12">
                        <h4 class="sdev-no-mt">{l s='Contract linked' mod='sdevatos'}</h4>

                        <div ng-if="update_configuration.payment_method.front_errors.id_contract" class="form-group">
                            <div class="margin-form col-lg-12">
                                <div role="alert" class="alert alert-danger">
                                    {l s='Please select a contract' mod='sdevatos'}.
                                </div>
                            </div>
                        </div>

                        {$configuration.payment_method.id_contract}
                    </div>
                </div><hr />
                {* END CONTRACT LINKED *}

                {* START NAME *}
                <div class="row">
                    <div class="col-lg-12">
                        <h4 class="sdev-no-mt">{l s='Name' mod='sdevatos'}</h4>

                        <div ng-if="update_configuration.payment_method.front_errors.name" class="form-group">
                            <div class="margin-form col-lg-12">
                                <div role="alert" class="alert alert-danger">
                                    {l s='Please enter a name' mod='sdevatos'}.
                                </div>
                            </div>
                        </div>

                        {$configuration.payment_method.name}
                    </div>
                </div>
                {* END NAME *}

                <div ng-if="configuration.payment_method.id_contract > 0"><hr />
                    {* START PAYMENT METHOD *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='Payment method' mod='sdevatos'}</h4>

                            <div ng-if="update_configuration.payment_method.front_errors.method" class="form-group">
                                <div class="margin-form col-lg-12">
                                    <div role="alert" class="alert alert-danger">
                                        {l s='Please select a payment method' mod='sdevatos'}.
                                    </div>
                                </div>
                            </div>

                            {$configuration.payment_method.method}
                        </div>
                    </div>
                    {* END PAYMENT METHOD *}

                    {* START AUTHENTICATION KEY *}
                    <div ng-if="isPaymentMethodFranfinance()"><hr />
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Authentication key' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.payment_method.front_errors.authentication_key" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="alert alert-danger">
                                            {l s='Please enter an authentication key' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.payment_method.authentication_key}
                            </div>
                        </div>
                    </div>
                    {* END AUTHENTICATION KEY *}

                    <div ng-if="isPaymentMethodFacilypay()"><hr />
                        {* START SETTLEMENT MODE *}
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Settlement mode' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.payment_method.front_errors.settlement_mode" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="alert alert-danger">
                                            {l s='Please enter a settlement mode of 20 characters maximum' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.payment_method.settlement_mode}
                            </div>
                        </div><hr />
                        {* END SETTLEMENT MODE *}

                        {* START SETTLEMENT MODE VERSION *}
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Settlement mode version' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.payment_method.front_errors.settlement_mode_version" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="alert alert-danger">
                                            {l s='Please enter a settlement mode version of 3 digits maximum' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.payment_method.settlement_mode_version}
                            </div>
                        </div>
                        {* END SETTLEMENT MODE VERSION *}
                    </div>

                    {* START PAYMENT OPTIONS *}
                    <div ng-if="isPaymentMethodCofinoga()"><hr />
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Payment options' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.payment_method.front_errors.payment_options" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="alert alert-danger">
                                            {l s='Please select a payment option' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.payment_method.payment_options}
                            </div>
                        </div>
                    </div>
                    {* END PAYMENT OPTIONS *}
                </div>

                <div ng-if="configuration.payment_method.is_enabled == true"><hr />
                    {* START MIN AMOUNT *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='Enable from' mod='sdevatos'}</h4>

                            <div ng-if="update_configuration.payment_method.front_errors.min_amount" class="form-group">
                                <div class="margin-form col-lg-12">
                                    <div role="alert" class="alert alert-danger">
                                        {l s='Please enter an decimal number and use a dot instead of the comma' mod='sdevatos'}.
                                    </div>
                                </div>
                            </div>

                            {$configuration.payment_method.min_amount}
                        </div>
                    </div><hr />
                    {* END MIN AMOUNT *}

                    {* START MAX AMOUNT *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='Disable from' mod='sdevatos'}</h4>

                            <div ng-if="update_configuration.payment_method.front_errors.max_amount" class="form-group">
                                <div class="margin-form col-lg-12">
                                    <div role="alert" class="alert alert-danger">
                                        {l s='Please enter an decimal number and use a dot instead of the comma' mod='sdevatos'}.
                                    </div>
                                </div>
                            </div>

                            {$configuration.payment_method.max_amount}
                        </div>
                    </div><hr />
                    {* END MAX AMOUNT *}

                    {* START 3D-SECURE MIN AMOUNT *}
                    <div ng-if="contracts[configuration.payment_method.id_contract].has_3d_secure == true">
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='Enable 3D-Secure from' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.payment_method.front_errors.has_3d_secure_from" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="alert alert-danger">
                                            {l s='Please enter an decimal number and use a dot instead of the comma' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.payment_method.has_3d_secure_from}
                            </div>
                        </div><hr />
                    </div>
                    {* END 3D-SECURE MIN AMOUNT *}

                    {* START CASHING MODE *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='Cashing mode' mod='sdevatos'}</h4>

                            <div ng-if="configuration.payment_method.cashing_mode == 'VALIDATION'" class="form-group">
                                <div class="margin-form col-lg-12">
                                    <div role="alert" class="warn alert alert-warning">
                                        {l s='In manual mode, you must validate your payments before the validation deadline set by your bank on your bank platform' mod='sdevatos'}.
                                    </div>
                                </div>
                            </div>

                            {$configuration.payment_method.cashing_mode}
                        </div>
                    </div><hr />
                    {* END CASHING MODE *}

                    {* START DELAY *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='Delay before debit' mod='sdevatos'}</h4>

                            <div ng-if="update_configuration.payment_method.front_errors.delay" class="form-group">
                                <div class="margin-form col-lg-12">
                                    <div role="alert" class="alert alert-danger">
                                        {l s='Please enter an integer number' mod='sdevatos'}.
                                    </div>
                                </div>
                            </div>

                            {$configuration.payment_method.delay}
                        </div>
                    </div>
                    {* END DELAY *}

                    {* START FIRST CASHING PERCENTAGE *}
                    <div ng-if="is_first_cashing_percentage_displayed"><hr />
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="sdev-no-mt">{l s='First cashing percentage' mod='sdevatos'}</h4>

                                <div ng-if="update_configuration.payment_method.front_errors.first_cashing_percentage" class="form-group">
                                    <div class="margin-form col-lg-12">
                                        <div role="alert" class="alert alert-danger">
                                            {l s='Please enter an decimal number and use a dot instead of the comma' mod='sdevatos'}.
                                        </div>
                                    </div>
                                </div>

                                {$configuration.payment_method.first_cashing_percentage}
                            </div>
                        </div>
                    </div>
                    {* END FIRST CASHING PERCENTAGE *}
                </div>

                {* START FILTERS *}
                <hr>
                <div class="well">
                    <h3>
                        <i class="fa fa-filter fa-fw"></i>
                        {l s='Filters' mod='sdevatos'}
                    </h3>

                    {* START SHOPS *}
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sdev-no-mt">{l s='By shop' mod='sdevatos'}</h4>

                            {$configuration.payment_method.shop}
                        </div>
                    </div>
                    {* END SHOPS *}
                </div>
                {* END FILTERS *}
            </div>

            {* SAVE *}
            <div class="panel-footer text-right">
                <button ng-if="!update_configuration.payment_method.loading" ng-click="updatePaymentMethod()" class="btn btn-default">
                    <i class="fa fa-save fa-fw"></i>
                    {l s='Save' mod='sdevatos'}
                </button>
            </div>
            {* END SAVE *}
        </div>
        {* END PAYMENT METHOD EDITION *}
    </div>
</section>
