<?php
/**
 * Saisie du n° RPPS par l'équipe depuis la fiche commande du back-office (demande client du
 * 26/11/2026). Avant, l'équipe notait le RPPS dans le champ « Autre » de l'adresse : le site ne le
 * lisait pas, et le client devait le ressaisir à chaque commande.
 *
 * Le numéro saisi ici est enregistré :
 *   - sur la COMMANDE (hfm_order_pro_doc), avec l'employé et la date de saisie ;
 *   - sur le COMPTE du client s'il en a un (hfm_customer_rpps) ;
 *   - par E-MAIL (hfm_rpps_by_email), pour être retrouvé à la prochaine commande, compte ou invité.
 * Mêmes tables et mêmes règles de format que le tunnel de commande (lib/api_base.php) : RPPS =
 * 11 chiffres avec clé de Luhn, ADELI = 9 chiffres. Une attestation ou un justificatif déjà
 * enregistrés ne sont jamais effacés.
 */
class HfmRppsAdmin
{
    /** Retire espaces, points et tirets d'une saisie (« 10 00 123 456 7 »). */
    public static function clean($raw)
    {
        return preg_replace('/[\s.\-]+/', '', trim((string) $raw));
    }

    public static function valid($rpps)
    {
        if (preg_match('/^\d{11}$/', (string) $rpps)) {
            return self::luhn($rpps);
        }
        return (bool) preg_match('/^\d{9}$/', (string) $rpps);
    }

    protected static function luhn($number)
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10 === 0;
    }

    protected static function tableExists($table)
    {
        return (bool) Db::getInstance()->getValue(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '" . _DB_PREFIX_ . pSQL($table) . "'",
            false
        );
    }

    /** Praticien de l'Annuaire Santé (nom, prenom, profession), ou null s'il n'y figure pas. */
    public static function registry($rpps)
    {
        if (!self::tableExists('hfm_rpps_registry')) {
            return null;
        }
        return Db::getInstance()->getRow(
            'SELECT nom, prenom, profession FROM `' . _DB_PREFIX_ . 'hfm_rpps_registry` WHERE rpps = \'' . pSQL($rpps) . '\'',
            false
        ) ?: null;
    }

    /** Tables du tunnel (créées si absentes, même structure) + colonnes de traçabilité de la saisie BO. */
    public static function ensureTables()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        $db = Db::getInstance();
        $p = _DB_PREFIX_;
        $e = _MYSQL_ENGINE_;
        $db->execute("CREATE TABLE IF NOT EXISTS `{$p}hfm_order_pro_doc` (
            `id_order` INT UNSIGNED NOT NULL,
            `rpps` VARCHAR(16) NOT NULL DEFAULT '',
            `attestation` TINYINT(1) NOT NULL DEFAULT 0,
            `pro_doc` VARCHAR(255) DEFAULT NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_order`)
        ) ENGINE={$e} DEFAULT CHARSET=utf8mb4");
        $db->execute("CREATE TABLE IF NOT EXISTS `{$p}hfm_customer_rpps` (
            `id_customer` INT UNSIGNED NOT NULL,
            `rpps` VARCHAR(16) NOT NULL DEFAULT '',
            `attestation` TINYINT(1) NOT NULL DEFAULT 0,
            `pro_doc` VARCHAR(255) DEFAULT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_customer`)
        ) ENGINE={$e} DEFAULT CHARSET=utf8mb4");
        $db->execute("CREATE TABLE IF NOT EXISTS `{$p}hfm_rpps_by_email` (
            `email` VARCHAR(191) NOT NULL,
            `rpps` VARCHAR(16) NOT NULL DEFAULT '',
            `attestation` TINYINT(1) NOT NULL DEFAULT 0,
            `pro_doc` VARCHAR(255) DEFAULT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`email`)
        ) ENGINE={$e} DEFAULT CHARSET=utf8mb4");
        $cols = [];
        foreach ((array) $db->executeS("SHOW COLUMNS FROM `{$p}hfm_order_pro_doc`", true, false) as $c) {
            $cols[$c['Field']] = true;
        }
        if (!isset($cols['id_employee'])) {
            $db->execute("ALTER TABLE `{$p}hfm_order_pro_doc` ADD COLUMN `id_employee` INT UNSIGNED NOT NULL DEFAULT 0");
        }
        if (!isset($cols['date_bo'])) {
            $db->execute("ALTER TABLE `{$p}hfm_order_pro_doc` ADD COLUMN `date_bo` DATETIME NULL");
        }
    }

    /** Saisie back-office de la commande : ['id_employee', 'date_bo', 'employee'] ou null. */
    public static function boEntry($idOrder)
    {
        self::ensureTables();
        $row = Db::getInstance()->getRow(
            'SELECT d.id_employee, d.date_bo, CONCAT(e.firstname, \' \', e.lastname) AS employee
             FROM `' . _DB_PREFIX_ . 'hfm_order_pro_doc` d
             LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON e.id_employee = d.id_employee
             WHERE d.id_order = ' . (int) $idOrder . ' AND d.id_employee > 0',
            false
        );
        return $row ?: null;
    }

    /**
     * Enregistre le numéro saisi par l'équipe pour cette commande.
     * @return array ['ok' => bool, 'message' => string]
     */
    public static function saveForOrder(Order $order, $raw, $idEmployee)
    {
        $rpps = self::clean($raw);
        if ($rpps === '') {
            return ['ok' => false, 'message' => 'Aucun numéro saisi.'];
        }
        if (!preg_match('/^\d+$/', $rpps) || !in_array(strlen($rpps), [9, 11], true)) {
            return ['ok' => false, 'message' => 'Numéro « ' . $rpps . ' » refusé : un RPPS a 11 chiffres, un ADELI 9 chiffres.'];
        }
        if (!self::valid($rpps)) {
            return ['ok' => false, 'message' => 'Numéro RPPS « ' . $rpps . ' » refusé : sa clé de contrôle est fausse (erreur de saisie probable).'];
        }
        self::ensureTables();
        $db = Db::getInstance();
        $p = _DB_PREFIX_;
        $now = date('Y-m-d H:i:s');
        $sql = pSQL($rpps);

        // 1) Sur la commande, avec l'auteur de la saisie.
        $ok = $db->execute(
            "INSERT INTO `{$p}hfm_order_pro_doc` (id_order, rpps, attestation, pro_doc, date_add, id_employee, date_bo)
             VALUES (" . (int) $order->id . ", '{$sql}', 0, NULL, '{$now}', " . (int) $idEmployee . ", '{$now}')
             ON DUPLICATE KEY UPDATE rpps = VALUES(rpps), id_employee = VALUES(id_employee), date_bo = VALUES(date_bo)"
        );
        if (!$ok) {
            return ['ok' => false, 'message' => 'Erreur base de données : le numéro n\'a pas été enregistré.'];
        }

        // 2) Sur le compte du client (pas pour un invité : son compte est éphémère).
        $customer = new Customer((int) $order->id_customer);
        if (Validate::isLoadedObject($customer)) {
            if (!$customer->is_guest) {
                $db->execute(
                    "INSERT INTO `{$p}hfm_customer_rpps` (id_customer, rpps, date_upd)
                     VALUES (" . (int) $customer->id . ", '{$sql}', '{$now}')
                     ON DUPLICATE KEY UPDATE rpps = VALUES(rpps), date_upd = VALUES(date_upd)"
                );
            }
            // 3) Par e-mail : retrouvé à la prochaine commande, avec ou sans compte.
            $email = Tools::strtolower(trim((string) $customer->email));
            if ($email !== '') {
                $db->execute(
                    "INSERT INTO `{$p}hfm_rpps_by_email` (email, rpps, date_upd)
                     VALUES ('" . pSQL($email) . "', '{$sql}', '{$now}')
                     ON DUPLICATE KEY UPDATE rpps = VALUES(rpps), date_upd = VALUES(date_upd)"
                );
            }
        }

        if (strlen($rpps) === 9) {
            $detail = 'numéro ADELI (9 chiffres), non vérifiable dans l\'Annuaire Santé';
        } elseif ($reg = self::registry($rpps)) {
            $who = trim($reg['prenom'] . ' ' . $reg['nom']);
            $detail = ($reg['profession'] ?: 'praticien') . ($who !== '' ? ' (' . $who . ')' : '') . ', trouvé dans l\'Annuaire Santé';
        } else {
            $detail = 'ATTENTION : non trouvé dans l\'Annuaire Santé, à vérifier';
        }
        return [
            'ok' => true,
            'message' => 'RPPS ' . $rpps . ' enregistré — ' . $detail . '. Il sera repris automatiquement aux prochaines commandes de ce client.',
        ];
    }
}
