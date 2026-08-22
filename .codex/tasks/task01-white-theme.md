# Tarefa de implementação dos temas. 

Essa terfa visa exclusivamente ao trabalho de implementar o tema branco já apresentado pelo designer no site além de corrigir elementos dos temas já vigentes com referências visuais para elaboração. 

#### Correções pontuais que devem ser feitas no que já existe: 

- Adaptar tooltip que não está totalmente adaptado: [Visualizar onde deve ser corrigido](./references/task01-ref01.png)
- Remover sombra do ícone do avatar no navbar: [Visualizar onde deve ser corrigido](./references/task01-ref02.png)
- O ícone do amazon music também deve estar também na cor `suspense-aurora` na parte `comunidade` no rodapé: [Visualizar onde deve ser corrigido](./references/task01-ref03.png)

#### Componentes que se repetem sempre: 

1. `EditorialTitle.svelte`
    Deve **APENAS** mudar o fundo da faixa do degradê azul para o mesmo degradê laranja seguindo o mesmo esquema da frase no `MainPlayer.svelte` [Refência visual no Main Player](./references/task01-ref04.png). 

    O fundo do componente deve ser da cor `neutral-white`;
    Quando o componente exibir subfrase (`phrase`), o fundo da subfrase deve ser `neutral-white`, igual ao fundo usado quando o componente exibe itens clicáveis;

2. `Navbar.svelte`
    Deve **APENAS** troque o fundo dele para branco default `neutral-white`.

3. Inputs, Selects, Textareas. 
    Deve **APENAS** mudar o fundo deles pra cor `neutral-gray` presente no `app.css`. Os textos digitados, placeholders, e itens selecionados coloque na cor `suspense-aurora`

4. Footer e `PlayerBar.svelte`
    Esses também devem ser adaptados nas páginas internas para o tema branco **COMO ESTÁ** na página inicial

#### Alterações individuais por página: 

1. /colunas , /news, /midias, /radio, /equipe, /contato
    O fundo da página padrão deve ser mudado para a cor `neutral-white`

2. /equipe 
    - Nome dos membros no carrousel deve ser mudado pra `blue-night` [Refêrencia visual](./references/task01-ref05.png)
    - O bloco com os dados de cargo, localização e idade do membro devem ter o fundo `orange-amber` com a cor de text `suspense-aurora` [Referência visual](./references/task01-ref06.png)
    - O texto de biografia do usuário deve ter a cor `blue-night` [Refêrencia visual](./references/task01-ref07.png)
    - A cor do texto `gosta de`, `não gosta de` e `meu top 3` devem ser trocadas para `blue-night` [Refêrencia visual](./references/task01-ref08.png)

3. /contato 
    Na descrição que aparece ao lado do formulário de recrutamento com o título `tenha certeza de:` troque a cor do texto pra `blue-night`
    No formulário de recrutamento, os campos devem ter fundo `neutral-gray/30`; textos digitados, placeholder, select e itens selecionados devem usar `blue-night`

4. /radio
    O bloco de informações do `Ouvinte do mês` deve ter o fundo `orange-amber`
