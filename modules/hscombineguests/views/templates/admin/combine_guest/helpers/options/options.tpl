{**
* Multi Accessories for PrestaShop.
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{extends file="helpers/options/options.tpl"}
{block name="field"}
    {if $field['type'] == 'hs_combineguests_footer'}
        <div id="hs_module_admin_footer" class="text-center text-muted mt-3">
            <ol>
                <li>
                    {$hs_cg_i18n['created_by']|escape:'htmlall':'UTF-8'} <strong>{$field['module_author']|escape:'htmlall':'UTF-8'}</strong>
                </li>
                <li>
                    © {$field['module_year']|escape:'htmlall':'UTF-8'}
                </li>
                <li>
                    {$hs_cg_i18n['current_version']|escape:'htmlall':'UTF-8'} <strong>{$field['module_version']|escape:'htmlall':'UTF-8'}</strong>
                </li>
                <li>
                    <a href="{$field['document_url']|escape:'htmlall':'UTF-8'}" target="_blank">
                        <i class="icon-book"></i> {$hs_cg_i18n['documentation']|escape:'htmlall':'UTF-8'}
                    </a>
                </li>
                <li>
                    <a href="https://addons.prestashop.com/en/ratings.php" target="_blank" class="hs-rate-module">
                        <i class="icon-star"></i> {$hs_cg_i18n['rate_us']|escape:'htmlall':'UTF-8'}
                    </a>
                </li>
                <li>
                    <a href="https://addons.prestashop.com/en/contact-us?id_product=23412" target="_blank">
                        <i class="icon-question-circle"></i> {$hs_cg_i18n['need_help']|escape:'htmlall':'UTF-8'}
                    </a>
                </li>
                <li>
                    <a href="https://addons.prestashop.com/en/2_community-developer?contributor=613198" target="_blank">
                        <i class="icon-puzzle-piece"></i>{$hs_cg_i18n['all_modules']|escape:'htmlall':'UTF-8'} {$field['module_author']|escape:'htmlall':'UTF-8'}
                    </a>
                </li>
            </ol>
        </div>
    {else}    
        {$smarty.block.parent}
    {/if}
{/block}