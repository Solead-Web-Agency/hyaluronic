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
        <div class="swiper-slide h-auto">
            <div class="thumb-container js-thumb-container h-100">
                <div class="iqitextened-img-thumb iqitextened-img-thumb-video grey-background h-100 text-center d-flex cursor-pointer">
                    <div class="align-self-center w-100">
                        <i class="fa fa-play d-block m-2"></i>
                        {l s='Video' mod='iqitextendedproduct'}
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
{/if}

{if isset($isThreeSixtyContent) && $isThreeSixtyContent}
    <div class="swiper-slide h-auto ">
        <div class="thumb-container js-thumb-container h-100">
            <div class="iqitextened-img-thumb iqitextened-img-thumb-360 grey-background h-100 text-center d-flex cursor-pointer">
                <div class="align-self-center w-100">
                    <i class="fa fa-arrows-h d-block m-2"></i>
                    {l s='360' mod='iqitextendedproduct'}
                </div>
            </div>
        </div>
    </div>
{/if}