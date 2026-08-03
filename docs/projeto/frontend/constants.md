---
status: ativo
tipo: guia-frontend
atualizado_em: 2026-08-03
---

# Constants

Constants guardam listas e configurações estáticas usadas pela interface.

## Onde Ficam

```txt
resources/js/lib/constants
```

Áreas atuais:

```txt
calendar
default
locution
post
team
user
```

## Exemplos

```txt
post/tag.json
post/reaction.json
calendar/tag.json
team/social.json
user/preference.json
default/navbar.json
```

## Quando Usar

Use constants para:

- opções de select;
- tags visuais;
- reações;
- ícones fixos;
- texturas;
- links de navbar;
- preferências conhecidas pelo frontend.

## Exemplo

```js
import { postTags } from "@/lib/constants";
```

```svelte
{#each Object.values(postTags) as item}
    <option value={item.value}>
        {item.label}
    </option>
{/each}
```

## Regras

- Se o valor muda pelo painel ou banco, não deve ser constant.
- Se é configuração fixa de interface, pode ser constant.
- Agrupe por domínio.
- Exporte pelo `index.js` quando for usado fora da pasta.

## Checklist

- O dado é realmente estático?
- O arquivo está no domínio correto?
- O nome do export é claro?
- O formato é simples para uso em Svelte?
