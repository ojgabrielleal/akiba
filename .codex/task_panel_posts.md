# Task --- Ajustes no Painel Administrativo de Matérias

## Contexto da atividade

A atividade atual deverá ser realizada **exclusivamente dentro do painel
administrativo**, na página `/materias`.

Todas as alterações descritas nas próximas tarefas devem considerar essa
página como o escopo principal.

Não realizar alterações na interface pública ou em outras áreas do
sistema, exceto quando uma tarefa solicitar isso explicitamente.

------------------------------------------------------------------------

## Tarefa 1 --- Corrigir as cores dos botões de submit

Na página `/materias`, os botões de submit relacionados às ações da
matéria estão utilizando cores incorretas.

Corrigir **somente as cores de background** desses botões, utilizando as
classes já existentes no projeto:

-   **Salvar como rascunho:** `bg-green-forest`
-   **Enviar para avaliação:** `bg-orange-amber`
-   **Publicar:** `bg-blue-skywave`

### Requisitos

-   Manter o estilo, tamanho, espaçamento, ícones, textos e
    comportamento atual dos botões.
-   Não criar novas cores ou classes CSS para essa alteração.
-   Utilizar exatamente as classes de background especificadas acima.
-   Não alterar a lógica de submit ou o funcionamento das ações.

------------------------------------------------------------------------

## Tarefa 2 --- Investigar e corrigir campos sendo sobrescritos durante a edição

Foi relatado um problema durante a edição de matérias existentes na
página `/materias`.

Ao corrigir manualmente textos que possuem problemas de codificação de
caracteres, o administrador começa a digitar normalmente, porém em
alguns momentos o conteúdo do campo volta sozinho para o valor anterior
que estava carregado/salvo, fazendo com que parte ou toda a edição em
andamento seja perdida.

### Investigação

A aplicação utiliza **Svelte**.

Antes de alterar o comportamento, rastrear a origem real da sobrescrita
do valor.

Verificar principalmente:

-   variáveis reativas que estejam recebendo novamente os dados
    originais da matéria;
-   blocos reativos `$:` que possam estar reatribuindo os valores do
    formulário;
-   uso de `bind:value` nos campos e possíveis conflitos com atribuições
    externas;
-   stores do Svelte que estejam atualizando o formulário enquanto o
    usuário digita;
-   subscriptions que possam reaplicar os dados recebidos da API;
-   `onMount`, callbacks, listeners ou outras rotinas que recarreguem os
    dados da matéria;
-   requisições ou atualizações assíncronas que terminem depois que o
    usuário já começou a editar;
-   reatribuições do objeto da matéria que possam atualizar novamente os
    campos;
-   qualquer autosave ou sincronização existente;
-   componentes filhos que recebam valores por props e possam ser
    reinicializados após mudanças no componente pai.

Não assumir previamente que o problema está em `bind:value`, stores ou
blocos reativos. Identificar primeiro o fluxo que realmente está
sobrescrevendo o conteúdo.

### Comportamento esperado

Os dados da matéria devem ser utilizados para preencher o formulário
quando a edição for aberta.

Depois que o usuário começar a editar um campo, o valor local digitado
por ele não deve ser substituído silenciosamente por uma atualização
posterior dos dados originais.

Re-renderizações normais do Svelte, atualizações de stores ou conclusão
de requisições assíncronas não podem restaurar o texto antigo enquanto o
usuário estiver editando.

### Requisitos

-   Corrigir a causa real da sobrescrita.
-   Preservar o uso correto de `bind:value` e da reatividade do Svelte.
-   Não desabilitar reatividade ou atualizações de stores globalmente
    como forma de contornar o problema.
-   Evitar duplicação desnecessária de estado.
-   Manter o fluxo atual de criação, carregamento, edição e submit das
    matérias.
-   Verificar a correção em todos os campos de texto editáveis da
    matéria que compartilhem o mesmo fluxo de estado.

------------------------------------------------------------------------

## Tarefa 3 --- Remover borda laranja do post selecionado para edição

Na página `/materias`, abaixo do formulário existe um grid que exibe
todas as matérias/posts cadastrados.

Atualmente, ao clicar no botão **Editar** de um item do grid, o card
correspondente passa a exibir uma **borda laranja** indicando que aquele
item está sendo editado.

Remover esse comportamento visual.

### Comportamento esperado

-   Ao clicar em **Editar**, o formulário deve continuar sendo
    preenchido normalmente com os dados da matéria selecionada.
-   O funcionamento do botão **Editar** deve permanecer exatamente como
    está.
-   O card selecionado **não deve receber borda laranja** ou qualquer
    outro destaque de borda por estar sendo editado.
-   A alteração deve ser apenas visual e não deve interferir no estado
    ou na lógica utilizada para identificar qual matéria está sendo
    editada.

### Requisitos

-   Remover somente o estilo responsável pela borda laranja no estado de
    edição.
-   Não alterar o layout, espaçamento ou demais estilos dos cards.
-   Não modificar a lógica de edição.
-   Não substituir a borda laranja por outro indicador visual.

------------------------------------------------------------------------

## Restrições gerais

-   Manter as alterações restritas ao necessário para cumprir as
    tarefas.
-   Não realizar refatorações não relacionadas.
-   Não alterar comportamentos existentes que não estejam descritos
    neste documento.
-   Reutilizar classes, componentes e padrões já existentes no projeto
    sempre que possível.
-   Antes de finalizar, revisar as alterações para garantir que as três
    tarefas foram atendidas sem introduzir regressões.
