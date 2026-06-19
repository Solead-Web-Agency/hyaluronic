<?php
/**
 * HFM Storefront API — bridge headless pour Next.js
 * Expose en JSON les flux que le webservice PrestaShop ne couvre pas proprement :
 * panier, checkout, client. Appelé en serveur-à-serveur par le backend Next.js
 * (protégé par un secret partagé), jamais directement par le navigateur.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Hfmstorefront extends Module
{
    public function __construct()
    {
        $this->name = 'hfmstorefront';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'HFM';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('HFM Storefront API (headless)');
        $this->description = $this->l('API JSON panier/checkout/client pour un front Next.js headless.');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        // Secret partagé : le backend Next.js doit l'envoyer dans l'en-tête X-Storefront-Token
        if (!Configuration::get('HFMSTOREFRONT_SECRET')) {
            Configuration::updateValue('HFMSTOREFRONT_SECRET', Tools::passwdGen(48));
        }
        // Origines autorisées (CORS) pour le front Next.js, séparées par des virgules
        Configuration::updateValue('HFMSTOREFRONT_CORS', 'http://localhost:3000');

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('HFMSTOREFRONT_SECRET');
        Configuration::deleteByName('HFMSTOREFRONT_CORS');

        return parent::uninstall();
    }

    /**
     * Affiche le secret + les URLs d'API dans la config du module (BO).
     */
    public function getContent()
    {
        $secret = Configuration::get('HFMSTOREFRONT_SECRET');
        $base = $this->context->link->getModuleLink($this->name, 'cart');
        $out = '<div class="panel"><h3>HFM Storefront API</h3>';
        $out .= '<p><strong>Secret (en-tête <code>X-Storefront-Token</code>) :</strong> <code>' . htmlspecialchars($secret) . '</code></p>';
        $out .= '<p><strong>Endpoint panier :</strong> <code>' . htmlspecialchars($base) . '</code></p>';
        $out .= '<p>Autres endpoints : <code>customer</code>, <code>checkout</code> (même base, remplacer le contrôleur).</p>';
        $out .= '<p>CORS autorisé : <code>' . htmlspecialchars(Configuration::get('HFMSTOREFRONT_CORS')) . '</code></p>';
        $out .= '</div>';

        return $out;
    }
}
