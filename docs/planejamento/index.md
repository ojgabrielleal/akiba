---
status: ativo
tipo: indice
atualizado_em: 2026-08-03
---

# Planejamento

Esta área guarda planejamento, decisões, operação e acompanhamento do projeto. Para entender a estrutura do código, comece pelo [Guia do Projeto](../projeto/).

O conteúdo fica em Markdown, versionado junto do código, e pode ser navegado pelo site local em `http://localhost:5174/`.

## Como Rodar

Use o ambiente Docker do projeto:

```bash
./run.sh up
```

As docs sobem automaticamente no serviço `akiba_docs`.

Comandos úteis:

```bash
./run.sh npm run docs:dev
./run.sh npm run docs:build
./run.sh npm run docs:preview
```

Use `docs:dev` para escrever com atualização automática, `docs:build` para validar o site estático e `docs:preview` para conferir o build gerado.

## Entrada

- [roadmap](./roadmap)
- [arquitetura](./arquitetura)
- [decisoes](./decisoes)
- [mapa-de-modulos](./mapa-de-modulos)
- [planejamento-operacional](./planejamento-operacional)
- [operacao/index](./operacao/index)
- [governanca/index](./governanca/index)
- [adocao/index](./adocao/index)
- [qualidade/index](./qualidade/index)
- [automacao/index](./automacao/index)
- [publicacao/index](./publicacao/index)
- [consolidacao/index](./consolidacao/index)
- [desenvolvimento/index](./desenvolvimento/index)
- [ciclos/2026-08](./ciclos/2026-08)
- [tarefas/backlog](./tarefas/backlog)

## Modulos

- [dashboard](./modulos/dashboard)
- [postagens](./modulos/postagens)
- [locucao](./modulos/locucao)
- [radio](./modulos/radio)
- [podcasts](./modulos/podcasts)
- [marketing](./modulos/marketing)
- [midias](./modulos/midias)
- [administracao](./modulos/administracao)
- [relatorios](./modulos/relatorios)
- [itens-desativados](./modulos/itens-desativados)

## Como Usar

- Cada módulo deve ter uma página própria em `modulos/`.
- Decisões estruturais devem entrar em [decisoes](./decisoes).
- Mudanças maiores de arquitetura devem ser refletidas em [arquitetura](./arquitetura).
- Etapas futuras, prioridades e pendências gerais devem ficar em [roadmap](./roadmap).
- A visão consolidada dos módulos deve ficar em [mapa-de-modulos](./mapa-de-modulos).
- O acompanhamento de ciclos e prioridades deve ficar em [planejamento-operacional](./planejamento-operacional).
- Fluxos de uso e operação do painel devem ficar em [operacao/index](./operacao/index).
- Regras de manutenção da documentação devem ficar em [governanca/index](./governanca/index).
- Guias para entrada e uso da documentação devem ficar em [adocao/index](./adocao/index).
- Auditorias e critérios de qualidade devem ficar em [qualidade/index](./qualidade/index).
- Scripts e rotinas automatizadas devem ficar em [automacao/index](./automacao/index).
- Orientações de compartilhamento e publicação devem ficar em [publicacao/index](./publicacao/index).
- Resumos executivos e fechamento de marcos devem ficar em [consolidacao/index](./consolidacao/index).
- Integração da documentação com o fluxo de desenvolvimento deve ficar em [desenvolvimento/index](./desenvolvimento/index).

## Padrão de Escrita

- Use links Markdown relativos: `[texto](./pagina)` ou `[texto](../pasta/pagina)`.
- Mantenha um `# Título` por página.
- Use frontmatter simples para status, tipo e data de atualização.
- Prefira páginas objetivas, com exemplos e passos executáveis.
- Rode `./run.sh npm run docs:build` antes de publicar mudanças grandes.
