# PageControls e CastMetrics Hotfix

Foi registrado o ajuste final do `PageControls` para carregamento incremental controlado no frontend, sem `Inertia::deepMerge`, e a correcao do `CastMetricsGrid` para tolerar ausencia temporaria dos dados de streaming.

## Alteracoes Realizadas

### 1. Backend de Tasks
- `DashboardController@indexTasks` passou a usar `paginate(5, ['*'], 'tasks')`.
- A query das tasks manteve ordenacao por:
  - `dead_line`
  - `created_at desc`
- A prop `tasks` voltou a ser retornada diretamente no render do dashboard.
- O uso de `Inertia::deepMerge` foi removido para evitar merge automatico e comportamento instavel em reloads parciais.

### 2. PageControls
- O componente `PageControls.svelte` foi simplificado.
- Foram removidas props e variaveis que nao eram mais necessarias:
  - `enabled`
  - `label`
  - `resetLabel`
  - `showResetWhenFinished`
  - derivadas intermediarias como `appendProp`, `useInfiniteObserver` e `hasNumberedPagination`
- O componente manteve os modos:
  - `pagination`
  - `button`
  - `infinite`

### 3. Carregamento Incremental sem Deep Merge
- O modo `button` passou a anexar novas paginas no frontend usando `router.replaceProp`.
- O modo `infinite` usa a mesma logica de append quando o sentinela entra na viewport.
- A prop `only` define qual propriedade do Inertia deve ser acumulada.
- Exemplo de uso para tasks:
  - `mode="button"`
  - `only={["tasks"]}`
  - `pageName="tasks"`
  - `preserveUrl`
- O carregamento agora funciona como:
  - 5 itens iniciais
  - carregar mais -> 10 itens
  - carregar mais -> 15 itens

### 4. Mostrar Menos
- Foi adicionado controle de historico local com `pageHistory`.
- O botao de mostrar menos remove apenas a ultima pagina carregada, sem voltar diretamente para a primeira pagina.
- O comportamento ficou:
  - 15 itens -> mostrar menos -> 10 itens
  - 10 itens -> mostrar menos -> 5 itens
- O estado acumulado e reconstruido a partir das paginas restantes no historico.

### 5. Icones e Tooltip
- O botao de carregar mais deixou de usar texto e borda.
- O controle passou a usar apenas icones laranja:
  - `chevron-down.svg` para mostrar mais
  - `chevron-up.svg` para mostrar menos
- Foram adicionados tooltips usando o componente `Tooltip.svelte`:
  - `Mostrar mais`
  - `Mostrar menos`
- Os botoes mantem `aria-label` com os mesmos textos dos tooltips.

### 6. TaskCarrousel
- O `TaskCarrousel` passou a usar `PageControls` em modo `button`.
- A lista de tasks agora carrega novos itens abaixo dos existentes sem depender de merge no backend.
- A URL visivel permanece limpa com `preserveUrl`.

### 7. CastMetricsGrid
- `CastMetricsGrid.svelte` passou a tolerar `streaming` nulo.
- Foi adicionado fallback local:
  - `cast = streaming ?? {}`
- As metricas agora exibem `N/A` quando a API externa de streaming falha ou retorna dados ausentes.
- Isso evita erro do tipo `Cannot read properties of null` durante o `usePoll` do layout privado.

### 8. Validacao
- `php -l app\Http\Controllers\Private\DashboardController.php` foi executado sem erros.
- `npm.cmd run build` foi executado com sucesso.
- Permanecem apenas avisos preexistentes de acessibilidade relacionados a `aria-label=""` vazio em outros componentes do projeto.

---
**Data:** 09 de Maio de 2026  
**Responsavel:** Codex (IA)
