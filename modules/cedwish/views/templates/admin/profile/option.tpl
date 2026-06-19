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
 * @package   CedVidaXL
 */
 -->

{if isset($categoryList) && !empty($categoryList)}
    <option value="">--Select--</option>
    {foreach $categoryList as $category}
            {if $selected_category_id|trim == $category['idcategoria']|trim}
                <option selected="selected" value="{$category['idcategoria']|escape:'htmlall':'UTF-8'}">
                    {$category['nombre']|escape:'htmlall':'UTF-8'}
                </option>
            {else}
                <option value="{$category['idcategoria']|escape:'htmlall':'UTF-8'}">
                    {$category['nombre']|escape:'htmlall':'UTF-8'}
                </option>
            {/if}
    {/foreach}
{/if}
