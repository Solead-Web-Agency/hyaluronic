{*
* 2007-2022 Olivier CLEMENCE
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to Olivier CLEMENCE so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize Olivier CLEMENCE for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Olivier CLEMENCE
*  @copyright 2007-2022 Olivier CLEMENCE
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Olivier CLEMENCE
*}


<!-- menu -->

{include file="$module_local_path/views/templates/admin/header.tpl"}


{if isset($tabkey)}
{if isset($confirmation)}<div class="alert alert-success">{$confirmation}</div>{/if}
{if isset($erreur)}<div class="alert alert-danger">{$erreur}</div>{/if}
<form class="form-horizontal indexnow_form tab-optiongroup" action="" method="post" enctype="multipart/form-data" name="indenow_form">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-cog"></i>{l s='Configuration' mod='opartindexnow'}
		</div>
		<div class="form-wrapper">
			<div class="form-group row">

				<div class="col-md-6">
					<label >{l s='Limit the maximum number of submitted urls stored in the database to : ' mod='opartindexnow'}</label>
				</div>
				<div class="col-md-3">
					<input type="number" name="limitlogs" id="limitlogs" {if isset($limitlogs)}value="{$limitlogs}"{/if}/>
				</div>
			</div>
			<div class="form-group row">

				<div class="col-md-6">
					<label >{l s='Global daily send limit : ' mod='opartindexnow'}</label>
				</div>
				<div class="col-md-3">
					<input type="number" name="limitsend" id="limitsend" value="{$limitsend}"/>
				</div>
			</div>
			{if isset($noindex) && $noindex}
			<div class="form-group">
					<label class="col-md-6">
						<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="">
						{l s='Exclude urls in noindex' mod='opartindexnow'}</span>
					</label>
					<div class="col-md-3">
						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="INDEXNOW_NOINDEX_ARGS" id="INDEXNOW_NOINDEX_ARGS_on" value="1" {if $INDEXNOW_NOINDEX_ARGS}checked{/if}>
							<label for="INDEXNOW_NOINDEX_ARGS_on">{l s='Yes' mod='opartindexnow'}</label>
							<input type="radio" name="INDEXNOW_NOINDEX_ARGS" id="INDEXNOW_NOINDEX_ARGS_off" value="0" {if !($INDEXNOW_NOINDEX_ARGS)}checked{/if}>
							<label for="INDEXNOW_NOINDEX_ARGS_off">{l s='No' mod='opartindexnow'}</label>
							<a class="slide-button btn"></a>
							</span>
					</div>
			</div>
			{/if}
			<div class="form-group">
					<label class="col-md-6">
						<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="">
						{l s='Exclude products in visibility nowhere' mod='opartindexnow'}</span>
					</label>
					<div class="col-md-3">
						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="INDEXNOW_VISIBILITY_ARGS" id="INDEXNOW_VISIBILITY_ARGS_on" value="1" {if isset($INDEXNOW_VISIBILITY_ARGS) && $INDEXNOW_VISIBILITY_ARGS}checked{/if}>
							<label for="INDEXNOW_VISIBILITY_ARGS_on">{l s='Yes' mod='opartindexnow'}</label>
							<input type="radio" name="INDEXNOW_VISIBILITY_ARGS" id="INDEXNOW_VISIBILITY_ARGS_off" value="0" {if isset($INDEXNOW_VISIBILITY_ARGS) && !($INDEXNOW_VISIBILITY_ARGS)}checked{/if}>
							<label for="INDEXNOW_VISIBILITY_ARGS_off">{l s='No' mod='opartindexnow'}</label>
							<a class="slide-button btn"></a>
							</span>
					</div>
			</div>
			<div class="form-group">
					<label class="col-md-12">
						<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="">
						{l s='Url cron' mod='opartindexnow'} : {$urlcron}</span>
					</label>
			</div>
		</div>
		<div class="panel-footer">
			<button type="submit" name="indexnowsendconfig" class='btn btn-default pull-right'>{l s='Save' mod='opartindexnow'}</button>
	    </div>
	</div>
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-key"></i>{l s='Security key' mod='opartindexnow'}
		</div>
		<div class="form-wrapper">
			<div class="form-group">
				{if !$key}
				<a href="{$admin_module_url}&action=key&generatekey=1" class="btn btn-primary text-center" title="{l s='save' mod='opartindexnow'}" name="save" id="opartIndexNowGenerateKey">
	                <i class="fas fa-key"></i>
	                {l s='Generate security key' mod='opartindexnow'}
	        	</a>
	        	{else}
	        		<p>{l s='Your security key is : ' mod='opartindexnow'}<strong>{$key}</strong></p>
	        		<a href="{$admin_module_url}&action=key&deletekey=1" class="btn btn-primary text-center" title="{l s='save' mod='opartindexnow'}" name="save">{l s='Delete security key' mod='opartindexnow'}</a>
	        	{/if}
			</div>
		</div>
		<div class="panel-footer">
	    </div>
	</div>
</form>
{/if}

{if isset($exclusion)}
<div class="exlcusion_form tab-optiongroup  row">
	<ps-tabs>
    <ps-tab-nav class="col-md-2 panel transparent">
        <li riot-tag="ps-tab-nav-item" target="tabCategory" class="list-group-item nav-optiongroup {if $selected_element == 1}active selected{/if}" onClick="oesfpLoadTab(1)">{l s='Category' mod='opartindexnow'}</li>
        <li riot-tag="ps-tab-nav-item" target="tabProducts"  class="list-group-item nav-optiongroup {if $selected_element == 2}active selected{/if}" onClick="oesfpLoadTab(2)">{l s='Products' mod='opartindexnow'}</li>
        <li riot-tag="ps-tab-nav-item" target="tabSuppliers"  class="list-group-item nav-optiongroup {if $selected_element == 3}active selected{/if}" onClick="oesfpLoadTab(3)">{l s='Suppliers' mod='opartindexnow'}</li>    
        <li riot-tag="ps-tab-nav-item" target="tabCms"  class="list-group-item nav-optiongroup {if $selected_element == 4}active selected{/if}" onClick="oesfpLoadTab(4)">{l s='Cms' mod='opartindexnow'}</li> 
        <li riot-tag="ps-tab-nav-item" target="tabManufacturers"  class="list-group-item nav-optiongroup {if $selected_element == 5}active selected{/if}" onClick="oesfpLoadTab(5)">{l s='Manufacturers' mod='opartindexnow'}</li>     
    </ps-tab-nav>
    <ps-tab-content class="col-md-10 panel">
    	<div class="panel-heading">
			<i class="icon-ban"></i>{if $selected_element == 1}{l s='Choose categories to exclude' mod='opartindexnow'}{elseif $selected_element == 2}{l s='Choose products to exclude' mod='opartindexnow'}{elseif $selected_element == 3}{l s='Choose suppliers to exclude' mod='opartindexnow'}{elseif $selected_element == 4}{l s='Choose CMS to exclude' mod='opartindexnow'}{elseif $selected_element == 5}{l s='Choose manufacturers to exclude' mod='opartindexnow'}{/if}
		</div>

		<!-- Filter -->
		<div class="panel col-md-5">
			<h3 class="active mb-2">{l s='Filter' mod='opartindexnow'}</h2>
		<form class="form-horizontal row" action="" method="post" enctype="multipart/form-data">
	    	<div class="form-group">
	    		<input type="hidden" name="selected_page" value ="" id="selected_page"/>
				<input type="hidden" name="item_number" value ="{$item_number|intval}" id="item_number"/>
				<input type="hidden" name="current_page" value ="{$selected_page|intval}" id="{$selected_page|intval}"/>
		        
		    </div>
		    <div class="form-group">
		        {if $selected_element==1 || $selected_element == 2}
				  	<label class="col-md-3 labelselect">{l s='Category' mod='opartindexnow'}:</label>
				    <select class="opartindexnowselect col-md-6" name="selected_category" id="selected_category">
				        <option value="-1">---</option>
				        {$select_category_options} {* Can't escape, html given *}
				    </select>
				{/if}
	    	</div>
	    	<div class="form-group">
				  	<label class="col-md-3 labelselect">{l s='Status' mod='opartindexnow'}:</label>
				    <select class="opartindexnowselect col-md-6" name="selected_status" id="selected_name">
				    	<option value="2">{l s='All' mod='opartindexnow'}</option>
				    	<option value="1" {if $exclude_only == 1}selected{/if}>{l s='Exlude' mod='opartindexnow'}</option>
				        <option value="0" {if $noexclude_only == 1}selected{/if}>{l s='No Exlude' mod='opartindexnow'}</option>
				    </select>
	    	</div>
	    	<div class="form-group">
				  	<label class="col-md-3 labelselect">{l s='Visibility' mod='opartindexnow'}:</label>
				    <select class="opartindexnowselect col-md-6" name="selected_active" id="selected_active">
				    	<option value="2">{l s='All' mod='opartindexnow'}</option>
				    	<option value="1" {if $active == 1}selected{/if}>{l s='Active' mod='opartindexnow'}</option>
				        <option value="0" {if $notactive == 1}selected{/if}>{l s='not active' mod='opartindexnow'}</option>
				    </select>
	    	</div>
	    	<div>
	    	<button class="btn btn-default pull-right" title="{l s='Apply filter' mod='opartindexnow'}" name="applyFilter" id="applyFilter">
            <i class="process-icon-cogs"></i>
            {l s='Apply filter' mod='opartindexnow'}
	        </button>
	        <button class="btn btn-default pull-left" title="{l s='Reset filter' mod='opartindexnow'}" direction="left" name="resetFilter" icon="save" ps_value="1" id="resetFilter">
	            <i class="process-icon-reset"></i>
	            {l s='Reset filter' mod='opartindexnow'}
	        </button>
	    	</div>
	    </form>
	</div>

	    <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" style="clear:both;margin-top:2%;" >
	    	<input type="hidden" name="selected_page" value ="" id="selected_page"/>
			<input type="hidden" name="item_number" value ="{$item_number|intval}" id="item_number"/>
			<input type="hidden" name="current_page" value ="{$selected_page|intval}" id="{$selected_page|intval}"/>
	    	<input type="hidden" name="oesfp_element_type" value="{$selected_element}"/>
	    	<input type="hidden" name="selected_category" value="{$id_selected_category}"/>
	    	<input type="hidden" name="selected_status" value="{if $exclude_only == 1}1{elseif  $noexclude_only == 1 } 0 {else} 2 {/if}"/>
	    	<!-- Content -->
	        <div id="tabCategory" class="tab-pane{if $selected_element == 1} active{/if}">{if $selected_element == 1}{include file="$module_local_path/views/templates/admin/category.tpl"}{/if}</div>
	        <div id="tabProducts" class="tab-pane{if $selected_element == 2} active{/if}">{if $selected_element == 2}{include file="$module_local_path/views/templates/admin/product.tpl"}{/if}</div>
	        <div id="tabSuppliers" class="tab-pane{if $selected_element == 3} active{/if}">{if $selected_element == 3}{include file="$module_local_path/views/templates/admin/suppliers.tpl"}{/if}</div>        
	        <div id="tabCms" class="tab-pane{if $selected_element == 4} active{/if}">{if $selected_element == 4}{include file="$module_local_path/views/templates/admin/cms.tpl"}{/if}</div>
	        <div id="tabManufacturers" class="tab-pane{if $selected_element == 5} active{/if}">{if $selected_element == 5}{include file="$module_local_path/views/templates/admin/manufacturers.tpl"}{/if}</div>          
	        <div class="panel-footer mb-1">
	        	<div class="form-group row">
	        		
	        	<label class="oparindexnow col-lg-3 col-md-2 labelselect text-left paginationindexnow">{l s='Items per page' mod='opartindexnow'}:</label>
	        			        <select class="col-md-1" name="item_number" onChange="oniGoToItemNumber(this.value)">
		            <option value="10" {if $item_number == 10}selected="selected"{/if}>10</option>
		            <option value="25"   {if $item_number == 25}selected="selected"{/if}>25</option>
		            <option value="50"  {if $item_number == 50}selected="selected"{/if}>50</option>
		            <option value="100"   {if $item_number == 100}selected="selected"{/if}>100</option>
		            <option value="200"   {if $item_number == 200}selected="selected"{/if}>200</option>
		            <option value="500"   {if $item_number == 500}selected="selected"{/if}>500</option>
		            <option value="750"  {if $item_number == 750}selected="selected"{/if}>750</option>
		            <option value="1000"  {if $item_number == 1000}selected="selected"{/if}>1000</option>
		            <option value="1500"   {if $item_number == 1500}selected="selected"{/if}>1500</option>
		        </select>
		    	</div>
		    	<label>{l s='page' mod='opartindexnow'} : </label>
					{assign var="foo" value="0"}
					{while $foo < (ceil($totalItem/$limit))}        
					    {assign var="foo" value=$foo+1}
					    {if $foo!=$selected_page}
					        <a href="#" onclick="oniGoToPage('{$foo|intval}'); return false;">{$foo|intval}</a> 
					    {else}
					        {$foo|intval}
					    {/if}       
					    |
					{/while}
	        	
	    	</div>   
	    </form>
    </ps-tab-content>
</ps-tabs>
</div>
{/if}

{if isset($urlssubmitted)}
<div class="soumission_form tab-optiongroup  row">
	 <form class="form-horizontal" action="" method="post">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-laptop"></i>{l s='Submitted URLs' mod='opartindexnow'}
		</div>
		<div class="form-wrapper">
			
			{include file="$module_local_path/views/templates/admin/submitted.tpl"}
		</div>
		<div class="panel-footer">
			<a href="{$admin_module_url}&action=soumission&deletelist=1" class="btn btn-primary pull-left" title="{l s='Empty the list' mod='opartindexnow'}" onClick='loaderindexnow()'>{l s='Empty the list' mod='opartindexnow'}</a>
			<button type="submit" class="btn btn-primary pull-right" name="submit_url">{l s='Resend urls' mod='opartindexnow'}</button>
	    </div>
	</div>
	</form>
	<div  class="panel">
		<div class="panel-heading">
			<i class="icon-question"></i> {l s='Status explanation' mod='opartindexnow'}
		</div>
		<table class="table  table-striped">
			 <thead>
			    <tr>
			      <th scope="col">{l s='HTTP Code' mod='opartindexnow'}</th>
			      <th scope="col">{l s='Response' mod='opartindexnow'}</th>
			      <th scope="col">{l s='Reasons' mod='opartindexnow'}</th>
			    </tr>
			  </thead>
			  <tbody>
			    <tr>
			      <th scope="row">200</th>
			      <td>Ok</td>
			      <td>{l s='URL submitted successfully' mod='opartindexnow'}</td>
			    </tr>
			     <tr>
			      <th scope="row">202</th>
			      <td>Ok</td>
			      <td>{l s='URL submitted successfully' mod='opartindexnow'}</td>
			    </tr>
			    <tr>
			      <th scope="row">400</th>
			      <td>{l s='Bad request' mod='opartindexnow'}</td>
			      <td>{l s='Invalid format' mod='opartindexnow'}</td>
			    </tr>
			    <tr>
			      <th scope="row">403</th>
			      <td>{l s='Forbidden' mod='opartindexnow'}</td>
			      <td>{l s='In case of key not valid (e.g. key not found, file found but key not in the file)' mod='opartindexnow'}</td>
			    </tr>
			      <tr>
			      <th scope="row">422</th>
			      <td>{l s='Unprocessable Entity' mod='opartindexnow'}</td>
			      <td>{l s='In case of URLs don\'t belong to the host or the key is not matching the schema in the protocol' mod='opartindexnow'}</td>
			    </tr>
			     </tr>
			      <tr>
			      <th scope="row">429</th>
			      <td>{l s='Too Many Requests' mod='opartindexnow'}</td>
			      <td>{l s='Too Many Requests (potential Spam)' mod='opartindexnow'}</td>
			    </tr>
			  </tbody>
		</table>
	<div>
</div>
{/if}

{if isset($help)}
<div class="help_form tab-optiongroup  row">
	 <form class="form-horizontal" action="" method="post">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-question-sign"></i> {l s='Help' mod='opartindexnow'}
		</div>
		<div class="form-wrapper">
			<div>
				<h2>{l s='Module overview' mod='opartindexnow'}</h2>
				<iframe width="560" height="315" src="https://www.youtube.com/embed/DaWfy-PY6Vg" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
			<div>
				<h2>{l s='FAQ' mod='opartindexnow'}</h2>
				<p><strong>{l s='Is indexnow technology used by Google ?' mod='opartindexnow'}</strong></p>
				<p>{l s='IndexNow is a technology invented by Bing and it is a non-proprietary technology.' mod='opartindexnow'}</p>
				<p>{l s='This means that it is free that everyone can use it and participate in its improvement.' mod='opartindexnow'}</p>
				<p>{l s='Google can therefore use it without any problem.' mod='opartindexnow'}</p>
				<p>{l s='But of course, from a marketing point of view, Google cannot rush into a technology that Bing (its main competitor) is at the initiative of.' mod='opartindexnow'}</p>
				<p>{l s='C’est pour cette raison que, pour l’instant, Google ne s’appuis pas encore sur IndexNow et continue d’utiliser l’ancienne technique du Crawl pour découvrir les pages.' mod='opartindexnow'}</p>
				<p>{l s='Yet we are convinced that Google will have no choice and will soon have to use IndexNow' mod='opartindexnow'}</p>
				<hr/>
				<p><strong>{l s='The indexnow module replaces the noindex?' mod='opartindexnow'}</strong></p>
				<p>{l s='The indexnow and noindex module have 2 different purposes.' mod='opartindexnow'}</p>
				<p>{l s='The indexnow module notifies search engines that use this technology that a page has been added, modified or deleted. This allows the pages to be indexed more quickly.' mod='opartindexnow'}</p>
				<p>{l s='With regard to noindex, the module allows the opposite, to deindex the pages which have little interest level referencing to avoid lowering the overall score of your site level seo.' mod='opartindexnow'}</p>
			</div>
		</div>
	</div>
</form>
{/if}


<script type="text/javascript">
    var admin_module_url = '{$admin_module_url|escape:'javascript':'UTF-8'}';
</script>