// SIRET : 14 chiffres avec clé de Luhn (même contrôle que PrestaShop, Validate::isSiret). Il
// contient le SIREN (9 premiers chiffres), qui identifie le client pro en facturation électronique.
export function normalizeSiret(v: string): string {
  return v.replace(/\D+/g, '');
}

export function validSiret(v: string): boolean {
  const s = normalizeSiret(v);
  if (!/^\d{14}$/.test(s)) return false;
  let sum = 0;
  for (let i = 0; i < 14; i++) {
    let d = Number(s[i]) * ((i + 1) % 2 + 1);
    if (d >= 10) d -= 9;
    sum += d;
  }
  return sum % 10 === 0;
}
