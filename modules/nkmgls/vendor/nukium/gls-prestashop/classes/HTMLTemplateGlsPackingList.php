<?php
/**
 *  Module made by Nukium
 *
 *  @author    Nukium
 *  @copyright 2023 Nukium SAS
 *  @license   All rights reserved
 *
 * ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
 * ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
 * ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
 * ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
 * ██   ████  ██████  ██   ██ ██  ██████  ██      ██
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

use Nukium\GLS\Common\Value\GlsValue;

class HTMLTemplateGlsPackingList extends HTMLTemplate
{
    public $packages = [];
    public $module = null;

    public function __construct($packages, $smarty)
    {
        $this->packages = $packages;
        $this->smarty = $smarty;
        $this->context = Context::getContext();

        $this->date = Tools::displayDate(date('Y-m-d H:i:s'));
        $this->title = $this->l('Packing list');

        $this->shop = $this->context->shop;
    }

    public function getHeader()
    {
        if (version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            $this->assignCommonHeaderData();
        }

        if (Configuration::get('GLS_API_SHOP_RETURN_ADDRESS')) {
            $shop_address = $this->context->shop->getAddress();
        } else {
            $shop_address = new Address();
            $shop_address->company = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_NAME');
            $shop_address->id_country = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_COUNTRY');
            $shop_address->address1 = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1');
            $shop_address->address2 = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2');
            $shop_address->postcode = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_POSTCODE');
            $shop_address->city = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_CITY');
        }

        $glsLogo = _PS_MODULE_DIR_ . 'nkmgls/views/img/admin/gls-logo-print.jpg';
        $width = 171;
        $height = 58;

        $nbLabels = $total_weight = 0;
        if (is_array($this->packages) && count($this->packages) > 0) {
            $nbLabels = count($this->packages);
            foreach ($this->packages as $key => $value) {
                $total_weight += (float) $value['weight'];
            }
        }

        $style_tab = '';
        if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            $style_tab = $this->smarty->fetch(_PS_MODULE_DIR_ . 'nkmgls/views/templates/admin/packing-list.style-tab.tpl');
        } else {
            $style_tab = $this->smarty->fetch('module:nkmgls/views/templates/admin/packing-list.style-tab.tpl');
        }

        $this->smarty->assign([
            'style_tab' => $style_tab,
            'gls_logo' => $glsLogo,
            'width_logo' => $width,
            'height_logo' => $height,
            'shop_address' => AddressFormat::generateAddress($shop_address, [], '<br>&nbsp;&nbsp;'),
            'shop_url' => $this->context->shop->getBaseURL(),
            'client_number' => Configuration::get('GLS_API_CUSTOMER_ID') . ' ' . Configuration::get('GLS_API_CONTACT_ID'),
            'nb_labels' => $nbLabels,
            'total_weight' => $total_weight,
            'packing_list_date' => Tools::displayDate(date('Y-m-d'), null, false),
        ]);

        if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            return $this->smarty->fetch(_PS_MODULE_DIR_ . 'nkmgls/views/templates/admin/header.tpl');
        } else {
            return $this->smarty->fetch('module:nkmgls/views/templates/admin/header.tpl');
        }
    }

    public function getFooter()
    {
        return '';
    }

    public function getContent()
    {
        $packages = [];
        if (is_array($this->packages) && count($this->packages) > 0) {
            $packages = $this->packages;
        }

        $style_tab = '';
        if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            $style_tab = $this->smarty->fetch(_PS_MODULE_DIR_ . 'nkmgls/views/templates/admin/packing-list.style-tab.tpl');
        } else {
            $style_tab = $this->smarty->fetch('module:nkmgls/views/templates/admin/packing-list.style-tab.tpl');
        }

        $this->smarty->assign([
            'style_tab' => $style_tab,
            'packages' => $packages,
            'product_code' => GlsValue::PRODUCT_CODE,
            'gls_order_reference_enable' => Configuration::get('GLS_EXPORT_ORDER_REFERENCE_ENABLE'),
        ]);

        if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            return $this->smarty->fetch(_PS_MODULE_DIR_ . 'nkmgls/views/templates/admin/packing-list.tpl');
        } else {
            return $this->smarty->fetch('module:nkmgls/views/templates/admin/packing-list.tpl');
        }
    }

    public function getBulkFilename()
    {
        return 'packings-lists-' . date('Y-m-d_H-i') . '.pdf';
    }

    public function getFilename()
    {
        return 'packing-list-' . date('Y-m-d_H-i') . '.pdf';
    }
}
