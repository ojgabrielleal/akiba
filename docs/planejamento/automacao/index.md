---
status: ativo
tipo: indice-automacao
atualizado_em: 2026-08-03
---

# Automacao

Esta area documenta automacoes leves para manter o site de documentação consistente.

## Guias

- [auditoria-local](./auditoria-local)
- [escopo-da-automacao](./escopo-da-automacao)

## Comando Principal

```bash
npm run docs:audit
```

No ambiente Docker do projeto, use:

```bash
./run.sh npm run docs:audit
```

## Objetivo

- detectar notas sem frontmatter;
- detectar notas sem titulo principal;
- detectar links internos sem nota correspondente;
- dar um feedback rapido antes de revisar a documentacao manualmente.

