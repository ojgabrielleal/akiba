# Ajustes de UI no ActivityCarrousel

Foi registrada a evolucao visual do `ActivityCarrousel`, com foco em melhorar a apresentacao dos participantes confirmados, o alinhamento do cabecalho do card e o comportamento dos elementos auxiliares de hover.

## Alteracoes Realizadas

### 1. Cabecalho do Card
- O cabecalho do card passou a usar alinhamento com `flex items-center`.
- O chevron entre o nome do autor e o texto `Equipe` foi reposicionado para ficar centralizado verticalmente com o texto.
- O caminho do icone foi ajustado para `/svg/chevron-right.svg`.
- O icone foi marcado como decorativo com `aria-hidden="true"`.

### 2. Avatares de Confirmacao
- Os avatares dos participantes confirmados foram colocados dentro de um wrapper circular fixo.
- O wrapper controla tamanho, borda, fundo e `overflow-hidden`.
- A imagem interna usa `object-cover object-top`, mantendo o enquadramento sem alterar o espaco ocupado no layout.
- Foi removido o scale excessivo que deixava imagens pequenas mais pixeladas.

### 3. Tooltip dos Participantes
- O tooltip individual dos avatares continua usando `group/avatar` e `group-hover/avatar`.
- A exibicao permanece baseada em CSS, sem handlers JavaScript para hover.
- O tooltip mostra o `nickname` do participante ao passar o mouse sobre o avatar.

### 4. Participantes Extras
- Quando existem mais de 5 confirmacoes, o carrossel passa a exibir um indicador `+N`.
- O indicador mostra quantas pessoas adicionais confirmaram participacao.
- Ao passar o mouse sobre o `+N`, um balao exibe apenas os nomes dos participantes restantes.
- A lista de nomes foi organizada em formato vertical, com um nome por linha.

### 5. Validacao
- A build do frontend foi executada com `npm.cmd run build`.
- A compilacao foi concluida com sucesso.
- Permanecem apenas avisos preexistentes de acessibilidade relacionados a `aria-label=""` vazio em outros componentes.

---
**Data:** 09 de Maio de 2026
**Responsavel:** Codex (IA)
