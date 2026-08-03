---
status: ativo
tipo: guia-publicacao
atualizado_em: 2026-08-03
---

# Pacote Estático

## Objetivo

Gerar um snapshot da documentação para compartilhar, arquivar ou publicar como site estático.

## Conteudo do Pacote

Incluir na fonte:

- `docs/.vitepress`;
- `docs/index.md`;
- `docs/planejamento`;
- todos os arquivos `.md` usados pelo site.

Não incluir:

- `.git`;
- `node_modules`;
- dumps;
- arquivos de ambiente.

## Build Sugerido

```bash
./run.sh npm run docs:build
```

O VitePress gera o site em:

```txt
docs/.vitepress/dist
```

Para compactar o build:

```bash
tar -czf akiba-docs-site-YYYY-MM-DD.tar.gz docs/.vitepress/dist
```

Para compactar a fonte Markdown:

```bash
tar -czf akiba-docs-source-YYYY-MM-DD.tar.gz docs
```

## Validação do Pacote

Antes de enviar:

- abrir o pacote localmente;
- conferir se `docs/.vitepress/dist/index.html` existe, quando for build;
- conferir se `docs/index.md` existe, quando for fonte;
- rodar [checklist-pre-publicacao](./checklist-pre-publicacao).

## Nome do Arquivo

Sugestão:

```txt
akiba-docs-site-YYYY-MM-DD.tar.gz
```
