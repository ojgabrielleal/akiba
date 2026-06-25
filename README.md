<div align="center">
  <img src="https://i.imgur.com/WbKAm6A.png" alt="Akiba V2" width="500" />
</div>
<br/>

Portal de notícias e web rádio da Rede Akiba, trazendo uma experiência renovada para fãs da cultura japonesa desde 2016.

## Sobre

Rede Akiba ( Akiba V2 ) é uma aplicação Laravel para gerenciar uma plataforma de comunidade e rádio focada em cultura anime. O projeto combina uma experiência pública de player com pedidos de músicas e um painel privado para conteúdo, programação, locução ao vivo, mídias, materiais de marketing, usuários, permissões e tarefas operacionais.

## Stack

- **Backend:** PHP 8.2, Laravel 12
- **Frontend:** Javascriot, Inertia, Svelte, Tailwind
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

## Instalação

Clone o repositório e rode a instalação usando Docker

```bash
./run.sh install
```

No Windows:

```powershell
.\scripts\run.ps1 install
```

Atalhos principais no Linux/macOS:

```bash
./run.sh up
./run.sh down
./run.sh artisan migrate
./run.sh npm install
./run.sh laravel sh
```

Atalhos principais no Windows:

```powershell
.\scripts\run.ps1 server up
.\scripts\run.ps1 server down
.\scripts\run.ps1 laravel php artisan migrate
.\scripts\run.ps1 node npm install
.\scripts\run.ps1 shell node
```

## Variáveis de ambiente

O projeto inclui variáveis de ambiente extras para integrações com stream e Discord:

```env
DISCORD_STREAM_WEBHOOK=null
```
- `DISCORD_STREAM_WEBHOOK`: Defina esse valor quando quiser enviar notificações da stream por webhook.

## Estrutura do Projeto

- `app/Actions` - casos de uso da aplicação agrupados por domínio
- `app/Http/Controllers` - controllers públicos, privados, de API e provisórios
- `app/Http/Requests` - validação de formulários
- `app/Http/Resources` - formatação de respostas de API/resources
- `app/Models` - Eloquent models
- `app/Policies` - regras de autorização
- `app/Services` - serviços externos e de processamento
- `database/seeders` - dados iniciais e registros de desenvolvimento
- `resources/js/pages` - páginas Inertia/Svelte
- `resources/js/ui` - componentes de UI, layouts e widgets reutilizáveis
- `routes/web` - rotas web separadas por contexto

## Licença

Este projeto é open-source sob a licença MIT.
