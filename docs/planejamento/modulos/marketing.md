---
status: rascunho
tipo: modulo
area: marketing
atualizado_em: 2026-08-03
---

# Marketing

## Objetivo

Gerenciar repositorios e materiais relacionados a marketing.

## Escopos Atuais

- Repositorios.
- Criacao de item.
- Edicao de item.
- Visualizacao individual.
- Desativacao.

## Funcionalidades

- Listar repositorios ativos.
- Agrupar repositorios por formato ou tipo.
- Criar material.
- Editar material.
- Abrir material individual no painel.
- Desativar material.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Marketing.svelte`.
- Controller de pagina: `RepositoryPageController`.
- Controller de recurso: `RepositoryController`.
- Invokes: `DeactivateRepositoryController`.
- Actions: `StoreRepositoryAction`, `UpdateRepositoryAction`, `DeactivateRepositoryAction`.
- Filter: `RepositoryFilter`.
- Model: `Repository`.

## Permissoes

- `marketing.module.view` controla acesso ao modulo no menu.
- Regras principais ficam em `RepositoryPolicy`.

## Riscos

- O termo repositorio pode ficar amplo demais se misturar muitos tipos de material.
- Categorias e tipos precisam ser previsiveis para facilitar busca e manutencao.

## Planejamento

- [ ] Definir tipos de materiais.
- [ ] Documentar regras de organizacao dos repositorios.
- [ ] Mapear permissao de acesso e edicao.

## Referencias

- [administracao](./administracao)
