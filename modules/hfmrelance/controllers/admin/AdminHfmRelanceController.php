<?php
class AdminHfmRelanceController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->meta_title = 'Relance Panier';
    }

    public function initContent()
    {
        parent::initContent();
        $db = Db::getInstance(); $p = _DB_PREFIX_;
        $v = function($sql) use ($db) { return $db->getValue($sql); };
        $eur = function($x){ return number_format((float)$x,2,',',' ').' €'; };

        /* ---------- Onglet 1 : Relance Panier ---------- */
        $sent      = (int)$v("SELECT IFNULL(SUM(nb_sent),0) FROM {$p}hfm_relance");
        $carts     = (int)$v("SELECT COUNT(*) FROM {$p}hfm_relance");
        $converted = (int)$v("SELECT COUNT(*) FROM {$p}hfm_relance WHERE converted=1 AND id_order IS NOT NULL");
        $recovered = (float)$v("SELECT IFNULL(SUM(recovered_amount),0) FROM {$p}hfm_relance WHERE converted=1");
        $rate      = $carts ? round(100*$converted/$carts,1) : 0;
        $codes     = (int)$v("SELECT COUNT(*) FROM {$p}cart_rule WHERE code LIKE 'HFM-%'");
        $byLang    = $db->executeS("SELECT lang, SUM(nb_sent) sent, SUM(converted) conv, IFNULL(SUM(recovered_amount),0) amt FROM {$p}hfm_relance GROUP BY lang ORDER BY sent DESC");
        $byStep    = $db->executeS("SELECT step, COUNT(*) n FROM {$p}hfm_relance GROUP BY step ORDER BY step");
        $recent    = $db->executeS("SELECT * FROM {$p}hfm_relance ORDER BY last_sent_at DESC LIMIT 25");
        $stepLbl   = ['0'=>'Non démarré','1'=>'Email #1','2'=>'Email #2 (-5€)','3'=>'Email #3 (-5€)'];

        /* ---------- Onglet 2 : Ouvertures Avis (+ Fidélité) ---------- */
        $camp = function($c) use ($db,$v,$p) {
            return [
                'sent'   => (int)$v("SELECT COUNT(*) FROM {$p}hfm_email_track WHERE campaign='".pSQL($c)."'"),
                'opened' => (int)$v("SELECT COUNT(*) FROM {$p}hfm_email_track WHERE campaign='".pSQL($c)."' AND opened_at IS NOT NULL"),
                'opens'  => (int)$v("SELECT IFNULL(SUM(open_count),0) FROM {$p}hfm_email_track WHERE campaign='".pSQL($c)."'"),
                'rows'   => $db->executeS("SELECT email,lang,sent_at,opened_at,open_count FROM {$p}hfm_email_track WHERE campaign='".pSQL($c)."' ORDER BY sent_at DESC LIMIT 200"),
            ];
        };
        $rv = $camp('review');
        $ly = $camp('loyalty');
        $rvRate = $rv['sent'] ? round(100*$rv['opened']/$rv['sent'],1) : 0;
        $lyRate = $ly['sent'] ? round(100*$ly['opened']/$ly['sent'],1) : 0;

        /* ---------- Onglet 3 : Popup -5€ (BIENVENUE5) ---------- */
        $popCount = (int)$v("SELECT COUNT(*) FROM {$p}hfm_promo_signup");
        $popRows  = $db->executeS("SELECT email,lang,sent_at FROM {$p}hfm_promo_signup ORDER BY sent_at DESC LIMIT 300");
        $bvId     = (int)$v("SELECT id_cart_rule FROM {$p}cart_rule WHERE code='BIENVENUE5'");
        $bvWhere  = "(ocr.id_cart_rule=".$bvId." OR ocr.name='BIENVENUE5')";
        $bvCount  = (int)$v("SELECT COUNT(DISTINCT ocr.id_order) FROM {$p}order_cart_rule ocr WHERE ".$bvWhere);
        $bvCA     = (float)$v("SELECT IFNULL(SUM(o.total_paid),0) FROM {$p}order_cart_rule ocr JOIN {$p}orders o ON o.id_order=ocr.id_order WHERE ".$bvWhere);
        $bvDisc   = (float)$v("SELECT IFNULL(SUM(ocr.value),0) FROM {$p}order_cart_rule ocr WHERE ".$bvWhere);
        $bvRate   = $popCount ? round(100*$bvCount/$popCount,1) : 0;
        $bvOrders = $db->executeS("SELECT o.id_order,o.reference,o.total_paid,ocr.value AS remise,o.date_add,osl.name AS statut FROM {$p}order_cart_rule ocr JOIN {$p}orders o ON o.id_order=ocr.id_order JOIN {$p}order_state_lang osl ON osl.id_order_state=o.current_state AND osl.id_lang=".(int)$this->context->language->id." WHERE ".$bvWhere." GROUP BY o.id_order ORDER BY o.date_add DESC LIMIT 200");

        /* ====================== RENDU ====================== */
        $h = '<style>
        .hfm-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:10px 0 24px}
        .hfm-c{background:#fff;border-radius:10px;padding:18px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
        .hfm-c .l{font-size:11px;color:#999;text-transform:uppercase;letter-spacing:.5px}
        .hfm-c .v{font-size:26px;font-weight:700;margin-top:6px}
        .hfm-green .v{color:#94c55d}.hfm-pink .v{color:#e771bd}.hfm-gold .v{color:#c9a45c}
        .hfm-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px}
        .hfm-box{background:#fff;border-radius:10px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
        .hfm-box h3{margin:0 0 12px;font-size:13px;color:#555}
        .hfm-badge{padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600}
        .hfm-ok{background:#eaf7e1;color:#5a9c2f}.hfm-wait{background:#fff3e0;color:#c77d1a}
        .hfm-no{background:#f1f1f1;color:#999}
        .hfm-tabs{display:flex;gap:6px;margin:8px 0 18px;border-bottom:2px solid #eee}
        .hfm-tab{padding:10px 18px;cursor:pointer;font-weight:600;font-size:14px;color:#888;border-bottom:3px solid transparent;margin-bottom:-2px}
        .hfm-tab.act{color:#e771bd;border-bottom-color:#e771bd}
        .hfm-pane{display:none}.hfm-pane.act{display:block}
        </style>';

        $h .= '<h2 style="margin-bottom:4px">📧 Tableau de bord Emailing</h2>';
        $h .= '<p style="color:#888;margin-top:0">Relance panier · Avis · Popup -5€ · système interne (remplace Carts Guru)</p>';

        // Onglets
        $h .= '<div class="hfm-tabs">'
            . '<div class="hfm-tab act" onclick="hfmTab(this,\'p1\')">🛒 Relance Panier</div>'
            . '<div class="hfm-tab" onclick="hfmTab(this,\'p2\')">⭐ Ouvertures Avis</div>'
            . '<div class="hfm-tab" onclick="hfmTab(this,\'p3\')">🎁 Popup -5€</div>'
            . '<div class="hfm-tab" onclick="hfmTab(this,\'p4\')">🔄 Récupération</div>'
            . '<div class="hfm-tab" onclick="hfmTab(this,\'p5\')">💌 Réactivation</div>'
            . '</div>';

        /* --------- PANE 1 : RELANCE --------- */
        $h .= '<div id="p1" class="hfm-pane act">';
        $h .= '<div class="hfm-cards">'
            . '<div class="hfm-c"><div class="l">Emails envoyés</div><div class="v">'.$sent.'</div><small>'.$carts.' paniers relancés</small></div>'
            . '<div class="hfm-c hfm-pink"><div class="l">Commandes récupérées</div><div class="v">'.$converted.'</div><small>taux '.$rate.'%</small></div>'
            . '<div class="hfm-c hfm-green"><div class="l">Montant récupéré</div><div class="v">'.$eur($recovered).'</div><small>CA sauvé</small></div>'
            . '<div class="hfm-c"><div class="l">Codes promo générés</div><div class="v">'.$codes.'</div><small>uniques -5€</small></div>'
            . '</div>';
        $h .= '<div class="hfm-row"><div class="hfm-box"><h3>Par langue</h3><table class="table"><tr><th>Langue</th><th>Envoyés</th><th>Récup.</th><th>CA</th></tr>';
        foreach ($byLang as $l) $h .= '<tr><td><b>'.Tools::strtoupper($l['lang']).'</b></td><td>'.(int)$l['sent'].'</td><td>'.(int)$l['conv'].'</td><td>'.$eur($l['amt']).'</td></tr>';
        $h .= '</table></div><div class="hfm-box"><h3>Par étape</h3><table class="table"><tr><th>Étape</th><th>Paniers</th></tr>';
        foreach ($byStep as $s) $h .= '<tr><td>'.($stepLbl[$s['step']] ?? $s['step']).'</td><td>'.(int)$s['n'].'</td></tr>';
        $h .= '</table></div></div>';
        $h .= '<div class="hfm-box"><h3>Activité récente</h3><table class="table"><tr><th>Panier</th><th>Email</th><th>Langue</th><th>Type</th><th>Étape</th><th>Envois</th><th>État</th><th>Récupéré</th><th>Dernier envoi</th></tr>';
        foreach ($recent as $r) {
            $badge = $r['converted'] ? '<span class="hfm-badge hfm-ok">Récupéré</span>' : '<span class="hfm-badge hfm-wait">En cours</span>';
            $h .= '<tr><td>#'.(int)$r['id_cart'].'</td><td>'.htmlspecialchars($r['email']).'</td><td>'.Tools::strtoupper($r['lang']).'</td><td>'.$r['audience'].'</td><td>'.(int)$r['step'].'/3</td><td>'.(int)$r['nb_sent'].'</td><td>'.$badge.'</td><td>'.($r['recovered_amount'] ? $eur($r['recovered_amount']) : '—').'</td><td><small>'.$r['last_sent_at'].'</small></td></tr>';
        }
        $h .= '</table></div></div>';

        /* --------- PANE 2 : OUVERTURES AVIS --------- */
        $openTable = function($title,$data) {
            $g = '<div class="hfm-box" style="margin-bottom:20px"><h3>'.$title.'</h3><table class="table"><tr><th>Email</th><th>Langue</th><th>Envoyé le</th><th>Ouvert le</th><th>Ouvertures</th><th>État</th></tr>';
            foreach ($data['rows'] as $r) {
                $opened = !empty($r['opened_at']);
                $badge = $opened ? '<span class="hfm-badge hfm-ok">Ouvert</span>' : '<span class="hfm-badge hfm-no">Pas encore</span>';
                $g .= '<tr><td>'.htmlspecialchars($r['email']).'</td><td>'.Tools::strtoupper($r['lang']).'</td>'
                    . '<td><small>'.$r['sent_at'].'</small></td>'
                    . '<td><small>'.($opened ? $r['opened_at'] : '—').'</small></td>'
                    . '<td>'.(int)$r['open_count'].'</td><td>'.$badge.'</td></tr>';
            }
            if (!$data['rows']) $g .= '<tr><td colspan="6" style="color:#999">Aucun envoi pour le moment.</td></tr>';
            $g .= '</table></div>';
            return $g;
        };
        $h .= '<div id="p2" class="hfm-pane">';
        $h .= '<div class="hfm-cards">'
            . '<div class="hfm-c"><div class="l">Avis · Envoyés</div><div class="v">'.$rv['sent'].'</div><small>campagne avis</small></div>'
            . '<div class="hfm-c hfm-pink"><div class="l">Avis · Ouverts</div><div class="v">'.$rv['opened'].'</div><small>taux '.$rvRate.'%</small></div>'
            . '<div class="hfm-c hfm-gold"><div class="l">Avis · Total ouvertures</div><div class="v">'.$rv['opens'].'</div><small>réouvertures incluses</small></div>'
            . '<div class="hfm-c hfm-green"><div class="l">Fidélité · Ouverts</div><div class="v">'.$ly['opened'].'/'.$ly['sent'].'</div><small>taux '.$lyRate.'%</small></div>'
            . '</div>';
        $h .= $openTable('⭐ Campagne Avis · ouvertures détaillées', $rv);
        $h .= $openTable('💛 Campagne Fidélité · ouvertures détaillées', $ly);
        $h .= '</div>';

        /* --------- PANE 3 : POPUP -5€ --------- */
        $h .= '<div id="p3" class="hfm-pane">';
        $h .= '<div class="hfm-cards">'
            . '<div class="hfm-c hfm-gold"><div class="l">Inscrits popup</div><div class="v">'.$popCount.'</div><small>ont reçu BIENVENUE5</small></div>'
            . '<div class="hfm-c hfm-pink"><div class="l">Commandes avec le code</div><div class="v">'.$bvCount.'</div><small>taux conversion '.$bvRate.'%</small></div>'
            . '<div class="hfm-c hfm-green"><div class="l">CA généré</div><div class="v">'.$eur($bvCA).'</div><small>via BIENVENUE5</small></div>'
            . '<div class="hfm-c"><div class="l">Remises accordées</div><div class="v">'.$eur($bvDisc).'</div><small>coût de l\'opération</small></div>'
            . '</div>';
        $h .= '<div class="hfm-box" style="margin-bottom:20px"><h3>💶 Commandes passées avec le code BIENVENUE5</h3><table class="table"><tr><th>Commande</th><th>Référence</th><th>Montant</th><th>Remise</th><th>État</th><th>Date</th></tr>';
        foreach ($bvOrders as $o) {
            $h .= '<tr><td>#'.(int)$o['id_order'].'</td><td>'.htmlspecialchars($o['reference']).'</td><td><b>'.$eur($o['total_paid']).'</b></td><td>-'.$eur($o['remise']).'</td><td>'.htmlspecialchars($o['statut']).'</td><td><small>'.$o['date_add'].'</small></td></tr>';
        }
        if (!$bvOrders) $h .= '<tr><td colspan="6" style="color:#999">Aucune commande avec ce code pour le moment.</td></tr>';
        $h .= '</table></div>';
        $h .= '<div class="hfm-box"><h3>🎁 Inscrits via la popup newsletter (code -5€ envoyé)</h3><table class="table"><tr><th>Email</th><th>Langue</th><th>Reçu le</th></tr>';
        foreach ($popRows as $r) {
            $h .= '<tr><td>'.htmlspecialchars($r['email']).'</td><td>'.Tools::strtoupper($r['lang']).'</td><td><small>'.$r['sent_at'].'</small></td></tr>';
        }
        if (!$popRows) $h .= '<tr><td colspan="3" style="color:#999">Aucun inscrit pour le moment.</td></tr>';
        $h .= '</table></div></div>';

        /* --------- PANE 4 : RÉCUPÉRATION (campagne 'recover' + code LIVRAISONOFFERTE) --------- */
        $rc = $camp('recover');
        $rcRate = $rc['sent'] ? round(100 * $rc['opened'] / $rc['sent'], 1) : 0;
        $loId = (int) $v("SELECT id_cart_rule FROM {$p}cart_rule WHERE code='LIVRAISONOFFERTE'");
        $loWhere = "(ocr.id_cart_rule=" . $loId . " OR ocr.name='LIVRAISONOFFERTE')";
        $loCount = (int) $v("SELECT COUNT(DISTINCT ocr.id_order) FROM {$p}order_cart_rule ocr WHERE " . $loWhere);
        $loCA = (float) $v("SELECT IFNULL(SUM(o.total_paid),0) FROM {$p}order_cart_rule ocr JOIN {$p}orders o ON o.id_order=ocr.id_order WHERE " . $loWhere);
        $loOrders = $db->executeS("SELECT o.id_order,o.reference,o.total_paid,o.date_add,osl.name AS statut FROM {$p}order_cart_rule ocr JOIN {$p}orders o ON o.id_order=ocr.id_order JOIN {$p}order_state_lang osl ON osl.id_order_state=o.current_state AND osl.id_lang=" . (int) $this->context->language->id . " WHERE " . $loWhere . " GROUP BY o.id_order ORDER BY o.date_add DESC LIMIT 200");

        $h .= '<div id="p4" class="hfm-pane">';
        $h .= '<div class="hfm-cards">'
            . '<div class="hfm-c"><div class="l">Mails de récupération</div><div class="v">' . $rc['sent'] . '</div><small>prospects bloqués relancés</small></div>'
            . '<div class="hfm-c hfm-pink"><div class="l">Ouverts</div><div class="v">' . $rc['opened'] . '</div><small>taux ' . $rcRate . '%</small></div>'
            . '<div class="hfm-c"><div class="l">Commandes avec le code</div><div class="v">' . $loCount . '</div><small>LIVRAISONOFFERTE</small></div>'
            . '<div class="hfm-c hfm-green"><div class="l">CA récupéré</div><div class="v">' . $eur($loCA) . '</div><small>via la relance</small></div>'
            . '</div>';
        $h .= '<div class="hfm-box" style="margin-bottom:20px"><h3>💶 Commandes récupérées (code LIVRAISONOFFERTE)</h3><table class="table"><tr><th>Commande</th><th>Référence</th><th>Montant</th><th>État</th><th>Date</th></tr>';
        foreach ($loOrders as $o) {
            $h .= '<tr><td>#' . (int) $o['id_order'] . '</td><td>' . htmlspecialchars($o['reference']) . '</td><td><b>' . $eur($o['total_paid']) . '</b></td><td>' . htmlspecialchars($o['statut']) . '</td><td><small>' . $o['date_add'] . '</small></td></tr>';
        }
        if (!$loOrders) $h .= '<tr><td colspan="5" style="color:#999">Aucune commande récupérée pour le moment.</td></tr>';
        $h .= '</table></div>';
        $h .= $openTable('🔄 Détail des ouvertures (mails de récupération)', $rc);
        $h .= '</div>';

        /* --------- PANE 5 : RÉACTIVATION (campagne 'reactiv' + code RETOUR5LIV) --------- */
        $ra = $camp('reactiv');
        $raRate = $ra['sent'] ? round(100 * $ra['opened'] / $ra['sent'], 1) : 0;
        $roId = (int) $v("SELECT id_cart_rule FROM {$p}cart_rule WHERE code='RETOUR5LIV'");
        $roWhere = "(ocr.id_cart_rule=" . $roId . " OR ocr.name='RETOUR5LIV')";
        $roCount = (int) $v("SELECT COUNT(DISTINCT ocr.id_order) FROM {$p}order_cart_rule ocr WHERE " . $roWhere);
        $roCA = (float) $v("SELECT IFNULL(SUM(o.total_paid),0) FROM {$p}order_cart_rule ocr JOIN {$p}orders o ON o.id_order=ocr.id_order WHERE " . $roWhere);
        $roOrders = $db->executeS("SELECT o.id_order,o.reference,o.total_paid,o.date_add,osl.name AS statut FROM {$p}order_cart_rule ocr JOIN {$p}orders o ON o.id_order=ocr.id_order JOIN {$p}order_state_lang osl ON osl.id_order_state=o.current_state AND osl.id_lang=" . (int) $this->context->language->id . " WHERE " . $roWhere . " GROUP BY o.id_order ORDER BY o.date_add DESC LIMIT 200");

        $h .= '<div id="p5" class="hfm-pane">';
        $h .= '<div class="hfm-cards">'
            . '<div class="hfm-c"><div class="l">Mails de réactivation</div><div class="v">' . $ra['sent'] . '</div><small>clients +1 an relancés</small></div>'
            . '<div class="hfm-c hfm-pink"><div class="l">Ouverts</div><div class="v">' . $ra['opened'] . '</div><small>taux ' . $raRate . '%</small></div>'
            . '<div class="hfm-c"><div class="l">Commandes avec le code</div><div class="v">' . $roCount . '</div><small>RETOUR5LIV</small></div>'
            . '<div class="hfm-c hfm-green"><div class="l">CA réactivé</div><div class="v">' . $eur($roCA) . '</div><small>via RETOUR5LIV</small></div>'
            . '</div>';
        $h .= '<div class="hfm-box" style="margin-bottom:20px"><h3>💶 Commandes réactivées (code RETOUR5LIV)</h3><table class="table"><tr><th>Commande</th><th>Référence</th><th>Montant</th><th>État</th><th>Date</th></tr>';
        foreach ($roOrders as $o) {
            $h .= '<tr><td>#' . (int) $o['id_order'] . '</td><td>' . htmlspecialchars($o['reference']) . '</td><td><b>' . $eur($o['total_paid']) . '</b></td><td>' . htmlspecialchars($o['statut']) . '</td><td><small>' . $o['date_add'] . '</small></td></tr>';
        }
        if (!$roOrders) $h .= '<tr><td colspan="5" style="color:#999">Aucune commande réactivée pour le moment.</td></tr>';
        $h .= '</table></div>';
        $h .= $openTable('💌 Détail des ouvertures (mails de réactivation)', $ra);
        $h .= '</div>';

        $h .= '<script>function hfmTab(el,id){var t=el.parentNode.querySelectorAll(".hfm-tab");for(var i=0;i<t.length;i++)t[i].classList.remove("act");el.classList.add("act");var pn=document.querySelectorAll(".hfm-pane");for(var j=0;j<pn.length;j++)pn[j].classList.remove("act");document.getElementById(id).classList.add("act");}</script>';

        $this->content .= $h;
        $this->context->smarty->assign('content', $this->content);
    }
}
