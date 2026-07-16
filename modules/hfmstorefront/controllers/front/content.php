<?php
/**
 * Endpoint contenu (pages CMS PrestaShop) pour le front headless.
 *   GET ?action=page&link_rewrite=...   -> { page: { title, content(HTML), meta_description, link_rewrite } }
 *   GET ?action=list                    -> { pages: [ { id_cms, link_rewrite, title } ] } (pages actives)
 * Le contenu reste éditable en back-office (ps_cms / ps_cms_lang).
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/cache.php';

class HfmstorefrontContentModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $action = (string) $this->in('action');

        // Pages CMS : lecture publique -> cacheable (tag content, TTL 3600).
        $key = HfmCache::key(HfmCache::TAG_CONTENT, $action === 'list' ? 'list' : 'page', [
            'id_lang' => $idLang,
            'id_shop' => $idShop,
            'link_rewrite' => (string) $this->in('link_rewrite'),
            'id_cms' => (int) $this->in('id_cms'),
            'locale' => preg_replace('/[^a-z]/', '', (string) $this->in('locale')),
        ]);

        return HfmCache::remember($key, HfmCache::TTL_CONTENT, function () use ($action) {
            if ($action === 'list') {
                return ['pages' => $this->listPages()];
            }
            return $this->page();
        });
    }

    protected function listPages()
    {
        $idLang = (int) $this->context->language->id;
        $defLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $rows = Db::getInstance()->executeS(
            'SELECT c.id_cms,
                    COALESCE(cl.link_rewrite, cld.link_rewrite) AS link_rewrite,
                    COALESCE(NULLIF(cl.meta_title, ""), cld.meta_title) AS title
             FROM ' . _DB_PREFIX_ . 'cms c
             LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl ON (cl.id_cms = c.id_cms AND cl.id_lang = ' . $idLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cld ON (cld.id_cms = c.id_cms AND cld.id_lang = ' . $defLang . ')
             WHERE c.active = 1
             ORDER BY c.position ASC'
        );
        $out = [];
        foreach ((array) $rows as $r) {
            if (empty($r['link_rewrite'])) {
                continue;
            }
            $out[] = ['id_cms' => (int) $r['id_cms'], 'link_rewrite' => $r['link_rewrite'], 'title' => $r['title']];
        }
        return $out;
    }

    protected function page()
    {
        $idLang = (int) $this->context->language->id;
        $defLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $slug = (string) $this->in('link_rewrite');
        $idCms = (int) $this->in('id_cms');
        $locale = preg_replace('/[^a-z]/', '', (string) $this->in('locale'));

        // Résolution de l'id_cms depuis N'IMPORTE QUEL slug, toutes langues confondues : les
        // anciennes URLs localisées indexées (rechtlicher-hinweis, aviso-legal…) doivent continuer
        // à résoudre. Le front les redirigera ensuite vers le slug canonique de la locale.
        if (!$idCms && $slug !== '') {
            $idCms = (int) Db::getInstance()->getValue(
                'SELECT id_cms FROM ' . _DB_PREFIX_ . 'cms_lang WHERE link_rewrite = \'' . pSQL($slug) . '\'',
                false
            );
        }
        if (!$idCms) {
            return ['error' => 'not_found'];
        }

        // Une page CMS a un slug DIFFÉRENT par langue (mentions-legales / rechtlicher-hinweis /
        // avviso-legale / aviso-legal). On renvoie donc le slug CANONIQUE de la locale demandée
        // (+ la map par langue) : sans ça, chaque slug servi sous chaque locale serait
        // auto-canonique -> autant de clusters hreflang concurrents pour une même page.
        $alt = $this->cmsAlternates($idCms);
        $canonical = isset($alt[$idLang]) ? $alt[$idLang] : (isset($alt[$defLang]) ? $alt[$defLang] : $slug);

        // 1) Traduction éditée en BO (table multilingue) pour cette locale.
        if ($slug !== '' && $locale !== '') {
            $tr = $this->getCmsTranslation($slug, $locale);
            if ($tr) {
                return ['page' => [
                    'id_cms' => $idCms,
                    'title' => $tr['title'],
                    'meta_description' => '',
                    'link_rewrite' => $canonical,
                    'alternates' => $alt,
                    'content' => $this->fixUrls((string) $tr['content']),
                    'date_upd' => isset($tr['date_upd']) ? $tr['date_upd'] : null,
                    'source' => 'i18n',
                ]];
            }
        }
        $active = (bool) Db::getInstance()->getValue('SELECT active FROM ' . _DB_PREFIX_ . 'cms WHERE id_cms = ' . $idCms, false);
        if (!$active) {
            return ['error' => 'not_found'];
        }

        // Contenu dans la langue courante, sinon repli sur la langue par défaut.
        $row = $this->cmsRow($idCms, $idLang);
        if (!$row || trim((string) $row['content']) === '') {
            $row = $this->cmsRow($idCms, $defLang);
        }
        if (!$row) {
            return ['error' => 'not_found'];
        }

        return ['page' => [
            'id_cms' => $idCms,
            'title' => $row['meta_title'],
            'meta_description' => $row['meta_description'],
            'link_rewrite' => $canonical,
            'alternates' => $alt,
            'content' => $this->fixUrls((string) $row['content']),
        ]];
    }

    /**
     * Slugs CMS par langue (id_lang => link_rewrite), langues INSTALLÉES uniquement.
     * Sert au canonical de la locale + aux hreflang. NB : cms_lang porte id_shop en PS9 -> filtré,
     * sinon une ligne par boutique ferait doublon.
     */
    protected function cmsAlternates($idCms)
    {
        $idShop = (int) $this->context->shop->id;
        $out = [];
        foreach ((array) Db::getInstance()->executeS(
            'SELECT cl.id_lang, cl.link_rewrite
             FROM ' . _DB_PREFIX_ . 'cms_lang cl
             INNER JOIN ' . _DB_PREFIX_ . 'lang l ON (l.id_lang = cl.id_lang AND l.active = 1)
             WHERE cl.id_cms = ' . (int) $idCms . ' AND cl.id_shop = ' . $idShop
        ) as $r) {
            if ((string) $r['link_rewrite'] !== '') {
                $out[(int) $r['id_lang']] = (string) $r['link_rewrite'];
            }
        }
        return $out;
    }

    protected function cmsRow($idCms, $idLang)
    {
        return Db::getInstance()->getRow(
            'SELECT meta_title, meta_description, link_rewrite, content
             FROM ' . _DB_PREFIX_ . 'cms_lang WHERE id_cms = ' . (int) $idCms . ' AND id_lang = ' . (int) $idLang,
            false
        );
    }

    /** Rend absolues les URLs relatives d'images/liens du contenu (vers la base PrestaShop). */
    protected function fixUrls($html)
    {
        $base = rtrim(Tools::getShopDomainSsl(true) . __PS_BASE_URI__, '/');
        // Rewrite racine-relatif -> absolu, SANS toucher aux URLs protocole-relatives (//cdn) ni absolues.
        $html = preg_replace('#(src|href)="/(?!/)#i', '$1="' . $base . '/', $html);
        $html = str_replace($base . '/http', 'http', $html);
        return $html;
    }
}
