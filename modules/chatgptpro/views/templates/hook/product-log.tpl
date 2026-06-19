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

<h2>{l s='Product edit history' mod='chatgptpro'}</h2>

<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a
      class="nav-link active"
      id="product-name-tab"
      data-toggle="tab"
      href="#product-name"
      role="tab"
      aria-controls="product-name"
      aria-expanded="true"
      >{l s='Product name' mod='chatgptpro'}</a
    >
  </li>
  <li class="nav-item">
    <a
      class="nav-link"
      id="desc-tab"
      data-toggle="tab"
      href="#desc"
      role="tab"
      aria-controls="desc"
      aria-expanded="true"
      >{l s='Description' mod='chatgptpro'}</a
    >
  </li>
  <li class="nav-item">
    <a
      class="nav-link"
      id="short-desc-tab"
      data-toggle="tab"
      href="#short-desc"
      role="tab"
      aria-controls="short-desc"
      aria-expanded="true"
      >{l s='Short Description' mod='chatgptpro'}</a
    >
  </li>
  <li class="nav-item">
    <a
      class="nav-link"
      id="meta-title-tab"
      data-toggle="tab"
      href="#meta-title"
      role="tab"
      aria-controls="meta-title"
      aria-expanded="true"
      >{l s='Meta Title' mod='chatgptpro'}</a
    >
  </li>
  <li class="nav-item">
    <a
      class="nav-link"
      id="meta-desc-tab"
      data-toggle="tab"
      href="#meta-desc"
      role="tab"
      aria-controls="meta-desc"
      aria-expanded="true"
      >{l s='Meta Description' mod='chatgptpro'}</a
    >
  </li>
</ul>
<div class="tab-content" id="tabContent">
  <div
    class="tab-pane fade show active"
    id="product-name"
    role="tabpanel"
    aria-labelledby="product-name-tab"
  >
    <table class="table table-striped">
	  <thead>
	    <tr>
	      <th>#</th>
	      <th>{l s='Product name' mod='chatgptpro'}</th>
	      <th>{l s='Language' mod='chatgptpro'}</th>
	      <th>{l s='Timestamp' mod='chatgptpro'}</th>
	      <th>{l s='Actions' mod='chatgptpro'}</th>
	    </tr>
	  </thead>
	  <tbody>
	  	{foreach from=$product_log item=prod_log}
	  		{if $prod_log.type == 'product_name' || $prod_log.type == 'single_product'}
		    <tr>
		      <th scope="row">{$prod_log.id_log}</th>
		      <td>
		      	<small>{$prod_log.new_name|truncate:120:"...":true} {if $prod_log.new_name|count_characters:true > 120}<span class="help-box" data-toggle="modal" data-target="#view-title-{$prod_log.id_log}"></span>{/if}</small>
		      	<div
				  class="modal fade"
				  id="view-title-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="product-name-{$prod_log.id_log}"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="product-name-{$prod_log.id_log}">{l s='Product Name' mod='chatgptpro'} {l s='on' mod='chatgptpro'} {$prod_log.date_updated}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        {$prod_log.new_name}
				      </div>
				    </div>
				  </div>
				</div>
			  </td>
		      <td class="text-center">{if isset($prod_log.id_lang) && $prod_log.id_lang|intval>0 && isset($languages_parsed.{$prod_log.id_lang})}<img title="{$languages_parsed.{$prod_log.id_lang}}" src="{$shopurl}/img/l/{$prod_log.id_lang}.jpg" width="16" height="11">{/if}</td>
		      <td>{$prod_log.date_updated}</td>
		      <td>
		      	<button type="button" href="#" class="btn btn-primary btn-sm" title="{l s='Restore product name' mod='chatgptpro'}" data-toggle="modal" data-target="#restoreContent-{$prod_log.id_log}"><i class="material-icons">restore</i></button>

		      	<div
				  class="modal fade restore-product-content"
				  id="restoreContent-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="restore-title"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="restore-title">{l s='Restore product name' mod='chatgptpro'}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">

				      	<input type="hidden" name="id_log" value="{$prod_log.id_log}" class="id_log">
				      	<input type="hidden" name="type" value="{$prod_log.type}" class="type">
				      	<div class="form-group">
						  <label class="form-control-label" for="lang-select">Select language</label>
						  <select class="form-control custom-select lang-select" id="lang-select">
						  	{foreach from=$languages_list item=single_lang}
							    <option value="{$single_lang.id_lang}" {if isset($prod_log.id_lang) && $prod_log.id_lang == $single_lang.id_lang}selected="selected"{else}{if $id_language_selected == $single_lang.id_lang}selected="selected"{/if}{/if} >{$single_lang.name}</option>
							{/foreach}
						  </select>
						</div>

				      </div>
				      <div class="modal-footer">
				        <button
				          type="button"
				          class="btn btn-outline-secondary"
				          data-dismiss="modal"
				        >
				          {l s='Cancel' mod='chatgptpro'}
				        </button>
				        <button type="button" class="btn btn-primary initiateRestore">{l s='Restore product name' mod='chatgptpro'}</button>
				      </div>
				    </div>
				  </div>
				</div>

		      </td>
		    </tr>
		    {/if}
		{/foreach}
	  </tbody>
	</table>
  </div>
  <div
    class="tab-pane fade"
    id="desc"
    role="tabpanel"
    aria-labelledby="desc-tab"
  >
    <table class="table table-striped">
	  <thead>
	    <tr>
	      <th>#</th>
	      <th>{l s='Description' mod='chatgptpro'}</th>
	      <th>{l s='Language' mod='chatgptpro'}</th>
	      <th>{l s='Timestamp' mod='chatgptpro'}</th>
	      <th>{l s='Actions' mod='chatgptpro'}</th>
	    </tr>
	  </thead>
	  <tbody>
	  	{foreach from=$product_log item=prod_log}
	  		{if $prod_log.type == 'product_description'}
		    <tr>
		      <th scope="row">{$prod_log.id_log}</th>
		      <td>
		      	<small>{$prod_log.new_description|truncate:240:"...":true} {if $prod_log.new_description|count_characters:true > 240}<span class="help-box" data-toggle="modal" data-target="#view-desc-{$prod_log.id_log}"></span>{/if}</small>
		      	<div
				  class="modal fade"
				  id="view-desc-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="product-desc-{$prod_log.id_log}"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="product-desc-{$prod_log.id_log}">{l s='Description' mod='chatgptpro'} {l s='on' mod='chatgptpro'} {$prod_log.date_updated}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        {$prod_log.new_description}
				      </div>
				    </div>
				  </div>
				</div>
			  </td>
		      <td class="text-center">{if isset($prod_log.id_lang) && $prod_log.id_lang|intval>0 && isset($languages_parsed.{$prod_log.id_lang})}<img title="{$languages_parsed.{$prod_log.id_lang}}" src="{$shopurl}/img/l/{$prod_log.id_lang}.jpg" width="16" height="11">{/if}</td>
		      <td>{$prod_log.date_updated}</td>
		      <td>
		      	<button type="button" href="#" class="btn btn-primary btn-sm" title="{l s='Restore product description' mod='chatgptpro'}" data-toggle="modal" data-target="#restoreContent-{$prod_log.id_log}"><i class="material-icons">restore</i></button>

		      	<div
				  class="modal fade restore-product-content"
				  id="restoreContent-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="restore-title"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="restore-title">{l s='Restore product description' mod='chatgptpro'}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">

				      	<input type="hidden" name="id_log" value="{$prod_log.id_log}" class="id_log">
				      	<input type="hidden" name="type" value="{$prod_log.type}" class="type">
				      	<div class="form-group">
						  <label class="form-control-label" for="lang-select">Select language</label>
						  <select class="form-control custom-select lang-select" id="lang-select">
						  	{foreach from=$languages_list item=single_lang}
							    <option value="{$single_lang.id_lang}" {if isset($prod_log.id_lang) && $prod_log.id_lang == $single_lang.id_lang}selected="selected"{else}{if $id_language_selected == $single_lang.id_lang}selected="selected"{/if}{/if} >{$single_lang.name}</option>
							{/foreach}
						  </select>
						</div>

				      </div>
				      <div class="modal-footer">
				        <button
				          type="button"
				          class="btn btn-outline-secondary"
				          data-dismiss="modal"
				        >
				          {l s='Cancel' mod='chatgptpro'}
				        </button>
				        <button type="button" class="btn btn-primary initiateRestore">{l s='Restore product description' mod='chatgptpro'}</button>
				      </div>
				    </div>
				  </div>
				</div>

		      </td>
		    </tr>
		    {/if}
		{/foreach}
	  </tbody>
	</table>
  </div>
  <div
    class="tab-pane fade"
    id="short-desc"
    role="tabpanel"
    aria-labelledby="short-desc-tab"
  >
        <table class="table table-striped">
	  <thead>
	    <tr>
	      <th>#</th>
	      <th>{l s='Short Description' mod='chatgptpro'}</th>
	      <th>{l s='Language' mod='chatgptpro'}</th>
	      <th>{l s='Timestamp' mod='chatgptpro'}</th>
	      <th>{l s='Actions' mod='chatgptpro'}</th>
	    </tr>
	  </thead>
	  <tbody>
	  	{foreach from=$product_log item=prod_log}
	  		{if $prod_log.type == 'product_description_short'}
		    <tr>
		      <th scope="row">{$prod_log.id_log}</th>
		      <td>
		      	<small>{$prod_log.new_description_short|truncate:180:"...":true} {if $prod_log.new_description_short|count_characters:true > 180}<span class="help-box" data-toggle="modal" data-target="#view-desc-{$prod_log.id_log}"></span>{/if}</small>
		      	<div
				  class="modal fade"
				  id="view-desc-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="product-short-desc-{$prod_log.id_log}"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="product-short-desc-{$prod_log.id_log}">{l s='Short Description' mod='chatgptpro'} {l s='on' mod='chatgptpro'} {$prod_log.date_updated}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        {$prod_log.new_description_short}
				      </div>
				    </div>
				  </div>
				</div>
			  </td>
		      <td class="text-center">{if isset($prod_log.id_lang) && $prod_log.id_lang|intval>0 && isset($languages_parsed.{$prod_log.id_lang})}<img title="{$languages_parsed.{$prod_log.id_lang}}" src="{$shopurl}/img/l/{$prod_log.id_lang}.jpg" width="16" height="11">{/if}</td>
		      <td>{$prod_log.date_updated}</td>
		      <td>
		      	<button type="button" href="#" class="btn btn-primary btn-sm" title="{l s='Restore product short description' mod='chatgptpro'}" data-toggle="modal" data-target="#restoreContent-{$prod_log.id_log}"><i class="material-icons">restore</i></button>

		      	<div
				  class="modal fade restore-product-content"
				  id="restoreContent-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="restore-title"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="restore-title">{l s='Restore product short description' mod='chatgptpro'}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">

				      	<input type="hidden" name="id_log" value="{$prod_log.id_log}" class="id_log">
				      	<input type="hidden" name="type" value="{$prod_log.type}" class="type">
				      	<div class="form-group">
						  <label class="form-control-label" for="lang-select">Select language</label>
						  <select class="form-control custom-select lang-select" id="lang-select">
						  	{foreach from=$languages_list item=single_lang}
							    <option value="{$single_lang.id_lang}" {if isset($prod_log.id_lang) && $prod_log.id_lang == $single_lang.id_lang}selected="selected"{else}{if $id_language_selected == $single_lang.id_lang}selected="selected"{/if}{/if} >{$single_lang.name}</option>
							{/foreach}
						  </select>
						</div>

				      </div>
				      <div class="modal-footer">
				        <button
				          type="button"
				          class="btn btn-outline-secondary"
				          data-dismiss="modal"
				        >
				          {l s='Cancel' mod='chatgptpro'}
				        </button>
				        <button type="button" class="btn btn-primary initiateRestore">{l s='Restore product short description' mod='chatgptpro'}</button>
				      </div>
				    </div>
				  </div>
				</div>

		      </td>
		    </tr>
		    {/if}
		{/foreach}
	  </tbody>
	</table>
  </div>
  <div
    class="tab-pane fade"
    id="meta-title"
    role="tabpanel"
    aria-labelledby="meta-title-tab"
  >
    <table class="table table-striped">
	  <thead>
	    <tr>
	      <th>#</th>
	      <th>{l s='Meta Title' mod='chatgptpro'}</th>
	      <th>{l s='Language' mod='chatgptpro'}</th>
	      <th>{l s='Timestamp' mod='chatgptpro'}</th>
	      <th>{l s='Actions' mod='chatgptpro'}</th>
	    </tr>
	  </thead>
	  <tbody>
	  	{foreach from=$product_log item=prod_log}
	  		{if $prod_log.type == 'product_meta_title'}
		    <tr>
		      <th scope="row">{$prod_log.id_log}</th>
		      <td>
		      	<small>{$prod_log.new_meta_title|truncate:70:"...":true} {if $prod_log.new_meta_title|count_characters:true > 70}<span class="help-box" data-toggle="modal" data-target="#view-meta-title-{$prod_log.id_log}"></span>{/if}</small>
		      	<div
				  class="modal fade"
				  id="view-meta-title-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="product-meta-title-{$prod_log.id_log}"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="product-meta-title-{$prod_log.id_log}">{l s='Meta Title' mod='chatgptpro'} {l s='on' mod='chatgptpro'} {$prod_log.date_updated}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        {$prod_log.new_meta_title}
				      </div>
				    </div>
				  </div>
				</div>
			  </td>
		      <td class="text-center">{if isset($prod_log.id_lang) && $prod_log.id_lang|intval>0 && isset($languages_parsed.{$prod_log.id_lang})}<img title="{$languages_parsed.{$prod_log.id_lang}}" src="{$shopurl}/img/l/{$prod_log.id_lang}.jpg" width="16" height="11">{/if}</td>
		      <td>{$prod_log.date_updated}</td>
		      <td>
		      	<button type="button" href="#" class="btn btn-primary btn-sm" title="{l s='Restore product meta title' mod='chatgptpro'}" data-toggle="modal" data-target="#restoreContent-{$prod_log.id_log}"><i class="material-icons">restore</i></button>

		      	<div
				  class="modal fade restore-product-content"
				  id="restoreContent-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="restore-title"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="restore-title">{l s='Restore product meta title' mod='chatgptpro'}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">

				      	<input type="hidden" name="id_log" value="{$prod_log.id_log}" class="id_log">
				      	<input type="hidden" name="type" value="{$prod_log.type}" class="type">
				      	<div class="form-group">
						  <label class="form-control-label" for="lang-select">Select language</label>
						  <select class="form-control custom-select lang-select" id="lang-select">
						  	{foreach from=$languages_list item=single_lang}
							    <option value="{$single_lang.id_lang}" {if isset($prod_log.id_lang) && $prod_log.id_lang == $single_lang.id_lang}selected="selected"{else}{if $id_language_selected == $single_lang.id_lang}selected="selected"{/if}{/if} >{$single_lang.name}</option>
							{/foreach}
						  </select>
						</div>

				      </div>
				      <div class="modal-footer">
				        <button
				          type="button"
				          class="btn btn-outline-secondary"
				          data-dismiss="modal"
				        >
				          {l s='Cancel' mod='chatgptpro'}
				        </button>
				        <button type="button" class="btn btn-primary initiateRestore">{l s='Restore product meta title' mod='chatgptpro'}</button>
				      </div>
				    </div>
				  </div>
				</div>

		      </td>
		    </tr>
		    {/if}
		{/foreach}
	  </tbody>
	</table>
  </div>
  <div
    class="tab-pane fade"
    id="meta-desc"
    role="tabpanel"
    aria-labelledby="meta-desc-tab"
  >
        <table class="table table-striped">
	  <thead>
	    <tr>
	      <th>#</th>
	      <th>{l s='Meta Description' mod='chatgptpro'}</th>
	      <th>{l s='Language' mod='chatgptpro'}</th>
	      <th>{l s='Timestamp' mod='chatgptpro'}</th>
	      <th>{l s='Actions' mod='chatgptpro'}</th>
	    </tr>
	  </thead>
	  <tbody>
	  	{foreach from=$product_log item=prod_log}
	  		{if $prod_log.type == 'product_meta_description'}
		    <tr>
		      <th scope="row">{$prod_log.id_log}</th>
		      <td>
		      	<small>{$prod_log.new_meta_description|truncate:159:"...":true} {if $prod_log.new_meta_description|count_characters:true > 159}<span class="help-box" data-toggle="modal" data-target="#view-meta-desc-{$prod_log.id_log}"></span>{/if}</small>
		      	<div
				  class="modal fade"
				  id="view-meta-desc-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="product-meta-desc-{$prod_log.id_log}"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="product-meta-desc-{$prod_log.id_log}">{l s='Meta Description' mod='chatgptpro'} {l s='on' mod='chatgptpro'} {$prod_log.date_updated}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        {$prod_log.new_meta_description}
				      </div>
				    </div>
				  </div>
				</div>
			  </td>
		      <td class="text-center">{if isset($prod_log.id_lang) && $prod_log.id_lang|intval>0 && isset($languages_parsed.{$prod_log.id_lang})}<img title="{$languages_parsed.{$prod_log.id_lang}}" src="{$shopurl}/img/l/{$prod_log.id_lang}.jpg" width="16" height="11">{/if}</td>
		      <td>{$prod_log.date_updated}</td>
		      <td>
		      	<button type="button" href="#" class="btn btn-primary btn-sm" title="{l s='Restore product meta description' mod='chatgptpro'}" data-toggle="modal" data-target="#restoreContent-{$prod_log.id_log}"><i class="material-icons">restore</i></button>

		      	<div
				  class="modal fade restore-product-content"
				  id="restoreContent-{$prod_log.id_log}"
				  tabindex="-1"
				  role="dialog"
				  aria-labelledby="restore-title"
				  aria-hidden="true"
				>
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="restore-title">{l s='Restore product meta description' mod='chatgptpro'}</h5>
				        <button
				          type="button"
				          class="close"
				          data-dismiss="modal"
				          aria-label="Close"
				        >
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">

				      	<input type="hidden" name="id_log" value="{$prod_log.id_log}" class="id_log">
				      	<input type="hidden" name="type" value="{$prod_log.type}" class="type">
				      	<div class="form-group">
						  <label class="form-control-label" for="lang-select">Select language</label>
						  <select class="form-control custom-select lang-select" id="lang-select">
						  	{foreach from=$languages_list item=single_lang}
							    <option value="{$single_lang.id_lang}" {if isset($prod_log.id_lang) && $prod_log.id_lang == $single_lang.id_lang}selected="selected"{else}{if $id_language_selected == $single_lang.id_lang}selected="selected"{/if}{/if} >{$single_lang.name}</option>
							{/foreach}
						  </select>
						</div>

				      </div>
				      <div class="modal-footer">
				        <button
				          type="button"
				          class="btn btn-outline-secondary"
				          data-dismiss="modal"
				        >
				          {l s='Cancel' mod='chatgptpro'}
				        </button>
				        <button type="button" class="btn btn-primary initiateRestore">{l s='Restore product meta description' mod='chatgptpro'}</button>
				      </div>
				    </div>
				  </div>
				</div>

		      </td>
		    </tr>
		    {/if}
		{/foreach}
	  </tbody>
	</table>
  </div>
</div>


<script type="text/javascript">

$(".initiateRestore").click(function(){
	var id_selected_lang = $(this).closest('.restore-product-content').find(".lang-select").val();
	var id_log = $(this).closest('.restore-product-content').find(".id_log").val();
	var type = $(this).closest('.restore-product-content').find(".type").val();

	$.ajax({

		method: "GET",

		url: "{$module_controller|escape:'htmlall':'UTF-8'}",

		data: {

			action: 'initiateProductRestore',

			id_lang: id_selected_lang,

			id_log: id_log,

			type: type,

		}

	}).done(function( msg ) {

		var data = jQuery.parseJSON( msg );

		if (typeof data.date !== 'undefined' && data.status == 'success') {
			$.growl({ title: "Success", message: data.msg });
	    } else if (typeof data.status !== 'undefined' && data.status == 'error') {
			$.growl.error({ message: data.msg });
	    }

  });


});

</script>