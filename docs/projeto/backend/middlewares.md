---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Middlewares

Middlewares interceptam requisições antes ou depois do controller. No projeto, eles são usados principalmente para Inertia, autenticação do painel e OAuth público.

## Onde Ficam

```txt
app/Http/Middleware
bootstrap/app.php
```

Os aliases são registrados em `bootstrap/app.php`:

```php
$middleware->alias([
    'inertia' => HandleInertiaRequestsMiddleware::class,
    'oauth.resolve' => ResolveOAuthAccount::class,
    'oauth' => EnsureOAuthAccountAuthenticated::class,
    'authenticated.user' => ShareAuthenticatedUserMiddleware::class,
]);
```

## HandleInertiaRequestsMiddleware

Arquivo:

```txt
app/Http/Middleware/HandleInertiaRequestsMiddleware.php
```

Alias:

```txt
inertia
```

O que faz:

- define o root view do Inertia;
- compartilha props globais com todas as páginas Inertia;
- envia `onair`, `stream` e `flash`;
- usa `OnairFilter`, `OnairResource` e `StreamService`.

Arquivos relacionados:

```txt
app/Filters/OnairFilter.php
app/Http/Resources/Onair/OnairResource.php
app/Services/External/StreamService.php
resources/js/lib/widgets/public/player
resources/js/lib/widgets/private/grid/StreamMetricsGrid.svelte
```

Use em rotas que renderizam páginas Inertia.

## ShareAuthenticatedUserMiddleware

Arquivo:

```txt
app/Http/Middleware/Auth/ShareAuthenticatedUserMiddleware.php
```

Alias:

```txt
authenticated.user
```

O que faz:

- carrega usuário autenticado com `roles.permissions`;
- compartilha prop global `user` no Inertia;
- envia dados básicos, roles e permissões para o painel.

Arquivos relacionados:

```txt
routes/web/private.php
resources/js/lib/utils/access/permissions.js
resources/js/lib/widgets/private/navbar/Navbar.svelte
```

Use apenas em rotas autenticadas do painel.

## ResolveOAuthAccount

Arquivo:

```txt
app/Http/Middleware/OAuth/ResolveOAuthAccount.php
```

Alias:

```txt
oauth.resolve
```

O que faz:

- lê cookies `akiba_oauth_token` e `akiba_user_token`;
- resolve conta OAuth ou usuário interno;
- adiciona `oauth_account` ou `member_user` nos atributos da request;
- compartilha prop global `oauth` no Inertia.

Arquivos relacionados:

```txt
app/Models/OAuthAccount.php
app/Models/User.php
routes/web/public.php
resources/js/lib/components/public/feedback/AuthGuard.svelte
resources/js/lib/components/public/feedback/ProfileIncompleteNotice.svelte
```

Use em rotas públicas que precisam saber se o visitante está identificado.

## EnsureOAuthAccountAuthenticated

Arquivo:

```txt
app/Http/Middleware/OAuth/EnsureOAuthAccountAuthenticated.php
```

Alias:

```txt
oauth
```

O que faz:

- exige usuário interno, membro resolvido ou conta OAuth;
- redireciona via `Inertia::location()` para OAuth quando não há autenticação;
- recebe provider padrão `discord`.

Arquivos relacionados:

```txt
routes/web/public.php
app/Http/Controllers/Api/External/OAuthAccount/OAuthAccountRedirectController.php
```

Use em ações públicas que exigem identificação, como comentar ou reagir.

## Checklist

- O middleware está registrado em `bootstrap/app.php`?
- A rota usa o alias correto?
- Props compartilhadas são leves ou lazy?
- Middleware público não assume usuário autenticado do painel?
- Middleware privado está dentro de `auth`?
