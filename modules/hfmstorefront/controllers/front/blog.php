<?php
/**
 * Endpoint BLOG (module ph_simpleblog) pour le front headless.
 *   GET ?action=list&page=1&limit=9&category=<slug>  -> { posts:[…], total, page, pages, category }
 *   GET ?action=post&slug=<slug>                      -> { post: {…} }  | { error:'not_found' }
 *   GET ?action=categories                            -> { categories:[ {id,name,slug,count} ] }
 *   GET ?action=latest&limit=3                        -> { posts:[…] }  (teaser home)
 *
 * Le contenu reste éditable en back-office (ps_simpleblog_post / _lang).
 * Lecture publique -> cacheable (tag « blog », TTL 24 h, purge événementielle).
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/cache.php';

class HfmstorefrontBlogModuleFrontController extends HfmStorefrontApiController
{
    public function handleGet()
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $action = (string) $this->in('action');
        if ($action === '') {
            $action = 'list';
        }

        $key = HfmCache::key(HfmCache::TAG_BLOG, $action, [
            'id_lang' => $idLang,
            'id_shop' => $idShop,
            'slug' => (string) $this->in('slug'),
            'id' => (int) $this->in('id'),
            'category' => (string) $this->in('category'),
            'page' => (int) $this->in('page'),
            'limit' => (int) $this->in('limit'),
            'locale' => preg_replace('/[^a-z]/', '', (string) $this->in('locale')),
        ]);

        return HfmCache::remember($key, HfmCache::TTL_BLOG, function () use ($action) {
            switch ($action) {
                case 'post':
                    return $this->post();
                case 'categories':
                    return ['categories' => $this->categories()];
                case 'latest':
                    $limit = (int) $this->in('limit');
                    $limit = ($limit > 0 && $limit <= 12) ? $limit : 3;
                    return ['posts' => $this->listPosts(1, $limit, '')['posts']];
                case 'list':
                default:
                    $page = max(1, (int) $this->in('page'));
                    $limit = (int) $this->in('limit');
                    $limit = ($limit > 0 && $limit <= 48) ? $limit : 9;
                    return $this->listPosts($page, $limit, (string) $this->in('category'));
            }
        });
    }

    /* ---------------------------------------------------------------- listing */

    protected function listPosts($page, $limit, $categorySlug)
    {
        $idLang = (int) $this->context->language->id;
        $defLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $offset = ($page - 1) * $limit;

        $catFilter = '';
        $category = null;
        if ($categorySlug !== '') {
            $idCat = $this->categoryIdFromSlug($categorySlug);
            if ($idCat) {
                $catFilter = ' AND p.id_simpleblog_category = ' . (int) $idCat;
                $category = $this->categoryHead($idCat);
            } else {
                return ['posts' => [], 'total' => 0, 'page' => 1, 'pages' => 0, 'category' => null];
            }
        }

        $total = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'simpleblog_post p WHERE p.active = 1' . $catFilter,
            false
        );

        $rows = Db::getInstance()->executeS(
            'SELECT p.id_simpleblog_post AS id, p.cover, p.author, p.is_featured, p.views, p.date_add,
                    p.id_simpleblog_category AS id_category,
                    COALESCE(NULLIF(pl.title, ""), pld.title) AS title,
                    COALESCE(NULLIF(pl.link_rewrite, ""), pld.link_rewrite) AS slug,
                    COALESCE(NULLIF(pl.short_content, ""), pld.short_content) AS excerpt,
                    COALESCE(NULLIF(cl.name, ""), cld.name) AS category_name,
                    COALESCE(NULLIF(cl.link_rewrite, ""), cld.link_rewrite) AS category_slug
             FROM ' . _DB_PREFIX_ . 'simpleblog_post p
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_post_lang pl ON (pl.id_simpleblog_post = p.id_simpleblog_post AND pl.id_lang = ' . $idLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_post_lang pld ON (pld.id_simpleblog_post = p.id_simpleblog_post AND pld.id_lang = ' . $defLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_category_lang cl ON (cl.id_simpleblog_category = p.id_simpleblog_category AND cl.id_lang = ' . $idLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_category_lang cld ON (cld.id_simpleblog_category = p.id_simpleblog_category AND cld.id_lang = ' . $defLang . ')
             WHERE p.active = 1' . $catFilter . '
             ORDER BY p.date_add DESC
             LIMIT ' . (int) $offset . ', ' . (int) $limit
        );

        $posts = [];
        foreach ((array) $rows as $r) {
            if (empty($r['slug'])) {
                continue;
            }
            $posts[] = $this->card($r);
        }

        return [
            'posts' => $posts,
            'total' => $total,
            'page' => (int) $page,
            'pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            'category' => $category,
        ];
    }

    /** Carte « liste » : champs légers + cover. L'excerpt reste du HTML (assaini côté front). */
    protected function card($r)
    {
        return [
            'id' => (int) $r['id'],
            'slug' => $r['slug'],
            'title' => $r['title'],
            'excerpt' => (string) $r['excerpt'],
            'cover' => $this->coverUrls((int) $r['id'], $r['cover']),
            'category' => [
                'id' => (int) $r['id_category'],
                'name' => $r['category_name'],
                'slug' => $r['category_slug'],
            ],
            'author' => (string) $r['author'],
            'date' => $r['date_add'],
            'views' => (int) $r['views'],
            'isFeatured' => (bool) $r['is_featured'],
        ];
    }

    /* ------------------------------------------------------------------ article */

    protected function post()
    {
        $idLang = (int) $this->context->language->id;
        $defLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $slug = (string) $this->in('slug');
        $id = (int) $this->in('id');

        if (!$id && $slug !== '') {
            // NB : getValue() ajoute déjà « LIMIT 1 » -> ne pas en remettre (sinon SQL 1064).
            $id = (int) Db::getInstance()->getValue(
                'SELECT id_simpleblog_post FROM ' . _DB_PREFIX_ . 'simpleblog_post_lang
                 WHERE link_rewrite = \'' . pSQL($slug) . '\'',
                false
            );
        }
        if (!$id) {
            return ['error' => 'not_found'];
        }

        $active = (bool) Db::getInstance()->getValue(
            'SELECT active FROM ' . _DB_PREFIX_ . 'simpleblog_post WHERE id_simpleblog_post = ' . $id,
            false
        );
        if (!$active) {
            return ['error' => 'not_found'];
        }

        $row = Db::getInstance()->getRow(
            'SELECT p.id_simpleblog_post AS id, p.cover, p.author, p.is_featured, p.views,
                    p.date_add, p.date_upd, p.id_simpleblog_category AS id_category,
                    COALESCE(NULLIF(pl.title, ""), pld.title) AS title,
                    COALESCE(NULLIF(pl.link_rewrite, ""), pld.link_rewrite) AS slug,
                    COALESCE(NULLIF(pl.meta_title, ""), pld.meta_title) AS meta_title,
                    COALESCE(NULLIF(pl.meta_description, ""), pld.meta_description) AS meta_description,
                    COALESCE(NULLIF(pl.content, ""), pld.content) AS content,
                    COALESCE(NULLIF(pl.short_content, ""), pld.short_content) AS short_content,
                    COALESCE(NULLIF(pl.video_code, ""), pld.video_code) AS video_code,
                    COALESCE(NULLIF(pl.external_url, ""), pld.external_url) AS external_url,
                    COALESCE(NULLIF(cl.name, ""), cld.name) AS category_name,
                    COALESCE(NULLIF(cl.link_rewrite, ""), cld.link_rewrite) AS category_slug
             FROM ' . _DB_PREFIX_ . 'simpleblog_post p
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_post_lang pl ON (pl.id_simpleblog_post = p.id_simpleblog_post AND pl.id_lang = ' . $idLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_post_lang pld ON (pld.id_simpleblog_post = p.id_simpleblog_post AND pld.id_lang = ' . $defLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_category_lang cl ON (cl.id_simpleblog_category = p.id_simpleblog_category AND cl.id_lang = ' . $idLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_category_lang cld ON (cld.id_simpleblog_category = p.id_simpleblog_category AND cld.id_lang = ' . $defLang . ')
             WHERE p.id_simpleblog_post = ' . $id
        );
        if (!$row) {
            return ['error' => 'not_found'];
        }

        return ['post' => [
            'id' => (int) $row['id'],
            'slug' => $row['slug'],
            'title' => $row['title'],
            'metaTitle' => (string) $row['meta_title'],
            'metaDescription' => (string) $row['meta_description'],
            'excerpt' => (string) $row['short_content'],
            'content' => $this->renderContent((string) $row['content'], (string) $row['short_content']),
            'cover' => $this->coverUrls((int) $row['id'], $row['cover']),
            'category' => [
                'id' => (int) $row['id_category'],
                'name' => $row['category_name'],
                'slug' => $row['category_slug'],
            ],
            'author' => (string) $row['author'],
            'date' => $row['date_add'],
            'dateUpd' => $row['date_upd'],
            'views' => (int) $row['views'],
            'videoCode' => (string) $row['video_code'],
            'externalUrl' => (string) $row['external_url'],
            'tags' => $this->tags($id, $idLang, $defLang),
            'relatedProductIds' => $this->relatedProductIds($id),
        ]];
    }

    protected function tags($idPost, $idLang, $defLang)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT DISTINCT COALESCE(NULLIF(t.name, ""), td.name) AS name
             FROM ' . _DB_PREFIX_ . 'simpleblog_post_tag pt
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_tag t ON (t.id_simpleblog_tag = pt.id_simpleblog_tag AND t.id_lang = ' . (int) $idLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_tag td ON (td.id_simpleblog_tag = pt.id_simpleblog_tag AND td.id_lang = ' . (int) $defLang . ')
             WHERE pt.id_simpleblog_post = ' . (int) $idPost
        );
        $out = [];
        foreach ((array) $rows as $r) {
            if (!empty($r['name'])) {
                $out[] = $r['name'];
            }
        }
        return $out;
    }

    protected function relatedProductIds($idPost)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT id_product FROM ' . _DB_PREFIX_ . 'simpleblog_post_product
             WHERE id_simpleblog_post = ' . (int) $idPost
        );
        $out = [];
        foreach ((array) $rows as $r) {
            $out[] = (int) $r['id_product'];
        }
        return $out;
    }

    /* --------------------------------------------------------------- catégories */

    protected function categories()
    {
        $idLang = (int) $this->context->language->id;
        $defLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $rows = Db::getInstance()->executeS(
            'SELECT c.id_simpleblog_category AS id,
                    COALESCE(NULLIF(cl.name, ""), cld.name) AS name,
                    COALESCE(NULLIF(cl.link_rewrite, ""), cld.link_rewrite) AS slug,
                    (SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'simpleblog_post p
                       WHERE p.id_simpleblog_category = c.id_simpleblog_category AND p.active = 1) AS count
             FROM ' . _DB_PREFIX_ . 'simpleblog_category c
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_category_lang cl ON (cl.id_simpleblog_category = c.id_simpleblog_category AND cl.id_lang = ' . $idLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_category_lang cld ON (cld.id_simpleblog_category = c.id_simpleblog_category AND cld.id_lang = ' . $defLang . ')
             ORDER BY count DESC'
        );
        $out = [];
        foreach ((array) $rows as $r) {
            if (empty($r['slug'])) {
                continue;
            }
            $out[] = [
                'id' => (int) $r['id'],
                'name' => $r['name'],
                'slug' => $r['slug'],
                'count' => (int) $r['count'],
            ];
        }
        return $out;
    }

    protected function categoryIdFromSlug($slug)
    {
        return (int) Db::getInstance()->getValue(
            'SELECT id_simpleblog_category FROM ' . _DB_PREFIX_ . 'simpleblog_category_lang
             WHERE link_rewrite = \'' . pSQL($slug) . '\'',
            false
        );
    }

    protected function categoryHead($idCat)
    {
        $idLang = (int) $this->context->language->id;
        $defLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $r = Db::getInstance()->getRow(
            'SELECT COALESCE(NULLIF(cl.name, ""), cld.name) AS name,
                    COALESCE(NULLIF(cl.link_rewrite, ""), cld.link_rewrite) AS slug
             FROM ' . _DB_PREFIX_ . 'simpleblog_category c
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_category_lang cl ON (cl.id_simpleblog_category = c.id_simpleblog_category AND cl.id_lang = ' . $idLang . ')
             LEFT JOIN ' . _DB_PREFIX_ . 'simpleblog_category_lang cld ON (cld.id_simpleblog_category = c.id_simpleblog_category AND cld.id_lang = ' . $defLang . ')
             WHERE c.id_simpleblog_category = ' . (int) $idCat
        );
        if (!$r) {
            return null;
        }
        return ['id' => (int) $idCat, 'name' => $r['name'], 'slug' => $r['slug']];
    }

    /* ------------------------------------------------------------------ helpers */

    protected function baseUrl()
    {
        return rtrim(Tools::getShopDomainSsl(true) . __PS_BASE_URI__, '/');
    }

    /** URLs de cover ph_simpleblog : modules/ph_simpleblog/covers/{id}.{ext} (+ -wide, -thumb). */
    protected function coverUrls($id, $ext)
    {
        $ext = trim((string) $ext);
        if ($ext === '') {
            return null;
        }
        $dir = $this->baseUrl() . '/modules/ph_simpleblog/covers/';
        return [
            'src' => $dir . $id . '.' . $ext,
            'wide' => $dir . $id . '-wide.' . $ext,
            'thumb' => $dir . $id . '-thumb.' . $ext,
        ];
    }

    /**
     * Corps de l'article -> HTML.
     * 22 des 24 articles ont leur corps stocké en JSON Elementor (héritage migration LitExtension) :
     * on le convertit en HTML propre. Sinon on renvoie le HTML tel quel. Repli sur l'intro si vide.
     */
    protected function renderContent($content, $fallback = '')
    {
        $trimmed = ltrim($content);
        if ($trimmed === '') {
            return $this->fixUrls($fallback);
        }
        if ($trimmed[0] !== '[') {
            return $this->fixUrls($content); // déjà du HTML
        }
        $data = json_decode($content, true);
        if (!is_array($data)) {
            return $this->fixUrls($content);
        }
        $html = '';
        foreach ($data as $node) {
            $html .= $this->elementorNode($node);
        }
        if (trim($html) === '') {
            return $this->fixUrls($fallback);
        }
        // Le titre de l'article est le seul <h1> de la page front : on rétrograde
        // les <h1> hérités du contenu Elementor en <h2> (hiérarchie SEO propre).
        $html = preg_replace('/<(\/?)h1(\s|>)/i', '<$1h2$2', $html);
        return $this->fixUrls($html);
    }

    /** Convertit récursivement un nœud Elementor (section/column/widget) en HTML. */
    protected function elementorNode($node)
    {
        if (!is_array($node)) {
            return '';
        }
        $type = isset($node['widgetType']) ? $node['widgetType'] : (isset($node['elType']) ? $node['elType'] : '');
        $s = (isset($node['settings']) && is_array($node['settings'])) ? $node['settings'] : [];
        $out = '';

        switch ($type) {
            case 'heading':
                $tag = (isset($s['header_size']) && preg_match('/^h[1-6]$/', (string) $s['header_size'])) ? $s['header_size'] : 'h2';
                $title = isset($s['title']) ? trim((string) $s['title']) : '';
                if ($title !== '') {
                    $out .= '<' . $tag . '>' . $title . '</' . $tag . '>';
                }
                break;
            case 'text-editor':
                if (!empty($s['editor'])) {
                    $out .= '<div class="hfm-blog-text">' . $s['editor'] . '</div>';
                }
                break;
            case 'image':
                $img = (isset($s['image']) && is_array($s['image'])) ? $s['image'] : [];
                $url = isset($img['url']) ? trim((string) $img['url']) : '';
                if ($url !== '') {
                    $caption = isset($s['caption']) ? trim((string) $s['caption']) : '';
                    $out .= '<figure class="hfm-blog-image"><img src="' . htmlspecialchars($url, ENT_QUOTES) . '" alt="' . htmlspecialchars($caption, ENT_QUOTES) . '" loading="lazy" />';
                    if ($caption !== '') {
                        $out .= '<figcaption>' . htmlspecialchars($caption, ENT_QUOTES) . '</figcaption>';
                    }
                    $out .= '</figure>';
                }
                break;
            case 'prestashop-widget-ProductsList':
                // Widget « liste de produits » Elementor : ignoré en headless (le front a ses propres blocs).
                break;
            default:
                break;
        }

        if (!empty($node['elements']) && is_array($node['elements'])) {
            foreach ($node['elements'] as $child) {
                $out .= $this->elementorNode($child);
            }
        }
        return $out;
    }

    /** Rend absolues les URLs relatives d'images/liens du contenu (vers la base PrestaShop). */
    protected function fixUrls($html)
    {
        $base = $this->baseUrl();
        $html = str_replace(['src="/', 'href="/'], ['src="' . $base . '/', 'href="' . $base . '/'], $html);
        $html = str_replace([$base . '/http', $base . '//'], ['http', $base . '/'], $html);
        return $html;
    }
}
