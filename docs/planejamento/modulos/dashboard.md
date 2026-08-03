---
status: rascunho
tipo: modulo
area: dashboard
atualizado_em: 2026-08-03
---

# Dashboard

## Objetivo

Centralizar a visao rapida do painel privado.

## Componentes Atuais

- Boas-vindas.
- Avisos e atividades.
- Minhas tarefas.
- Minhas ultimas materias.
- Calendario.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Dashboard.svelte`.
- Controller: `app/Http/Controllers/Private/Pages/DashboardPageController.php`.
- Widgets: `WellcomeHero`, `ActivityCarousel`, `TaskList`, `PostGrid`, `CalendarGrid`.
- Filters: `ActivityFilter`, `TaskFilter`, `PostFilter`, `CalendarFilter`.
- Resources: `ActivityResource`, `TaskResource`, `PostResource`, `CalendarWeekResource`.

## Permissoes

- Usa autorizacao por recurso via `whenCanViewAny`.
- Depende das permissoes de atividades, tarefas, postagens e calendario.

## Riscos

- O dashboard pode ficar inconsistente se cada bloco evoluir isoladamente.
- Filtros diferentes entre Dashboard e Administracao podem mostrar dados divergentes para tarefas e calendario.

## Planejamento

- [ ] Definir quais indicadores devem aparecer como resumo.
- [ ] Decidir se tarefas pendentes devem ter destaque por prioridade.
- [ ] Mapear permissoes necessarias para cada bloco.

## Referencias

- [administracao](./administracao)
- [postagens](./postagens)
