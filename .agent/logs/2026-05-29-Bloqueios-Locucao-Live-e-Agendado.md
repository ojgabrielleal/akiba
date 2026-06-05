# Bloqueios Locucao Live e Agendado

Foi ajustada a tela de locucao para bloquear corretamente a abertura de um novo programa quando ja existe um programa ao vivo ou agendado em andamento.

## Alteracoes Realizadas

### 1. Remocao da Regra Antiga com Automatic
- A condicao antiga do bloqueio visual do formulario usava `automatic` junto com `scheduled`.
- A regra foi substituida por `isLocutionFormBlocked`, centralizando os tipos bloqueados em `["live", "playlist", "scheduled"]`.
- As classes `opacity-50` e `pointer-events-none` passaram a usar essa variavel reativa.

### 2. Modal para Programa ao Vivo
- Foi redesenhado o modal exibido quando existe um programa `live` de outro locutor.
- O modal passou a seguir melhor a identidade visual do sistema, usando `blue-ocean`, `blue-marinho`, `red-crimson`, `orange-citric` e `suspense-aurora`.
- O modal exibe o avatar do locutor, o badge `Ao vivo`, o nome do programa e o locutor com prefixo `DJ`.
- O texto foi ajustado para explicar que nao e possivel comecar um novo programa enquanto o DJ atual esta ao vivo.
- Foi adicionada uma dica visual com lampada indicando quando a acao volta a ficar disponivel.

### 3. Modal para Programa Agendado
- Foi criado um modal especifico para `scheduled`.
- Essa variante usa icone de calendario, badge `Agendado`, nome do programa e contexto de agenda da programacao.
- O texto diferencia o bloqueio de programa agendado do bloqueio de programa ao vivo.

### 4. Ajustes de Acessibilidade e Marcacao
- Foi importado `fade` de `svelte/transition`, ja que o modal usa `transition:fade`.
- O wrapper do modal foi ajustado para `div` com `role="dialog"`, `aria-modal`, `aria-labelledby` e `tabindex="-1"`.

## Validacao

- Foi revisado o diff de `resources/js/pages/private/Locution.svelte`.
- A tentativa de build com `npm.cmd run build` nao concluiu por um problema ja existente: o Vite nao encontrou `resources/css/app.css`.

---
**Data:** 29 de Maio de 2026  
**Responsavel:** Codex (IA)
