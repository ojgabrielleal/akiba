# Componente Tooltip e Integracoes

Foi registrada a criacao do componente `Tooltip` para reutilizar baloes de informacao por hover no painel privado, reduzindo duplicacao de markup e centralizando a logica de posicionamento visual.

## Alteracoes Realizadas

### 1. Componente Tooltip
- **Tooltip.svelte**: Criado componente em `resources/js/ui/components/private/Tooltip.svelte`.
- O componente usa `group/tooltip` e `group-hover/tooltip` para exibir o balao apenas com CSS.
- A estrutura possui um slot principal para o elemento que recebe hover e um slot nomeado `content` para o conteudo do balao.
- O componente foi mantido focado apenas em exibir o balao; a estilizacao do elemento interno fica no proprio elemento passado pelo slot.

### 2. Posicionamento
- Adicionada prop `position` ao `Tooltip`.
- O posicionamento padrao e `top`.
- Foram suportadas as direcoes:
  - `top`
  - `bottom`
  - `left`
  - `right`
- A seta do balao muda de orientacao conforme a direcao escolhida.

### 3. Exportacao
- O componente foi exportado em `resources/js/ui/components/private/index.js`.
- Isso permite importar `Tooltip` pelo barrel `@/ui/components/private`.

### 4. ActivityCarrousel
- Os tooltips inline dos avatares de participantes foram substituidos pelo componente `Tooltip`.
- Cada avatar agora mostra o nickname no balao ao passar o mouse.
- O indicador `+N` tambem usa `Tooltip` para exibir a lista vertical dos participantes extras.
- A lista de participantes extras permanece com um nome por linha.

### 5. Navbar Privada
- Os itens do menu desktop da navbar privada passaram a usar `Tooltip`.
- Como o menu desktop mostra apenas icones, o balao exibe o nome da secao ao passar o mouse.
- O tooltip da navbar usa `position="bottom"`.
- A versao mobile nao foi alterada, pois ja exibe icone e texto.

### 6. Validacao
- A build do frontend foi executada com `npm.cmd run build`.
- A compilacao foi concluida com sucesso.
- Permanecem apenas avisos preexistentes de acessibilidade relacionados a `aria-label=""` vazio em outros componentes.

---
**Data:** 09 de Maio de 2026
**Responsavel:** Codex (IA)
