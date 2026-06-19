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



{foreach $horizontal_menu as $tab}
	<li
		class="mobile-menu__tab mobile-menu__tab--id-{$tab.id_tab} {if !empty($tab.submenu_content)} mobile-menu__tab--has-submenu js-mobile-menu__tab--has-submenu{/if} js-mobile-menu__tab">
		<a class="text-reset mobile-menu__link {if !empty($tab.submenu_content)}js-mobile-menu__link--has-submenu{/if} " {if ($tab.url)}href="{$tab.url}" {/if}
			{if $tab.new_window}target="_blank" rel="noopener noreferrer" {/if}>
			<span class="js-mobile-menu__tab-title">{$tab.title|replace:'/n':'<br />' nofilter}</span>

			{if !empty($tab.label)}
				<span class="mobile-menu__legend mobile-menu__legend--id-{$tab.id_tab} ">{$tab.label}</span>
			{/if}
		</a>

		{if !empty($tab.submenu_content)}
			<div class="mobile-menu__submenu js-mobile-menu__submenu">
				{foreach $tab.submenu_content as $element}
					{include file="module:iqitmegamenu/views/templates/hook/_partials/submenu_content_mobile.tpl" node=$element}
				{/foreach}
			</div>
		{/if}
	</li>
{/foreach}
