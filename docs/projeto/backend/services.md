---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Services

Services encapsulam integrações externas ou processos internos reutilizáveis. Eles existem para tirar detalhes técnicos de controllers e actions.

## Onde Ficam

```txt
app/Services/External
app/Services/Process
```

## Tipos

| Pasta | Uso |
| --- | --- |
| `External` | APIs, webhooks, OAuth, stream e provedores externos. |
| `Process` | Rotinas internas reutilizáveis que coordenam models ou services externos. |

## Regra Geral

Use service quando o código precisa responder “como falo com isso?” ou “como executo este processo técnico?”.

Não use service para autorização, validação de request, response HTTP, flash message ou renderização Inertia.

## Services Externos

### AnimeNewsFeedService

Arquivo:

```txt
app/Services/External/AnimeNewsFeedService.php
```

O que faz:

- busca notícias externas de anime;
- suporta fontes WordPress e RSS;
- normaliza posts externos para um formato único;
- aplica cache por fonte;
- pagina resultados para uso no painel;
- registra warning quando uma fonte falha.

Serviços externos usados:

```txt
Anime United WordPress API
IntoxiAnime WordPress API
AnimeNew WordPress API
OtakuPT WordPress API
Crunchyroll RSS
```

Arquivos relacionados:

```txt
app/Http/Controllers/Private/Pages/PostPageController.php
resources/js/pages/private/Post.svelte
resources/js/lib/widgets/private/grid/AnimeNewsFeedGrid.svelte
routes/web/private.php
```

Fluxo:

```txt
/panel/post
  -> PostPageController
     -> AnimeNewsFeedService::sources()
     -> AnimeNewsFeedService::paginate()
  -> private/Post.svelte
     -> AnimeNewsFeedGrid
```

Permissão relacionada:

```txt
post.feed.view
```

Cuidados:

- a fonte externa pode cair;
- o serviço retorna coleção vazia em falha;
- cache reduz chamadas externas;
- conteúdo externo deve ser tratado como sugestão para criação de post, não como dado interno confiável.

### AnimeThemeService

Arquivo:

```txt
app/Services/External/AnimeThemeService.php
```

O que faz:

- consulta a API do AnimeThemes;
- busca músicas por termo;
- busca animes por termo;
- normaliza nomes, imagens, músicas, artistas e metadados;
- usa cache por query.

Serviço externo usado:

```txt
https://api.animethemes.moe
```

Arquivos relacionados:

```txt
app/Http/Controllers/Api/External/AnimeThemesController.php
routes/api.php
resources/js/lib/widgets/private/form/MusicForm.svelte
resources/js/lib/widgets/private/form/ReviewForm.svelte
```

Rotas:

```txt
GET /api/anime-themes/search
GET /api/anime-themes/anime/search
```

Fluxo:

```txt
Frontend
  -> /api/anime-themes/search?q=...
     -> AnimeThemesController::search()
        -> AnimeThemeService::search()
```

Cuidados:

- retorna `null` quando a API falha;
- respostas são cacheadas por 12 horas;
- use timeout curto para não travar a experiência do painel.

### AudienceService

Arquivo:

```txt
app/Services/External/AudienceService.php
```

O que faz:

- consulta o endpoint de audiência de uma rádio;
- lê o número de ouvintes usando `listeners_path`;
- mede tempo de resposta;
- retorna status `online`, `offline` ou `invalid_response`;
- evita retorno negativo de ouvintes.

Serviço externo usado:

```txt
Endpoint configurado em cada RadioStation
```

Arquivos relacionados:

```txt
app/Models/RadioStation.php
app/Services/Process/AudienceCollectorService.php
app/Console/Commands/Schedules/CollectAudience.php
tests/Unit/Services/AudienceServiceTest.php
```

Fluxo:

```txt
php artisan audience:collect
  -> CollectAudience
     -> AudienceCollectorService::collect()
        -> AudienceService::get($radioStation)
        -> RadioAudienceSnapshot
```

Cuidados:

- `listeners_path` precisa bater com o JSON externo;
- falhas HTTP não quebram a coleta;
- o retorno é normalizado para ser salvo em snapshot.

### DiscordWebhookService

Arquivo:

```txt
app/Services/External/DiscordWebhookService.php
```

O que faz:

- envia notificação de programa ao vivo para Discord;
- monta payload com mensagem, embed, DJ, programa e timestamp;
- só envia em produção;
- ignora envio quando webhook não está configurado.

Serviço externo usado:

```txt
Discord Webhook
```

Configuração:

```txt
config/services.php
services.discord.webhook
DISCORD_WEBHOOK_STREAM_NOTIFICATION
```

Arquivos relacionados:

```txt
app/Actions/Locution/StartLocutionAction.php
app/Http/Controllers/Private/Invokes/StartLocutionController.php
routes/web/private.php
```

Fluxo:

```txt
POST /panel/locution/start/{program}
  -> StartLocutionController
     -> StartLocutionAction
        -> DiscordWebhookService::sendStreamNotificationHook()
```

Cuidados:

- não espere envio em ambiente local;
- se a URL não estiver configurada, o método retorna sem enviar;
- payload de produção deve evitar dados sensíveis.

### OneSignalService

Arquivo:

```txt
app/Services/External/OneSignalService.php
```

O que faz:

- envia push notification pelo OneSignal;
- monta título, mensagem e URL;
- envia para o segmento `All`;
- registra erro quando a API falha;
- só envia em produção.

Serviço externo usado:

```txt
https://api.onesignal.com
```

Configuração:

```txt
config/services.php
services.onesignal.app_id
services.onesignal.api_key
ONESIGNAL_APP_ID
ONESIGNAL_REST_API_KEY
```

Arquivos relacionados:

```txt
app/Actions/Locution/StartLocutionAction.php
app/Http/Controllers/Private/Invokes/StartLocutionController.php
routes/web/private.php
```

Fluxo:

```txt
POST /panel/locution/start/{program}
  -> StartLocutionController
     -> StartLocutionAction
        -> OneSignalService::sendPush()
```

Cuidados:

- não enviar push em ambiente local;
- conferir credenciais antes de publicar;
- mensagens precisam ser curtas e seguras para público geral.

### StreamService

Arquivo:

```txt
app/Services/External/StreamService.php
```

O que faz:

- consulta metadados da rádio;
- retorna status, ouvintes, bitrate e música atual;
- usa fallback quando a URL não está configurada, falha ou retorna formato inválido.

Serviço externo usado:

```txt
Endpoint configurado em STREAM_METADATA
```

Configuração:

```txt
config/services.php
services.stream.url
services.stream.metadata
STREAM_URL
STREAM_METADATA
```

Arquivos relacionados:

```txt
app/Http/Controllers/Api/StreamController.php
app/Http/Middleware/HandleInertiaRequestsMiddleware.php
bootstrap/app.php
routes/api.php
resources/js/lib/widgets/public/player
resources/js/lib/widgets/private/grid/StreamMetricsGrid.svelte
```

Rotas:

```txt
GET /api/stream
GET /api/stream/metadata
```

Fluxo:

```txt
Inertia middleware
  -> StreamService::data()
  -> prop compartilhada stream
  -> player público / métricas privadas
```

Cuidados:

- o fallback evita quebrar páginas públicas;
- logs ajudam a identificar URL ausente ou resposta inválida;
- a estrutura retornada deve continuar compatível com widgets do player.

### DiscordOAuthAccountService

Arquivo:

```txt
app/Services/External/OAuthAccount/DiscordOAuthAccountService.php
```

O que faz:

- monta URL de autorização do Discord;
- salva `state` na sessão;
- troca `code` por token;
- busca dados do usuário autenticado;
- valida `state` para reduzir risco de CSRF no OAuth.

Serviço externo usado:

```txt
Discord OAuth2
```

Configuração:

```txt
services.discord.oauth.client_id
services.discord.oauth.client_secret
services.discord.oauth.redirect_uri
DISCORD_CLIENT_ID
DISCORD_CLIENT_SECRET
DISCORD_REDIRECT_URI
```

Arquivos relacionados:

```txt
app/Actions/OAuthAccount/Providers/DiscordOAuthAccountAction.php
app/Http/Controllers/Api/External/OAuthAccount/OAuthAccountRedirectController.php
app/Http/Controllers/Api/External/OAuthAccount/OAuthAccountCallbackController.php
app/Http/Middleware/OAuth/ResolveOAuthAccount.php
app/Http/Middleware/OAuth/EnsureOAuthAccountAuthenticated.php
routes/web/public.php
```

Rotas:

```txt
GET /oauth/{provider}/redirect
GET /oauth/{provider}/callback
POST /oauth/logout
```

### GoogleOAuthAccountService

Arquivo:

```txt
app/Services/External/OAuthAccount/GoogleOAuthAccountService.php
```

O que faz:

- monta URL de autorização do Google;
- salva `state` na sessão;
- troca `code` por token;
- busca dados do usuário;
- usa escopos `openid email profile`.

Serviço externo usado:

```txt
Google OAuth2
```

Configuração:

```txt
services.google.oauth.client_id
services.google.oauth.client_secret
services.google.oauth.redirect_uri
GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET
GOOGLE_REDIRECT_URI
```

Arquivos relacionados:

```txt
app/Actions/OAuthAccount/Providers/GoogleOAuthAccountAction.php
app/Http/Controllers/Api/External/OAuthAccount/OAuthAccountRedirectController.php
app/Http/Controllers/Api/External/OAuthAccount/OAuthAccountCallbackController.php
app/Http/Middleware/OAuth/ResolveOAuthAccount.php
routes/web/public.php
```

## Services de Processo

### AudienceCollectorService

Arquivo:

```txt
app/Services/Process/AudienceCollectorService.php
```

O que faz:

- percorre rádios ativas;
- chama `AudienceService` para medir audiência;
- salva snapshots em `RadioAudienceSnapshot`;
- atualiza pico de audiência do programa atual quando a estação é a interna.

Configuração:

```txt
services.audience.internal_station_name
AUDIENCE_INTERNAL_STATION_NAME
```

Arquivos relacionados:

```txt
app/Console/Commands/Schedules/CollectAudience.php
app/Models/RadioStation.php
app/Models/RadioAudienceSnapshot.php
app/Models/Onair.php
tests/Unit/Services/AudienceCollectorServiceTest.php
```

Comando:

```bash
./run.sh artisan audience:collect
```

### ImageProcessService

Arquivo:

```txt
app/Services/Process/ImageProcessService.php
```

O que faz:

- recebe upload;
- converte imagem para WebP;
- salva no disco `public`;
- remove imagem antiga quando informado;
- retorna caminho público em `/storage/...`.

Dependências:

```txt
Intervention Image
Storage public disk
```

Arquivos relacionados:

```txt
app/Actions/Post/StorePostAction.php
app/Actions/Post/UpdatePostAction.php
app/Actions/Podcast/StorePodcastAction.php
app/Actions/Podcast/UpdatePodcastAction.php
app/Actions/Program/StoreProgramAction.php
app/Actions/Program/UpdateProgramAction.php
app/Actions/Music/UpdateMusicAction.php
app/Actions/Role/StoreRoleAction.php
app/Actions/Role/UpdateRoleAction.php
app/Actions/Role/DestroyRoleAction.php
app/Actions/Repository/StoreRepositoryAction.php
app/Actions/Repository/UpdateRepositoryAction.php
app/Actions/ListenerGallery/StoreListenerGalleryAction.php
app/Actions/ListenerGallery/UpdateListenerGalleryAction.php
app/Actions/ListenerGallery/DestroyListenerGalleryAction.php
app/Actions/Profile/UpdateProfileAction.php
app/Actions/Profile/UpdateUserAction.php
tests/Unit/Services/ImageProcessServiceTest.php
```

Cuidados:

- passar imagem antiga quando update deve substituir arquivo;
- usar `forceFormData` no frontend quando enviar arquivo;
- garantir link simbólico de storage em ambiente Laravel quando necessário.

## Checklist

- O service tem responsabilidade clara?
- Integração externa tem timeout/fallback/log?
- Configurações ficam em `config/services.php`?
- Controller/action não conhece detalhes do provedor externo?
- Existe teste quando a regra é crítica?
- O service não retorna redirect, Inertia ou flash message?
