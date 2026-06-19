{*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if $provider_name == 'GoogleTranslate'}
	<ol>
		<li>{l s='Log in to [1]%s[/1]' mod='autotranslator' sprintf=['https://console.cloud.google.com/'] tags=['<span class="b">']}</li>
		<li>{l s='Click the [1]Project[/1] drop-down in top panel' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>
			{l s='Click [1]New project[/1], specify [1]Project name[/1], click [1]Create[/1]' mod='autotranslator' tags=['<span class="b">']}.
			{l s='Wait until you are redirected to dashboard' mod='autotranslator'}.
		<li>
			{l s='Click on sandwitch menu in top left corner and select [1]APIs & Services[/1]' mod='autotranslator' tags=['<span class="b">']}
			<ul>
				<li>
					{l s='Click [1]+ ENABLE APIS AND SERVICES[/1]' mod='autotranslator' tags=['<span class="b">']},
					{l s='find [1]Cloud Translation API[/1] and [1]ENABLE[/1] it' mod='autotranslator' tags=['<span class="b">']}
				</li>
				<li>
					{l s='If you don\'t have a billing account, you will be asked to create one' mod='autotranslator'}
					<ul>
						<li>
							{l s='Fill required form and specify card details' mod='autotranslator'}.
							{l s='You should get 300 USD credit' mod='autotranslator'}
						</li>
					</ul>
				</li>
			</ul>
		</li>
		<li>
			{l s='Click on sandwitch menu in top left corner and select [1]APIs & Services > Credentials[/1]' mod='autotranslator' tags=['<span class="b">']}
			<ul>
				<li>{l s='Click [1]Create credentials > API Key[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
			</ul>
		</li>
	</ol>
{else if $provider_name == 'DeeplTranslate'}
	<ol>
		<li>
			{l s='Log in to [1]%s[/1]' mod='autotranslator' sprintf=['https://www.deepl.com/pro-account/'] tags=['<span class="b">']}
			<ul>
				<li>{l s='If you don\'t have an account, register at [1]%s[/1]'
					mod='autotranslator' sprintf=['https://www.deepl.com/pro-checkout/account?productId=1200&yearly=false&trial=false'] tags=['<span class="b">']}
				</li>
			</ul>
		</li>
		<li>{l s='On [1]"Plan" tab[/1] you can select [2]Free[/2] or [2]Pro[/2] Subscription plan' mod='autotranslator' tags=['<span class="b">', '<span class="i">']}</li>
		<li>{l s='On [1]"Account" tab[/1] you will find [1]Authentication Key for DeepL API[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>{l s='Paste that key in the field above' mod='autotranslator'}</li>
	</ol>
{else if $provider_name == 'IBMTranslate'}
	<ol>
		<li>{l s='Log in to [1]%s[/1]' mod='autotranslator' sprintf=['https://cloud.ibm.com'] tags=['<span class="b">']}</li>
		<li>{l s='Go to [1]%s[/1]' mod='autotranslator' sprintf=['https://cloud.ibm.com/catalog/services/language-translator'] tags=['<span class="b">']}</li>
		<li>{l s='Select pricing plan (free plan available) and click [1]Create[/1] in right column' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>{l s='Go to [1]%s[/1]' mod='autotranslator' sprintf=['https://cloud.ibm.com/resources'] tags=['<span class="b">']}</li>
		<li>{l s='Find [1]Services > Language Translator[/1] and click it' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>{l s='Select [1]Manage[/1] tab in left column and there you will find [1]API Key[/1] and [1]URL[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
	</ol>
{else if $provider_name == 'MicrosoftTranslate'}
	<ol>
		<li>
			{l s='Log in to [1]%s[/1]' mod='autotranslator' sprintf=['https://ms.portal.azure.com/'] tags=['<span class="b">']}
			<span class="i">({l s='You might be asked to add card details during registration' mod='autotranslator'})</span>
		</li>
		<li>
			{l s='If you don\'t have a [1]Resource group[/1], you should create one' mod='autotranslator' tags=['<span class="b">']}.
			<ul>
				<li>{l s='For that you should click on [1]Resource groups[/1], and then [1]+ Add[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
				<li>{l s='Fill required fields, then click [1]Review + create[/1] and then [1]Create[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
				<li>{l s='Then return to main page to continue setting up the provider' mod='autotranslator'}</li>
			</ul>
		</li>
		<li>{l s='On main page Click [1]+ Create a resource[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>{l s='Find [1]Translator[/1], click on it and then click [1]Create[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>
			{l s='Fill required fields and select a [1]Pricing tier[/1]' mod='autotranslator' tags=['<span class="b">']}.
			<span class="i">({l s='Free tier should be available' mod='autotranslator'})</span>
			<ul>
				<li>{l s='When it comes to [1]Resource region[/1], you should set it to [1]Global[/1], no matter what region was selected for [1]Resource group[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
			</ul>
		</li>
		<li>{l s='Click [1]Review + create[/1] and then [1]Create[/1] to complete the process' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>{l s='Click [1]Go to resource[/1] and then navigate to [1]Keys and Endpoint[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>{l s='Copy [1]Key1[/1] which is actually the [1]API Key[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
	</ol>
{else if $provider_name == 'YandexTranslate'}
	<ol>
		<li>
			{l s='Log in to [1]%s[/1]' mod='autotranslator' sprintf=['https://translate.yandex.com/developers/account'] tags=['<span class="b">']}
			<ul>
				<li>{l s='Fill [1]Account information[/1] and add a [1]Payment method[/1] if it is not added yet' mod='autotranslator' tags=['<span class="b">']}</li>
			</ul>
		</li>
		<li>{l s='Click [1]Add funds[/1] in top right corner and upload funds to your account (min 15$)' mod='autotranslator' tags=['<span class="b">']}</li>
		<li>{l s='Click [1]API keys[/1] in left panel and then [1]+ Create a new key[/1]' mod='autotranslator' tags=['<span class="b">']}</li>
	</ol>
{/if}
{* since 3.0.3 *}
