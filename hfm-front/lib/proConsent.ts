import { bridgePost } from './ps';
import { visitorIp } from './geoip';

/**
 * Preuve de la déclaration professionnelle (fiche commande en BO : « Accepté le … — IP … — langue … »).
 * Posée sur le panier AVANT le départ vers le prestataire de paiement, le client étant présent : si la
 * commande est ensuite créée par la notification serveur (webhook Viva, IPN Amazon), elle garde l'IP
 * et la langue du client. N'empêche jamais le paiement (erreurs ignorées).
 */
export async function recordProConsent(headers: Headers, id_cart: number, id_customer: number, locale: string): Promise<void> {
  await bridgePost('checkout', {
    action: 'pro-consent',
    id_cart,
    id_customer,
    client_ip: visitorIp(headers),
    locale,
  }).catch(() => null);
}
