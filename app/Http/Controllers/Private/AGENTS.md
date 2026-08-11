# Regras De Controllers Privados

Escopo: tudo em `app/Http/Controllers/Private`.

## Regra Principal

Controllers privados orquestram requests, autorizacao, services, resources e renderizacao Inertia do painel privado.

## Estrutura

- Controllers privados ficam direto em `app/Http/Controllers/Private`.
- Nao use pastas `Pages` ou `Invokes`.
- Nao use sufixo `Page` no nome do controller.
- O controller deve representar a tela ou escopo que ele atende, como `DashboardController`, `LocutionController`, `TrashController` e `PushSubscriptionController`.

## Responsabilidades

- Acoes de negocio devem passar por `app/Services`, injetados no construtor ou no metodo conforme o padrao local.
- Input validado deve usar `app/Http/Requests`, injetados como parametros de metodo.
- Mantenha parametros de metodo na ordem: request, service e depois model vindo da rota, quando todos existirem.
- Controllers privados devem autorizar cada metodo; quando um FormRequest valida o metodo, coloque a autorizacao no request.
- Metodos `show` devem retornar `InertiaRender` com a prop correspondente e quaisquer props de pagina que a UI ainda precise.
- Nomes de metodos de acao devem indicar acao e escopo, como `storePost`, `updateProfile`, `deactivatePodcast`, `markSongRequestAsPlayed`.

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
- Importe dependencias com `use` antes da classe, mantendo agrupamento legivel entre controller base/concerns, models, requests, resources, services, facades e Inertia.

## Finalizacao

- Nao coloque regras de negocio extensas no controller; use services.
- Nao coloque validacao inline no controller; use FormRequests.
