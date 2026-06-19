<?php
/* Batch réactivation programmé — envoie 400 mails du segment 1-2 ans, priorité DE->IT->EN->FR->JA->ES,
   exclut les déjà contactés (campaign='reactiv'), espacé 4-5s. Lancé par cron (one-shot). */
if (PHP_SAPI !== 'cli') { exit; }
require dirname(__FILE__).'/../../config/config.inc.php';
$LOGO='https://hyaluronicfillermarket.com/img/fillerdistribution-logo-1623274423.jpg';
$BASE='https://hyaluronicfillermarket.com';
$T=[
'fr'=>['subj'=>'Vous nous avez manqué 💙 — 5% + livraison offerte','hi'=>'Bonjour','p1'=>'Cela fait un moment que nous n\'avons pas eu le plaisir de vous compter parmi nos clients, et vous nous avez manqué !','p2'=>'Pour célébrer votre retour, nous vous offrons sur votre prochaine commande :','rw'=>'-5% + livraison offerte','cd'=>'sur toute votre commande, sans minimum d\'achat','cta'=>'Je redécouvre la boutique','ft'=>'Au plaisir de vous revoir très bientôt.','un'=>'Vous recevez cet email en tant que client de Hyaluronic Filler Market. Pour ne plus recevoir nos offres, répondez avec la mention STOP.'],
'en'=>['subj'=>'We\'ve missed you 💙 — 5% + free shipping','hi'=>'Hello','p1'=>'It has been a while since your last order, and we\'ve genuinely missed you!','p2'=>'To celebrate your return, here is our gift for your next order:','rw'=>'-5% + free shipping','cd'=>'on your entire order, with no minimum purchase','cta'=>'Rediscover the shop','ft'=>'We look forward to welcoming you back very soon.','un'=>'You receive this email as a customer of Hyaluronic Filler Market. To stop receiving our offers, reply with STOP.'],
'de'=>['subj'=>'Wir haben Sie vermisst 💙 — 5% + kostenloser Versand','hi'=>'Hallo','p1'=>'Es ist eine Weile her seit Ihrer letzten Bestellung — wir haben Sie vermisst!','p2'=>'Zu Ihrer Rückkehr schenken wir Ihnen für Ihre nächste Bestellung:','rw'=>'-5% + kostenloser Versand','cd'=>'auf Ihre gesamte Bestellung, ohne Mindestbestellwert','cta'=>'Zum Shop','ft'=>'Wir freuen uns, Sie bald wiederzusehen.','un'=>'Sie erhalten diese E-Mail als Kunde von Hyaluronic Filler Market. Wenn Sie keine Angebote mehr erhalten möchten, antworten Sie mit STOP.'],
'it'=>['subj'=>'Ci è mancata/o 💙 — 5% + spedizione gratuita','hi'=>'Buongiorno','p1'=>'È passato un po\' di tempo dal suo ultimo ordine e ci è mancata/o davvero!','p2'=>'Per festeggiare il suo ritorno, le offriamo sul suo prossimo ordine:','rw'=>'-5% + spedizione gratuita','cd'=>'su tutto il suo ordine, senza importo minimo','cta'=>'Riscopri il negozio','ft'=>'Saremo felici di rivederla molto presto.','un'=>'Riceve questa email in quanto cliente di Hyaluronic Filler Market. Per non ricevere più le nostre offerte, risponda con STOP.'],
'es'=>['subj'=>'Le echamos de menos 💙 — 5% + envío gratis','hi'=>'Hola','p1'=>'Ha pasado un tiempo desde su último pedido, ¡y le hemos echado de menos!','p2'=>'Para celebrar su regreso, le ofrecemos en su próximo pedido:','rw'=>'-5% + envío gratis','cd'=>'en todo su pedido, sin importe mínimo','cta'=>'Redescubrir la tienda','ft'=>'Estaremos encantados de verle de nuevo muy pronto.','un'=>'Recibe este correo como cliente de Hyaluronic Filler Market. Para no recibir más ofertas, responda con STOP.'],
'ja'=>['subj'=>'またのご利用をお待ちしております ― 5%OFF＋送料無料','hi'=>'','p1'=>'ご無沙汰しております。前回のご注文から少しお時間が空いてしまいましたが、お客様にまたお会いできることを心よりお待ちしておりました。','p2'=>'またのご利用を記念いたしまして、次回のご注文に以下の特典をご用意いたしました。','rw'=>'5%OFF ＋ 送料無料','cd'=>'ご注文全体に適用・最低購入金額なし','cta'=>'ショップを見る','ft'=>'またのご利用を心よりお待ちしております。','un'=>'本メールはHyaluronic Filler Marketのお客様にお送りしています。配信停止をご希望の場合は「STOP」とご返信ください。'],
];
function build($t,$iso,$name,$LOGO,$pixel){
 $shop='https://hyaluronicfillermarket.com/'.$iso.'/';
 $greet=($iso=='ja')?(htmlspecialchars($name).'様'):($t['hi'].' '.htmlspecialchars($name).',');
 return '<!doctype html><html><head><meta charset="utf-8"></head><body style="margin:0;background:#fff;font-family:Montserrat,Arial,sans-serif;color:#333;">'
 .'<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center"><table width="600" cellpadding="0" cellspacing="0" style="max-width:100%;">'
 .'<tr><td align="center" style="padding:24px 0 12px;"><img src="'.$LOGO.'" width="270" style="display:block;border:0;max-width:78%;height:auto;"></td></tr>'
 .'<tr><td style="padding:8px 36px;font-size:15px;line-height:1.7;"><p>'.$greet.'</p><p>'.$t['p1'].'</p><p>'.$t['p2'].'</p>'
 .'<div style="background:#fdf9f0;border:2px dashed #c9a45c;border-radius:10px;padding:22px;margin:18px 0;text-align:center;"><p style="margin:0 0 8px;font-size:21px;font-weight:bold;color:#94c55d;">'.$t['rw'].'</p><p style="margin:0 0 14px;font-size:13px;color:#777;">'.$t['cd'].'</p><p style="margin:0;font-size:24px;letter-spacing:3px;font-weight:bold;color:#383838;background:#fff;border:2px dashed #e771bd;border-radius:8px;padding:12px 24px;display:inline-block;">RETOUR5LIV</p></div>'
 .'<div style="text-align:center;margin:18px 0;"><a href="'.$shop.'" style="display:inline-block;background:#e771bd;color:#fff;padding:14px 32px;border-radius:4px;text-decoration:none;font-weight:bold;font-size:15px;">'.$t['cta'].'</a></div>'
 .'<p style="color:#888;font-size:13px;">'.$t['ft'].'</p><p style="margin-top:14px;">Hyaluronic Filler Market</p></td></tr>'
 .'<tr><td style="background:#f7f7f7;padding:14px 20px;font-size:10px;color:#aaa;text-align:center;font-family:Arial,sans-serif;">'.$t['un'].'<br>Hyaluronic Filler Market • 28 Rue Saint-Denis, 92700 Colombes</td></tr>'
 .'</table></td></tr></table>'.$pixel.'</body></html>';
}
$db=Db::getInstance();
$rows=$db->executeS("SELECT c.id_customer,c.firstname,c.email,l.iso_code AS iso
 FROM ps_customer c JOIN ps_lang l ON l.id_lang=c.id_lang
 JOIN (SELECT id_customer, MAX(date_add) last_order FROM ps_orders WHERE valid=1 GROUP BY id_customer) s ON s.id_customer=c.id_customer
 WHERE s.last_order >= '2024-06-09' AND s.last_order < '2025-06-09'
   AND c.email NOT LIKE '%amzal%' AND c.email NOT LIKE '%leetcall%' AND c.email NOT LIKE '%mamzal%' AND c.email<>''
   AND NOT EXISTS (SELECT 1 FROM "._DB_PREFIX_."hfm_email_track t WHERE t.campaign='reactiv' AND t.email=c.email)
 ORDER BY FIELD(l.iso_code,'de','it','en','fr','ja','es'), s.last_order DESC LIMIT 400");
$transport=(new Swift_SmtpTransport(Configuration::get('PS_MAIL_SERVER'),Configuration::get('PS_MAIL_SMTP_PORT'),Configuration::get('PS_MAIL_SMTP_ENCRYPTION')))->setUsername(Configuration::get('PS_MAIL_USER'))->setPassword(Configuration::get('PS_MAIL_PASSWD'));
$mailer=new Swift_Mailer($transport);
$ok=0;$fail=0; echo "[".date('Y-m-d H:i:s')."] BATCH2 DEBUT — ".count($rows)." destinataires\n";
foreach($rows as $r){
 $iso=in_array($r['iso'],['fr','en','de','it','es','ja'])?$r['iso']:'en'; $t=$T[$iso];
 $name=trim($r['firstname'])?:($iso=='it'?'Gentile cliente':($iso=='de'?'Hallo':($iso=='es'?'Estimado cliente':($iso=='ja'?'お客':($iso=='en'?'there':'Bonjour')))));
 $token=md5(uniqid($r['email'].mt_rand(),true));
 $db->execute("INSERT INTO "._DB_PREFIX_."hfm_email_track (token,campaign,email,lang,sent_at) VALUES ('".pSQL($token)."','reactiv','".pSQL($r['email'])."','".pSQL($iso)."','".date('Y-m-d H:i:s')."')");
 $pixel='<img src="'.$BASE.'/track-open.php?t='.$token.'" width="1" height="1" style="display:none" alt="">';
 try{ $msg=(new Swift_Message($t['subj']))->setFrom([Configuration::get('PS_MAIL_USER')=>'Hyaluronic Filler Market'])->setReplyTo('sales@hyaluronicfillermarket.com')->setTo([$r['email']])->setBody(build($t,$iso,$name,$LOGO,$pixel),'text/html','utf-8'); $mailer->send($msg)?$ok++:$fail++; }catch(Exception $e){$fail++;}
 sleep(4); usleep(mt_rand(0,1000000));
}
echo "[".date('Y-m-d H:i:s')."] BATCH2 FIN — $ok envoyés, $fail échecs\n";
