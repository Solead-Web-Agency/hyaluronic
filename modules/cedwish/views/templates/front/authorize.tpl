<!--
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt

 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */
-->
{extends "$layout"}
{block name="content"}
<style>
    {literal}
    ol.tasks {
        padding: 2%;
    }
    li.task {
        padding: 1%;
        margin: 1%;
        background-color: #a0cff6;
    }
    {/literal}
</style>
<div class="panel" >
    <div class="panel-body">
        <div class="card">
            <ol class="tasks">
                <li class="task">Get Code - <span style="color:green;">{$code|escape:'htmlall':'UTF-8'}</span></li>
                {if $response['success']}
                    <li class="task">Generating Token <span style="color:green;">Token Created Successfully.</span></li>
                {else}
                    <li class="task">
                        Generating Token <span style="color:red;">{$response['message']|escape:'htmlall':'UTF-8'}</span>
                    </li>
                {/if}
                {if $response['success']}
                <div style="padding: 3%;" id="response">
                    <pre>{$response['message']|var_export|escape:'htmlall':'UTF-8'}</pre>
                </div>
                {/if}
            </ol>
            <p style="margin: 2%;"> Please visit back office, and see token and refresh token should be populated, else try again. </p>
        </div>
    </div>
</div>
{/block}
