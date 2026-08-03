---
status: ativo
tipo: guia-desenvolvimento
atualizado_em: 2026-08-03
---

# Vinculo com Tarefas

## Antes de Comecar

1. Conferir se a demanda existe em [backlog](../tarefas/backlog) ou no ciclo atual.
2. Identificar o modulo afetado em [mapa-de-modulos](../mapa-de-modulos).
3. Conferir decisoes existentes em [decisoes](../decisoes).
4. Criar ou atualizar tarefa quando o trabalho for maior que um ajuste simples.

## Durante a Implementacao

- Registrar decisoes estruturais em [decisoes](../decisoes).
- Atualizar a nota do modulo se o comportamento mudar.
- Atualizar guia operacional se o uso do painel mudar.

## Ao Finalizar

- Mover status da tarefa no ciclo ou backlog.
- Atualizar [historico](../historico) se for entrega relevante.
- Rodar [auditoria-local](../automacao/auditoria-local) quando o ambiente estiver disponivel.

## Commits

Commits nao precisam repetir a documentacao inteira. Eles devem ser claros e apontar o tipo de mudanca.

Exemplos:

```bash
git commit -m "docs: document radio planning workflow"
git commit -m "feat: add task review status"
git commit -m "fix: correct locution request ordering"
```

