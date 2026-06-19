{*
* 2007-2022 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="ets_abancart_wrapper_overload">
	<div class="table">
		<div class="table-cell">
			<div class="wrapper_form">
				<form id="ets_abancart_send_test_mail" class="defaultForm form-horizontal" action="{if isset($action) && $action}{$action|escape:'quotes':'UTF-8'}{else}{$smarty.server.HTTP_HOST|cat:$smarty.server.REQUEST_URI|escape:'quotes':'UTF-8'}{/if}" novalidate method="post" enctype="multipart/form-data">
					<div id="fieldset_1" class="panel">
						<div class="panel-heading">
                            {l s='Send test email' mod='ets_abandonedcart'}
                            <span class="sendmail_cancel">+</span>
                        </div>
						<div class="form-wrapper">
							<div class="form-group form-group-email required isEmail">
								<label class="control-label col-lg-3 required">
                                    {l s='Email' mod='ets_abandonedcart'}
								</label>
								<div class="col-lg-9">
									<input id="email" type="text" name="email" placeholder="{l s='Email address' mod='ets_abandonedcart'}">
									<p class="help-block">
                                        {l s='Enter an email address to receive test email' mod='ets_abandonedcart'}
									</p>
								</div>
							</div>
						</div>
						<div class="panel-footer">
                            <button type="button" class="btn btn-default sendmail_cancel">
								<i class="process-icon-cancel"></i> {l s='Cancel' mod='ets_abandonedcart'}
							</button>
							<button type="submit" value="1" id="configuration_form_send_test_mail_btn" name="submitSendTestMail" class="btn btn-default pull-right">
								<i class="process-icon-envelope"></i> {l s='Send' mod='ets_abandonedcart'}
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>