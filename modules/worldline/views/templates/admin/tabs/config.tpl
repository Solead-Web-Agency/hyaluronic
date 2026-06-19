{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<h3>
    <i class="icon-link"></i> {l s='Configure' mod='worldline'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
</h3>
<div class="panel-body">
    <div class="row-fluid" class="text-left">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <p><b>{l s='Welcome to the interface of your WORLDLINE page !' mod='worldline'}</b></p>
            <ul style='list-style-type:circle;'>
                <li>{l s='Afin de profiter pleinement des fonctionnalités offertes par le module ATOS WORDLINE, il est nécéssaire que vous ayez souscrit un contrat de VAD auprès de votre banque. C\'est cette dernière et ATOS qui vous fournirons les éléments nécessaires (ID Marchand et Clé) à la configuration et la mise en production de votre module.' mod='worldline'}</li>
                <li>{l s='Ce module vous permet de gérer les paiement de 1 à 4 fois. Il vous permet aussi  et de gérer l\'activation du 3D secure.' mod='worldline'}</li>
            </ul>
        </div>
        <div class="col-md-12">
            <h4><i class="icon-globe"></i>&nbsp;&nbsp;{l s='Mode de fonctionnement du module : ' mod='worldline'}</h4>
            <hr/>
            <p>{l s='Nous vous proposons un mode test et un mode production. Nous vous recommandons le mode "test" dans un premier temps afin de tester le bon fonctionnement du module. Après avoir valider son fonctionnement et effectuez des commandes, vous pouvez passer en production.' mod='worldline'}</p>
            <form role="form" id="worldline_form" action="" method="post">
                <div>
                    <span id="range_options" class="switch prestashop-switch input-group col-lg-4">
                        <input type="radio" name="atos_mode" id="production" {if $atos_mode eq 1}checked="checked"{/if} value="1" />
                        <label for="production" class="radioCheck">
                                <i class="color_success"></i> {l s='Production' mod='worldline'}
                        </label>
                        <input type="radio" name="atos_mode" id="test_mode" {if $atos_mode eq 0}checked="checked"{/if} value="0"/>
                        <label for="test_mode" class="radioCheck">
                                <i class="color_danger"></i> {l s='Test Mode' mod='worldline'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                    <br/>
                </div>
                <div id="2" {if $atos_mode eq 0}style="display:none;"{/if}>
                    <hr />
                    <h4><i class="icon-user"></i>&nbsp;&nbsp;{l s='Vos identifiants : ' mod='worldline'}</h4>
                    <p>{l s='Vous avez souscrits un contrat VAD ? Votre banque / ATOS WORLDLINE vont vous envoyer vos identifiants.' mod='worldline'}</p>
                    <div class="clear">&nbsp;</div>
          <div class="input-group col-md-5">
            <span>
              <b>{l s='Bank selection' mod='worldline'}</b>
            </span>
            <select name="worldline_bank_selector" required>
              <option value="sogenactif" {if $psp_brand eq 'sogenactif'}selected{/if}>{l s='Société Générale - Sogenactif 2.0' mod='worldline'}</option>
              <option value="worldlineDirect" {if $psp_brand eq 'worldlineDirect'}selected{/if}>{l s='Wordline Direct' mod='worldline'}</option>
            </select>
          </div>
                    <div class="clear">&nbsp;</div>
                    <div class="input-group col-md-5">
                        <span>
                            <b>{l s='Merchant ID' mod='worldline'}</b>
                        </span>
                        <input type="text" class="form-control atos_mode_only" name="atos_merchant_id" id="atos_merchant_id" value="{if isset($atos_merchant_id)}{$atos_merchant_id|escape:'htmlall':'UTF-8'}{/if}" {if $atos_mode eq 0}disabled="disabled"{/if}>
                    </div>
                    <div class="clear">&nbsp;</div>
                    <div class="input-group col-md-5">
                        <span>
                            <b>{l s='Secret Key' mod='worldline'}</b>
                        </span>
                        <input type="text" class="form-control atos_mode_only" name="atos_secret_key" id="atos_secret_key" value="{if isset($atos_secret_key)}{$atos_secret_key|escape:'htmlall':'UTF-8'}{/if}" {if $atos_mode eq 0}disabled="disabled"{/if}>
                    </div>
                    <div class="clear">&nbsp;</div>
                    <div class="input-group col-md-5">
                        <span>
                            <b>{l s='Version Key' mod='worldline'}</b>
                        </span>
                        <input type="text" class="form-control atos_mode_only" name="atos_version_key" id="atos_version_key" value="{if isset($atos_version_key)}{$atos_version_key|escape:'htmlall':'UTF-8'}{/if}" {if $atos_mode eq 0}disabled="disabled"{/if}>
                    </div>
                    <div class="clear">&nbsp;</div>
                </div>
                <hr />
                <h4><i class="icon-shopping-cart"></i>&nbsp;&nbsp;{l s='Configurez la méthode de paiement : ' mod='worldline'}</h4>
                <p>{l s='En fonction du montant des commandes de vos clients, laissez les payer en 1, 2, 3 ou 4 fois ! Cela peut vous permettre d\'optimiser votre taux de conversion.' mod='worldline'}</p>
                <div class="well well-sm" id="duplicate">
                    <p><i class="icon-exclamation-triangle icon-3x" style="float:left; padding: 0px 10px 0px 3px;"></i>
                        {l s='Make sure you have the instalment payment option subscribed before you activate it.' mod='worldline'}
                        <br><br>
                    </p>
                </div>
                <div class="clear">&nbsp;</div>
                <div class="form-group form-inline">
                    <table>
                        <tr>
                            <td width="200px">
                                <h4>{l s='2x Payment' mod='worldline'}</h4>
                            </td>
                            <td width="150px">
                                <center>
                                <span id="2xPayment" class="switch prestashop-switch input-group col-lg-12">
                                    <input type="radio" name="2x" id="2xOn" {if $2x_payment eq 1}checked="checked"{/if} value="1" />
                                    <label for="2xOn" class="radioCheck">
                                            <i class="color_success"></i> {l s='Yes' mod='worldline'}
                                    </label>
                                    <input type="radio" name="2x" id="2xOff" {if $2x_payment eq 0}checked="checked"{/if} value="0"/>
                                    <label for="2xOff" class="radioCheck">
                                            <i class="color_danger"></i> {l s='No' mod='worldline'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                                </center>
                            </td>
                            <td width="300px">
                                <div class="input-group col-lg-7" style="margin-left: 50px;">
                                    <span class="input-group-addon">{l s='From' mod='worldline'}</span>
                                    {if $default_currency eq "$"}
                                        <span class="input-group-addon">{$default_currency|escape:'htmlall':'UTF-8'}
                                        </span>
                                    {/if}
                                    <input type="text" class="form-control isInt" name="2x_from" id="2x_from" value="{if isset($2x_from)}{$2x_from|escape:'htmlall':'UTF-8'}{/if}" {if $2x_payment eq 0}disabled="disabled"{/if} size="10" width="300px">
                                    {if $default_currency neq "$"}
                                        <span class="input-group-addon">{$default_currency|escape:'htmlall':'UTF-8'}
                                        </span>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div><div class="form-group form-inline">
                    <table>
                        <tr>
                            <td width="200px">
                                <h4>{l s='3x Payment' mod='worldline'}</h4>
                            </td>
                            <td width="150px">
                                <center>
                                <span id="3xPayment" class="switch prestashop-switch input-group col-lg-12">
                                    <input type="radio" name="3x" id="3xOn" {if $3x_payment eq 1}checked="checked"{/if} value="1" />
                                    <label for="3xOn" class="radioCheck">
                                            <i class="color_success"></i> {l s='Yes' mod='worldline'}
                                    </label>
                                    <input type="radio" name="3x" id="3xOff" {if $3x_payment eq 0}checked="checked"{/if} value="0"/>
                                    <label for="3xOff" class="radioCheck">
                                            <i class="color_danger"></i> {l s='No' mod='worldline'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                                </center>
                            </td>
                            <td width="300px">
                                <div class="input-group col-lg-7" style="margin-left: 50px;">
                                    <span class="input-group-addon">{l s='From' mod='worldline'}</span>
                                    {if $default_currency eq "$"}
                                        <span class="input-group-addon">{$default_currency|escape:'htmlall':'UTF-8'}
                                        </span>
                                    {/if}
                                    <input type="text" class="input-mini form-control isInt" name="3x_from" id="3x_from" value="{if isset($3x_from)}{$3x_from|escape:'htmlall':'UTF-8'}{/if}" {if $3x_payment eq 0}disabled="disabled"{/if} size="10">
                                    {if $default_currency neq "$"}
                                        <span class="input-group-addon">{$default_currency|escape:'htmlall':'UTF-8'}
                                        </span>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div><div class="form-group form-inline">
                    <table>
                        <tr>
                            <td width="200px">
                                <h4>{l s='4x Payment' mod='worldline'}</h4>
                            </td>
                            <td width="150px">
                                <center>
                                <span id="4xPayment" class="switch prestashop-switch input-group col-lg-12">
                                    <input type="radio" name="4x" id="4xOn" {if $4x_payment eq 1}checked="checked"{/if} value="1" />
                                    <label for="4xOn" class="radioCheck">
                                            <i class="color_success"></i> {l s='Yes' mod='worldline'}
                                    </label>
                                    <input type="radio" name="4x" id="4xOff" {if $4x_payment eq 0}checked="checked"{/if} value="0"/>
                                    <label for="4xOff" class="radioCheck">
                                            <i class="color_danger"></i> {l s='No' mod='worldline'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                                </center>
                            </td>
                            <td width="300px">
                                <div class="input-group col-lg-7" style="margin-left: 50px;">
                                    <span class="input-group-addon">{l s='From' mod='worldline'}</span>
                                    {if $default_currency eq "$"}
                                        <span class="input-group-addon">{$default_currency|escape:'htmlall':'UTF-8'}
                                        </span>
                                    {/if}
                                    <input type="text" class="input-mini form-control isInt" name="4x_from" id="4x_from" value="{if isset($4x_from)}{$4x_from|escape:'htmlall':'UTF-8'}{/if}" {if $4x_payment eq 0}disabled="disabled"{/if} size="10">
                                    {if $default_currency neq "$"}
                                        <span class="input-group-addon">{$default_currency|escape:'htmlall':'UTF-8'}
                                        </span>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="clear">&nbsp;</div>
                <hr />
                <h4><i class="icon-lock"></i>&nbsp;&nbsp;{l s='3D secure : ' mod='worldline'}</h4>
                <p>{l s='Selon votre contrat de VAD et votre banque, vous pouvez personnaliser l\'activation ou non du 3DS en fonction du montant des commandes.' mod='worldline'}</p>
                <div class="well well-sm" id="duplicate">
                    <p><i class="icon-exclamation-triangle icon-3x" style="float:left; padding: 0px 10px 0px 3px;"></i>
                        {l s='Make sure you have the 3D secure option subscribed before you activate it.' mod='worldline'}
                        <br><br>
                    </p>
                </div>
                <div class="form-group form-inline">
                    <table>
                        <tr>
                            <td width="100px">
                                <h4>{l s='3D Secure' mod='worldline'}</h4>
                            </td>
                            <td width="150px">
                                <center>
                                <span id="3xPayment" class="switch prestashop-switch input-group col-lg-12">
                                    <input type="radio" name="3DS" id="3DSOn" {if $3ds eq 1}checked="checked"{/if} value="1" />
                                    <label for="3DSOn" class="radioCheck">
                                            <i class="color_success"></i> {l s='Yes' mod='worldline'}
                                    </label>
                                    <input type="radio" name="3DS" id="3DSOff" {if $3ds eq 0}checked="checked"{/if} value="0"/>
                                    <label for="3DSOff" class="radioCheck">
                                            <i class="color_danger"></i> {l s='No' mod='worldline'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                                </center>
                            </td>
                        </tr>
                        </table>
                </div>
                <hr />
                <div id="3" {if $atos_mode eq 0}style="display:none;"{/if}>
                <h4><i class="icon-cog"></i>&nbsp;&nbsp;{l s='Génération du champ "transaction reference" : ' mod='worldline'}</h4>
                <p>{l s='Selon votre contrat de VAD et votre banque, vous pouvez être amené à activer ou désactiver la génération du champ "transaction référence".' mod='worldline'}</p>
                <div class="well well-sm" id="duplicate">
                    <p><i class="icon-exclamation-triangle icon-3x" style="float:left; padding: 0px 10px 0px 3px;"></i>
                        {l s='Select ON in order to get the transaction reference generated by PrestaShop. If you prefer to let the bank generate this field select OFF.' mod='worldline'}
                        <br><br>
                    </p>
                </div>
                <div class="form-group form-inline">
                    <table>
                        <tr>
                            <td width="100px">
                                <h4>{l s='Transaction reference' mod='worldline'}</h4>
                            </td>
                            <td width="150px">
                                <center>
                                <span id="Transactionref" class="switch prestashop-switch input-group col-lg-12">
                                    <input type="radio" name="TF" id="TFOn" {if $transactionRef eq 1}checked="checked"{/if} value="1" />
                                    <label for="TFOn" class="radioCheck">
                                            <i class="color_success"></i> {l s='ON' mod='worldline'}
                                    </label>
                                    <input type="radio" name="TF" id="TFOff" {if $transactionRef eq 0}checked="checked"{/if} value="0"/>
                                    <label for="TFOff" class="radioCheck">
                                            <i class="color_danger"></i> {l s='OFF' mod='worldline'}
                                    </label>
                                    <a class="slide-button btn"></a>
                                </span>
                                </center>
                            </td>
                        </tr>
                        </table>
                </div>
                <hr />
                </div>
                <h4><i class="icon-remove-sign"></i>&nbsp;&nbsp;{l s='Payment error : ' mod='worldline'}</h4>
                    <!-- Multiple Radios -->
                        <table>
                        <tr><td>
                            <label class="radio" for="error_save">
                              {l s='Save order as error' mod='worldline'}
                            </label>
                        </td><td>
                          <input name="radios" id="error_save" value="save" type="radio" {if $error_mail eq "save"}checked{/if}>
                        </td></tr>
                        <tr><td>
                            <label class="radio" for="error_mail_save">
                                {l s='Send email and save order as error' mod='worldline'}
                            </label>
                        </td><td>
                            <input name="radios" id="error_mail_save" value="mail_save" {if $error_mail eq "mail_save"}checked{/if} type="radio">
                        </td></tr>
                        <tr><td>
                            <label class="radio" for="error_mail">
                              {l s='Send email and don\'t save order as error' mod='worldline'}
                            </label>
                        </td><td>
                          <input name="radios" id="error_mail" value="mail" type="radio" {if $error_mail eq "mail"}checked{/if}>
                        </td></tr>
                        <tr><td>
                            <label class="radio" for="error_nothing">
                              {l s='Do nothing' mod='worldline'}
                            </label>
                        </td><td>
                          <input name="radios" id="error_nothing" value="nothing" type="radio" {if $error_mail eq "nothing"}checked{/if}>
                        </td></tr>
                        <tr><td width="200px">
                            <label for="error_mails">
                              <h4>{l s='Mails list (seperate with coma)' mod='worldline'}</h4>
                            </label>
                        </td><td>
                          <input name="error_mails" id="error_mails" value="{$error_mails|escape:'htmlall':'UTF-8'}" type="text" style="width: 500px;" {if $error_mail eq "nothing" or $error_mail eq "save"}disabled="disabled"{/if}>
                        </td></tr>
                      </table>
            </form>
        </div>
    </div>
    <div class="">
        <a class="btn btn-default pull-right" onclick="$('#worldline_form').submit();">
        <i class="process-icon-save">&nbsp;</i>
            {l s='Update' mod='worldline'}
        </a>
    </div>
</div>
<script>
var atos_token = '{$atos_token|escape:'htmlall':'UTF-8'}';
</script>
