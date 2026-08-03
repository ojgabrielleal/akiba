---
status: rascunho
tipo: modulo
area: itens-desativados
atualizado_em: 2026-08-03
---

# Itens Desativados

## Objetivo

Permitir consulta, restauracao e remocao definitiva de registros desativados.

## Escopos Atuais

- Listagem de itens desativados.
- Reativacao.
- Exclusao definitiva.

## Funcionalidades

- Consolidar usuarios, programas, posts, podcasts, enquetes, tarefas e repositorios desativados.
- Ordenar itens por titulo.
- Exibir tipo, titulo, subtitulo, imagem e genero quando existir.
- Reativar item.
- Excluir item definitivamente.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/InactiveItems.svelte`.
- Controller de pagina: `InactiveItemsPageController`.
- Invokes: `ReactivateInactiveItemController`, `DestroyInactiveItemController`.
- Filters: `UserFilter`, `ProgramFilter`, `PostFilter`, `PodcastFilter`, `PollFilter`, `TaskFilter`, `RepositoryFilter`.
- Models: `User`, `Program`, `Post`, `Podcast`, `Poll`, `Task`, `Repository`.

## Permissoes

- `inactive.module.view` controla acesso ao modulo no menu e na rota.
- `inactive.restore` controla reativacao.
- `inactive.delete` controla exclusao definitiva.

## Riscos

- Exclusao definitiva precisa ser tratada com cautela por tipo de recurso.
- Restaurar itens pode exigir restaurar relacoes ou validar conflitos.

## Planejamento

- [ ] Documentar tipos suportados.
- [ ] Definir regras para restauracao.
- [ ] Definir quando exclusao definitiva e permitida.
- [ ] Mapear permissoes especificas.

## Referencias

- [administracao](./administracao)
- [decisoes](../decisoes)
