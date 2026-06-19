<?php
/**
 * Inscription newsletter popup -> ajoute à la newsletter + envoie l'email de bienvenue avec le code BIENVENUE5 (par langue).
 */
require dirname(__FILE__).'/config/config.inc.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
$first = isset($_POST['first_name']) ? trim((string)$_POST['first_name']) : '';
$lang  = isset($_POST['lang']) ? preg_replace('/[^a-z]/','',substr((string)$_POST['lang'],0,2)) : 'fr';
if (!in_array($lang, ['fr','en','de','it','es'])) $lang = 'fr';

if (!Validate::isEmail($email)) { echo json_encode(['ok'=>false,'err'=>'email']); exit; }
// blocage : ex-prestataire carts.guru banni, on ne lui envoie jamais d'email
if (stripos($email, 'carts.guru') !== false) { echo json_encode(['ok'=>false,'err'=>'blocked']); exit; }

$db = Db::getInstance(); $p = _DB_PREFIX_;
$idShop = (int)Configuration::get('PS_SHOP_DEFAULT');

// 1) inscrire à la newsletter (ps_emailsubscription) si pas déjà inscrit
$exists = (int)$db->getValue("SELECT COUNT(*) FROM {$p}emailsubscription WHERE email='".pSQL($email)."'");
if (!$exists) {
    $db->insert('emailsubscription', [
        'id_shop'=>$idShop, 'id_shop_group'=>(int)Configuration::get('PS_SHOP_GROUP_DEFAULT'),
        'email'=>pSQL($email), 'newsletter_date_add'=>date('Y-m-d H:i:s'), 'ip_registration_newsletter'=>pSQL($_SERVER['REMOTE_ADDR'] ?? ''),
        'http_referer'=>'', 'active'=>1
    ], false, true, Db::INSERT_IGNORE);
}

// 2) anti-doublon : ne pas renvoyer l'email de bienvenue 2x au même email (table de suivi simple)
$db->execute("CREATE TABLE IF NOT EXISTS {$p}hfm_promo_signup (email VARCHAR(255) PRIMARY KEY, lang CHAR(2), sent_at DATETIME)");
$already = (int)$db->getValue("SELECT COUNT(*) FROM {$p}hfm_promo_signup WHERE email='".pSQL($email)."'");
if ($already) { echo json_encode(['ok'=>true,'already'=>true]); exit; }

// 3) email de bienvenue par langue
$LOGO = 'https://hyaluronicfillermarket.com/img/fillerdistribution-logo-1623274423.jpg';
$CODE = 'BIENVENUE5';
$T = [
 'fr'=>['subj'=>'Bienvenue ! Voici votre code -5€ 🎁','hi'=>'Bonjour','p1'=>'Merci de votre inscription à notre newsletter ! Comme promis, voici votre cadeau de bienvenue :','reward'=>'-5€ sur votre première commande','cond'=>'Dès 100€ d\'achat','cta'=>'Je découvre la boutique','foot'=>'Vous recevez cet email suite à votre inscription à la newsletter Hyaluronic Filler Market.'],
 'en'=>['subj'=>'Welcome! Here is your -€5 code 🎁','hi'=>'Hello','p1'=>'Thank you for subscribing to our newsletter! As promised, here is your welcome gift:','reward'=>'-€5 on your first order','cond'=>'On orders from €100','cta'=>'Discover the shop','foot'=>'You are receiving this email following your subscription to the Hyaluronic Filler Market newsletter.'],
 'de'=>['subj'=>'Willkommen! Hier ist Ihr -5€ Code 🎁','hi'=>'Hallo','p1'=>'Vielen Dank für Ihre Newsletter-Anmeldung! Wie versprochen, hier ist Ihr Willkommensgeschenk:','reward'=>'-5€ auf Ihre erste Bestellung','cond'=>'Ab 100€ Einkaufswert','cta'=>'Zum Shop','foot'=>'Sie erhalten diese E-Mail aufgrund Ihrer Anmeldung zum Hyaluronic Filler Market Newsletter.'],
 'it'=>['subj'=>'Benvenuto! Ecco il tuo codice -5€ 🎁','hi'=>'Ciao','p1'=>'Grazie per esserti iscritto alla nostra newsletter! Come promesso, ecco il tuo regalo di benvenuto:','reward'=>'-5€ sul tuo primo ordine','cond'=>'A partire da 100€ di acquisto','cta'=>'Scopri il negozio','foot'=>'Ricevi questa email a seguito dell\'iscrizione alla newsletter Hyaluronic Filler Market.'],
 'es'=>['subj'=>'¡Bienvenido! Aquí tienes tu código -5€ 🎁','hi'=>'Hola','p1'=>'¡Gracias por suscribirte a nuestra newsletter! Como prometimos, aquí tienes tu regalo de bienvenida:','reward'=>'-5€ en tu primer pedido','cond'=>'A partir de 100€ de compra','cta'=>'Descubrir la tienda','foot'=>'Recibes este correo tras tu suscripción a la newsletter de Hyaluronic Filler Market.'],
];
$t = $T[$lang];
$name = $first ? htmlspecialchars($first) : '';
$html = '<!doctype html><html><head><meta charset="utf-8"></head><body style="margin:0;background:#fff;font-family:Montserrat,Arial,sans-serif;color:#333;">'
.'<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">'
.'<table width="600" cellpadding="0" cellspacing="0" style="max-width:100%;">'
.'<tr><td align="center" style="padding:24px 0 12px;"><img src="'.$LOGO.'" width="280" alt="Hyaluronic Filler Market" style="display:block;border:0;max-width:80%;height:auto;"></td></tr>'
.'<tr><td style="padding:8px 36px;font-size:15px;line-height:1.65;text-align:center;">'
.'<p style="text-align:left;">'.$t['hi'].($name?' '.$name:'').',</p>'
.'<p style="text-align:left;">'.$t['p1'].'</p>'
.'<div style="background:#fdf9f0;border:2px dashed #c9a45c;border-radius:10px;padding:22px;margin:18px 0;">'
.'<p style="margin:0 0 6px;font-size:20px;font-weight:bold;color:#94c55d;">🎁 '.$t['reward'].'</p>'
.'<p style="margin:0 0 12px;font-size:13px;color:#777;">'.$t['cond'].'</p>'
.'<p style="margin:0;font-size:24px;letter-spacing:3px;font-weight:bold;color:#383838;background:#fff;border:2px dashed #e771bd;border-radius:8px;padding:12px 22px;display:inline-block;">'.$CODE.'</p>'
.'</div>'
.'<a href="https://hyaluronicfillermarket.com/'.$lang.'/" style="display:inline-block;background:#e771bd;color:#fff;padding:14px 32px;border-radius:4px;text-decoration:none;font-weight:bold;font-size:15px;">'.$t['cta'].'</a>'
.'</td></tr>'
.'<tr><td style="background:#f7f7f7;padding:16px 20px;font-size:11px;color:#999;text-align:center;font-family:Arial,sans-serif;">'
.'Hyaluronic Filler Market • 28 Rue Saint-Denis, 92700 Colombes • +33 7 68 82 38 43<br>'.$t['foot'].'</td></tr>'
.'</table></td></tr></table></body></html>';

try {
    $transport = (new Swift_SmtpTransport(Configuration::get('PS_MAIL_SERVER'),Configuration::get('PS_MAIL_SMTP_PORT'),Configuration::get('PS_MAIL_SMTP_ENCRYPTION')))
        ->setUsername(Configuration::get('PS_MAIL_USER'))->setPassword(Configuration::get('PS_MAIL_PASSWD'));
    $mailer = new Swift_Mailer($transport);
    $msg = (new Swift_Message($t['subj']))->setFrom([Configuration::get('PS_MAIL_USER')=>'Hyaluronic Filler Market'])
        ->setReplyTo('sales@hyaluronicfillermarket.com')->setTo([$email])->setBody($html,'text/html','utf-8');
    $mailer->send($msg);
    $db->insert('hfm_promo_signup', ['email'=>pSQL($email),'lang'=>pSQL($lang),'sent_at'=>date('Y-m-d H:i:s')], false, true, Db::INSERT_IGNORE);
    echo json_encode(['ok'=>true]);
} catch (Exception $e) { echo json_encode(['ok'=>false,'err'=>'send']); }
