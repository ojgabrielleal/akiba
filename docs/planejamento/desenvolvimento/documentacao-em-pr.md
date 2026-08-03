---
status: ativo
tipo: guia-desenvolvimento
atualizado_em: 2026-08-03
---

# Documentacao em PR

## Regra Geral

Toda pull request deve responder se a documentacao precisa ser atualizada.

Atualizar documentacao quando a PR altera:

- comportamento do usuario;
- regra de negocio;
- permissao;
- modulo;
- rota;
- fluxo operacional;
- status de entidade;
- arquitetura;
- integracao externa;
- processo de desenvolvimento.

## Onde Atualizar

| Mudanca | Onde documentar |
| --- | --- |
| Modulo ou tela | nota em `modulos/` e [mapa-de-modulos](../mapa-de-modulos) |
| Fluxo de uso | nota em `operacao/` |
| Regra tecnica | [arquitetura](../arquitetura) ou [decisoes](../decisoes) |
| Decisao estrutural | [decisoes](../decisoes) |
| Tarefa ou prioridade | [backlog](../tarefas/backlog) ou ciclo atual |
| Processo | `governanca/`, `qualidade/`, `automacao/` ou `desenvolvimento/` |

## Como Escrever na PR

Use uma frase curta:

```md
Documentation:
- Updated [radio](../modulos/radio) and [radio-e-locucao](../operacao/radio-e-locucao) because program scheduling behavior changed.
```

Ou, quando nao precisar:

```md
Documentation:
- Not needed. This PR only fixes an internal typo with no behavior change.
```

## Revisao

Quem revisa a PR deve verificar se a justificativa faz sentido. Se a mudanca altera comportamento e nao atualiza docs, pedir ajuste.

