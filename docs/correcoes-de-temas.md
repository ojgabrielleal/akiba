# Correções de Temas

## Regra de implementação

- Toda cor informada em hexadecimal neste documento deve entrar pelo utilitário de override de temas em `resources/js/lib/utils/themes.js`.
- Quando a cor precisar ser reutilizada por classes, gradientes, filtros ou imagens, criar o token, classe auxiliar ou `filter` necessário mantendo o controle pelo tema.
- As alterações de tema não devem afetar modais nem offcanvas.

## Interface pública

### Tema branco

- `blue-marinho` deve virar `#ffffff`.
- `blue-night` deve virar `#fbfdff`.
- No `editorialTitle`, manter o mesmo degradê, mudando apenas as cores, e remover qualquer personalização de tema do texto. O texto deve ficar branco fixo:
  - Cor 1: `#0091ff`
  - Cor 2: `#0059c0`
  - Cor 3: `#0091ff`
- Na frase do player, remover qualquer personalização de tema do texto. O texto deve ficar branco fixo e os trechos destacados devem continuar em laranja como no tema original.
- Nos blocos de equipe que usam degradê, usar o mesmo degradê azul do tema branco aplicado ao player/`editorialTitle`. O nome e o apelido da pessoa devem ficar em branco.
- Nos cards do destaque da Akiba, usar apenas um degradê com duas cores, mantendo o mesmo estilo e mudando apenas as cores. No tema branco, o título do card deve ficar em branco:
  - Cor 1: `#0095c0`
  - Cor 2: `#0091ff`
- Na enquete, no tema branco, o fundo deve usar o mesmo degradê dos cards de Destaques da Akiba. O título, as opções e a palavra Votos devem ficar em branco, inclusive na enquete em destaque.
- Nos componentes `section` do tema branco, os títulos e suas linhas devem ficar na cor `#000036`.
- No bloco Últimas matérias do tema branco, os títulos das matérias devem ficar na cor `#000036`, e os ícones das tags devem usar a mesma cor. Pode criar um `filter` para esses ícones.

### Calendário de eventos

- Para todos os temas, a coluna Evento deve ter cada célula com background `#ffaa35` e texto na cor `#000014`.
- Para todos os temas, as colunas Data e Local devem ter cada célula com background `#002080` e texto na cor `suspense-honeycream`.

### Frase do player e editorialTitle

- A textura/imagem deve ter um filtro para trocar a cor conforme o tema:
  - Tema branco: `#002080`
  - Tema Akiba: `#0091ff`
  - Tema dark: `#0059c0`

### Texturas de fundo

- As texturas de fundo gerais, como as estrelas atrás dos destaques e a imagem de fundo atrás dos podcasts, também devem mudar de cor conforme o tema:
  - Tema branco: `#f68000`
  - Demais temas: `#0059c0`

### Grade de programação

- Adicionar uma aba para mostrar todos os programas da grade, excluindo o Auto DJ.
- Exibir apenas o dia e o horário local da pessoa. Não mostrar a região/timezone, como `BRT - Brasília`, porque essa informação será alterada dinamicamente.

### Ranking de música

- Remover a quebra de linha dos textos longos e usar reticências quando o conteúdo ultrapassar o espaço disponível.

## Páginas especiais

### Página de login

- Remover o background/textura da página de login.

### Página 404

- Remover o background/textura da página 404.

## Painel administrativo

### Criação de enigma

- Substituir o texto de enigma por upload/adição de uma imagem.
