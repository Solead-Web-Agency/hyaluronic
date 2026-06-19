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

{function name="categories_links" nodes=[] level=1}
    {strip}
        <ul
            class="mobile-menu__links-list {if $level==1}mobile-menu__links-list--lvl-1 {elseif $level==2}mobile-menu__links-list--lvl-hidden mobile-menu__links-list--lvl-2{elseif $level==3}mobile-menu__links-list--lvl-hidden mobile-menu__links-list-lvl--3{/if}">
            {foreach $categories as $category}
                {if isset($category.title)}
                    <li class="mobile-menu__links-list-li" >
                            <a href="{$category.href}" class="text-reset">{$category.title}</a>
                           
                            {if isset($category.children)}
                                <span class="cursor-pointer ml-3 p-0 mobile-menu__subcat-expander js-mobile-menu__subcat-expander"><i class="fa fa-angle-down mobile-menu__subcat-expander-icon-down"></i><i class="fa fa-angle-up mobile-menu__subcat-expander-icon-up"></i></span>
                                {categories_links categories=$category.children level=$level+1}
                            {/if}
                    </li>
                {/if}
            {/foreach}
        </ul>
    {/strip}
{/function}



{if $node.type==1}
    <div class="mobile-menu__row  mobile-menu__row--id-{$node.elementId}">

    {elseif $node.type==2}
        <div
            class="mobile-menu__column mobile-menu__column--id-{$node.elementId}">
        {/if}
        {if $node.type==2}

            {if isset($node.content_s.title)}
             {if isset($node.content_s.href)} <a href="{$node.content_s.href}" class="mobile-menu__column-title"> {else} <span class="mobile-menu__column-title"> {/if}
                    {$node.content_s.title}
                {if isset($node.content_s.href)} </a> {else} </span> {/if}
            {/if}

            {if $node.contentType==1}

                {if isset($node.content.ids) && $node.content.ids}
                    {*HTML CONTENT*} {$node.content.ids nofilter}
                {/if}

            {elseif $node.contentType==2}

                {if isset($node.content.ids)}

                    {if $node.content.treep}
                        {foreach from=$node.content.ids item=category}
                            {if isset($category.title)}
                                <div class="cbp-category-link-w mobile-menu__column-categories">
                                    <a href="{$category.href}" class="mobile-menu__column-title">{$category.title}</a>
                                    {if isset($category.thumb) && $category.thumb != ''}<a href="{$category.href}"
                                            class="mobile-menu__category-image"><img class="img-fluid" src="{$category.thumb}" loading="lazy"
                                            alt="{$category.title}" /></a>{/if}
                                    {if isset($category.children)}
                                        {categories_links categories=$category.children level=1}
                                    {/if}
                                </div>

                            {/if}
                        {/foreach}

                    {else}
                        <ul class="mobile-menu__links-list">
                            {foreach from=$node.content.ids item=category}
                                {if isset($category.title)}
                                    <li class="mobile-menu__links-list {if isset($category.children)}cbp-hrsub-haslevel2{/if}" >
                                        <div class="cbp-category-link-w">
                                            <a href="{$category.href}" class="text-reset">{$category.title}</a>

                                            {if isset($category.children)}
                                                {categories_links categories=$category.children level=2}
                                            {/if}
                                        </div>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    {/if}
                {/if}

            {elseif $node.contentType==3}

                {if isset($node.content.ids)}
                    <ul
                        class="mobile-menu__links-list">
                        {foreach from=$node.content.ids item=va_link}
                            {if isset($va_link.href) && isset($va_link.title) && $va_link.href != '' && $va_link.title != ''}
                                <li class="mobile-menu__links-list-li" ><a href="{$va_link.href}" class="text-reset" {if isset($va_link.new_window) && $va_link.new_window}target="_blank"
                                        rel="noopener noreferrer" {/if}>{$va_link.title}</a>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                {/if}

            {elseif $node.contentType==4}
                    {if isset($node.content.ids)}
                            {include file="module:iqitmegamenu/views/templates/hook/_partials/products_mobile.tpl" products=$node.content.ids}
                    {/if}
            {elseif $node.contentType==5}
                    <div class="mobile-menu__brands-row row small-gutters">
                        {foreach from=$node.content.ids item=manufacturer}
                            {assign var="myfile" value="img/m/{$manufacturer}-small_default.jpg"}
                            {assign var="manufacturerName" value=Manufacturer::getNameById($manufacturer)}
                            {if $manufacturerName !=''}
                            {if file_exists($myfile)}
                                <div class="col col-4 p-2">
                                    <a href="{Context::getContext()->link->getManufacturerLink($manufacturer)}"
                                       title="{$manufacturerName}">
                                        <img src="{$urls.img_manu_url}{$manufacturer}-small_default.jpg"
                                             loading="lazy"
                                             class="img-fluid mobile-menu__brand-img " {if isset($manufacturerSize)} width="{$manufacturerSize.width}" height="{$manufacturerSize.height}"{/if}
                                             alt="{$manufacturerName}"/>
                                    </a>
                                </div>
                            {/if}
                                {/if}
                        {/foreach}
                    </div>
            {elseif $node.contentType==6}

                {if isset($node.content.source)}
                    {if !isset($node.content.absolute)}
                        {if isset($node.content.href)}<a href="{$node.content.href}">{/if}
                            <img src="{$node.content.source}" loading="lazy" class="img-fluid mobile-menu__banner-image"
                                {if isset($node.content.size)}
                                    {if isset($node.content.size.w)} width="{$node.content.size.w}" {/if}
                                    {if isset($node.content.size.h)} height="{$node.content.size.h}" {/if} 
                                {/if} />
                                {if isset($node.content.alt)}<span class="mobile-menu__banner-image-desc p-c8 d-flex align-items-start"><span class="d-inline-block py-c8 paragraph-p1">{$node.content.alt}</span><span class="btn btn-ghost btn-icon"><i class="mi-arrow-right"></i></span></span>{/if}
                                {if isset($node.content.href)}</a>{/if}
                        {/if}
                    {/if}
                {/if}

        {/if}


        {if isset($node.children) && $node.children|@count > 0}
            {foreach from=$node.children item=child name=categoryTreeBranch}
                {include file="module:iqitmegamenu/views/templates/hook/_partials/submenu_content_mobile.tpl" node=$child}
            {/foreach}
        {/if}

        {if $node.type==2}
        {/if}
</div>