# Mapa guiado dos temas publicos

Este arquivo serve para preencher as cores dos temas publicos do site Akiba.

Ele foi feito para dois usos:

1. Uma pessoa leiga preencher sem precisar entender codigo.
2. Uma IA usar como roteiro, perguntando uma coisa por vez ate completar tudo.

## Como usar se voce for uma pessoa

Para cada item, responda com uma destas opcoes:

- `manter`: se quiser deixar igual ao tema atual.
- Uma cor simples: exemplo `azul escuro`, `branco quente`, `laranja vibrante`.
- Uma cor em hexadecimal: exemplo `#000014`, `#ffaa35`, `#fffaf3`.
- Uma explicacao curta: exemplo `quero mais claro, mas ainda com cara de anime`.
- `nao sei`: se quiser que a IA ou o designer sugira.

Voce nao precisa saber nomes tecnicos. Pode escrever do seu jeito.

Exemplos:

```txt
Fundo principal do tema claro: branco quente
Fundo principal do tema Akiba: manter
Fundo principal do tema escuro: preto azulado
Observacao: quero que continue com cara de radio/anime, nao corporativo.
```

## Como usar se voce for uma IA

Voce deve ajudar uma pessoa leiga a preencher este arquivo.

Regras para conduzir:

- Comece com uma mensagem curta de boas-vindas e, na mesma resposta, ja faca a primeira pergunta do formulario.
- Nao pergunte se a pessoa quer ajuda.
- Faca uma pergunta por vez.
- Nao junte varias perguntas na mesma mensagem.
- Nao pergunte se os temas existem. Os tres temas ja vao existir: Claro, Akiba e Escuro.
- Os nomes padrao dos temas sao Claro, Akiba e Escuro, mas o design pode personalizar o nome que aparece no site.
- Depois que a pessoa definir os nomes que aparecem no site, use esses nomes nas proximas perguntas em vez de repetir Claro, Akiba e Escuro.
- Mantenha `Claro`, `Akiba` e `Escuro` como nomes internos dos campos no arquivo, mas na conversa use os nomes personalizados escolhidos pela pessoa.
- Se a pessoa responder algo vago, transforme em uma sugestao clara.
- Sempre confirme antes de fechar uma secao, tambem com uma pergunta por vez.
- Se a pessoa disser `nao sei`, sugira uma opcao coerente com o site Akiba.
- Preserve a identidade: radio, anime, comunidade otaku, energia jovem e visual publico.
- Nao altere layout, tamanho, fonte, espacamento ou posicao. Este arquivo trata apenas de cores, fundos, bordas, sombras e icones.
- No fim, devolva este mesmo arquivo em Markdown, mantendo a estrutura original e preenchendo todos os campos `Resposta:`, `Claro:`, `Akiba:` e `Escuro:` com as escolhas finais da pessoa. Os campos `Claro`, `Akiba` e `Escuro` continuam sendo os nomes internos do arquivo.
- Tente gerar ou anexar o arquivo Markdown respondido, se a plataforma permitir.
- Se nao conseguir gerar ou anexar o arquivo, cole o conteudo completo do arquivo respondido na conversa para a pessoa copiar e salvar manualmente.
- Nesse caso, explique de forma simples: "Nao consegui gerar um arquivo anexado aqui. Copie o conteudo abaixo, cole em um arquivo `.md` e envie o briefing para o dev Suzuh."
- Nao entregue apenas um resumo. A entrega final deve ser o arquivo completo ja respondido, pronto para ser enviado para o dev Suzuh.

Mensagem inicial obrigatoria:

```txt
Oi! Este e um briefing para definir os temas publicos do site Akiba. Vou te fazer uma pergunta por vez e preencher suas respostas no arquivo.

No final, eu vou tentar gerar o mesmo briefing completo em Markdown, ja respondido. Se eu nao conseguir gerar um arquivo anexado aqui, vou colocar o conteudo completo na conversa para voce copiar e salvar manualmente. Quando terminar, envie esse briefing preenchido para o dev Suzuh.

Parte 1: Identidade dos temas.
Qual nome deve aparecer no site para o tema Claro?
```

## Resumo rapido do que cada coisa significa

- Fundo principal: fundo do player, navbar e cabecalho das grandes paginas.
- Fundo secundario: fundo da parte de baixo da Home e areas atras de secoes/listas.
- Ativo/clicavel: botoes, links, cards clicaveis e acoes.
- Exibicao/nao clicavel: numeros, likes, tags e destaques informativos.
- Hover/foco: cor quando passa o mouse ou navega pelo teclado.
- Bordas suaves: linhas em cards, paineis, comentarios e avisos.
- Icones: cor dos icones em navbar, cards, botoes, tags e player.

## Paleta atual do projeto

Use esta tabela como referencia. Pode manter ou trocar.

| Nome simples | Cor atual | Uso atual |
| --- | --- | --- |
| Azul noite | `#000014` | Fundo principal escuro |
| Azul marinho | `#000036` | Fundo secundario escuro |
| Azul ocean | `#002080` | Cards, blocos e superficies azuis |
| Azul vivo | `#0091ff` | Detalhes azuis brilhantes |
| Branco quente | `#fffaf3` | Texto claro e fundos claros quentes |
| Laranja citrico | `#ffaa35` | Acoes clicaveis |
| Laranja forte | `#ff8000` | Numeros, likes e exibicao |
| Laranja claro | `#ffd29f` | Apoio visual e destaques suaves |

# Parte 1: Identidade dos temas

Os tres temas vao existir. Os nomes abaixo sao os nomes padrao, mas podem ser personalizados pelo design:

- Claro
- Akiba
- Escuro

## Tema claro

Nome que aparece no site:
Resposta:

## Tema Akiba

Nome que aparece no site:
Resposta:

## Tema escuro

Nome que aparece no site:
Resposta:

## Tema que abre primeiro

Escolha um:

- [ ] Claro
- [ ] Akiba
- [ ] Escuro

Observacao:
Resposta:

# Parte 2: Cores principais

Preencha primeiro esta parte. Ela resolve a maior parte das lacunas do site.

## Fundo principal

Onde aparece:
Fundo do player, navbar e cabecalho das grandes paginas.

Base atual:
Azul noite `#000014`.

Tema claro:
Resposta:

Tema Akiba:
Resposta:

Tema escuro:
Resposta:

## Fundo secundario

Onde aparece:
Parte de baixo da Home, fundo das secoes e listas.

Base atual:
Azul marinho `#000036`.

Tema claro:
Resposta:

Tema Akiba:
Resposta:

Tema escuro:
Resposta:

## Acoes clicaveis

Onde aparece:
Botoes, links importantes, cards clicaveis e acoes.

Base atual:
Laranja citrico `#ffaa35`.

Tema claro:
Resposta:

Tema Akiba:
Resposta:

Tema escuro:
Resposta:

## Informacoes em destaque

Onde aparece:
Numeros, likes, tags e destaques que nao sao botoes.

Base atual:
Laranja forte `#ff8000`.

Tema claro:
Resposta:

Tema Akiba:
Resposta:

Tema escuro:
Resposta:

## Hover e foco

Onde aparece:
Quando passa o mouse, clica, seleciona ou navega pelo teclado.

Base atual:
Laranja citrico `#ffaa35`.

Tema claro:
Resposta:

Tema Akiba:
Resposta:

Tema escuro:
Resposta:

## Textos principais

Onde aparece:
Titulos, paragrafos, menus e conteudos importantes.

Base atual:
Branco quente `#fffaf3` em fundos escuros.

Tema claro:
Resposta:

Tema Akiba:
Resposta:

Tema escuro:
Resposta:

## Bordas e separadores

Onde aparece:
Cards, comentarios, caixas, avisos e divisorias.

Base atual:
Branco quente ou azul escuro com baixa opacidade.

Tema claro:
Resposta:

Tema Akiba:
Resposta:

Tema escuro:
Resposta:

## Icones

Onde aparece:
Navbar, player, botoes, tags, cards e rodape.

Base atual:
Branco quente em fundos escuros; azul escuro em fundos claros.

Tema claro:
Resposta:

Tema Akiba:
Resposta:

Tema escuro:
Resposta:

# Parte 3: Home

## Fundo da Home

Fundo do player principal:
Claro:
Akiba:
Escuro:

Fundo das secoes:
Claro:
Akiba:
Escuro:

Fundo do rodape:
Claro:
Akiba:
Escuro:

Fundo do player fixo inferior:
Claro:
Akiba:
Escuro:

Texturas decorativas:
Claro:
Akiba:
Escuro:

## Player principal

Faixa com frase:
Claro:
Akiba:
Escuro:

Texto da frase:
Claro:
Akiba:
Escuro:

Destaque da frase:
Claro:
Akiba:
Escuro:

Botoes e icones do player:
Claro:
Akiba:
Escuro:

Volume e controles:
Claro:
Akiba:
Escuro:

## Secoes da Home

Titulos das secoes:
Claro:
Akiba:
Escuro:

Linhas dos titulos:
Claro:
Akiba:
Escuro:

Cards de destaques:
Claro:
Akiba:
Escuro:

Cards de reviews:
Claro:
Akiba:
Escuro:

Cards de materias:
Claro:
Akiba:
Escuro:

Calendario de eventos:
Claro:
Akiba:
Escuro:

Cards de podcasts:
Claro:
Akiba:
Escuro:

Likes e numeros da Home:
Claro:
Akiba:
Escuro:

# Parte 4: Paginas publicas

Exemplos:
Editoriais, radio, podcasts, midia, busca, equipe e contato.

Fundo do topo:
Claro:
Akiba:
Escuro:

Fundo do corpo:
Claro:
Akiba:
Escuro:

Cabecalho grande com titulo:
Claro:
Akiba:
Escuro:

Cards de lista:
Claro:
Akiba:
Escuro:

Tela sem conteudo:
Claro:
Akiba:
Escuro:

# Parte 5: Paginas de leitura

Exemplos:
Materia, review, editorial, podcast individual ou paginas com texto longo.

Fundo da pagina:
Claro:
Akiba:
Escuro:

Area do texto:
Claro:
Akiba:
Escuro:

Titulo da leitura:
Claro:
Akiba:
Escuro:

Texto da leitura:
Claro:
Akiba:
Escuro:

Links no texto:
Claro:
Akiba:
Escuro:

Comentarios:
Claro:
Akiba:
Escuro:

Campo de comentario:
Claro:
Akiba:
Escuro:

# Parte 6: Navegacao

Navbar:
Claro:
Akiba:
Escuro:

Links da navbar:
Claro:
Akiba:
Escuro:

Avatar ou perfil:
Claro:
Akiba:
Escuro:

Seletor de tema:
Claro:
Akiba:
Escuro:

Painel de notificacoes:
Claro:
Akiba:
Escuro:

# Parte 7: Rodape e player fixo

Rodape:
Claro:
Akiba:
Escuro:

Player fixo inferior:
Claro:
Akiba:
Escuro:

Controle de volume:
Claro:
Akiba:
Escuro:

# Parte 8: Componentes gerais

Botoes:
Claro:
Akiba:
Escuro:

Campos de formulario:
Claro:
Akiba:
Escuro:

Janelas e modais:
Devem seguir o tema publico?
Resposta:

Se sim, como?
Claro:
Akiba:
Escuro:

Aviso de cookies:
Claro:
Akiba:
Escuro:

Baloezinhos de ajuda:
Claro:
Akiba:
Escuro:

Publicidade e anuncios:
Claro:
Akiba:
Escuro:

# Parte 9: Revisao final para IA

Antes de encerrar, confira:

- [ ] Todos os temas escolhidos tem nome.
- [ ] Foi definido qual tema abre primeiro.
- [ ] Toda resposta vazia foi preenchida, marcada como `manter` ou marcada como `nao sei`.
- [ ] As cores clicaveis continuam claras para o usuario perceber onde pode clicar.
- [ ] Os likes/numeros nao foram confundidos com botoes.
- [ ] O tema claro tem contraste suficiente para leitura.
- [ ] O tema escuro nao ficou igual ao tema Akiba, a menos que a pessoa tenha pedido.
- [ ] O arquivo final esta em Markdown.

# Anexo tecnico

Esta parte e para quem for aplicar no codigo.

- Tema publico: `resources/js/css/themes.css`
- Paleta base: `resources/js/css/app.css`
- Lista de temas: `resources/js/lib/utils/publicTheme.js`
- Titulo de secao: `public-section-heading-title`
- Linha/separador de secao: `public-section-heading-line`

Observacao tecnica:
O preenchimento deste arquivo nao muda o site sozinho. Depois de preenchido, alguem precisa aplicar as decisoes nos arquivos de tema.
