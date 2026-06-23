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
            case 'add-address':
                return $this->addAddress();
            case 'update':
                return $this->updateProfile();
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
        $address->phone = (string) $this->in('phone', '');
        if (!Validate::isLoadedObject((new Country($address->id_country)))) {
            return ['error' => 'invalid_country'];
        }
        if (!$address->add()) {
            return ['error' => 'address_create_failed'];
        }
        return ['created' => true, 'id_address' => (int) $address->id, 'addresses' => $this->listAddresses($idCustomer)];
    }

    protected function listAddresses($idCustomer)
    {
        $out = [];
        $customer = new Customer((int) $idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            return $out;
        }
        foreach ($customer->getAddresses((int) $this->context->language->id) as $a) {
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
            ];
        }
        return $out;
    }

    protected function customerPayload(Customer $c)
    {
        return [
            'id_customer' => (int) $c->id,
            'email' => $c->email,
            'firstname' => $c->firstname,
            'lastname' => $c->lastname,
            'id_lang' => (int) $c->id_lang,
            'groups' => array_map('intval', $c->getGroups()),
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
