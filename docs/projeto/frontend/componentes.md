---
status: ativo
tipo: guia-frontend
atualizado_em: 2026-08-03
---

# Componentes e Widgets

Esta página separa o que deve virar componente pequeno e o que deve virar widget de domínio.

## Pastas

```txt
resources/js/lib/components/private
resources/js/lib/components/public
resources/js/lib/components/shared
resources/js/lib/widgets/private
resources/js/lib/widgets/public
```

Use `private` para painel, `public` para site aberto e `shared` quando o componente realmente serve para os dois contextos.

## Componentes

Use `components` para peças pequenas, reutilizáveis e pouco acopladas ao domínio.

Exemplos:

```txt
Button
Input
Modal
Section
Meta
EmptyState
Pagination
```

Categorias atuais:

```txt
actions       botões e ações clicáveis
feedback      badges, loading, empty state, toaster
forms         campos e wrappers de formulário
layout        section, grid, divisores e superfícies
navigation    tabs, carousel, pagination
overlays      modal, offcanvas, tooltip
```

Arquitetura:

```txt
<script>
    imports
    export let para props públicas
    estados locais simples
    dispatch/events quando necessário
    funções auxiliares
</script>

markup
```

## Props

Prefira props explícitas:

```svelte
<script>
    export let title;
    export let disabled = false;
    export let loading = false;
</script>
```

Evite componente que depende de `$page.props` se ele pode receber os dados da página ou widget.

## Eventos

Para ação externa, o componente pode receber callback ou disparar evento.

Callback:

```svelte
<script>
    export let onClick = () => {};
</script>

<button on:click={onClick}>Salvar</button>
```

Evento:

```svelte
<script>
    import { createEventDispatcher } from "svelte";

    const dispatch = createEventDispatcher();
</script>

<button on:click={() => dispatch("save")}>Salvar</button>
```

## Widgets

Use `widgets` para blocos maiores, ligados a uma tela ou módulo.

Exemplos:

```txt
PostForm
PostGrid
AnimeNewsFeedGrid
ProgramForm
TaskGrid
```

Categorias atuais:

```txt
carousel
chart
form
grid
hero
list
navbar
table
player
read
team
footer
```

Um widget pode usar vários componentes pequenos por dentro.

## Estrutura de Widget

```txt
<script>
    imports de componentes
    props do domínio
    estado local do bloco
    handlers de formulário, seleção ou ação
</script>

markup do bloco completo
```

Exemplo:

```svelte
<script>
    import { router } from "@inertiajs/svelte";
    import PostCard from "./PostCard.svelte";

    export let posts;

    function openPost(post) {
        router.visit(`/panel/post/${post.uuid}`);
    }
</script>

<div>
    {#each posts.data as post}
        <PostCard {post} onOpen={() => openPost(post)} />
    {/each}
</div>
```

## Regra Prática

Se o arquivo responde “como esse campo aparece?”, provavelmente é componente.

Se o arquivo responde “como essa parte do módulo funciona?”, provavelmente é widget.

## Quando Extrair

Extraia para componente quando:

- o markup se repete;
- o estilo precisa ser padronizado;
- o comportamento é genérico;
- a página está ficando difícil de ler.

Extraia para widget quando:

- o bloco conhece dados de um resource;
- o bloco tem ações de domínio;
- o bloco tem vários componentes internos;
- a mesma parte aparece em mais de uma tela do módulo.

## Nome de Arquivo

Use nomes com substantivo claro:

```txt
PostGrid.svelte
PostForm.svelte
SongRequestGrid.svelte
StreamMetricsGrid.svelte
ThemeSwitcher.svelte
```

Evite nomes genéricos para domínio, como:

```txt
List.svelte
Form.svelte
Card.svelte
```

Esses nomes só fazem sentido dentro de pastas muito específicas.

## O Que Evitar

- Componente pequeno importando regra específica de módulo.
- Widget com muitas responsabilidades diferentes.
- Página gigante porque nada foi extraído.
- Props implícitas ou dependência escondida em store global.
- Componente reutilizável chamando rota fixa sem necessidade.

## Checklist

- O arquivo tem responsabilidade clara?
- Props públicas estão no topo?
- Eventos/callbacks são previsíveis?
- Componentes pequenos estão em `components`?
- Blocos de domínio estão em `widgets`?
