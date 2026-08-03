---
status: ativo
tipo: auditoria
atualizado_em: 2026-08-03
---

# Auditoria de Links

## Objetivo

Verificar se os links internos do VitePress continuam apontando para notas existentes ou intencionalmente planejadas.

## Revisao Manual

1. Abrir [index](../index) no VitePress.
2. Conferir se cada link principal abre uma nota.
3. Abrir o grafo local de [index](../index).
4. Procurar notas isoladas.
5. Conferir links em `operacao/`, `governanca/`, `adocao/` e `qualidade/`.

## Revisao por Busca

Use busca por links Markdown relativos para listar referências internas.

```bash
rg "\\[\\[" docs/planejamento
```

Use busca por arquivos para listar notas existentes.

```bash
find docs/planejamento -name "*.md" | sort
```

## Sinais de Problema

- link que cria nota vazia sem querer;
- nota importante sem link de entrada;
- nota duplicada com nome parecido;
- links para nomes com acento quando o arquivo nao tem acento;
- guias operacionais apontando para modulos errados.

## Frequencia

- Revisao leve: ao final de cada ciclo.
- Revisao completa: antes de fechar marco importante.

