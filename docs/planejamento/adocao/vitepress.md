---
status: ativo
tipo: guia-adocao
atualizado_em: 2026-08-03
---

# VitePress

## Como Acessar

Com os containers ativos, acesse:

```txt
http://localhost:5174/
```

O serviço `akiba_docs` inicia automaticamente com o Docker Compose e executa:

```bash
npm run docs:dev -- --host 0.0.0.0 --port 5174
```

## Página Inicial

Use [Planejamento Akiba](../index) como entrada para planejamento, módulos, operação, governança, qualidade e desenvolvimento.

## Links

- Links internos devem usar Markdown padrão: `[texto do link](../pagina)`.
- Para páginas em subpastas, use caminhos relativos: `[Radio](../modulos/radio)`.
- Nomes de arquivos devem evitar acentos, espaços e letras maiúsculas quando possível.

## Frontmatter

Cada página deve começar com metadados simples:

```md
---
status: rascunho
tipo: modulo
atualizado_em: YYYY-MM-DD
---
```

## Status Sugeridos

| Status | Uso |
| --- | --- |
| `rascunho` | página inicial ainda incompleta |
| `ativo` | página em uso |
| `em-andamento` | página que acompanha processo aberto |
| `arquivado` | página mantida por histórico |

## Boas Práticas

- Preferir páginas pequenas, bem tituladas e conectadas pela sidebar.
- Atualizar o `atualizado_em` quando a mudança for relevante.
- Usar listas de tarefas Markdown para pendências.
- Evitar duplicar a mesma regra em muitos lugares; linkar a página principal.
- Manter `docs/` versionado junto com o código.
- Rodar `./run.sh npm run docs:build` antes de publicar alterações grandes.
