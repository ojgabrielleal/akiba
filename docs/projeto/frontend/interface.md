---
status: ativo
tipo: guia-frontend
atualizado_em: 2026-08-03
---

# Interface

A interface usa Svelte com Inertia. O backend renderiza uma página e envia props; a página Svelte organiza a tela com layouts, componentes e widgets.

## Onde Fica

```txt
resources/js/pages
resources/js/lib
```

## Mapa da Interface

```txt
resources/js/pages
    private       páginas do painel
    public        páginas públicas
    provisory     páginas temporárias

resources/js/lib/layouts
    private       layout do painel
    public        layout público

resources/js/lib/components
    private       componentes pequenos do painel
    public        componentes pequenos públicos
    shared        componentes usados nos dois contextos

resources/js/lib/widgets
    private       blocos grandes do painel
    public        blocos grandes públicos

resources/js/lib/stores
    estado global compartilhado

resources/js/lib/utils
    funções auxiliares

resources/js/lib/constants
    listas e configurações estáticas
```

## Fluxo Inertia

O fluxo padrão é:

```txt
Controller Laravel
  -> Inertia::render('private/Post', props)
     -> resources/js/pages/private/Post.svelte
        -> layouts
        -> widgets
        -> components
```

A página Svelte não busca os dados iniciais por conta própria. Ela recebe as props montadas pelo controller e formatadas pelos resources.

## Separação por Tamanho

| Tipo | Exemplo | Responsabilidade |
| --- | --- | --- |
| Page | `pages/private/Post.svelte` | Orquestra a tela inteira. |
| Layout | `layouts/private/Layout.svelte` | Estrutura global da área. |
| Widget | `widgets/private/form/PostForm.svelte` | Bloco grande de domínio. |
| Component | `components/private/forms/TextInput.svelte` | Peça pequena reutilizável. |
| Store | `stores/playerStore.js` | Estado compartilhado. |
| Utils | `utils/presentation/gridStatus.js` | Função auxiliar reutilizável. |
| Constants | `constants/post/tag.json` | Opções estáticas de interface. |

## Páginas

Páginas são arquivos renderizados diretamente pelo Inertia.

```txt
resources/js/pages/private/Post.svelte
resources/js/pages/public/Home.svelte
resources/js/pages/public/Radio.svelte
```

Arquitetura esperada:

```txt
<script>
    imports externos
    imports de layout/componentes/widgets
    leitura de props do Inertia
    estados locais
    valores reativos
    funções de evento
    actions/menus
</script>

<Meta />
<Layout>
    conteúdo da página
</Layout>
```

## Ordem Interna da Página

Dentro do `<script>`, prefira esta ordem:

1. imports externos;
2. imports internos com alias `@/`;
3. leitura de props com `$page.props`;
4. estados locais com `let`;
5. declarações reativas `$:`;
6. funções de evento;
7. arrays de actions, menus ou tabs.

Isso facilita bater o olho e entender o que a página recebe, o que ela controla e quais eventos dispara.

Exemplo:

```svelte
<script>
    import { page, router } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import { PostForm, PostGrid } from "@/lib/widgets/private";

    $: ({ post, posts } = $page.props);

    let show = Boolean(post);
    $: title = post ? "Editar matéria" : "Criar matéria";

    function openCreate() {
        show = true;
        router.visit("/panel/post");
    }
</script>

<Meta meta={{ title }} />

<Layout>
    <Section title={title}>
        {#if show}
            <PostForm {post} />
        {/if}
    </Section>

    <PostGrid posts={posts} />
</Layout>
```

## Props

Props vindas do backend devem ser lidas de forma explícita:

```svelte
$: ({ post, posts, permissions } = $page.props);
```

Quando uma prop só existe com determinada permissão, trate o estado vazio:

```svelte
$: canViewReports = Boolean(reports);
```

Evite acessar campos profundamente sem checar se a prop existe quando ela depende de permissão ou filtro.

## Layouts

Layouts definem a estrutura comum da página.

```txt
resources/js/lib/layouts/private/Layout.svelte
resources/js/lib/layouts/public/Layout.svelte
```

Use layout privado para painel e layout público para páginas abertas. A página deve cuidar do conteúdo; o layout cuida de navegação, estrutura, áreas comuns e moldura visual.

## Components

Componentes são peças menores e reutilizáveis.

```txt
resources/js/lib/components/shared
resources/js/lib/components/private
resources/js/lib/components/public
```

Use para botões, campos, seções, modal, meta tags e elementos genéricos.

Componentes devem receber dados por props e emitir eventos quando necessário. Evite componente genérico chamando rota diretamente se ele puder receber uma função de callback.

## Widgets

Widgets são blocos maiores de domínio.

```txt
resources/js/lib/widgets/private
resources/js/lib/widgets/public
```

Use para grids, formulários completos, cards de módulos e blocos que conhecem uma área do sistema.

Widgets podem conhecer endpoints, formatos de dados de um módulo e regras visuais específicas daquele domínio. Exemplo: um `PostGrid` pode conhecer a estrutura de `PostResource::format('grid')`.

## Estados de Tela

Toda página de painel normalmente precisa pensar em:

- estado vazio;
- carregamento ou submissão;
- erro de validação vindo do backend;
- item selecionado;
- modo criar/editar;
- permissões que escondem blocos da tela.

## Páginas Privadas

Páginas privadas usam o layout do painel e normalmente trabalham com widgets de grid, form, list, table, chart ou carousel.

Exemplos:

```txt
Dashboard.svelte
Post.svelte
Radio.svelte
Locution.svelte
Administration.svelte
Reports.svelte
```

## Páginas Públicas

Páginas públicas usam layout público e widgets voltados para leitura, player, navegação e formulários abertos.

Exemplos:

```txt
Home.svelte
Radio.svelte
ReadPost.svelte
ReadReview.svelte
Search.svelte
Team.svelte
```

## Arquivos de Barril

O projeto usa `index.js` para facilitar imports:

```js
import { Section, TextInput } from "@/lib/components/private";
import { PostForm, PostGrid } from "@/lib/widgets/private";
```

Ao criar componente ou widget novo que será importado de fora da pasta, exporte no `index.js` correspondente.

## O Que Evitar

- Duplicar regra de permissão que já veio do backend.
- Fazer fetch inicial manual quando a página já é Inertia.
- Misturar muitos formulários grandes direto na página.
- Criar widget que serve para tudo e fica difícil de manter.
- Usar estado global quando estado local resolve.
- Importar arquivo profundo quando já existe export no `index.js`.

## Checklist

- A página recebe dados pelo Inertia?
- Dados já vêm formatados por resource?
- Layout certo foi usado?
- Componentes genéricos ficaram em `components`?
- Blocos de domínio ficaram em `widgets`?
- Estado local pertence mesmo à tela?
