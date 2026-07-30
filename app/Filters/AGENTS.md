# Regras De Filters

Escopo: tudo em `app/Filters`.

## Regra Principal

Filters concentram composicao de queries para listagens, buscas, ordenacao e filtros de tela.

## Estrutura

- Mantenha filters na raiz de `app/Filters`.
- Nomeie filters pelo model seguido de `Filter`, como `UserFilter` ou `CalendarFilter`.

## Responsabilidades

- Os pontos de entrada dos filters devem receber `array $filters = []`.
- Monte queries com `when` do Eloquent.
- Aplique ordenacao padrao quando fizer sentido.
- Retorne paginacao somente quando solicitada; caso contrario, retorne a collection.
- Mantenha filters focados na composicao de query do model correspondente.

## Finalizacao

- Nao coloque regras de escrita em filters.
- Nao coloque formato de resposta em filters; isso pertence a resources ou controllers.
