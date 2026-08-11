---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Services

Services encapsulam integrações externas ou processos internos reutilizáveis. Eles existem para tirar detalhes técnicos de controllers e actions.

## Onde Ficam

```txt
app/Integrations
app/Processing
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
app/Integrations/AnimeNewsFeedService.php
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
app/Integrations/AnimeThemeService.php
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
app/Integrations/AudienceService.php
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
app/Processing/AudienceCollectorProcess.php
app/Console/Commands/Schedules/CollectAudience.php
tests/Unit/Services/AudienceServiceTest.php
```

Fluxo:

```txt
php artisan audience:collect
  -> CollectAudience
     -> AudienceCollectorProcess::collect()
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
app/Integrations/DiscordWebhookService.php
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

### PushNotificationService

Arquivo:

```txt
app/Services/PushNotificationService.php
```

O que faz:

- registra inscrições Web Push de usuários internos;
- envia push para um usuário específico;
- envia push para todos quando não houver usuário alvo;
- remove inscrições expiradas.

Serviço externo usado:

```txt
Web Push nativo
```

Configuração:

```txt
config/services.php
services.webpush.public_key
services.webpush.private_key
services.webpush.subject
VAPID_PUBLIC_KEY
VAPID_PRIVATE_KEY
VAPID_SUBJECT
```

Arquivos relacionados:

```txt
app/Models/PushSubscription.php
app/Http/Controllers/Private/PushSubscriptionController.php
app/Services/SongRequestService.php
routes/web/private.php
public/push-worker.js
```

Fluxo:

```txt
POST /song-request
  -> PlayerController::storeSongRequest()
     -> SongRequestService::store()
        -> PushNotificationService::sendToUserOrAll()
```

Cuidados:

- sem chaves VAPID configuradas o envio é ignorado;
- inscrições expiradas devem ser removidas;
- mensagens devem ser curtas e seguras para notificações de sistema.

### StreamService

Arquivo:

```txt
app/Integrations/StreamService.php
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

### OAuth via Socialite

Arquivo:

```txt
app/Services/OAuthAccountService.php
```

O que faz:

- completa perfil OAuth público;
- normaliza usuário retornado pelo Socialite;
- cria ou atualiza `OAuthAccount`;
- gera token local e grava apenas o hash no banco;
- cria cookie `akiba_oauth_token`.

Serviço externo usado:

```txt
Laravel Socialite
socialiteproviders/discord
```

Configuração:

```txt
services.discord.client_id
services.discord.client_secret
services.discord.redirect
services.google.client_id
services.google.client_secret
services.google.redirect
DISCORD_CLIENT_ID
DISCORD_CLIENT_SECRET
DISCORD_REDIRECT_URI
GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET
GOOGLE_REDIRECT_URI
```

Arquivos relacionados:

```txt
app/Http/Controllers/Api/External/OAuthAccount/OAuthAccountRedirectController.php
app/Http/Controllers/Api/External/OAuthAccount/OAuthAccountCallbackController.php
app/Http/Middleware/OAuth/ResolveOAuthAccount.php
app/Http/Middleware/OAuth/EnsureOAuthAccountAuthenticated.php
routes/web/public.php
app/Providers/AppServiceProvider.php
```

Rotas:

```txt
GET /oauth/{provider}/redirect
GET /oauth/{provider}/callback
POST /oauth/logout
```

### AudienceCollectorProcess

Arquivo:

```txt
app/Processing/AudienceCollectorProcess.php
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
tests/Unit/Services/AudienceCollectorProcessTest.php
```

Comando:

```bash
./run.sh artisan audience:collect
```

### ImageProcess

Arquivo:

```txt
app/Processing/ImageProcess.php
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
tests/Unit/Services/ImageProcessTest.php
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
