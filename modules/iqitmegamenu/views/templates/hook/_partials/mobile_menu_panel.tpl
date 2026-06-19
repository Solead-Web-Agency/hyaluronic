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
				class="mobile-menu__submenu mobile-menu__submenu--panel js-mobile-menu__submenu position-absolute w-100 h-100  m-0 mm-panel__scroller px-4 py-4">{/if}
				{foreach from=$nodes item=node}
					{if isset($node.title)}
						<li
							class="mobile-menu__tab  d-flex align-items-center {if isset($node.children)}  mobile-menu__tab--has-submenu js-mobile-menu__tab--has-submenu{/if} js-mobile-menu__tab">
							<a {if isset($node.href)} href="{$node.href}" {/if} class="flex-fill mobile-menu__link
								{if isset($iqitTheme.mm_expand_trigger) && $iqitTheme.mm_expand_trigger == 'entire-link'}	
									{if isset($node.children)} js-mobile-menu__link--has-submenu {/if}
								{/if}
								">
								<span class="js-mobile-menu__tab-title">{$node.title}</span>
							</a>
							{if isset($node.children)}<span class="mobile-menu__arrow js-mobile-menu__link--has-submenu"><i
									class="fa fa-angle-right expand-icon" aria-hidden="true"></i></span>
									
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