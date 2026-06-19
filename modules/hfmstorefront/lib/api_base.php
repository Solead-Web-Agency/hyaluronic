<?php
/**
 * Base commune des contrôleurs d'API headless HFM.
 * - Auth par secret partagé (en-tête X-Storefront-Token) : appels serveur-à-serveur depuis Next.js
 * - CORS pour les origines configurées
 * - Entrée/sortie JSON, dispatch par méthode HTTP (get()/post()/...)
 * On n'appelle PAS parent::init() pour éviter le rendu thème / redirections SSL : on lie le contexte à la main.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class HfmStorefrontApiController extends ModuleFrontController
{
    /** @var array données fusionnées (corps JSON + query string) */
    protected $input = [];

    public function init()
    {
        $this->handleCors();
        if (Tools::strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
        if (!$this->checkSecret()) {
            $this->respond(['error' => 'unauthorized'], 401);
        }

        // Initialise le contexte + le conteneur Symfony (nécessaire aux opérations panier/prix PS9),
        // puis on rendra du JSON et on sortira avant tout rendu de thème.
        parent::init();

        $this->input = $this->readInput();
        $this->bindContext();

        // Préfixe "handle" pour éviter toute collision avec les méthodes du Controller PS (ex: get($serviceId))
        $handler = 'handle' . Tools::ucfirst(Tools::strtolower($_SERVER['REQUEST_METHOD']));
        if (!method_exists($this, $handler)) {
            $this->respond(['error' => 'method_not_allowed'], 405);
        }
        try {
            $this->respond($this->{$handler}(), 200);
        } catch (Exception $e) {
            $this->respond(['error' => $e->getMessage()], 400);
        }
    }

    protected function handleCors()
    {
        $allowed = array_filter(array_map('trim', explode(',', (string) Configuration::get('HFMSTOREFRONT_CORS'))));
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if ($origin && in_array($origin, $allowed, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
        }
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Storefront-Token');
    }

    protected function checkSecret()
    {
        $expected = (string) Configuration::get('HFMSTOREFRONT_SECRET');
        $given = isset($_SERVER['HTTP_X_STOREFRONT_TOKEN']) ? $_SERVER['HTTP_X_STOREFRONT_TOKEN'] : '';
        return $expected !== '' && hash_equals($expected, (string) $given);
    }

    protected function readInput()
    {
        $body = [];
        $raw = Tools::file_get_contents('php://input');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }
        return array_merge($_GET, $body);
    }

    /** Lie langue/devise/boutique/panier/client au contexte à partir des paramètres reçus. */
    protected function bindContext()
    {
        $ctx = $this->context;
        if ($idLang = (int) $this->in('id_lang')) {
            $ctx->language = new Language($idLang);
        }
        if ($idCurrency = (int) $this->in('id_currency')) {
            $ctx->currency = new Currency($idCurrency);
        }
        if ($idCustomer = (int) $this->in('id_customer')) {
            $customer = new Customer($idCustomer);
            if (Validate::isLoadedObject($customer)) {
                $ctx->customer = $customer;
            }
        }
    }

    protected function in($key, $default = null)
    {
        return array_key_exists($key, $this->input) ? $this->input[$key] : $default;
    }

    protected function respond($data, $code = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
