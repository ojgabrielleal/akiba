<div align="center">
  <img src="https://i.imgur.com/WbKAm6A.png" alt="Akiba V2" width="500" />
</div>
<br/>

Portal de notícias e web rádio da Rede Akiba, trazendo uma experiência renovada para fãs da cultura japonesa desde 2016.

## Sobre

Rede Akiba ( Akiba V2 ) é uma aplicação Laravel para gerenciar uma plataforma de comunidade e rádio focada em cultura anime. O projeto combina uma experiência pública de player com pedidos de músicas e um painel privado para conteúdo, programação, locução ao vivo, mídias, materiais de marketing, usuários, permissões e tarefas operacionais.

## Stack

- **Backend:** PHP 8.2, Laravel 12
- **Frontend:** JavaScript, Inertia, Svelte, Tailwind
- **Banco de dados:** MySQL

## Principais Recursos

- Página pública com player e suporte a pedidos de músicas
- Painel privado com autenticação
- Dashboard com atividades e conclusão de tarefas
- Gerenciamento de posts, reviews, eventos, podcasts, mídias e materiais de marketing
- Gerenciamento da rádio para programas, rankings de músicas, ouvinte do mês e pedidos de músicas
- Fluxo de locução ao vivo para iniciar e finalizar transmissões
- Área de administração para usuários, funções, permissões, calendários, atividades, tarefas e programas automáticos
- Gerenciamento de perfil
- Endpoints de cast para redirecionamento de stream e metadados
- Integração com webhook do Discord para eventos da stream

## Requisitos

- Docker
- Docker Compose

## Instalação

Clone o repositório, copie o arquivo de ambiente e suba os containers:

```bash
cp .env.example .env
docker compose up -d --build
```

Instale as dependências dentro dos containers:

```bash
docker compose exec app composer install
docker compose exec node npm install
```

Prepare a aplicação:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

## Comandos Docker

Subir e parar o ambiente:

```bash
docker compose up -d
docker compose down
```

Comandos Laravel/PHP:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app composer install
docker compose exec app sh
```

Comandos Node/NPM:

```bash
docker compose exec node npm install
docker compose exec node npm run dev
docker compose exec node npm run build
docker compose exec node node --version
docker compose exec node sh
```

## Variáveis de ambiente

O projeto inclui variáveis de ambiente extras para integrações com stream e Discord:

```env
DISCORD_WEBHOOK_STREAM_NOTIFICATION=null
```
- `DISCORD_WEBHOOK_STREAM_NOTIFICATION`: Defina esse valor quando quiser enviar notificações da stream por webhook.

## Estrutura do Projeto

- `app/Services` - regras de negócio e operações de domínio
- `app/Integrations` - clientes para serviços externos, APIs, webhooks e streams
- `app/Processing` - processamento interno reutilizável que não é regra de negócio direta
- `app/Http/Controllers` - controllers públicos, privados, de API e provisórios
- `app/Http/Requests` - validação de formulários
- `app/Http/Resources` - formatação de respostas de API/resources
- `app/Models` - Eloquent models
- `app/Policies` - regras de autorização
- `app/Services` - serviços externos e de processamento
- `database/seeders` - dados iniciais e registros de desenvolvimento
- `resources/js/pages` - páginas Inertia/Svelte
- `resources/js/lib` - layouts, componentes, widgets, stores, utils e constants do frontend
- `routes/web` - rotas web separadas por contexto

## Planejamento e Documentação

A documentação do projeto fica em `docs/` e é publicada localmente com VitePress. Comece pelo site em `http://localhost:5174/` ou pelo arquivo `docs/projeto/index.md`.

Também é possível navegar a documentação com VitePress:

```bash
docker compose up -d docs
```

Outros comandos disponíveis:

```bash
docker compose exec node npm run docs:build
docker compose exec node npm run docs:preview
```

## Licença

Este projeto é open-source sob a licença MIT.
