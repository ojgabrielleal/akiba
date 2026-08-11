---
status: ativo
tipo: guia-auth
atualizado_em: 2026-08-11
---

# Painel Administrativo

Esta página documenta a autenticação do painel administrativo. Ela usa usuários do próprio sistema, sessão Laravel, cookie local e permissões por roles.

## Rotas

```txt
GET /panel
POST /panel/auth
POST /panel/logout
```

## Arquivos Envolvidos

| Arquivo | O que faz |
| --- | --- |
| `routes/web/private.php` | Declara rotas do painel, login, logout e grupos protegidos. |
| `LoginController.php` | Renderiza a página Inertia `private/Login`, autentica usuário interno, cria cookie local, encerra sessão e remove cookie local. |
| `AuthLoginRequest.php` | Valida credenciais do login. |
| `ShareAuthenticatedUserMiddleware.php` | Compartilha usuário autenticado e permissões com o Inertia. |
| `PermissionSeeder.php` | Cria permissões usadas no painel. |
| `RoleSeeder.php` | Cria papéis e vínculos iniciais. |
| `UserSeeder.php` | Cria usuários iniciais. |

## Fluxo de Login

```txt
POST /panel/auth
  -> AuthLoginRequest
     -> valida email/login e senha
  -> LoginController
     -> adiciona is_active = true nas credenciais
     -> Auth::attempt()
     -> session()->regenerate()
     -> gera accountToken
     -> salva hash em users.account_token_hash
     -> cria cookie akiba_user_token
     -> redirect panel.dashboard
```

## Fluxo de Logout

```txt
POST /panel/logout
  -> LoginController::logoutUser()
     -> limpa users.account_token_hash
     -> Auth::logout()
     -> session()->invalidate()
     -> session()->regenerateToken()
     -> remove cookie akiba_user_token
     -> redirect login
```

## Cookie Interno

Cookie:

```txt
akiba_user_token
```

Uso:

- criado no login interno;
- salvo no navegador por até 30 dias;
- banco guarda apenas `hash('sha256', token)`;
- usado pelo `ResolveOAuthAccount` para reconhecer membro interno em páginas públicas;
- não substitui a sessão Laravel do painel.

Quando a sessão do painel está ativa, `request()->user()` identifica o membro interno. Quando a sessão caiu, mas `akiba_user_token` ainda existe e bate com `users.account_token_hash`, o site público ainda reconhece o visitante como membro interno e compartilha `member_user` nos atributos da request.

Nessa situação, componentes públicos que exigem sessão do painel, como `AuthGuard`, devem pedir novo login no painel. A prop `oauth.member_session_authenticated` indica se a sessão Laravel do painel está ativa.

## Perfil Público do Membro Interno

Membros internos podem editar dados básicos pelo modal de perfil no site público, sem entrar no painel completo, desde que tenham permissões próprias.

Rotas:

```txt
PATCH /site/member-profile
POST /site/member-logout
```

Arquivos:

```txt
routes/web/public.php
app/Http/Controllers/Public/HomeController.php
app/Http/Requests/Profile/UpdatePublicMemberProfileRequest.php
app/Services/ProfileService.php
resources/js/lib/widgets/public/navbar/Navbar.svelte
resources/js/lib/widgets/public/form/ProfileForm.svelte
```

Permissões exigidas:

```txt
user.view.own
user.update.own
```

Campos editáveis:

```txt
avatar
nickname
birth_date
city
state
country
bibliography
```

O formulário só é exibido quando o membro possui permissão para ver e atualizar o próprio perfil. O request também valida as mesmas permissões no backend usando `request()->user()` ou `member_user`.

O logout público do membro interno remove `akiba_user_token`, zera `users.account_token_hash` e derruba a sessão Laravel quando ela estiver ativa.

## Middlewares

### auth

Middleware Laravel usado para proteger rotas internas.

### authenticated.user

Arquivo:

```txt
app/Http/Middleware/Auth/ShareAuthenticatedUserMiddleware.php
```

O que compartilha com Inertia:

```txt
id
uuid
slug
name
nickname
avatar
gender
roles
permissions
```

Fluxo:

```txt
Rota /panel protegida
  -> auth
  -> authenticated.user
     -> carrega roles.permissions
     -> compartilha prop user
  -> PageController
  -> Svelte recebe $page.props.user
```

## Permissões Internas

Usuário interno usa:

```txt
User
Role
Permission
Policies
HasPermissions
```

Arquivos:

```txt
database/seeders/PermissionSeeder.php
app/Policies
app/Models/Concerns/HasPermissions.php
resources/js/lib/utils/access/permissions.js
```

Exemplo de permissão:

```txt
report.module.view
post.create
locution.start
trash.restore
```

## Interface Relacionada

```txt
resources/js/pages/private/Login.svelte
resources/js/lib/widgets/private/form/LoginForm.svelte
resources/js/lib/layouts/private/Layout.svelte
resources/js/lib/widgets/private/navbar/Navbar.svelte
```

## Checklist

- Rota privada está dentro de `auth`?
- Rota do painel usa `authenticated.user` quando precisa da prop `user`?
- Login valida com `AuthLoginRequest`?
- Login exige `is_active = true`?
- Logout limpa sessão e cookie?
- Logout público do membro interno limpa `akiba_user_token` e sessão quando existir?
- Permissão nova foi criada no seeder?
- Policy e frontend usam o mesmo nome de permissão?
