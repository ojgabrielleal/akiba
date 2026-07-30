# Front-end Rules

Escopo: tudo em `resources/js`.

## Regra Principal

Este projeto usa Svelte + Inertia (`@inertiajs/svelte`) + Vite dentro de uma aplicacao Laravel.

O codigo em `resources/js` e somente a camada de UI/client. Nao crie `lib/server/`, `server/` ou qualquer equivalente dentro do frontend. Codigo de servidor, acesso a banco, secrets, SDKs privados, integracoes sensiveis e regras que nao podem ir para o browser pertencem ao Laravel.

Use a organizacao do Svelte como inspiracao, mas adapte ao Inertia:

```txt
SvelteKit              Este projeto
--------------------------------------------
src/routes             resources/js/pages
src/lib                resources/js/lib
src/lib/components     resources/js/lib/components
src/lib/server         nao existe no frontend
src/lib/utils          resources/js/lib/utils
src/lib/stores         resources/js/lib/stores
static                 public
```

## Stack

- Svelte.
- Inertia (`@inertiajs/svelte`).
- Vite.
- Tailwind CSS v4 com tokens em `css/app.css`.
- Imports internos com alias `@/`.

## Estrutura

```txt
resources/js/
  app.js
  bootstrap.js

  pages/
    private/
    public/
    provisory/

  lib/
    layouts/
      shared/
      private/
      public/

    components/
      shared/
      private/
        actions/
        feedback/
        forms/
        layout/
        navigation/
        overlays/
      public/
        actions/
        feedback/
        forms/
        layout/
        navigation/
        overlays/

    widgets/
      shared/
      private/
        carousel/
        chart/
        form/
        grid/
        hero/
        list/
        navbar/
        table/
      public/
        footer/
        form/
        grid/
        navbar/
        player/
        read/

    stores/
    utils/
    constants/

  css/
```

## Responsabilidades

- `pages/`: paginas Inertia. Orquestram `Meta`, `Layout`, dados de `$page.props` e widgets. Evite colocar forms grandes, tabelas complexas ou muita regra visual diretamente aqui.
- `lib/layouts/`: estruturas persistentes de tela por contexto, como layout publico, privado ou compartilhado.
- `lib/components/`: pecas pequenas e reutilizaveis, como botoes, inputs, badges, modais, tooltips, paginacao, `Meta` e blocos basicos de layout.
- `lib/widgets/`: blocos maiores de produto, como forms, grids, tabelas, charts, carrosseis, navbar, player e secoes especificas de tela.
- `lib/stores/`: estado compartilhado do browser.
- `lib/utils/`: helpers puros de frontend, separados por dominio.
- `lib/constants/`: constantes e opcoes estaticas usadas pela UI, como listas de select, tags, preferencias e configuracoes fixas.
- `css/`: estilos globais, tokens e temas.

## Paginas Inertia

- O backend renderiza paginas com `Inertia::render(...)`; o nome renderizado deve mapear para um arquivo em `resources/js/pages`.
- Paginas sao a fronteira com o Inertia: devem ler dados via `$page.props` e distribuir props explicitas para widgets.
- Paginas devem importar `Meta` de `@/lib/components/shared`, o `Layout` do contexto correto e widgets via `@/lib/...`.
- Formularios grandes pertencem a `lib/widgets/.../form`, nao diretamente em `pages/`.
- Use `useForm` para formularios Inertia.
- Passe erros do Inertia para os campos.
- Use `forceFormData: true` em uploads.
- Normalize arrays/objetos opcionais antes de montar forms.

## Componentes E Widgets

- Preserve a separacao `private`/`public`.
- Use `shared` somente quando o mesmo layout, componente ou widget for usado de verdade por mais de um contexto (`public`, `private` ou `provisory`). Se o uso for exclusivo de um contexto, mantenha no contexto especifico.
- Evite prop drilling: passe props apenas para quem usa diretamente.
- Evite passar objetos grandes como `data`, `props`, `pageData` ou um recurso inteiro apenas para algum neto acessar uma pequena parte.
- Widgets devem receber props explicitas com somente os dados que usam diretamente.
- Components pequenos nao devem ler `$page.props`; recebem props explicitas.
- Layouts podem ler `$page.props` quando o dado for global ou transversal, como `user`, `flash`, `oauth`, navbar, player, tema ou estado de menu.
- Excecoes a `$page.props` fora de paginas/layouts devem ser componentes globais/transversais bem justificados, como `Meta`, `FlashToaster` ou helpers de permissao/autenticacao.
- Use `lib/stores` apenas para estado client-side compartilhado; nao use stores como espelho de props do backend.
- Antes de criar algo novo, procure um componente ou widget parecido.
- Ao desmembrar um widget grande, extraia somente partes com responsabilidade real, como uma area visual/interativa propria, uma lista, um perfil, um formulario interno ou uma secao complexa. Evite criar wrappers finos que apenas encapsulam outro componente sem regra, estado, markup relevante ou reducao clara de duplicacao.
- Componentes internos extraidos de um widget devem ficar perto dele e nao precisam ser exportados no `index.js` publico quando forem detalhes de implementacao daquele widget.
- Se um arquivo em uma pasta generica for desmembrado em multiplos arquivos do mesmo escopo, crie uma subpasta com o nome/base do widget dentro da pasta original e mova todos os arquivos relacionados para ela. Exemplo: `widgets/public/grid/PostListGrid.svelte` ao virar varios arquivos deve ficar em `widgets/public/grid/PostListGrid/PostListGrid.svelte`, junto com seus componentes internos.
- Componentes reutilizaveis devem aceitar extensao visual com `class` quando fizer sentido.
- Inputs e botoes reutilizaveis devem repassar `{...$$restProps}` quando apropriado.
- Variantes visuais devem ficar em mapas internos, como `variants`, `sizes` ou `shapes`, com fallback padrao.
- Ao criar componente/widget reutilizavel, atualize o `index.js` do diretorio quando existir.

## Organizacao Interna Svelte

- Use `<script>` primeiro, markup depois e `<style>` no fim quando existir.
- Dentro de `<script>`, organize nesta ordem:
  1. imports externos, como Svelte, Inertia e bibliotecas npm.
  2. imports internos via `@/lib/...`.
  3. props (`export let ...`).
  4. constantes, mapas e configuracoes locais.
  5. estado local (`let ...`).
  6. reatividade (`$:`).
  7. helpers puros.
  8. handlers/actions que disparam navegacao, submit, router ou alteram estado.
- Separe cada bloco com uma linha em branco: imports externos, imports internos, props, constantes, estado, reatividade, helpers e handlers/actions.
- Use `const` para valores que nao sao reatribuidos no componente, incluindo mapas, opcoes e permissao local (`const can = ...Permissions()`). Use `let` somente para estado que muda por interacao, lifecycle ou reatividade.
- Evite declarar props depois de imports internos ou misturadas com estado local.
- Em paginas Inertia, apos os imports, leia `$page.props` em um bloco reativo unico sempre que possivel.

## Estilo

- Siga mobile-first: construa primeiro a experiencia mobile como base e use breakpoints (`sm:`, `md:`, `lg:` etc.) para ampliar layout, densidade e composicao em telas maiores.
- Evite criar uma segunda versao mobile separada quando o mesmo componente puder ser responsivo com classes e estrutura adaptavel. Crie componentes separados por viewport somente quando a experiencia, interacao ou markup forem realmente diferentes.
- Use tokens existentes do Tailwind em `css/app.css`.
- Novas cores, gradientes ou filtros devem ser adicionados em `css/app.css` antes de uso.
- Use `font-noto-sans`.
- Hover/focus de item clicavel deve usar `orange-citric` no texto, indicador e icone quando houver.
- Prefira componentes existentes como `Section`, `GridList`, `Surface`, `Badge`, `Button`, `IconButton`, `Modal`, `Tooltip` e inputs existentes.
- Cards/listas clicaveis com texto devem usar `transition duration-300 ease-out`, leve `hover:-translate-y-0.5`, foco acessivel com ring e suporte a `motion-reduce`.
- Blocos que forem apenas imagem/visual devem usar somente scale leve, como `hover:scale-[1.02]`, sem brilho ou deslocamento vertical.
- Mantenha telas responsivas com classes explicitas, como `grid-cols-1`, `lg:*`, `min-w-0` e `overflow-x-clip`.

## Finalizacao

- Nao altere fora de `resources/js` sem necessidade explicita da tarefa.
- Nao coloque dado sensivel, segredo, acesso a banco ou logica server-only em `resources/js`.
