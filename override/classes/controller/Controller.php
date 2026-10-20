<?php
/**
 * Override de compatibilité PS9 : réintroduit Controller::ajaxDie(), supprimée en PrestaShop 9
 * (remplacée par ajaxRender()) mais encore appelée par de nombreux modules 1.7 : dhlexpress (étiquettes),
 * nkmgls (point relais, étiquettes), psgdpr, pscartabandonmentpro… Sans elle : erreur 500 sur leurs appels AJAX.
 * Comportement PS 1.7 : rendu de la réponse puis fin de la requête.
 */
abstract class Controller extends ControllerCore
{
    protected function ajaxDie($value = null, $controller = null, $method = null)
    {
        if ($method === null) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $method = isset($bt[1]['function']) ? $bt[1]['function'] : null;
        }
        $this->ajaxRender($value, $controller, $method);
        exit;
    }
}
