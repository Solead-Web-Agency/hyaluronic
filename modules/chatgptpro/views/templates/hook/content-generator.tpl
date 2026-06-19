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

{addJsDef currentController=$currentController}
{addJsDef id_language_selected=$id_language_selected}

<style type="text/css">

#chatgptpro_dropdown div#fieldset_playground {

    margin-bottom: 0;

    border-radius: 10px;

}

#fieldset_playground label.form-check-label {
    color: #fff;
}

#chatgptpro_dropdown .modal-dialog {

    opacity: 0.95;

}



#chatgptpro_dropdown #fieldset_playground .panel-footer {

    padding-right: 10px;

    padding-top: 25px;

}



#chatgptpro_dropdown #fieldset_playground .panel-heading {

    text-transform: uppercase;

    text-align: center;

}



.the-playground {

    min-height: 50px;

    display: block;

    overflow-y: scroll;

    max-height: 360px;

    padding: 10px;

    margin-top: 10px;

}



.the-playground .the-answer {

    margin: 0;

    margin-bottom: 10px;

    background: #10a37f;

    border-radius: 0px 30px 30px 30px;

    max-width: 70%;

    padding: 16px;

    clear: both;

    position: relative;

}



.closeChatGPT {
    position: absolute;
    right: -12px;
    z-index: 9999;
    top: -12px;
    padding: 0px 0px;
    border-radius: 30px;
    width: 30px;
    height: 30px;
}



#chatgptpro_dropdown .modal-body, #chatgptpro_dropdown .modal-content {

    padding: 0;

    border-radius: 10px;

}



.the-playground p.the-quest {

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



.copytext .after-copy,

.after-save {

	display: none;

}



i.before-save,

i.after-save {

    font-size: 13px;

}



.the-playground .the-answer.much-danger {

    background: #E74C3C;

}



p.the-quest span.the-response {

    max-width: 75%;

}



.the-answer span.the-response {

    color: #fff;

    display: block;

}



#chatgptpro_dropdown .the-answer span.the-tokens {

    color: #fff;

    position: absolute;

    right: 90px;

    bottom: -10px;

    z-index: 9;

    border-radius: 20px;

    background: #F39C12;

    padding: 0px 10px;

}



button.copytext i {

    font-size: 10px;

}



button.copytext {

    background: #444857;

    border: none;

    color: #fff;

    margin: 0;

    padding: 2px 7px;

    text-transform: uppercase;

    border-radius: 21px;

    font-size: 11px;

    position: absolute;

    right: 27px;

    bottom: -10px;

    border: 1px solid #2c303a;

    transition: 0.2s all;

    display: none !important;

}



button.copytext:hover {

    background: #F39C12;

    border: 1px solid #F39C12;

}



#ChatGPTMessage {
    width: 95%;
    background: #1e1f26;
    border: none;
    color: #d5d7de;
    margin: 0 auto;
    padding: 3px 7px;
    border-radius: 3px;
    outline: 0px none transparent;
    box-shadow: 0 4px 50px rgb(10 10 12 / 60%);
    margin-top: 10px;
    resize: auto !important;
}

button.insert-prompt {
    border-radius: 30px;
    border: 1px solid #fefefe;
    font-size: 11px;
    margin-bottom: 3px;
    clear: both;
    display: block;
}


textarea#ChatGPTMessage.has-error {

    border: 2px solid #E74C3C;

}

#chatgptpro {
	padding: 10px 0;
}

#ChatGPTMessage:focus,

#ChatGPTMessage:focus-visible,

#ChatGPTMessage:active {

    outline:0px none transparent;

}



button#submitPrompt, button#submitSettings {
    background: #444857;
    border: none;
    color: #fff;
    margin: 5px 5px;
    padding: 2px 5px;
    text-transform: uppercase;
}





#fieldset_playground .content {

    box-shadow: 0 4px 50px rgba(10,10,12,0.6);

    margin: 10px;

    padding: 5px 7px;

    font-size: 10px;

}



.autocomplete-prompt:hover {

    background: green;

}



div#chatgptpro_dropdown .panel-footer label {

    min-width: 129px;

}



div#chatgptpro img {

    transform: scale(0.8);

    border-radius: 30px;

    transition: 0.2s all;

}



div#chatgptpro img:hover {

    transform: scale(1);

}


button.insert-shortcode {

    border-radius: 30px;

    border: 1px solid #fefefe;

    font-size: 11px;

    margin-bottom: 3px;

    clear: both;

    display: block;

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


<div style="display: none;">

    <p class="caret-container-module help-block">

        {l s='You can also use the following shortcodes.' mod='chatgptpro'} <br>

        {l s='For products:' mod='chatgptpro'}

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_name}{/literal}')" >{literal}{product_name}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_description}{/literal}')" >{literal}{product_description}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_description_short}{/literal}')" >{literal}{product_description_short}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_tags}{/literal}')" >{literal}{product_tags}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_reference}{/literal}')" >{literal}{product_reference}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_weight}{/literal}')" >{literal}{product_weight}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_default_category}{/literal}')" >{literal}{product_default_category}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_categories}{/literal}')" >{literal}{product_categories}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_category_description}{/literal}')" >{literal}{product_category_description}{/literal}</button> 
        
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_brand}{/literal}')" >{literal}{product_brand}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_attributes}{/literal}')" >{literal}{product_attributes}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_features}{/literal}')" >{literal}{product_features}{/literal}</button> 

        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{product_feature_values}{/literal}')" >{literal}{product_feature_values}{/literal}</button>
        <br><br>

        {l s='For categories:' mod='chatgptpro'}
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{category_name}{/literal}')" >{literal}{category_name}{/literal}</button>
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{category_description}{/literal}')" >{literal}{category_description}{/literal}</button>
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{category_parent_name}{/literal}')" >{literal}{category_parent_name}{/literal}</button>
        <br><br>

        {l s='For Brands:' mod='chatgptpro'}
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{brand_name}{/literal}')" >{literal}{brand_name}{/literal}</button>
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{brand_description}{/literal}')" >{literal}{brand_description}{/literal}</button>
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{brand_short_description}{/literal}')" >{literal}{brand_short_description}{/literal}</button>
        <br><br>

        {l s='For CMS Pages:' mod='chatgptpro'}
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{cms_title}{/literal}')" >{literal}{cms_title}{/literal}</button>
        <br><br>

        {l s='General Usage:' mod='chatgptpro'}
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{shop_name}{/literal}')" >{literal}{shop_name}{/literal}</button>
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{shop_url}{/literal}')" >{literal}{shop_url}{/literal}</button>
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{shop_language}{/literal}')" >{literal}{shop_language}{/literal}</button>
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{shop_country}{/literal}')" >{literal}{shop_country}{/literal}</button>
        <button class="insert-shortcode" type="button" onclick="$('#WEBLIR_CHATGPTPRO_TEMPLATE').insertAtCaretModule('{literal}{shop_currency}{/literal}')" >{literal}{shop_currency}{/literal}</button>
    </p>

</div>





<div id="chatgptpro-container" style="display: none !important;">







	<div class="component header-right-component" id="chatgptpro">



		<a href="javascript:void(0);" data-toggle="modal" data-target="#chatgptpro_dropdown" title="{l s='ChatGPT Content Generator' mod='chatgptpro'}">



			<img src="{$path|escape:'htmlall':'UTF-8'}logo-spin.png" class="rotating" alt="0" width="24">



		</a>



	</div>



</div>





<div class="modal fade" id="chatgptpro_dropdown" tabindex="-1" role="dialog" aria-labelledby="chatgptproModalLabel" aria-hidden="true">



	<div class="modal-dialog modal-lg" role="document">



		<div class="modal-content">



			<div class="modal-body">



				<button type="button" class="btn btn-danger closeChatGPT" data-dismiss="modal" title="{l s='Close' mod='chatgptpro'}"><i class="material-icons">close</i></button>



				<div class="panel" id="fieldset_playground" style="background-color: #2c303a;padding: 10px;color: #fff;">

					<div class="panel-heading"><i class="icon-info-circle"></i> {l s='Generate content using OpenAI ChatGPT' mod='chatgptpro'} <a href="#" title="{l s='Prompt templates' mod='chatgptpro'}" class="show-prompts"><i class="material-icons">swap_horizontal_circle</i></a> </div>



					<div class="the-playground">



					</div>



					<div class="row clearfix"></div>



					<div class="form-wrapper">

						<div class="form-group hide">

							<input type="hidden" name="sender" id="sender" value="">

						</div>

						<div class="form-group">

							<div class="row">

								<div class="col-lg-10" style="text-align: center;">

									<textarea id="ChatGPTMessage" class="textarea-autosize" style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 51.6042px;white-space: pre-line;"></textarea>

                                    {if isset($currentController) && $currentController && $currentController == 'AdminCategories'}
                                        <div id="autoCompleteCategory" style="display: none;text-align: left;">
                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoName" value="autoName"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Category Name' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoDesc" value="autoDesc" checked />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Category Description' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoMetaTitle" value="autoMetaTitle"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Category Meta Title' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoMetaDesc" value="autoMetaDesc"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Category Meta Description' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <select class="form-control custom-select lang-select" id="lang-select">
                                                {foreach from=$languages_list item=single_lang}
                                                    <option value="{$single_lang.id_lang}" {if isset($id_language_selected) && $id_language_selected == $single_lang.id_lang}selected="selected"{/if} >{$single_lang.name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    {/if}

                                    {if isset($currentController) && $currentController && $currentController == 'AdminCmsContent'}
                                        <div id="autoCompleteCms" style="display: none;">
                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoName" value="autoName"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete CMS Name' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoDesc" value="autoDesc" checked />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete CMS Page Content' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoMetaTitle" value="autoMetaTitle"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete CMS Meta Title' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoMetaDesc" value="autoMetaDesc"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete CMS Meta Description' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <select class="form-control custom-select lang-select" id="lang-select">
                                                {foreach from=$languages_list item=single_lang}
                                                    <option value="{$single_lang.id_lang}" {if isset($id_language_selected) && $id_language_selected == $single_lang.id_lang}selected="selected"{/if} >{$single_lang.name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    {/if}

                                    {if isset($currentController) && $currentController && $currentController == 'AdminManufacturers'}
                                        <div id="autoCompleteBrand" style="display: none;">
                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoName" value="autoName"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Brand Name' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoDesc" value="autoDesc" checked />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Brand Description' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoShortDesc" value="autoShortDesc"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Brand Short Description' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoMetaTitle" value="autoMetaTitle"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Brand Meta Title' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                              <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="autoContent" id="autoMetaDesc" value="autoMetaDesc"  />
                                                <i class="form-check-round"></i>
                                                {l s='Autocomplete Brand Meta Description' mod='chatgptpro'}
                                              </label>
                                            </div>

                                            <select class="form-control custom-select lang-select" id="lang-select">
                                                {foreach from=$languages_list item=single_lang}
                                                    <option value="{$single_lang.id_lang}" {if isset($id_language_selected) && $id_language_selected == $single_lang.id_lang}selected="selected"{/if} >{$single_lang.name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    {/if}

								</div>



								<div class="col-lg-2 text-right align-right">

									<a href="#" title="{l s='Module settings' mod='chatgptpro'}" class="show-settings"><i class="material-icons mi-settings">settings</i></a> <button type="button" id="submitPrompt" class="btn btn-default pull-right submit-prompt"><div class="spinnerx"> <div class="bounce1"></div> <div class="bounce2"></div> <div class="bounce3"></div> </div>  <span class="submit_text">{l s='Submit' mod='chatgptpro'}</span> </button>

								</div>

							</div>

                            <div id="promptsContainer" style="display: none;">
                                <p>{l s='Prompt Templates:' mod='chatgptpro'}</p>
                                {foreach from=$prompts item=prompt}
                                    <button class="insert-prompt" type="button" data-prompt="{$prompt.prompt nofilter}" >{$prompt.title}</button> 
                                {/foreach}
                            </div>


						</div>

					</div>


                    

                    

					<div class="panel-footer" style="display: none;">

						<div class="row">

							<div class="col-lg-4">

								<label for="WEBLIR_CHATGPTPRO_MAX_TOKENS">{l s='Max Tokens:' mod='chatgptpro'}</label>

								<input type="text" id="WEBLIR_CHATGPTPRO_MAX_TOKENS" readonly style="border:0; color:#f6931f; font-weight:bold;max-width: 100px;">

							</div>

							<div class="col-lg-8">

								<div id="SLIDER_WEBLIR_CHATGPTPRO_MAX_TOKENS"></div>

							</div>

							<hr>

						</div>



						<div class="row">

							<div class="col-lg-4">

								<label for="WEBLIR_CHATGPTPRO_TEMPERATURE">{l s='Temperature:' mod='chatgptpro'}</label>

								<input type="text" id="WEBLIR_CHATGPTPRO_TEMPERATURE" readonly style="border:0; color:#f6931f; font-weight:bold;max-width: 100px;">

							</div>

							<div class="col-lg-8">

								<div id="SLIDER_WEBLIR_CHATGPTPRO_TEMPERATURE"></div>

							</div>

							<hr>

						</div>



						<div class="row">

							<div class="col-lg-4">

								<label for="WEBLIR_CHATGPTPRO_TOP_P">{l s='Top P:' mod='chatgptpro'}</label>

								<input type="text" id="WEBLIR_CHATGPTPRO_TOP_P" readonly style="border:0; color:#f6931f; font-weight:bold;max-width: 100px;">

							</div>

							<div class="col-lg-8">

								<div id="SLIDER_WEBLIR_CHATGPTPRO_TOP_P"></div>

							</div>

							<hr>

						</div>



						<div class="row">

							<div class="col-lg-4">

								<label for="WEBLIR_CHATGPTPRO_FREQ_PEN">{l s='Frequency Penalty:' mod='chatgptpro'}</label>

								<input type="text" id="WEBLIR_CHATGPTPRO_FREQ_PEN" readonly style="border:0; color:#f6931f; font-weight:bold;max-width: 100px;">

							</div>

							<div class="col-lg-8">

								<div id="SLIDER_WEBLIR_CHATGPTPRO_FREQ_PEN"></div>

							</div>

							<hr>

						</div>



						<div class="row">

							<div class="col-lg-4">

								<label for="WEBLIR_CHATGPTPRO_PRES_PEN">{l s='Presence Penalty:' mod='chatgptpro'}</label>

								<input type="text" id="WEBLIR_CHATGPTPRO_PRES_PEN" readonly style="border:0; color:#f6931f; font-weight:bold;max-width: 100px;">

							</div>

							<div class="col-lg-8">

								<div id="SLIDER_WEBLIR_CHATGPTPRO_PRES_PEN"></div>

							</div>

						</div>



						<div class="row">

							<div class="col-lg-12 text-right">

								<button type="button" id="submitSettings" class="btn btn-default submit-prompt">

									<div class="spinnerx"> <div class="bounce1"></div> <div class="bounce2"></div> <div class="bounce3"></div> </div>

									<span class="submit_text">

										<i class="material-icons before-save">save</i>

										<i class="material-icons after-save">check</i>

										{l s='Submit' mod='chatgptpro'}

									</span>

								</button>

							</div>

						</div>







					</div>

				</div>



			</div>





				





		</div>



	</div>



</div>




{if isset($currentController) && $currentController && $currentController == 'AdminCmsContent'}
<script type="text/javascript">
$( document ).ready(function() {
    if ($('input[type=radio][name=cms_page\\[is_indexed_for_search\\]]').length) {
       $("#autoCompleteCms").show();
    }
});
</script>
{/if}


{if isset($currentController) && $currentController && $currentController == 'AdminCategories'}
<script type="text/javascript">
$( document ).ready(function() {
    if ($('input[type=radio][name=category\\[active\\]]').length) {
       $("#autoCompleteCategory").show();
    }
});
</script>
{/if}


{if isset($currentController) && $currentController && $currentController == 'AdminManufacturers'}
<script type="text/javascript">
$( document ).ready(function() {
    if ($('input[type=radio][name=manufacturer\\[is_enabled\\]]').length) {
       $("#autoCompleteBrand").show();
    }
});
</script>
{/if}
                



<script type="text/javascript">






$( document ).ready(function() {

    $(".insert-prompt").click(function(){

        $("#ChatGPTMessage").val($(this).attr('data-prompt'));

    });

    $(".caret-container-module").insertAfter($("#WEBLIR_CHATGPTPRO_TEMPLATE"));

});


$.fn.extend({

  insertAtCaretModule: function(myValue){

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


$( function() {

    $( "#SLIDER_WEBLIR_CHATGPTPRO_MAX_TOKENS" ).slider({

      range: "max",

      min: 1,

      max: 4000,

      step: 1,

      value: {$module_vars.WEBLIR_CHATGPTPRO_MAX_TOKENS},

      slide: function( event, ui ) {

        $( "#WEBLIR_CHATGPTPRO_MAX_TOKENS" ).val( ui.value );

      }

    });

    $( "#WEBLIR_CHATGPTPRO_MAX_TOKENS" ).val( $( "#SLIDER_WEBLIR_CHATGPTPRO_MAX_TOKENS" ).slider( "value" ) );

  } );



$( function() {

    $( "#SLIDER_WEBLIR_CHATGPTPRO_TEMPERATURE" ).slider({

      range: "max",

      min: 0,

      max: 1,

      step: 0.1,

      value: {$module_vars.WEBLIR_CHATGPTPRO_TEMPERATURE},

      slide: function( event, ui ) {

        $( "#WEBLIR_CHATGPTPRO_TEMPERATURE" ).val( ui.value );

      }

    });

    $( "#WEBLIR_CHATGPTPRO_TEMPERATURE" ).val( $( "#SLIDER_WEBLIR_CHATGPTPRO_TEMPERATURE" ).slider( "value" ) );

  } );



$( function() {

    $( "#SLIDER_WEBLIR_CHATGPTPRO_TOP_P" ).slider({

      range: "max",

      min: 0,

      max: 1,

      step: 0.1,

      value: {$module_vars.WEBLIR_CHATGPTPRO_TOP_P},

      slide: function( event, ui ) {

        $( "#WEBLIR_CHATGPTPRO_TOP_P" ).val( ui.value );

      }

    });

    $( "#WEBLIR_CHATGPTPRO_TOP_P" ).val( $( "#SLIDER_WEBLIR_CHATGPTPRO_TOP_P" ).slider( "value" ) );

  } );



$( function() {

    $( "#SLIDER_WEBLIR_CHATGPTPRO_FREQ_PEN" ).slider({

      range: "max",

      min: 0,

      max: 2,

      step: 0.1,

      value: {$module_vars.WEBLIR_CHATGPTPRO_FREQ_PEN},

      slide: function( event, ui ) {

        $( "#WEBLIR_CHATGPTPRO_FREQ_PEN" ).val( ui.value );

      }

    });

    $( "#WEBLIR_CHATGPTPRO_FREQ_PEN" ).val( $( "#SLIDER_WEBLIR_CHATGPTPRO_FREQ_PEN" ).slider( "value" ) );

  } );



$( function() {

    $( "#SLIDER_WEBLIR_CHATGPTPRO_PRES_PEN" ).slider({

      range: "max",

      min: 0,

      max: 2,

      step: 0.1,

      value: {$module_vars.WEBLIR_CHATGPTPRO_PRES_PEN},

      slide: function( event, ui ) {

        $( "#WEBLIR_CHATGPTPRO_PRES_PEN" ).val( ui.value );

      }

    });

    $( "#WEBLIR_CHATGPTPRO_PRES_PEN" ).val( $( "#SLIDER_WEBLIR_CHATGPTPRO_PRES_PEN" ).slider( "value" ) );

  } );







setTimeout(function(){

	$('#chatgptpro').insertAfter('#quick-access-container, #header_quick');

}, 1000);





$(".autocomplete-prompt").click(function(){

	$("#ChatGPTMessage").val($(this).html());

});



$(".show-settings").click(function(){

	$('#chatgptpro_dropdown .panel-footer').toggle('slow');

});


$(".show-prompts").click(function(){

    $('#promptsContainer').toggle('slow');

});



$("#submitSettings").click(function(){

	$("#submitSettings .submit_text").hide();

	$("#submitSettings .before-save").hide();

	$("#submitSettings .after-save").hide();

	$("#submitSettings .spinnerx").show();

	$.ajax({

		method: "GET",

		url: "{$module_controller|escape:'htmlall':'UTF-8'}",

		data: {

			WEBLIR_CHATGPTPRO_MAX_TOKENS: $("#WEBLIR_CHATGPTPRO_MAX_TOKENS").val(),

			WEBLIR_CHATGPTPRO_TEMPERATURE: $("#WEBLIR_CHATGPTPRO_TEMPERATURE").val(),

			WEBLIR_CHATGPTPRO_TOP_P: $("#WEBLIR_CHATGPTPRO_TOP_P").val(),

			WEBLIR_CHATGPTPRO_FREQ_PEN: $("#WEBLIR_CHATGPTPRO_FREQ_PEN").val(),

			WEBLIR_CHATGPTPRO_PRES_PEN: $("#WEBLIR_CHATGPTPRO_PRES_PEN").val(),

			action: 'updateSettings'

		}

	}).done(function( msg ) {

		var data = jQuery.parseJSON( msg );

		$("#submitSettings .spinnerx").hide();

		$("#submitSettings .submit_text").show();

		$("#submitSettings .after-save").show();

		

    	setTimeout(function(){

			$("#submitSettings .before-save").show();

			$("#submitSettings .after-save").hide();

		}, 3000);



    });



});





$("#submitPrompt").click(function(){

	if ($('#lang-select').length) {
        var lang_id = $('#lang-select').val();
    } else {
        var lang_id = id_language_selected;
    }

	var chat_message = $("#ChatGPTMessage").val();

    var chat_message_formated = chat_message.replace(/(<([^>]+)>)/gi, "");

	$("#ChatGPTMessage").val("");

	var len = chat_message.length;

	if (len > 0) {

		$('#ChatGPTMessage').removeClass("has-error");

		$("#submitPrompt .submit_text").hide();

		$("#submitPrompt .spinnerx").show();

		

	  	$(".the-playground").append('<p class="the-quest text-right"><span>'+chat_message_formated+'</span></p>');

	  	$(".the-playground").animate({ scrollTop: $('.the-playground').prop("scrollHeight")}, 1000);

        if (typeof idcategory != 'undefined' && $.isNumeric(idcategory)) {
            var category_id = idcategory;
        } else {
            var category_id = false;
        }

        if (typeof idcms != 'undefined' && $.isNumeric(idcms)) {
            var cms_id = idcms;
        } else {
            var cms_id = false;
        }

        if (typeof idmanufacturer != 'undefined' && $.isNumeric(idmanufacturer)) {
            var manufacturer_id = idmanufacturer;
        } else {
            var manufacturer_id = false;
        }

	  	$.ajax({

			method: "GET",

			url: "{$module_controller|escape:'htmlall':'UTF-8'}",

			data: {

				prompt: encodeURIComponent(chat_message),

				action: 'initiatePrompt',

                id_category: category_id,

                id_cms: cms_id,

                id_manufacturer: manufacturer_id,

                id_language: lang_id

			}

		}).done(function( msg ) {

			var data = jQuery.parseJSON( msg );

			

			if (typeof data.date !== 'undefined' && data.status == 'success') {

				$(".the-playground").append('<div class="the-answer text-left"><span class="the-response">'+data.date.response+'</span><button type="button" class="copytext" data-clipboard-target="'+data.date.response+'"><i class="material-icons before-copy">content_copy</i><i class="material-icons after-copy">check</i> {l s='Copy' mod='chatgptpro'}</button><span class="the-tokens">{l s='Total tokens used' mod='chatgptpro'}: '+data.date.total_tokens+'</span></div>');

                if ($('input[type=radio][name=category\\[active\\]]').length) {
                    var autocompleted_field = $('input[name="autoContent"]:checked').val();
                    var autocomplete_lang = $("#lang-select").val();

                    if (autocompleted_field == "autoName") {
                        $("#category_name_"+autocomplete_lang).val(data.date.response);
                    } else if (autocompleted_field == "autoDesc") {
                        $("#category_description_"+autocomplete_lang).val(data.date.response);
                        $(tinymce.get('category_description_'+autocomplete_lang).getBody()).html(data.date.response);
                    } else if (autocompleted_field == "autoMetaTitle") {
                        $("#category_meta_title_"+autocomplete_lang).val(data.date.response);
                    } else if (autocompleted_field == "autoMetaDesc") {
                        $("#category_meta_description_"+autocomplete_lang).val(data.date.response);
                    }
                }

                if ($('input[type=radio][name=cms_page\\[is_indexed_for_search\\]]').length) {
                    var autocompleted_field = $('input[name="autoContent"]:checked').val();
                    var autocomplete_lang = $("#lang-select").val();

                    if (autocompleted_field == "autoName") {
                        $("#cms_page_title_"+autocomplete_lang).val(data.date.response);
                    } else if (autocompleted_field == "autoDesc") {
                        $("#cms_page_content_"+autocomplete_lang).val(data.date.response);
                        $(tinymce.get('cms_page_content_'+autocomplete_lang).getBody()).html(data.date.response);
                    } else if (autocompleted_field == "autoMetaTitle") {
                        $("#cms_page_meta_title_"+autocomplete_lang).val(data.date.response);
                    } else if (autocompleted_field == "autoMetaDesc") {
                        $("#cms_page_meta_description_"+autocomplete_lang).val(data.date.response);
                    }
                }

                if ($('input[type=radio][name=manufacturer\\[is_enabled\\]]').length) {
                    var autocompleted_field = $('input[name="autoContent"]:checked').val();
                    var autocomplete_lang = $("#lang-select").val();

                    if (autocompleted_field == "autoName") {
                        $("#manufacturer_name").val(data.date.response);
                    } else if (autocompleted_field == "autoDesc") {
                        $("#manufacturer_description_"+autocomplete_lang).val(data.date.response);
                        $(tinymce.get('manufacturer_description_'+autocomplete_lang).getBody()).html(data.date.response);
                    } else if (autocompleted_field == "autoShortDesc") {
                        $("#manufacturer_short_description_"+autocomplete_lang).val(data.date.response);
                        $(tinymce.get('manufacturer_short_description_'+autocomplete_lang).getBody()).html(data.date.response);
                    } else if (autocompleted_field == "autoMetaTitle") {
                        $("#manufacturer_meta_title_"+autocomplete_lang).val(data.date.response);
                    } else if (autocompleted_field == "autoMetaDesc") {
                        $("#manufacturer_meta_description_"+autocomplete_lang).val(data.date.response);
                    }
                }

	  			$(".the-playground").animate({ scrollTop: $('.the-playground').prop("scrollHeight")}, 1000);

	        } else if (typeof data.status !== 'undefined' && data.status == 'error') {

	        	$(".the-playground").append('<div class="the-answer text-left much-danger"><span>'+data.msg+'</span></div>');

	  			$(".the-playground").animate({ scrollTop: $('.the-playground').prop("scrollHeight")}, 1000);

	        }



	        $("#submitPrompt .submit_text").show();

			$("#submitPrompt .spinnerx").hide();



			$(".copytext").click(function(){

				var $target;

				$target = $(this).attr('data-clipboard-target');



				var select_this = $(this);



				$('body').append('<textarea id="clipboard-text">' + $target + '</textarea>');

				$('#clipboard-text').select();



				if (document.execCommand('copy')) {

					$(this).parent().find(".before-copy").hide();

			    	$(this).parent().find(".after-copy").show();

			    	setTimeout(function(){

						select_this.parent().find(".before-copy").show();

				    	select_this.parent().find(".after-copy").hide();

					}, 3000);

				} else {

					alert('error');

				}

				$('#clipboard-text').remove();

			});



	    });



	} else {

		$('#ChatGPTMessage').addClass("has-error");

	 	$("#ChatGPTMessage").focus();

	}



	



});



</script>



