---
status: ativo
tipo: processo
atualizado_em: 2026-08-03
---

# Planejamento Operacional

## Objetivo

Organizar o trabalho do projeto em ciclos curtos, mantendo as decisoes, tarefas e prioridades conectadas aos modulos documentados.

## Ciclos

- Ciclo atual: [2026-08](./ciclos/2026-08)
- Backlog geral: [backlog](./tarefas/backlog)
- Registro de mudancas: [historico](./historico)

## Status de Tarefas

Os status devem seguir os valores usados no sistema:

| Status | Uso |
| --- | --- |
| `in_progress` | tarefa aberta ou em execucao |
| `in_review` | tarefa pronta para revisao |
| `completed` | tarefa concluida |
| `late` | tarefa atrasada |

## Prioridades

| Prioridade | Uso |
| --- | --- |
| Alta | bloqueia fluxo importante ou afeta usuario final |
| Media | melhora fluxo existente ou remove risco relevante |
| Baixa | organizacao, polimento ou melhoria sem urgencia |

## Modelo de Tarefa

```md
## Titulo da tarefa

- Modulo: [radio](./modulos/radio)
- Status: `in_progress`
- Prioridade: Media
- Responsavel:
- Prazo:

### Contexto

Por que essa tarefa existe.

### Entrega Esperada

- [ ] Item verificavel

### Arquivos Provaveis

- `app/...`
- `resources/js/...`

### Decisoes Relacionadas

- [decisoes](./decisoes)
```

## Rotina Recomendada

- Revisar o ciclo atual no inicio da semana.
- Mover tarefas maduras do [backlog](./tarefas/backlog) para o ciclo.
- Atualizar status conforme avanco real.
- Registrar decisoes estruturais em [decisoes](./decisoes).
- Registrar mudancas entregues em [historico](./historico).

