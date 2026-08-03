---
status: rascunho
tipo: modulo
area: radio
atualizado_em: 2026-08-03
---

# Radio

## Objetivo

Gerenciar a estrutura da radio, incluindo programas, musicas, ranking e listener do mes.

## Escopos Atuais

- Programas.
- Musicas.
- Ranking musical.
- Listener do mes.
- Grade de programacao.
- Auto DJ.

## Funcionalidades

- Listar usuarios para associacao com programas.
- Listar programas ativos agrupados.
- Carregar apresentador, horarios e proximas execucoes de programas.
- Exibir ranking musical por total de pedidos.
- Buscar listener mais ativo do mes e listener atual cadastrado.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Radio.svelte`.
- Controller de pagina: `RadioPageController`.
- Controllers de recurso: `ProgramController`, `MusicController`, `ListenerMonthController`.
- Invokes: `DeactivateProgramController`, `RefreshMusicRankingController`.
- Filters: `ProgramFilter`, `MusicFilter`, `UserFilter`.
- Models: `Program`, `ProgramAirtime`, `ProgramSchedule`, `Music`, `ListenerMonth`, `User`.

## Permissoes

- `radio.module.view` controla acesso ao modulo no menu.
- Regras de programas ficam em `ProgramPolicy`.
- Regras de musicas ficam em `MusicPolicy`.
- Regras de listener do mes ficam em `ListenerMonthPolicy`.

## Riscos

- Relação entre programa, grade, execucao ao vivo e Auto DJ precisa continuar bem documentada.
- Ranking musical depende de pedidos e pode gerar divergencia se o criterio mudar.
- Alteracoes em horarios podem afetar locucao e relatorios.

## Planejamento

- [ ] Documentar regras de programas ao vivo e Auto DJ.
- [ ] Descrever relacionamento entre programas e horarios.
- [ ] Registrar criterios do ranking musical.
- [ ] Mapear fluxo do listener do mes.

## Referencias

- [locucao](./locucao)
- [dashboard](./dashboard)
