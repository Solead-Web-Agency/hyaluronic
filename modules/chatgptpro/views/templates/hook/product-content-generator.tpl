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

div#fieldset_playground {

    margin-bottom: 15px;

}



p.the-quest span.the-response {

    max-width: 75%;

}



p.the-answer span.the-response {

    color: #fff;

    display: block;

}



p.the-answer span.the-tokens {

    color: #fff;

    position: absolute;

    right: 52px;

    bottom: -10px;

    z-index: 9;

    border-radius: 20px;

    background: #F39C12;

    padding: 0px 10px;

}



#ChatGPTProductMessage {

    width: 100%;

    background: #1e1f26;

    border: none;

    color: #d5d7de;

    padding: 3px 7px;

    border-radius: 3px;

    resize: auto !important;

}



textarea#ChatGPTProductMessage.has-error {

    border: 2px solid #E74C3C;

}



#ChatGPTProductMessage:focus,

#ChatGPTProductMessage:focus-visible,

#ChatGPTProductMessage:active {

    outline:0px none transparent;

}



button#submitProductPrompt {

    background: #444857;

    border: none;

    color: #fff;

    margin: 10px;

}

button.insert-prompt-product {

    border-radius: 30px;

    border: 1px solid #fefefe;

    font-size: 11px;

    margin-bottom: 3px;

    clear: both;

    display: block;

}



#fieldset_playground .content {

    box-shadow: 0 4px 50px rgba(10,10,12,0.6);

    margin: 10px;

    padding: 5px 7px;

    font-size: 10px;

    overflow: auto;

}



.autocomplete-prompt:hover {

    background: green;

}





/*spinnerx*/

.spinnerx {

    padding-top: 2px;

    text-align: center;

    transform: scale(0.6);

    display: none;

}



.spinnerx > div {

  width: 18px;

  height: 18px;

  background-color: #fff;



  border-radius: 100%;

  display: inline-block;

  -webkit-animation: sk-bouncedelay 1.4s infinite ease-in-out both;

  animation: sk-bouncedelay 1.4s infinite ease-in-out both;

}



.spinnerx .bounce1 {

  -webkit-animation-delay: -0.32s;

  animation-delay: -0.32s;

}



.spinnerx .bounce2 {

  -webkit-animation-delay: -0.16s;

  animation-delay: -0.16s;

}



@-webkit-keyframes sk-bouncedelay {

  0%, 80%, 100% { -webkit-transform: scale(0) }

  40% { -webkit-transform: scale(1.0) }

}



@keyframes sk-bouncedelay {

  0%, 80%, 100% { 

    -webkit-transform: scale(0);

    transform: scale(0);

  } 40% { 

    -webkit-transform: scale(1.0);

    transform: scale(1.0);

  }

}



</style>


<div class="panel" id="fieldset_playground" style="background-color: #2c303a;padding: 10px;color: #fff;">

	<div class="panel-heading"><i class="icon-info-circle"></i> {l s='Generate product description content' mod='chatgptpro'}</div>



	<div class="form-wrapper">

		<div class="form-group hide">

			<input type="hidden" name="sender" id="sender" value="">

		</div>

		<div class="form-group">

			<div class="row">

				<div class="col-lg-10">

					<textarea id="ChatGPTProductMessage" class="textarea-autosize" style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 51.6042px;white-space: pre-line;"></textarea>

				</div>



				<div class="col-lg-2 text-right align-right">

					<a href="{$mod_controller}" title="{l s='Module settings' mod='chatgptpro'}"><i class="material-icons mi-settings">settings</i></a> <button type="button" id="submitProductPrompt" class="btn btn-default pull-right submit-prompt"><div class="spinnerx"> <div class="bounce1"></div> <div class="bounce2"></div> <div class="bounce3"></div> </div>  <span class="submit_text">{l s='Submit' mod='chatgptpro'}</span> </button>

				</div>

			</div>

		</div>

	</div>



	<div class="panel-footer">

		<div class="row">

			<div class="col-lg-4">

				<div class="content">

					<p>{l s='Set content for:' mod='chatgptpro'}</p>

					<div class="radio form-check form-check-radio">

					  <label class="form-check-label">

					    <input type="radio" id="fill_desc" name="fill_type" value="0" checked="checked">

					    <i class="form-check-round"></i> {l s='Description' mod='chatgptpro'} </label>

					</div>

					<div class="widget-radio-inline">

					  <div class="radio form-check form-check-radio">

					    <label class="form-check-label">

					      <input type="radio" id="fill_desc_short" name="fill_type" value="1" >

					      <i class="form-check-round"></i> {l s='Short Description' mod='chatgptpro'} </label>

					  </div>

					</div>

					<div class="radio form-check form-check-radio">

					  <label class="form-check-label">

					    <input type="radio" id="fill_name" name="fill_type" value="3">

					    <i class="form-check-round"></i> {l s='Product Name' mod='chatgptpro'} </label>

					</div>

					<div class="radio form-check form-check-radio">

					  <label class="form-check-label">

					    <input type="radio" id="fill_name" name="fill_type" value="6">

					    <i class="form-check-round"></i> {l s='Product Tags' mod='chatgptpro'} </label>

					</div>

					<div class="radio form-check form-check-radio">

					  <label class="form-check-label">

					    <input type="radio" id="fill_meta_title" name="fill_type" value="4">

					    <i class="form-check-round"></i> {l s='Meta title' mod='chatgptpro'} </label>

					</div>

					<div class="radio form-check form-check-radio">

					  <label class="form-check-label">

					    <input type="radio" id="fill_meta_description" name="fill_type" value="5">

					    <i class="form-check-round"></i> {l s='Meta description' mod='chatgptpro'} </label>

					</div>

				</div>

				  

			</div>







			<div class="col-lg-8">

				<div class="content">

					<p>{l s='Select a pre-defined option for fast autocomplete:' mod='chatgptpro'}</p>

					{foreach from=$prompts item=prompt}
				    	<button class="insert-prompt-product autocomplete-prompt" type="button" data-prompt="{$prompt.prompt nofilter}" >{$prompt.title}</button> 
				    {/foreach}

				</div>

				  

			</div>





			

		</div>

		

	</div>

</div>



<script type="text/javascript">




$(".autocomplete-prompt").click(function(){

	$("#ChatGPTProductMessage").val($(this).attr('data-prompt'));

});





$("#submitProductPrompt").click(function(){

	

	var chat_message = $("#ChatGPTProductMessage").val();

	$("#ChatGPTProductMessage").val("");



	var len = chat_message.length;

	if (len > 0) {

		$('#ChatGPTProductMessage').removeClass("has-error");

		$("#submitProductPrompt .submit_text").hide();

		$("#submitProductPrompt .spinnerx").show();

		var fill_arget = $('input[name="fill_type"]:checked').val();

		

	  	//$(".the-playground").append('<p class="the-quest text-right"><span>'+chat_message+'</span></p>');

	  	//$(".the-playground").animate({ scrollTop: $('.the-playground').prop("scrollHeight")}, 1000);



	  	$.ajax({

			method: "GET",

			url: "{$module_controller|escape:'htmlall':'UTF-8'}",

			data: {
				prompt: encodeURIComponent(chat_message),
				id_product: '{$idproduct}',
				id_lang: default_language,
				idlanguage: default_language,
				target: fill_arget,
				behavior: 'single_product',
				action: 'updateProduct',
			}

		}).done(function( msg ) {

			var data = jQuery.parseJSON( msg );

			var fill_type = $('input[name="fill_type"]:checked').val();

			var prod_desc = "form_step1_description_"+default_language;

			var prod_desc_short = "form_step1_description_short_"+default_language;

			var prod_name = "form_step1_name_"+default_language;

			var meta_title = "form_step5_meta_title_"+default_language;

			var meta_description = "form_step5_meta_description_"+default_language;

			var prod_tags = "form_step6_tags_"+default_language;



			if ($("#form_switch_language").length) {

				var prod_desc = "form_step1_description_"+lang_summary[$("#form_switch_language").val()];

				var prod_desc_short = "form_step1_description_short_"+lang_summary[$("#form_switch_language").val()];

				var prod_name = "form_step1_name_"+lang_summary[$("#form_switch_language").val()];

			}

			

			if (typeof data.date !== 'undefined' && data.status == 'success') {

				if (fill_type == "0") {

					tinymce.get(prod_desc).setContent(data.date.response); 

					$("#"+prod_desc).val(data.date.response);

					$.growl({ title: "{l s='Status' mod='chatgptpro'}", message: "{l s='Content successfully generated' mod='chatgptpro'}" });

				} else if (fill_type == "1") {

				  	tinymce.get(prod_desc_short).setContent(data.date.response); 

				  	$("#"+prod_desc_short).val(data.date.response);

				  	$.growl({ title: "{l s='Status' mod='chatgptpro'}", message: "{l s='Content successfully generated' mod='chatgptpro'}" });

				} else if (fill_type == "2") {

				  	tinymce.get(prod_desc).setContent(data.date.response); 

				  	tinymce.get(prod_desc_short).setContent(data.date.response); 

				  	$("#"+prod_desc).val(data.date.response);

				  	$("#"+prod_desc_short).val(data.date.response);

				  	$.growl({ title: "{l s='Status' mod='chatgptpro'}", message: "{l s='Content successfully generated' mod='chatgptpro'}" });

				} else if (fill_type == "3") {

				  	$("#"+prod_name).val(data.date.response);

				  	$.growl({ title: "{l s='Status' mod='chatgptpro'}", message: "{l s='Content successfully generated' mod='chatgptpro'}" });

				} else if (fill_type == "4") {

				  	$("#"+meta_title).val(data.date.response);

				  	$.growl({ title: "{l s='Status' mod='chatgptpro'}", message: "{l s='Content successfully generated' mod='chatgptpro'}" });

				} else if (fill_type == "5") {

				  	$("#"+meta_description).val(data.date.response);

				  	$.growl({ title: "{l s='Status' mod='chatgptpro'}", message: "{l s='Content successfully generated' mod='chatgptpro'}" });

				} else if (fill_type == "6") {

				  	$("#"+prod_tags).tokenfield('setTokens', data.date.response);

				  	$.growl({ title: "{l s='Status' mod='chatgptpro'}", message: "{l s='Content successfully generated' mod='chatgptpro'}" });

				}



	        } else if (typeof data.status !== 'undefined' && data.status == 'error') {

	        	$.growl.error({ message: "{l s='There was an error while generating content!' mod='chatgptpro'}" });
	        	$.growl.error({ message: data.msg });

	        	//$(".the-playground").append('<p class="the-answer text-left much-danger"><span>'+data.msg+'</span></p>');

	  			//$(".the-playground").animate({ scrollTop: $('.the-playground').prop("scrollHeight")}, 1000);

	        }



	        $("#submitProductPrompt .submit_text").show();

			$("#submitProductPrompt .spinnerx").hide();

	    });



	} else {

		$('#ChatGPTProductMessage').addClass("has-error");

	 	$("#ChatGPTProductMessage").focus();

	}



});



</script>