---
status: rascunho
tipo: modulo
area: administracao
atualizado_em: 2026-08-03
---

# Administracao

## Objetivo

Gerenciar estruturas internas do painel, usuarios, cargos, calendario, atividades, tarefas e revisoes administrativas.

## Escopos Atuais

- Usuarios.
- Cargos.
- Calendario.
- Atividades.
- Tarefas.
- Revisao de formularios.

## Funcionalidades

- Criar usuarios.
- Atualizar acesso de usuarios.
- Desativar usuarios.
- Criar, editar e remover cargos.
- Criar e editar eventos de calendario.
- Criar e editar atividades.
- Criar, editar, concluir, revisar e desativar tarefas.
- Aprovar ou rejeitar formularios recebidos.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Administration.svelte`.
- Controller de pagina: `AdministrationPageController`.
- Controllers de recurso: `UserController`, `RoleController`, `CalendarController`, `ActivityController`, `TaskController`.
- Invokes: `CompleteTaskController`, `DeactivateTaskController`, `DeactivateUserController`, `ApproveFormSubmissionController`, `RejectFormSubmissionController`.
- Filters: `UserFilter`, `RoleFilter`, `PermissionFilter`, `CalendarFilter`, `ActivityFilter`, `TaskFilter`, `FormSubmissionFilter`.
- Models: `User`, `Role`, `Calendar`, `Activity`, `Task`, `FormSubmission`.

## Permissoes

- `administration.module.view` controla acesso ao modulo no menu.
- Recursos usam policies dedicadas: `UserPolicy`, `RolePolicy`, `CalendarPolicy`, `ActivityPolicy`, `TaskPolicy`.
- Revisao de formularios usa permissoes como `form.submission.list` e `form.submission.review`.

## Riscos

- Este modulo concentra muitas responsabilidades administrativas.
- Alteracoes de permissao podem afetar varios widgets ao mesmo tempo.
- O fluxo de tarefas precisa de status bem definidos para evitar ambiguidade entre pendente, revisao, concluida e desativada.

## Planejamento

- [ ] Documentar permissoes por recurso.
- [ ] Separar tarefas operacionais de tarefas editoriais, se necessario.
- [ ] Definir fluxo completo de status das tarefas.
- [ ] Mapear telas de cadastro e edicao.

## Referencias

- [dashboard](./dashboard)
- [arquitetura](../arquitetura)
- [decisoes](../decisoes)
