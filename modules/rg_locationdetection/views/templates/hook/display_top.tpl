{**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 *}

{if $rgld_css}
    {$rgld_css nofilter}
{/if}

<div id="rg_infobar" style="display: none;">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="content">
                    <div class="left-side">
                        {$rgld.infobar_content nofilter}
                    </div>
                    <div class="right-side">
                        <div class="right-content">
                            <div class="close-button">
                                <a href="#">{l s='Close' mod='rg_locationdetection'}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
