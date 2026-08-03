---
status: ativo
tipo: guia-auth
atualizado_em: 2026-08-03
---

# OAuth para Site Publico

OAuth identifica visitantes no site público para ações como comentar, reagir, pedir música e completar perfil. Ele não substitui autenticação do painel administrativo.

## Visão Geral

Providers configurados:

```txt
discord
google
```

Rotas:

```txt
GET /oauth/{provider}/redirect
GET /oauth/{provider}/callback
POST /oauth/logout
PATCH /site/profile
```

Fluxo resumido:

```txt
Frontend
  -> /oauth/{provider}/redirect
  -> Provider externo
  -> /oauth/{provider}/callback
  -> OAuthAccount
  -> cookie akiba_oauth_token
  -> ResolveOAuthAccount
  -> prop oauth no Inertia
```

## Etapa 1: Configurar Providers

| Arquivo | O que faz |
| --- | --- |
| `config/oauth.php` | Mapeia cada provider para um service e uma action. |
| `config/services.php` | Guarda credenciais e redirect URI de Discord e Google. |
| `.env` | Define IDs, secrets e callbacks reais do ambiente. |

Exemplo:

```php
'discord' => [
    'service' => DiscordOAuthAccountService::class,
    'action' => DiscordOAuthAccountAction::class,
],
'google' => [
    'service' => GoogleOAuthAccountService::class,
    'action' => GoogleOAuthAccountAction::class,
],
```

Variáveis:

```ini
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_REDIRECT_URI=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
```

## Etapa 2: Abrir Login no Frontend

| Arquivo | O que faz |
| --- | --- |
| `Navbar.svelte` | Mostra ações de login/perfil no site público. |
| `ProfileForm.svelte` | Pode redirecionar para OAuth quando o usuário quer completar perfil. |
| `oauthPendingAction.js` | Guarda ação pendente para reabrir modal/fluxo depois do login. |

Exemplo de destino:

```txt
/oauth/discord/redirect
/oauth/google/redirect
```

O frontend não troca token diretamente com Google ou Discord. Ele só manda o visitante para a rota de redirect do backend.

## Etapa 3: Redirect Para o Provider

Rota:

```txt
GET /oauth/{provider}/redirect
```

| Arquivo | O que faz |
| --- | --- |
| `routes/web/public.php` | Declara a rota `oauth.redirect`. |
| `OAuthAccountRedirectController.php` | Resolve provider no config e redireciona para a URL externa. |
| `DiscordOAuthAccountService.php` | Monta URL de autorização do Discord. |
| `GoogleOAuthAccountService.php` | Monta URL de autorização do Google. |

Fluxo:

```txt
GET /oauth/discord/redirect
  -> OAuthAccountRedirectController
     -> config("oauth.providers.discord")
     -> DiscordOAuthAccountService::authorizationUrl()
        -> cria state
        -> salva state na sessão
        -> monta URL externa
     -> redirect()->away(...)
```

## Etapa 4: Callback do Provider

Rota:

```txt
GET /oauth/{provider}/callback
```

| Arquivo | O que faz |
| --- | --- |
| `routes/web/public.php` | Declara a rota `oauth.callback`. |
| `OAuthAccountCallbackController.php` | Orquestra troca de code, busca usuário externo e chama action do provider. |
| `DiscordOAuthAccountService.php` | Troca `code` por token e busca usuário Discord. |
| `GoogleOAuthAccountService.php` | Troca `code` por token e busca usuário Google. |
| `DiscordOAuthAccountAction.php` | Cria/atualiza `OAuthAccount` do Discord e grava cookie. |
| `GoogleOAuthAccountAction.php` | Cria/atualiza `OAuthAccount` do Google e grava cookie. |

Fluxo:

```txt
GET /oauth/discord/callback
  -> OAuthAccountCallbackController
     -> config("oauth.providers.discord")
     -> service::exchangeCodeForToken($request)
        -> valida code
        -> valida state da sessão
        -> troca code por access_token
     -> service::getUser($accessToken)
        -> busca dados do usuário no provider
     -> action::execute($providerUser, $request)
        -> updateOrCreate OAuthAccount
        -> gera token local
        -> salva hash no banco
        -> cria cookie akiba_oauth_token
     -> redirect home
```

## Etapa 5: Criar ou Atualizar OAuthAccount

| Arquivo | O que faz |
| --- | --- |
| `app/Models/OAuthAccount.php` | Model da conta OAuth local. |
| `DiscordOAuthAccountAction.php` | Normaliza usuário Discord e salva conta local. |
| `GoogleOAuthAccountAction.php` | Normaliza usuário Google e salva conta local. |

Campos importantes:

```txt
provider
provider_user_id
username
nickname
avatar
account_token_hash
profile_completed_at
```

Cookie criado:

```txt
akiba_oauth_token
```

Importante: o cookie guarda o token original, mas o banco guarda apenas o hash em `account_token_hash`.

## Etapa 6: Resolver OAuth nas Páginas Públicas

Middleware:

```txt
oauth.resolve
```

| Arquivo | O que faz |
| --- | --- |
| `routes/web/public.php` | Aplica `oauth.resolve` nos grupos públicos. |
| `ResolveOAuthAccount.php` | Lê cookies, resolve usuário/conta OAuth e compartilha prop `oauth`. |
| `OAuthAccount.php` | Consulta conta pelo hash do token. |
| `User.php` | Consulta usuário interno pelo hash do token quando há `akiba_user_token`. |

Fluxo:

```txt
Request pública
  -> ResolveOAuthAccount
     -> lê akiba_oauth_token
     -> hash(token)
     -> busca OAuthAccount.account_token_hash
     -> request.attributes.oauth_account = conta
     -> Inertia::share('oauth', ...)
  -> Controller da página
  -> Svelte recebe $page.props.oauth
```

Prop compartilhada:

```txt
type
authenticated
is_member
is_oauth
profile_completed
profile
```

## Etapa 7: Proteger Ações Públicas

Middleware:

```txt
oauth
```

| Arquivo | O que faz |
| --- | --- |
| `EnsureOAuthAccountAuthenticated.php` | Bloqueia ação se não houver usuário, membro resolvido ou OAuth. |
| `OAuthAccountRedirectController.php` | Recebe usuário não autenticado quando precisa iniciar OAuth. |

Usado em rotas como:

```txt
POST /materia/{post:slug}/reaction
POST /materia/{post:slug}/comment
PATCH /site/profile
```

Fluxo:

```txt
Ação pública protegida
  -> oauth.resolve
  -> oauth
     -> se identificado, continua
     -> se não identificado, Inertia::location(route('oauth.redirect'))
  -> Controller da ação
```

## Etapa 8: Completar Perfil OAuth

Rota:

```txt
PATCH /site/profile
```

| Arquivo | O que faz |
| --- | --- |
| `OAuthAccountController.php` | Recebe request e chama action. |
| `CompleteOAuthAccountProfileRequest.php` | Valida campos e garante que existe `oauth_account`. |
| `CompleteOAuthAccountProfileAction.php` | Atualiza perfil e define `profile_completed_at`. |
| `ProfileForm.svelte` | Formulário público de perfil. |
| `ProfileIncompleteNotice.svelte` | Avisa quando perfil OAuth está incompleto. |

Campos:

```txt
nickname
birth_date
address
bio
profile_completed_at
```

Fluxo:

```txt
PATCH /site/profile
  -> oauth.resolve
  -> oauth
  -> CompleteOAuthAccountProfileRequest
  -> OAuthAccountController::update()
  -> CompleteOAuthAccountProfileAction::execute()
  -> flash message
```

## Etapa 9: Logout OAuth

Rota:

```txt
POST /oauth/logout
```

| Arquivo | O que faz |
| --- | --- |
| `OAuthAccountLogoutController.php` | Remove cookie `akiba_oauth_token` e redireciona para home. |
| `ProfileForm.svelte` | Dispara logout no frontend público. |

Fluxo:

```txt
POST /oauth/logout
  -> oauth.resolve
  -> OAuthAccountLogoutController
     -> Cookie::forget('akiba_oauth_token')
     -> redirect home
```

## Arquivos por Responsabilidade

| Responsabilidade | Arquivos |
| --- | --- |
| Configurar providers | `config/oauth.php`, `config/services.php`, `.env` |
| Declarar rotas | `routes/web/public.php` |
| Redirecionar para provider | `OAuthAccountRedirectController.php` |
| Receber callback | `OAuthAccountCallbackController.php` |
| Falar com Discord | `DiscordOAuthAccountService.php` |
| Falar com Google | `GoogleOAuthAccountService.php` |
| Criar conta Discord | `DiscordOAuthAccountAction.php` |
| Criar conta Google | `GoogleOAuthAccountAction.php` |
| Resolver cookie | `ResolveOAuthAccount.php` |
| Exigir autenticação pública | `EnsureOAuthAccountAuthenticated.php` |
| Completar perfil | `OAuthAccountController.php`, `CompleteOAuthAccountProfileRequest.php`, `CompleteOAuthAccountProfileAction.php` |
| Interface pública | `Navbar.svelte`, `ProfileForm.svelte`, `ProfileIncompleteNotice.svelte` |

## Checklist

- Provider foi registrado em `config/oauth.php`?
- Credenciais existem em `config/services.php` e `.env`?
- Callback configurado no provider externo bate com `*_REDIRECT_URI`?
- `state` é validado no callback?
- Cookie OAuth salva token, banco salva hash?
- Rotas públicas que precisam da prop `oauth` usam `oauth.resolve`?
- Ações públicas protegidas usam `oauth`?
