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

## Configuração obrigatória

Antes de rodar, copie o arquivo `.env.example` e renomeie a cópia para `.env`.

Depois, preencha as variáveis de conexão com o banco de dados no `.env`:

```env
DB_DATABASE=akiba
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

- `DB_DATABASE`: nome do banco de dados usado pela aplicação.
- `DB_USERNAME`: usuário usado para conectar no banco de dados.
- `DB_PASSWORD`: senha do usuário usado para conectar no banco de dados.

Após isso, preencha as variáveis com as informações da sua stream:

```env
STREAM_URL=null
STREAM_METADATA=null
```

- `STREAM_URL`: URL usada para redirecionar ou conectar o player ao stream da rádio.
- `STREAM_METADATA`: URL usada para buscar os metadados da stream, como música atual e informações da transmissão.

## Instalação com Docker

Clone o repositório e rode a instalação usando Docker. O `install.sh` irá montar todo o ambiente pronto para execução.

```bash
./scripts/install.sh
```

## Instalação sem Docker

Para rodar o projeto sem Docker, instale previamente:

- PHP 8
- Composer
- Node.js
- MySQL

Caso deseje acessar o MySQL por uma interface gráfica, você pode usar o `DBeaver` ou outra ferramenta de sua preferência.

Depois clone o repositório e crie um banco de dados MySQL chamado `akiba`.

Em seguida, rode os comandos:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Para levantar o ambiente de desenvolvimento, use o comando:

```bash
php artisan dev
```

## Integrações externas

O projeto inclui variáveis de ambiente extras para integrações com Discord:

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
