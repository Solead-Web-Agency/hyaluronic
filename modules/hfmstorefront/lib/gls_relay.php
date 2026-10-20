<?php
/**
 * Point relais GLS pour le tunnel headless.
 *
 * Sur l'ancien site, le module nkmgls affichait le choix du relais dans le tunnel PrestaShop et
 * l'enregistrait dans `gls_cart_carrier`. À la validation de la commande, son hook
 * actionValidateOrder remplace l'adresse de livraison par celle du relais et envoie l'email GLS.
 * Ici on reproduit seulement la recherche et l'enregistrement (mêmes appels et même ligne que
 * nkmgls/controllers/front/checkout.php) : la suite reste assurée par le module.
 */
class HfmGlsRelay
{
    protected static $module = false;

    /** Module nkmgls actif et chargé (sinon : pas de point relais, transporteur traité normalement). */
    public static function module()
    {
        if (self::$module === false) {
            $m = Module::isEnabled('nkmgls') ? Module::getInstanceByName('nkmgls') : null;
            if ($m) {
                require_once _PS_MODULE_DIR_ . 'nkmgls/vendor/autoload.php';
            }
            self::$module = $m ?: null;
        }
        return self::$module;
    }

    public static function relayCarrierId()
    {
        return self::module() ? (int) Configuration::get('GLS_GLSRELAIS_ID') : 0;
    }

    public static function isRelayCarrier($idCarrier)
    {
        $id = self::relayCarrierId();
        return $id > 0 && (int) $idCarrier === $id;
    }

    protected static function api()
    {
        return \Nukium\GLS\Common\Legacy\GlsController::createInstance(self::module()->getConfigFormValues());
    }

    /** Relais proches de l'adresse de livraison du panier (ou du code postal / de la ville saisis). */
    public static function search(Cart $cart, $postcode = '', $city = '')
    {
        $address = new Address((int) $cart->id_address_delivery);
        if (!Validate::isLoadedObject($address)) {
            return ['error' => 'address_required'];
        }
        $country = new Country((int) $address->id_country);
        // Sans saisie : code postal et ville de l'adresse de livraison.
        if (trim((string) $postcode) === '') {
            $postcode = (string) $address->postcode;
            $city = (string) $address->city;
        }
        $postcode = trim((string) $postcode);
        $city = trim((string) $city);
        if (!$country->checkZipCode($postcode)) {
            return ['error' => 'invalid_postcode'];
        }
        $cfg = self::module()->getConfigFormValues();
        $res = self::api()->searchRelay($postcode, $city, $country->iso_code, '', ['only_xl' => (int) ($cfg['GLS_GLSRELAIS_XL_ONLY'] ?? 0) === 1]);
        if (!isset($res->exitCode->ErrorCode)) {
            return ['error' => 'service_unavailable'];
        }
        if ((int) $res->exitCode->ErrorCode !== 0) {
            return ['error' => 'not_found', 'relays' => []];
        }
        $out = [];
        foreach ((array) ($res->SearchResults ?? []) as $r) {
            $p = $r->Parcelshop;
            $a = $p->Address;
            $hours = [];
            foreach ((array) ($p->GLSWorkingDay ?? []) as $d) {
                $hours[] = [
                    'day' => (int) $d->Day,
                    'from' => (string) $d->OpeningHours->Hours->From,
                    'to' => (string) $d->OpeningHours->Hours->To,
                    'break_from' => (string) ($d->Breaks->Hours->From ?? ''),
                    'break_to' => (string) ($d->Breaks->Hours->To ?? ''),
                ];
            }
            $out[] = [
                'id' => (string) $p->ParcelShopId,
                'name' => trim($a->Name1 . ' ' . $a->Name2),
                'street' => trim($a->Street1 . ' ' . $a->BlockNo1),
                'postcode' => (string) $a->ZipCode,
                'city' => (string) $a->City,
                'country' => (string) $a->Country,
                'lat' => (float) $p->GLSCoordinates->Latitude,
                'lng' => (float) $p->GLSCoordinates->Longitude,
                'distance_km' => round((float) $r->AirLineDistance, 2),
                'hours' => $hours,
            ];
        }
        return ['relays' => $out];
    }

    /** Enregistre le relais choisi pour le panier (même ligne que le module, lue à la validation). */
    public static function select(Cart $cart, $relayId)
    {
        $relayId = trim((string) $relayId);
        if ($relayId === '' || !preg_match('/^[A-Za-z0-9_\-]{3,64}$/', $relayId)) {
            return ['error' => 'invalid_relay'];
        }
        $d = self::api()->getRelayDetail($relayId);
        if (!$d) {
            return ['error' => 'relay_not_found'];
        }
        $idCarrier = self::relayCarrierId();
        $address = new Address((int) $cart->id_address_delivery);
        $mobile = Validate::isLoadedObject($address) ? ($address->phone_mobile ?: $address->phone) : '';
        $db = Db::getInstance();
        $db->delete('gls_cart_carrier', 'id_cart = ' . (int) $cart->id);
        $ok = $db->execute(
            'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'gls_cart_carrier` VALUES ('
            . (int) $cart->id . ', ' . (int) $cart->id_customer . ', ' . $idCarrier . ', \''
            . pSQL(self::module()->getGlsProductCode($idCarrier, $d['Country'])) . '\', \''
            . pSQL($relayId) . '\', \'' . pSQL($d['Name1']) . '\', \'' . pSQL($d['Street1']) . '\', \''
            . pSQL($d['Street2']) . '\', \'' . pSQL($d['ZipCode']) . '\', \'' . pSQL($d['City']) . '\', \''
            . pSQL($d['Phone']) . '\', \'' . pSQL($d['Mobile']) . '\', \'' . pSQL((string) $mobile) . '\', '
            . (int) Country::getByIso($d['Country']) . ', \'' . pSQL(json_encode($d['GLSWorkingDay'])) . '\')'
        );
        return $ok ? ['ok' => true, 'relay' => self::selected($cart)] : ['error' => 'save_failed'];
    }

    /** Relais déjà choisi pour ce panier (et ce transporteur), ou null. */
    public static function selected(Cart $cart)
    {
        if (!self::module()) {
            return null;
        }
        $row = Db::getInstance()->getRow(
            'SELECT parcel_shop_id, name, address1, postcode, city FROM `' . _DB_PREFIX_ . 'gls_cart_carrier`
             WHERE id_cart = ' . (int) $cart->id . ' AND id_carrier = ' . self::relayCarrierId() . ' AND parcel_shop_id IS NOT NULL AND parcel_shop_id <> \'\''
        );
        return $row ? [
            'id' => (string) $row['parcel_shop_id'],
            'name' => (string) $row['name'],
            'street' => (string) $row['address1'],
            'postcode' => (string) $row['postcode'],
            'city' => (string) $row['city'],
        ] : null;
    }
}
