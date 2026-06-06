# Events como Extensao de Posts

Foi registrada a primeira etapa da unificacao de publicacoes, transformando `events` em uma tabela extensora de `posts`.

## Alteracoes Realizadas

### 1. Migration de Transformacao
- Foi criada `database/migrations/2026_05_16_150000_transform_events_table_into_posts_extension.php`.
- A migration foi organizada com um unico `Schema::table('events', ...)` no `up` e um unico `Schema::table('events', ...)` no `down`, pois todas as operacoes afetam a mesma tabela.
- A migration usa apenas abstracoes comuns do Laravel:
  - `Schema::table`;
  - `Blueprint`;
  - `foreignId`;
  - `constrained`;
  - `cascadeOnDelete`;
  - `dropForeign`;
  - `dropColumn`.
- Nao foi usado SQL puro e nao foi usado `DB::`.
- A tabela `events` passou a receber `post_id` como relacionamento com `posts`.
- Foram removidos da tabela `events` os campos que passam a pertencer ao post base:
  - `is_active`;
  - `user_id`;
  - `cover`;
  - `image`;
  - `slug`;
  - `title`;
  - `type`;
  - `content`.
- A tabela `events` passa a manter apenas:
  - `id`;
  - `uuid`;
  - `post_id`;
  - `dates`;
  - `address`;
  - `created_at`;
  - `updated_at`.
- `post_id` foi criado como nullable para permitir aplicar a migration em producao sem falhar caso existam eventos antigos sem post associado ainda.

### 2. Models
- `app/Models/Event.php` foi simplificado para representar apenas a extensao de evento.
- `Event` passou a expor o relacionamento `post()`.
- Foram removidos de `Event` os scopes e relacionamentos baseados em campos que sairam da tabela.
- `app/Models/Post.php` passou a expor o relacionamento `event()`.
- `app/Models/User.php` passou a resolver `events()` via `hasManyThrough(Event::class, Post::class, ...)`.

### 3. Factories
- `database/factories/EventFactory.php` passou a representar apenas os dados proprios da extensao de evento.
- A associacao com `Post` deve ser feita explicitamente por quem usa a factory, via relacionamento `post`.
- A factory manteve apenas os campos proprios de `events`:
  - `dates`;
  - `address`.

### 4. Seeders
- `database/seeders/EventSeeder.php` foi atualizado para criar `Post` como publicacao base.
- Cada post de evento criado pelo seeder recebe a extensao `Event` via relacionamento `event`.
- O seeder deixou de tentar associar `Event` diretamente ao usuario por `author`, pois essa relacao agora pertence ao `Post`.

### 5. Testes
- `tests/Unit/Models/EventTest.php` foi atualizado para validar o relacionamento `Event -> Post`.
- Os testes antigos de `Event` baseados em `author`, `active` e `slug` foram removidos, pois estes dados passam a pertencer a `Post`.
- `tests/Unit/Models/PostTest.php` passou a cobrir o relacionamento `Post -> Event`.
- `tests/Unit/Models/UserTest.php` foi ajustado para validar eventos do usuario via posts.

### 6. Validacao
- A sintaxe PHP foi validada com sucesso nos arquivos alterados:
  - migration de transformacao de `events`;
  - `app/Models/Event.php`;
  - `app/Models/Post.php`;
  - `app/Models/User.php`;
  - `database/factories/EventFactory.php`;
  - `database/seeders/EventSeeder.php`;
  - `tests/Unit/Models/EventTest.php`;
  - `tests/Unit/Models/PostTest.php`;
  - `tests/Unit/Models/UserTest.php`.
- A execucao dos testes segue bloqueada pelo ambiente local apontar para o banco MySQL `testing`, que nao existe:
  - erro: `SQLSTATE[HY000] [1049] Unknown database 'testing'`.

---
**Data:** 16 de Maio de 2026  
**Responsavel:** Codex (IA)
