{*

*  2007-2023 Weblir

*

*  @author    weblir <contact@weblir.com>

*  @copyright 2012-2023 weblir

*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)

*  International Registered Trademark & Property of weblir.com

*

*  You are allowed to modify this copy for your own use only. You must not redistribute it. License

*  is permitted for one Prestashop instance only but you can install it on your test instances.

*}



{addJsDef category_list=$listed_categories}


<div style="display: none;">
	<div id="prompt-list">
		<p>{l s='Prompt Templates:' mod='chatgptpro'}</p>
		{foreach from=$prompts item=prompt}
    	<button class="insert-prompt" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaretPrompt(`{$prompt.prompt nofilter}`)" >{$prompt.title}</button> 
    {/foreach}
	</div>
</div>

<div class="row" id="category-mass-generate-container">

	<div class="col-sm-8">

		<div class="panel panel-default" id="panel-pending">

		    <div class="panel-heading">{l s='Categories to update:' mod='chatgptpro'} {$listed_categories|count}</div>

		    <div class="panel-body">

		    	<table class="table table-condensed" id="pending-table">

					<thead>

					  <tr>

					    <th><input type="checkbox" checked="checked" id="target-selector"></th>

					    <th>{l s='ID' mod='chatgptpro'}</th>

					    <th>{l s='Category' mod='chatgptpro'}</th>

					    {foreach from=$lang_list item=single_lang}

					    	<th>{$single_lang.name}</th>

					    {/foreach}

					  </tr>

					</thead>

					<tbody>

					  	{foreach from=$listed_categories item=category}

						  	<tr id="category-{$category.id_category}" data-category="{$category.id_category}">

							    <td><input type="checkbox" class="targeted-category" value="{$category.id_category}" data-id_category="{$category.id_category}" name="targeted_categories" checked="checked"></td>

							    <td>{$category.id_category}</td>

							    <td>{$category.lang_data.{$current_lang}.name}</td>


							    

						    	{foreach from=$category.lang_data item=category_data}

						    		<td>

						    			<span class="label {if $category_data.name_size < 1}label-danger{else}label-success{/if}" title="{l s='Category name size' mod='chatgptpro'}">{$category_data.name_size}</span>

						    			<span class="label {if $category_data.description_size < 1}label-danger{else}label-success{/if}" title="{l s='Description size' mod='chatgptpro'}">{$category_data.description_size}</span>

						    			<span class="label {if $category_data.meta_title_size < 1}label-danger{else}label-success{/if}" title="{l s='Meta Title size' mod='chatgptpro'}">{$category_data.meta_title_size}</span>

						    			<span class="label {if $category_data.meta_description_size < 1}label-danger{else}label-success{/if}" title="{l s='Meta Description size' mod='chatgptpro'}">{$category_data.meta_description_size}</span>

						    		</td>

						    	{/foreach}

							    

						  	</tr>

						{/foreach}

					</tbody>

				</table>

		    </div>

		</div>

	</div>



	<div class="col-sm-4">

		<div class="panel panel-default" id="panel-finished">

		    <div class="panel-heading">{l s='Updated categories' mod='chatgptpro'} <span id="processed-categories"></span></div>

		    <div class="panel-body">

		    	<table class="table table-condensed" id="finished-table">

					<thead>

					  <tr>

					    <th>{l s='Category' mod='chatgptpro'}</th>

					    <th>{l s='Content' mod='chatgptpro'}</th>

					  </tr>

					</thead>

					<tbody>



					</tbody>

				</table>

		    </div>

		</div>

	</div>

</div>



<style type="text/css">

#category-mass-generate-container .panel .panel-body {

    padding: 0;

    max-height: 200px;

    overflow-y: scroll;

}

div#prompt-list, .caret-container {
    padding: 8px;
    background: #f8f8f8;
    border-radius: 10px;
    margin-top: 5px;
}

textarea#WEBLIR_CHATGPTPRO_CONTENT {
    min-height: 110px !important;
    height: auto;
    resize: auto !important;
    max-height: none;
}

#category-mass-generate-container .panel .panel-body ul.list-group {

	margin: 0;

}



table#finished-table td i {

    font-size: 17px;

}



.the-loader {

    position: absolute;

    display: none;

    width: 100%;

    height: 100%;

    top: 0;

    left: 0;

    background: rgba(0,0,0,0.3);

    text-align: center;

    vertical-align: middle;

}



textarea#WEBLIR_CHATGPTPRO_CONTENT.has-error {

    border: 1px solid red;

}

button.insert-prompt {

    border-radius: 30px;

    border: 1px solid #fefefe;

    font-size: 11px;

    margin-bottom: 3px;

    clear: both;

    display: block;

}

button.insert-shortcode {

    border-radius: 30px;

    border: 1px solid #fefefe;

    font-size: 11px;

    margin-bottom: 3px;

    clear: both;

    display: block;

}



.lds-ellipsis {

    display: inline-block;

    width: 80px;

    height: 80px;

    position: absolute;

    right: 0;

    left: 0;

    top: 0;

    bottom: 0;

    margin: auto;

}



.lds-ellipsis div {

  position: absolute;

  top: 33px;

  width: 13px;

  height: 13px;

  border-radius: 50%;

  background: #fff;

  animation-timing-function: cubic-bezier(0, 1, 1, 0);

}



.lds-ellipsis div:nth-child(1) {

  left: 8px;

  animation: lds-ellipsis1 0.6s infinite;

}



.lds-ellipsis div:nth-child(2) {

  left: 8px;

  animation: lds-ellipsis2 0.6s infinite;

}



.lds-ellipsis div:nth-child(3) {

  left: 32px;

  animation: lds-ellipsis2 0.6s infinite;

}



.lds-ellipsis div:nth-child(4) {

  left: 56px;

  animation: lds-ellipsis3 0.6s infinite;

}



@keyframes lds-ellipsis1 {

  0% {

    transform: scale(0);

  }

  100% {

    transform: scale(1);

  }

}

@keyframes lds-ellipsis3 {

  0% {

    transform: scale(1);

  }

  100% {

    transform: scale(0);

  }

}

@keyframes lds-ellipsis2 {

  0% {

    transform: translate(0, 0);

  }

  100% {

    transform: translate(24px, 0);

  }

}





</style>



<div style="display: none;">

	<p class="caret-container help-block">

		You can also use the following shortcodes:

		<button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaret('{literal}{category_name}{/literal}')" >{literal}{category_name}{/literal}</button>
		<button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaret('{literal}{category_description}{/literal}')" >{literal}{category_description}{/literal}</button> 
		<button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaret('{literal}{category_parent_name}{/literal}')" >{literal}{category_parent_name}{/literal}</button> 
		<button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaret('{literal}{shop_name}{/literal}')" >{literal}{shop_name}{/literal}</button> 
		<button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaret('{literal}{shop_url}{/literal}')" >{literal}{shop_url}{/literal}</button> 
		<button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaret('{literal}{shop_language}{/literal}')" >{literal}{shop_language}{/literal}</button> 
		<button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaret('{literal}{shop_country}{/literal}')" >{literal}{shop_country}{/literal}</button> 
		<button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_CONTENT').insertAtCaret('{literal}{shop_currency}{/literal}')" >{literal}{shop_currency}{/literal}</button> 


	</p>

</div>



<script type="text/javascript">

  $.fn.extend({

  insertAtCaret: function(myValue){

  var obj;

  if( typeof this[0].name !='undefined' ) obj = this[0];

  else obj = this;



  if ($.browser.msie) {

    obj.focus();

    sel = document.selection.createRange();

    sel.text = myValue;

    obj.focus();

    }

  else if ($.browser.mozilla || $.browser.webkit) {

    var startPos = obj.selectionStart;

    var endPos = obj.selectionEnd;

    var scrollTop = obj.scrollTop;

    obj.value = obj.value.substring(0, startPos)+myValue+obj.value.substring(endPos,obj.value.length);

    obj.focus();

    obj.selectionStart = startPos + myValue.length;

    obj.selectionEnd = startPos + myValue.length;

    obj.scrollTop = scrollTop;

  } else {

    obj.value += myValue;

    obj.focus();

   }

 }

})



$.fn.extend({

  insertAtCaretPrompt: function(myValue){

  var obj;

  if( typeof this[0].name !='undefined' ) obj = this[0];

  else obj = this;



  if ($.browser.msie) {

    obj.focus();

    sel = document.selection.createRange();

    sel.text = myValue;

    obj.focus();

    }

  else if ($.browser.mozilla || $.browser.webkit) {

    var startPos = obj.selectionStart;

    var endPos = obj.selectionEnd;

    var scrollTop = obj.scrollTop;

    obj.value = obj.value.substring(0, startPos)+myValue+obj.value.substring(endPos,obj.value.length);

    obj.focus();

    obj.selectionStart = startPos + myValue.length;

    obj.selectionEnd = startPos + myValue.length;

    obj.scrollTop = scrollTop;

  } else {

    obj.value += myValue;

    obj.focus();

   }

 }

})





$( document ).ready(function() {

	$("form#configuration_form .panel").append('<div class="the-loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>');



	$("#prompt-list").insertAfter($("#WEBLIR_CHATGPTPRO_CONTENT"));
	$(".caret-container").insertAfter($("#prompt-list"));



	$("#target-selector").click(function(){

	    $('input.targeted-category:checkbox').not(this).prop('checked', this.checked);

	});



  $("#generateCategoryContent").click(function(event){

		event.preventDefault();

		if ($("#WEBLIR_CHATGPTPRO_CONTENT").val().length>0) {

			$("#WEBLIR_CHATGPTPRO_CONTENT").removeClass("has-error");

			$(".the-loader ").show();



			//check the list and remove the unselected categories

			$('#pending-table > tbody > tr').each(function(){

				if ($(this).find('input.targeted-category').is(':checked')) {

					//console.log("found one!");

				} else {

					$(this).remove();

				}

			});



			initiateContentGeneration();

		} else {

			$("#WEBLIR_CHATGPTPRO_CONTENT").addClass("has-error");

		}

			

	});

});



function initiateContentGeneration() {

	var id_category = $('#pending-table > tbody > tr:first').attr("data-category");

	var chat_message = $("#WEBLIR_CHATGPTPRO_CONTENT").val();

	var target = $('input[name="WEBLIR_CHATGPTPRO_FIELD"]:checked').val();

	var id_lang = $('input[name="WEBLIR_CHATGPTPRO_LANGUAGE"]:checked').val();

	var behavior = $('input[name="WEBLIR_CHATGPTPRO_EXISTING"]:checked').val();

	var api_delay = $('input[name="WEBLIR_CHATGPTPRO_DELAY"]').val();


	$.ajax({

		method: "GET",

		url: "{$module_controller|escape:'htmlall':'UTF-8'}",

		data: {

			prompt: encodeURIComponent(chat_message),

			action: 'updateCategory',

			id_category: id_category,

			id_lang: id_lang,

			target: target,

			behavior: behavior,

		}

	}).done(function( msg ) {

		var data = jQuery.parseJSON( msg );

		

		if (typeof data.date !== 'undefined' && data.status == 'success') {

			$("#pending-table #category-"+id_category).remove();

			$('#finished-table tr:last').after('<tr class="success"><td>'+data.date.category_name+'</td><td><i class="icon icon-info-circle" title="'+data.date.generated_content.replace(/"|'/g,'')+'" data-toggle="tooltip" data-placement="left" title="Tooltip on top"></i></td></tr>');

			$("#processed-categories").html(": "+($('#finished-table tr').length - 1));

			$("#panel-finished .panel-body").animate({ scrollTop: $('#panel-finished .panel-body').prop("scrollHeight")}, 1000);

			$('[data-toggle="tooltip"]').tooltip();

    } else if (typeof data.status !== 'undefined' && data.status == 'error') {

      $("#pending-table #category-"+id_category).remove();

			$('#finished-table tr:last').after('<tr class="danger"><td>'+data.date.category_name+' : '+data.msg+'</td><td></td></tr>');

			$("#processed-categories").html(": "+($('#finished-table tr').length - 1));

			//$(".the-loader ").hide();

    }



    if ($('#pending-table > tbody tr').length > 0) {

    	if (parseInt(api_delay)>0) {
    			setTimeout(
				  function() 
				  {
				    	initiateContentGeneration();
				  }, parseInt(api_delay)*1000);
    	} else {
    			initiateContentGeneration();
    	}

    } else {

    	$(".the-loader ").hide();

    }



  }).fail(function(xhr, status, error) {
      $.growl.error({ message: "{l s='There was an error while generating content!' mod='chatgptpro'}" });
      console.log(xhr);
      console.log(status);
      console.log(error);
  });

}



</script>

