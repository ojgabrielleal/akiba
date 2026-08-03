---
status: ativo
tipo: guia-frontend
atualizado_em: 2026-08-03
---

# Forms

Formulários grandes ficam como widgets de domínio em `resources/js/lib/widgets/*/form`. Campos pequenos ficam em `resources/js/lib/components/*/forms`.

## Onde Ficam

```txt
resources/js/lib/widgets/private/form
resources/js/lib/widgets/public/form
resources/js/lib/components/private/forms
resources/js/lib/components/public/forms
```

## Componentes de Campo

Exemplos:

```txt
FormField
TextInput
TextArea
SelectInput
RadioInput
CheckboxInput
Preview
Wysiwyg
```

Use esses componentes para padronizar label, erro, espaçamento, variantes visuais e acessibilidade.

## Formulários de Domínio

Exemplos:

```txt
PostForm
ReviewForm
EventForm
PodcastForm
ProgramForm
TaskForm
UserForm
SongRequestForm
RecruitmentForm
```

Eles conhecem os campos, endpoint e estrutura de dados do domínio.

## Padrão com `useForm`

```svelte
<script>
    import { useForm } from "@inertiajs/svelte";
    import { FormField, TextInput } from "@/lib/components/private";

    export let post = null;

    $: form = useForm({
        _method: post ? "PATCH" : "POST",
        title: post?.data.title ?? null,
        content: post?.data.content ?? null,
    });

    function submit() {
        const url = post ? `/panel/post/${post.data.uuid}` : "/panel/post";

        $form.post(url, {
            preserveState: false,
            onSuccess: () => post ? null : $form.reset(),
        });
    }
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="title" label="Título" error={$form.errors.title}>
        <TextInput id="title" bind:value={$form.title} error={$form.errors.title} />
    </FormField>
</form>
```

## Erros de Validação

Os erros vêm do Laravel pelo Inertia:

```svelte
error={$form.errors.title}
```

Para arrays:

```svelte
error={$form.errors["references.0.url"]}
```

## Uploads

Quando houver arquivo, use `forceFormData`:

```js
$form.post(url, {
    forceFormData: true,
});
```

## Criação vs Edição

Use `_method` para edição quando o envio passa por `post()`:

```js
_method: model ? "PATCH" : "POST"
```

E monte a URL conforme o estado:

```js
const url = model ? `/panel/resource/${model.data.uuid}` : "/panel/resource";
```

## Checklist

- O formulário usa `useForm()`?
- Campos iniciais consideram criação e edição?
- Erros do backend aparecem no campo certo?
- Upload usa `forceFormData`?
- Arrays têm fallback para evitar `undefined`?
- O submit chama endpoint do domínio, não lógica manual espalhada?
