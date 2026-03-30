/**
 * Lit le token CSRF depuis la meta tag injectée par le serveur.
 * À utiliser dans tous les appels fetch mutants (POST, PUT, PATCH, DELETE).
 */
export function getCsrfToken(): string {
  return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}
