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



.the-playground-main {

    min-height: 50px;

    display: block;

    overflow-y: scroll;

    max-height: 300px;

    padding: 10px;

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



.the-playground-main p.the-answer {

    margin: 0;

    margin-bottom: 10px;

    background: #10a37f;

    border-radius: 0px 30px 30px 30px;

    max-width: 70%;

    padding: 16px;

    clear: both;

    position: relative;

}



.the-playground-main p.the-quest {

	background: #BDC3C7;

    border-radius: 30px 30px 0px 30px;

    margin: 0;

    padding: 16px;

    margin-bottom: 10px;

    margin-top: 10px;

    max-width: 70%;

    float: right;

    clear: both;

}



.the-playground-main p.the-answer.much-danger {

    background: #E74C3C;

}









/*spinner*/

.spinner {

    padding-top: 2px;

    text-align: center;

    transform: scale(0.6);

    display: none;

}



.spinner > div {

  width: 18px;

  height: 18px;

  background-color: #fff;



  border-radius: 100%;

  display: inline-block;

  -webkit-animation: sk-bouncedelay 1.4s infinite ease-in-out both;

  animation: sk-bouncedelay 1.4s infinite ease-in-out both;

}



.spinner .bounce1 {

  -webkit-animation-delay: -0.32s;

  animation-delay: -0.32s;

}



.spinner .bounce2 {

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



<div class="panel" id="fieldset_playground" style="background-color: #f8fbf9;">

	<div class="panel-heading"><i class="icon-info-circle"></i> {l s='Playground' mod='chatgptpro'}</div>



	<div class="row the-playground-main">



	</div>

	

	<div class="form-wrapper">

		<div class="form-group hide">

			<input type="hidden" name="sender" id="sender" value="">

		</div>

		<div class="form-group">

			<div class="col-lg-12">

				<textarea id="ChatGPTMessagePlayground" class="textarea-autosize" style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 51.6042px;white-space: pre-line;"></textarea>

			</div>

		</div>

	</div>



	<div class="panel-footer">

		<div class="row">

			<div class="col-lg-6">

				<p class="">{l s='Enter the prompt and hit the Submit button.' mod='chatgptpro'}</p>

				<p class="">{l s='Example:' mod='chatgptpro'}</p>

				<small class="">{l s='Write a creative product description for the following product:' mod='chatgptpro'}</small><br>

				<small class="">{l s='Product: Fitbit Sense 2' mod='chatgptpro'}</small>

			</div>

			<div class="col-lg-6">

				<button type="button" id="submitPromptPlayground" class="btn btn-default pull-right submit-prompt"><div class="spinner"> <div class="bounce1"></div> <div class="bounce2"></div> <div class="bounce3"></div> </div>  <span class="submit_text">{l s='Submit' mod='chatgptpro'}</span> </button>

			</div>

		</div>

		

	</div>

</div>







<script type="text/javascript">

	



$("#submitPromptPlayground").click(function(){

	

	var chat_message = $("#ChatGPTMessagePlayground").val();

	$("#ChatGPTMessagePlayground").val("");



	var len = chat_message.length;

	if (len > 0) {

		$('#ChatGPTMessagePlayground').css({

	        'border': '1px solid #bbcdd2',

	    });

		$("#submitPromptPlayground .submit_text").hide();

		$("#submitPromptPlayground .spinner").show();

		

	  	$(".the-playground-main").append('<p class="the-quest text-right"><span>'+chat_message+'</span></p>');

	  	$(".the-playground-main").animate({ scrollTop: $('.the-playground-main').prop("scrollHeight")}, 1000);



	  	$.ajax({

			method: "GET",

			url: "{$module_controller|escape:'htmlall':'UTF-8'}",

			data: {

				prompt: encodeURIComponent(chat_message),

				action: 'initiatePrompt'

			}

		}).done(function( msg ) {

			var data = jQuery.parseJSON( msg );

			

			if (typeof data.date !== 'undefined' && data.status == 'success') {

				$(".the-playground-main").append('<p class="the-answer text-left"><span class="the-response">'+data.date.response+'</span><span class="the-tokens">{l s='Total tokens used' mod='chatgptpro'}: '+data.date.total_tokens+'</span></p>');

	  			$(".the-playground-main").animate({ scrollTop: $('.the-playground-main').prop("scrollHeight")}, 1000);

	        } else if (typeof data.status !== 'undefined' && data.status == 'error') {

	        	$(".the-playground-main").append('<p class="the-answer text-left much-danger"><span>'+data.msg+'</span></p>');

	  			$(".the-playground-main").animate({ scrollTop: $('.the-playground-main').prop("scrollHeight")}, 1000);

	        }



	        $("#submitPromptPlayground .submit_text").show();

			$("#submitPromptPlayground .spinner").hide();

	    });



	} else {

		$('#ChatGPTMessagePlayground').css({

	        'border': '2px solid #E74C3C',

	    });

	 	$("#ChatGPTMessagePlayground").focus();

	}



	



});



</script>