# AGENTS.md

## Stack do Projeto

Este projeto usa Laravel no backend e Svelte no frontend, integrados via Inertia.

- Backend: PHP 8.2, Laravel 12, Laravel Sanctum e Inertia Laravel.
- Frontend: Svelte, Inertia Svelte, Vite e Tailwind CSS.
- Banco de dados: MySQL 8.
- Ferramentas auxiliares: Composer, Artisan, Node/NPM, phpMyAdmin.

## Regras Gerais

- Nao rode `./run.sh npm run build` automaticamente apos alteracoes. O usuario prefere rodar o build manualmente.
- Nao suba o ambiente Docker automaticamente. Quando os containers forem necessarios, peca para o usuario executar `./run.sh up`.

## Arquitetura Atual

- `app/Services`: regras de negocio e operacoes de dominio, um arquivo por escopo na raiz da pasta. Exemplos: `UserService.php`, `PostService.php`, `SongRequestService.php`.
- `app/Integrations`: clientes para servicos externos, APIs, webhooks e streams. Os arquivos ficam na raiz de `Integrations`, exceto quando houver subescopo real.
- `app/Processing`: processamento interno reutilizavel que nao e regra de negocio direta. Use sufixo `Process`, como `ImageProcess` e `AudienceCollectorProcess`.
- `app/Actions` e `app/Filters` nao fazem mais parte da arquitetura. Nao crie arquivos novos nessas camadas.
- Controllers privados e publicos ficam na raiz do respectivo escopo, sem `Pages`, sem `Invokes` e sem sufixo `Page`.
- OAuth publico usa Laravel Socialite. Nao recrie troca manual de token/callback para providers.
- Push notification usa Web Push nativo com VAPID e `PushNotificationService`. Nao reintroduza OneSignal.

## Ambiente de Execucao

O projeto roda via Docker. Para executar o projeto e ter acesso aos comandos de PHP e Node, primeiro levante os containers:

```bash
./run.sh up
```

Use o wrapper `run.sh` para os comandos comuns do projeto:

```bash
./run.sh up
./run.sh npm <comando>
./run.sh node <comando>
./run.sh artisan <comando>
./run.sh composer <comando>
```

Exemplos:

```bash
./run.sh npm run build
./run.sh artisan test
./run.sh composer install
./run.sh node --version
```

Caso seja necessario executar algo que nao esteja coberto por esses atalhos, use os comandos padrao do Docker/Docker Compose.

## Containers Docker

Os containers definidos para o projeto sao:

- `akiba_app`: container da aplicacao Laravel/PHP. Executa `php artisan serve` na porta `8000`.
- `akiba_node`: container Node.js 22. Executa o servidor Vite na porta `5173`.
- `akiba_mysql`: container MySQL 8, exposto na porta `3306`.
- `akiba_phpmyadmin`: container phpMyAdmin, exposto na porta `8081`.

Todos os servicos usam a network `akiba_network`. O MySQL persiste dados no volume `mysql_data`.
