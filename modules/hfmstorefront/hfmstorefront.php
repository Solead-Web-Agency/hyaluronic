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
        $rpps = '';
        $attestation = 0;
        $proDoc = '';

        // 1) Données "pro" rattachées à la COMMANDE (cas commande invité).
        $hasOrderTable = (bool) $db->getValue(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . "hfm_order_pro_doc'",
            false
        );
        if ($hasOrderTable) {
            $orow = $db->getRow('SELECT rpps, attestation, pro_doc FROM `' . _DB_PREFIX_ . 'hfm_order_pro_doc` WHERE id_order = ' . (int) $idOrder, false);
            if ($orow) {
                $rpps = (string) $orow['rpps'];
                $attestation = (int) $orow['attestation'];
                $proDoc = $orow['pro_doc'] ? (string) $orow['pro_doc'] : '';
            }
        }

        // 2) Sinon, données sur le CLIENT (cas client connecté, réutilisable).
        if ($rpps === '' && !$attestation && $proDoc === '') {
            $hasCustTable = (bool) $db->getValue(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . _DB_PREFIX_ . "hfm_customer_rpps'",
                false
            );
            if ($hasCustTable) {
                $crow = $db->getRow('SELECT rpps, attestation, pro_doc FROM `' . _DB_PREFIX_ . 'hfm_customer_rpps` WHERE id_customer = ' . (int) $order->id_customer, false);
                if ($crow) {
                    $rpps = (string) $crow['rpps'];
                    $attestation = (int) $crow['attestation'];
                    $proDoc = $crow['pro_doc'] ? (string) $crow['pro_doc'] : '';
                }
            }
        }

        if ($rpps === '' && !$attestation) {
            return '';
        }

        // Cas 1 : numéro RPPS fourni -> rapprochement au registre local (si importé).
        if ($rpps !== '') {
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

        // Cas 2 : pas de numéro RPPS, mais attestation "professionnel de santé".
        if ($proDoc !== '') {
            $url = rtrim(Tools::getShopDomainSsl(true) . __PS_BASE_URI__, '/') . '/modules/hfmstorefront/pro_docs/' . rawurlencode($proDoc);
            $docHtml = '<div style="font-size:12px;color:#5a6a52;margin-top:6px;">Justificatif fourni : <a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank"><strong>télécharger</strong></a> — à vérifier.</div>';
        } else {
            $docHtml = '<div style="font-size:12px;color:#9a6700;margin-top:6px;">Aucun justificatif joint — à recevoir par email / à vérifier.</div>';
        }

        return '<div class="card mb-2" style="border-left:4px solid #1e7e34;">'
            . '<div class="card-body" style="padding:14px 18px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">'
            . '<div style="font-size:12px;font-weight:700;letter-spacing:.04em;color:#6b7280;text-transform:uppercase;">Professionnel de santé</div>'
            . '<span style="display:inline-flex;align-items:center;gap:6px;background:#e6f4ea;color:#1e7e34;border:1px solid #b7dfc3;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:600;">&#10003; Attestation sur l\'honneur</span>'
            . '<div style="flex-basis:100%;">' . $docHtml . '</div>'
            . '</div></div>';
    }

    // ---------- Éditeur de pages CMS multilingues (table hfm_cms_i18n) ----------

    /** Pages légales éditables (slug => libellé BO). */
    protected function cmsPages()
    {
        return [
            'livraison-retours' => 'Livraison & retours',
            'conditions-generales-de-ventes' => 'Conditions générales de vente',
            'politique-de-confidentialite' => 'Politique de confidentialité',
        ];
    }

    /** Les 22 langues du front (locale => nom FR). */
    protected function cmsLocales()
    {
        return [
            'fr' => 'Français', 'en' => 'Anglais', 'de' => 'Allemand', 'es' => 'Espagnol',
            'it' => 'Italien', 'pt' => 'Portugais', 'nl' => 'Néerlandais', 'pl' => 'Polonais',
            'ja' => 'Japonais', 'zh' => 'Chinois', 'ko' => 'Coréen', 'ar' => 'Arabe',
            'he' => 'Hébreu', 'sv' => 'Suédois', 'no' => 'Norvégien', 'da' => 'Danois',
            'fi' => 'Finnois', 'cs' => 'Tchèque', 'el' => 'Grec', 'ro' => 'Roumain',
            'bg' => 'Bulgare', 'sl' => 'Slovène',
        ];
    }

    protected function cmsTable()
    {
        return _DB_PREFIX_ . 'hfm_cms_i18n';
    }

    protected function ensureCmsTable()
    {
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . $this->cmsTable() . '` (
                `slug` VARCHAR(128) NOT NULL, `locale` VARCHAR(8) NOT NULL,
                `title` VARCHAR(255) DEFAULT NULL, `content` LONGTEXT,
                `date_upd` DATETIME NOT NULL, PRIMARY KEY (`slug`,`locale`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
    }

    /**
     * Config du module (BO) : infos API + éditeur des pages CMS multilingues.
     */
    public function getContent()
    {
        $this->ensureCmsTable();
        $db = Db::getInstance();
        $pages = $this->cmsPages();
        $locales = $this->cmsLocales();
        $table = $this->cmsTable();
        $confirm = '';

        // Sélection courante (POST prioritaire, sinon GET, sinon défauts).
        $slug = (string) Tools::getValue('hfm_slug', array_key_first($pages));
        $locale = (string) Tools::getValue('hfm_locale', 'fr');
        if (!isset($pages[$slug])) {
            $slug = array_key_first($pages);
        }
        if (!isset($locales[$locale])) {
            $locale = 'fr';
        }

        // Enregistrement.
        if (Tools::isSubmit('submitHfmCms')) {
            $title = trim((string) Tools::getValue('hfm_title'));
            // Contenu HTML : on récupère la valeur brute sans nettoyage HTML.
            $content = (string) Tools::getValue('hfm_content');
            $db->execute(
                'INSERT INTO `' . $table . '` (slug, locale, title, content, date_upd)
                 VALUES (\'' . pSQL($slug) . '\', \'' . pSQL($locale) . '\', \'' . pSQL($title) . '\', \'' . pSQL($content, true) . '\', NOW())
                 ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), date_upd = NOW()'
            );
            $confirm = $this->displayConfirmation(
                'Page « ' . htmlspecialchars($pages[$slug]) . ' » (' . htmlspecialchars($locales[$locale]) . ') enregistrée.'
            );
        }

        // Valeurs courantes pour le formulaire.
        $row = $db->getRow(
            'SELECT title, content FROM `' . $table . '` WHERE slug = \'' . pSQL($slug) . '\' AND locale = \'' . pSQL($locale) . '\'',
            false
        );
        $curTitle = $row ? (string) $row['title'] : '';
        $curContent = $row ? (string) $row['content'] : '';

        // Couverture par page (nb de langues remplies) pour info.
        $coverage = [];
        foreach (array_keys($pages) as $s) {
            $coverage[$s] = (int) $db->getValue(
                'SELECT COUNT(*) FROM `' . $table . '` WHERE slug = \'' . pSQL($s) . '\' AND TRIM(content) <> \'\'',
                false
            );
        }

        $token = Tools::getAdminTokenLite('AdminModules');
        $action = 'index.php?controller=AdminModules&configure=' . $this->name . '&token=' . $token;
        $total = count($locales);

        // URL d'aperçu front (origine CORS).
        $cors = array_filter(array_map('trim', explode(',', (string) Configuration::get('HFMSTOREFRONT_CORS'))));
        $frontBase = $cors ? rtrim(reset($cors), '/') : '';
        $previewUrl = $frontBase ? $frontBase . '/' . $locale . '/content/' . $slug : '';

        $out = $confirm;

        // --- Éditeur CMS ---
        $out .= '<div class="panel">';
        $out .= '<h3><i class="icon icon-file-text"></i> Pages CMS multilingues (front headless)</h3>';
        $out .= '<p class="text-muted">Édite le titre et le contenu HTML des pages légales du front, dans chacune des ' . $total . ' langues. '
              . 'Les modifications sont visibles immédiatement sur le front.</p>';

        // Sélecteurs (auto-submit pour charger la page/langue).
        $out .= '<form method="post" action="' . $action . '" id="hfm-cms-select" class="form-horizontal" style="margin-bottom:14px;">';
        $out .= '<div class="row"><div class="col-lg-5">';
        $out .= '<label>Page</label><select name="hfm_slug" class="form-control" onchange="document.getElementById(\'hfm-cms-select\').submit();">';
        foreach ($pages as $s => $label) {
            $out .= '<option value="' . htmlspecialchars($s) . '"' . ($s === $slug ? ' selected' : '') . '>'
                  . htmlspecialchars($label) . ' (' . $coverage[$s] . '/' . $total . ' langues)</option>';
        }
        $out .= '</select></div>';
        $out .= '<div class="col-lg-4">';
        $out .= '<label>Langue</label><select name="hfm_locale" class="form-control" onchange="document.getElementById(\'hfm-cms-select\').submit();">';
        foreach ($locales as $lc => $name) {
            $filled = (bool) $db->getValue('SELECT COUNT(*) FROM `' . $table . '` WHERE slug = \'' . pSQL($slug) . '\' AND locale = \'' . pSQL($lc) . '\' AND TRIM(content) <> \'\'', false);
            $out .= '<option value="' . htmlspecialchars($lc) . '"' . ($lc === $locale ? ' selected' : '') . '>'
                  . htmlspecialchars($name) . ' (' . htmlspecialchars($lc) . ')' . ($filled ? '' : ' — vide') . '</option>';
        }
        $out .= '</select></div></div></form>';

        // Formulaire d'édition.
        $out .= '<form method="post" action="' . $action . '" class="form-horizontal">';
        $out .= '<input type="hidden" name="hfm_slug" value="' . htmlspecialchars($slug) . '"/>';
        $out .= '<input type="hidden" name="hfm_locale" value="' . htmlspecialchars($locale) . '"/>';
        $out .= '<div class="form-group"><label class="control-label col-lg-2">Titre</label><div class="col-lg-10">';
        $out .= '<input type="text" name="hfm_title" class="form-control" value="' . htmlspecialchars($curTitle, ENT_QUOTES, 'UTF-8') . '"/>';
        $out .= '</div></div>';
        $out .= '<div class="form-group"><label class="control-label col-lg-2">Contenu (HTML)</label><div class="col-lg-10">';
        $out .= '<textarea name="hfm_content" rows="22" class="form-control" style="font-family:Menlo,Consolas,monospace;font-size:12.5px;line-height:1.5;">'
              . htmlspecialchars($curContent, ENT_QUOTES, 'UTF-8') . '</textarea>';
        $out .= '<p class="help-block">HTML autorisé (titres, paragraphes, listes, liens). Les chemins relatifs d\'images/liens sont automatiquement préfixés vers la boutique.</p>';
        $out .= '</div></div>';
        $out .= '<div class="panel-footer">';
        $out .= '<button type="submit" name="submitHfmCms" class="btn btn-default pull-right"><i class="process-icon-save"></i> Enregistrer</button>';
        if ($previewUrl) {
            $out .= '<a href="' . htmlspecialchars($previewUrl) . '" target="_blank" class="btn btn-default"><i class="icon icon-external-link"></i> Aperçu front</a>';
        }
        $out .= '</div>';
        $out .= '</form>';
        $out .= '</div>';

        // --- Infos API (existant) ---
        $secret = Configuration::get('HFMSTOREFRONT_SECRET');
        $base = $this->context->link->getModuleLink($this->name, 'cart');
        $out .= '<div class="panel"><h3>HFM Storefront API</h3>';
        $out .= '<p><strong>Secret (en-tête <code>X-Storefront-Token</code>) :</strong> <code>' . htmlspecialchars($secret) . '</code></p>';
        $out .= '<p><strong>Endpoint panier :</strong> <code>' . htmlspecialchars($base) . '</code></p>';
        $out .= '<p>Autres endpoints : <code>customer</code>, <code>checkout</code>, <code>products</code>, <code>taxonomy</code>, <code>orders</code>, <code>wishlist</code>, <code>content</code> (même base, remplacer le contrôleur).</p>';
        $out .= '<p>CORS autorisé : <code>' . htmlspecialchars(Configuration::get('HFMSTOREFRONT_CORS')) . '</code></p>';
        $out .= '<p>Passerelle de paiement headless : les commandes sont validées via ce module.</p>';
        $out .= '</div>';

        return $out;
    }
}
