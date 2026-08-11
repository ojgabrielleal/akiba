# Regras De Controllers Provisorios

Escopo: tudo em `app/Http/Controllers/Provisory`.

## Regra Principal

Controllers provisorios devem seguir o mesmo padrao de orquestracao dos controllers Inertia, mantendo codigo temporario organizado e facil de remover.

## Estrutura

- Controllers provisorios ficam direto em `app/Http/Controllers/Provisory`.
- Nao crie pastas `Pages` ou `Invokes`.
- Nao use sufixo `Page`.
- Ao promover uma tela provisoria para definitiva, mova o controller para `Public` ou `Private` e preserve a assinatura dos metodos.

## Responsabilidades

- Acoes de negocio devem passar por `app/Services`.
- Input validado deve usar `app/Http/Requests`, injetados como parametros de metodo.
- Mantenha parametros de metodo na ordem: request, service e depois model vindo da rota, quando todos existirem.
- Metodos `show` devem retornar `InertiaRender` com a prop correspondente e quaisquer props de pagina que a UI ainda precise.

## Paginas Inertia

- Controllers que renderizam pagina devem ter um metodo `render`.
- O metodo `render` deve ser a ultima funcao do controller.
- Props devem ser montadas por metodos privados como `indexPosts`.
- Queries de pagina devem usar o service do escopo correspondente, normalmente pelo metodo `filter()`.
- Use metodos privados `index*` para montar props/resources de pagina.
- Nao use `show*` como helper de prop; reserve `show` para actions de controller que retornam `InertiaRender`.

## Organizacao Interna

- Mantenha arrays ou strings simples de relations diretamente no `with`/`load` correspondente.
- Crie metodos privados `*Relations` somente quando o conjunto de relations tiver callbacks de query, logica encadeada ou for compartilhado por multiplas queries no mesmo escopo.
- Atributos e construtor devem aparecer logo apos abertura da classe e `use` de traits.
- Importe dependencias com `use` antes da classe, mantendo agrupamento legivel entre controller base, models, requests, resources, services, facades e Inertia.

## Finalizacao

- Nao deixe regras temporarias se espalharem para models ou resources.
- Ao promover uma tela provisoria para definitiva, mova o controller para o escopo correto e preserve este padrao.
