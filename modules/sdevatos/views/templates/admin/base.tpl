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

<main ng-controller="SdevAtosController"
    ng-init="getJsDefL('{$links.index|escape:'htmlall':'UTF-8'}')"
    id="module-{$module_name|escape:'htmlall':'UTF-8'}" {if $ps15}class="bootstrap"{/if}
>
    <nav ng-if="is_js_translations_loaded"
        ng-init="getTabs()"
        class="col-lg-2"
    >
        <ul class="list-group">
            <li ng-repeat="tab in tabs"
                ng-class="{ldelim}active: selectTab($first, tab.class_name){rdelim}"
                ng-click="changeTab(tab.class_name)"
                class="list-group-item"
            >
                <span ng-if="tab.class_name == 'AdminInfosdevatos'"><i class="fa fa-info fa-fw"></i></span>
                <span ng-if="tab.class_name == 'AdminConfigsdevatos'"><i class="fa fa-cog fa-fw"></i></span>
                [[ tab.name ]]
            </li>
        </ul>
    </nav>

    <div ng-repeat="tab in tabs"
        ng-show="is_js_translations_loaded && (tab.class_name == current_tab)"
        id="Content[[tab.class_name]]"
        class="panel {if $ps15}col-lg-9{else}col-lg-10{/if}"
        bind-html-compile="tab.template"
    ></div>

    <div class="clearfix"></div>
    <div class="col-xs-12" ng-if="htmlPub != ''" bind-html-compile="htmlPub"></div>
</main>

<!-- Angular module. -->
<script>angular.bootstrap(document, ['SdevAtosApp'])</script>
