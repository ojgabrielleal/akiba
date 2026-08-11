# Regras De Controllers Publicos

Escopo: tudo em `app/Http/Controllers/Public`.

## Regra Principal

Controllers publicos orquestram paginas e interacoes abertas ao usuario, mantendo queries, resources e services separados por responsabilidade.

## Estrutura

- Controllers publicos ficam direto em `app/Http/Controllers/Public`.
- Nao use pastas `Pages` ou `Invokes`.
- Nao use sufixo `Page` no nome do controller.
- O controller deve representar a tela ou escopo que atende, como `HomeController`, `RadioController`, `ReadController` ou `PlayerController`.

## Responsabilidades

- Acoes de negocio devem passar por `app/Services`, injetados no construtor ou no metodo conforme o padrao local.
- Input validado deve usar `app/Http/Requests`, injetados como parametros de metodo.
- Mantenha parametros de metodo na ordem: request, service e depois model vindo da rota, quando todos existirem.
- Metodos `show` devem retornar `InertiaRender` com a prop correspondente e quaisquer props de pagina que a UI ainda precise.
- Nomes de metodos de acao devem indicar acao e escopo, como `storeSongRequest`, `storeComment`, `toggleLike` ou `updateOAuthAccountProfile`.
- Fluxos de player publico pertencem a `PlayerController`, nao a controllers de pagina como `RadioController`.

## Paginas Inertia

- Controllers que renderizam pagina devem ter um metodo `render`.
- O metodo `render` deve ser a ultima funcao do controller.
- Props devem ser montadas por metodos privados como `indexPosts`.
- Queries de pagina devem usar o service do escopo correspondente, normalmente pelo metodo `filter()`.
- Use metodos privados `index*` para montar props/resources de pagina.
- Nao use `show*` como helper de prop; reserve `show` para actions de controller que retornam `InertiaRender`.
- Quando um metodo privado existir apenas para retornar um array usado por outro metodo, incorpore o array no ponto de uso.

## Organizacao Interna

- Atributos e construtor devem aparecer logo apos abertura da classe e `use` de traits.
- Use promocao de propriedades no construtor, como `public function __construct(private PostService $postFilter) {}`.
- Mantenha arrays ou strings simples de relations diretamente no `with`/`load` correspondente.
- Crie metodos privados `*Relations` somente quando o conjunto de relations tiver callbacks de query, logica encadeada ou for compartilhado por multiplas queries no mesmo escopo.
- Importe dependencias com `use` antes da classe, mantendo agrupamento legivel entre controller base, models, requests, resources, services, facades e Inertia.

## Finalizacao

- Nao coloque regra privada ou administrativa em controllers publicos.
- Nao coloque validacao inline no controller; use FormRequests.
