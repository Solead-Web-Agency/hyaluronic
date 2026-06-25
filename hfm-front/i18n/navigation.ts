import { createNavigation } from 'next-intl/navigation';
import { routing } from './routing';

// Versions locale-aware de Link / useRouter / usePathname / redirect / getPathname.
// Les liens internes préfixent automatiquement la locale courante.
export const { Link, redirect, usePathname, useRouter, getPathname } =
  createNavigation(routing);
