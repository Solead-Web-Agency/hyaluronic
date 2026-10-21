import { type Instrumentation } from 'next';
import { sendAlert } from '@/lib/alert';

// Toute erreur serveur (rendu de page, route API) déclenche une alerte : en pratique surtout une panne
// ou une lenteur du back PrestaShop (BridgeError, timeout), qui affiche alors la page d'erreur.
export const onRequestError: Instrumentation.onRequestError = async (err, request, context) => {
  const name = err instanceof Error ? err.name : 'Error';
  const message = err instanceof Error ? err.message : String(err);
  await sendAlert(
    `${context.routeType}:${context.routePath}:${name}`,
    `Erreur serveur sur ${context.routePath}`,
    `${request.method} ${request.path}\n${name} : ${message.slice(0, 300)}`,
  );
};
