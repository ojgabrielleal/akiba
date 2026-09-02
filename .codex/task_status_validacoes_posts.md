# Task: Corrigir status e validações dos Posts

Investigar e corrigir o fluxo de status dos posts e suas validações.

Tipos: - Post/Matéria - Review - Evento

## 1. Status

Verificar as ações: - `Salvar como rascunho` - `Enviar para avaliação` -
`Publicar`

### Problema

O status está sendo refletido como `publicado` antes da publicação
realmente ser concluída.

A UI pode estar alterando/assumindo o status localmente com base na ação
executada, em vez de utilizar o status real persistido no banco e
retornado pelo back-end.

### Verificar

-   Analisar o fluxo completo das 3 ações.
-   Identificar de onde a UI obtém o status após cada ação.
-   Verificar alterações antecipadas/locais de status.
-   Verificar Store, estado, callback ou lógica que defina status com
    base apenas no botão clicado.
-   Comparar o status exibido com o realmente persistido no banco.

### Comportamento esperado

O back-end/banco é a fonte de verdade.

`ação → requisição → back-end → persistência → resposta → UI reflete status real`

A UI não deve assumir `rascunho`, `avaliação` ou `publicado` antes da
confirmação do back-end.

Se a operação falhar ou não for concluída, o status anterior deve
permanecer refletido corretamente.

## 2. Campos obrigatórios no Update

Revisar as validações de **atualização** para Post/Matéria, Review e
Evento.

### Regra

Quando o post **não estiver em rascunho**, o update deve exigir os
mesmos campos obrigatórios utilizados na criação daquele respectivo
tipo.

`Create obrigatório = Update obrigatório`

Respeitar as particularidades existentes de Post/Matéria, Review e
Evento.

Não inventar novos campos obrigatórios. Usar as regras já existentes de
criação de cada tipo como referência.

### Exceção: Rascunho

Se o post estiver sendo salvo/mantido como `rascunho`, **nenhum campo
deve ser obrigatório**.

Um rascunho deve poder ser salvo mesmo incompleto.

A validação deve considerar o status real da operação/post para decidir
se os campos são obrigatórios.

## Regras

-   Primeiro identificar as causas antes de alterar.
-   Back-end/banco como fonte de verdade do status.
-   Não simular ou antecipar status na UI.
-   Não alterar regras de negócio dos status.
-   Update deve seguir os mesmos campos obrigatórios do Create, exceto
    rascunhos.
-   Rascunho não deve exigir nenhum campo obrigatório.
-   Aplicar corretamente para Post/Matéria, Review e Evento.
-   Reutilizar validações existentes quando possível.
-   Evitar duplicação de regras.
-   Não refatorar fora do escopo.
