<?php
/**
 * RefreshSitemap module
 *
 * @author    Samdha <contact@samdha.net>
 * @copyright Samdha
 * @license   commercial license see license.txt
 * @category  Prestashop
 * @category  Module
 */

class cronCronModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public function init()
    {
        header('Access-Control-Allow-Origin: *');

        parent::init();

        Tools::setCookieLanguage($this->context->cookie);

        if ($this->module && $this->module->active) {
            $log = $this->module->runJobs();
        }

        if ($this->module->config->_method == 'traffic') {
            // generate empty picture http://www.nexen.net/articles/dossier/16997-une_image_vide_sans_gd.php
            $hex = '47494638396101000100800000ffffff00000021f90401000000002c00000000010001000002024401003b';
            $img = '';
            $t = strlen($hex) / 2;
            for ($i = 0; $i < $t; $i++) {
                $img .= chr(hexdec(substr($hex, $i * 2, 2)));
            }
            header('Last-Modified: Fri, 01 Jan 1999 00:00 GMT', true, 200);
            header('Content-Length: '.strlen($img));
            header('Content-Type: image/gif');
            echo $img;
        } else {
            header('Content-Type: text/plain');
            echo $log;
        }
        die();
    }
}
