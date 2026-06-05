# Plans Base

Foi criada a base da tabela `plans` para representar a fila de acoes futuras do sistema.

## Alteracoes Realizadas

### 1. Migration de Plans
- Foi criada a tabela `plans`.
- A tabela usa `uuid`, relacionamento com `users`, relacionamento polimorfico `plannable`, `root_id`, `action`, `scheduled_at` e `status`.
- `root_id` referencia a propria tabela `plans` e permite vincular acoes do mesmo contexto.
- `action` ficou como `string` para permitir evolucao para programas, posts e outros tipos de acoes.
- `status` ficou como enum com os estados `pending`, `running`, `paused`, `completed`, `cancelled`, `failed` e `expired`.
- Nao foram adicionados `ends_at` nem `metadata` nesta etapa.

### 2. Model e Factory
- Foi criado o model `Plan`.
- Foram adicionados relacionamentos com `user`, `plannable`, `root` e `children`.
- Foi criada `PlanFactory` com `Program` como plannable padrao.
- Foi adicionado o estado `finishProgram()` na factory.

### 3. Relacionamentos Inversos
- `User` recebeu relacionamento `plans()`.
- `Program` recebeu relacionamento polimorfico `plans()`.

### 4. Testes
- Foi criado `PlanTest`.
- Os testes cobrem relacionamento com usuario, relacionamento polimorfico, plano raiz e planos filhos.

### 5. Seed Inicial de Plans
- Foi criado `PlanSeeder`.
- O seeder cria pares de planos `start_program` e `finish_program` para programas ativos que nao sejam playlist.
- Todos os agendamentos criados pelo seeder ficam no futuro.
- O plano `finish_program` recebe `root_id` apontando para o plano `start_program`.
- `PlanSeeder` foi adicionado ao fluxo de `DatabaseSeeder` apos `ProgramSeeder`.

### 6. Frases em ProgramFactory
- `ProgramFactory` passou a preencher `phrases` em todos os estados principais.
- As frases geradas incluem `text`, `icon`, `decoration` e `texture`.

### 7. Remocao de Horarios em Onair
- Foi criada uma migration para remover as colunas `start_at` e `finish_at` da tabela `onair`.
- O rollback recria `start_at` e `finish_at` como `dateTime` nullable.
- `Onair`, `OnairFactory` e `OnairResource` deixaram de expor/preencher esses campos.
- A listagem de programas agendados em `RadioController` deixou de consultar `onair.finish_at` e `onair.start_at`.
- `ProgramGrid` deixou de renderizar `start_at` e `finish_at` vindos de `onair`.

### 8. Simplificacao de OnairResource
- `OnairResource` deixou de usar `HasFormats`.
- A logica de collection `grouped_by_execution_mode` foi removida.
- `RadioController` passou a retornar a estrutura vazia de programados diretamente, ate a listagem ser ligada a `plans`.

### 9. Agrupamento por Execution Mode em ProgramResource
- `ProgramResource` passou a usar `HasFormats`.
- Foi adicionado o formato `grouped_by_execution_mode`.
- O formato agrupa programas em `live`, `scheduled` e `playlist` com base em `programs.execution_mode`.
- `RadioController` passou a retornar os programas categorizados por execution mode.
- `ProgramGrid` passou a consumir `programmed.data.live`, `programmed.data.scheduled` e `programmed.data.playlist`.

### 10. Reuso de Index Programs
- O metodo separado `indexProgramsByExecutionMode()` foi removido de `RadioController`.
- `indexPrograms()` passou a aceitar um formato opcional.
- A prop `programmed` passou a usar `indexPrograms('grouped_by_execution_mode')`.

### 11. PlanResource em ProgramResource
- Foi criado `PlanResource`.
- `ProgramResource` passou a devolver `plans` quando o relacionamento estiver carregado.
- `RadioController` passou a carregar `plans.user` e `plans.root` junto dos programas.
- `PlanResource` expoe `uuid`, `root_uuid`, `action`, `scheduled_at`, `status` e `user`.

### 12. Formato Summary em PlanResource
- `PlanResource` passou a usar `HasFormats`.
- Foi adicionado o formato `summary`.
- O formato `summary` retorna apenas `uuid` e `scheduled_at`.

### 13. Airtimes e Execution Modes no ProgramSeeder
- `ProgramSeeder` passou a criar programas nos tres `execution_mode`: `live`, `scheduled` e `playlist`.
- Programas `live` criados pelo seeder passam a receber horarios em `airtimes`.
- Programas `playlist` nao recebem `airtimes`.
- Foi adicionada cobertura em `ProgramTest` para validar os horarios e os modos gerados pelo seeder.

### 14. Meio Dia e Meia Noite em AirtimeResource
- `AirtimeResource` passou a tratar `00:00` como `meia noite`.
- `AirtimeResource` passou a tratar `12:00` como `meio dia`.
- Foram adicionados testes focados para os dois formatos.

### 15. Simplificacao do Estado em ProgramGrid
- `ProgramGrid` deixou de guardar uma lista `object` dentro do estado selecionado.
- O componente passou a guardar apenas `selectedExecutionMode`.
- A lista exibida passou a ser derivada de `programs.data[selectedExecutionMode]`.

### 16. Filtro de Plans Nao Executados
- Foi adicionado o scope `unexecuted()` em `Plan`.
- O scope retorna plans com status `pending`, `running` ou `paused`.
- `RadioController` passou a carregar somente `plans` nao executados nos programas.
- Os plans carregados sao ordenados por `scheduled_at`.
- Foi adicionada cobertura em `PlanTest` para o scope.

### 17. Summary em PlanResource
- O filtro de planos nao executados ficou no scope `Plan::unexecuted()` e no controller.
- `PlanResource` manteve apenas o formato `summary` para retornar `uuid` e `scheduled_at`.
- `ProgramResource` passou a usar `PlanResource::collection(...)->format('summary')`.
- Foi adicionada cobertura em `PlanTest` para o formato `summary`.

## Arquivos Criados

- `database/migrations/2026_06_02_000004_create_plans_table.php`
- `database/migrations/2026_06_02_000005_remove_schedule_columns_from_onair_table.php`
- `app/Models/Plan.php`
- `database/factories/PlanFactory.php`
- `database/seeders/PlanSeeder.php`
- `app/Http/Resources/PlanResource.php`
- `tests/Unit/Models/PlanTest.php`

## Arquivos Atualizados

- `app/Models/User.php`
- `app/Models/Program.php`
- `app/Models/Onair.php`
- `database/factories/ProgramFactory.php`
- `database/seeders/ProgramSeeder.php`
- `database/factories/OnairFactory.php`
- `database/seeders/DatabaseSeeder.php`
- `app/Http/Controllers/Private/RadioController.php`
- `app/Http/Resources/ProgramResource.php`
- `app/Http/Resources/AirtimeResource.php`
- `app/Http/Resources/OnairResource.php`
- `resources/js/ui/widgets/private/grid/ProgramGrid.svelte`
- `tests/Unit/Models/ProgramTest.php`
- `tests/Unit/Models/AirtimeTest.php`

## Validacao

- `php -l` na migration de `plans`.
- `php -l` em `Plan.php`.
- `php -l` em `PlanFactory.php`.
- `php -l` em `PlanSeeder.php`.
- `php -l` em `PlanTest.php`.
- `php -l` em `ProgramResource.php`.
- `php -l` em `ProgramFactory.php`.
- `php -l` em `ProgramTest.php`.
- `php -l` em `ProgramSeeder.php`.
- `php -l` em `AirtimeResource.php`.
- `php -l` em `AirtimeTest.php`.
- `php -l` na migration de remocao de `onair.start_at` e `onair.finish_at`.
- `php -l` em `Onair.php`.
- `php -l` em `OnairFactory.php`.
- `php -l` em `OnairResource.php`.
- `php -l` em `RadioController.php`.
- `php -l` em `ProgramResource.php`.
- `php -l` em `RadioController.php` apos remover `indexProgramsByExecutionMode()`.
- `php -l` em `PlanResource.php`.
- `php -l` em `PlanResource.php` apos adicionar o formato `summary`.
- `php -l` em `Plan.php`.
- `php -l` em `PlanTest.php`.
- `php artisan test tests\Unit\Models\PlanTest.php tests\Unit\Models\ProgramTest.php tests\Unit\Models\UserTest.php`.
- `php artisan test tests\Unit\Models\PlanTest.php tests\Unit\Models\ProgramTest.php`.
- `php artisan test tests\Unit\Models\OnairTest.php tests\Unit\Models\ProgramTest.php tests\Unit\Models\PlanTest.php`.
- `php artisan test tests\Unit\Models\OnairTest.php tests\Unit\Models\ProgramTest.php`.
- `php artisan test tests\Unit\Models\ProgramTest.php tests\Unit\Models\PlanTest.php`.
- `php artisan test tests\Unit\Models\ProgramTest.php tests\Unit\Models\PlanTest.php` apos reusar `indexPrograms()`.
- `php artisan test tests\Unit\Models\PlanTest.php tests\Unit\Models\ProgramTest.php` apos linkar `PlanResource`.
- `php artisan test tests\Unit\Models\ProgramTest.php tests\Unit\Models\PlanTest.php` apos ajustar `ProgramSeeder`.
- `php artisan test tests\Unit\Models\PlanTest.php tests\Unit\Models\ProgramTest.php` apos adicionar o filtro de plans nao executados.
- `php artisan test tests\Unit\Models\PlanTest.php tests\Unit\Models\ProgramTest.php` apos manter o filtro no scope/controller.
- `php artisan test tests\Unit\Models\AirtimeTest.php`.
- Os testes focados passaram com 29 testes e 42 assertions.
- Os testes focados finais passaram com 12 testes e 22 assertions.
- Os testes focados apos remover horarios de `onair` passaram com 16 testes e 30 assertions.
- Os testes focados apos simplificar `OnairResource` passaram com 11 testes e 21 assertions.
- Os testes focados apos mover o agrupamento para `ProgramResource` passaram com 12 testes e 22 assertions.
- Os testes focados apos reusar `indexPrograms()` passaram com 12 testes e 22 assertions.
- Os testes focados apos linkar `PlanResource` passaram com 12 testes e 22 assertions.
- Os testes focados apos ajustar `ProgramSeeder` passaram com 13 testes e 27 assertions.
- Os testes focados apos adicionar o filtro de plans nao executados passaram com 14 testes e 34 assertions.
- Os testes focados apos manter o filtro no scope/controller passaram com 15 testes e 38 assertions.
- Os testes focados de `AirtimeTest` passaram com 3 testes e 3 assertions.

## Observacoes

- A fonte de programacoes futuras passa a ser `plans`; a listagem de programados ainda precisa ser ligada a essa nova tabela em uma etapa posterior.

---
**Data:** 2 de Junho de 2026  
**Responsavel:** Codex (IA)
