# Componente Carrousel Privado

Foi registrada a criacao do componente base `Carrousel` para padronizar a navegacao horizontal dos carrosseis privados, substituindo a logica anterior de scroll manual por uma interface reutilizavel com setas em telas maiores e rolagem nativa no mobile.

## Alteracoes Realizadas

### 1. Componente Base
- **Carrousel.svelte**: Criado componente em `resources/js/ui/components/private/Carrousel.svelte`.
- O componente centraliza a estrutura de carrossel horizontal usada pelos widgets privados.
- A navegacao por botoes usa `container.scrollBy()` com comportamento suave.
- O estado interno controla se existe conteudo disponivel para navegar a esquerda ou a direita.

### 2. Padronizacao de Nomenclatura
- O antigo conceito de `Scroll` foi renomeado para `Carrousel`, alinhando o nome do componente com a pasta `carrousel` dos widgets.
- As funcoes e estados internos tambem passaram a usar a nomenclatura de carrossel:
  - `canCarrouselLeft`
  - `canCarrouselRight`
  - `updateCarrouselState`
  - `moveCarrousel`
- O export em `resources/js/ui/components/private/index.js` passou a disponibilizar `Carrousel`.

### 3. Integracao com Widgets
- Os componentes privados de carrossel passaram a importar e renderizar `<Carrousel>`:
  - `ActivityCarrousel.svelte`
  - `AudienceCarrousel.svelte`
  - `ProgramCarrousel.svelte`
  - `TaskCarrousel.svelte`
- O `ActivityCarrousel` foi ajustado para usar o componente base em vez de manter a estrutura local de rolagem horizontal.

### 4. Responsividade
- Em telas `md+`, as setas aparecem apenas quando existe overflow horizontal para navegar.
- No inicio do carrossel, a seta esquerda fica oculta; no final, a seta direita fica oculta.
- No mobile, os botoes ficam escondidos com `hidden md:flex`.
- A navegacao mobile fica por conta do scroll/arraste horizontal nativo, preservando o comportamento esperado em toque.

### 5. Interacao
- A rolagem horizontal via wheel do mouse foi removida do componente.
- Em desktop, a navegacao principal do carrossel ficou concentrada nas setas.
- Os botoes usam os icones `chevron-left.svg` e `chevron-right.svg`, mantendo a identidade visual do painel.

### 6. Validacao
- A build do frontend foi executada com `npm.cmd run build`.
- A compilacao foi concluida com sucesso.
- Permanecem apenas avisos preexistentes de acessibilidade relacionados a `aria-label=""` vazio em outros componentes.

---
**Data:** 08 de Maio de 2026
**Responsavel:** Codex (IA)
