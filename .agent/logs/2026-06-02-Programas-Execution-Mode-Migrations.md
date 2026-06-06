# Programas Execution Mode Migrations

Foram criadas migrations para separar o tipo de acesso do programa do modo de execucao.

## Alteracoes Realizadas

### 1. Remocao de Automatic e Is Auto DJ
- Foi criada uma migration para remover a opcao `automatic` do enum `type` da tabela `programs`.
- A mesma migration remove a coluna `is_auto_dj` da tabela `programs`.
- O rollback restaura a opcao `automatic` no enum `type` e recria a coluna `is_auto_dj` com valor padrao `false`.

### 2. Novo Campo Execution Mode
- Foi criada uma migration separada para adicionar a coluna `execution_mode` na tabela `programs`.
- O campo usa enum com as opcoes `playlist`, `scheduled` e `live`.
- O valor padrao definido para novos registros e `live`.
- O rollback remove a coluna `execution_mode`.

### 3. Adequacao de Model, Factory, Seeders e Testes
- O model `Program` deixou de usar `is_auto_dj` em `$fillable` e `$casts`.
- O campo `execution_mode` foi adicionado ao `$fillable` de `Program`.
- A `ProgramFactory` passou a preencher `execution_mode` com `live` por padrao.
- O estado antigo `withAutomatic()` foi substituido por `withPlaylist()`.
- Foram adicionados estados `withScheduled()` e `withLive()` para os novos modos de execucao.
- `ProgramSeeder` passou a criar o programa automatico como `execution_mode = playlist`.
- `OnairSeeder` passou a localizar programas de playlist por `execution_mode`.
- `ProgramTest` passou a validar os estados de factory para `playlist`, `scheduled` e `live`.

### 4. Ajuste Funcional Relacionado
- `FinishLocutionAction` passou a buscar o programa de retorno por `execution_mode = playlist`.
- A verificacao de existencia do programa foi ajustada antes de acessar `phrases`.

### 5. Renomeacao de Type para Access Type em Programs
- Foi criada uma migration para renomear a coluna `type` para `access_type` na tabela `programs`.
- O rollback renomeia `access_type` de volta para `type`.
- `Program`, `ProgramFactory`, actions, request, resource, seeders e testes passaram a usar `access_type`.
- A interface de formulario e grade de programas passou a enviar e ler `access_type`.

### 6. Renomeacao de Type para Execution Mode em Onair
- Foi criada uma migration para renomear a coluna `type` para `execution_mode` na tabela `onair`.
- O rollback renomeia `execution_mode` de volta para `type`.
- `Onair`, `OnairFactory`, actions, controllers, resource e testes passaram a usar `execution_mode`.
- O formato de collection do `OnairResource` foi renomeado de `grouped_by_type` para `grouped_by_execution_mode`.
- A tela de locucao passou a ler `onair.data.execution_mode`.

## Arquivos Criados

- `database/migrations/2026_06_02_000000_remove_automatic_type_and_is_auto_dj_from_programs_table.php`
- `database/migrations/2026_06_02_000001_add_execution_mode_to_programs_table.php`
- `database/migrations/2026_06_02_000002_rename_type_to_access_type_on_programs_table.php`
- `database/migrations/2026_06_02_000003_rename_type_to_execution_mode_on_onair_table.php`
- `app/Models/Program.php`
- `app/Models/Onair.php`
- `database/factories/ProgramFactory.php`
- `database/factories/OnairFactory.php`
- `database/seeders/ProgramSeeder.php`
- `database/seeders/OnairSeeder.php`
- `app/Actions/Locution/StartLocutionAction.php`
- `app/Actions/Locution/FinishLocutionAction.php`
- `app/Actions/Program/CreateProgramAction.php`
- `app/Actions/Program/UpdateProgramAction.php`
- `app/Http/Controllers/Private/LocutionController.php`
- `app/Http/Controllers/Private/LogsController.php`
- `app/Http/Controllers/Private/RadioController.php`
- `app/Http/Requests/Radio/CreateProgramRequest.php`
- `app/Http/Resources/ProgramResource.php`
- `app/Http/Resources/OnairResource.php`
- `resources/js/pages/private/Locution.svelte`
- `resources/js/ui/widgets/private/form/ProgramForm.svelte`
- `resources/js/ui/widgets/private/grid/ProgramScheduleGrid.svelte`
- `tests/Unit/Models/ProgramTest.php`
- `tests/Unit/Models/OnairTest.php`

## Validacao

- `php -l` na migration de remocao de `automatic` e `is_auto_dj`.
- `php -l` na migration de adicao de `execution_mode`.
- `php -l` na migration de renomeacao `programs.type` para `programs.access_type`.
- `php -l` na migration de renomeacao `onair.type` para `onair.execution_mode`.
- `php -l` em `Program.php`.
- `php -l` em `Onair.php`.
- `php -l` em `ProgramFactory.php`.
- `php -l` em `OnairFactory.php`.
- `php -l` em `ProgramSeeder.php`.
- `php -l` em `OnairSeeder.php`.
- `php -l` em `StartLocutionAction.php`.
- `php -l` em `FinishLocutionAction.php`.
- `php -l` em `CreateProgramAction.php`.
- `php -l` em `UpdateProgramAction.php`.
- `php -l` em `RadioController.php`.
- `php -l` em `LogsController.php`.
- `php -l` em `ProgramResource.php`.
- `php -l` em `OnairResource.php`.
- `php -l` em `ProgramTest.php`.
- `php artisan test tests\Unit\Models\ProgramTest.php tests\Unit\Models\OnairTest.php`.
- `php artisan test tests\Unit\Models\ProgramTest.php tests\Unit\Models\OnairTest.php tests\Unit\Models\UserTest.php`.
- Nao foram encontrados erros de sintaxe.
- Os testes focados finais passaram com 29 testes e 45 assertions.

## Observacoes

- As migrations usam apenas abstracoes do Laravel via `Schema::table()` e `Blueprint`.
- Nao foi usado SQL puro nem o atalho `DB::`.
- A busca final por usos funcionais antigos de `is_auto_dj`, `withAutomatic()` e `isAutoDj()` retornou apenas migrations historicas ou rollback.
- A busca final por usos funcionais antigos de `onair.type` retornou apenas usos de outras entidades, HTML ou migrations historicas.
- Arquivos de controller e frontend ja modificados no working tree antes desta etapa foram preservados, com alteracoes apenas nos pontos necessarios para acompanhar os novos nomes de campos.

---
**Data:** 2 de Junho de 2026  
**Responsavel:** Codex (IA)
