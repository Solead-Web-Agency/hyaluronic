<?php
/**
 * Paiements Viva (carte bancaire) vus du back-office : lecture d'une transaction et
 * annulation / remboursement depuis la fiche commande (demande du 17/11/2026, présentation
 * reprise du bloc PayPlug de l'ancien site).
 *
 * API « classique » de Viva, comme le front (hfm-front/lib/viva.ts) : Basic auth Merchant ID + clé
 * d'API, réglages HFM_VIVA_MERCHANT_ID / HFM_VIVA_API_KEY / HFM_VIVA_ENV (live | demo) en configuration.
 * Annuler et rembourser passent par le MÊME appel (DELETE /api/transactions/{id}?amount=…) : Viva
 * annule si la transaction n'est pas encore compensée, rembourse sinon.
 * Chaque opération est journalisée dans la table hfm_viva_refund (preuve, montant déjà remboursé).
 */
class HfmVivaAdmin
{
    public static function configured()
    {
        return (string) Configuration::get('HFM_VIVA_MERCHANT_ID') !== '' && (string) Configuration::get('HFM_VIVA_API_KEY') !== '';
    }

    public static function env()
    {
        return Configuration::get('HFM_VIVA_ENV') === 'demo' ? 'demo' : 'live';
    }

    protected static function host()
    {
        return self::env() === 'demo' ? 'https://demo.vivapayments.com' : 'https://www.vivapayments.com';
    }

    public static function table()
    {
        return _DB_PREFIX_ . 'hfm_viva_refund';
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
                `transaction_id` VARCHAR(64) NOT NULL,
                `refund_transaction_id` VARCHAR(64) NOT NULL DEFAULT \'\',
                `amount` DECIMAL(20,2) NOT NULL,
                `reason` VARCHAR(255) NOT NULL DEFAULT \'\',
                `id_employee` INT UNSIGNED NOT NULL DEFAULT 0,
                `date_add` DATETIME NOT NULL,
                KEY `id_order` (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
        $done = true;
    }

    /** Transaction Viva de la commande (paiement enregistré avec un identifiant de transaction), ou null. */
    public static function orderTransaction(Order $order)
    {
        $isViva = stripos((string) $order->payment, 'viva') !== false || $order->module === 'vivawalletsmartcheckout';
        if (!$isViva) {
            return null;
        }
        foreach (OrderPayment::getByOrderReference($order->reference) as $p) {
            $t = trim((string) $p->transaction_id);
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $t)) {
                return ['transaction_id' => $t, 'amount' => (float) $p->amount];
            }
        }
        return null;
    }

    /** Appel HTTP à l'API Viva. Renvoie [code HTTP, données décodées]. */
    protected static function call($method, $path)
    {
        $ch = curl_init(self::host() . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_USERPWD => Configuration::get('HFM_VIVA_MERCHANT_ID') . ':' . Configuration::get('HFM_VIVA_API_KEY'),
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode((string) $body, true);
        return [$code, is_array($data) ? $data : []];
    }

    /** Détails de la transaction chez Viva (statut, montant, carte, date), ou null si illisible. */
    public static function fetch($transactionId)
    {
        if (!self::configured()) {
            return null;
        }
        list($code, $d) = self::call('GET', '/api/transactions/' . rawurlencode($transactionId));
        $t = $d['Transactions'][0] ?? null;
        if ($code !== 200 || !$t) {
            return null;
        }
        return [
            'status' => (string) ($t['StatusId'] ?? ''),
            'amount' => (float) ($t['Amount'] ?? 0),
            'date' => (string) ($t['InsDate'] ?? ''),
            'card' => (string) ($t['CreditCard']['Number'] ?? ''),
            'card_type' => (string) ($t['CreditCard']['CardType']['Name'] ?? ''),
            'order_code' => (string) ($t['Order']['OrderCode'] ?? ''),
        ];
    }

    public static function statusLabel($s)
    {
        $labels = ['F' => 'Payée', 'C' => 'Payée (en compensation)', 'A' => 'Autorisée, non capturée', 'E' => 'Échouée',
            'X' => 'Annulée', 'R' => 'Remboursée', 'M' => 'Remboursement partiel', 'MA' => 'Remboursement partiel', 'MI' => 'Remboursement partiel'];
        return $labels[$s] ?? ($s !== '' ? $s : 'inconnu');
    }

    public static function refunds($idOrder)
    {
        self::ensureTable();
        return Db::getInstance()->executeS('SELECT * FROM `' . self::table() . '` WHERE id_order = ' . (int) $idOrder . ' ORDER BY id_refund') ?: [];
    }

    public static function refundedTotal($idOrder)
    {
        $sum = 0.0;
        foreach (self::refunds($idOrder) as $r) {
            $sum += (float) $r['amount'];
        }
        return round($sum, 2);
    }

    /**
     * Annule / rembourse $amount € de la transaction. Renvoie ['ok' => bool, 'message' => string].
     * Remboursement total -> état « Remboursé » (e-mail natif au client) ; partiel -> note privée.
     */
    public static function refund(Order $order, $amount, $reason, $idEmployee)
    {
        $tx = self::orderTransaction($order);
        if (!$tx) {
            return ['ok' => false, 'message' => 'Aucune transaction Viva sur cette commande.'];
        }
        if (!self::configured()) {
            return ['ok' => false, 'message' => 'Identifiants Viva absents (HFM_VIVA_MERCHANT_ID / HFM_VIVA_API_KEY).'];
        }
        $paid = $tx['amount'];
        $already = self::refundedTotal((int) $order->id);
        $left = round($paid - $already, 2);
        $amount = $amount > 0 ? round($amount, 2) : $left;
        if ($amount <= 0 || $amount > $left + 0.001) {
            return ['ok' => false, 'message' => sprintf('Montant invalide : %.2f € remboursables au plus.', max(0, $left))];
        }
        list($code, $d) = self::call('DELETE', '/api/transactions/' . rawurlencode($tx['transaction_id']) . '?amount=' . (int) round($amount * 100));
        $ok = $code >= 200 && $code < 300 && ($d['Success'] ?? true) !== false && empty($d['ErrorCode']);
        if (!$ok) {
            $err = (string) ($d['ErrorText'] ?? $d['message'] ?? ('HTTP ' . $code));
            PrestaShopLogger::addLog('HFM Viva : remboursement refusé (commande ' . (int) $order->id . ') : ' . $err, 3, null, 'Order', (int) $order->id, true);
            return ['ok' => false, 'message' => 'Viva a refusé l\'opération : ' . $err];
        }
        self::ensureTable();
        Db::getInstance()->insert('hfm_viva_refund', [
            'id_order' => (int) $order->id,
            'transaction_id' => pSQL($tx['transaction_id']),
            'refund_transaction_id' => pSQL((string) ($d['TransactionId'] ?? '')),
            'amount' => (float) $amount,
            'reason' => pSQL(Tools::substr(trim((string) $reason), 0, 255)),
            'id_employee' => (int) $idEmployee,
            'date_add' => date('Y-m-d H:i:s'),
        ]);
        $full = ($already + $amount) >= $paid - 0.001;
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
        $m->message = 'Viva : ' . ($full ? 'remboursement total' : 'remboursement partiel') . ' de ' . $label
            . ($reason !== '' ? ' — ' . Tools::substr(trim((string) $reason), 0, 200) : '');
        $m->add();
        return ['ok' => true, 'message' => ($full ? 'Remboursement total' : 'Remboursement partiel') . ' de ' . $label . ' effectué chez Viva.'];
    }
}
