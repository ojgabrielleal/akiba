# Ajustes de Locucao, Auto DJ e Programacao

Data: 2026-06-03

## Locucao e retomada de planejamento

- Analisei o fluxo de inicio e finalizacao de locucao em `StartLocutionAction` e `FinishLocutionAction`.
- Confirmei que, ao iniciar uma locucao, o sistema pausava planos `start_program` com status `running`.
- Ajustei o fluxo para registrar no novo `onair` qual plano foi pausado, usando `paused_plan_id`.
- Na finalizacao da locucao, o sistema passou a retomar somente o plano associado ao `onair` atual, em vez de retomar todos os planos pausados.
- Adicionei a migration `2026_06_03_000001_add_paused_plan_id_to_onair_table.php`.
- Atualizei o model `Onair` para aceitar `paused_plan_id` e expor a relacao `pausedPlan()`.
- Adicionei teste cobrindo que apenas o plano pausado pela locucao atual volta para `running`.

## Programas, auto DJ e frases

- Ajustei `ProgramFactory` para que frases pre-definidas existam somente em programas `auto_dj`.
- Programas `live`, `scheduled` e `playlist` passaram a ficar com `phrases = null`.
- Corrigi o enum `programs.execution_mode`, incluindo `auto_dj`.
- Atualizei a migration original de `execution_mode` para incluir `auto_dj`.
- Criei migration adicional `2026_06_03_000002_add_auto_dj_to_programs_execution_mode.php` para bancos MySQL que ja tinham rodado a migration antiga.
- Protegi essa migration para nao executar o SQL `ALTER TABLE ... MODIFY` em SQLite durante testes.
- Ajustei testes de `ProgramTest` para validar os modos `playlist`, `scheduled`, `live` e `auto_dj`.
- Ajustei o seeder de programas para manter frases apenas no auto DJ.

## Planos e seeders

- Identifiquei que o `PlanSeeder` estava gerando planos para programas `auto_dj` depois que esse modo entrou em `programs.execution_mode`.
- Corrigi o `PlanSeeder` para excluir `playlist` e `auto_dj` da geracao de planos.
- O teste de planos voltou a esperar corretamente 4 registros: 2 `start_program` e 2 `finish_program`.

## Airtimes

- O backend passou a retornar o horario original em formato `H:i:s`.
- Atualizei os testes de `AirtimeResource` para esperar `00:00:00` e `12:00:00`, pois a transformacao para textos como "meia noite" e "meio dia" foi movida para o front-end.

## UpdateProgramAction

- O teste de atualizacao de programa privado falhava porque o payload nao enviava `user`.
- Ajustei o teste para enviar `user => $user->uuid`, pois `UpdateProgramAction` exige esse campo quando `access_type` e `private`.

## Scope de programas para locucao

- Revisei `Program::availableForLocution`.
- Ajustei a regra para retornar:
  - programas `live` ativos, privados, do usuario atual;
  - todos os programas `live` ativos e `free`.
- Removi uma condicao que tornava impossivel retornar programas `free`.

## Interface de locucao

- Investiguei por que os programas nao chegavam na interface de locucao.
- Encontrei que `LocutionController` usava `cannot('viewAny', Program::class)`, mas `ProgramPolicy` nao tinha o metodo `viewAny`.
- Adicionei `viewAny()` em `ProgramPolicy`, reaproveitando a permissao de `list()`.

## Testes

- Rodei testes focados durante os ajustes.
- Rodei a suite completa com `php artisan test`.
- Resultado final da suite:
  - 100 testes passaram;
  - 1 teste ficou pulado;
  - 204 assertions.
- O teste pulado continua sendo do `CalendarTest`, por depender de SQL especifico de MySQL em ambiente SQLite.
