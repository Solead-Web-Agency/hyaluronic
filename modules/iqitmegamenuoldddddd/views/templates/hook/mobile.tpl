	<div id="_desktop_iqitmegamenu-mobile">
		<div id="iqitmegamenu-mobile"
			class="mobile-menu js-mobile-menu {if $iqitTheme.mm_type == 'push'} h-100 {/if} d-flex flex-column">

			<div class="mm-panel__header  mobile-menu__header-wrapper px-2 py-2">
				<div class="mobile-menu__header js-mobile-menu__header">

					<button type="button" class="mobile-menu__back-btn js-mobile-menu__back-btn btn">
						<span aria-hidden="true" class="fa fa-angle-left  align-middle mr-2"></span>
						<span class="mobile-menu__title js-mobile-menu__title paragraph-p1 align-middle"></span>
					</button>
					
				</div>
				<button type="button" class="btn btn-icon mobile-menu__close js-mobile-menu__close" aria-label="Close"
					data-toggle="dropdown">
					<span aria-hidden="true" class="fa fa-times"></span>
				</button>
			</div>

			<div class="position-relative mobile-menu__content flex-grow-1 mx-c16 my-c24 ">
				<ul
					class="{if $iqitTheme.mm_type == 'push'}position-absolute h-100 {/if} w-100  m-0 mm-panel__scroller mobile-menu__scroller px-4 py-4">
					<li class="mobile-menu__above-content">{hook h='displayAboveMobileMenu'}</li>
						{if isset($iqitTheme.mm_content) && $iqitTheme.mm_content == 'accordion'}
							{include file="module:iqitmegamenu/views/templates/hook/_partials/mobile_menu.tpl" menu=$mobile_menu}
						{elseif $iqitTheme.mm_content == 'panel'}
							{include file="module:iqitmegamenu/views/templates/hook/_partials/mobile_menu_panel.tpl" menu=$mobile_menu}
						{else}

		
								{foreach $mobile_menu as $tab}
									<li
										class="d-flex align-items-center mobile-menu__tab mobile-menu__tab--id-{$tab.id_tab} {if !empty($tab.submenu_content)} mobile-menu__tab--has-submenu js-mobile-menu__tab--has-submenu{/if} js-mobile-menu__tab">
										<a class="flex-fill mobile-menu__link 
										{if isset($iqitTheme.mm_expand_trigger) && $iqitTheme.mm_expand_trigger == 'entire-link'}		
											{if !empty($tab.submenu_content)}js-mobile-menu__link--has-submenu{/if} 
										{/if}
										" {if ($tab.url)}href="{$tab.url}" {/if} {if $tab.new_window}target="_blank" rel="noopener noreferrer" {/if}>
											{if $tab.icon_type && !empty($tab.icon_class)} 
												<i class="icon fa {$tab.icon_class} mobile-menu__tab-icon"></i>
											{/if}
											
											{if !$tab.icon_type && !empty($tab.icon)}
												<img src="{$tab.icon}" alt="{$tab.title}" class="mobile-menu__tab-icon mobile-menu__tab-icon--img" />
											{/if}

											<span class="js-mobile-menu__tab-title">{$tab.title|replace:'/n':'<br />' nofilter}</span>

											{if !empty($tab.label)}
												<span class="mobile-menu__legend mobile-menu__legend--id-{$tab.id_tab} ">
												{if !empty($tab.legend_icon)} 
													<i class="icon fa {$tab.legend_icon}"></i>
												{/if} 
												{$tab.label}
												</span>
											{/if}
										</a>
										{if !empty($tab.submenu_content)}
											<span class="mobile-menu__arrow js-mobile-menu__link--has-submenu">
												<i class="fa fa-angle-right expand-icon" aria-hidden="true"></i>
											</span>
										{/if}

										{if isset($tab.submenu_content_tabs)}
											<div class="mobile-menu__submenu mobile-menu__submenu--panel px-4 py-4 js-mobile-menu__submenu">
												<ul>
													{foreach $tab.submenu_content_tabs as $innertab name=innertabscontent}

														<li
															class="d-flex align-items-center mobile-menu__tab mobile-menu__tab--id-{$innertab->id_tab} {if !empty($innertab->submenu_content)} mobile-menu__tab--has-submenu js-mobile-menu__tab--has-submenu{/if} js-mobile-menu__tab">
															<a class="flex-fill mobile-menu__link {if isset($iqitTheme.mm_expand_trigger) && $iqitTheme.mm_expand_trigger == 'entire-link'}{if !empty($innertab->submenu_content)}js-mobile-menu__link--has-submenu{/if}{/if} "
																{if ($innertab->url)}href="{$innertab->url}" {/if}
																{if $innertab->new_window}target="_blank" rel="noopener noreferrer" {/if}>

																	{if $innertab->icon_type && !empty($innertab->icon_class)} 
																		<i class="icon fa {$innertab->icon_class} mobile-menu__tab-icon"></i>
																	{/if}
																	
																	{if !$innertab->icon_type && !empty($innertab->icon)}
																		<img src="{$innertab->icon}" alt="{$innertab->title}" class="mobile-menu__tab-icon mobile-menu__tab-icon--img" />
																	{/if}
																
																	<span class="js-mobile-menu__tab-title">{$innertab->title|replace:'/n':'<br />' nofilter}</span>

																{if !empty($innertab->label)}
																	<span
																		class="mobile-menu__legend mobile-menu__legend--id-{$innertab->id_tab}"> {if !empty($innertab->legend_icon)} <i
																		class="icon fa {$innertab->legend_icon}"></i>{/if} {$innertab->label}</span>
																{/if}
															</a>
															{if !empty($innertab->submenu_content)}
																<span class="mobile-menu__arrow js-mobile-menu__link--has-submenu">
																	<i class="fa fa-angle-right expand-icon" aria-hidden="true"></i>
																</span>
															{/if}

															{if !empty($innertab->submenu_content)}
																{foreach $innertab->submenu_content as $element}
																	<div class="mobile-menu__submenu mobile-menu__submenu--panel px-4 py-4 js-mobile-menu__submenu">
																		{include file="module:iqitmegamenu/views/templates/hook/_partials/submenu_content_mobile.tpl" node=$element}
																	</div>
																{/foreach}
															{/if}

														</li>

													{/foreach}
													<ul>
											</div>
										{else}

											{if !empty($tab.submenu_content)}
												<div class="mobile-menu__submenu mobile-menu__submenu--panel px-4 py-4 js-mobile-menu__submenu">
													{foreach $tab.submenu_content as $element}
														{include file="module:iqitmegamenu/views/templates/hook/_partials/submenu_content_mobile.tpl" node=$element}
													{/foreach}
												</div>
											{/if}
										{/if}
									</li>
								{/foreach}
					{/if}
					<li class="mobile-menu__below-content"> {hook h='displayBelowMobileMenu'}</li>
				</ul>
			</div>

			<div class="js-top-menu-bottom mobile-menu__footer justify-content-between px-4 py-4">
				

			<div class="d-flex align-items-start mobile-menu__language-currency js-mobile-menu__language-currency">

			{hook h="litespeedEsiBegin" m="ps_languageselector" field="widget_block" tpl="module:ps_languageselector/ps_languageselector-mobile-menu.tpl"}
				{widget_block name="ps_languageselector"}
					{include 'module:ps_languageselector/ps_languageselector-mobile-menu.tpl'}
				{/widget_block}
			{hook h="litespeedEsiEnd"}

			{hook h="litespeedEsiBegin" m="ps_currencyselector" field="widget_block" tpl="module:ps_currencyselector/ps_currencyselector-mobile-menu.tpl"}
				{widget_block name="ps_currencyselector"}
					{include 'module:ps_currencyselector/ps_currencyselector-mobile-menu.tpl'}
				{/widget_block}
			{hook h="litespeedEsiEnd"}

			</div>


			<div class="mobile-menu__user">
			<a href="{$urls.pages.my_account}" class="text-reset"><i class="fa fa-user" aria-hidden="true"></i>
				{hook h="litespeedEsiBegin" m="ps_customersignin" field="widget_block" tpl="module:ps_customersignin/ps_customersignin-mobile-menu.tpl"}
				{widget_block name="ps_customersignin"}
					{include 'module:ps_customersignin/ps_customersignin-mobile-menu.tpl'}
				{/widget_block}
				{hook h="litespeedEsiEnd"}
			</a>
			</div>


			</div>
		</div>
	</div>