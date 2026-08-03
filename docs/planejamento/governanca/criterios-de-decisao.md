---
status: ativo
tipo: guia-governanca
atualizado_em: 2026-08-03
---

# Criterios de Decisao

## Quando Registrar uma Decisao

Registrar uma decisao em [decisoes](../decisoes) quando ela:

- muda arquitetura;
- cria ou remove modulo;
- altera permissao;
- altera ciclo de vida de uma entidade;
- define padrao de codigo;
- muda fluxo operacional relevante;
- cria dependencia externa;
- muda forma de armazenar ou consultar dados.

## Quando Nao Precisa Registrar

Nao precisa virar decisao formal quando for:

- ajuste visual pequeno;
- correcao de texto;
- refatoracao interna sem mudanca de comportamento;
- ajuste localizado que nao cria padrao.

## Como Escrever

Uma boa decisao deve responder:

- qual problema existia;
- quais opcoes foram consideradas;
- qual escolha foi feita;
- quais consequencias essa escolha traz;
- quais modulos ou arquivos podem ser afetados.

## Status de Decisao

| Status | Uso |
| --- | --- |
| proposta | ainda precisa de validacao |
| aprovada | pode orientar implementacao |
| substituida | nao vale mais, mas fica registrada historicamente |

