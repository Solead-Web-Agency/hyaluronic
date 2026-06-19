<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ZendeskAddon\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ZendeskClasslib\Hook\AbstractHook;

class HeaderHook extends AbstractHook
{
    /** @var \Zendesk */
    protected $module;

    const AVAILABLE_HOOKS = [
        'displayBackOfficeHeader',
        'displayFooter',
    ];

    public function displayBackOfficeHeader()
    {
        $context = \Context::getContext();
        if (isset($context->controller) && $context->controller instanceof \AdminModulesController && \Tools::getValue('configure') == $this->module->name) {
            $context->controller->addJQuery();
        // $context->controller->addJS($this->_path.'views/js/script.js');
        } elseif (isset($context->controller) && $context->controller instanceof \AdminZendeskController) {
            $context->controller->addJQuery();
            $context->controller->addJqueryPlugin(['fancybox']);
            $context->controller->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/admin.js');

            if (version_compare(_PS_VERSION_, '1.6.0.1', '<')) {
                $context->controller->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/bootstrap.css');
            }
            $context->controller->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/admin.css');
        }
    }

    public function displayFooter($params)
    {
        if (\Tools::getValue('ajax') !== false) {
            return false;
        }

        $context = \Context::getContext();
        if ($this->module->api->isValid(true)) {
            if ($context->controller instanceof \ZendeskOrdersModuleFrontController || $context->controller instanceof \ZendeskCustomersModuleFrontController) {
                return false;
            }
            if (!$context->controller instanceof \ContactController && \Configuration::get(\Zendesk::WIDGET)) {
                $js_defs = [
                    'zendesk_subdomain' => \Configuration::get(\Zendesk::SUBDOMAIN),
                    'zendesk_iso' => $context->language->iso_code,
                    'zendesk_preload_widget' => false,
                ];

                if ((int) \Configuration::get(\Zendesk::PRELOAD_WIDGET)) {
                    $zendesk_preload_widget_controllers = json_decode(\Configuration::get(\Zendesk::PRELOAD_WIDGET_CONTROLLERS), true);
                    if (isset($zendesk_preload_widget_controllers[$context->controller->php_self])) {
                        $js_defs['zendesk_preload_widget'] = true;
                    }
                }

                $context->smarty->assign($js_defs);

                return $context->smarty->fetch(_PS_MODULE_DIR_ . 'zendesk/views/templates/hook/header.tpl');
            }
        }
    }
}
