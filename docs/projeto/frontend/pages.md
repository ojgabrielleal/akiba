---
status: ativo
tipo: guia-frontend
atualizado_em: 2026-08-03
---

# Pages

Pages são as telas renderizadas pelo Inertia. Cada arquivo em `resources/js/pages` corresponde a uma página que um controller Laravel pode abrir com `Inertia::render()`.

## Onde Ficam

```txt
resources/js/pages/private
resources/js/pages/public
resources/js/pages/provisory
```

## Contextos

| Pasta | Uso |
| --- | --- |
| `private` | Telas do painel administrativo. |
| `public` | Telas abertas do site. |
| `provisory` | Telas temporárias durante transição ou desenvolvimento. |

## Relação com Controller

O caminho usado no controller aponta para uma page:

```php
return Inertia::render('private/Post', [
    'posts' => $this->indexPosts(),
]);
```

Esse render abre:

```txt
resources/js/pages/private/Post.svelte
```

## Arquitetura da Page

```txt
<script>
    imports externos
    imports internos
    leitura de props
    estado local
    valores reativos
    funções
    actions/menus/tabs
</script>

<Meta />
<Layout>
    seções da tela
    widgets
    componentes pontuais
</Layout>
```

## Exemplo

```svelte
<script>
    import Cookies from "js-cookie";
    import { router, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import { PostForm, PostGrid } from "@/lib/widgets/private";

    $: ({ post, posts } = $page.props);

    let show = Boolean(post);
    $: form = post?.data.module ?? Cookies.get("akiba_post_module");

    function operation(module) {
        form = module;
        show ? router.visit("/panel/post") : show = true;
        Cookies.set("akiba_post_module", module);
    }
</script>

<Meta meta={{ title: "Postagens" }} />

<Layout>
    <Section title="Criar">
        {#if show}
            <PostForm {post} />
        {/if}
    </Section>

    <PostGrid title="Todas as matérias" posts={posts} />
</Layout>
```

## Responsabilidade da Page

A page pode:

- escolher o layout;
- ler props do Inertia;
- controlar estado local da tela;
- alternar abas, modos e filtros visuais;
- montar arrays de ações;
- decidir quais widgets aparecem;
- passar callbacks para widgets.

A page não deve:

- montar query;
- validar formulário no lugar do backend;
- formatar dados complexos que deveriam vir do resource;
- repetir regra de permissão sensível;
- concentrar markup grande que deveria virar widget.

## Props do Inertia

Leia as props no topo:

```svelte
$: ({ post, posts, newsFeedSources } = $page.props);
```

Trate props opcionais:

```svelte
$: canViewNewsFeed = Boolean(newsFeedSources);
```

Isso é importante porque algumas props dependem de permissões no backend.

## Checklist

- A page corresponde ao caminho usado no `Inertia::render()`?
- O layout correto foi usado?
- Props foram lidas de forma explícita?
- Widgets grandes foram extraídos?
- Estados opcionais foram tratados?
- A page não está fazendo trabalho de controller/resource?
