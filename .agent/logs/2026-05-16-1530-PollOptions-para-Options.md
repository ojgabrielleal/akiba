# PollOptions para Options

Foi registrada a renomeacao das opcoes de enquetes para `options`, mantendo o padrao de migrations pequenas e focadas em renomeacao.

## Alteracoes Realizadas

### 1. Migration de Renomeacao
- Foi criada `database/migrations/2026_05_16_153000_rename_polls_options_table_to_options_table.php`.
- A migration usa exclusivamente `Schema::rename`, sem SQL puro e sem `DB::`.
- A tabela `polls_options` foi renomeada para `options`.
- O `down` restaura `options` para `polls_options`.

### 2. Models, Factory e Resource
- `app/Models/PollOption.php` foi renomeado para `app/Models/Option.php`.
- `database/factories/PollOptionFactory.php` foi renomeada para `database/factories/OptionFactory.php`.
- `app/Http/Resources/PollOptionResource.php` foi renomeado para `app/Http/Resources/OptionResource.php`.
- `Option` passou a apontar para a tabela `options`.
- `Poll::options()` passou a usar `Option`.

### 3. Fluxos Atualizados
- `PollResource` passou a usar `OptionResource`.
- `PollSeeder` passou a criar `Option` via relacionamento `options`.
- `MediaController` passou a usar `Option` no voto.

### 4. Testes
- `tests/Unit/Models/PollTest.php` passou a usar `Option`.
- Foi adicionada assercao garantindo que `Option` usa a tabela `options`.

### 5. Validacao
- A sintaxe PHP foi validada nos arquivos PHP alterados.
- A execucao dos testes segue bloqueada pelo ambiente local apontar para o banco MySQL `testing`, que nao existe:
  - erro: `SQLSTATE[HY000] [1049] Unknown database 'testing'`.

---
**Data:** 16 de Maio de 2026  
**Responsavel:** Codex (IA)
