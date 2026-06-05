# PageControls e Carregamento Incremental

Foi registrada a evolucao do antigo componente de paginacao para um controle reutilizavel, capaz de trabalhar com paginacao tradicional, botao de carregar mais e scroll infinito.

## Alteracoes Realizadas

### 1. Renomeacao do Componente
- O componente `Pagination.svelte` foi renomeado para `PageControls.svelte`.
- O barrel `resources/js/ui/components/private/index.js` passou a exportar `PageControls`.
- Os usos antigos de `Pagination` foram atualizados para `PageControls` em grids e tabelas privadas.

### 2. Modos de Uso
- O `PageControls` manteve o modo padrao `pagination` para compatibilidade com telas existentes.
- Foi adicionado o modo `button` para exibir um botao de carregamento incremental.
- Foi adicionado o modo `infinite` usando `IntersectionObserver` para carregar novos itens quando o sentinela entra na viewport.
- Foram adicionadas props para personalizar comportamento e texto:
  - `mode`
  - `label`
  - `loadingLabel`
  - `resetLabel`
  - `rootMargin`
  - `enabled`
  - `only`
  - `pageName`
  - `showResetWhenFinished`

### 3. Integracao com Inertia
- O componente passou a usar `router.visit` quando existe `next_page_url` ou links de paginacao.
- As visitas preservam scroll e estado.
- A prop `only` permite fazer reload parcial de propriedades especificas, como `tasks`.
- O reset usa `reset: only` para limpar o merge anterior quando o usuario clica em `Exibir menos`.

### 4. Cursor Pagination em Tasks
- `DashboardController@indexTasks` passou a usar `cursorPaginate`.
- A query ganhou ordenacao por `dead_line` e `id` para manter cursor pagination estavel.
- A paginacao das tasks usa o nome de cursor `tasks`.
- A prop `tasks` no render do dashboard passou a usar `Inertia::deepMerge`, permitindo que novas paginas sejam anexadas em `tasks.data` em vez de substituir o conteudo existente.

### 5. TaskCarrousel
- O `TaskCarrousel` passou a importar `PageControls`.
- O controle foi adicionado abaixo da lista de tasks com:
  - `mode="button"`
  - `only={["tasks"]}`
  - `pageName="tasks"`
  - `showResetWhenFinished`
- Ao carregar mais, os novos cards sao adicionados abaixo dos existentes.
- Ao chegar no fim, o botao muda para `Exibir menos`, retornando para a primeira leva de tasks.

### 6. Estilo e Icones
- Os controles clicaveis do `PageControls` foram padronizados com `orange-citric`.
- O botao usa borda, texto e hover discretos para nao competir com os cards.
- Foram adicionados icones ja existentes em `public/svg`:
  - `plus.svg` para `Carregar mais`
  - `return.svg` para `Exibir menos`
  - `chevron-left.svg` para voltar pagina
  - `chevron-right.svg` para proxima pagina
- Os icones usam `filter-orange-citric`.

### 7. Validacao
- A build do frontend foi executada com `npm.cmd run build`.
- A compilacao foi concluida com sucesso.
- Permanecem apenas avisos preexistentes de acessibilidade relacionados a `aria-label=""` vazio em outros componentes.

---
**Data:** 09 de Maio de 2026  
**Responsavel:** Codex (IA)
