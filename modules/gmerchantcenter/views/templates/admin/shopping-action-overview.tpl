{*
*
* Google merchant center Pro
*
* @author BusinessTech.fr
* @copyright Business Tech
*
*           ____    _______
*          |  _ \  |__   __|
*          | |_) |    | |
*          |  _ <     | |
*          | |_) |    | |
*          |____/     |_|
*
*}

<div class="col-xs-12">

	<div class="clr_50"></div>
	
	<div class="row">
		<div class="col-xs-12 col-lg-3 text-center">
			<i class="fa fa-money gsa-icon-market" aria-hidden="true"></i>
			<div class="clr_10"></div>
			<p class="advantages-label">{l s='Promote at lower cost' mod='gmerchantcenter'}</p>
			<p>{l s='No subscription fees' mod='gmerchantcenter'}
			<br />{l s='Only commissions on sales' mod='gmerchantcenter'}
			<br />{l s='Commission rates lower than Amazon' mod='gmerchantcenter'}</p>			
		</div>
		<div class="col-xs-12 col-lg-3 text-center">
			<i class="fa fa-desktop gsa-icon-market" aria-hidden="true"></i>
			<div class="clr_10"></div>
			<p class="advantages-label">{l s='Improve your visibility' mod='gmerchantcenter'}</p>
			<p>{l s='Products for purchase on Google Shopping' mod='gmerchantcenter'}
			<br />{l s='Products for purchase on Google Voice Assistant' mod='gmerchantcenter'}
			<br/>{l s='Soon on all Google platforms' mod='gmerchantcenter'}</p>
		</div>
		<div class="col-xs-12 col-lg-3 text-center">
			<i class="fa fa-line-chart gsa-icon-market" aria-hidden="true"></i>
			<div class="clr_10"></div>
			<p class="advantages-label">{l s='Increase your sales' mod='gmerchantcenter'}</p>
			<p>{l s='People buy directly on Google' mod='gmerchantcenter'}
			<br/>{l s='Payment info saved' mod='gmerchantcenter'}
			<br/>{l s='Login info saved' mod='gmerchantcenter'}</p>	
		</div>
		<div class="col-xs-12 col-lg-3 text-center">
			<i class="fa fa-handshake-o gsa-icon-market" aria-hidden="true"></i>
			<div class="clr_10"></div>
			<p class="advantages-label">{l s='Boost loyalty' mod='gmerchantcenter'}</p>
			<p>{l s='Simple, secure and fast buying' mod='gmerchantcenter'}
			<br />{l s='Google Purchase Warranties' mod='gmerchantcenter'}
			<br />{l s='Visibility that increases with purchases number' mod='gmerchantcenter'}</p>
		</div>
	</div>

	<div class="clr_50"></div>

	<div class="col-xs-12 text-center">
		{if $sCurrentIso == "fr"} 
			<a href="https://docs.google.com/forms/d/e/1FAIpQLSfRhvz2jOIsnZYPgWXjx-BnO52thLkWZ8_ZBPARqIT-e7w4zw/viewform?usp=sf_link" target="_blank" class="text-center btn btn-lg btn-success btn-request-beta"><i class="fa fa-sign-in"></i>&nbsp;{l s='Request beta access' mod='gmerchantcenter'}</a>
		{else}
			<a href="https://docs.google.com/forms/d/e/1FAIpQLSeUUTWh9NS3eln_MSihgmArirO4Uq6ghzDIV9JEIgqa9w7cug/viewform?usp=sf_link" target="_blank" class="text-center btn btn-lg btn-success btn-request-beta"><i class="fa fa-sign-in"></i>&nbsp;{l s='Request beta access' mod='gmerchantcenter'}</a>
		{/if}
	</div>	
	
	<div class="clr_50"></div>

	<p class="col-xs-12 market-text">
		<a href="https://www.google.com/retail/solutions/shopping-actions/" target="_blank">{l s='Google Shopping Actions' mod='gmerchantcenter'}</a>&nbsp;{l s='is a program that allows every Internet user to buy, in a single transaction, products coming from different sites directly on Google. In addition, thanks to the recording of payment and delivery information, their shopping journey is made as simple as possible.' mod='gmerchantcenter'}
	</p class="col-xs-12">

	<div class="clr_10"></div>

	<div class="col-xs-12 market-text">
		{l s='For more details:' mod='gmerchantcenter'}
		<div class="clr_10"></div>
		<ul>
			<li><a href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/331">{l s='How to participate in Google Shopping Actions program?' mod='gmerchantcenter'}</a></li>
			<li><a href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/332">{l s='What are the advantages of Google Shopping Actions for e-merchants?' mod='gmerchantcenter'}</a></li>
			<li><a href="{$smarty.const._GMC_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/333">{l s='Why sign up for our beta now?' mod='gmerchantcenter'}</a></li>
		</ul>	
	</div>	

	<div class="clr_10"></div>
	<div class="clr_hr"></div>
	<div class="clr_20"></div>
</div>	