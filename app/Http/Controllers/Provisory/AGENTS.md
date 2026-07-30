# Regras De Controllers Provisorios

Escopo: tudo em `app/Http/Controllers/Provisory`.

## Regra Principal

Controllers provisorios devem seguir o mesmo padrao de orquestracao dos controllers Inertia, mantendo codigo temporario organizado e facil de remover.

## Estrutura

- Mantenha renderizadores de pagina em `/Pages`.
- Mantenha handlers nao CRUD em `/Invokes`.
- Mantenha controllers CRUD de modulo na raiz da pasta do modulo.

## Responsabilidades

- `store`, `update` e `delete` devem passar por `app/Actions`, injetadas como parametros de metodo.
- Input validado deve usar `app/Http/Requests`, injetados como parametros de metodo.
- Mantenha parametros de metodo na ordem: request, action e depois model vindo da rota.
- Metodos `show` devem retornar `InertiaRender` com a prop correspondente e quaisquer props de pagina que a UI ainda precise.

## Paginas Inertia

- Page controllers devem renderizar por um metodo `render`.
- Props devem ser montadas por metodos privados como `indexPosts`.
- Queries de pagina devem usar filters injetados pelo construtor.
- Mantenha `render` como primeiro metodo de comportamento.
- Quando uma pagina precisar de um model/query compartilhado, busque com um metodo privado `get*` logo apos `render` e passe o resultado para os metodos de props que precisam dele.
- Use metodos privados `index*` para montar props/resources de pagina.
- Nao use `show*` como helper de prop; reserve `show` para actions de controller que retornam `InertiaRender`.

## Organizacao Interna

- Mantenha arrays ou strings simples de relations diretamente no `with`/`load` correspondente.
- Crie metodos privados `*Relations` somente quando o conjunto de relations tiver callbacks de query, logica encadeada ou for compartilhado por multiplas queries no mesmo escopo.
- Importe dependencias com `use` antes da classe, ordenadas como defaults do Laravel, exceptions, models, requests, resources, actions e depois services.

## Finalizacao

- Nao deixe regras temporarias se espalharem para models ou resources.
- Ao promover uma tela provisoria para definitiva, mova o controller para o escopo correto e preserve este padrao.
