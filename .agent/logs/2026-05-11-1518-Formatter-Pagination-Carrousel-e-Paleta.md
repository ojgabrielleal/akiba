# Formatter, Pagination, Carrousel e Paleta

Foi registrada uma rodada de ajustes de manutencao no frontend, cobrindo a evolucao do formatador customizado, a renomeacao do componente de paginacao, pequenos refinamentos visuais em carrossel/paginacao e a organizacao de cores na paleta CSS.

## Alteracoes Realizadas

### 1. Formatador Frontend
- O antigo script `resources/js/scripts/format-svelte.js` foi renomeado para `resources/js/scripts/format-frontend.js`.
- O comando `format:frontend` foi adicionado ao `package.json`.
- O comando `format:svelte` foi mantido como alias para preservar compatibilidade com chamadas antigas.
- O formatador passou a recompor chamadas `axios.get/post/put/patch/delete(...)` quando elas forem quebradas no formato:
  - `axios`
  - `.get(...)`
- Foi corrigida a normalizacao de objetos em `class={[...]}` para lidar corretamente com classes Tailwind que possuem `:`, como `md:w-52`.
- A correcao evita que chaves como `"md:w-52 grid-cols-[1fr_1fr]"` sejam quebradas no primeiro `:`.

### 2. Pagination
- O componente `PageControls.svelte` foi renomeado para `Pagination.svelte`.
- O barrel `resources/js/ui/components/private/index.js` passou a exportar `Pagination`.
- Os imports e usos de `PageControls` foram atualizados para `Pagination` em grids, tabelas e carrosseis privados.
- As setas de `Mostrar mais` e `Mostrar menos` foram reduzidas visualmente.
- Foi adicionada animacao sutil nas setas:
  - a seta para baixo pulsa levemente para baixo, indicando que ha mais conteudo;
  - a seta para cima pulsa levemente para cima, indicando a acao de reduzir a lista.

### 3. Carrousel
- As setas do componente `resources/js/ui/components/private/Carrousel.svelte` foram ajustadas para usar tamanho explicito.
- O icone deixou de depender de largura exagerada/arbitraria e passou a usar `w-9 h-9 xl:w-12 xl:h-12`.
- Foi aplicado `flex-none` para impedir que o tamanho do icone seja controlado pelo comportamento flex do botao.

### 4. Paleta e Gradientes
- Os gradientes azuis passaram a usar `color-mix()` com `var(--color-blue-cerulean)` em vez de `rgba(0, 89, 192, ...)`.
- O `--gradient-red-primary` passou a usar variaveis da paleta.
- A cor `#ed3237` foi reaproveitada como `--color-red-crimson`.
- A cor `#570300` foi cadastrada como `--color-red-blood`.
- `--color-red-blood` tambem foi registrado no `@theme`, permitindo uso pelas utilities do Tailwind.

### 5. Validacao
- O formatador foi executado com `npm.cmd run format:frontend`.
- A execucao foi concluida com sucesso.
- O script `format-frontend.js` foi validado com `node --check`.

---
**Data:** 11 de Maio de 2026  
**Responsavel:** Codex (IA)
