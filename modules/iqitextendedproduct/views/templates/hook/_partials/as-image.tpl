{*
* 2017 IQIT-COMMERCE.COM
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement
*
* @author    IQIT-COMMERCE.COM <support@iqit-commerce.com>
* @copyright 2017 IQIT-COMMERCE.COM
* @license   Commercial license (You can not resell or redistribute this software.)
*
*}


{if isset($productVideoContent) && $productVideoContent}
        {foreach $productVideoContent as $video}
            <div class="product-lmage-large swiper-slide h-auto ">
            <div class="iqitvideo align-self-center h-100 ">
                {if $video.p == 'hosted'}
                    <video width="100%" height="100%" controls class="video-hosted">
                        <source src="{$video.id}" type="video/mp4">
                    </video>
                {else}
                    <iframe class="iframe" width="100%" height="100%"
                        {if $video.p == 'youtube'}src="//www.youtube-nocookie.com/embed/{$video.id}?rel=0&showinfo=0"{/if}
                        {if $video.p == 'dailymotion'}src="//www.dailymotion.com/embed/video/{$video.id}" {/if}
                        {if $video.p == 'vimeo'}src="//player.vimeo.com/video/{$video.id}" {/if}></iframe>
                {/if}
            </div>
            </div>
        {/foreach}
{/if}

{if isset($isThreeSixtyContent) && $isThreeSixtyContent}
    <div class="product-lmage-large swiper-slide swiper-no-swiping">
                    <div id="iqit-threesixty" data-threesixty="{$threeSixtyContent nofilter}"><i class="fa fa-circle-o-notch fa-spin fa-2x icon-tidi-load"></i></div>
    </div>
{/if}






