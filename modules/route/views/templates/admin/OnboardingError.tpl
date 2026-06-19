{*
* A Route Prestashop Extension that adds secure shipping
* protection to your orders
*
* Php version 7.0^
*
* @author    Route Development Team <dev@routeapp.io>
* @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
* @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
*}

<div class="notification-global notification-global-warning">
    {if $failureStep eq '1'}
        <h1><strong style="color:#ff0000">Important: </strong> We couldn't create your user account, please contact our <a href="https://help.route.com/hc/en-us" target="_blank">support</a>.</h1>
    {elseif $failureStep eq '2'}
        <h1><strong style="color:#ff0000">Important: </strong> We couldn't activate your user account, please contact our <a href="https://help.route.com/hc/en-us" target="_blank">support</a>.</h1>
    {elseif $failureStep eq '3'}
        <h1><strong style="color:#ff0000">Important: </strong> Route merchant already exists, please contact our <a href="https://help.route.com/hc/en-us" target="_blank">support</a>.</h1>
    {elseif $failureStep eq '4'}
        <h1><strong style="color:#ff0000">Important: </strong> You need to configure this module for a specific shop.</h1>
    {else}
        <h1><strong style="color:#ff0000">Important: </strong> Route installation has failed for an unknown reason, please contact our <a href="https://help.route.com/hc/en-us" target="_blank">support</a>.</h1>
    {/if}
</div>
