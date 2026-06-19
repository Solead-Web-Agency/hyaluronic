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


<div id="cron-info">
	<br>
	<p>{l s='Product Cron Job parameter info:' mod='chatgptpro'}</p>
	<ul class="list-group list-group-flush">
		<li class="list-group-item"><span class="badge badge-success">secret</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Used for security' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">productTarget</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Defines which product will be updated: pendingOpenAI - products that haven\'t been updated with this module, emptyField - products with the empty field' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">updatedField</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Defines which product field will be updated: description, description_short, meta_title, meta_description' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">productsToUpdate</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Set the number of products you wish to update. You can lower the number if there\'s any execution timeout.' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">languageId</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Set the language ID for the content generation' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">promptTemplateId</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Set the Prompt Template ID that you wish to be used on CRON Job Execution' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">categories</span> - {l s='optional' mod='chatgptpro'} | {l s='Set the category ID list separated by comma.' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-secondary">debug</span> - {l s='optional' mod='chatgptpro'} | {l s='Allows you to view aditional data for debugging withoud updating the products' mod='chatgptpro'}</li>
	</ul>
</div>

<div id="cat-cron-info">
	<br>
	<p>{l s='Category Cron Job parameter info:' mod='chatgptpro'}</p>
	<ul class="list-group list-group-flush">
		<li class="list-group-item"><span class="badge badge-success">secret</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Used for security' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">categoryTarget</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Defines which categories will be updated: pendingOpenAI - categories that haven\'t been updated with this module, emptyField - categories with the empty field' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">updatedField</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Defines which category field will be updated: name, description, meta_title, meta_description' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">categoriesToUpdate</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Set the number of categories you wish to update. You can lower the number if there\'s any execution timeout.' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">languageId</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Set the language ID for the content generation' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-success">promptTemplateId</span> - {l s='mandatory' mod='chatgptpro'} | {l s='Set the Prompt Template ID that you wish to be used on CRON Job Execution' mod='chatgptpro'}</li>
		<li class="list-group-item"><span class="badge badge-secondary">debug</span> - {l s='optional' mod='chatgptpro'} | {l s='Allows you to view aditional data for debugging withoud updating the categories' mod='chatgptpro'}</li>
	</ul>
</div>




<style type="text/css">
div#cron-info ul.list-group li.list-group-item span.badge {
    float: none;
}

div#cat-cron-info ul.list-group li.list-group-item span.badge {
    float: none;
}
</style>

<script>

function generateTokenAndFill(length){
    //edit the token allowed characters
    var a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890".split("");
    var b = [];  
    for (var i=0; i<length; i++) {
        var j = (Math.random() * (a.length-1)).toFixed(0);
        b[i] = a[j];
    }
    $("#WEBLIR_CHATGPTPRO_SECRET").val(b.join(""));
    $(".cron-secret").html(b.join(""));
}

function generateCategoryTokenAndFill(length){
    //edit the token allowed characters
    var a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890".split("");
    var b = [];  
    for (var i=0; i<length; i++) {
        var j = (Math.random() * (a.length-1)).toFixed(0);
        b[i] = a[j];
    }
    $("#WEBLIR_CHATGPTPRO_CAT_SECRET").val(b.join(""));
    $(".cat-cron-secret").html(b.join(""));
}

$( document ).ready(function() {
	var cron_parameters = '?action=generateProductContent&secret=<span class="cron-secret">{$formvalues.WEBLIR_CHATGPTPRO_SECRET}</span>&updatedField=<span class="cron-updatedField">{$formvalues.updatedField}</span>&productTarget=<span class="cron-productTarget">{$formvalues.productTarget}</span>&productsToUpdate=<span class="cron-productsToUpdate">{$formvalues.productsToUpdate}</span>&languageId=<span class="cron-languageId">{$formvalues.languageId}</span>&promptTemplateId=<span class="cron-promptTemplateId">{$formvalues.promptTemplateId}</span><span class="categories-block">&categories=<span class="cron-categories">{$formvalues.categories}</span></span>&debug=<span class="cron-debug">{$formvalues.debug}</span>';

	$(".cron-parameters").html(cron_parameters);
	

    $("#cron-info").insertAfter($(".cron-command"));
    

    $('#languageId').change(function(){
		$("span.cron-languageId").html($(this).val());
	});

	$('#productsToUpdate').change(function(){
		$("span.cron-productsToUpdate").html($(this).val());
	});

	$('input[type=radio][name=updatedField]').change(function(){
		$("span.cron-updatedField").html($(this).val());
	});

	$('input[type=radio][name=productTarget]').change(function(){
		$("span.cron-productTarget").html($(this).val());
	});

	$('#promptTemplateId').change(function(){
		$("span.cron-promptTemplateId").html($(this).val());
	});

	$('input[type=radio][name=debug]').change(function(){
		$("span.cron-debug").html($(this).val());
	});

	$("#id_category input[type=checkbox]").change(function() {

		var array = [];

		$("#id_category input[type=checkbox]:checked").each(function(){
		    array.push($(this).val())
		});

		if (array.length > 0) {
			$(".cron-categories").html(array.join(','));
			$("#categories-block").show();
		} else {
			$(".cron-categories").html('');
			$("#categories-block").hide();
		}

	});





	var cat_cron_parameters = '?action=generateCategoryContent&secret=<span class="cat-cron-secret">{$catformvalues.WEBLIR_CHATGPTPRO_CAT_SECRET}</span>&updatedField=<span class="cron-catupdatedField">{$catformvalues.catupdatedField}</span>&categoryTarget=<span class="cron-categoryTarget">{$catformvalues.categoryTarget}</span>&categoriesToUpdate=<span class="cron-categoriesToUpdate">{$catformvalues.categoriesToUpdate}</span>&languageId=<span class="cron-catlanguageId">{$catformvalues.catlanguageId}</span>&promptTemplateId=<span class="cron-catpromptTemplateId">{$catformvalues.catpromptTemplateId}</span></span>&debug=<span class="cron-catdebug">{$catformvalues.catdebug}</span>';

	$(".cat-cron-parameters").html(cat_cron_parameters);
	$("#cat-cron-info").insertAfter($(".cat-cron-command"));

	$('#catlanguageId').change(function(){
		$("span.cron-catlanguageId").html($(this).val());
	});

	$('#categoriesToUpdate').change(function(){
		$("span.cron-categoriesToUpdate").html($(this).val());
	});

	$('input[type=radio][name=catupdatedField]').change(function(){
		$("span.cron-catupdatedField").html($(this).val());
	});

	$('input[type=radio][name=categoryTarget]').change(function(){
		$("span.cron-categoryTarget").html($(this).val());
	});

	$('#catpromptTemplateId').change(function(){
		$("span.cron-catpromptTemplateId").html($(this).val());
	});

	$('input[type=radio][name=catdebug]').change(function(){
		$("span.cron-catdebug").html($(this).val());
	});

});











var explode = function(){

	function setCron() {

		var cronos = $('.interval_select').val();



		if (cronos==1) {

			cron = '* * * * * wget'

		} else if (cronos==2) {

			cron = '*/2 * * * * wget'

		} else if (cronos==3) {

			cron = '*/5 * * * * wget'

		} else if (cronos==4) {

			cron = '*/10 * * * * wget'

		} else if (cronos==5) {

			cron = '*/30 * * * * wget'

		} else if (cronos==6) {

			cron = '0 * * * * wget'

		} else if (cronos==7) {

			cron = '0 */2 * * * wget'

		} else if (cronos==8) {

			cron = '0 */6 * * * wget'

		}

		$('.cron-target').html(cron);

	}

	setCron();

	$('#WEBLIR_CHATGPTPRO_CRON_INTERVAL').change(function(){
		setCron();
	});

	function enableCron() {
		if ($('input[type=radio][name=WEBLIR_CHATGPTPRO_ENABLE_CRON]').val() == 1) {
	        $('#WEBLIR_CHATGPTPRO_CRON_INTERVAL').parent().parent().show();
	        $('.cron-command').parent().parent().parent().show();
	    }
	    else if ($('input[type=radio][name=WEBLIR_CHATGPTPRO_ENABLE_CRON]').val() == 0) {
	        $('#WEBLIR_CHATGPTPRO_CRON_INTERVAL').parent().parent().hide();
	        $('.cron-command').parent().parent().parent().hide();
	    }
	}

	enableCron();

	$('input[type=radio][name=WEBLIR_CHATGPTPRO_ENABLE_CRON]').change(function() {
	    if (this.value == 1) {
	        $('#WEBLIR_CHATGPTPRO_CRON_INTERVAL').parent().parent().show();
	        $('.cron-command').parent().parent().parent().show();
	    }
	    else if (this.value == 0) {
	        $('#WEBLIR_CHATGPTPRO_CRON_INTERVAL').parent().parent().hide();
	        $('.cron-command').parent().parent().parent().hide();
	    }
	});


};

setTimeout(explode, 1500);










var cat_explode = function(){

	function cat_setCron() {

		var cronos = $('.cat_interval_select').val();



		if (cronos==1) {

			cron = '* * * * * wget'

		} else if (cronos==2) {

			cron = '*/2 * * * * wget'

		} else if (cronos==3) {

			cron = '*/5 * * * * wget'

		} else if (cronos==4) {

			cron = '*/10 * * * * wget'

		} else if (cronos==5) {

			cron = '*/30 * * * * wget'

		} else if (cronos==6) {

			cron = '0 * * * * wget'

		} else if (cronos==7) {

			cron = '0 */2 * * * wget'

		} else if (cronos==8) {

			cron = '0 */6 * * * wget'

		}

		$('.cat-cron-target').html(cron);

	}

	cat_setCron();

	$('#WEBLIR_CHATGPTPRO_CAT_CRON_INTERVAL').change(function(){
		cat_setCron();
	});

	function cat_enableCron() {
		if ($('input[type=radio][name=WEBLIR_CHATGPTPRO_ENABLE_CRON]').val() == 1) {
	        $('#WEBLIR_CHATGPTPRO_CAT_CRON_INTERVAL').parent().parent().show();
	        $('.cat-cron-command').parent().parent().parent().show();
	    }
	    else if ($('input[type=radio][name=WEBLIR_CHATGPTPRO_ENABLE_CRON]').val() == 0) {
	        $('#WEBLIR_CHATGPTPRO_CAT_CRON_INTERVAL').parent().parent().hide();
	        $('.cat-cron-command').parent().parent().parent().hide();
	    }
	}

	cat_enableCron();

	$('input[type=radio][name=WEBLIR_CHATGPTPRO_ENABLE_CRON]').change(function() {
	    if (this.value == 1) {
	        $('#WEBLIR_CHATGPTPRO_CAT_CRON_INTERVAL').parent().parent().show();
	        $('.cat-cron-command').parent().parent().parent().show();
	    }
	    else if (this.value == 0) {
	        $('#WEBLIR_CHATGPTPRO_CAT_CRON_INTERVAL').parent().parent().hide();
	        $('.cat-cron-command').parent().parent().parent().hide();
	    }
	});


};

setTimeout(cat_explode, 1500);



</script>