<?php
/**
* 2013-2017 RouGe Services
*
* DISCLAIMER
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA and Contributors <contact@prestashop.com>
*  @copyright Since 2007 PrestaShop SA and Contributors
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*  @version   Release: $Revision: 7685 $
*/

 error_reporting(E_ALL);
 ini_set('display_errors', 0);

if (!defined('_PS_VERSION_')) {
    exit;
}
class StatsCompta extends Module
{
    private $option;
    public function __construct()
    {
        $this->name = 'statscompta';
        $this->tab = 'analytics_stats';
        $this->version = '2.5.019';
        $this->author = 'RouGe Services';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('Accounting synthesis');
        $this->description = $this->l('This module allows you to display a table summary of accounting controls. It is ideal for the VAT return ! After the installation, the tables are dispayed in the page STATS and the sub-title Accounting synthesis');
        $this->module_key = '05745ce5521c714e2e519e5ca3ae0182';
        $this->author_address = '0xc5e19A710253a6980FEF65122EC7cc27d95Bf10C';
    }
    public function install()
    {
        return (parent::install() && $this->registerHook('displayAdminStatsModules'));
    }
    public function uninstall()
    {
        if (parent::uninstall()) {
            return true;
        } else {
            return false;
        }
    }
    public function hookDisplayAdminStatsModules()
    {
        $version_ps = (_PS_VERSION_ >= '1.5' ? '1.5' : '1.4');
        static $row = 0;
        static $row_avoir = 0;
        static $titres_colonne_taxe;
        static $titres_colonne_mode_paiement;
        static $titres_colonne_taxe_sous_ligne;
        static $titres_colonne_taxe_avoir = '';
        static $queries_detail_avoir = '';
        static $queries_detail_fin_avoir = '';
        static $query = '';
        static $query_tva = '';
        static $query_mode_paiement = '';
        static $left_outer_join_tva = '';
        static $color;
        static $color_if_error;
        static $rollup;
        static $html = '';
        static $tableau = '';
        static $totaux = '';
        static $montant_achat_ht_total = 0;
        static $montant_ecotax_total = 0;
        static $frais_port_ht_total = 0;
        static $frais_port_ttc_total = 0;
        static $frais_port_ht_sans_tva_total = 0;
        static $currency;
        static $date_for_analysis = 'o.date_add';
        static $sortby_for_orders = 'o.date_add';
        if (class_exists('Context')) {
            $this->context = Context::getContext();
        }
        if (!isset($this->context->cookie->stats_SynthGroupBy)) {
            $this->context->cookie->stats_SynthGroupBy = 2;
        }
        if (Tools::isSubmit('submitSynthGroupBy')) {
            $this->context->cookie->stats_SynthGroupBy = Tools::getValue('stats_SynthGroupBy');
        }
        if (!isset($this->context->cookie->stats_SynthCurrency)) {
            $this->context->cookie->stats_SynthCurrency = 1;
        }
        if (Tools::isSubmit('submitSynthCurrency')) {
            $this->context->cookie->stats_SynthCurrency = Tools::getValue('stats_SynthCurrency');
        }
        if (!isset($this->context->cookie->show_ErrorColor)) {
            $this->context->cookie->show_ErrorColor = 'couleur';
        }
        if (Tools::isSubmit('submitErrorColor')) {
            $this->context->cookie->show_ErrorColor = Tools::getValue('show_ErrorColor');
        }
        if ($this->context->cookie->show_ErrorColor == 'normal') {
            $color_if_error = '';
        } else {
            $color_if_error = '#FF0000';
        }
        if (!isset($this->context->cookie->stats_DateForAnalysis)) {
            $this->context->cookie->stats_DateForAnalysis = 'order';
        }
        if (Tools::isSubmit('submitDateForAnalysis')) {
            $this->context->cookie->stats_DateForAnalysis = Tools::getValue('stats_DateForAnalysis');
        }
        if ($this->context->cookie->stats_DateForAnalysis == 'order') {
            $date_for_analysis = 'o.date_add';
        } else {
            $date_for_analysis = 'oi.FactureDate';
        }
        if (!isset($this->context->cookie->stats_SortOrderBy)) {
            $this->context->cookie->stats_SortOrderBy = 'orderId';
        }
        if (Tools::isSubmit('submitSortOrderBy')) {
            $this->context->cookie->stats_SortOrderBy = Tools::getValue('stats_SortOrderBy');
        }
        switch ($this->context->cookie->stats_SortOrderBy) {
            case 'orderId':
                $sortby_for_orders = 'o.id_order';
                break;
            case 'invoiceId':
                $sortby_for_orders = 'oi.FactureId';
                break;
            case 'customerId':
                $sortby_for_orders = 'o.id_customer';
                break;
            case 'totalPaid':
                $sortby_for_orders = 'SUM(oi.CA_TTC)';
                break;
        }
        static $filtre_orders = '';
        static $filtre_dates_variable = '';
        static $id_currency = '';
        static $language;
        $language = $this->context->language->id;
        $id_currency = $this->context->cookie->stats_SynthCurrency;
        if ($version_ps == '1.5') {
            $ru = AdminController::$currentIndex.'&module='.$this->name.'&token='.Tools::getValue('token');
        }
        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('SET SQL_BIG_SELECTS=1');
        $currencies = DB::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'currency WHERE active = 1');
        $currency = Currency::getCurrency($id_currency);
        $filtre_orders = ' o.valid = \'1\''.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o').' AND o.id_currency = '.$id_currency.' AND o.payment NOT LIKE \'Achat%magasin\' ';
        $filtre_dates_variable = ' AND '.$date_for_analysis.' BETWEEN '.ModuleGraph::getDateBetween().' ';
        $filtre_avoirs = ' o.valid = \'1\''.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o').' AND o.id_currency = '.$id_currency.' AND os.date_add BETWEEN '.ModuleGraph::getDateBetween().' AND o.payment NOT LIKE \'Achat%magasin\' ';
        $taxes = Db::getInstance()->ExecuteS('SELECT DISTINCT(ROUND(t.rate, 2)) tax_rate
                                                FROM '._DB_PREFIX_.'order_detail_tax odt
                                                INNER JOIN '._DB_PREFIX_.'order_detail od ON od.id_order_detail = odt.id_order_detail
                                                INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order
                                                INNER JOIN '._DB_PREFIX_.'tax t ON t.id_tax = odt.id_tax
                                                LEFT OUTER JOIN (
                                                            SELECT 
                                                                fact.id_order,
                                                                fact.number as FactureId,
                                                                fact.total_paid_tax_incl as CA_TTC,
                                                                fact.total_paid_tax_excl as CA_HT,
                                                                CAST(fact.date_add as DATE) as FactureDate
                                                            FROM '._DB_PREFIX_.'order_invoice as fact
                                                            GROUP BY fact.id_order
                                                            ) as oi ON oi.id_order = o.id_order
                                                WHERE'.$filtre_orders.$filtre_dates_variable.'
                                                ORDER BY tax_rate ASC');
        $taxes_avoir = Db::getInstance()->ExecuteS('SELECT DISTINCT(ROUND(t.rate, 2)) tax_rate
                                                FROM '._DB_PREFIX_.'order_detail_tax odt
                                                INNER JOIN '._DB_PREFIX_.'order_detail od ON od.id_order_detail = odt.id_order_detail
                                                INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order
                                                INNER JOIN '._DB_PREFIX_.'order_slip_detail osd ON osd.id_order_detail = od.id_order_detail
                                                INNER JOIN '._DB_PREFIX_.'order_slip os ON os.id_order_slip = osd.id_order_slip
                                                INNER JOIN '._DB_PREFIX_.'tax t ON t.id_tax = odt.id_tax
                                                WHERE '.$filtre_avoirs.'
                                                ORDER BY tax_rate ASC');
        $query_totaux = 'SELECT
                                                SUM(oi.CA_TTC) as ca_ttc_total,
                                                SUM(oi.CA_HT) as ca_ht_total,
                                                SUM(o.total_shipping_tax_incl * IF(ocr.not_free_shipping IS NULL, 1,ocr.not_free_shipping)) as frais_port_ttc_total,
                                                SUM((o.total_shipping_tax_incl - o.total_shipping_tax_excl) * IF(ocr.not_free_shipping IS NULL, 1,ocr.not_free_shipping)) as frais_port_tva_total,
                                                SUM(IF(o.carrier_tax_rate = 0, o.total_shipping_tax_excl * IF(ocr.not_free_shipping IS NULL, 1,ocr.not_free_shipping), 0)) as frais_port_ht_sans_tva_total, 
                                                SUM(IF(o.carrier_tax_rate = 0, 0, o.total_shipping_tax_excl * IF(ocr.not_free_shipping IS NULL, 1,ocr.not_free_shipping))) as frais_port_ht_total, 
                                                SUM(total_wrapping_tax_excl) as emballage_ht_total,
                                                SUM(total_wrapping_tax_incl) as emballage_ttc_total,
                                                SUM(total_wrapping_tax_incl - total_wrapping_tax_excl) as emballage_tva_total,
                                                SUM(o.total_products - (IF(o.total_discounts_tax_excl = 0, 0, o.total_discounts_tax_excl - (o.total_shipping_tax_excl * (IF(ocr.free_shipping IS NULL, 1,ocr.free_shipping)))))) as montant_ht_total,
                                                SUM(total_discounts_tax_excl) as reductions_ht_total,
                                                SUM(o.total_products_wt - (IF(o.total_discounts_tax_incl = 0, 0, o.total_discounts_tax_incl - (o.total_shipping_tax_incl * (IF(ocr.free_shipping IS NULL, 1,ocr.free_shipping)))))) as montant_ttc_total,
                                                SUM(total_discounts_tax_incl) as reductions_ttc_total,
                                                SUM(odt_tex.HT_Sans_Taxe) as montant_ht_total_ventes_sans_tva,
                                                COUNT(o.id_order) as nombre_commandes_total
                                            FROM '._DB_PREFIX_.'orders o
                                            LEFT OUTER JOIN (
                                                SELECT 
                                                ocr.id_order,
                                                SUM(ocr.value),
                                                SUM(ocr.value_tax_excl),
                                                MAX(ocr.free_shipping) as free_shipping,
                                                MIN(NOT(ocr.free_shipping)) as not_free_shipping
                                            FROM '._DB_PREFIX_.'order_cart_rule as ocr
                                            GROUP BY ocr.id_order
                                            ) as ocr ON ocr.id_order = o.id_order
                                            LEFT OUTER JOIN (
                                                SELECT 
                                                    fact.id_order,
                                                    fact.number as FactureId,
                                                    fact.total_paid_tax_incl as CA_TTC,
                                                    fact.total_paid_tax_excl as CA_HT,
                                                    CAST(fact.date_add as DATE) as FactureDate
                                                FROM '._DB_PREFIX_.'order_invoice as fact
                                                GROUP BY fact.id_order
                                            ) as oi ON oi.id_order = o.id_order
                                            LEFT OUTER JOIN (
                                                SELECT 
                                                    o.id_order,
                                                    ROUND(SUM(od.total_price_tax_excl * ((od.product_quantity - IF(ocr.gift_product = od.product_id, 1, 0)) / od.product_quantity) * ((o.total_products + IF(gift.amount IS NULL, 0, gift.amount) - (IF(o.total_discounts_tax_excl = 0, 0, o.total_discounts_tax_excl - (o.total_shipping_tax_excl * (IF(ocr.free_shipping IS NULL, 1,ocr.free_shipping)))))) / o.total_products)), 2) as HT_Sans_Taxe
                                                FROM '._DB_PREFIX_.'order_detail od
                                                INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order
                                                LEFT OUTER JOIN (
                                                    SELECT 
                                                        ocr.id_order,
                                                        SUM(ocr.value),
                                                        SUM(ocr.value_tax_excl),
                                                        MAX(ocr.free_shipping) as free_shipping,
                                                        MIN(NOT(ocr.free_shipping)) as not_free_shipping,
                                                        cr.gift_product as gift_product
                                                    FROM '._DB_PREFIX_.'order_cart_rule as ocr
                                                    LEFT OUTER JOIN '._DB_PREFIX_.'cart_rule as cr ON cr.id_cart_rule = ocr.id_cart_rule
                                                    GROUP BY ocr.id_order
                                                ) as ocr ON ocr.id_order = o.id_order
                                                LEFT OUTER JOIN (
                                                    SELECT 
                                                        od.id_order as id_order,
                                                        SUM(od.product_price) as amount
                                                    FROM '._DB_PREFIX_.'order_detail as od
                                                    LEFT OUTER JOIN '._DB_PREFIX_.'order_cart_rule as ocr ON ocr.id_order = od.id_order
                                                    LEFT OUTER JOIN '._DB_PREFIX_.'cart_rule as cr ON cr.id_cart_rule = ocr.id_cart_rule
                                                    WHERE cr.gift_product = od.product_id
                                                    GROUP BY od.id_order
                                                ) as gift ON gift.id_order = o.id_order
                                                LEFT OUTER JOIN '._DB_PREFIX_.'order_detail_tax odt ON odt.id_order_detail = od.id_order_detail
                                                WHERE odt.id_order_detail IS NULL OR odt.id_tax = 0
                                                GROUP BY o.id_order
                                            ) as odt_tex ON odt_tex.id_order = o.id_order
                                            WHERE'.$filtre_orders.$filtre_dates_variable;
        $totaux = Db::getInstance()->ExecuteS($query_totaux);
        $ca_ttc_total = $totaux[0]['ca_ttc_total'];
        $ca_ht_total = $totaux[0]['ca_ht_total'];
        $frais_port_ht_total = $totaux[0]['frais_port_ht_total'];
        $frais_port_ht_sans_tva_total = $totaux[0]['frais_port_ht_sans_tva_total'];
        $frais_port_ttc_total = $totaux[0]['frais_port_ttc_total'];
        $frais_port_tva_total = $totaux[0]['frais_port_tva_total'];
        $emballage_ht_total = $totaux[0]['emballage_ht_total'];
        $emballage_ttc_total = $totaux[0]['emballage_ttc_total'];
        $emballage_tva_total = $totaux[0]['emballage_tva_total'];
        $montant_ht_total = $totaux[0]['montant_ht_total'];
        $reductions_ht_total = $totaux[0]['reductions_ht_total'];
        $montant_ttc_total = $totaux[0]['montant_ttc_total'];
        $reductions_ttc_total = $totaux[0]['reductions_ttc_total'];
        $reductions_total = $reductions_ht_total + $reductions_ttc_total;
        $montant_ht_total_ventes_sans_tva = $totaux[0]['montant_ht_total_ventes_sans_tva'];
        $nombre_commandes_total = $totaux[0]['nombre_commandes_total'];
                                        //var_dump($nombre_commandes_total);die;
        $montant_ecotax_total = Db::getInstance()->getValue('SELECT SUM(od.ecotax * od.product_quantity) as ecotax
                                                            FROM '._DB_PREFIX_.'order_detail as od
                                                            INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order
                                                            LEFT OUTER JOIN (
                                                                SELECT 
                                                                    fact.id_order,
                                                                    CAST(fact.date_add as DATE) as FactureDate
                                                                FROM '._DB_PREFIX_.'order_invoice as fact
                                                                GROUP BY fact.id_order
                                                            ) as oi ON oi.id_order = o.id_order
                                                            WHERE'.$filtre_orders.$filtre_dates_variable);
        $montant_achat_ht_total = Db::getInstance()->getValue('SELECT SUM(od3.purchase_supplier_price * od3.product_quantity * o.conversion_rate)
                                                            FROM '._DB_PREFIX_.'order_detail od3
                                                            INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = od3.id_order
                                                            LEFT OUTER JOIN (
                                                                SELECT 
                                                                    fact.id_order,
                                                                    CAST(fact.date_add as DATE) as FactureDate
                                                                FROM '._DB_PREFIX_.'order_invoice as fact
                                                                GROUP BY fact.id_order
                                                            ) as oi ON oi.id_order = o.id_order
                                                            WHERE'.$filtre_orders.$filtre_dates_variable);
        $marge_nette_totale  = $montant_ht_total - $montant_achat_ht_total;
        $nombre_clients_avec_societe = Db::getInstance()->getValue('SELECT count(*)
                                                            FROM '._DB_PREFIX_.'orders o
                                                            LEFT OUTER JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
                                                            LEFT OUTER JOIN '._DB_PREFIX_.'address ai ON ai.id_address = o.id_address_invoice
                                                            LEFT OUTER JOIN (
                                                                SELECT 
                                                                    fact.id_order,
                                                                    CAST(fact.date_add as DATE) as FactureDate
                                                                FROM '._DB_PREFIX_.'order_invoice as fact
                                                                GROUP BY fact.id_order
                                                            ) as oi ON oi.id_order = o.id_order
                                                            WHERE ai.company is not null and ai.company <> "" AND'.$filtre_orders.$filtre_dates_variable);
        $nombre_clients_avec_numero_tva = Db::getInstance()->getValue('SELECT count(*)
                                                            FROM '._DB_PREFIX_.'orders o
                                                            LEFT OUTER JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
                                                            LEFT OUTER JOIN '._DB_PREFIX_.'address ai ON ai.id_address = o.id_address_invoice
                                                            LEFT OUTER JOIN (
                                                                SELECT 
                                                                    fact.id_order,
                                                                    CAST(fact.date_add as DATE) as FactureDate
                                                                FROM '._DB_PREFIX_.'order_invoice as fact
                                                                GROUP BY fact.id_order
                                                            ) as oi ON oi.id_order = o.id_order
                                                            WHERE ai.vat_number is not null and ai.vat_number <> "" AND'.$filtre_orders.$filtre_dates_variable);
                                        //var_dump($nombre_clients_avec_societe);die;
        $nombre_commandes_avec_numero_colis = Db::getInstance()->getValue('SELECT count(*)
                                                            FROM '._DB_PREFIX_.'order_carrier oc
                                                            INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = oc.id_order
                                                            LEFT OUTER JOIN (
                                                                SELECT 
                                                                    fact.id_order,
                                                                    CAST(fact.date_add as DATE) as FactureDate
                                                                FROM '._DB_PREFIX_.'order_invoice as fact
                                                                GROUP BY fact.id_order
                                                            ) as oi ON oi.id_order = o.id_order
                                                            WHERE oc.tracking_number is not null and oc.tracking_number <> "" AND'.$filtre_orders.$filtre_dates_variable);
        $nombre_avoirs_total = Db::getInstance()->getValue('SELECT count(id_order_slip)
                                                            FROM '._DB_PREFIX_.'order_slip os
                                                            INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = os.id_order
                                                            WHERE'.$filtre_avoirs);
        $queries_detail_fin_avoir = ',-(os.shipping_cost_amount / (1 + o.carrier_tax_rate / 100)) as Frais_Port_HT,
                        -(os.shipping_cost_amount) as Frais_Port_TTC,
                        -((os.shipping_cost_amount - (os.shipping_cost_amount / (1 + o.carrier_tax_rate / 100)))) as Frais_Port_TVA';
        // Forgeage de la requete partielle des montants  dynamiques de TVA
        foreach ($taxes as $tax) {
            $row++;
            $tx = 'tx'.$row;
            $$tx = $tax['tax_rate'];
            $titres_colonne_taxe = $titres_colonne_taxe.'<th style = "text-align:center" colspan = 2>'.$this->l('Tax rate').' '.number_format($tax['tax_rate'], 2).' %</th>';
            $titres_colonne_taxe_sous_ligne = $titres_colonne_taxe_sous_ligne.$this->getHeader($this->l('Tax excl.'), 1, 1).'
                                                                            '.$this->getHeader($this->l('VAT'), 1, 1);
            $query_tva = $query_tva.'ROUND(SUM(odt_tin_tx'.$row.'.TVA_tx), 2) as TVA_tx'.$row.', 
                                        ROUND(SUM(odt_tin_tx'.$row.'.HT_tx), 2) as HT_tx'.$row.',';
            $left_outer_join_tva = $left_outer_join_tva.'LEFT OUTER JOIN (
                    SELECT 
                        od.id_order,
                        ROUND(SUM((od.total_price_tax_incl - od.total_price_tax_excl) * ((o.total_products + IF(gift.amount IS NULL, 0, gift.amount) -  (IF(o.total_discounts_tax_excl = 0, 0, o.total_discounts_tax_excl - (o.total_shipping_tax_excl * (IF(ocr.free_shipping IS NULL, 1,ocr.free_shipping)))))) / o.total_products)) - (IF(gift.amount IS NULL, 0, gift.amount) * t.rate / 100), 2) as TVA_tx,
                        ROUND(SUM(od.total_price_tax_excl * ((o.total_products + IF(gift.amount IS NULL, 0, gift.amount) - (IF(o.total_discounts_tax_excl = 0, 0, o.total_discounts_tax_excl - (o.total_shipping_tax_excl * (IF(ocr.free_shipping IS NULL, 1,ocr.free_shipping)))))) / o.total_products))  - IF(gift.amount IS NULL, 0, gift.amount), 2) as HT_tx,
                        ROUND(SUM(od.total_price_tax_incl - od.reduction_amount_tax_incl), 2) as TTC_tx,
                        t.rate as tax_rate
                    FROM '._DB_PREFIX_.'order_detail od
                    INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order 
                    INNER JOIN '._DB_PREFIX_.'order_detail_tax odt ON odt.id_order_detail = od.id_order_detail
                    INNER JOIN '._DB_PREFIX_.'tax t ON t.id_tax = odt.id_tax 
                    LEFT OUTER JOIN (
                        SELECT 
                            od.id_order as id_order,
                            SUM(od.product_price) as amount
                        FROM '._DB_PREFIX_.'order_detail as od
                        LEFT OUTER JOIN '._DB_PREFIX_.'order_cart_rule as ocr ON ocr.id_order = od.id_order
                        LEFT OUTER JOIN '._DB_PREFIX_.'cart_rule as cr ON cr.id_cart_rule = ocr.id_cart_rule
                        WHERE cr.gift_product = od.product_id
                        GROUP BY od.id_order
                    ) as gift ON gift.id_order = od.id_order
                    LEFT OUTER JOIN (
                        SELECT 
                            ocr.id_order,
                            SUM(ocr.value),
                            SUM(ocr.value_tax_excl),
                            MAX(ocr.free_shipping) as free_shipping,
                            MIN(NOT(ocr.free_shipping)) as not_free_shipping,
                            cr.gift_product as gift_product
                        FROM '._DB_PREFIX_.'order_cart_rule as ocr
                        LEFT OUTER JOIN '._DB_PREFIX_.'cart_rule as cr ON cr.id_cart_rule = ocr.id_cart_rule
                        GROUP BY ocr.id_order
                        ) as ocr ON ocr.id_order = od.id_order
                    WHERE ROUND(t.rate,2) = '.$$tx.'
                    /*AND (ocr.gift_product IS NULL OR ocr.gift_product <> od.product_id)  commenté pour hotfix 2.5.010 7620 avec ajout réduction gift.amount sur HT et TVA*/
                    GROUP BY od.id_order
                    ) as odt_tin_tx'.$row.' ON odt_tin_tx'.$row.'.id_order = o.id_order
                    ';
        }
        $query = 'SELECT
                    o.id_order as Cde,
                    osl.name as Status,
                    YEAR('.$date_for_analysis.') As Annee, 
                    CONCAT(YEAR('.$date_for_analysis.'),\'-\',date_format('.$date_for_analysis.',\'%m\')) as Mois, 
                    CAST('.$date_for_analysis.' AS DATE) As Jour,
                    CAST(o.date_add AS DATE) As Date_Add,
                    COUNT(o.id_order) as Nb_Cdes,    
                    o.reference as Ref,    
                    oc.tracking_number as NumColis,    
                    CONCAT(c.firstname, \' \', c.lastname) as Client,
                    c.id_customer as ClientId,
                    oi.FactureId as FactureId, 
                    oi.FactureDate as FactureDate,
                    SUM(oi.CA_TTC) as CA_TTC,
                    SUM(oi.CA_HT) as CA_HT,
                    SUM(o.total_products_wt - (IF( o.total_discounts_tax_incl = 0, 0, o.total_discounts_tax_incl - (o.total_shipping_tax_incl * (IF(ocr.free_shipping IS NULL, 1,ocr.free_shipping)))))) as Mtt_TTC,
                    SUM(o.total_products - (IF( o.total_discounts_tax_excl = 0, 0, o.total_discounts_tax_excl - (o.total_shipping_tax_excl * (IF(ocr.free_shipping IS NULL, 1,ocr.free_shipping)))))) as Mtt_HT, 
                    SUM(odt_tex.HT_Sans_Taxe) as HT_Sans_Taxe,
                    SUM(prod.ecotax) as Ecotax_TTC,
                    '.$query_tva.'
                    IF(car.name IS NULL, \''.$this->l('None').'\', car.name) as Carrier_Name,
                    SUM(o.total_shipping_tax_incl * IF(ocr.not_free_shipping IS NULL, 1,ocr.not_free_shipping)) as Frais_Port_TTC, 
                    SUM(IF(o.carrier_tax_rate = 0, o.total_shipping_tax_excl * IF(ocr.not_free_shipping IS NULL, 1,ocr.not_free_shipping), 0)) as Frais_Port_HT_Sans_TVA, 
                    SUM(IF(o.carrier_tax_rate = 0, 0, o.total_shipping_tax_excl * IF(ocr.not_free_shipping IS NULL, 1,ocr.not_free_shipping))) as Frais_Port_HT, 
                    SUM((o.total_shipping_tax_incl - o.total_shipping_tax_excl) * IF(ocr.not_free_shipping IS NULL, 1,ocr.not_free_shipping)) as Frais_Port_TVA, 
                    SUM(o.total_wrapping_tax_incl) as Emballage_TTC, 
                    SUM(o.total_wrapping_tax_excl) as Emballage_HT, 
                    SUM(o.total_wrapping_tax_incl - o.total_wrapping_tax_excl) as Emballage_TVA,
                    SUM(o.total_discounts_tax_incl) as Reduc_TTC,
                    SUM(o.total_discounts_tax_excl) as Reduc_HT,
                    o.payment as Mode_Paiement,
                    ai.vat_number as Numero_TVA,
                    ai.company as Societe,
                    ci.iso_code as Pays_Facturation,
                    cd.iso_code as Pays_Livraison,
                    si.name as Etat_Facturation,
                    sd.name as Etat_Livraison,
                    CONCAT(ci.iso_code, IF(si.name IS NULL, \'\' , CONCAT(\' - \', si.name))) as Pays_Etat_Facturation,
                    CONCAT(cd.iso_code, IF(sd.name IS NULL, \'\', CONCAT(\' - \', sd.name))) as Pays_Etat_Livraison,
                    zi.name as Zone_Facturation,
                    zd.name as Zone_Livraison,
                    gl.name as Groupe_Client,
                    CONCAT(gl.name, \' - \', ci.iso_code) as Groupe_Client_Pays_Facturation,
                    CONCAT(gl.name, \' - \', zi.name) as Groupe_Client_Zone_Facturation,
                    CONCAT(gl.name, \' - \', cd.iso_code) as Groupe_Client_Pays_Livraison,
                    CONCAT(gl.name, \' - \', zd.name) as Groupe_Client_Zone_Livraison,
                    CONCAT(o.payment, \' - \', cd.iso_code) as Mode_Paiement_Pays_Livraison,
                    CONCAT(o.payment, \' - \', zd.name) as Mode_Paiement_Zone_Livraison,
                    ROUND(SUM(prod.Achat_HT), 2) as Achat_HT,
                    ROUND(SUM(prod.Marge_Nette), 2) as Marge_Nette
                    FROM '._DB_PREFIX_.'orders o
                    LEFT OUTER JOIN (
                        SELECT 
                            ocr.id_order,
                            SUM(ocr.value),
                            SUM(ocr.value_tax_excl),
                            MAX(ocr.free_shipping) as free_shipping,
                            MIN(NOT(ocr.free_shipping)) as not_free_shipping
                        FROM '._DB_PREFIX_.'order_cart_rule as ocr
                        GROUP BY ocr.id_order
                    ) as ocr ON ocr.id_order = o.id_order
                    LEFT OUTER JOIN (
                        SELECT 
                            fact.id_order,
                            fact.number as FactureId,
                            fact.total_paid_tax_incl as CA_TTC,
                            fact.total_paid_tax_excl as CA_HT,
                            CAST(fact.date_add as DATE) as FactureDate
                        FROM '._DB_PREFIX_.'order_invoice as fact
                        GROUP BY fact.id_order
                    ) as oi ON oi.id_order = o.id_order
                    LEFT OUTER JOIN (
                        SELECT 
                            o.id_order,
                            od.ecotax * od.product_quantity as ecotax_TTC,
                            ROUND(SUM(od.total_price_tax_excl * ((od.product_quantity - IF(ocr.gift_product = od.product_id, 1, 0)) / od.product_quantity) * ((o.total_products + IF(gift.amount IS NULL, 0, gift.amount) - (IF(o.total_discounts_tax_excl = 0, 0, o.total_discounts_tax_excl - (o.total_shipping_tax_excl * (IF(ocr.free_shipping IS NULL, 1,ocr.free_shipping)))))) / o.total_products)), 2) as HT_Sans_Taxe
                        FROM '._DB_PREFIX_.'order_detail od
                        INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order
                        LEFT OUTER JOIN (
                            SELECT 
                                ocr.id_order as id_order,
                                SUM(ocr.value),
                                SUM(ocr.value_tax_excl),
                                MAX(ocr.free_shipping) as free_shipping,
                                MIN(NOT(ocr.free_shipping)) as not_free_shipping,
                                cr.gift_product as gift_product
                            FROM '._DB_PREFIX_.'order_cart_rule as ocr
                            LEFT OUTER JOIN '._DB_PREFIX_.'cart_rule as cr ON cr.id_cart_rule = ocr.id_cart_rule
                            GROUP BY ocr.id_order
                        ) as ocr ON ocr.id_order = o.id_order
                        LEFT OUTER JOIN (
                            SELECT 
                                od.id_order as id_order,
                                SUM(od.product_price) as amount
                            FROM '._DB_PREFIX_.'order_detail as od
                            LEFT OUTER JOIN '._DB_PREFIX_.'order_cart_rule as ocr ON ocr.id_order = od.id_order
                            LEFT OUTER JOIN '._DB_PREFIX_.'cart_rule as cr ON cr.id_cart_rule = ocr.id_cart_rule
                            WHERE cr.gift_product = od.product_id
                        GROUP BY od.id_order
                        ) as gift ON gift.id_order = o.id_order
                        WHERE NOT EXISTS (
                        SELECT odt.id_order_detail
                        FROM '._DB_PREFIX_.'order_detail_tax odt 
                        WHERE odt.id_order_detail = od.id_order_detail AND odt.id_tax <> 0)
                        GROUP BY o.id_order
                    ) as odt_tex ON odt_tex.id_order = o.id_order
                    '.$left_outer_join_tva.'
                    LEFT OUTER JOIN (
                        SELECT 
                            o.id_order,
                            SUM(od.ecotax * od.product_quantity) as ecotax,
                            SUM(od.purchase_supplier_price * od.product_quantity * o.conversion_rate) as Achat_HT,
                            SUM(od.total_price_tax_excl - (od.purchase_supplier_price * od.product_quantity * o.conversion_rate)) as Marge_Nette
                        FROM '._DB_PREFIX_.'order_detail od
                        INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = od.id_order
                        LEFT OUTER JOIN (
                            SELECT 
                                ocr.id_order,
                                SUM(ocr.value),
                                SUM(ocr.value_tax_excl),
                                MAX(ocr.free_shipping) as free_shipping,
                                MIN(NOT(ocr.free_shipping)) as not_free_shipping
                            FROM '._DB_PREFIX_.'order_cart_rule as ocr
                            GROUP BY ocr.id_order
                            ) as ocr ON ocr.id_order = o.id_order
                        GROUP BY o.id_order
                        ) as prod ON prod.id_order = o.id_order
                        
                    LEFT OUTER JOIN '._DB_PREFIX_.'order_state_lang osl ON osl.id_order_state = o.current_state and osl.id_lang = '.$language.'
                    LEFT OUTER JOIN '._DB_PREFIX_.'order_carrier oc ON oc.id_order = o.id_order
                    LEFT OUTER JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
                    LEFT OUTER JOIN '._DB_PREFIX_.'address ai ON ai.id_address = o.id_address_invoice
                    LEFT OUTER JOIN '._DB_PREFIX_.'country ci on ci.id_country = ai.id_country
                    LEFT OUTER JOIN '._DB_PREFIX_.'state si on si.id_state = ai.id_state
                    LEFT OUTER JOIN '._DB_PREFIX_.'zone zi on zi.id_zone = ci.id_zone
                    LEFT OUTER JOIN '._DB_PREFIX_.'address ad ON ad.id_address = o.id_address_delivery
                    LEFT OUTER JOIN '._DB_PREFIX_.'country cd on cd.id_country = ad.id_country
                    LEFT OUTER JOIN '._DB_PREFIX_.'state sd on sd.id_state = ad.id_state
                    LEFT OUTER JOIN '._DB_PREFIX_.'zone zd on zd.id_zone = cd.id_zone
                    LEFT OUTER JOIN '._DB_PREFIX_.'carrier car on car.id_carrier = o.id_carrier
                    LEFT OUTER JOIN '._DB_PREFIX_.'group_lang gl on gl.id_group = c.id_default_group and gl.id_lang = '.$language.'
                    
                    WHERE'.$filtre_orders.$filtre_dates_variable;
                                        //var_dump($query);die;
        foreach ($taxes_avoir as $tax) {
            $row_avoir++;
            $tx = 'tx'.$row_avoir;
            $$tx = $tax['tax_rate'];
            $titres_colonne_taxe_avoir = $titres_colonne_taxe_avoir.'<th style = "text-align:center">'.$this->l('VAT').'<br>'.number_format($tax['tax_rate'], 2).' %</th>';
            $queries_detail_avoir = $queries_detail_avoir.',(- ROUND((SELECT (SUM(osd2.amount_tax_incl) - SUM( osd2.amount_tax_excl))
                        FROM '._DB_PREFIX_.'order_detail od2
                            INNER JOIN '._DB_PREFIX_.'order_slip_detail osd2 ON osd2.id_order_detail = od2.id_order_detail
                            INNER JOIN '._DB_PREFIX_.'order_detail_tax odt ON odt.id_order_detail = od2.id_order_detail
                            INNER JOIN '._DB_PREFIX_.'tax t ON t.id_tax = odt.id_tax
                        WHERE (od2.id_order_detail = osd2.id_order_detail
                            AND ROUND(t.rate,2) = '.$$tx.'
                            AND osd2.id_order_slip = os.id_order_slip)),2))
                        as TVA_tx'.$row_avoir;
        }
        // EN TETE
        $html .= '
        <script type = "text/javascript" language = "javascript">$(\'calendar\').slideToggle();
        function fnExcelReport(id)
        {
            var dt = new Date();
            var day = dt.getDate();
            var month = dt.getMonth() + 1;
            var year = dt.getFullYear();
            var hour = dt.getHours();
            var mins = dt.getMinutes();
            var prefix = \'\';
            var tabsource = \'\';
            if (id == 1)
            {
                tabsource = \'dvData\';
                prefix = \'Commandes_\';
            }
            else if (id == 2)
            {
                tabsource = \'dvDataAvoirs\';
                prefix = \'Avoirs_\';
            }
            var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
            var fileName = prefix + postfix;
            var tab_text="<table border=\'2px\'><tr>";
            var textRange; var j=0;
            tab = document.getElementById(tabsource); // id of table
            for(j = 0 ; j < tab.rows.length ; j++) 
            {     
                tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
                //tab_text=tab_text+"</tr>";
            }
            tab_text=tab_text+"</table>";
            tab_text= tab_text
                .replace(/€/g, \'&#8364;\')
                .replace(/£/g, \'&#163;\')
                .replace(/$/g, \'&#36;\')
                .replace(/è/g, \'&#232;\')
                .replace(/é/g, \'&#233;\')
                .replace(/É/g, \'&#201;\')
                .replace(/ç/g, \'&#231;\')
                .replace(/ë/g, \'&#235;\')
                .replace(/ê/g, \'&#234;\');
            tab_text= tab_text.replace(/ /g,\' \'); // remove kilo separator
            tab_text= tab_text.replace(/&nbsp;/g,\' \'); // remove space in currencies
            tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params
            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE "); 
            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
            {
                txtArea1.document.open("txt/html","replace");
                txtArea1.document.write(tab_text);
                txtArea1.document.close();
                txtArea1.focus(); 
                sa=txtArea1.document.execCommand("SaveAs",true, prefix + postfix + \'.xls\');
                return (sa);
            }  
            if(navigator.userAgent.match(/Firefox/))    // If FireFox
            {
                sa = window.open(\'data:application/vnd.ms-excel,\' + encodeURIComponent(tab_text));
                return (sa);
            }
            else                 // other browser
            {
                var a = document.createElement(\'a\');
                var data_type = \'data:application/vnd.ms-excel\';
                a.href = data_type + \', \' + encodeURIComponent(tab_text);
                a.download = prefix + postfix + \'.xls\';
                a.click(); 
            }
        }
        </script>
        <style type="text/css">
        <!--
        .cell {
            white-space:nowrap;
            text-align:right;
        }
        -->
        </style>
        <div class = "blocStats">
        <h2 >'.$this->l('Accounting synthesis').'</h2>
        ';
        $html .= '<form id = "SynthGroupBy1"    style = "position:relative" action = "'.Tools::safeOutput($ru).'" method = "post">
                <input type = "hidden" name = "submitSynthCurrency" value = "1" />
                '.$this->l('Currency').' : <select name = "stats_SynthCurrency" onchange = "this.form.submit();" style = "width:160px">';
                $html .= '<option value="0">'.$this->l('Select...').'</option>';
        foreach ($currencies as $cur) {
            $html .= '<option value="'.$cur['id_currency'].'" '.($id_currency == $cur['id_currency'] ? ' selected="selected"' : '').'>'.$cur['iso_code'].' - '.$cur['name'].'</option>';
        }
                $html .= '</select>
            </form>
            </br>';
        if ($id_currency == 0) {
            $html .= '</br>'.$this->l('Please select a currency.').'</br>';
        } else if ($nombre_commandes_total == 0) {
            $html .= '</br>'.$this->l('There are no orders on the requested period for this currency.').'</br>';
        } else {
            // BLOC SUPERIEUR
            $html .= '
            <fieldset><legend><img src = "../modules/'.$this->name.'/views/img/pieces.gif" /> '.$this->l('Orders').'</legend>';
            // LISTE DEROULANTE
            $html .= '<div><div style="float:left;padding-right:40px;padding-bottom:20px">
                <form id = "SynthGroupBy2"    style = "position:relative" action = "'.Tools::safeOutput($ru).'" method = "post">
                <input type = "hidden" name = "submitSynthGroupBy" value = "1" />
                '.$this->l('Display a row by :').' <select name = "stats_SynthGroupBy" onchange = "this.form.submit();" style = "width:260px">
                    <option value = "1">'.$this->l('Year').'</option>
                    <option value = "2" '.($this->context->cookie->stats_SynthGroupBy == '2' ? 'selected = "selected"' : '').'>'.$this->l('Month').'</option>
                    <option value = "3" '.($this->context->cookie->stats_SynthGroupBy == '3' ? 'selected = "selected"' : '').'>'.$this->l('Day').'</option>
                    <option value = "4" '.($this->context->cookie->stats_SynthGroupBy == '4' ? 'selected = "selected"' : '').'>'.$this->l('Order').'</option>
                    <option value = "5" '.($this->context->cookie->stats_SynthGroupBy == '5' ? 'selected = "selected"' : '').'>'.$this->l('Payment type').'</option>
                    <option value = "6" '.($this->context->cookie->stats_SynthGroupBy == '6' ? 'selected = "selected"' : '').'>'.$this->l('Billing country').'</option>
                    <option value = "7" '.($this->context->cookie->stats_SynthGroupBy == '7' ? 'selected = "selected"' : '').'>'.$this->l('Country of delivery').'</option>
                    <option value = "8" '.($this->context->cookie->stats_SynthGroupBy == '8' ? 'selected = "selected"' : '').'>'.$this->l('Billing Zone').'</option>
                    <option value = "9" '.($this->context->cookie->stats_SynthGroupBy == '9' ? 'selected = "selected"' : '').'>'.$this->l('Zone of delivery').'</option>
                    <option value = "10" '.($this->context->cookie->stats_SynthGroupBy == '10' ? 'selected = "selected"' : '').'>'.$this->l('Billing State').'</option>
                    <option value = "11" '.($this->context->cookie->stats_SynthGroupBy == '11' ? 'selected = "selected"' : '').'>'.$this->l('State of delivery').'</option>
                    <option value = "12" '.($this->context->cookie->stats_SynthGroupBy == '12' ? 'selected = "selected"' : '').'>'.$this->l('Carrier').'</option>
                    <option value = "13" '.($this->context->cookie->stats_SynthGroupBy == '13' ? 'selected = "selected"' : '').'>'.$this->l('Group of customer').'</option>
                    <option value = "15" '.($this->context->cookie->stats_SynthGroupBy == '15' ? 'selected = "selected"' : '').'>'.$this->l('Group of customer - billing country').'</option>
                    <option value = "16" '.($this->context->cookie->stats_SynthGroupBy == '16' ? 'selected = "selected"' : '').'>'.$this->l('Group of customer - billing zone').'</option>
                    <option value = "17" '.($this->context->cookie->stats_SynthGroupBy == '17' ? 'selected = "selected"' : '').'>'.$this->l('Group of customer - country of delivery').'</option>
                    <option value = "18" '.($this->context->cookie->stats_SynthGroupBy == '18' ? 'selected = "selected"' : '').'>'.$this->l('Group of customer - zone of delivery').'</option>
                    <option value = "19" '.($this->context->cookie->stats_SynthGroupBy == '19' ? 'selected = "selected"' : '').'>'.$this->l('Payment type - country of delivery').'</option>
                    <option value = "20" '.($this->context->cookie->stats_SynthGroupBy == '20' ? 'selected = "selected"' : '').'>'.$this->l('Payment type - zone of delivery').'</option>
                    <option value = "14" '.($this->context->cookie->stats_SynthGroupBy == '14' ? 'selected = "selected"' : '').'>'.$this->l('Day for checkout').'</option>
                </select>
            </form></div>';
            if ($this->context->cookie->stats_SynthGroupBy == 4) {
                $html .= '<div style="float: left;padding-right: 40px;">
                    <form id = "SynthSortBy"    style = "position:relative" action = "'.Tools::safeOutput($ru).'" method = "post">
                    <input type = "hidden" name = "submitSortOrderBy" value = "orderId" />
                    '.$this->l('The orders are sorted by :').' <select name = "stats_SortOrderBy" onchange = "this.form.submit();" style = "width:160px">
                        <option value = "orderId">'.$this->l('The order ID').'</option>
                        <option value = "invoiceId" '.($this->context->cookie->stats_SortOrderBy == 'invoiceId' ? 'selected = "selected"' : '').'>'.$this->l('The invoice ID').'</option>
                        <option value = "customerId" '.($this->context->cookie->stats_SortOrderBy == 'customerId' ? 'selected = "selected"' : '').'>'.$this->l('The customer ID').'</option>
                        <option value = "totalPaid" '.($this->context->cookie->stats_SortOrderBy == 'totalPaid' ? 'selected = "selected"' : '').'>'.$this->l('Total paid').'</option>
                    </select>
                </form></div>';
            }
            $html .= '<div style="float: left;padding-right: 40px;"><form id = "SynthGroupBy3"    style = "position:relative" action = "'.Tools::safeOutput($ru).'" method = "post">
                <input type = "hidden" name = "submitDateForAnalysis" value = "order" />
                '.$this->l('The period of analysis is focused on the date of :').' <select name = "stats_DateForAnalysis" onchange = "this.form.submit();" style = "width:160px">
                    <option value = "order">'.$this->l('the order').'</option>
                    <option value = "invoice" '.($this->context->cookie->stats_DateForAnalysis == 'invoice' ? 'selected = "selected"' : '').'>'.$this->l('the invoice').'</option>
                </select>
            </form></div></div>';
            $rollup = array('TVA_tx1' => 0,'TVA_tx2' => 0,'TVA_tx3' => 0,
                            'TVA_tx4' => 0,'TVA_tx5' => 0,'TVA_tx6' => 0,
                            'TVA_tx7' => 0,'TVA_tx8' => 0,'TVA_tx9' => 0,
                            'TVA_tx10' => 0,
                            'HT_tx1' => 0,'HT_tx2' => 0,'HT_tx3' => 0,
                            'HT_tx4' => 0,'HT_tx5' => 0,'HT_tx6' => 0,
                            'HT_tx7' => 0,'HT_tx8' => 0,'HT_tx9' => 0,
                            'HT_tx10' => 0,
                            'Mod_Pai1' => 0,'Mod_Pai2' => 0,'Mod_Pai3' => 0,
                            'Mod_Pai4' => 0,'Mod_Pai5' => 0,'Mod_Pai6' => 0,
                            'Mod_Pai7' => 0,'Mod_Pai8' => 0,'Mod_Pai9' => 0,
                            'Mod_Pai10' => 0,);
            // TABLEAU RECAP
            $html .= $this->getTableauRecapForOrders($this->context->employee->stats_date_from, $this->context->employee->stats_date_to, $this->l('Number of orders'), $nombre_commandes_total, $ca_ttc_total, $ca_ht_total, $ca_ttc_total - $ca_ht_total, $currency);
            $html .= '</br>';
            // Début TABLEAU
            $tableau .= '<div><table id="dvData" width = "100%" border = "1" class = "table" cellspacing = "0" cellpadding = "0" >';
            //  ------------  A N N E E    ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 1) {
                // REQUETE
                $lignes_annee = Db::getInstance()->ExecuteS($query.'
                    GROUP BY Annee');
                //var_dump($lignes_annee);die;
                // TABLEAU
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By year'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Year'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                    </tr>
                    <tr>
                ';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '</tr>';
                foreach ($lignes_annee as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2)) ? $color_if_error : '';
                    // Details
                    $tableau .= '<tr>';
                    $tableau .= $this->feedValue($ligne['Annee']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '</tr>';
                }
                // Ligne de total
                $tableau .= '<tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = 2>TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------  M O I S  ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 2) {
                // REQUETE
                $results_mois = Db::getInstance()->ExecuteS($query.'
                    GROUP BY Mois
                ');
                // TABLEAU
                // ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By month'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Month'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                    <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($results_mois as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2)) ? $color_if_error : '';
                    // Détails
                    $tableau .= '<tr>';
                    $tableau .= $this->feedValue($ligne['Mois']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '<tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = 2>TOTAL</th>';
                        $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                        $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                        $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                        $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                        $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                        $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                        $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------  J O U R    ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 3) {
                // REQUETE
                $results_mois = Db::getInstance()->ExecuteS($query.'
                    GROUP BY Jour
                ');
                // TABLEAU
                // ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By day'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Day'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                    <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($results_mois as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2)) ? $color_if_error : '';
                    // Détails
                    $tableau .= '<tr>';
                    $tableau .= $this->feedValue($ligne['Jour']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '<tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = 2>TOTAL</th>';
                        $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                        $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                        $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                        $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                        $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                        $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                        $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------  C O M M A N D E    ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 4) {
                // REQUETE
                $lignes_commande = Db::getInstance()->ExecuteS($query.'
                    GROUP BY o.id_order
                    ORDER BY '.$sortby_for_orders.' ASC
                ');
                // TABLEAU
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - Details'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, ($nombre_clients_avec_societe > 0 ? 1 : 0) + ($nombre_clients_avec_numero_tva > 0 ? 1 : 0) + ($nombre_commandes_avec_numero_colis > 0 ? 1 : 0) + 21);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Order'), ($nombre_commandes_avec_numero_colis > 0 ? 1 : 0) + 4, 1);
                $tableau .= $this->getHeader($this->l('Invoice'), 2, 1);
                $tableau .= $this->getHeaderForCustomer($this->l('Customer'), $nombre_clients_avec_societe, $nombre_clients_avec_numero_tva);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 2 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeader($this->l('Payment type'), 1, 3);
                $tableau .= $this->getHeader($this->l('Billing'), 3, 1);
                $tableau .= $this->getHeader($this->l('Delivery'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeader($this->l('Id'), 1, 2);
                $tableau .= $this->getHeader($this->l('Reference'), 1, 2);
                $tableau .= $this->getHeader($this->l('Date'), 1, 2);
                $tableau .= $this->getHeader($this->l('Status'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($nombre_commandes_avec_numero_colis, $this->l('Shipping number'), 1, 2);
                $tableau .= $this->getHeader($this->l('Id'), 1, 2);
                $tableau .= $this->getHeader($this->l('Date'), 1, 2);
                $tableau .= $this->getHeader($this->l('Id'), 1, 2);
                $tableau .= $this->getHeader($this->l('Name'), 1, 2);
                $tableau .= $this->getHeader($this->l('Group'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($nombre_clients_avec_societe, $this->l('Company'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($nombre_clients_avec_numero_tva, $this->l('VAT Number'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Carrier'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getHeader($this->l('Country'), 1, 2);
                $tableau .= $this->getHeader($this->l('Zone'), 1, 2);
                $tableau .= $this->getHeader($this->l('State'), 1, 2);
                $tableau .= $this->getHeader($this->l('Country'), 1, 2);
                $tableau .= $this->getHeader($this->l('Zone'), 1, 2);
                $tableau .= $this->getHeader($this->l('State'), 1, 2);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                    </tr>';
                foreach ($lignes_commande as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                            ? $color_if_error
                            : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Cde']);
                    $tableau .= $this->feedValue($ligne['Ref']);
                    $tableau .= $this->feedValue($ligne['Date_Add']);
                    $tableau .= $this->feedValue($ligne['Status']);
                    $tableau .= $this->feedValueIfNotNull($nombre_commandes_avec_numero_colis, $ligne['NumColis']);
                    $tableau .= '
                        <td class = "cell">'.Configuration::get(_DB_PREFIX_.'INVOICE_PREFIX', $language, null, Context::getContext()->shop).sprintf('%06d', $ligne['FactureId']).'</td>';
                    $tableau .= $this->feedValue($ligne['FactureDate']);
                    $tableau .= $this->feedValue($ligne['ClientId']);
                    $tableau .= $this->feedValue($ligne['Client']);
                    $tableau .= $this->feedValue($ligne['Groupe_Client']);
                    $tableau .= $this->feedValueIfNotNull($nombre_clients_avec_societe, $ligne['Societe']);
                    $tableau .= $this->feedValueIfNotNull($nombre_clients_avec_numero_tva, $ligne['Numero_TVA']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedValueIfNotNull($frais_port_ttc_total, $ligne['Carrier_Name']);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedValue($ligne['Mode_Paiement']);
                    $tableau .= $this->feedValue($ligne['Pays_Facturation']);
                    $tableau .= $this->feedValue($ligne['Zone_Facturation']);
                    $tableau .= $this->feedValue($ligne['Etat_Facturation']);
                    $tableau .= $this->feedValue($ligne['Pays_Livraison']);
                    $tableau .= $this->feedValue($ligne['Zone_Livraison']);
                    $tableau .= $this->feedValue($ligne['Etat_Livraison']);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '<tr>';
                        $tableau .= $this->getTotalLabel($this->l('TOTAL'), $nombre_clients_avec_societe, $nombre_clients_avec_numero_tva, $nombre_commandes_avec_numero_colis);
                        $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                        $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                        $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                        $tableau .= $this->addEmptyTotalIfNotNull($frais_port_ttc_total);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                        $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                        $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                        $tableau .= '<th></th><th></th><th></th><th></th><th></th><th></th><th></th>';
                        $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                        $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------  M O D E  D E  R E G L E M E N T    ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 5) {
                // REQUETE
                $lignes_mode_reglement = Db::getInstance()->ExecuteS($query.'
                GROUP BY Mode_Paiement
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By payment type'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Payment type'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                    </tr>';
                foreach ($lignes_mode_reglement as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '<tr>';
                    $tableau .= $this->feedValue($ligne['Mode_Paiement']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '<tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                        $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                        $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                        $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                        $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                        $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                        $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                        $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                        $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   P A Y S  D E   F A C T U R A T I O N  ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 6) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Pays_Facturation
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By billing country'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Country'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                    </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '<tr>';
                    $tableau .= $this->feedValue($ligne['Pays_Facturation']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   P A Y S   D E   L I V R A I S O N  ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 7) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Pays_Livraison
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By country of delivery'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Country'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '<tr>';
                    $tableau .= $this->feedValue($ligne['Pays_Livraison']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   Z O N E    D E   F A C T U R A T I O N  ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 8) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Zone_Facturation
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By billing zone'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Zone'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                    </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '<tr>';
                    $tableau .= $this->feedValue($ligne['Zone_Facturation']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   Z O N E   D E   L I V R A I S O N  ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 9) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Zone_Livraison
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By Zone of delivery'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Zone'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Zone_Livraison']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   E T A T   D E   F A C T U R A T I O N  ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 10) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Pays_Etat_Facturation
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By billing state'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('State'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                    </tr>
                    <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                    </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '<tr>';
                    $tableau .= $this->feedValue($ligne['Pays_Etat_Facturation']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   E T A T   D E   L I V R A I S O N  ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 11) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Pays_Etat_Livraison
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By State of delivery'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('State'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Pays_Etat_Livraison']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   T R A N S P O R T E U R  ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 12) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Carrier_Name
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By    Carrier'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Carrier'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Carrier_Name']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R  G R O U P E  D E  C L I E N T   ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 13) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Groupe_Client
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By Group of customer'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Group'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Groupe_Client']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   G R O U P E  D E  C L I E N T  E T   P A Y S   D E  F A C T U R A T I O N ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 15) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Groupe_Client_Pays_Facturation
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By Group of customer and billing country'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Group - Country'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Groupe_Client_Pays_Facturation']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   G R O U P E  D E  C L I E N T  E T   Z O N E   D E  F A C T U R A T I O N ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 16) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Groupe_Client_Zone_Facturation
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By Group of customer and billing zone'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Group - Zone'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Groupe_Client_Zone_Facturation']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   G R O U P E  D E  C L I E N T  E T   P A Y S   D E  L I V R A I S O N ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 17) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Groupe_Client_Pays_Livraison
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By Group of customer and country of delivery'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Group - Country'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Groupe_Client_Pays_Livraison']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   G R O U P E  D E  C L I E N T  E T   Z O N E   D E  L I V R A I S O N ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 18) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Groupe_Client_Zone_Livraison
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By Group of customer and zone of delivery'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Group - Zone'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Groupe_Client_Zone_Livraison']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   M O Y E N  D E  P A I E M E N T   E T   P A Y S   D E  L I V R A I S O N ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 19) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Mode_Paiement_Pays_Livraison
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By payment type and country of delivery'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Payment - Country'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Mode_Paiement_Pays_Livraison']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P A R   M O Y E N  D E  P A I E M E N T   E T   Z O N E   D E  L I V R A I S O N ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 20) {
                // REQUETE
                $lignes_pays = Db::getInstance()->ExecuteS($query.'
                GROUP BY Mode_Paiement_Zone_Livraison
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getTitleOfTable($this->l('ORDERS - By payment type and zone of delivery'), $row, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, 6);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Payment - Zone'), 1, 3);
                $tableau .= $this->getHeader($this->l('Nb'), 1, 3);
                $tableau .= $this->getHeader($this->l('Total paid'), 2, 1);
                $tableau .= $this->getHeaderForProducts($this->l('Products'), $row, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Shipping'), 1 + (($frais_port_ht_total > 0 ? 2 : 0)) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0), 1);
                $tableau .= $this->getHeaderIfNotNull($emballage_ttc_total, $this->l('Packing'), 3, 1);
                $tableau .= $this->getHeaderIfNotNull($reductions_total, $this->l('Including discounts'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($montant_achat_ht_total, $this->l('Margin'), 3, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeader($this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeader($this->l('Tax excl.'), 1, 2);
                $tableau .= $this->getTwoHeadersForTotalTtcHt();
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Without tax'), 1, 1);
                $tableau .= $titres_colonne_taxe;
                $tableau .= $this->getHeaderIfNotNull($montant_ecotax_total, $this->l('Including Ecotax'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ttc_total, $this->l('Tax incl.'), 1, 2);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('With VAT'), 2, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Without tax'), 1, 1);
                $tableau .= $this->getThreeHeadersIfNotNull($emballage_ttc_total);
                $tableau .= $this->getTwoHeadersForDiscountIfNotNull($reductions_total);
                $tableau .= $this->getThreeHeadersForMarginIfNotNull($montant_achat_ht_total);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $this->getHeaderIfNotNull($montant_ht_total_ventes_sans_tva, $this->l('Tax excl.'), 1, 1);
                $tableau .= $titres_colonne_taxe_sous_ligne;
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_total, $this->l('VAT'), 1, 1);
                $tableau .= $this->getHeaderIfNotNull($frais_port_ht_sans_tva_total, $this->l('Tax excl.'), 1, 1);
                $tableau .= '
                </tr>';
                foreach ($lignes_pays as $ligne) {
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['HT_tx'.$i] += $ligne['HT_tx'.$i];
                        $rollup['TVA_tx'.$i] += $ligne['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne['TVA_tx'.$i];
                    }
                    $color = (ROUND($tot_tva_locale, 2) <> ROUND($ligne['Mtt_TTC'] - $ligne['Mtt_HT'], 2))
                                    ? $color_if_error
                                    : '';
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Mode_Paiement_Zone_Livraison']);
                    $tableau .= $this->feedValue($ligne['Nb_Cdes']);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['CA_TTC'], $ligne['CA_HT'], $currency);
                    $tableau .= $this->feedTwoValuesTtcHt($ligne['Mtt_TTC'], $ligne['Mtt_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($montant_ht_total_ventes_sans_tva, $ligne['HT_Sans_Taxe'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedTwoValuesForTaxRate($color, $ligne['HT_tx'.$i], $ligne['TVA_tx'.$i], $currency);
                    }
                    $tableau .= $this->feedOneValueIfNotNull($montant_ecotax_total, $ligne['Ecotax_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ttc_total, $ligne['Frais_Port_TTC'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_HT'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_total, $ligne['Frais_Port_TVA'], $currency);
                    $tableau .= $this->feedOneValueIfNotNull($frais_port_ht_sans_tva_total, $ligne['Frais_Port_HT_Sans_TVA'], $currency);
                    $tableau .= $this->feedThreeValuesIfNotNull($emballage_ttc_total, $ligne['Emballage_TTC'], $ligne['Emballage_HT'], $ligne['Emballage_TVA'], $currency);
                    $tableau .= $this->feedTwoValuesIfNotNull($reductions_total, $ligne['Reduc_TTC'], $ligne['Reduc_HT'], $currency);
                    $tableau .= $this->feedThreeValuesForMarginIfNotNull($montant_achat_ht_total, $ligne['Achat_HT'], $ligne['Marge_Nette'], $currency);
                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap" colspan = "2">TOTAL</th>';
                $tableau .= $this->feedTwoTotalTtcHt($ca_ttc_total, $ca_ht_total, $currency);
                $tableau .= $this->feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($montant_ht_total_ventes_sans_tva, $montant_ht_total_ventes_sans_tva, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTwoTotalForTaxRate($color, $rollup['HT_tx'.$i], $rollup['TVA_tx'.$i], $currency);
                }
                $tableau .= $this->feedOneTotalIfNotNull($montant_ecotax_total, $montant_ecotax_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ttc_total, $frais_port_ttc_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_ht_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_total, $frais_port_tva_total, $currency);
                $tableau .= $this->feedOneTotalIfNotNull($frais_port_ht_sans_tva_total, $frais_port_ht_sans_tva_total, $currency);
                $tableau .= $this->feedThreeTotalIfNotNull($emballage_ttc_total, $emballage_ttc_total, $emballage_ht_total, $emballage_tva_total, $currency);
                $tableau .= $this->feedTwoTotalIfNotNull($reductions_total, $reductions_ttc_total, $reductions_ht_total, $currency);
                $tableau .= $this->feedThreeTotalForMarginIfNotNull($montant_achat_ht_total, $marge_nette_totale, $currency);
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            //  ------------    P  A  R     J O U R   P O U R   C A I S S E   ----------------------
            if ($this->context->cookie->stats_SynthGroupBy == 14) {
                                                        $modes_paiement = Db::getInstance()->ExecuteS('SELECT DISTINCT(o.payment) mode_paiement
                                                        FROM '._DB_PREFIX_.'orders o
                                                        LEFT OUTER JOIN (
                                                            SELECT 
                                                                fact.id_order,
                                                                CAST(fact.date_add as DATE) as FactureDate
                                                            FROM '._DB_PREFIX_.'order_invoice as fact
                                                            GROUP BY fact.id_order
                                                        ) as oi ON oi.id_order = o.id_order
                                                        WHERE'.$filtre_orders.$filtre_dates_variable.'
                                                        ORDER BY mode_paiement ASC');
                // Forgeage de la requete partielle des montants dynamiques par modes de paiement
                                                        $row = 0;
                foreach ($modes_paiement as $mode_paiement) {
                                                            $row++;
                                                            $titres_colonne_mode_paiement = $titres_colonne_mode_paiement.'<th style = "text-align:center">'.$mode_paiement['mode_paiement'].'</th>';
                                                            $query_mode_paiement = $query_mode_paiement.'SUM(IF(o.payment = \''.$mode_paiement['mode_paiement'].'\', oi.CA_TTC, 0)) AS Mod_Pai'.$row.',';
                }//var_dump($query_mode_paiement);die;
                                                        $query = 'SELECT
                            o.id_order as Cde,
                            CAST('.$date_for_analysis.' AS DATE) As Jour,
                            CAST(o.date_add AS DATE) As Date_Add,
                            SUM(oi.CA_TTC) as CA_TTC,
                            '.$query_mode_paiement.'
                            o.payment as Mode_Paiement
                            FROM '._DB_PREFIX_.'orders o
                            LEFT OUTER JOIN (
                                SELECT 
                                    fact.id_order,
                                    fact.number as FactureId,
                                    fact.total_paid_tax_incl as CA_TTC,
                                    fact.total_paid_tax_excl as CA_HT,
                                    CAST(fact.date_add as DATE) as FactureDate
                                FROM '._DB_PREFIX_.'order_invoice as fact
                                GROUP BY fact.id_order
                            ) as oi ON oi.id_order = o.id_order
                            WHERE'.$filtre_orders.$filtre_dates_variable;
                            //var_dump($query);die;
                
                // REQUETE
                $lignes_mode_paiement = Db::getInstance()->ExecuteS($query.'
                GROUP BY Jour
                ');
                // TABLEAU DETAILS
                // Ligne de titre
                $tableau .= $this->getHeader($this->l('ORDERS - By Day for checkout'), 2 + $row, 1);
                $tableau .= '
                    <tr>';
                $tableau .= $this->getHeader($this->l('Day'), 1, 2);
                $tableau .= $this->getHeader($this->l('Total paid'), 1, 2);
                $tableau .= $this->getHeader($this->l('Payment type'), $row, 1);
                $tableau .= '
                </tr>
                <tr>';
                $tableau .= $titres_colonne_mode_paiement;
                $tableau .= '
                </tr>';
                foreach ($lignes_mode_paiement as $ligne) {
                    for ($i = 1; $i <= $row; $i++) {
                        $rollup['Mod_Pai'.$i] += $ligne['Mod_Pai'.$i];
                    }
                    // Details
                    $tableau .= '
                        <tr>';
                    $tableau .= $this->feedValue($ligne['Jour']);
                    $tableau .= $this->feedOneValueIfNotNull(1, $ligne['CA_TTC'], $currency);
                    for ($i = 1; $i <= $row; $i++) {
                        $tableau .= $this->feedOneValueIfNotNull(1, $ligne['Mod_Pai'.$i], $currency);
                    }
                                                    $tableau .= '
                        </tr>';
                }
                // Ligne de total
                $tableau .= '
                    <tr>
                        <th style = "text-align:right; white-space:nowrap">TOTAL</th>';
                $tableau .= $this->feedTotalWithCurrency($ca_ttc_total, $currency);
                for ($i = 1; $i <= $row; $i++) {
                    $tableau .= $this->feedTotalWithCurrency($rollup['Mod_Pai'.$i], $currency);
                }
                $tableau .= '
                    </tr>
                </table>
            </div>';
            }
            $html .= $tableau;
            $html .= '<iframe id="txtArea1" style="display:none"></iframe>
                        <button id="btnExport2" class="btn btn-default" title="'.$this->l('Export the table in XLS format').'" onclick="fnExcelReport(1);">'.$this->l('Export').'</button>';
        }
        //  ------------  A  V  O  I  R  S    ----------------------
        if ($id_currency == 0) {
            $html .= '';
        } else if ($nombre_avoirs_total == 0) {
            $html .= '</fieldset></br>'.$this->l('There are no credits on the requested period for this currency').'</br>';
        } else {
            // REQUETE
            $lignes_avoir = Db::getInstance()->ExecuteS(
                'SELECT CAST(os.date_add AS DATE) As Date_Add,
                    os.id_customer as client_id,
                    CONCAT(c.firstname, \' \', c.lastname) as client_name,
                    os.id_order_slip as id,
                    os.id_order as id_Cde,
                    Cast(o.date_add AS DATE) as Date_Cde,
                    SUM(- osd.amount_tax_excl) as Mtt_HT,
                    SUM(- osd.amount_tax_incl) as Mtt_TTC,
                    o.payment as Mode_Paiement,
                    ci.iso_code as Pays_Facturation'
                .$queries_detail_avoir
                .$queries_detail_fin_avoir.'
                FROM '._DB_PREFIX_.'order_slip os
                LEFT OUTER JOIN '._DB_PREFIX_.'order_slip_detail osd ON osd.id_order_slip = os.id_order_slip
                LEFT OUTER JOIN '._DB_PREFIX_.'order_detail od ON od.id_order_detail = osd.id_order_detail
                INNER JOIN '._DB_PREFIX_.'orders o ON o.id_order = os.id_order
                LEFT OUTER JOIN '._DB_PREFIX_.'customer c ON c.id_customer = os.id_customer
                LEFT OUTER JOIN '._DB_PREFIX_.'address ai ON ai.id_address = o.id_address_invoice
                LEFT OUTER JOIN '._DB_PREFIX_.'country ci on ci.id_country = ai.id_country
                WHERE'.$filtre_avoirs.'
                GROUP BY id'
            );
                $rollup = array('HT' => 0, 'TTC' => 0,
                                'TVA_tx1' => 0, 'TVA_tx2' => 0,'TVA_tx3' => 0,
                                'TVA_tx4' => 0,'TVA_tx5' => 0,'TVA_tx6' => 0,
                                'TVA_tx7' => 0,'TVA_tx8' => 0,'TVA_tx9' => 0,
                                'TVA_tx10' => 0,
                                'Port_HT' => 0, 'Port_TTC' => 0,'Port_TVA' => 0,);
            // TABLEAU AVOIRS
                $html .= '
                </fieldset></br>
                <fieldset><legend><img src = "../modules/'.$this->name.'/views/img/pieces.gif" /> '.$this->l('Credits').'</legend>';
            // TABLEAU RECAP
                $html .= $this->getTableauRecapForCredits($this->context->employee->stats_date_from, $this->context->employee->stats_date_to, $this->l('Number of credits'), $nombre_avoirs_total);
                $html .= '</br>';
            // TABLEAU DETAILS
                $html .= '            
                <div><table id="dvDataAvoirs" width = "Auto" border = "1" class = "table" cellspacing = "0" cellpadding = "0" width = "100%">';
            // Ligne de titre
                $html .= '
                <tr>';
                $html .= $this->getHeader($this->l('CREDITS'), (14 + $row_avoir), 1);
                $html .= '
                </tr>
                <tr>';
                $html .= $this->getHeader($this->l('Credit'), 2, 1);
                $html .= $this->getHeader($this->l('Order'), 3, 1);
                $html .= $this->getHeader($this->l('Customer'), 3, 1);
                $html .= $this->getHeader($this->l('Total tax incl.'), 1, 2);
                $html .= $this->getHeader($this->l('Products'), (2 + $row_avoir), 1);
                $html .= $this->getHeader($this->l('Shipping'), 3, 1);
                $html .= '
                </tr>
                <tr>';
                $html .= $this->getHeader($this->l('Id'), 1, 1);
                $html .= $this->getHeader($this->l('Date'), 1, 1);
                $html .= $this->getHeader($this->l('Id'), 1, 1);
                $html .= $this->getHeader($this->l('Date'), 1, 1);
                $html .= $this->getHeader($this->l('Payment type'), 1, 1);
                $html .= $this->getHeader($this->l('Id'), 1, 1);
                $html .= $this->getHeader($this->l('Name'), 1, 1);
                $html .= $this->getHeader($this->l('Country'), 1, 1);
                $html .= $this->getHeader($this->l('Tax incl.'), 1, 1);
                $html .= $this->getHeader($this->l('Tax excl.'), 1, 1);
                $html .= $titres_colonne_taxe_avoir;
                $html .= $this->getHeader($this->l('Tax incl.'), 1, 1);
                $html .= $this->getHeader($this->l('Tax excl.'), 1, 1);
                $html .= $this->getHeader($this->l('VAT'), 1, 1);
                $html .= '
                </tr>';
            // Details
                $color = '';
                foreach ($lignes_avoir as $ligne_avoir) {
                    $rollup['HT'] += $ligne_avoir['Mtt_HT'];
                    $rollup['TTC'] += $ligne_avoir['Mtt_TTC'];
                    $tot_tva_locale = 0;
                    for ($i = 1; $i <= $row_avoir; $i++) {
                        $rollup['TVA_tx'.$i] += $ligne_avoir['TVA_tx'.$i];
                        $tot_tva_locale = $tot_tva_locale + $ligne_avoir['TVA_tx'.$i];
                    }
                    $rollup['Port_HT'] += ROUND($ligne_avoir['Frais_Port_HT'], 2);
                    $rollup['Port_TTC'] += ROUND($ligne_avoir['Frais_Port_TTC'], 2);
                    $rollup['Port_TVA'] += ROUND($ligne_avoir['Frais_Port_TVA'], 2);
                    $html .= '
                        <tr>';
                    $html .= $this->feedValue($ligne_avoir['id']);
                    $html .= $this->feedValue($ligne_avoir['Date_Add']);
                    $html .= $this->feedValue($ligne_avoir['id_Cde']);
                    $html .= $this->feedValue($ligne_avoir['Date_Cde']);
                    $html .= $this->feedValue($ligne_avoir['Mode_Paiement']);
                    $html .= $this->feedValue($ligne_avoir['client_id']);
                    $html .= $this->feedValue($ligne_avoir['client_name']);
                    $html .= $this->feedValue($ligne_avoir['Pays_Facturation']);
                    $html .= $this->feedValueWithCurrency($ligne_avoir['Frais_Port_TTC'] + $ligne_avoir['Mtt_HT'] + $tot_tva_locale, $currency);
                    $html .= '
                    <td class = "cell" style = "color:'.$color.'">'.Tools::displayPrice($ligne_avoir['Mtt_HT'] + $tot_tva_locale, $currency).'</td>
                    <td class = "cell" style = "color:'.$color.'">'.Tools::displayPrice($ligne_avoir['Mtt_HT'], $currency).'</td>';
                    for ($i = 1; $i <= $row_avoir; $i++) {
                        $html .= '<td class = "cell" style = "color:'.$color.'">'.Tools::displayPrice($ligne_avoir['TVA_tx'.$i], $currency).'</td>';
                    }
                    $html .= $this->feedValueWithCurrency($ligne_avoir['Frais_Port_TTC'], $currency);
                    $html .= $this->feedValueWithCurrency($ligne_avoir['Frais_Port_HT'], $currency);
                    $html .= $this->feedValueWithCurrency($ligne_avoir['Frais_Port_TVA'], $currency);
                    $html .= '</tr>';
                }
            // Ligne de total
                $html .= '<tr>
                    <th style = "text-align:right; white-space:nowrap" colspan = "8">TOTAL</th>';
                $html .= $this->feedTotalWithCurrency($rollup['TTC'] + $rollup['Port_TTC'], $currency);
                $html .= $this->feedTotalWithCurrency($rollup['TTC'], $currency);
                $html .= $this->feedTotalWithCurrency($rollup['HT'], $currency);
                for ($i = 1; $i <= $row_avoir; $i++) {
                    $html .= '<th style = "text-align:right; white-space:nowrap" style = "color:'.$color.'">'.Tools::displayPrice($rollup['TVA_tx'.$i], $currency).'</th>';
                }
                $html .= $this->feedTotalWithCurrency($rollup['Port_TTC'], $currency);
                $html .= $this->feedTotalWithCurrency($rollup['Port_HT'], $currency);
                $html .= $this->feedTotalWithCurrency($rollup['Port_TVA'], $currency);
                $html .= '</tr>
                </table>';
                $html .= '<button class="btn btn-default" id="btnExport3" onclick="fnExcelReport(2);" title="'.$this->l('Export the table in XLS format').'">'.$this->l('Export').'</button>';
        }
        // Pied de page
        $html .= '
            </fieldset></br>
            <fieldset><legend><img src = "../modules/'.$this->name.'/views/img/comment.gif" /> '.$this->l('Informations').'</legend>
            <p>'.$this->l('Only the valid orders are used in the synthesis.').'</p>
                        
            <form id = "InactiveErrorColor"    style = "position:relative" action = "'.Tools::safeOutput($ru).'" method = "post">
                <input type = "hidden" name = "submitErrorColor" value = "1" />
                <p>'.$this->l('Display the amount witch are not repect the sum (tax excl. + VAT = tax incl.)').' 
                <select name = "show_ErrorColor" onchange = "this.form.submit();" style = "width:140px">
                <option value = "couleur">'.$this->l('in color').'</option>
                <option value = "normal" '.($this->context->cookie->show_ErrorColor == 'normal' ? 'selected = "selected"' : '').'>'.$this->l('in black').'</option>
                </select>
            </form>
            <hr>
            <p>'.$this->l('For more details, you can read the help file of the module by').'
                <a href = "../modules/'.$this->name.'/readme_fr.pdf" target = "_blank"> '.$this->l('clicking here').' <img src = "../modules/'.$this->name.'/views/img/pdf.gif"></a>
                (<a href = "../modules/'.$this->name.'/readme_fr.pdf" target = "_blank">français </a> - 
                <a href = "../modules/'.$this->name.'/readme_en.pdf" target = "_blank"> english </a> - 
                <a href = "../modules/'.$this->name.'/readme_it.pdf" target = "_blank"> italiano </a> - 
                <a href = "../modules/'.$this->name.'/readme_es.pdf" target = "_blank"> español</a>)
            </p>
            <p><img src = "../modules/'.$this->name.'/views/img/info.png" /> '.$this->l('For further information please contact the support with ').'
                <a href = "https://addons.prestashop.com/contact-form.php?id_product=8892" target = "_blank"> '.$this->l('this dedicated form.').'</a>
            </p>
            <p>'.$this->l('You have bought the module').' "'.$this->displayName.'", <strong>'.$this->l('Your opinion is welcome').'</strong>.
            <a href = "http://addons.prestashop.com/fr/ratings.php" target = "_blank">
            </br>'.$this->l('Thanks for rate by clicking here :').' <img src = "../modules/'.$this->name.'/views/img/rate.jpg"></a></p>
            <hr>
            <p>'.$this->l('Release').' '.$this->version.' '.$this->l('for PS 1.5, 1.6 and 1.7').' | 03-2022</p>
            <p>"'.$this->displayName.'" '.$this->l('is a PrestaShop module made by').' 
            <img src = "../modules/'.$this->name.'/logo.gif">'.$this->author.'</p>
            </fieldset></div>';
        //$html .= '<p>SET OPTION SQL_BIG_SELECTS=1;</br>'.$query.' GROUP BY Mois</p>';
        return $html;
    }
    private function getHeader($header_title, $nb_col_span, $nb_row_span)
    {
        return '<th style = "text-align:center" rowspan = '.$nb_row_span.' colspan = '.$nb_col_span.'>'.$header_title.'</th>';
    }
    private function getHeaderForCustomer($header_title, $nombre_clients_avec_societe, $nombre_clients_avec_numero_tva)
    {
        return $this->getHeader($header_title, ($nombre_clients_avec_societe > 0 ? 1 : 0) + ($nombre_clients_avec_numero_tva > 0 ? 1 : 0) + 3, 1);
    }
    private function getHeaderIfNotNull($value_to_test, $header_title, $nb_col_span, $nb_row_span)
    {
        if ($value_to_test > 0) {
            return $this->getHeader($header_title, $nb_col_span, $nb_row_span);
        } else {
            return '';
        }
    }
    private function getThreeHeadersIfNotNull($value_to_test)
    {
        if ($value_to_test > 0) {
            return $this->getHeader($this->l('Tax incl.'), 1, 2).'
                '.$this->getHeader($this->l('Tax excl.'), 1, 2).'
                '.$this->getHeader($this->l('VAT'), 1, 2);
        } else {
            return '';
        }
    }
    private function getTwoHeadersForDiscountIfNotNull($value_to_test)
    {
        if ($value_to_test > 0) {
            return $this->getTwoHeadersForTotalTtcHt();
        } else {
            return '';
        }
    }
    private function getTotalLabel($title, $nombre_clients_avec_societe, $nombre_clients_avec_numero_tva, $nombre_commandes_avec_numero_colis)
    {
        return '<th style = "text-align:right; white-space:nowrap" colspan = '.(($nombre_clients_avec_societe > 0 ? 1 : 0) + ($nombre_clients_avec_numero_tva > 0 ? 1 : 0) + ($nombre_commandes_avec_numero_colis > 0 ? 1 : 0) + 9).'>'.$title.'</th>';
    }
    private function getThreeHeadersForMarginIfNotNull($value_to_test)
    {
        if ($value_to_test > 0) {
            return $this->getHeader($this->l('Purchase'), 1, 2).'
                '.$this->getHeader($this->l('Margin'), 1, 2).'
                '.$this->getHeader($this->l('Margin rate'), 1, 2);
        } else {
            return '';
        }
    }
    private function getTwoHeadersForTotalTtcHt()
    {
        return $this->getHeader($this->l('Total tax incl.'), 1, 2).'
            '.$this->getHeader($this->l('Total tax excl.'), 1, 2);
    }
    private function getTitleOfTable($title, $nb_taxes, $montant_ht_total_ventes_sans_tva, $frais_port_ttc_total, $emballage_ttc_total, $reductions_total, $frais_port_ht_total, $frais_port_ht_sans_tva_total, $montant_achat_ht_total, $montant_ecotax_total, $constant)
    {
        return '<tr>'.$this->getHeader($title, ($constant + ($nb_taxes * 2) + ($montant_ht_total_ventes_sans_tva > 0 ? 1 : 0) + ($frais_port_ttc_total > 0 ? 1 : 0) + ($emballage_ttc_total > 0 ? 3 : 0) + ($reductions_total > 0 ? 2 : 0) + ($frais_port_ht_total > 0 ? 2 : 0) + ($frais_port_ht_sans_tva_total > 0 ? 1 : 0) + ($montant_achat_ht_total > 0 ? 3 : 0) + ($montant_ecotax_total > 0 ? 1 : 0)), 1).'</tr>';
    }
    private function getHeaderForProducts($title, $nb_taxes, $montant_ht_total_ventes_sans_tva, $montant_ecotax_total)
    {
        return $this->getHeader($title, (2 + ($montant_ht_total_ventes_sans_tva > 0 ? 1 : 0) + ($montant_ecotax_total > 0 ? 1 : 0) + ($nb_taxes * 2)), 1);
    }
    private function addEmptyTotalIfNotNull($data_to_test)
    {
        if ($data_to_test > 0) {
            return '<th></th>';
        } else {
            return '';
        }
    }
    private function feedValue($value)
    {
        return '<td class = "cell">'.$value.'</td>';
    }
    private function feedValueIfNotNull($data_to_test, $value)
    {
        if ($data_to_test > 0) {
            return $this->feedValue($value);
        } else {
            return '';
        }
    }
    private function feedValueWithCurrency($value, $currency)
    {
        if ($value == 0) {
            return '<td class = "cell"></td>';
        } else {
            return '<td class = "cell">'.Tools::displayPrice($value, $currency).'</td>';
        }
    }
    private function feedThreeValuesIfNotNull($data_to_test, $value1, $value2, $value3, $currency)
    {
        if ($data_to_test > 0) {
            return $this->feedValueWithCurrency($value1, $currency).'
            '.$this->feedValueWithCurrency($value2, $currency).'
            '.$this->feedValueWithCurrency($value3, $currency);
        } else {
            return '';
        }
    }
    private function feedTwoValuesIfNotNull($data_to_test, $value1, $value2, $currency)
    {
        if ($data_to_test > 0) {
            return $this->feedValueWithCurrency($value1, $currency).'
            '.$this->feedValueWithCurrency($value2, $currency);
        } else {
            return '';
        }
    }
    private function feedOneValueIfNotNull($data_to_test, $value, $currency)
    {
        if ($data_to_test > 0) {
            return $this->feedValueWithCurrency($value, $currency);
        } else {
            return '';
        }
    }
    private function feedThreeValuesForMarginIfNotNull($data_to_test, $achat_ht, $marge_nette, $currency)
    {
        if ($data_to_test > 0) {
            return $this->feedValueWithCurrency($achat_ht, $currency).'
                '.$this->feedValueWithCurrency($marge_nette, $currency).'
                    <td class = "cell">'.($achat_ht == 0 ? 0 : ROUND((($marge_nette) / $achat_ht) * 100, 0)).' %</td>';
        } else {
            return '';
        }
    }
    private function feedTwoValuesForTaxRate($color, $ht, $tva, $currency)
    {
        return '<td class = "cell" style = "color:'.$color.'">'.Tools::displayPrice($ht, $currency).'</td>
                <td class = "cell" style = "color:'.$color.'">'.Tools::displayPrice($tva, $currency).'</td>';
    }
    private function feedTwoValuesTtcHt($montant_ttc, $montant_ht, $currency)
    {
        return $this->feedValueWithCurrency($montant_ttc, $currency).'
        '.$this->feedValueWithCurrency($montant_ht, $currency);
    }
    private function feedTotalWithCurrency($value, $currency)
    {
        return '<th style = "text-align:right; white-space:nowrap">'.Tools::displayPrice($value, $currency).'</th>';
    }
    private function feedTwoTotalTtcHt($montant_ttc_total, $montant_ht_total, $currency)
    {
        return $this->feedTotalWithCurrency($montant_ttc_total, $currency).'
                '.$this->feedTotalWithCurrency($montant_ht_total, $currency);
    }
    private function feedOneTotalIfNotNull($data_to_test, $value, $currency)
    {
        if ($data_to_test > 0) {
            return $this->feedTotalWithCurrency($value, $currency);
        } else {
            return '';
        }
    }
    private function feedTwoTotalForTaxRate($color, $ht, $tva, $currency)
    {
        return '<th style = "text-align:right; white-space:nowrap" style = "color:'.$color.'">'.Tools::displayPrice($ht, $currency).'</th>
                <th style = "text-align:right; white-space:nowrap" style = "color:'.$color.'">'.Tools::displayPrice($tva, $currency).'</th>';
    }
    private function feedThreeTotalIfNotNull($data_to_test, $value1, $value2, $value3, $currency)
    {
        if ($data_to_test > 0) {
            return $this->feedTotalWithCurrency($value1, $currency).'
                '.$this->feedTotalWithCurrency($value2, $currency).'
                '.$this->feedTotalWithCurrency($value3, $currency);
        } else {
            return '';
        }
    }
    private function feedTwoTotalIfNotNull($data_to_test, $value1, $value2, $currency)
    {
        if ($data_to_test > 0) {
            return $this->feedTotalWithCurrency($value1, $currency).'
                '.$this->feedTotalWithCurrency($value2, $currency);
        } else {
            return '';
        }
    }
    private function feedThreeTotalForMarginIfNotNull($achat_ht, $marge_nette, $currency)
    {
        if ($achat_ht > 0) {
            return $this->feedTotalWithCurrency($achat_ht, $currency).'
                '.$this->feedTotalWithCurrency($marge_nette, $currency).'
                <th style = "text-align:right; white-space:nowrap">'.($achat_ht == 0 ? 0 : ROUND((($marge_nette) / ($achat_ht) * 100), 0)).' %</th>';
        } else {
            return '';
        }
    }
    private function getTableauRecapForOrders($start, $end, $title, $count, $ttc, $ht, $tva, $currency)
    {
        return ('<table border = "1" class = "table" cellspacing = "0" cellpadding = "0">
                <tr>'
                    .$this->getHeader($this->l('Start date'), 1, 1)
                    .$this->getHeader($this->l('End date'), 1, 1)
                    .$this->getHeader($this->l('Tax incl.'), 1, 1)
                    .$this->getHeader($this->l('Tax excl.'), 1, 1)
                    .$this->getHeader($this->l('VAT'), 1, 1)
                    .$this->getHeader($title, 1, 1)
                .'</tr>
                <tr>'
                    .$this->getHeader($start, 1, 1)
                    .$this->getHeader($end, 1, 1)
                    .$this->feedTotalWithCurrency($ttc, $currency)
                    .$this->feedTotalWithCurrency($ht, $currency)
                    .$this->feedTotalWithCurrency($tva, $currency)
                    .$this->getHeader($count, 1, 1)
                .'</tr>
                </table>');
    }
    private function getTableauRecapForCredits($start, $end, $title, $count)
    {
        return ('<table width = "Auto" border = "1" class = "table" cellspacing = "0" cellpadding = "0" width = "100%">
                <tr>'
                    .$this->getHeader($this->l('Start date'), 1, 1)
                    .$this->getHeader($this->l('End date'), 1, 1)
                    .$this->getHeader($title, 1, 1)
                .'</tr>
                <tr>'
                    .$this->getHeader($start, 1, 1)
                    .$this->getHeader($end, 1, 1)
                    .$this->getHeader($count, 1, 1)
                .'</tr>
                </table>');
    }
}
