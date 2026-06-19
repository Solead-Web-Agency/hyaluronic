{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    SeoSA <885588@bk.ru>
* @copyright 2012-2020 SeoSA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<div class="col-xs-12">

	<div data-combinations>

		<label>
			<span class="btn btn-success btn-xs combinations-btn hidden-md hidden-lg">{l s='Combinations' mod='masseditproduct'}</span>
		</label>

		<div class="selector_item_bg">
		</div>

		<div class="selector_item select_combinations">

			<div class="">
				<a class="check_all_combinations btn btn-default button" href="#">
					<i class="icon-check-sign"></i>
                    {l s='Check all' mod='masseditproduct'}
				</a>
				<a class="uncheck_all_combinations btn btn-default button" href="#">
					<i class="icon-check-empty"></i>
                    {l s='Uncheck all' mod='masseditproduct'}
				</a>
				<a class="invert_all_combinations btn btn-default button" href="#">
					<i class="icon-check-empty"></i>
                    {l s='Invert all' mod='masseditproduct'}
				</a>
				<a class="check_attribute_combinations btn btn-default button" href="#">
					<i class="icon-list"></i>
                    {l s='Select attribute' mod='masseditproduct'}
				</a>
				<a class="close_combinations btn btn-danger button float-right" title="{l s='Сlose' mod='masseditproduct'}" href="#">
					<i class="icon-close"></i>
				</a>
			</div>

			<div id="attributes_select">
				<ul class="selected-attr list-group">
				</ul>
				<div class="attribute_group_block">
					<div>
						<label class="margin-right">{l s='Attribute' mod='masseditproduct'}</label>
						<select class="select_attribute fixed-width-md margin-right-lg custom-select">
							<option value="0">--</option>
                            {foreach from=$attribures_groups item=attribute_group}
								<option value="{$attribute_group.id_attribute_group|intval}">{$attribute_group.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
						</select>

						<span class="select_attribute_value_block">
							<label class="margin-right">{l s='Value' mod='masseditproduct'}</label>
							<select class="select_attribute_value fixed-width-md margin-right custom-select"></select>
						</span>

					</div>
				</div>
				<a class="more_select_combinations btn btn-default button" href="#">
                    {l s='Add attributes' mod='masseditproduct'}
				</a>
				<a class="start_select_combinations btn btn-success button" href="#">
                    {l s='Select' mod='masseditproduct'}
				</a>
			</div>
		</div>

		<div class="tn-box_combinations tn-box mv_succes">
			<p class="message_mv_content">{l s='Attribute added' mod='masseditproduct'}</p>
			<div class="tn-progress"></div>
		</div>
		<div class="tn-box_combinations tn-box mv_error">
			<p class="message_mv_content">{l s='No combinations with these attributes' mod='masseditproduct'}</p>
			<div class="tn-progress"></div>
		</div>
		<div class="tn-box_more_select_combinations tn-box mv_error">
			<p class="message_mv_content">{l s='Attribute not set' mod='masseditproduct'}</p>
			<div class="tn-progress"></div>
		</div>
	</div>

	<table class="table-new">
		<thead>
		<tr class="table_head">
			<th>
				<span class="title_box" data-orderby="id_product">{l s='ID' mod='masseditproduct'}
					<a href="#" data-orderway="DESC">
						<i class="icon-caret-down"></i>
					</a>
					<a href="#"  data-orderway="ASC">
						<i class="icon-caret-up"></i>
					</a>
				</span>
			</th>
			<th>{l s='Image' mod='masseditproduct'}</th>
			<th>
				<span class="title_box" data-orderby="name">{l s='Name' mod='masseditproduct'}
					<a href="#" data-orderway="DESC">
						<i class="icon-caret-down"></i>
					</a>
					<a href="#"  data-orderway="ASC">
						<i class="icon-caret-up"></i>
					</a>
				</span>
			</th>
			<th>
				<span class="title_box" data-orderby="reference">{l s='Reference' mod='masseditproduct'}
					<a href="#" data-orderway="DESC">
						<i class="icon-caret-down"></i>
					</a>
					<a href="#"  data-orderway="ASC">
						<i class="icon-caret-up"></i>
					</a>
				</span>
			</th>
			<th>
				<span class="title_box" data-orderby="category">{l s='Category default' mod='masseditproduct'}
					<a href="#" data-orderway="DESC">
						<i class="icon-caret-down"></i>
					</a>
					<a href="#"  data-orderway="ASC">
						<i class="icon-caret-up"></i>
					</a>
				</span>
			</th>
			<th>
				<span class="title_box" data-orderby="price">{l s='Price' mod='masseditproduct'}
					<a href="#" data-orderway="DESC">
						<i class="icon-caret-down"></i>
					</a>
					<a href="#"  data-orderway="ASC">
						<i class="icon-caret-up"></i>
					</a>
				</span>
			</th>
			<th>{l s='Price final' mod='masseditproduct'}</th>
			<th>
				<span class="title_box" data-orderby="manufacturer">{l s='Manufacturer' mod='masseditproduct'}
					<a href="#" data-orderway="DESC">
						<i class="icon-caret-down"></i>
					</a>
					<a href="#"  data-orderway="ASC">
						<i class="icon-caret-up"></i>
					</a>
				</span>
			</th>
			<th>{l s='Supplier' mod='masseditproduct'}</th>
      {*<th>{l s='Carrier' mod='masseditproduct'}</th>*}
			<th>
				<span class="title_box" data-orderby="quantity">{l s='Quantity' mod='masseditproduct'}
					<a href="#" data-orderway="DESC">
						<i class="icon-caret-down"></i>
					</a>
					<a href="#"  data-orderway="ASC">
						<i class="icon-caret-up"></i>
					</a>
				</span>
			</th>
			<th>{l s='Stock management' mod='masseditproduct'}</th>
			<th>{l s='Active' mod='masseditproduct'}</th>
			<th data-combinations>
				{l s='Combinations' mod='masseditproduct'} <a class="btn btn-success btn-xs combinations-btn" href="#">{l s='Select' mod='masseditproduct'}</a>
			</th>
		</tr>
		</thead>
		<tbody>
        {if isset($products) && (!isset($without_product) || (isset($without_product) && !$without_product))}
            {if count($products)}
                {foreach from=$products item=product}
                    {include file="./product_line.tpl" product=$product}
                {/foreach}
            {else}
				<tr class="no_products text-center">
					<td colspan="11">{l s='No products' mod='masseditproduct'}</td>
				</tr>
            {/if}
        {/if}
		</tbody>
	</table>
    {if isset($products) && (!isset($without_product) || (isset($without_product) && !$without_product)) && isset($p)}
		<div class="pagination clearfix">
            {if $start!=$stop}
				<ul class="pagination">
                    {if $p != 1}
                        {assign var='p_previous' value=$p-1}
						<li class="pagination_previous">
							<a onclick="setPage('{$p_previous|intval}'); return false;" href="#">
								<i class="icon-chevron-left"></i> <b>{l s='Previous' mod='masseditproduct'}</b>
							</a>
						</li>
                    {else}
						<li class="disabled pagination_previous">
						<span>
							<i class="icon-chevron-left"></i> <b>{l s='Previous' mod='masseditproduct'}</b>
						</span>
						</li>
                    {/if}
                    {if $start==3}
						<li>
							<a onclick="setPage('1'); return false;" href="#"">
							<span>1</span>
							</a>
						</li>
						<li>
							<a onclick="setPage('2'); return false;" href="#">
								<span>2</span>
							</a>
						</li>
                    {/if}
                    {if $start==2}
						<li>
							<a onclick="setPage('1'); return false;" href="#">
								<span>1</span>
							</a>
						</li>
                    {/if}
                    {if $start>3}
						<li>
							<a onclick="setPage('1'); return false;" href="#">
								<span>1</span>
							</a>
						</li>
						<li class="truncate">
						<span>
							<span>...</span>
						</span>
						</li>
                    {/if}
                    {section name=pagination start=$start loop=$stop+1 step=1}
                        {if $p == $smarty.section.pagination.index}
							<li class="active current">
							<span>
								<span>{$p|escape:'html':'UTF-8'}</span>
							</span>
							</li>
                        {else}
							<li>
								<a onclick="setPage('{$smarty.section.pagination.index|intval}'); return false;" href="#">
									<span>{$smarty.section.pagination.index|escape:'html':'UTF-8'}</span>
								</a>
							</li>
                        {/if}
                    {/section}
                    {if $pages_nb>$stop+2}
						<li class="truncate">
						<span>
							<span>...</span>
						</span>
						</li>
						<li>
							<a onclick="setPage('{$pages_nb|intval}'); return false;" href="#">
								<span>{$pages_nb|intval}</span>
							</a>
						</li>
                    {/if}
                    {if $pages_nb==$stop+1}
						<li>
							<a onclick="setPage('{$pages_nb|intval}'); return false;" href="#">
								<span>{$pages_nb|intval}</span>
							</a>
						</li>
                    {/if}
                    {if $pages_nb==$stop+2}
						<li>
							<a onclick="setPage('{$pages_nb-1|intval}'); return false;" href="#">
								<span>{$pages_nb-1|intval}</span>
							</a>
						</li>
						<li>
							<a onclick="setPage('{$pages_nb|intval}'); return false;" href="#">
								<span>{$pages_nb|intval}</span>
							</a>
						</li>
                    {/if}
                    {if $pages_nb > 1 AND $p != $pages_nb}
                        {assign var='p_next' value=$p+1}
						<li class="pagination_next">
							<a onclick="setPage('{$p_next|intval}'); return false;" href="#">
								<b>{l s='Next' mod='masseditproduct'}</b> <i class="icon-chevron-right"></i>
							</a>
						</li>
                    {else}
						<li class="disabled pagination_next">
						<span>
							<b>{l s='Next' mod='masseditproduct'}</b> <i class="icon-chevron-right"></i>
						</span>
						</li>
                    {/if}
				</ul>
            {/if}
		</div>
    {/if}
</div>