---
status: ativo
tipo: guia-config
atualizado_em: 2026-08-03
---

# Configuracoes e ENV

Esta página lista as configurações importantes do projeto e onde elas são usadas.

## Arquivos Principais

```txt
.env
config/services.php
config/oauth.php
config/database.php
config/session.php
config/filesystems.php
config/queue.php
```

## Stream da Rádio

Variáveis:

```ini
STREAM_URL=https://stm3.painelcast.com:7770/stream
STREAM_METADATA=http://cast.radioamc.com.br/api-json/...
```

Config:

```txt
config/services.php
services.stream.url
services.stream.metadata
```

Usado por:

```txt
app/Services/External/StreamService.php
app/Http/Controllers/Api/StreamController.php
app/Http/Middleware/HandleInertiaRequestsMiddleware.php
routes/api.php
resources/js/lib/widgets/public/player
resources/js/lib/widgets/private/grid/StreamMetricsGrid.svelte
```

Rotas:

```txt
GET /api/stream
GET /api/stream/metadata
```

## Audiência

Variável:

```ini
AUDIENCE_INTERNAL_STATION_NAME="Rádio Akiba"
```

Config:

```txt
services.audience.internal_station_name
```

Usado por:

```txt
app/Services/External/AudienceService.php
app/Services/Process/AudienceCollectorService.php
app/Console/Commands/Schedules/CollectAudience.php
database/seeders/RadioStationSeeder.php
```

Comando:

```bash
./run.sh artisan audience:collect
```

## Discord Webhook

Variável:

```ini
DISCORD_WEBHOOK_STREAM_NOTIFICATION=
```

Config:

```txt
services.discord.webhook
```

Usado por:

```txt
app/Services/External/DiscordWebhookService.php
app/Actions/Locution/StartLocutionAction.php
```

Observações:

- só envia em produção;
- se a variável estiver vazia, nada é enviado;
- usado para notificar programa ao vivo.

## Discord OAuth

Variáveis:

```ini
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_REDIRECT_URI=
```

Config:

```txt
services.discord.oauth
config/oauth.php
```

Usado por:

```txt
app/Services/External/OAuthAccount/DiscordOAuthAccountService.php
app/Actions/OAuthAccount/Providers/DiscordOAuthAccountAction.php
routes/web/public.php
```

## Google OAuth

Variáveis:

```ini
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
```

Config:

```txt
services.google.oauth
config/oauth.php
```

Usado por:

```txt
app/Services/External/OAuthAccount/GoogleOAuthAccountService.php
app/Actions/OAuthAccount/Providers/GoogleOAuthAccountAction.php
routes/web/public.php
```

## OneSignal

Variáveis:

```ini
ONESIGNAL_APP_ID=
ONESIGNAL_REST_API_KEY=
ONESIGNAL_VERIFY_SSL=true
```

Config:

```txt
services.onesignal
```

Usado por:

```txt
app/Services/External/OneSignalService.php
app/Actions/Locution/StartLocutionAction.php
```

Observações:

- só envia em produção;
- usado para push notification de programa ao vivo.

## Banco de Dados

Variáveis comuns:

```ini
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=akiba
DB_USERNAME=root
DB_PASSWORD=root
```

Usado por:

```txt
docker-compose.yml
config/database.php
database/migrations
database/seeders
tests
```

## Session e Cookies

Arquivos:

```txt
config/session.php
app/Http/Controllers/Private/Invokes/LoginController.php
app/Actions/OAuthAccount/Providers
app/Http/Middleware/OAuth/ResolveOAuthAccount.php
```

Cookies próprios:

```txt
akiba_user_token
akiba_oauth_token
```

## Storage

Arquivos:

```txt
config/filesystems.php
app/Services/Process/ImageProcessService.php
```

O `ImageProcessService` salva no disco `public` e retorna caminho em:

```txt
/storage/images/...
```

## Checklist

- Variável nova foi adicionada em `config/*`?
- Nome da variável no README bate com `config/services.php`?
- Feature funciona com variável ausente quando for integração opcional?
- Integração externa tem fallback/log?
- Ambiente local não dispara webhook/push de produção?
