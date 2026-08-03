---
status: ativo
tipo: guia-automacao
atualizado_em: 2026-08-03
---

# Auditoria Local

## Como Rodar

```bash
npm run docs:audit
```

Ou, usando o wrapper do projeto:

```bash
./run.sh npm run docs:audit
```

## O Que Valida

- existencia de frontmatter;
- chaves obrigatorias no frontmatter: `status`, `tipo`, `atualizado_em`;
- existencia de titulo H1;
- links internos `[...](../...)` apontando para uma nota existente.

## Quando Rodar

- depois de criar notas novas;
- depois de renomear arquivos;
- antes de fechar um ciclo;
- antes de abrir uma revisao de documentacao.

## Limites

A auditoria nao valida qualidade do texto, coerencia de regras, atualidade tecnica ou se o fluxo descrito bate com a interface real. Esses pontos continuam dependendo da revisao manual em [checklist-de-revisao](../governanca/checklist-de-revisao).

## Arquivo do Script

- `resources/js/scripts/audit-planning-docs.js`

