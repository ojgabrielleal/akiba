---
status: ativo
tipo: guia-governanca
atualizado_em: 2026-08-03
---

# Manutencao

## Quando Atualizar

Atualizar a documentacao quando houver:

- novo modulo;
- nova rota importante;
- nova permissao;
- novo fluxo operacional;
- mudanca de regra de negocio;
- mudanca de status ou ciclo de vida de entidade;
- decisao tecnica que afete arquitetura;
- remocao ou substituicao de funcionalidade.

## Onde Atualizar

| Mudanca | Arquivo |
| --- | --- |
| Nova pagina ou modulo | [mapa-de-modulos](../mapa-de-modulos) e nota em `modulos/` |
| Nova regra tecnica | [arquitetura](../arquitetura) ou [decisoes](../decisoes) |
| Nova regra de uso | nota em `operacao/` |
| Nova prioridade | [backlog](../tarefas/backlog) ou ciclo atual |
| Nova entrega | [historico](../historico) |
| Novo padrao de documentacao | `governanca/` |

## Rotina Sugerida

- Antes de implementar: conferir se a tarefa existe no ciclo ou backlog.
- Durante a implementacao: registrar decisoes que mudarem o plano.
- Ao finalizar: atualizar historico, modulo afetado e guia operacional se necessario.
- No fim do ciclo: revisar pendencias e mover itens para o proximo ciclo.

## Criterio de Pronto

Uma mudanca documentada esta pronta quando:

- nota afetada foi atualizada;
- links internos foram criados quando fizer sentido;
- decisao foi registrada se houve escolha estrutural;
- tarefa ou ciclo foi atualizado;
- historico recebeu uma linha quando a entrega for relevante.

