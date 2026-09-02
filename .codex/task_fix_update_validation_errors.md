# Task: Investigar erros de validação no Update dos Posts

Investigar e corrigir a exibição dos erros de validação ao
**atualizar**:

-   Post/Matéria
-   Review
-   Evento

## Problema

Na criação, os componentes de input possuem suporte para exibir erros de
validação.

Porém, ao editar um Post, Review ou Evento e enviar um update faltando
algum campo obrigatório, o back-end rejeita a requisição, mas o
respectivo input não apresenta:

-   borda vermelha;
-   mensagem de campo obrigatório abaixo.

## Investigar

Comparar o fluxo de **Create** com o fluxo de **Update** e identificar
por que os erros retornados pelo back-end/Inertia não estão chegando aos
componentes de input durante a atualização.

Verificar principalmente:

-   Como os erros do Inertia são recebidos após falha no update.
-   Se os erros estão disponíveis no formulário/página de edição.
-   Se cada erro está sendo passado para a prop de erro do respectivo
    componente de input.
-   Diferenças entre a implementação do Create e Update.
-   Se algum estado, callback, tratamento de resposta ou transformação
    de dados está descartando os erros.
-   Se os nomes/chaves dos campos retornados pelo back-end correspondem
    aos utilizados pelos inputs.

## Comportamento esperado

Se durante um update o back-end retornar, por exemplo, erro de campo
obrigatório para `title`:

`Update → back-end valida → Inertia retorna errors.title → formulário → input title`

O input deve:

-   receber o erro;
-   ficar com borda vermelha;
-   exibir abaixo a mensagem retornada pelo back-end, como
    `Campo obrigatório`.

O mesmo comportamento deve funcionar para todos os campos obrigatórios
de Post/Matéria, Review e Evento.

## Importante

Não criar Store ou estado global para resolver isso.

Os erros devem utilizar diretamente o fluxo do Inertia e ser passados
aos componentes:

`Back-end → Inertia → página/formulário de edição → prop error do input`

Se o Create já possui esse comportamento funcionando, utilizar a
implementação existente como referência e corrigir o Update para seguir
o mesmo padrão.

## Regras

-   Primeiro identificar a causa do problema.
-   Não alterar as regras de validação do back-end apenas para contornar
    o problema visual.
-   Não criar Store para erros.
-   Não duplicar o sistema de validação.
-   Reutilizar o suporte a erros já existente nos componentes de input.
-   Não criar mensagens manualmente se o back-end já retorna a mensagem.
-   Não alterar o fluxo de Create que já estiver funcionando.
-   Não refatorar fora do escopo.
