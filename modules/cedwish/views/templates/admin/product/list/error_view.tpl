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

{if $error_rows}
    <div class="table-responsive table">
        <table class="table">
            <thead>
                <th>{l s='S. No.' mod='cedwish'}</th>
                <th>{l s='Message' mod='cedwish'}</th>
            </thead>
            <tbody>
            {foreach $error_rows as $index => $error_row}
                {foreach $error_row as $key => $error}
                    <tr>
                        <td>{($index+1)|escape:'htmlall':'UTF-8'}</td>
                        <td>{$error|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/foreach}
            {/foreach}
            </tbody>
        </table>
    </div>
{else}
    <div>{l s='No Error Found.' mod='cedwish'}</div>
{/if}
