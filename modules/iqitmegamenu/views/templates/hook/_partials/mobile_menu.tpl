{*
* 2007-2017 IQIT-COMMERCE.COM
*
* NOTICE OF LICENSE
*
*  @author    IQIT-COMMERCE.COM <support@iqit-commerce.com>
*  @copyright 2007-2017 IQIT-COMMERCE.COM
*  @license   GNU General Public License version 2
*
* You can not resell or redistribute this software.
*
*}

{function name="mobile_links" nodes=[] first=false}
	{strip}
		{if $nodes|count}
			{if !$first}<ul
				class="mobile-menu__submenu  mobile-menu__submenu--accordion js-mobile-menu__submenu">{/if}
				{foreach from=$nodes item=node}
					{if isset($node.title)}
						<li
							class="mobile-menu__tab  d-flex flex-wrap js-mobile-menu__tab">
								<a {if isset($node.href)} href="{$node.href}" {/if} class="flex-fill mobile-menu__link 	
								{if isset($iqitTheme.mm_expand_trigger) && $iqitTheme.mm_expand_trigger == 'entire-link'}{if isset($node.children)} js-mobile-menu__link-accordion--has-submenu {/if} {/if}">{$node.title}</a>
							
								{if isset($node.children)}
											<span class="mobile-menu__arrow js-mobile-menu__link-accordion--has-submenu">
												<i class="fa fa-angle-down mobile-menu__expand-icon" aria-hidden="true"></i>
												<i class="fa fa-angle-up mobile-menu__close-icon" aria-hidden="true"></i>
											</span>
											<div class="mobile-menu__tab-row-break"></div>
									{mobile_links nodes=$node.children first=false}
								{/if}
							</li>
						{/if}
					{/foreach}
					{if !$first}
				</ul>{/if}
			{/if}
		{/strip}
	{/function}


	{if isset($menu)}
		{mobile_links nodes=$menu first=true}
	{/if}