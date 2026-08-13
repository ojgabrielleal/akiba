---
status: em_validacao
tipo: experimento
atualizado_em: 2026-08-12
---

# Experimento: grade semanal da radio publica

## Contexto

A grade de programacao da pagina publica de radio deixou de ser filtrada por tipo de programa e passou a ser filtrada por dia da semana.

O objetivo do experimento e facilitar a leitura da programacao como calendario semanal normal, com dias no topo, de segunda a domingo.

## Estado atual do experimento

- `resources/js/lib/widgets/public/grid/RadioProgramGrid.svelte`
  - Mantem o estilo visual anterior dos cards de programa.
  - Substitui os filtros de formato (`Ao vivo`, `Gravados`, `Automaticos`) por filtros de dia (`Segunda` ate `Domingo`).
  - Mostra somente programas do dia selecionado.
  - Ordena os programas do dia por horario.
  - Exibe os horarios do Brasil no card:
    - `BRT · Brasilia`
    - `AMT Amazonas`
    - `ACT Acre`
  - Converte AMT e ACT a partir do horario de Brasilia e indica o dia quando houver virada.

- `app/Http/Controllers/Public/RadioController.php`
  - A pagina publica de radio passa a buscar programas ativos ao vivo (`execution_mode = live`) com horarios publicos.
  - Remove a prop `activeProgramMode`.
  - Remove o filtro via query string `program_mode`.
  - Remove a paginacao da programacao para permitir o filtro local por dia.

- `resources/js/pages/public/Radio.svelte`
  - `RadioProgramGrid` recebe apenas `programs`.

## Reversao caso os administradores nao aprovem

Para voltar ao comportamento anterior:

1. Em `RadioProgramGrid.svelte`
   - Restaurar os filtros por formato:
     - `Ao vivo`
     - `Gravados`
     - `Automaticos`
   - Restaurar uso de `Link` do Inertia com `program_mode`.
   - Restaurar `Pagination`.
   - Voltar a listar `selectedPrograms` diretamente, sem filtrar por dia.
   - Voltar a exibir `item.airtimes` e, quando nao houver, `item.schedules`.
   - Remover conversao de fusos `BRT`, `AMT` e `ACT`.

2. Em `RadioController.php`
   - Restaurar `resolveProgramMode(Request $request)`.
   - Restaurar `activeProgramMode` nas props.
   - Restaurar filtro `execution_mode` vindo de `resolveProgramMode($request)`.
   - Restaurar `public_schedule` apenas para modo `live`.
   - Restaurar `paginate => 8`.

3. Em `Radio.svelte`
   - Restaurar leitura de `activeProgramMode` em `$page.props`.
   - Passar `{activeProgramMode}` para `RadioProgramGrid`.

## Decisao pendente

- Validar com administradores se a leitura por dia deve substituir definitivamente a leitura por tipo de programa.
- Validar se a exibicao de tres fusos brasileiros deve permanecer ou se deve voltar a mostrar apenas horario de Brasilia.
