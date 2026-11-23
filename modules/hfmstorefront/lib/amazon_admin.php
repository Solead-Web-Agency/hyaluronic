<?php
/**
 * Paiements Amazon Pay vus du back-office : lecture du paiement (« charge ») et remboursement depuis la
 * fiche commande, sur le modèle du bloc Viva (lib/viva_admin.php) — demande du 18/11/2026.
 *
 * API Amazon Pay v2 signée avec les clés de l'ancien module amazonpay (AMAZONPAY_PUBLIC_KEY_ID /
 * AMAZONPAY_PRIVATE_KEY, région AMAZONPAY_REGION, AMAZONPAY_LIVEMODE). On réutilise le SDK officiel livré
 * avec ce module (modules/amazonpay/vendor) : le module est désactivé mais ses fichiers restent en place.
 *
 * Identifiant de paiement : ps_order_payment.transaction_id (commandes du nouveau site, front headless)
 * ou ps_amazonpay_orders.amazon_charge_id (commandes de l'ancien site).
 * Un remboursement Amazon est ASYNCHRONE : il part à l'état « RefundInitiated » puis passe à « Refunded »
 * (ou « Declined ») en quelques minutes à quelques heures ; l'état est relu à chaque affichage du bloc.
 * Chaque opération est journalisée dans la table hfm_amazon_refund.
 */
class HfmAmazonAdmin
{
    const SDK = 'amazonpay/vendor/autoload.php';

    public static function configured()
    {
        return (string) Configuration::get('AMAZONPAY_PUBLIC_KEY_ID') !== ''
            && (string) Configuration::get('AMAZONPAY_PRIVATE_KEY') !== ''
            && is_file(_PS_MODULE_DIR_ . self::SDK);
    }

    public static function live()
    {
        return (bool) Configuration::get('AMAZONPAY_LIVEMODE');
    }

    protected static function client()
    {
        static $client = null;
        if ($client === null) {
            require_once _PS_MODULE_DIR_ . self::SDK;
            $region = strtolower((string) Configuration::get('AMAZONPAY_REGION')) ?: 'eu';
            $client = new \Amazon\Pay\API\Client([
                'public_key_id' => (string) Configuration::get('AMAZONPAY_PUBLIC_KEY_ID'),
                'private_key' => (string) Configuration::get('AMAZONPAY_PRIVATE_KEY'),
                'region' => in_array($region, ['eu', 'de', 'uk', 'na', 'us', 'jp'], true) ? $region : 'eu',
                'sandbox' => !self::live(),
                'algorithm' => 'AMZN-PAY-RSASSA-PSS-V2',
            ]);
        }
        return $client;
    }

    public static function table()
    {
        return _DB_PREFIX_ . 'hfm_amazon_refund';
    }

    public static function ensureTable()
    {
        static $done = false;
        if ($done) {
            return;
        }
        Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . self::table() . '` (
                `id_refund` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_order` INT UNSIGNED NOT NULL,
                `charge_id` VARCHAR(64) NOT NULL,
                `refund_id` VARCHAR(64) NOT NULL DEFAULT \'\',
                `amount` DECIMAL(20,2) NOT NULL,
                `state` VARCHAR(32) NOT NULL DEFAULT \'\',
                `reason` VARCHAR(255) NOT NULL DEFAULT \'\',
                `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
                `date_add` DATETIME NOT NULL,
                `date_upd` DATETIME NOT NULL,
                KEY `id_order` (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
        $done = true;
    }

    /** Paiement Amazon de la commande : ['charge_id', 'amount'] ou null. */
    public static function orderCharge(Order $order)
    {
        $isAmazon = stripos((string) $order->payment, 'amazon') !== false || $order->module === 'amazonpay';
        if (!$isAmazon) {
            return null;
        }
        $re = '/^[A-Z]\d{2}-\d{7}-\d{7}-C\d{6,}$/';
        foreach (OrderPayment::getByOrderReference($order->reference) as $p) {
            $t = trim((string) $p->transaction_id);
            if (preg_match($re, $t)) {
                return ['charge_id' => $t, 'amount' => (float) $p->amount];
            }
        }
        $t = (string) Db::getInstance()->getValue('SELECT amazon_charge_id FROM `' . _DB_PREFIX_ . 'amazonpay_orders` WHERE id_order = '
            . (int) $order->id . ' AND amazon_charge_id <> \'\' ORDER BY id DESC');
        if (preg_match($re, $t)) {
            return ['charge_id' => $t, 'amount' => (float) $order->total_paid_real ?: (float) $order->total_paid];
        }
        return null;
    }

    /** Décode la réponse du SDK : [code HTTP, données]. Ne lève pas d'exception. */
    protected static function call(callable $fn)
    {
        try {
            $r = $fn(self::client());
        } catch (\Throwable $e) {
            return [0, ['message' => $e->getMessage()]];
        }
        $d = json_decode((string) ($r['response'] ?? ''), true);
        return [(int) ($r['status'] ?? 0), is_array($d) ? $d : []];
    }

    /** Paiement chez Amazon (état, montants capturé / remboursé), ou null si illisible. */
    public static function fetch($chargeId)
    {
        if (!self::configured()) {
            return null;
        }
        list($code, $d) = self::call(function ($c) use ($chargeId) {
            return $c->getCharge($chargeId);
        });
        if ($code !== 200 || empty($d['chargeId'])) {
            return null;
        }
        return [
            'state' => (string) ($d['statusDetails']['state'] ?? ''),
            'reason' => (string) ($d['statusDetails']['reasonCode'] ?? ''),
            'charged' => (float) ($d['chargeAmount']['amount'] ?? 0),
            'captured' => (float) ($d['captureAmount']['amount'] ?? 0),
            'refunded' => (float) ($d['refundedAmount']['amount'] ?? 0),
            'currency' => (string) ($d['chargeAmount']['currencyCode'] ?? 'EUR'),
            'date' => (string) ($d['creationTimestamp'] ?? ''),
        ];
    }

    public static function chargeStateLabel($s)
    {
        $labels = ['Captured' => 'Payé (capturé)', 'Authorized' => 'Autorisé, non capturé', 'AuthorizationInitiated' => 'Autorisation en cours',
            'CaptureInitiated' => 'Capture en cours', 'Canceled' => 'Annulé', 'Declined' => 'Refusé'];
        return $labels[$s] ?? ($s !== '' ? $s : 'inconnu');
    }

    public static function refundStateLabel($s)
    {
        $labels = ['RefundInitiated' => 'en cours chez Amazon', 'Refunded' => 'effectué', 'Declined' => 'REFUSÉ par Amazon'];
        return $labels[$s] ?? $s;
    }

    /** Remboursements journalisés de la commande ; ceux encore « en cours » sont relus chez Amazon. */
    public static function refunds($idOrder)
    {
        self::ensureTable();
        $rows = Db::getInstance()->executeS('SELECT * FROM `' . self::table() . '` WHERE id_order = ' . (int) $idOrder . ' ORDER BY id_refund') ?: [];
        foreach ($rows as &$r) {
            if ($r['state'] === 'RefundInitiated' && $r['refund_id'] !== '' && self::configured()) {
                $id = $r['refund_id'];
                list($code, $d) = self::call(function ($c) use ($id) {
                    return $c->getRefund($id);
                });
                $state = (string) ($d['statusDetails']['state'] ?? '');
                if ($code === 200 && $state !== '' && $state !== $r['state']) {
                    $r['state'] = $state;
                    Db::getInstance()->update('hfm_amazon_refund', ['state' => pSQL($state), 'date_upd' => date('Y-m-d H:i:s')], 'id_refund = ' . (int) $r['id_refund']);
                }
            }
        }
        return $rows;
    }

    /** Total déjà remboursé selon notre journal (hors remboursements refusés). */
    public static function refundedTotal($idOrder, array $rows = null)
    {
        $sum = 0.0;
        foreach ($rows ?? self::refunds($idOrder) as $r) {
            if ($r['state'] !== 'Declined') {
                $sum += (float) $r['amount'];
            }
        }
        return round($sum, 2);
    }

    /**
     * Rembourse $amount (0 = tout le restant) du paiement. Renvoie ['ok' => bool, 'message' => string].
     * Total -> état « Remboursé » (e-mail natif au client) ; partiel -> note privée.
     */
    public static function refund(Order $order, $amount, $reason, $idEmployee)
    {
        $ch = self::orderCharge($order);
        if (!$ch) {
            return ['ok' => false, 'message' => 'Aucun paiement Amazon Pay sur cette commande.'];
        }
        if (!self::configured()) {
            return ['ok' => false, 'message' => 'Clés Amazon Pay absentes (AMAZONPAY_PUBLIC_KEY_ID / AMAZONPAY_PRIVATE_KEY) ou SDK introuvable.'];
        }
        // Montants de référence lus chez Amazon (couvre aussi les remboursements faits ailleurs : Seller Central, ancien module).
        $info = self::fetch($ch['charge_id']);
        if (!$info) {
            return ['ok' => false, 'message' => 'Paiement illisible chez Amazon : réessayez dans un instant.'];
        }
        if ($info['state'] !== 'Captured') {
            return ['ok' => false, 'message' => 'Paiement non remboursable dans son état actuel (' . self::chargeStateLabel($info['state']) . ').'];
        }
        $captured = $info['captured'] > 0 ? $info['captured'] : $ch['amount'];
        $already = max($info['refunded'], self::refundedTotal((int) $order->id));
        $left = round($captured - $already, 2);
        $amount = $amount > 0 ? round($amount, 2) : $left;
        if ($amount <= 0 || $amount > $left + 0.001) {
            return ['ok' => false, 'message' => sprintf('Montant invalide : %.2f € remboursables au plus.', max(0, $left))];
        }
        $reasonTxt = Tools::substr(trim((string) $reason), 0, 255);
        $payload = [
            'chargeId' => $ch['charge_id'],
            'refundAmount' => ['amount' => number_format($amount, 2, '.', ''), 'currencyCode' => $info['currency'] ?: 'EUR'],
        ];
        $key = 'hfm-' . (int) $order->id . '-' . bin2hex(random_bytes(8));
        list($code, $d) = self::call(function ($c) use ($payload, $key) {
            return $c->createRefund($payload, ['x-amz-pay-idempotency-key' => $key]);
        });
        if ($code < 200 || $code >= 300 || empty($d['refundId'])) {
            $err = trim((string) ($d['reasonCode'] ?? '') . ' ' . (string) ($d['message'] ?? '')) ?: ('HTTP ' . $code);
            PrestaShopLogger::addLog('HFM Amazon Pay : remboursement refusé (commande ' . (int) $order->id . ') : ' . $err, 3, null, 'Order', (int) $order->id, true);
            return ['ok' => false, 'message' => 'Amazon a refusé l\'opération : ' . $err];
        }
        self::ensureTable();
        $now = date('Y-m-d H:i:s');
        Db::getInstance()->insert('hfm_amazon_refund', [
            'id_order' => (int) $order->id,
            'charge_id' => pSQL($ch['charge_id']),
            'refund_id' => pSQL((string) $d['refundId']),
            'amount' => (float) $amount,
            'state' => pSQL((string) ($d['statusDetails']['state'] ?? 'RefundInitiated')),
            'reason' => pSQL($reasonTxt),
            'id_employee' => (int) $idEmployee,
            'date_add' => $now,
            'date_upd' => $now,
        ]);
        $full = ($already + $amount) >= $captured - 0.001;
        $label = sprintf('%.2f €', $amount);
        if ($full && ($state = (int) Configuration::get('PS_OS_REFUND')) && (int) $order->current_state !== $state) {
            $h = new OrderHistory();
            $h->id_order = (int) $order->id;
            $h->id_employee = (int) $idEmployee;
            $h->changeIdOrderState($state, $order, true);
            $h->addWithemail(true);
        }
        $m = new Message();
        $m->id_order = (int) $order->id;
        $m->id_employee = (int) $idEmployee;
        $m->private = 1;
        $m->message = 'Amazon Pay : ' . ($full ? 'remboursement total' : 'remboursement partiel') . ' de ' . $label
            . ' (' . $d['refundId'] . ')' . ($reasonTxt !== '' ? ' — ' . Tools::substr($reasonTxt, 0, 200) : '');
        $m->add();
        return ['ok' => true, 'message' => ($full ? 'Remboursement total' : 'Remboursement partiel') . ' de ' . $label
            . ' demandé à Amazon (traitement en quelques minutes ; l\'état se met à jour dans ce bloc).'];
    }
}
