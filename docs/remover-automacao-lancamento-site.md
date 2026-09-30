# Remover automacao de lancamento do site

Este documento descreve o que remover depois que o lancamento do site publico for concluido e validado.

## Contexto

A automacao `site:promote-public` foi criada para rodar no dia 9 as 20h no horario de Brasilia. Ela remove a interface provisoria, promove as rotas de `/site` para `/`, remove o bloqueio `auth` da home publica e atualiza a versao publica para que abas abertas facam uma nova requisicao Inertia.

Depois do lancamento, essa automacao nao precisa continuar no projeto.

## Remocao recomendada

1. Remover o agendamento em `routes/console.php`:

```php
Schedule::command('site:promote-public')
    ->monthlyOn(9, '20:00')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping();
```

2. Remover o comando:

```text
app/Console/Commands/Schedules/PromotePublicSite.php
```

3. Remover a prop compartilhada `publicSiteVersion` de `app/Http/Middleware/HandleInertiaRequestsMiddleware.php`.

Tambem remover o import:

```php
use Illuminate\Support\Facades\Cache;
```

caso ele nao esteja sendo usado por outro trecho do arquivo.

4. Remover o watcher do layout publico em `resources/js/lib/layouts/public/Layout.svelte`:

```js
startPublicSiteRefreshWatcher,
```

```js
export let publicSiteVersion = null;
```

```js
const stopRefreshWatcher = startPublicSiteRefreshWatcher(() => publicSiteVersion);
```

```js
stopRefreshWatcher?.();
```

5. Remover o export em `resources/js/lib/utils/index.js`:

```js
export { startPublicSiteRefreshWatcher } from "./navigation/publicSiteRefresh.js"
```

6. Remover o arquivo:

```text
resources/js/lib/utils/navigation/publicSiteRefresh.js
```

## Validacao depois da limpeza

Rodar:

```bash
php -l routes/console.php
php -l app/Http/Middleware/HandleInertiaRequestsMiddleware.php
php artisan schedule:list
```

Confirmar que `site:promote-public` nao aparece mais no `schedule:list`.

Como a mudanca toca o front-end, rodar o build manualmente quando for conveniente:

```bash
docker compose exec node npm run build
```

## Observacao

Nao desfazer as mudancas aplicadas pelo lancamento. Depois que o site estiver no ar, `/` deve continuar sendo a rota publica definitiva e a interface provisoria deve continuar removida.
