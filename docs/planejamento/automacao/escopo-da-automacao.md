---
status: ativo
tipo: guia-automacao
atualizado_em: 2026-08-03
---

# Escopo da Automacao

## Deve Automatizar

- verificacoes repetitivas;
- existencia de metadados;
- presenca de titulo;
- resolucao basica de links internos;
- contagem de notas auditadas.

## Nao Deve Automatizar Ainda

- formatacao agressiva dos arquivos;
- alteracao automatica de conteudo;
- criacao automatica de notas;
- validacao semantica de regras de negocio;
- comparacao completa entre documentacao e codigo.

## Possiveis Evolucoes

- validar rotas citadas contra arquivos de rota;
- validar arquivos citados contra o filesystem;
- gerar relatorio em Markdown;
- rodar em CI;
- detectar notas sem link de entrada;
- detectar notas duplicadas por titulo.

