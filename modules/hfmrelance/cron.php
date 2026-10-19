<?php
/**
 * Moteur de relance panier abandonné — remplace Carts Guru.
 * Séquence : J+1h Email#1, +2j Email#2 (-5€), +2j Email#3 (-5€). Sortie si commande.
 * Usage : php cron.php [--dry] [--test=email@x.com]
 */
if (PHP_SAPI !== "cli") { http_response_code(403); exit("forbidden"); }
define('_PS_ADMIN_DIR_', __DIR__ . '/../../admin78026');
require __DIR__ . '/../../config/config.inc.php';
// PrestaShop 9 : le calcul des totaux de panier (règles, taxes) exige le conteneur Symfony,
// absent en ligne de commande -> on démarre le noyau back-office.
require_once __DIR__ . '/../../app/AdminKernel.php';
$hfmKernel = new AdminKernel('prod', false);
$hfmKernel->boot();
Context::getContext()->container = $hfmKernel->getContainer();
Context::getContext()->shop = new Shop((int)Configuration::get('PS_SHOP_DEFAULT'));
Context::getContext()->language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
Context::getContext()->currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
Context::getContext()->country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));

$DRY  = in_array('--dry', $argv);
$TEST = null;
foreach ($argv as $a) if (strpos($a, '--test=') === 0) $TEST = substr($a, 7);

// INTERRUPTEUR : les relances réelles ne partent que si HFM_RELANCE_ENABLED = 1 (à activer sur la
// PROD uniquement). Évite tout envoi à de vrais clients depuis une pré-prod / copie de base.
// --dry et --test=adresse restent utilisables pour vérifier sans activer.
if (!$DRY && !$TEST && (int) Configuration::get('HFM_RELANCE_ENABLED') !== 1) {
    echo "Relances désactivées (HFM_RELANCE_ENABLED != 1) : rien n'est envoyé.\n";
    exit(0);
}
$ONLY_CART = null;
foreach ($argv as $a) if (strpos($a, '--cart=') === 0) $ONLY_CART = (int)substr($a, 7);
$FORCE_STEP = null;
foreach ($argv as $a) if (strpos($a, '--step=') === 0) $FORCE_STEP = (int)substr($a, 7);

$TPL_DIR = __DIR__ . '/templates';
$META    = json_decode(file_get_contents($TPL_DIR . '/_meta.json'), true);
$LANG_MAP = []; // id_lang -> iso
foreach (Language::getLanguages(false) as $l) $LANG_MAP[(int)$l['id_lang']] = $l['iso_code'];
$SUPPORTED = ['fr','en','de','it','es'];

// délais (secondes)
$DELAY_STEP1 = 3600;          // 1h après abandon
$DELAY_NEXT  = 2 * 86400;     // 2 jours entre étapes
$MAX_AGE     = 30 * 86400;    // ne pas relancer un panier de +30j
$MAX_PER_RUN = (int)getenv('HFM_MAX') ?: 50;  // plafond d'envois par exécution (sécurité)

$now = time();

// SMTP — PrestaShop 9 n'embarque plus SwiftMailer : Symfony Mailer (fourni par PS9) à la place.
$enc = Tools::strtolower((string) Configuration::get('PS_MAIL_SMTP_ENCRYPTION'));
$dsn = sprintf(
    '%s://%s:%s@%s:%d',
    $enc === 'ssl' ? 'smtps' : 'smtp',
    rawurlencode((string) Configuration::get('PS_MAIL_USER')),
    rawurlencode((string) Configuration::get('PS_MAIL_PASSWD')),
    (string) Configuration::get('PS_MAIL_SERVER'),
    (int) Configuration::get('PS_MAIL_SMTP_PORT')
);
$mailer = new Symfony\Component\Mailer\Mailer(Symfony\Component\Mailer\Transport::fromDsn($dsn));
$FROM = Configuration::get('PS_MAIL_USER');

// Site public (front headless) : liens « reprendre mon panier » et « se désinscrire ».
$FRONT = rtrim((string) Configuration::get('HFM_FRONT_URL'), '/');

// Désinscrits (lien signé des emails, cf. bridge hfmstorefront newsletter?action=unsubscribe).
Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hfm_relance_optout` (
    `email` VARCHAR(255) NOT NULL PRIMARY KEY, `date_add` DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

function unsubUrl($email, $iso) {
    global $FRONT;
    $email = Tools::strtolower(trim((string) $email));
    $e = rtrim(strtr(base64_encode($email), '+/', '-_'), '=');
    $s = hash_hmac('sha256', $email, Configuration::get('HFM_RECOVER_SECRET'));
    return $FRONT . '/' . $iso . '/desabonnement?e=' . $e . '&s=' . $s;
}

function fmtPrice($p){ return number_format((float)$p, 2, ',', ' ') . ' €'; }

// génère un code promo unique -5€/100€ valable 14j, 1 usage, pour ce client
function makeCode($id_customer, $email) {
    $code = 'HFM-' . strtoupper(substr(md5($email . microtime()), 0, 8));
    $rule = new CartRule();
    foreach (Language::getLanguages(false) as $l) $rule->name[(int)$l['id_lang']] = 'Relance panier -5€';
    $rule->id_customer = (int)$id_customer ?: 0;
    $rule->date_from = date('Y-m-d H:i:s');
    $rule->date_to = date('Y-m-d H:i:s', time() + 14*86400);
    $rule->quantity = 1; $rule->quantity_per_user = 1;
    $rule->minimum_amount = 100; $rule->minimum_amount_tax = 1; $rule->minimum_amount_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
    $rule->reduction_amount = 5; $rule->reduction_tax = 1; $rule->reduction_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
    $rule->highlight = 1; $rule->active = 1; $rule->code = $code;
    $rule->add();
    return [$code, (int)$rule->id];
}

// construit la grille HTML produits + l'URL de récupération depuis le panier réel
function buildCart($cart, $id_lang, $code, $iso = 'fr') {
    global $FRONT;
    $products = $cart->getProducts();
    $rows = ''; $plist = [];
    foreach ($products as $p) {
        $plist[] = (int)$p['id_product'] . ':' . (int)$p['cart_quantity'];
        $img = '';
        $cover = Product::getCover((int)$p['id_product']);
        if ($cover && !empty($cover['id_image'])) {
            $img = 'https://' . Configuration::get('PS_SHOP_DOMAIN_SSL') . '/' . (int)$cover['id_image'] . '-home_default/' . $p['link_rewrite'] . '.jpg';
        }
        $rows .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;border-collapse:collapse;"><tr>'
            . '<td width="80" valign="middle" style="padding:6px;width:80px;"><img src="' . htmlspecialchars($img) . '" width="74" style="display:block;border-radius:6px;border:1px solid #eee;"></td>'
            . '<td valign="middle" align="left" style="padding:6px;font-family:Hanken Grotesk,Arial,sans-serif;color:#333;font-size:14px;"><strong>'
            . htmlspecialchars($p['name']) . '</strong><br><span style="color:#777;">Qté : ' . (int)$p['cart_quantity'] . '</span></td>'
            . '<td width="90" valign="middle" align="right" style="padding:6px;width:90px;font-family:Hanken Grotesk,Arial,sans-serif;color:#333;font-size:14px;white-space:nowrap;"><strong>'
            . fmtPrice($p['price_wt'] * $p['cart_quantity']) . '</strong></td></tr></table>';
    }
    $subtotal = $cart->getOrderTotal(true, Cart::BOTH);
    $hasReduc = (bool)$code;
    $total = $hasReduc ? max(0, $subtotal - 5) : $subtotal;
    $sumrows = '<tr><td align="right" style="padding:4px 8px;font-family:Hanken Grotesk,Arial,sans-serif;font-size:13px;color:#666;">Sous-total :</td>'
             . '<td width="110" align="right" style="padding:4px 8px;width:110px;font-family:Hanken Grotesk,Arial,sans-serif;font-size:13px;color:#666;white-space:nowrap;">' . fmtPrice($subtotal) . '</td></tr>';
    if ($hasReduc) {
        $sumrows .= '<tr><td align="right" style="padding:4px 8px;font-family:Hanken Grotesk,Arial,sans-serif;font-size:13px;color:#4F7A1A;">Réduction (' . $code . ') :</td>'
                  . '<td align="right" style="padding:4px 8px;font-family:Hanken Grotesk,Arial,sans-serif;font-size:13px;color:#4F7A1A;white-space:nowrap;">- ' . fmtPrice(5) . '</td></tr>';
    }
    $sumrows .= '<tr><td align="right" style="padding:8px;border-top:2px solid #333;font-family:Hanken Grotesk,Arial,sans-serif;font-size:15px;font-weight:bold;color:#333;">Total :</td>'
              . '<td align="right" style="padding:8px;border-top:2px solid #333;font-family:Hanken Grotesk,Arial,sans-serif;font-size:15px;font-weight:bold;color:#333;white-space:nowrap;">' . fmtPrice($total) . '</td></tr>';
    $rows .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-top:6px;">' . $sumrows . '</table>';
    $p_raw = implode(',', $plist);
    $c_raw = $code ? $code : '';
    $sig = hash_hmac('sha256', $p_raw . '|' . $c_raw, Configuration::get('HFM_RECOVER_SECRET'));
    // Reprise du panier sur le FRONT headless (page /{langue}/reprise-panier) : il recrée le panier
    // à partir de la liste produits:quantités, puis ouvre le tunnel avec le code promo pré-appliqué.
    $recover = $FRONT . '/' . $iso . '/reprise-panier?p=' . urlencode($p_raw);
    if ($code) $recover .= '&c=' . urlencode($c_raw);
    $recover .= '&s=' . $sig;
    return [$rows, $recover];
}

// ---- sélection des paniers éligibles ----
$prefix = _DB_PREFIX_;
$dateMin = pSQL(date('Y-m-d H:i:s', $now - $MAX_AGE));
$dateMax = pSQL(date('Y-m-d H:i:s', $now - $DELAY_STEP1));
$sql = "SELECT c.id_cart, c.id_customer, c.id_lang, cu.email, cu.id_customer AS cust
        FROM {$prefix}cart c
        JOIN {$prefix}customer cu ON cu.id_customer = c.id_customer
        JOIN (SELECT id_customer, MAX(date_upd) AS mx
              FROM {$prefix}cart
              WHERE id_customer > 0 AND date_upd >= '$dateMin' AND date_upd <= '$dateMax'
                AND EXISTS (SELECT 1 FROM {$prefix}cart_product cp2 WHERE cp2.id_cart = {$prefix}cart.id_cart)
              GROUP BY id_customer) latest
          ON latest.id_customer = c.id_customer AND latest.mx = c.date_upd
        WHERE c.id_customer > 0 AND cu.email <> ''
          AND cu.email NOT LIKE '%carts.guru%'
          AND NOT EXISTS (SELECT 1 FROM {$prefix}hfm_relance_optout o WHERE o.email = LOWER(cu.email))
          AND c.date_upd >= '$dateMin' AND c.date_upd <= '$dateMax'
          AND EXISTS (SELECT 1 FROM {$prefix}cart_product cp WHERE cp.id_cart = c.id_cart)
        GROUP BY c.id_customer
        ORDER BY c.date_upd DESC LIMIT 500";
if ($ONLY_CART) $sql = str_replace("ORDER BY", "AND c.id_cart=".$ONLY_CART." ORDER BY", $sql);
$carts = Db::getInstance()->executeS($sql);

$stats = ['scanned'=>0,'sent'=>0,'converted'=>0,'skipped'=>0];
$doneCustomers = [];
foreach ($carts as $row) {
    $stats['scanned']++;
    $id_cart = (int)$row['id_cart'];
    $cid = (int)$row['id_customer'];
    // garde-fou : ne JAMAIS relancer une adresse @carts.guru (ex-prestataire banni)
    if (stripos((string)$row['email'], 'carts.guru') !== false) { $stats['skipped']++; continue; }
    // anti-doublon : 1 seul email par client par run + pas 2 envois en moins de 36h (toutes carts confondues)
    if (isset($doneCustomers[$cid])) { $stats['skipped']++; continue; }
    $doneCustomers[$cid] = true;
    $lastCust = Db::getInstance()->getValue("SELECT MAX(last_sent_at) FROM {$prefix}hfm_relance WHERE id_customer=$cid");
    if ($lastCust && strtotime($lastCust) > ($now - 36*3600)) { $stats['skipped']++; continue; }
    $cart = new Cart($id_cart);
    if (!Validate::isLoadedObject($cart)) { $stats['skipped']++; continue; }

    // commande déjà passée ? -> converti, sortie (+ montant récupéré si on avait relancé)
    $id_order = (int)Order::getIdByCartId($id_cart);
    if ($id_order) {
        $tr = Db::getInstance()->getRow("SELECT step, converted FROM {$prefix}hfm_relance WHERE id_cart=$id_cart");
        if ($tr && (int)$tr['step'] > 0 && !(int)$tr['converted']) {
            $order = new Order($id_order);
            $amt = (float)$order->total_paid_real ?: (float)$order->total_paid;
            Db::getInstance()->execute("UPDATE {$prefix}hfm_relance SET converted=1, id_order=$id_order, recovered_amount=" . (float)$amt . ", converted_at='" . date('Y-m-d H:i:s') . "' WHERE id_cart=$id_cart");
            $stats['converted']++;
        } elseif ($tr) {
            Db::getInstance()->execute("UPDATE {$prefix}hfm_relance SET converted=1 WHERE id_cart=$id_cart");
        }
        continue;
    }

    // état du suivi
    $track = Db::getInstance()->getRow("SELECT * FROM {$prefix}hfm_relance WHERE id_cart=$id_cart");
    $step = $track ? (int)$track['step'] : 0;
    if ($track && (int)$track['converted']) { $stats['skipped']++; continue; }

    // langue + audience
    $iso = isset($LANG_MAP[(int)$row['id_lang']]) ? $LANG_MAP[(int)$row['id_lang']] : 'fr';
    if (!in_array($iso, $SUPPORTED)) $iso = 'en';
    $nbOrders = (int)Db::getInstance()->getValue("SELECT COUNT(*) FROM {$prefix}orders WHERE id_customer=" . (int)$row['id_customer'] . " AND valid=1");
    $audience = $nbOrders > 0 ? 'client' : 'prospect';

    // quelle étape envoyer ?
    $cartTime = strtotime($cart->date_upd);
    $lastSent = $track && $track['last_sent_at'] ? strtotime($track['last_sent_at']) : 0;
    if (!$TEST && $stats['sent'] >= $MAX_PER_RUN) { break; }
    $nextStep = 0;
    if ($step == 0 && ($now - $cartTime) >= $DELAY_STEP1) $nextStep = 1;
    elseif ($step == 1 && ($now - $lastSent) >= $DELAY_NEXT) $nextStep = 2;
    elseif ($step == 2 && ($now - $lastSent) >= $DELAY_NEXT) $nextStep = 3;
    if ($FORCE_STEP) $nextStep = $FORCE_STEP;
    if ($nextStep == 0) { $stats['skipped']++; continue; }

    // code promo (étapes 2 et 3 : -5€)
    $code = $track['code'] ?? null;
    $id_rule = $track['id_cart_rule'] ?? null;
    if ($nextStep >= 2 && !$code) { list($code, $id_rule) = makeCode((int)$row['id_customer'], $row['email']); }

    // template
    $key = $iso . '_' . $audience . '_' . $nextStep;
    $tplFile = $TPL_DIR . '/' . $key . '.html';
    if (!file_exists($tplFile)) { $stats['skipped']++; continue; }
    $htmlT = file_get_contents($tplFile);
    $subject = $META[$key]['subject'] ?? 'Votre panier vous attend';

    // données client
    $customer = new Customer((int)$row['id_customer']);
    $firstname = $customer->firstname ?: '';
    list($cartRows, $recover) = buildCart($cart, (int)$row['id_lang'], ($nextStep >= 2 ? $code : null), $iso);

    // promo box (étapes 2-3)
    $promoBox = ($nextStep >= 2 && $code)
        ? '<div style="text-align:center;padding:6px 40px;"><div style="display:inline-block;background:#4F7A1A;color:#fff;font-family:Hanken Grotesk,Arial,sans-serif;font-size:18px;font-weight:bold;letter-spacing:2px;border:2px solid #8CC63F;border-radius:4px;padding:12px 28px;">' . $code . '</div></div>'
        : '';

    $html = strtr($htmlT, [
        '{{FIRSTNAME}}' => htmlspecialchars($firstname),
        '{{CART_ROWS}}' => $cartRows,
        '{{PROMO_BOX}}' => $promoBox,
        '{{RECOVER_URL}}' => $recover,
        '{{UNSUB_URL}}' => htmlspecialchars(unsubUrl($row['email'], $iso)),
    ]);
    $subject = str_replace('{{FIRSTNAME}}', $firstname, $subject);

    $dest = $TEST ?: $row['email'];
    if ($DRY) {
        echo "DRY  | cart $id_cart | $iso/$audience step$nextStep | $dest | $subject\n";
    } else {
        try {
            $msg = (new Symfony\Component\Mime\Email())
                ->from(new Symfony\Component\Mime\Address($FROM, 'Hyaluronic Filler Market'))
                ->to($dest)
                ->subject($subject)
                ->html($html);
            // Désinscription en un clic côté messagerie (Gmail / Yahoo l'exigent pour les envois promotionnels).
            $msg->getHeaders()->addTextHeader('List-Unsubscribe', '<' . unsubUrl($row['email'], $iso) . '>');
            $mailer->send($msg);
            echo "SENT | cart $id_cart | $iso/$audience step$nextStep | $dest\n";
            $stats['sent']++;
        } catch (Exception $e) { echo "ERR  | cart $id_cart : " . $e->getMessage() . "\n"; continue; }
    }

    // upsert suivi — ni en essai à blanc (--dry) ni en test (--test) : sinon un dry-run marque
    // des relances « envoyées » et bloque les vraies (anti-doublon 36 h).
    if (!$TEST && !$DRY) {
        $nowStr = date('Y-m-d H:i:s');
        $codeSql = $code ? "'" . pSQL($code) . "'" : "NULL";
        $ruleSql = $id_rule ? (int)$id_rule : "NULL";
        Db::getInstance()->execute("INSERT INTO {$prefix}hfm_relance (id_cart,id_customer,email,lang,audience,step,last_sent_at,code,id_cart_rule,created_at,nb_sent)
            VALUES ($id_cart," . (int)$row['id_customer'] . ",'" . pSQL($row['email']) . "','" . pSQL($iso) . "','" . pSQL($audience) . "',$nextStep,'$nowStr',$codeSql,$ruleSql,'$nowStr',1)
            ON DUPLICATE KEY UPDATE step=$nextStep, last_sent_at='$nowStr', nb_sent=nb_sent+1, code=COALESCE($codeSql, code), id_cart_rule=COALESCE($ruleSql, id_cart_rule)");
    }
    if ($TEST && $stats['sent'] >= 1) break; // en test : un seul
}

echo "\n=== Scannés: {$stats['scanned']} | Envoyés: {$stats['sent']} | Convertis: {$stats['converted']} | Ignorés: {$stats['skipped']} ===\n";
