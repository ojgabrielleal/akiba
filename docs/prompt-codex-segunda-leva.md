# Ajustes visuais — Reviews, Matérias e Histórico de Programação

Analise a implementação atual do projeto antes de realizar as alterações.

O projeto utiliza Laravel e já possui componentes e padrões visuais existentes. Antes de modificar o código, identifique os componentes responsáveis pelas interfaces descritas abaixo e verifique se eles são reutilizados em outras páginas.

## Regras gerais

- Preserve a arquitetura existente.
- Evite duplicação de CSS, markup ou lógica.
- Prefira alterar componentes compartilhados quando a alteração realmente fizer sentido para todos os lugares onde eles são utilizados.
- Não faça refatorações fora do escopo apenas porque componentes visualmente semelhantes foram encontrados.
- Preserve a identidade visual atual do site.
- Garanta responsividade.
- Não altere funcionalidades do painel ou do site público que não estejam relacionadas aos requisitos abaixo.

---

## 1. Reviews no site público — permitir títulos com até 2 linhas

Na listagem de reviews do site público, os títulos dos cards atualmente possuem pouco espaço e são truncados muito cedo.

### Alteração

Permitir que o título de cada review ocupe no máximo **2 linhas**.

Caso o título ultrapasse duas linhas, aplicar truncamento com reticências (`...`), preferencialmente utilizando uma solução apropriada como `line-clamp`.

### Comportamento esperado

- Títulos curtos podem continuar ocupando apenas uma linha.
- Títulos maiores podem ocupar duas linhas.
- Títulos que ultrapassarem duas linhas devem receber reticências.
- Não permitir clipping vertical do texto.
- Os cards devem continuar visualmente alinhados.
- As dimensões dos cards devem permanecer consistentes.
- Não alterar desnecessariamente imagem, cores, espaçamentos ou demais elementos do card.

Antes de alterar, identifique se esse card/componente de review é reutilizado em outras áreas do site público.

Se for o mesmo componente e o comportamento de duas linhas fizer sentido em todas essas utilizações, implemente a alteração no componente compartilhado.

Não aplique essa alteração ao painel administrativo sem necessidade.

---

## 2. Matérias/posts no site público — reduzir levemente o tamanho dos títulos

Na interface de listagem de matérias/posts do site público, os títulos estão relativamente grandes e acabam atingindo o limite disponível rapidamente, gerando reticências mesmo em títulos que poderiam ter uma porção maior exibida.

### Alteração

Reduza **levemente** o tamanho da fonte dos títulos.

O objetivo não é redesenhar a tipografia nem deixar os títulos pequenos demais.

O objetivo é apenas permitir que mais caracteres sejam exibidos antes do truncamento.

### Preservar

Mantenha:

- família da fonte;
- peso;
- estilo itálico, quando existente;
- cores;
- limite atual de linhas;
- truncamento como proteção para títulos realmente longos;
- identidade visual da interface.

Se necessário, ajuste levemente o `line-height` para manter boa legibilidade após a redução do `font-size`.

A alteração deve afetar apenas as interfaces/listagens apropriadas, e não o título completo exibido dentro da página individual de uma matéria.

---

## 3. Investigar reutilização dos componentes de Reviews e Posts/Matérias

Antes de implementar os dois requisitos anteriores, investigue como essas interfaces foram construídas.

Existem interfaces visualmente semelhantes/repetidas em diferentes partes do site público.

Exemplos conhecidos incluem:

- página inicial;
- `/news`;
- `/colunas`;
- outras páginas/categorias que possam reutilizar a mesma estrutura.

### Objetivo

Descubra se as listagens de matérias/posts e reviews utilizam componentes compartilhados.

Se a mesma implementação/componente for reutilizada em várias páginas e o ajuste visual fizer sentido em todas elas:

- altere o componente compartilhado;
- evite criar overrides individuais de CSS para cada página;
- evite duplicar markup ou estilos.

Por exemplo, se a listagem de matérias da home e a listagem encontrada em `/news` utilizarem exatamente o mesmo componente de apresentação, a redução de fonte deve ser implementada nesse componente para manter consistência.

O mesmo princípio deve ser considerado para os cards/listagens de reviews.

### Importante

Não presuma que duas interfaces visualmente semelhantes necessariamente utilizam o mesmo componente.

Primeiro investigue a estrutura atual.

Se forem implementações diferentes que apenas possuem aparência semelhante, não faça uma grande refatoração ou unificação fora do escopo desta tarefa.

Preserve a arquitetura existente e aplique os ajustes somente onde forem apropriados.

Depois das alterações, revise as páginas que utilizam os componentes modificados para evitar regressões visuais.

---

## 4. `/panel/reports` — impedir sobreposição da mensagem com o horário nos pedidos de música

Na página:

`/panel/reports`

existe o Histórico de Programação, onde é possível visualizar os pedidos de música realizados pelos ouvintes.

Nos cards desses pedidos são exibidas informações como:

- usuário;
- localização;
- status;
- anime;
- artista;
- música;
- mensagem do ouvinte;
- horário do pedido.

### Problema atual

Quando a mensagem enviada pelo ouvinte possui várias linhas, o texto pode crescer até a região inferior do card e ficar sobreposto ao horário exibido no canto inferior direito.

Isso não deve acontecer.

### Alteração

Corrija o layout para garantir que a mensagem e o horário nunca ocupem o mesmo espaço.

O horário deve possuir uma área devidamente reservada no layout.

A mensagem deve respeitar essa área.

Caso seja necessário, o card deve poder aumentar sua altura para acomodar mensagens maiores.

### Comportamento esperado

O layout deve funcionar corretamente quando:

- não houver mensagem;
- houver mensagem curta;
- houver mensagem de uma linha;
- houver mensagem com várias linhas;
- houver mensagens maiores.

O horário deve permanecer visualmente no **canto inferior direito** do card.

Não permita que o texto passe por cima do horário.

### Implementação

Evite soluções frágeis específicas para o exemplo atual, como:

- valores arbitrários enormes de `padding-bottom`;
- posições absolutas sem reserva adequada de espaço;
- alturas fixas que cortem mensagens maiores;
- regras específicas para determinada quantidade de caracteres.

Prefira uma solução estrutural compatível com o código existente, utilizando corretamente o fluxo do layout, flexbox, grid ou outra abordagem apropriada.

O card deve se adaptar ao conteúdo.

Preserve o design atual o máximo possível.

---

# Verificação final

Antes de concluir a tarefa:

1. Revise todas as alterações realizadas.
2. Verifique a home e as páginas públicas que reutilizam os componentes alterados.
3. Verifique especialmente `/news` e `/colunas`.
4. Confirme que os títulos de reviews podem utilizar até duas linhas sem quebrar os cards.
5. Confirme que a redução da fonte das matérias melhorou a quantidade de texto exibido sem prejudicar a leitura.
6. Confirme que títulos extremamente longos continuam sendo truncados corretamente.
7. Teste os cards de pedidos de música com mensagens de diferentes tamanhos.
8. Confirme que o horário nunca fica sobreposto pela mensagem.
9. Verifique comportamento responsivo em diferentes larguras e alturas de tela.
10. Não altere componentes ou funcionalidades fora do escopo sem necessidade.

Ao finalizar, apresente um resumo contendo:

- arquivos alterados;
- componentes identificados;
- quais componentes eram compartilhados;
- páginas impactadas pelas alterações;
- ajustes CSS/layout realizados;
- eventuais pontos que mereçam teste manual.
