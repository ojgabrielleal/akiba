---
status: rascunho
tipo: modulo
area: relatorios
atualizado_em: 2026-08-03
---

# Relatorios

## Objetivo

Concentrar consultas analiticas e visoes de acompanhamento do projeto.

## Escopos Atuais

- Relatorios privados protegidos por permissao.
- Dados de audiencia e acompanhamento.

## Funcionalidades

- Exibir audiencia atual por estacao.
- Exibir historico de audiencia por periodo.
- Listar transmissoes ao vivo ou agendadas.
- Calcular ranking interno.
- Identificar redator mais ativo.
- Identificar locutor mais ativo.
- Identificar dia com mais pedidos atendidos.
- Identificar pico de audiencia.
- Identificar maior interacao e enquete mais votada.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Reports.svelte`.
- Controller de pagina: `ReportsPageController`.
- Filters: `AudienceFilter`, `OnairFilter`, `PollFilter`, `PostFilter`, `SongRequestFilter`.
- Resources: `AudienceResource`, `OnairResource`, `UserResource`.
- Models: `Onair`, `Poll`, `Post`, `SongRequest`.

## Permissoes

- `report.module.view` controla acesso ao modulo no menu e na rota.

## Riscos

- Relatorios podem ficar pesados se filtros retornarem colecoes grandes.
- Rankings calculados em memoria podem precisar migrar para consultas otimizadas.
- Periodos de audiencia precisam ser consistentes com fuso horario.

## Planejamento

- [ ] Definir indicadores principais.
- [ ] Mapear origem dos dados.
- [ ] Registrar periodicidade de atualizacao.
- [ ] Separar metricas operacionais de metricas editoriais.

## Referencias

- [dashboard](./dashboard)
- [radio](./radio)
