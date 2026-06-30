<?php
/**
 * Endpoint client headless : authentification, inscription, profil, adresses.
 * Appelé serveur-à-serveur par le backend Next.js (en-tête X-Storefront-Token).
 *   POST {action:login,    email, password}
 *   POST {action:register, email, password, firstname, lastname, id_lang?}
 *   GET  ?action=me&id_customer=..
 *   GET  ?action=addresses&id_customer=..
 *   POST {action:add-address, id_customer, alias, firstname, lastname, address1, postcode, city, id_country, phone?}
 */
require_once _PS_MODULE_DIR_ . 'hfmstorefront/lib/api_base.php';

class HfmstorefrontCustomerModuleFrontController extends HfmStorefrontApiController
{
    public function handlePost()
    {
        $action = (string) $this->in('action');
        switch ($action) {
            case 'login':
                return $this->login();
            case 'register':
                return $this->register();
            case 'guest':
                return $this->registerGuest();
            case 'add-address':
                return $this->addAddress();
            case 'update-address':
                return $this->updateAddress();
            case 'delete-address':
                return $this->deleteAddress();
            case 'update':
                return $this->updateProfile();
            case 'set-rpps':
                return $this->setRpps();
            case 'set-pro-attestation':
                return $this->setProAttestation();
            default:
                return ['error' => 'unknown_action'];
        }
    }

    public function handleGet()
    {
        $action = (string) $this->in('action');
        $idCustomer = (int) $this->in('id_customer');
        if ($action === 'addresses') {
            return ['addresses' => $this->listAddresses($idCustomer)];
        }
        // me
        $customer = new Customer($idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            return ['error' => 'customer_not_found'];
        }
        return ['customer' => $this->customerPayload($customer)];
    }

    protected function login()
    {
        $email = (string) $this->in('email');
        $password = (string) $this->in('password');
        if (!Validate::isEmail($email) || $password === '') {
            return ['error' => 'invalid_credentials_format'];
        }
        $customer = (new Customer())->getByEmail($email, $password);
        if (!$customer || !Validate::isLoadedObject($customer)) {
            return ['authenticated' => false, 'error' => 'bad_credentials'];
        }
        return ['authenticated' => true, 'customer' => $this->customerPayload($customer)];
    }

    protected function register()
    {
        $email = (string) $this->in('email');
        $password = (string) $this->in('password');
        if (!Validate::isEmail($email) || Tools::strlen($password) < 5) {
            return ['error' => 'invalid_email_or_password'];
        }
        if (Customer::customerExists($email)) {
            return ['error' => 'email_already_exists'];
        }
        $customer = new Customer();
        $customer->email = $email;
        $customer->passwd = $this->hashPassword($password);
        $customer->firstname = (string) $this->in('firstname', 'Client');
        $customer->lastname = (string) $this->in('lastname', 'Client');
        $customer->id_lang = (int) ($this->in('id_lang') ?: $this->context->language->id);
        $customer->id_shop = (int) $this->context->shop->id;
        $customer->id_shop_group = (int) $this->context->shop->id_shop_group;
        if (!$customer->add()) {
            return ['error' => 'create_failed'];
        }
        return ['created' => true, 'customer' => $this->customerPayload($customer)];
    }

    /** Création d'un client INVITÉ (commande sans compte) : pas de mot de passe à choisir. */
    protected function registerGuest()
    {
        $email = (string) $this->in('email');
        if (!Validate::isEmail($email)) {
            return ['error' => 'invalid_email'];
        }
        $firstname = (string) $this->in('firstname', 'Client');
        $lastname = (string) $this->in('lastname', 'Client');
        if (!Validate::isName($firstname) || !Validate::isName($lastname)) {
            return ['error' => 'invalid_name'];
        }
        // Si un VRAI compte existe déjà avec cet e-mail, on invite à se connecter.
        if (Customer::customerExists($email)) {
            $existing = (new Customer())->getByEmail($email);
            if ($existing && !$existing->is_guest) {
                return ['error' => 'email_already_exists'];
            }
        }
        // Réutilise un INVITÉ existant pour ce même e-mail (cas édition) au lieu de créer un doublon.
        $existingGuest = (new Customer())->getByEmail($email, null, false);
        if (Validate::isLoadedObject($existingGuest) && $existingGuest->is_guest) {
            $existingGuest->firstname = $firstname;
            $existingGuest->lastname = $lastname;
            $existingGuest->id_lang = (int) ($this->in('id_lang') ?: $existingGuest->id_lang);
            $existingGuest->update();
            return ['created' => false, 'guest' => true, 'customer' => $this->customerPayload($existingGuest)];
        }
        $customer = new Customer();
        $customer->email = $email;
        $customer->passwd = $this->hashPassword(Tools::passwdGen(16));
        $customer->firstname = $firstname;
        $customer->lastname = $lastname;
        $customer->is_guest = 1;
        $customer->id_default_group = (int) (Configuration::get('PS_GUEST_GROUP') ?: Configuration::get('PS_CUSTOMER_GROUP'));
        $customer->id_lang = (int) ($this->in('id_lang') ?: $this->context->language->id);
        $customer->id_shop = (int) $this->context->shop->id;
        $customer->id_shop_group = (int) $this->context->shop->id_shop_group;
        if (!$customer->add()) {
            return ['error' => 'create_failed'];
        }
        return ['created' => true, 'guest' => true, 'customer' => $this->customerPayload($customer)];
    }

    /** Mise à jour du profil (prénom/nom/e-mail, et mot de passe si fourni). id_customer imposé par la session. */
    protected function updateProfile()
    {
        $idCustomer = (int) $this->in('id_customer');
        $customer = new Customer($idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            return ['error' => 'customer_not_found'];
        }
        $firstname = (string) $this->in('firstname', $customer->firstname);
        $lastname = (string) $this->in('lastname', $customer->lastname);
        $email = (string) $this->in('email', $customer->email);
        if (!Validate::isName($firstname) || !Validate::isName($lastname)) {
            return ['error' => 'invalid_name'];
        }
        if (!Validate::isEmail($email)) {
            return ['error' => 'invalid_email'];
        }
        if ($email !== $customer->email && Customer::customerExists($email)) {
            return ['error' => 'email_already_exists'];
        }
        $customer->firstname = $firstname;
        $customer->lastname = $lastname;
        $customer->email = $email;
        $newPassword = (string) $this->in('password', '');
        if ($newPassword !== '') {
            if (Tools::strlen($newPassword) < 5) {
                return ['error' => 'password_too_short'];
            }
            $customer->passwd = $this->hashPassword($newPassword);
        }
        if (!$customer->update()) {
            return ['error' => 'update_failed'];
        }
        return ['updated' => true, 'customer' => $this->customerPayload($customer)];
    }

    protected function addAddress()
    {
        $idCustomer = (int) $this->in('id_customer');
        $customer = new Customer($idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            return ['error' => 'customer_not_found'];
        }
        $address = new Address();
        $address->id_customer = $idCustomer;
        $address->alias = (string) $this->in('alias', 'Mon adresse');
        $address->firstname = (string) $this->in('firstname', $customer->firstname);
        $address->lastname = (string) $this->in('lastname', $customer->lastname);
        $address->address1 = (string) $this->in('address1');
        $address->postcode = (string) $this->in('postcode');
        $address->city = (string) $this->in('city');
        $address->id_country = (int) $this->in('id_country');
        // Société + n° de TVA intracommunautaire (B2B). S'ils sont renseignés, le module
        // Advanced VAT Manager valide le numéro via VIES/GOV.UK au moment du add()
        // (hook actionObjectAddAfter) et, si valide, exonère le client (passage groupe B2B).
        // On ne réimplémente pas VIES : on pose les champs, le moteur du module fait le reste.
        $company = trim((string) $this->in('company', ''));
        if ($company !== '') {
            $address->company = $company;
        }
        $vat = trim((string) $this->in('vat_number', ''));
        if ($vat !== '') {
            $address->vat_number = $vat;
        }
        // Téléphone OBLIGATOIRE.
        $phone = trim((string) $this->in('phone', ''));
        if ($phone === '' || !Validate::isPhoneNumber($phone)) {
            return ['error' => 'invalid_phone'];
        }
        $address->phone = $phone;
        if (!Validate::isLoadedObject((new Country($address->id_country)))) {
            return ['error' => 'invalid_country'];
        }
        if (!$address->add()) {
            return ['error' => 'address_create_failed'];
        }
        return [
            'created' => true,
            'id_address' => (int) $address->id,
            'vat' => $this->vatStatus($idCustomer, (int) $address->id, (int) $address->id_country, $vat),
            'addresses' => $this->listAddresses($idCustomer),
        ];
    }

    protected function updateAddress()
    {
        $idCustomer = (int) $this->in('id_customer');
        $idAddress = (int) $this->in('id_address');
        $old = new Address($idAddress);
        // Sécurité : l'adresse doit appartenir au client de la session.
        if (!Validate::isLoadedObject($old) || (int) $old->id_customer !== $idCustomer) {
            return ['error' => 'address_not_found'];
        }
        $phone = trim((string) $this->in('phone', $old->phone));
        if ($phone === '' || !Validate::isPhoneNumber($phone)) {
            return ['error' => 'invalid_phone'];
        }
        $idCountry = (int) $this->in('id_country', (int) $old->id_country);
        if (!Validate::isLoadedObject(new Country($idCountry))) {
            return ['error' => 'invalid_country'];
        }
        // Si l'adresse est déjà rattachée à une commande, PrestaShop impose d'en créer une
        // nouvelle (préservation de l'historique de commande) et de masquer l'ancienne.
        $used = (bool) $old->isUsed();
        $address = $used ? new Address() : $old;
        $address->id_customer = $idCustomer;
        $address->alias = (string) $this->in('alias', $old->alias ?: 'Mon adresse');
        $address->firstname = (string) $this->in('firstname', $old->firstname);
        $address->lastname = (string) $this->in('lastname', $old->lastname);
        $address->address1 = (string) $this->in('address1', $old->address1);
        $address->postcode = (string) $this->in('postcode', $old->postcode);
        $address->city = (string) $this->in('city', $old->city);
        $address->id_country = $idCountry;
        $address->phone = $phone;
        // Société + TVA : le hook Advanced VAT Manager re-valide via VIES/GOV.UK à l'update().
        $address->company = (string) $this->in('company', '');
        $address->vat_number = trim((string) $this->in('vat_number', ''));
        $ok = $used ? $address->add() : $address->update();
        if (!$ok) {
            return ['error' => 'address_update_failed'];
        }
        if ($used) {
            $old->deleted = 1;
            $old->update();
        }
        return [
            'updated' => true,
            'id_address' => (int) $address->id,
            'vat' => $this->vatStatus($idCustomer, (int) $address->id, (int) $address->id_country, (string) $address->vat_number),
            'addresses' => $this->listAddresses($idCustomer),
        ];
    }

    protected function deleteAddress()
    {
        $idCustomer = (int) $this->in('id_customer');
        $idAddress = (int) $this->in('id_address');
        $address = new Address($idAddress);
        if (!Validate::isLoadedObject($address) || (int) $address->id_customer !== $idCustomer) {
            return ['error' => 'address_not_found'];
        }
        // Soft-delete : PrestaShop conserve l'adresse en base si elle est liée à une commande.
        $address->deleted = 1;
        if (!$address->update()) {
            return ['error' => 'address_delete_failed'];
        }
        return ['deleted' => true, 'addresses' => $this->listAddresses($idCustomer)];
    }

    /**
     * Statut de validation TVA d'une adresse APRÈS son enregistrement : le module
     * Advanced VAT Manager a déjà appelé VIES/GOV.UK dans le hook actionObjectAddAfter.
     * Renvoie null si pas de TVA saisie, ou un statut { number, valid, invalid, exempt, available }.
     */
    protected function vatStatus($idCustomer, $idAddress, $idCountry, $vat)
    {
        if ($vat === '') {
            return null;
        }
        $cvat = _PS_MODULE_DIR_ . 'advancedvatmanager/classes/CustomersVAT.php';
        $eng = _PS_MODULE_DIR_ . 'advancedvatmanager/classes/ValidationEngine.php';
        if (!file_exists($cvat) || !file_exists($eng)) {
            return ['number' => $vat, 'valid' => false, 'invalid' => false, 'exempt' => false, 'available' => false];
        }
        require_once $cvat;
        require_once $eng;
        $valid = (bool) CustomersVAT::checkCustomerVATValid($idCustomer, $idAddress);
        $invalid = (bool) CustomersVAT::checkCustomerVATInvalid($idCustomer, $idAddress);
        $exempt = $valid ? (bool) ValidationEngine::checkNoTax($idCustomer, $idAddress, $idCountry) : false;
        return ['number' => $vat, 'valid' => $valid, 'invalid' => $invalid, 'exempt' => $exempt, 'available' => true];
    }

    protected function listAddresses($idCustomer)
    {
        $out = [];
        $customer = new Customer((int) $idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            return $out;
        }
        $hasCvat = file_exists(_PS_MODULE_DIR_ . 'advancedvatmanager/classes/CustomersVAT.php');
        if ($hasCvat) {
            require_once _PS_MODULE_DIR_ . 'advancedvatmanager/classes/CustomersVAT.php';
        }
        $hasEng = file_exists(_PS_MODULE_DIR_ . 'advancedvatmanager/classes/ValidationEngine.php');
        if ($hasEng) {
            require_once _PS_MODULE_DIR_ . 'advancedvatmanager/classes/ValidationEngine.php';
        }
        foreach ($customer->getAddresses((int) $this->context->language->id) as $a) {
            $vatNum = isset($a['vat_number']) ? (string) $a['vat_number'] : '';
            // Un pays NON listé dans ADVANCEDVATMANAGER_COUNTRY (ex. la France, pays de la boutique)
            // n'est pas soumis à validation : la TVA y est domestique, aucune exonération possible.
            $checkable = $hasEng ? !ValidationEngine::skipVATFieldBycountry((int) $a['id_country']) : true;
            $out[] = [
                'id_address' => (int) $a['id_address'],
                'alias' => $a['alias'],
                'firstname' => $a['firstname'],
                'lastname' => $a['lastname'],
                'address1' => $a['address1'],
                'postcode' => $a['postcode'],
                'city' => $a['city'],
                'country' => $a['country'],
                'id_country' => (int) $a['id_country'],
                'phone' => $a['phone'],
                'company' => isset($a['company']) ? (string) $a['company'] : '',
                'vat_number' => $vatNum,
                'vat_checkable' => (bool) $checkable,
                'vat_valid' => ($hasCvat && $vatNum !== '') ? (bool) CustomersVAT::checkCustomerVATValid((int) $idCustomer, (int) $a['id_address']) : false,
                'vat_invalid' => ($hasCvat && $vatNum !== '') ? (bool) CustomersVAT::checkCustomerVATInvalid((int) $idCustomer, (int) $a['id_address']) : false,
            ];
        }
        return $out;
    }

    /** Enregistre le numéro RPPS du client (optionnel ; sert aux produits qui l'exigent). */
    protected function setRpps()
    {
        $idCustomer = (int) $this->in('id_customer');
        if (!$idCustomer) {
            return ['error' => 'unauthenticated'];
        }
        $rpps = trim((string) $this->in('rpps'));
        // Format permissif uniquement (pas de blocage registre : confirmation en back-office).
        if ($rpps !== '' && !$this->isValidRpps($rpps)) {
            return ['error' => 'invalid_rpps'];
        }
        $this->setCustomerRpps($idCustomer, $rpps);
        // Indice (non bloquant) : rapprochement au registre pour info.
        $info = $rpps !== '' ? $this->rppsRegistryInfo($rpps) : null;
        return ['ok' => true, 'rpps' => $rpps, 'practitioner' => $info, 'registry_match' => (bool) $info];
    }

    /**
     * Attestation "professionnel de santé" : alternative au numéro RPPS au checkout.
     * { attestation:1, doc_name?, doc_data? (base64, justificatif optionnel) }
     */
    protected function setProAttestation()
    {
        $idCustomer = (int) $this->in('id_customer');
        if (!$idCustomer) {
            return ['error' => 'unauthenticated'];
        }
        if ((int) $this->in('attestation') !== 1) {
            return ['error' => 'attestation_required'];
        }
        $docFile = null;
        $data = (string) $this->in('doc_data');
        if ($data !== '') {
            $saved = $this->saveProDoc($idCustomer, (string) $this->in('doc_name'), $data);
            if (isset($saved['error'])) {
                return $saved;
            }
            $docFile = $saved['file'];
        }
        $this->setCustomerProAttestation($idCustomer, true, $docFile);
        return ['ok' => true, 'attestation' => 1, 'doc' => $docFile];
    }

    /** Enregistre un justificatif (base64) dans modules/hfmstorefront/pro_docs/ (nom aléatoire non listable). */
    protected function saveProDoc($idCustomer, $name, $base64)
    {
        $b64 = preg_replace('#^data:[^;]+;base64,#', '', $base64);
        $bin = base64_decode($b64, true);
        if ($bin === false || $bin === '') {
            return ['error' => 'invalid_doc'];
        }
        if (strlen($bin) > 6 * 1024 * 1024) {
            return ['error' => 'doc_too_large'];
        }
        $ext = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'heic'], true)) {
            $ext = 'pdf';
        }
        $dir = _PS_MODULE_DIR_ . 'hfmstorefront/pro_docs/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        // Empêche le listing du dossier (le nom de fichier reste aléatoire et non devinable).
        if (!file_exists($dir . 'index.php')) {
            @file_put_contents($dir . 'index.php', "<?php header('HTTP/1.1 403 Forbidden'); exit;");
        }
        $file = 'pro-' . (int) $idCustomer . '-' . Tools::passwdGen(24) . '.' . $ext;
        if (@file_put_contents($dir . $file, $bin) === false) {
            return ['error' => 'doc_save_failed'];
        }
        return ['file' => $file];
    }

    protected function customerPayload(Customer $c)
    {
        // RPPS/attestation effectifs : compte d'abord, sinon mémorisation par email.
        $pro = $this->resolveProData((int) $c->id, $c->email);
        $validated = $this->isValidRpps($pro['rpps']) || (int) $pro['attestation'] === 1;
        return [
            'id_customer' => (int) $c->id,
            'email' => $c->email,
            'firstname' => $c->firstname,
            'lastname' => $c->lastname,
            'id_lang' => (int) $c->id_lang,
            'is_guest' => (int) $c->is_guest,
            'groups' => array_map('intval', $c->getGroups()),
            'rpps' => $pro['rpps'],
            'pro_attestation' => (int) $pro['attestation'],
            'rpps_validated' => $validated,
        ];
    }

    /** Hash PS9 du mot de passe via le service de crypto. */
    protected function hashPassword($plain)
    {
        try {
            $crypto = $this->get('hashing'); // service PS9
            if (is_object($crypto) && method_exists($crypto, 'hash')) {
                return $crypto->hash($plain, _COOKIE_KEY_);
            }
        } catch (\Throwable $e) {
            // fallback
        }
        return Tools::hash($plain);
    }
}
