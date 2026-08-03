---
status: ativo
tipo: indice-projeto
atualizado_em: 2026-08-03
---

# Guia do Projeto

Esta documentação explica o Akiba pela estrutura real do código. A ideia é simples: quando você abrir uma pasta, esta doc deve explicar o que existe ali, para que serve e como criar algo novo seguindo o padrão do projeto.

## Por Onde Começar

1. Leia [Estrutura Geral](./estrutura-geral) para entender o mapa do projeto.
2. Leia [Fluxo de uma Feature](./fluxos/feature) para entender como uma mudança passa por rota, controller, action, model e interface.
3. Use as páginas por tipo de arquivo quando estiver mexendo em uma camada específica.

## Consulta Rápida

| Se você vai mexer em... | Leia |
| --- | --- |
| Rota nova ou endpoint | [Rotas](./backend/rotas) |
| Configuração ou variável ENV | [Configurações e ENV](./configuracoes) |
| Login do painel | [Painel Administrativo](./autenticacao-interna) |
| OAuth público | [OAuth para Site Publico](./oauth) |
| Tela Inertia | [Controllers](./backend/controllers) e [Interface](./frontend/interface) |
| Page Svelte | [Pages](./frontend/pages) |
| Layout público ou privado | [Layouts](./frontend/layouts) |
| Formulário | [Requests](./backend/requests), [Actions](./backend/actions) e [Componentes](./frontend/componentes) |
| Formulário Svelte | [Forms](./frontend/forms) |
| Componente ou widget | [Componentes e Widgets](./frontend/componentes) |
| Estado compartilhado | [Stores](./frontend/stores) |
| Helper frontend | [Utils](./frontend/utils) |
| Lista fixa de opções | [Constants](./frontend/constants) |
| Regra de salvar/alterar | [Actions](./backend/actions) |
| Listagem com busca | [Filters](./backend/filters) e [Resources](./backend/resources) |
| Dados enviados para Svelte | [Resources](./backend/resources) |
| Permissões | [Policies](./backend/policies) |
| Middleware ou prop global Inertia | [Middlewares](./backend/middlewares) |
| Integração externa | [Services](./backend/services) |
| Command ou schedule | [Commands e Schedules](./backend/commands) |
| Tabela ou coluna nova | [Banco de Dados](./database/banco) e [Models](./backend/models) |
| Factory | [Factories](./database/factories) |
| Seeder ou permissão inicial | [Seeders](./database/seeders) |
| Teste | [Testes](./testes) |

## Áreas Principais

| Área | Pasta | O que você encontra |
| --- | --- | --- |
| Backend | `app/` | Controllers, actions, models, filters, requests, resources, policies e services. |
| Rotas | `routes/` | Entrada HTTP da aplicação, separada por contexto. |
| Interface | `resources/js/` | Páginas Svelte, layouts, componentes, widgets, stores e utilitários. |
| Banco | `database/` | Migrations, seeders e factories. |
| Testes | `tests/` | Testes unitários, feature, comandos, services, actions e models. |
| Docs | `docs/` | Documentação VitePress do projeto. |

## Regra Mental

Uma feature normalmente passa por este caminho:

```txt
Rota
  -> Controller
     -> Request
     -> Policy
     -> Action
        -> Model
     -> Resource
  -> Página Svelte
     -> Componentes/widgets
```

Nem toda feature usa todas as camadas. Uma tela apenas de leitura pode usar rota, controller, filter, resource e página. Uma ação de salvar normalmente usa request, policy e action.

## Convenções Gerais

- Controllers coordenam.
- Requests validam.
- Policies autorizam.
- Actions executam regra de negócio.
- Models representam dados e relações.
- Filters montam queries reutilizáveis.
- Resources formatam dados para a interface.
- Pages Svelte organizam a tela.
- Components são peças pequenas.
- Widgets são blocos maiores de domínio.
