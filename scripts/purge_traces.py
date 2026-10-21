"""Deuxième passe après scripts/purge_commandes.py : supprime tout ce qui gravite encore autour des transactions
antérieures à une date (paniers, paiements PSP, expéditions, suivi colis, relances, SAV, mouvements de stock liés
aux commandes, orphelins). Conservés : clients, adresses, catalogue, configuration, bons de réduction, preuves RGPD,
liste de désinscription des relances.
python3 -I scripts/purge_traces.py <date AAAA-MM-JJ> <tables existantes séparées par des virgules> <sortie.sql> <sortie_where.tsv>"""
import sys

CUT, EXISTING, OUT_SQL, OUT_WHERE = sys.argv[1], set(sys.argv[2].split(',')), sys.argv[3], sys.argv[4]
# Paniers anciens sans commande conservée (les paniers des commandes purgées en font partie).
C = f"SELECT id_cart FROM ps_cart WHERE date_add < '{CUT}' AND id_cart NOT IN (SELECT id_cart FROM ps_orders)"
KEPT_ORDERS = "SELECT id_order FROM ps_orders"
OLD_LABELS = f"SELECT id_colissimo_label FROM ps_colissimo_label WHERE date_add < '{CUT}'"
OLD_THREADS = f"SELECT id_customer_thread FROM ps_customer_thread WHERE date_add < '{CUT}'"

BY_CART = [
    'ps_amazonpay_orders', 'ps_bridge_transactions', 'ps_cart_abandonment_customer_send', 'ps_cart_cart_rule',
    'ps_cart_product', 'ps_chrono_cart_relais', 'ps_chrono_cart_creneau', 'ps_chrono_cart_saturday_supplement',
    'ps_colissimo_address', 'ps_colissimo_cart_pickup_point', 'ps_customization', 'ps_dpdfrance_shipping',
    'ps_ets_abancart_index', 'ps_ets_abancart_tracking', 'ps_ets_abancart_email_queue', 'ps_ets_abancart_mail_log',
    'ps_ets_abancart_shopping_cart', 'ps_ets_abancart_form_submit', 'ps_ets_abancart_discount_display_tracking',
    'ps_ganalytics_data', 'ps_gls_cart_carrier', 'ps_hfm_pro_declaration', 'ps_hfm_relance', 'ps_message',
    'ps_paypal_order', 'ps_paypal_processlogger', 'ps_payplug_lock', 'ps_payplug_payment', 'ps_payplug_queue',
    'ps_pbp_cart_productextra', 'ps_pscheckout_cart', 'ps_revolut_payment_orders', 'ps_specific_price',
    'ps_wishlist_product_cart', 'ps_blockwishlist_statistics', 'ps_atos_test', 'ps_tggatos_response_lock',
    'ps_mol_order_payment_fee', 'ps_mol_recurring_order',
]
BY_CART_ID = ['ps_gact_orders', 'ps_mollie_payments', 'ps_vivawallet_smart_checkout_orders', 'ps_vivawallet_transaction']

PLAN = []
PLAN += [(t, f"id_cart IN ({C})") for t in BY_CART]
PLAN += [(t, f"cart_id IN ({C})") for t in BY_CART_ID]
PLAN += [
    # Paiements datés (y compris ceux qui ne pointent sur aucun panier).
    ('ps_vivawallet_smart_checkout_orders', f"date_add < '{CUT}'"),
    ('ps_vivawallet_smart_checkout_transactions', f"date_add < '{CUT}'"),
    ('ps_vivawallet_transaction', f"date_add < '{CUT}'"),
    ('ps_amazonpay_transactions', f"date_add < '{CUT}'"),
    ('ps_mollie_payments', f"created_at < '{CUT}'"),
    ('ps_bridge_transactions', f"date_add < '{CUT}'"),
    # Expéditions et suivi.
    ('ps_colissimo_shipment_tracking', f"id_colissimo_label IN ({OLD_LABELS})"),
    ('ps_colissimo_label_product', f"id_colissimo_label IN ({OLD_LABELS})"),
    ('ps_colissimo_label', f"date_add < '{CUT}'"),
    ('ps_colissimo_deposit_slip', f"date_add < '{CUT}'"),
    ('ps_colissimo_order', f"id_order NOT IN ({KEPT_ORDERS})"),
    ('ps_hl_tracking_current_status', "id_order_carrier NOT IN (SELECT id_order_carrier FROM ps_order_carrier)"),
    ('ps_gls_log', f"date_add < '{CUT}'"),
    # Relances paniers, SAV, messages, stock, historique orphelin.
    ('ps_ets_abancart_tracking', f"date_add < '{CUT}'"),
    ('ps_customer_message', f"id_customer_thread IN ({OLD_THREADS})"),
    ('ps_customer_thread', f"date_add < '{CUT}'"),
    ('ps_message', f"date_add < '{CUT}'"),
    ('ps_stock_mvt', f"date_add < '{CUT}' AND id_order <> 0"),
    ('ps_order_history', f"id_order NOT IN ({KEPT_ORDERS})"),
    # Paniers eux-mêmes en dernier (les conditions ci-dessus s'appuient dessus).
    ('ps_cart', f"date_add < '{CUT}' AND id_cart NOT IN (SELECT id_cart FROM ps_orders)"),
]
PLAN = [(t, w) for t, w in PLAN if t in EXISTING]

with open(OUT_WHERE, 'w') as f:
    for t, w in PLAN:
        f.write(f"{t}\t{w}\n")
with open(OUT_SQL, 'w') as f:
    f.write(f"-- Purge des traces liées aux transactions antérieures au {CUT} (archivées au préalable). Rejouable.\n")
    f.write("SET FOREIGN_KEY_CHECKS = 0;\nSTART TRANSACTION;\n")
    for t, w in PLAN:
        f.write(f"DELETE FROM `{t}` WHERE {w};\n")
    f.write("COMMIT;\nSET FOREIGN_KEY_CHECKS = 1;\n")
    f.write(f"SELECT (SELECT COUNT(*) FROM ps_cart WHERE date_add < '{CUT}' AND id_cart NOT IN (SELECT id_cart FROM ps_orders)) AS paniers_restants,"
            f" (SELECT COUNT(*) FROM ps_customer_thread WHERE date_add < '{CUT}') AS sav_restant;\n")
print(len(PLAN), 'opérations')
