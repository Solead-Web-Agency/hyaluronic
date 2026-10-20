"""Archivage puis purge des commandes et avoirs antérieurs à une date (demande client : avant le 19/09/2024).
Génère : la liste (table, condition) partagée par l'archive (mariadb-dump --where) et la suppression SQL.
Conservés volontairement : fiches clients/adresses, conversations SAV (customer_thread), historique de stock.
python3 -I scripts/purge_commandes.py <date AAAA-MM-JJ> <tables existantes séparées par des virgules> <sortie.sql> <sortie_where.tsv>"""
import sys

CUT, EXISTING, OUT_SQL, OUT_WHERE = sys.argv[1], set(sys.argv[2].split(',')), sys.argv[3], sys.argv[4]
O = f"SELECT id_order FROM ps_orders WHERE date_add < '{CUT}'"
REF = (f"SELECT reference FROM ps_orders WHERE date_add < '{CUT}' AND reference NOT IN "
       f"(SELECT reference FROM ps_orders WHERE date_add >= '{CUT}')")

# Tables filles d'abord (leur condition passe par la table parente, supprimée ensuite).
CHILDREN = [
    ('ps_order_detail_tax', f"id_order_detail IN (SELECT id_order_detail FROM ps_order_detail WHERE id_order IN ({O}))"),
    ('ps_shipment_product', f"id_order_detail IN (SELECT id_order_detail FROM ps_order_detail WHERE id_order IN ({O}))"),
    ('ps_order_invoice_tax', f"id_order_invoice IN (SELECT id_order_invoice FROM ps_order_invoice WHERE id_order IN ({O}))"),
    ('ps_order_slip_detail', f"id_order_slip IN (SELECT id_order_slip FROM ps_order_slip WHERE id_order IN ({O}))"),
    ('ps_order_return_detail', f"id_order_return IN (SELECT id_order_return FROM ps_order_return WHERE id_order IN ({O}))"),
    ('ps_dhl_commercial_invoice', f"id_dhl_order IN (SELECT id_dhl_order FROM ps_dhl_order WHERE id_order IN ({O}))"),
    ('ps_dhl_label', f"id_dhl_order IN (SELECT id_dhl_order FROM ps_dhl_order WHERE id_order IN ({O}))"),
    ('ps_dhl_shipment_tracking', f"id_dhl_order IN (SELECT id_dhl_order FROM ps_dhl_order WHERE id_order IN ({O}))"),
    ('ps_order_payment', f"order_reference IN ({REF})"),
    ('ps_mollie_payments', f"order_reference IN ({REF})"),
]
BY_ORDER = [
    'ps_order_history', 'ps_order_carrier', 'ps_order_cart_rule', 'ps_order_invoice_payment', 'ps_order_slip',
    'ps_order_return', 'ps_message', 'ps_dhl_order', 'ps_gls_label', 'ps_gls_tracking_state', 'ps_paypal_order',
    'ps_paypal_processlogger', 'ps_payplug_lock', 'ps_amazonpay_orders', 'ps_amazon_shipping_service',
    'ps_advancedvatmanager_orders', 'ps_bridge_transactions', 'ps_cdc_gtm_order_log', 'ps_chrono_lt_history',
    'ps_colissimo_order', 'ps_ganalytics', 'ps_gmc_gsa_orders_data', 'ps_gmc_gsa_returns_data',
    'ps_gmcp_gsa_orders_data', 'ps_gmcp_gsa_returns_data', 'ps_hfm_order_pro_doc', 'ps_hfm_pro_declaration',
    'ps_hfm_relance', 'ps_hl_tracking_email', 'ps_hl_tracking_history', 'ps_hl_tracking_return',
    'ps_marketplace_order_items', 'ps_marketplace_orders', 'ps_mol_order_payment_fee', 'ps_mol_recurring_order',
    'ps_orderedit_history', 'ps_revolut_payment_orders', 'ps_routeapp_shipment', 'ps_shipment',
    'ps_order_invoice', 'ps_order_detail',
]
PLAN = [(t, w) for t, w in CHILDREN] + [(t, f"id_order IN ({O})") for t in BY_ORDER] + [('ps_orders', f"date_add < '{CUT}'")]
PLAN = [(t, w) for t, w in PLAN if t in EXISTING]

with open(OUT_WHERE, 'w') as f:
    for t, w in PLAN:
        f.write(f"{t}\t{w}\n")
with open(OUT_SQL, 'w') as f:
    f.write(f"-- Purge des commandes et avoirs antérieurs au {CUT} (archivés au préalable). Rejouable.\n")
    f.write("SET FOREIGN_KEY_CHECKS = 0;\nSTART TRANSACTION;\n")
    for t, w in PLAN:
        f.write(f"DELETE FROM `{t}` WHERE {w};\n")
    f.write("COMMIT;\nSET FOREIGN_KEY_CHECKS = 1;\n")
    f.write(f"SELECT COUNT(*) AS commandes_restantes_avant_{CUT.replace('-', '')} FROM ps_orders WHERE date_add < '{CUT}';\n")
print(len(PLAN), 'tables')
