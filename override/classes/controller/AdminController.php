<?php
/**
 * Override de compatibilité PS9 : réintroduit la méthode de traduction l() supprimée des
 * contrôleurs admin en PrestaShop 9 mais encore appelée par de nombreux contrôleurs de modules 1.7
 * (ets_seo, etc.). Couvre aussi ModuleAdminController (qui étend AdminController).
 * Renvoie la chaîne source (pas de traduction) : suffisant pour ne plus planter et afficher le texte.
 */
class AdminController extends AdminControllerCore
{
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        // Tentative de traduction module si dispo, sinon chaîne brute
        if (isset($this->module) && is_object($this->module) && method_exists($this->module, 'l')) {
            try {
                return $this->module->l($string, $class ? Tools::strtolower($class) : Tools::strtolower(get_class($this)));
            } catch (\Throwable $e) {
                return $string;
            }
        }

        return $string;
    }
}
