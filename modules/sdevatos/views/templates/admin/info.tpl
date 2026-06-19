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

{if $ps15}{assign var="col_lg_9" value="col-lg-8"}
{else}{assign var="col_lg_9" value="col-lg-9"}{/if}

<section id="module-{$module_name|escape:'htmlall':'UTF-8'}-info">
    <header>
        <h3>
            <i class="fa fa-info"></i>&nbsp;
            {l s='Informations' mod='sdevatos'}
        </h3>
    </header>

    <div class="form-horizontal">
        <header class="col-lg-12">
            {* LOGO SCALEDEV *}
            <img id="{$module_name|escape:'htmlall':'UTF-8'}-scaledev-logo" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logos/logo-scaledev-full.png" alt="ScaleDEV" />

            {* LOGO MODULE *}
            <img id="{$module_name|escape:'htmlall':'UTF-8'}-module-logo" src="{$module_dir|escape:'htmlall':'UTF-8'}logo.png" alt="Atos - Worldline" />
        </header>

        {* PRESTASHOP VERSION *}
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='PrestaShop version' mod='sdevatos'}
            </label>

            <div class="margin-form col-lg-9">
                {if version_compare($smarty.const._PS_VERSION_, '1.5.4.0', '<')}
                    <div class="warn alert alert-danger">
                        <p>
                            {l s='PrestaShop version not compatible (minimal requires : 1.5.4.0)' mod='sdevatos'}
                            <em>({l s='used version' mod='sdevatos'} : {$smarty.const._PS_VERSION_|escape:'htmlall':'UTF-8'})</em>
                        </p>
                    </div>
                {else}
                    <div class="conf alert alert-success">
                        <p>
                            {l s='Compatible PrestaShop version' mod='sdevatos'}
                            <em>({l s='used version' mod='sdevatos'} : {$smarty.const._PS_VERSION_|escape:'htmlall':'UTF-8'})</em>
                        </p>
                    </div>
                {/if}
            </div>
        </div>
        {* END PRESTASHOP VERSION *}

        {* OVERRIDES *}
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Overrides detected' mod='sdevatos'}
            </label>

            <div class="margin-form col-lg-9">
                {if !$has_overrides}
                    <div class="conf alert alert-success">
                        <p>{l s='Any override detected' mod='sdevatos'}</p>
                    </div>
                {else}
                    <div class="warn alert alert-warning">
                        <p>
                            {l s='One or many overrides have been detected. The operation of the module can be altered. If you encounter a problem, it is necessary to specify it in the support request' mod='sdevatos'}.
                        </p>

                        {* CONTROLLERS *}
                        {if $overrides.controllers && !empty($overrides.controllers)}
                            <p>
                                {if count($overrides.controllers) > 1}{l s='Controllers :' mod='sdevatos'}<br/ >
                                {else}{l s='Controller :' mod='sdevatos'}<br />{/if}
                                {foreach $overrides.controllers as $controller_overrided}
                                    - {$controller_overrided.path|escape:'htmlall':'UTF-8'}<br />
                                {/foreach}
                            </p>
                        {/if}

                        {* CLASSES *}
                        {if $overrides.classes && !empty($overrides.classes)}
                            <p>
                                {if count($overrides.classes) > 1}{l s='Classes :' mod='sdevatos'}<br />
                                {else}{l s='Classe :' mod='sdevatos'}<br />{/if}
                                {foreach $overrides.classes as $class_overrided}
                                    - {$class_overrided.path|escape:'htmlall':'UTF-8'}<br />
                                {/foreach}
                            </p>
                        {/if}
                    </div>
                {/if}
            </div>
        </div>
        {* END OVERRIDES *}

        {* PHP VERSION *}
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='PHP version' mod='sdevatos'}
            </label>

            <div class="margin-form col-lg-9">
                {if phpversion() < '5.6'}
                    <div class="warn alert alert-danger">
                        <p>
                            {l s='Unsupported PHP version for the module (minimal required : 5.6)' mod='sdevatos'}
                            <em>({l s='used version' mod='sdevatos'} : {phpversion()|escape:'htmlall':'UTF-8'})</em>
                        </p>
                    </div>
                {else}
                    <div class="conf alert alert-success">
                        <p>
                            {l s='Compatible PHP version' mod='sdevatos'}
                            <em>({l s='used version' mod='sdevatos'} : {phpversion()|escape:'htmlall':'UTF-8'})</em>
                        </p>
                    </div>
                {/if}
            </div>
        </div>
        {* END PHP VERSION *}

        {* PHP SETTINGS *}
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Settings of php.ini' mod='sdevatos'}
            </label>

            <div class="margin-form col-lg-9">
                {* MAX_EXECUTION_TIME *}
                <div class="warn alert alert-{if ini_get('max_execution_time') < 120}warning{else}info{/if}">
                    max_execution_time : {ini_get('max_execution_time')|escape:'htmlall':'UTF-8'}
                </div>

                {* MEMORY_LIMIT *}
                <div class="warn alert alert-{if ini_get('memory_limit')|replace:'M':'' < 256}warning{else}info{/if}">
                    memory_limit : {ini_get('memory_limit')|escape:'htmlall':'UTF-8'}
                </div>

                {* MAX_INPUT_VARS *}
                <div class="warn alert alert-info">
                    max_input_vars : {ini_get('max_input_vars')|escape:'htmlall':'UTF-8'}
                </div>
            </div>
        </div>
        {* END PHP SETTINGS *}

        {* MODULE VERSION *}
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Module version' mod='sdevatos'}
            </label>

            <div class="margin-form col-lg-9">
                <div class="warn alert alert-info">
                    <p>{$module_version|escape:'htmlall':'UTF-8'}</p>
                </div>
            </div>
        </div>
        {* END MODULE VERSION *}

        {* START DOCUMENTATION *}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Documentations' mod='sdevatos'}</label>

            <div class="margin-form col-lg-9">
                {foreach $doc_list as $doc_iso => $doc_file name=doc_loop}
                    {if $smarty.foreach.doc_loop.index > 0}&nbsp;{/if}
                    <a href="{$module_dir|escape:'htmlall':'UTF-8'}{$doc_file|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-default">
                        <i class="fa fa-book"></i>&nbsp;
                        {$doc_iso|escape:'htmlall':'UTF-8'|upper}
                    </a>
                {/foreach}
            </div>
        </div><br />
        {* END DOCUMENTATION *}

        {* LICENCE *}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Licence' mod='sdevatos'}</label>

            <div class="margin-form col-lg-9">
                <div class="warn alert alert-warning">
                    <p>
                        <strong>
                            {l s='This module is under a commerciale licence from ScaleDEV society.' mod='sdevatos'}<br />
                            {l s='Any unauthorized use find yourself liable' mod='sdevatos'}
                        </strong>
                    </p>
                </div>
            </div>
        </div>
        {* END LICENCE *}

        {* SUPPORT *}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Support' mod='sdevatos'}</label>

            <div class="margin-form col-lg-9">
                <a href="https://addons.prestashop.com/fr/contactez-nous?id_product=48071" target="_blank" class="btn btn-default">
                    <i class="fa fa-envelope"></i>&nbsp;
                    {l s='Click here to contact us' mod='sdevatos'}
                </a>
            </div>
        </div>
        {* END SUPPORT *}
    </div>
</section>
