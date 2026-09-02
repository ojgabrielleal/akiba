# Task: Aplicar validação do Create no Update

Aplicar no fluxo de **Update** o mesmo esquema de validação já existente
e funcionando no **Create** para:

-   Post/Matéria
-   Review
-   Evento

## Implementação

Usar o Create de cada tipo como referência e replicar no Update:

-   Mesmas regras de validação.
-   Mesmos campos obrigatórios.
-   Mesmo tratamento dos erros retornados pelo back-end/Inertia.
-   Mesma associação entre erro e respectivo componente de input.
-   Mesmo estado visual de erro.
-   Mesma mensagem de validação abaixo do campo.

O objetivo é:

`Create validation = Update validation`

Se no Create um campo inválido recebe borda vermelha e mensagem abaixo,
o mesmo deve acontecer no Update.

## Exceção: Rascunho

Posts salvos como `rascunho` continuam sem exigir campos obrigatórios.

Essa exceção deve funcionar tanto no Create quanto no Update.

## Importante

Não criar um segundo sistema de validação para Update.

Reutilizar o padrão já implementado no Create e adaptar apenas o
necessário para funcionar durante a edição.

Os erros devem seguir diretamente:

`Back-end → Inertia → formulário → componente de input`

Não utilizar Store ou estado global para armazenar erros de validação.

## Regras

-   Analisar primeiro a implementação do Create.
-   Aplicar o mesmo padrão no Update.
-   Não duplicar lógica desnecessariamente.
-   Não criar novas regras de validação.
-   Não alterar o comportamento correto do Create.
-   Preservar a exceção de rascunhos.
-   Aplicar para Post/Matéria, Review e Evento.
-   Não refatorar fora do escopo.
