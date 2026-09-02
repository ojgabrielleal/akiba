# Task: Refletir erros de validação do Inertia nos inputs

Corrigir a exibição de erros de validação nos formulários do painel.

## Contexto

Os erros de validação vêm diretamente do back-end através do
**Inertia**, como parte do próprio fluxo da requisição.

Cada campo utiliza seu próprio componente de input.

## Comportamento esperado

Quando o formulário for enviado e o back-end retornar erros, por exemplo
`title` e `content` obrigatórios:

-   Capturar os erros retornados pelo Inertia na própria requisição.
-   Passar o erro de cada campo diretamente para seu componente de
    input.
-   Campo com erro deve ter borda vermelha.
-   Exibir abaixo do campo a mensagem retornada pelo back-end, ex.:
    `Obrigatório`.
-   Apenas campos que possuem erro devem receber o estado visual de
    erro.
-   Após novo envio, se o campo não possuir mais erro, remover
    borda/mensagem.

## Implementação

-   Usar diretamente os erros retornados pelo Inertia.
-   Passar o erro correspondente como prop para cada componente de
    input.
-   Adaptar os componentes de input para receber/exibir essa prop, caso
    necessário.
-   Centralizar apenas a parte visual dentro dos próprios componentes de
    input.
-   Manter estilo/comportamento atual quando não houver erro.

## Importante: não usar Store

**Não criar Store, estado global ou qualquer mecanismo intermediário
para armazenar erros de validação.**

Os erros pertencem à própria requisição/formulário e devem seguir
diretamente:

`Back-end → Inertia → formulário → prop do componente de input`

-   Não copiar erros para Store.
-   Não sincronizar erros do Inertia com Store.
-   Não criar estado global para validação.
-   Não criar lógica paralela de gerenciamento de erros.
-   Se atualmente existir Store criada especificamente para
    armazenar/tratar esses erros de validação, remover essa
    implementação e substituir pelo fluxo direto do Inertia.
-   Remover imports, estados e código que ficarem inutilizados após essa
    alteração.

## Escopo

Aplicar nos formulários de criação de: - Matérias - Reviews - Eventos

## Regras

-   Não alterar as validações do back-end.
-   Não alterar mensagens retornadas pelo back-end sem necessidade.
-   Reutilizar os componentes de input existentes.
-   Evitar lógica duplicada entre os formulários.
-   Não refatorar fora do escopo.
-   Garantir: submit → back-end valida → Inertia retorna erros →
    formulário passa erro para input → borda vermelha + mensagem abaixo.
