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
 * @package   cedwish
 */
-->

{if $wish_error}
    <button
            type="button"
            class="btn btn-danger text-danger btn-xs"
            onclick="getOrderDetails(this, '{$wish_order_id|escape:'htmlall':'UTF-8'}');"
            title="{$wish_error|escape:'htmlall':'UTF-8'}"
    >
        <i class="process-icon-previewURL"></i>
    </button>
    <input
            type="hidden"
            id="order-error-message{$wish_order_id|escape:'htmlall':'UTF-8'}"
            value="{$wish_error|escape:'htmlall':'UTF-8'}"
    />
{else}
    <button
            type="button"
            class="btn btn-success btn-xs"
            onclick="getOrderDetails(this, '{$wish_order_id|escape:'htmlall':'UTF-8'}');"
    >
        <i class="process-icon-previewURL"></i>
    </button>
{/if}
