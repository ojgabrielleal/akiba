---
status: ativo
tipo: indice-auth
atualizado_em: 2026-08-03
---

# Autenticacao

O projeto tem duas categorias de autenticação.

## Categorias

- [Painel Administrativo](./autenticacao-interna): login do painel privado com usuário interno, sessão Laravel, cookie `akiba_user_token`, roles e permissions.
- [OAuth para Site Publico](./oauth): identificação pública com Discord/Google, cookie `akiba_oauth_token`, middleware `oauth.resolve` e ações públicas protegidas.

## Diferença Principal

| Categoria | Uso | Identidade |
| --- | --- | --- |
| Painel Administrativo | Painel administrativo | `User` autenticado pelo Laravel |
| OAuth para Site Publico | Site público e interações públicas | `OAuthAccount` ou membro resolvido por cookie |

OAuth não substitui permissão administrativa. Ele apenas identifica visitantes/membros em fluxos públicos.
