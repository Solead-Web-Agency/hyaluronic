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
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedClaroshop
 */
 -->

{if $id_product && $has_error}
    <button
            class="btn btn-danger btn-xs"
            type="button"
            onclick="viewProductError(this, '{$id_product|escape:'htmlall':'UTF-8'}','{$id_product_attribute|escape:'htmlall':'UTF-8'}','{$product_grid_token|escape:'htmlall':'UTF-8'}')"
    >
        <i class="process-icon-previewURL"></i>
    </button>
{elseif $id_product && $marketplace_id}
    <button
            class="btn btn-success btn-xs"
            onclick="viewProductData(this, '{$marketplace_id|escape:'htmlall':'UTF-8'}','{$product_grid_token|escape:'htmlall':'UTF-8'}');"
            type="button"
    >
        <i class="process-icon-previewURL"></i>
    </button>
{else}
    <button
            class="btn btn-success btn-xs"
            disabled
            type="button"
    >
        <i class="process-icon-previewURL"></i>
    </button>
{/if}
