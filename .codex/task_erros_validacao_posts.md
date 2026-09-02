# Task: Exibir erros de validação nos formulários de posts

Corrigir o tratamento de erros nos formulários de criação de **matérias,
reviews e eventos** dentro do painel.

## Problema

Quando o envio falha por erro de validação retornado pelo back-end, a UI
não está refletindo corretamente os erros nos campos.

## Implementar

-   Analisar o tratamento atual das respostas de erro nesses 3
    formulários.
-   Exibir cada erro de validação no campo correspondente.
-   Exibir a mensagem retornada pelo back-end próxima ao campo.
-   Aplicar o estado visual de erro seguindo o padrão existente no
    projeto.
-   Campos sem erro permanecem normais.
-   Ao corrigir e reenviar, atualizar/remover os erros conforme a nova
    resposta.
-   Erros sem campo específico devem ser exibidos como mensagem geral no
    formulário.
-   Preservar as mensagens específicas retornadas pelo back-end.

## Escopo

Aplicar somente na criação de: - Matérias - Reviews - Eventos

Se os formulários compartilharem componentes/lógica, implementar o
tratamento de forma reutilizável.

## Regras

-   Reutilizar componentes/helpers existentes.
-   Evitar lógica duplicada.
-   Não alterar as validações do back-end.
-   Não alterar o fluxo de sucesso.
-   Não refatorar fora do escopo.
-   Garantir: envio → erro de validação → erro exibido no campo →
    correção → novo envio.
