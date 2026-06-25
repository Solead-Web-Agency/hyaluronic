<?php
/**
 * HFM Storefront API — bridge headless pour Next.js
 * Expose en JSON les flux que le webservice PrestaShop ne couvre pas proprement :
 * panier, checkout, client. Appelé en serveur-à-serveur par le backend Next.js
 * (protégé par un secret partagé), jamais directement par le navigateur.
 *
 * C'est aussi le MODULE DE PAIEMENT du front headless : les commandes passées via
 * le tunnel Next sont validées par ce module (validateOrder), donc rattachées
 * proprement à "hfmstorefront" dans le back-office, avec le bon libellé de paiement
 * (Carte bancaire PayPlug / Virement / Chèque).
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Hfmstorefront extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'hfmstorefront';
        $this->tab = 'payments_gateways';
        $this->version = '1.1.0';
        $this->author = 'HFM';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        parent::__construct();
        $this->displayName = $this->l('HFM Storefront (headless + paiement)');
        $this->description = $this->l('API JSON panier/checkout/client et passerelle de paiement pour un front Next.js headless.');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        if (!Configuration::get('HFMSTOREFRONT_SECRET')) {
            Configuration::updateValue('HFMSTOREFRONT_SECRET', Tools::passwdGen(48));
        }
        Configuration::updateValue('HFMSTOREFRONT_CORS', 'http://localhost:3000');

        // Hooks de paiement : rend le module reconnu comme passerelle de paiement.
        $this->registerHook('paymentOptions');
        $this->registerHook('paymentReturn');
        // Affichage du RPPS praticien en haut de la fiche commande (back-office).
        $this->registerHook('displayAdminOrderTop');

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('HFMSTOREFRONT_SECRET');
        Configuration::deleteByName('HFMSTOREFRONT_CORS');

        return parent::uninstall();
    }

    /**
     * Tunnel d'achat en headless : aucun bouton de paiement n'est rendu par le thème PS
     * (le front Next gère l'UI). On renvoie donc une liste vide tout en restant un module
     * de paiement valide pour validateOrder().
     */
    public function hookPaymentOptions($params)
    {
        return [];
    }

    public function hookPaymentReturn($params)
    {
        return '';
    }

    /**
     * Back-office, haut de la fiche commande : affiche le N° RPPS du praticien
     * UNIQUEMENT si le client de la commande en a renseigné un (lecture conditionnelle,
     * aucune donnée ajoutée à la commande). Badge vert si rapproché au registre ANS local.
     */
    public function hookDisplayAdminOrderTop($params)
    {
        $idOrder = (int) (isset($params['id_order']) ? $params['id_order'] : 0);
        if (!$idOrder) {
            return '';
        }
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }
        $db = Db::getInstance();
        // Le RPPS vit sur le CLIENT (table hfm_customer_rpps) -> conditionnel.
        $hasCustTable = (bool) $db->getValue(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . "hfm_customer_rpps'",
            false
        );
        if (!$hasCustTable) {
            return '';
        }
        $rpps = (string) $db->getValue(
            'SELECT rpps FROM `' . _DB_PREFIX_ . 'hfm_customer_rpps` WHERE id_customer = ' . (int) $order->id_customer,
            false
        );
        if ($rpps === '') {
            return '';
        }

        // Rapprochement au registre local (si importé).
        $found = false;
        $praticien = '';
        $hasReg = (bool) $db->getValue(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . "hfm_rpps_registry'",
            false
        );
        if ($hasReg) {
            $row = $db->getRow('SELECT nom, prenom, profession FROM `' . _DB_PREFIX_ . 'hfm_rpps_registry` WHERE rpps = \'' . pSQL($rpps) . '\'', false);
            if ($row) {
                $found = true;
                $praticien = trim($row['prenom'] . ' ' . $row['nom'] . ($row['profession'] ? ' — ' . $row['profession'] : ''));
            }
        }

        $rppsHtml = htmlspecialchars($rpps, ENT_QUOTES, 'UTF-8');
        if ($found) {
            $badge = '<span style="display:inline-flex;align-items:center;gap:6px;background:#e6f4ea;color:#1e7e34;border:1px solid #b7dfc3;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:600;">&#10003; Vérifié au registre ANS</span>';
            $detail = '<div style="font-size:12px;color:#5a6a52;margin-top:6px;">Praticien : <strong>' . htmlspecialchars($praticien, ENT_QUOTES, 'UTF-8') . '</strong></div>';
            $accent = '#1e7e34';
        } else {
            $badge = '<span style="display:inline-flex;align-items:center;gap:6px;background:#fff4e5;color:#9a6700;border:1px solid #ffd8a8;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:600;">&#9888; À confirmer manuellement</span>';
            $detail = '<div style="font-size:12px;color:#9a6700;margin-top:6px;">Non rapproché au registre ANS — à vérifier.</div>';
            $accent = '#9a6700';
        }

        return '<div class="card mb-2" style="border-left:4px solid ' . $accent . ';">'
            . '<div class="card-body" style="padding:14px 18px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">'
            . '<div style="font-size:12px;font-weight:700;letter-spacing:.04em;color:#6b7280;text-transform:uppercase;">Numéro RPPS praticien</div>'
            . '<div style="font-size:18px;font-weight:700;color:#1f2937;letter-spacing:.02em;">' . $rppsHtml . '</div>'
            . $badge
            . '<div style="flex-basis:100%;">' . $detail . '</div>'
            . '</div></div>';
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
        $out .= '<p>Autres endpoints : <code>customer</code>, <code>checkout</code>, <code>products</code>, <code>taxonomy</code>, <code>orders</code>, <code>wishlist</code> (même base, remplacer le contrôleur).</p>';
        $out .= '<p>CORS autorisé : <code>' . htmlspecialchars(Configuration::get('HFMSTOREFRONT_CORS')) . '</code></p>';
        $out .= '<p>Passerelle de paiement headless : les commandes sont validées via ce module.</p>';
        $out .= '</div>';

        return $out;
    }
}
