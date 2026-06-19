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

body {
 background-color #3399ff
}
.container {
    position:relative;
    padding:0;
    margin:0;

    top:0;
    left:0;

    width: 100%;
    height: 100%;
    
    display: inline-block;
}
h1, h2, h3, h4, h5, h6 {
  font-weight 300
}
</style>


<div class="panel panel-default">

    <div class="panel-heading">{l s='Current module version' mod='chatgptpro'}</div>

    <div class="panel-body">
    	<h2>{l s='Current module version' mod='chatgptpro'}: <span class="badge badge-primary">v{$current_module_version}</span></h2>
    </div>

</div>


<div class="panel panel-default">

    <div class="panel-heading">{l s='OpenAI Integration PRO Module Changelog' mod='chatgptpro'}</div>

    <div class="panel-body">

		<div class="containerx">
		  <div class="containerx">


		    <p class="h1">v1.3.5 <span class="uk-text-muted uk-text-small">October 3 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to set a product number limit on the Mass Product Generation Page, in order to avoid timeouts when processing large number of products</li>
		    </ul>
		    <hr>
		    <br>


		    <p class="h1">v1.3.4 <span class="uk-text-muted uk-text-small">September 7 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added detailed message on failed executions</li>
		    </ul>
		    <hr>
		    <br>


		    <p class="h1">v1.3.3 <span class="uk-text-muted uk-text-small">August 10 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed bug on Top Bar CTA Popup</li>
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed issue on some PS versions when using product shortcodes</li>
		    </ul>
		    <hr>
		    <br>


		    <p class="h1">v1.3.2 <span class="uk-text-muted uk-text-small">July 31 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added the possibility to generate product tags</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new product shortcode {literal}{product_images}{/literal}</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to delete multiple prompt templates at once</li>
		    </ul>
		    <hr>
		    <br>


		    <p class="h1">v1.3.1 <span class="uk-text-muted uk-text-small">July 18 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed bug when disabling the top bar CTA button</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.3.0 <span class="uk-text-muted uk-text-small">July 10 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed bug with new lines</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.9 <span class="uk-text-muted uk-text-small">June 19 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added support for gpt-3.5-turbo-0613 and gpt-3.5-turbo-16k models
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Improved</span>Improved module security</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.8 <span class="uk-text-muted uk-text-small">June 13 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added compatibility with PrestaShop 1.6
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed issue with shortcodes: {literal}{product_features}, {product_feature_values}{/literal}</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.7 <span class="uk-text-muted uk-text-small">May 10 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to store product history edits from the product edit page
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed issue with wrong language updated on the Mass Content Generator Page</li>
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed issue when using category and Brand filter on the Mass Content Generator Page</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.6 <span class="uk-text-muted uk-text-small">May 30 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to target only products in stock on the Mass Content Generator page
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.5 <span class="uk-text-muted uk-text-small">May 10 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to set API Call Delay on Mass Content Generator</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added current module version information on module Changelog Page</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added more pre-defined prompt templates</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed issue with prompt list - display only active prompts</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.4 <span class="uk-text-muted uk-text-small">May 9 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to generate category content using Cron Job</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.3 <span class="uk-text-muted uk-text-small">May 8 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to generate meta title and meta description content on product edit page</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added confirmation messages regarding generating status on product edit page</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Added fix for GPT-4 model</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.2 <span class="uk-text-muted uk-text-small">April 14 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added manufacturer id on history log listing table</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added small improvements</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed issue on Product Mass Content Generator page: undefined getAttribute</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.1 <span class="uk-text-muted uk-text-small">April 10 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added product update history log on edit page</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added product option to restore a past data (title, descriptions, m</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added language field to logs table</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to generate mass content for categories</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added shortcodes to be used on category mass content generate: {literal}{category_name}, {category_description}, {category_parent_name}, {shop_name}, {shop_url}, {shop_language}, {shop_country}, {shop_currency}{/literal}</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to generate content on category add/edit page</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to generate content on CMS add/edit page</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to generate content on Brand add/edit page</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added module changelog dedicated page</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.2.0 <span class="uk-text-muted uk-text-small">April 4 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to update product title via product page edit, mass update form, cron job</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new shortcodes: {literal}{product_brand}, {product_category_description}{/literal}</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added support for gpt-4 model</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new Prompt Templates examples</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to clear History log</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.1.8 <span class="uk-text-muted uk-text-small">March 31 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Fixed issue on Mass Content Generation: filter products that were not updated by the module</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Fixed issue on Cron Job execution: filter products that were not updated by the module</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.1.7 <span class="uk-text-muted uk-text-small">March 30 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added possibility to create Prompt templates containing html code</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.1.6 <span class="uk-text-muted uk-text-small">March 28 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new Cron Job option to filter products using category ID list</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-primary uk-text-small uk-margin-right uk-text-center">Changed</span>Improved the method of filtering products by category</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed console log JavaScript error</li>
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed meta title & meta description saving error</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.1.5 <span class="uk-text-muted uk-text-small">March 27 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		    	<li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed shortcode bug on product edit page</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.1.4 <span class="uk-text-muted uk-text-small">March 26 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added Cron Job features</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added Cron Job options to filter and target products to update</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added Cron Job option to define which fields will be updated when using Cron Jobs</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.1.3 <span class="uk-text-muted uk-text-small">March 25 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added small improvements and bugfixes</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.1.2 <span class="uk-text-muted uk-text-small">March 24 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to Add, edit and Delete Prompt Templates</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added Prompt template list on product edit page, mass content generation page, top bar popup</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new filter options on mass content generation page: Product Add Date From</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new filter options on mass content generation page: Product Add Date To</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new filter options on mass content generation page: Update only (Products that have not been updated with OpenAi content, Products that already have been updated with OpenAi content, Update all)</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.0.9 <span class="uk-text-muted uk-text-small">March 20 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added small improvements and bugfixes</li>
		    </ul>
		    <hr>

		    <p class="h1">v1.0.8 <span class="uk-text-muted uk-text-small">March 15 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added multiple translations: bg, de, es, fr, it, nl, pl, pt, ro, uk</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added small improvements and bugfixes</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added module zip size optimization</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.0.7 <span class="uk-text-muted uk-text-small">March 14 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new shortcodes within the prompt when generating mass content: {literal}{product_description_short}, {product_tags}, {product_reference}, {product_weight}, {shop_name}, {shop_url}, {shop_language}, {shop_country}, {shop_currency}, {product_default_category}, {product_categories}, {product_attributes}, {product_features}, {product_feature_values}{/literal}</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to select specific products on the mass content generation form</li>
		    </ul>
		    <ul class="uk-list">
		    	<li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed small issue on product edit page: Associated cateogries listing</li>
		    	<li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Fixed small issue on product filter for mass content generation</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.0.6 <span class="uk-text-muted uk-text-small">March 13 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added new shortcodes within the prompt when generating mass content: {literal}{product_description}, {product_description_short}{/literal}</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added API KEY password mask on backend are for increased security</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.0.5 <span class="uk-text-muted uk-text-small">March 10 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added dedicated links on backend left menu</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added page for mass content generation</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to mass generate product descriptions, short descriptions, meta title, meta descriptions</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to filter products for which can be generated content using the mass option: category, brand, status, product ids, product reference codes</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to target the updated field (descriptions, short descriptions, meta title, meta descriptions) when generating mass content</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to target the updated field by language when generating mass content</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to use shortcodes({literal}{product_name}, {product_tags}, {product_reference}, {product_weight}{/literal}) within the prompt when generating mass content</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added option to select the behavior of the script if certain fields already have content (Keep existing data and add new generated content at the top, Keep existing data and add new generated content at the end, Keep existing data and skip product, Remove data and add new generated content)</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added debug mode option</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added support for gpt-3.5-turbo model</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added Dedicated page where user can check the usage based on the selected date</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.0.2 <span class="uk-text-muted uk-text-small">February 25 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added Playground feature</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Added CTA Button on backoffice top bar</li>
		    </ul>
		    <hr>
		    <br>

		    <p class="h1">v1.0.0 <span class="uk-text-muted uk-text-small">February 11 2023</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>First module release</li>
		    </ul>
		    <hr>
















		    {*
		    <p class="h1">2.0.0.0 <span class="uk-text-muted uk-text-small">April 7, 2017</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>This is a brief description</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Of a feature that has been added to your product.</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Alternatively, this is a long description of a feature that has been added.</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>This is a brief description</li>
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Of a feature that has been added to your product.</li>
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Alternatively, this is a long description of a feature that has been added.</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-primary uk-text-small uk-margin-right uk-text-center">Changed</span>This is a brief description</li>
		      <li><span class="uk-label uk-label-primary uk-text-small uk-margin-right uk-text-center">Changed</span>Of a feature that has been added to your product.</li>
		      <li><span class="uk-label uk-label-primary uk-text-small uk-margin-right uk-text-center">Changed</span>Alternatively, this is a long description of a feature that has been added.</li>
		    </ul>
		     <ul class="uk-list">
		      <li><span class="uk-label uk-label-warning uk-text-small uk-margin-right uk-text-center">Deprecated</span>This is a brief description</li>
		      <li><span class="uk-label uk-label-warning uk-text-small uk-margin-right uk-text-center">Deprecated</span>Of a feature that has been added to your product.</li>
		      <li><span class="uk-label uk-label-warning uk-text-small uk-margin-right uk-text-center">Deprecated</span>Alternatively, this is a long description of a feature that has been added.</li>
		    </ul>

		    <hr>
		    
		    <p class="h1">1.0.0.0 <span class="uk-text-muted uk-text-small">April 6, 2017</span></p>
		    <!--   Added   -->
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>This is a brief description</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Of a feature that has been added to your product.</li>
		      <li><span class="uk-label uk-label-success uk-text-small uk-margin-right uk-text-center">Added</span>Alternatively, this is a long description of a feature that has been added.</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>This is a brief description</li>
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Of a feature that has been added to your product.</li>
		      <li><span class="uk-label uk-label-danger uk-text-small uk-margin-right uk-text-center">Fixed</span>Alternatively, this is a long description of a feature that has been added.</li>
		    </ul>
		    <ul class="uk-list">
		      <li><span class="uk-label uk-label-primary uk-text-small uk-margin-right uk-text-center">Changed</span>This is a brief description</li>
		      <li><span class="uk-label uk-label-primary uk-text-small uk-margin-right uk-text-center">Changed</span>Of a feature that has been added to your product.</li>
		      <li><span class="uk-label uk-label-primary uk-text-small uk-margin-right uk-text-center">Changed</span>Alternatively, this is a long description of a feature that has been added.</li>
		    </ul>
		     <ul class="uk-list">
		      <li><span class="uk-label uk-label-warning uk-text-small uk-margin-right uk-text-center">Deprecated</span>This is a brief description</li>
		      <li><span class="uk-label uk-label-warning uk-text-small uk-margin-right uk-text-center">Deprecated</span>Of a feature that has been added to your product.</li>
		      <li><span class="uk-label uk-label-warning uk-text-small uk-margin-right uk-text-center">Deprecated</span>Alternatively, this is a long description of a feature that has been added.</li>
		    </ul>

		    <hr>

		    *}
		    
		  </div>
		</div>

    </div>

</div>
