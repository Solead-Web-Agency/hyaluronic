<style type="text/css">
@-webkit-keyframes rotating /* Safari and Chrome */ {
  from {
    -webkit-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  to {
    -webkit-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes rotating {
  from {
    -ms-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -webkit-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  to {
    -ms-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -webkit-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
.rotating {
  -webkit-animation: rotating 2s linear infinite;
  -moz-animation: rotating 2s linear infinite;
  -ms-animation: rotating 2s linear infinite;
  -o-animation: rotating 2s linear infinite;
  animation: rotating 2s linear infinite;
}
</style>

<div class="row">

	<div class="col-lg-3">

		<div class="panel" id="fieldset_0" style="text-align:center; background-color: #f8fbf9;">

			<a href="{$module_config}" target="_blank">
				<img src="{$path|escape:'htmlall':'UTF-8'}logo-spin.png" class="rotating" style="max-width: 64px; padding: 0; margin: 0 auto">
				<h4>{l s='OpenAI Module Settings' mod='chatgptpro'}</h4>
			</a>

		</div>

	</div>

	<div class="col-lg-3">

		<div class="panel" id="fieldset_0" style="text-align:center; background-color: #f8fbf9;">

			<a href="{$AdminChatGPTPRO_link}" target="_blank">
				<img src="{$path|escape:'htmlall':'UTF-8'}logo-spin.png" class="rotating" style="max-width: 64px; padding: 0; margin: 0 auto">
				<h4>{l s='Mass content Generator' mod='chatgptpro'}</h4>
			</a>

		</div>

	</div>

	<div class="col-lg-3">

		<div class="panel" id="fieldset_0" style="text-align:center; background-color: #f8fbf9;">

			<a href="{$AdminChatGPTPROCron_link}" target="_blank">
				<img src="{$path|escape:'htmlall':'UTF-8'}logo-spin.png" class="rotating" style="max-width: 64px; padding: 0; margin: 0 auto">
				<h4>{l s='Cron Job Settings' mod='chatgptpro'}</h4>
			</a>

		</div>

	</div>

	<div class="col-lg-3">

		<div class="panel" id="fieldset_0" style="text-align:center; background-color: #f8fbf9;">

			<a href="{$AdminChatGPTPROUsage_link}" target="_blank">
				<img src="{$path|escape:'htmlall':'UTF-8'}logo-spin.png" class="rotating" style="max-width: 64px; padding: 0; margin: 0 auto">
				<h4>{l s='View OpenAI Account Usage' mod='chatgptpro'}</h4>
			</a>

		</div>

	</div>

</div>