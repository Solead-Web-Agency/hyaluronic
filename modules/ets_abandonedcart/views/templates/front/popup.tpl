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
<div class="ets_abancart_shopping_cart">
	<span class="ets_abancart_close" title="{l s='Close' mod='ets_abandonedcart'}"></span>
	<div class="ets_abancart_form_save_cart">
		<div class="front front_login panel">
			<h4>
				<i class="svg_fill_gray lh_18">
				<svg class="w_16 h_16" width="16" height="16" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1596 380q28 28 48 76t20 88v1152q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1600q0-40 28-68t68-28h896q40 0 88 20t76 48zm-444-244v376h376q-10-29-22-41l-313-313q-12-12-41-22zm384 1528v-1024h-416q-40 0-68-28t-28-68v-416h-768v1536h1280z"/></svg>
				</i> {l s='Save your shopping cart?' mod='ets_abandonedcart'}</h4>
			<p class="alert alert-info">{l s='You have %d items in your shopping cart, do you want to save your shopping to checkout later?' sprintf=[$product_count] mod='ets_abandonedcart'}</p>
			<form action="{$link_action nofilter}" id="save_cart_form" method="post">
				<input type="hidden" name="id_customer" id="id_customer" value="{if isset($id_customer)}{$id_customer|intval}{/if}"/>
				<input type="hidden" name="submitCart" id="submitCart" value="1"/>
				<div class="form-group">
					<label class="control-label">{l s='Cart name' mod='ets_abandonedcart'}<span class="required">*</span></label>
					<input name="cart_name" type="text" id="cart_name" class="form-control" value="" autofocus="autofocus" tabindex="1" required />
				</div>
				<div class="form-group row-padding-top">
					<button id="submit_cart" class="submit_cart btn btn-primary" tabindex="2" name="submitCart">
                       <i class="svg_fill_white svg_fill_hover_white lh_18">
                        <svg class="w_16 h_16" width="16" height="16" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M512 1536h768v-384h-768v384zm896 0h128v-896q0-14-10-38.5t-20-34.5l-281-281q-10-10-34-20t-39-10v416q0 40-28 68t-68 28h-576q-40 0-68-28t-28-68v-416h-128v1280h128v-416q0-40 28-68t68-28h832q40 0 68 28t28 68v416zm-384-928v-320q0-13-9.5-22.5t-22.5-9.5h-192q-13 0-22.5 9.5t-9.5 22.5v320q0 13 9.5 22.5t22.5 9.5h192q13 0 22.5-9.5t9.5-22.5zm640 32v928q0 40-28 68t-68 28h-1344q-40 0-68-28t-28-68v-1344q0-40 28-68t68-28h928q40 0 88 20t76 48l280 280q28 28 48 76t20 88z"/></svg>
                       </i> {l s='Save cart' mod='ets_abandonedcart'}
                    </button>
					{*
					<button id="submit_off" class="submit_off btn btn-primary" tabindex="3" name="submitOff">{l s='No, don\'t show it anymore' mod='ets_abandonedcart'}</button>
					*}
					<a class="ets_abancart_checkout btn btn-primary" href="{$link_checkout nofilter}">
						<i class="svg_fill_white svg_fill_hover_white lh_18">
							<svg class="w_18 h_18" width="18" height="18" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M704 1536q0 52-38 90t-90 38-90-38-38-90 38-90 90-38 90 38 38 90zm896 0q0 52-38 90t-90 38-90-38-38-90 38-90 90-38 90 38 38 90zm128-1088v512q0 24-16.5 42.5t-40.5 21.5l-1044 122q13 60 13 70 0 16-24 64h920q26 0 45 19t19 45-19 45-45 19h-1024q-26 0-45-19t-19-45q0-11 8-31.5t16-36 21.5-40 15.5-29.5l-177-823h-204q-26 0-45-19t-19-45 19-45 45-19h256q16 0 28.5 6.5t19.5 15.5 13 24.5 8 26 5.5 29.5 4.5 26h1201q26 0 45 19t19 45z"/></svg>
						</i> {l s='Checkout now' mod='ets_abandonedcart'}
					</a>
				</div>
			</form>
		</div>
	</div>
	<div class="ets_abancart_form_login" style="display: none;">
		<div class="front front_login panel">
			<p>{l s='Oops! You have not logged in. Please log in to complete saving your cart' mod='ets_abandonedcart'}</p>
			<h4>{l s='Log in' mod='ets_abandonedcart'}</h4>
			<form action="{$link_action nofilter}" id="login_form" method="post">
				<input id="submitLogin" name="submitLogin" type="hidden" value="1">
				<div class="form-group">
					<label class="control-label" for="email2">{l s='Email address' mod='ets_abandonedcart'}</label>
					<input name="email2" type="email" id="email2" class="form-control" value="" autofocus="autofocus" autocomplete="email" tabindex="1" placeholder="test@example.com" />
				</div>
				<div class="form-group">
					<label class="control-label" for="passwd2">{l s='Password' mod='ets_abandonedcart'}</label>
					<input name="passwd2" type="password" id="passwd2" class="form-control" value="" tabindex="2" autocomplete="password" placeholder="{l s='Password' mod='ets_abandonedcart'}" />
				</div>
				<div class="form-group row-padding-top">
					<button id="submit_login" name="submitLogin" type="submit" tabindex="3" class="btn btn-primary btn-lg btn-block ladda-button" data-style="slide-up" data-spinner-color="white" >
						<span class="ladda-label">{l s='Log in and save cart' mod='ets_abandonedcart'}</span>
					</button>
				</div>
				<div class="form-group text-center">
					<a href="#" class="ets_abancart_create_account">
                        {l s='Do not have account? Register now' mod='ets_abandonedcart'}
					</a>
				</div>
			</form>
		</div>
	</div>
	<div class="ets_abancart_form_create" style="display: none;">
		<div class="front front_create panel">
			<h4>{l s='Register' mod='ets_abandonedcart'}</h4>
			<form action="{$link_action nofilter}" id="create_form" method="post">
				<input id="submitCreate" name="submitCreate" type="hidden" value="1">
				<div class="form-group">
					<label class="control-label" for="firstname3">{l s='First name' mod='ets_abandonedcart'}</label>
					<input name="firstname3" type="text" id="firstname3" class="form-control" value="" autofocus="autofocus" tabindex="1" />
				</div>
				<div class="form-group">
					<label class="control-label" for="lastname3">{l s='Last name' mod='ets_abandonedcart'}</label>
					<input name="lastname3" type="text" id="lastname3" class="form-control" value="" autofocus="autofocus" tabindex="2" />
				</div>
				<div class="form-group">
					<label class="control-label" for="email3">{l s='Email address' mod='ets_abandonedcart'}</label>
					<input name="email3" type="email" id="email3" class="form-control" value="" autofocus="autofocus" autocomplete="email" tabindex="3" placeholder="test@example.com" />
				</div>
				<div class="form-group">
					<label class="control-label" for="passwd3">{l s='Password' mod='ets_abandonedcart'}</label>
					<input name="passwd3" type="password" id="passwd3" class="form-control" value="" tabindex="4" autocomplete="password" placeholder="{l s='Password' mod='ets_abandonedcart'}"/>
				</div>
				<div class="form-group row-padding-top">
					<button id="submit_create" name="submitCreate" type="submit" tabindex="5" class="btn btn-primary btn-lg btn-block ladda-button" data-style="slide-up" data-spinner-color="white" >
						<span class="ladda-label">{l s='Register and save cart' mod='ets_abandonedcart'}</span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>