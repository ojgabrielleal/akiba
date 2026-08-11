---
status: rascunho
tipo: modulo
area: lixeira
atualizado_em: 2026-08-03
---

# Lixeira

## Objetivo

Permitir consulta, restauracao e remocao definitiva de registros desativados.

## Escopos Atuais

- Listagem de lixeira.
- Reativacao.
- Exclusao definitiva.

## Funcionalidades

- Consolidar usuarios, programas, posts, podcasts, enquetes, tarefas e repositorios desativados.
- Ordenar itens por titulo.
- Exibir tipo, titulo, subtitulo, imagem e genero quando existir.
- Reativar item.
- Excluir item definitivamente.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Trash.svelte`.
- Controller: `TrashController`.
- Service: `TrashService`.
- Grid: `TrashGrid`.
- Services de escopo: `UserService`, `ProgramService`, `PostService`, `PodcastService`, `PollService`, `TaskService`, `RepositoryService`.
- Models: `User`, `Program`, `Post`, `Podcast`, `Poll`, `Task`, `Repository`.

## Permissoes

- `trash.module.view` controla acesso ao modulo no menu e na rota.
- `trash.restore` controla reativacao.
- `trash.delete` controla exclusao definitiva.

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
