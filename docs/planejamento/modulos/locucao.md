---
status: rascunho
tipo: modulo
area: locucao
atualizado_em: 2026-08-03
---

# Locucao

## Objetivo

Controlar a experiencia de locucao ao vivo, incluindo inicio e encerramento de programas e gerenciamento de pedidos de musica.

## Escopos Atuais

- Iniciar locucao.
- Finalizar locucao.
- Abrir ou fechar caixa de pedidos.
- Marcar pedido como tocado.
- Marcar pedido como cancelado.

## Funcionalidades

- Listar programas disponiveis para o usuario iniciar locucao.
- Identificar transmissao ao vivo atual.
- Carregar pedidos associados a transmissao atual.
- Ordenar pedidos por chegada.
- Controlar estado dos pedidos durante a locucao.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Locution.svelte`.
- Controller de pagina: `LocutionPageController`.
- Invokes: `StartLocutionController`, `FinishLocutionController`, `ToggleSongRequestBoxStatusController`, `MarkSongRequestAsPlayedController`, `MarkSongRequestAsCanceledController`.
- Actions: `StartLocutionAction`, `FinishLocutionAction`, `ToggleSongRequestBoxStatusAction`, `MarkSongRequestAsPlayedAction`, `MarkSongRequestAsCanceledAction`.
- Filters: `OnairFilter`, `ProgramFilter`, `SongRequestFilter`.
- Models: `Onair`, `Program`, `SongRequest`.

## Permissoes

- `locution.module.view` controla acesso ao modulo no menu.
- Programas disponiveis dependem do usuario autenticado.
- Pedidos usam autorizacao de `SongRequestPolicy`.

## Riscos

- Deve existir apenas uma transmissao ao vivo coerente por contexto de radio.
- Encerrar locucao precisa preservar historico e audiencia.
- Pedidos nao podem ficar em estado indefinido quando a locucao termina.

## Planejamento

- [ ] Documentar estados possiveis da locucao.
- [ ] Definir regras de transicao entre programas.
- [ ] Mapear relacao entre locucao, programa, grade e Auto DJ.
- [ ] Registrar comportamento esperado para pedidos de musica.

## Referencias

- [radio](./radio)
