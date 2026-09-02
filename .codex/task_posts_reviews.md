# Task: Posts / Reviews

## 1. Fontes de pesquisa

-   Tornar `fontes de pesquisa` opcional nos posts no back-end.
-   Ajustar as validações necessárias no back-end.
-   Aplicar na leitura pública de reviews, eventos e matérias:
    -   Se houver 1 fonte, exibir apenas essa fonte.
    -   Se houver 2 fontes, exibir as duas.
    -   Se não houver nenhuma, exibir `Sem fontes de pesquisa`.
-   Manter o padrão visual já existente para exibição das fontes.

## 2. Campo `studio` para reviews

Adicionar `studio`, exclusivo para posts do tipo review.

Seguir o padrão dos campos adicionais de posts, usando eventos como
referência.

Implementar:

-   Nova migration para `studio`. Não alterar migrations existentes.
-   Campo nullable.
-   Atualizar Model.
-   Atualizar Services e fluxos de create/update/read.
-   Atualizar validações/DTOs/resources, se aplicável.
-   Atualizar Factories.
-   Atualizar Seeders.
-   Adicionar `studio` ao formulário de criação/edição de reviews no
    painel.
-   Persistir e carregar corretamente o valor na edição.
-   Exibir `studio` na leitura pública da review.
-   Se `studio` estiver vazio, não exibir o campo.

## Regras

-   `studio` apenas para reviews.
-   Reutilizar padrões/componentes existentes.
-   Não duplicar lógica.
-   Não refatorar fora do escopo.
-   Não alterar comportamento dos demais tipos de posts.
-   Garantir fluxo completo: painel → persistência → edição → leitura
    pública.
