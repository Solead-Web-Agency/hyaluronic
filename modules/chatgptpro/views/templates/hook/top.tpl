{*

* 2007-2023 Weblir

*

*  @author    weblir <hello@weblir.com>

*  @copyright 2012-2023 weblir

*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)

*  International Registered Trademark & Property of weblir.com

*

*  You are allowed to modify this copy for your own use only. You must not redistribute it. License

*  is permitted for one Prestashop instance only but you can install it on your test instances.

*}



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
#modelsModal ul.list-group li {
    width: 33%;
    display: inline-block;
    margin-bottom: 3px;
    padding: 3px;
    transition: 0.2s all;
}

#modelsModal ul.list-group li:hover {
    background: #f1f1f1;
}

#modelsModal ul.list-group {
	padding: 5px;
}

#modelsModal ul.list-group li button {
    padding: 0px 3px;
    font-size: 10px;
    text-transform: uppercase;
    margin-left: 7px;
}


</style>





<div class="alert alert-info">

	<p><i class="icon-star"></i> {$strings_top[0]|escape:'html':'UTF-8'} {$strings_top[1]|escape:'html':'UTF-8'} <strong><a href="https://addons.prestashop.com/en/2_community-developer?contributor=371964" target="_blank">{$strings_top[2]|escape:'html':'UTF-8'}</a></strong> {$strings_top[3]|escape:'html':'UTF-8'}</p>

</div>

<div class="alert alert-info version-status">{$strings_top[4]|escape:'html':'UTF-8'}</div>

<div class="panel" id="fieldset_0" style="text-align:center; background-color: #f8fbf9;">

	<div class="row">

		<div class="col-lg-12">

			<img src="{$path|escape:'htmlall':'UTF-8'}logo-spin.png" class="rotating" style="max-width: 200px; padding: 0; margin: 0 10px">

			<h1 style="color: #10a37f;margin: 10px 0;font-size: 50px;">

				{l s='OpenAI ChatGP Integratio PRO' mod='chatgptpro'}

			</h1>

			<h2>{l s='AI Content generation for products, categories, CMS pages and any other type of content' mod='chatgptpro'}</h2>

		</div>

	</div>

</div>


<div class="row">

	<div class="col-lg-3">

		<div class="panel" id="fieldset_00" style="text-align:center; background-color: #f8fbf9;">

			<a href="{$AdminChatGPTPRO_link}" target="_blank">
				<img src="">
				<h4>{l s='Mass content Generator' mod='chatgptpro'}</h4>
			</a>

		</div>

	</div>

	<div class="col-lg-3">

		<div class="panel" id="fieldset_10" style="text-align:center; background-color: #f8fbf9;">

			<a href="{$AdminChatGPTPROCron_link}" target="_blank">
				<img src="">
				<h4>{l s='Cron Job Settings' mod='chatgptpro'}</h4>
			</a>

		</div>

	</div>

	<div class="col-lg-3">

		<div class="panel" id="fieldset_20" style="text-align:center; background-color: #f8fbf9;">

			<a href="{$AdminChatGPTPROUsage_link}" target="_blank">
				<img src="">
				<h4>{l s='View OpenAI Account Usage' mod='chatgptpro'}</h4>
			</a>

		</div>

	</div>

	<div class="col-lg-3">

		<div class="panel" id="fieldset_20" style="text-align:center; background-color: #f8fbf9;">

			<a href="{$AdminChatGPTPROChangelog_link}" target="_blank">
				<img src="">
				<h4>{l s='Module changelog' mod='chatgptpro'}</h4>
			</a>

		</div>

	</div>

</div>


<div class="modal fade bd-models-modal-lg" id="modelsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered modal-lg">

    <div class="modal-content">

    	<ul class="list-group">

    		<li class="list-group-item" style="background: #55efc4;" title="" data-toggle="tooltip" data-placement="bottom" data-original-title="{l s='Use this only if you have access to OpenAi GPT-4 API!' mod='chatgptpro'}">

			    	<small>GPT-4

			    		<button type="button" class="btn btn-info btn-info pull-right" onclick="changeModel('gpt-4')"> {l s='Use' mod='chatgptpro'} </button>

			    		<span class="pull-right"><i class="icon icon-check"></i> {l s='Recommended' mod='chatgptpro'} </span>

			   	</li>

			   	{foreach from=$chatgpt_models item=model}

			    <li class="list-group-item" 

			    	{if $model.id == "text-davinci-003"} style="background: #55efc4;" title="{l s='It is the most advanced and capable language model in the GPT-3 family. It is capable of completing any task that the other models can perform, often with higher quality, longer output, and better ability to follow instructions. Additionally, it has the ability to insert completions within the text' mod='chatgptpro'}" data-toggle="tooltip" data-placement="top" title="Tooltip on top"{/if}

			    	{if $model.id == "text-curie-001"} style="background: #55efc4;" title="{l s='Very capable, but faster and lower cost than Davinci.' mod='chatgptpro'}" data-toggle="tooltip" data-placement="top" title="Tooltip on top"{/if}

			    	{if $model.id == "gpt-3.5-turbo"} style="background: #55efc4;" title="{l s='gpt-3.5-turbo is 10x cheaper than the existing GPT-3.5 models. It’s also best model for many non-chat use cases.' mod='chatgptpro'}" data-toggle="tooltip" data-placement="top" title="Tooltip on top"{/if}

			    	{if $model.id == "text-babbage-001"} style="background: #55efc4;" title="{l s='It can complete straightforward tasks efficiently and at a lower cost compared to other models.' mod='chatgptpro'}" data-toggle="tooltip" data-placement="top" title="Tooltip on top"{/if}

			    	{if $model.id == "text-ada-001"} style="background: #55efc4;" title="{l s='It is capable of performing simple tasks quickly and at a low cost. It is typically the fastest model in the GPT-3 family.' mod='chatgptpro'}" data-toggle="tooltip" data-placement="top" title="Tooltip on top"{/if}

			    >

			    	<small>

			    		{$model.id}   

			    		<button type="button" class="btn btn-info btn-info pull-right" onclick="changeModel('{$model.id}')"> {l s='Use' mod='chatgptpro'} </button>

			    		{if $model.id == "text-davinci-003" || $model.id == "text-curie-001" || $model.id == "text-babbage-001" || $model.id == "text-ada-001" || $model.id == "gpt-3.5-turbo"}<span class="pull-right"><i class="icon icon-check"></i> {l s='Recommended' mod='chatgptpro'} </span>{/if}

			    	</small>

			   	</li>

			{/foreach}

		</ul>

    </div>

  </div>

</div>





<script>



function showAllModels() {

	$('#modelsModal').modal('show');

}



function changeModel(model) {

	$('#WEBLIR_CHATGPTPRO_MODEL').val(model);

	$('#modelsModal').modal('toggle');

}



setTimeout(

	function version_status()

	{

	  $('[data-toggle="tooltip"]').tooltip();

	  var api_check = "https://www.weblir.com/version/latest.php?shop={$shop|escape:'html':'UTF-8'}&ref={$ref|escape:'html':'UTF-8'}&module={$modulename|escape:'html':'UTF-8'}&version={$moduleversion|escape:'html':'UTF-8'}";

	  $.getJSON(api_check)

	    .done(function( data ) {

	    	if (typeof data.version_status === 'undefined') {

	    		//

	    	} else {

	    		$( ".version-status").html(data.version_status).show();

	    	}

	    	

	    }).error(function() {});

	},

1000);

</script>