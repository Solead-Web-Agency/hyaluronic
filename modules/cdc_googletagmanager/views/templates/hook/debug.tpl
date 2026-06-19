{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @package   cdc_googletagmanager
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<style>
#cdcgtm_debug {
    position: fixed;
    top: 0;
    right: 0;
    width: 500px;
    max-height: 420px;
    overflow-y: scroll;
    z-index: 9999;
    opacity: 0.7;
    margin-right: -470px;
    transition: margin 700ms;
}

#cdcgtm_debug:hover {
    opacity: 1;
    margin-right: 0;
}

</style>

<div id="cdcgtm_debug">
    <div style="position: absolute; top: 0; bottom: 0; left: 0; width: 30px; padding: 5px; font-weight: bold; font-size: 2em; line-height: 100%; background: #000000; color: #00ff00; text-align: center; border-right: 1px solid #00ff00;">
        &laquo; <span style="font-size: 70%;">D E B U G</span>
        <div style="position: absolute; bottom: 0; text-align: center; font-size: 80%;">
            <a href="?cdcgtm_debug=0">x</a>
        </div>
    </div>

    <div id="cdcgtm_debug_content" style="margin-left: 30px;">
        {foreach $debug_stack as $text}
            <pre style="background: #000; color: #0f0; font-size: 12px; font-family: monospace; margin: 0px; border: 0; border-bottom: 1px dashed #0f0;">{$text|escape:'htmlall':'UTF-8'}</pre>
        {/foreach}
    </div>
</div>