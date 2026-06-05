# Reviews como Extensao de Posts

Foi registrada a etapa de transformacao de `reviews` em uma tabela extensora de `posts`, mantendo em `reviews` apenas os dados especificos da obra revisada.

## Alteracoes Realizadas

### 1. Migration de Transformacao
- Foi criada `database/migrations/2026_05_16_151000_transform_reviews_table_into_posts_extension.php`.
- A migration usa apenas abstracoes comuns do Laravel:
  - `Schema::table`;
  - `Blueprint`;
  - `foreignId`;
  - `constrained`;
  - `cascadeOnDelete`;
  - `dropForeign`;
  - `dropColumn`.
- Nao foi usado SQL puro e nao foi usado `DB::`.
- A tabela `reviews` passou a receber `post_id` como relacionamento com `posts`.
- Foram removidos da tabela `reviews` os campos que passam a pertencer ao post base:
  - `is_active`;
  - `user_id`;
  - `slug`;
  - `cover`;
  - `image`;
  - `title`.
- A tabela `reviews` passa a manter apenas:
  - `id`;
  - `uuid`;
  - `post_id`;
  - `year_of_release`;
  - `sinopse`;
  - `created_at`;
  - `updated_at`.
- `post_id` foi criado como nullable para permitir aplicar a migration em producao sem falhar caso existam reviews antigas sem post associado ainda.

### 2. Models
- `app/Models/Review.php` foi simplificado para representar apenas a extensao de review.
- `Review` passou a expor o relacionamento `post()`.
- `Review` passou a usar `contents()` como nome principal para os registros de `ReviewContent`.
- `Review::reviews()` foi mantido como alias temporario de compatibilidade para `contents()`.
- `app/Models/Post.php` passou a expor o relacionamento `review()`.

### 3. Factories e Seeders
- `database/factories/ReviewFactory.php` passou a representar apenas os dados proprios da extensao de review:
  - `year_of_release`;
  - `sinopse`.
- A associacao com `Post` deve ser feita explicitamente por quem usa a factory, via relacionamento `post`.
- `database/seeders/ReviewSeeder.php` foi atualizado para criar `Post` como publicacao base.
- Cada post de review criado pelo seeder recebe a extensao `Review` via relacionamento `review`.
- Os conteudos da review continuam sendo criados em `reviews_contents`, agora via relacionamento `contents`.

### 4. Testes
- `tests/Unit/Models/ReviewTest.php` foi atualizado para validar:
  - `Review -> Post`;
  - `Review -> contents`;
  - nome da tabela `reviews`.
- `tests/Unit/Models/ReviewContentTest.php` passou a criar `Review` associada explicitamente a um `Post`.
- `tests/Unit/Models/PostTest.php` passou a cobrir o relacionamento `Post -> Review`.
- `tests/Unit/Models/UserTest.php` foi ajustado para validar conteudos de review do usuario com a review associada a um post.

### 5. Validacao
- A sintaxe PHP foi validada com sucesso nos arquivos alterados:
  - migration de transformacao de `reviews`;
  - `app/Models/Review.php`;
  - `app/Models/Post.php`;
  - `database/factories/ReviewFactory.php`;
  - `database/seeders/ReviewSeeder.php`;
  - `tests/Unit/Models/ReviewTest.php`;
  - `tests/Unit/Models/ReviewContentTest.php`;
  - `tests/Unit/Models/PostTest.php`;
  - `tests/Unit/Models/UserTest.php`.
- A execucao dos testes segue bloqueada pelo ambiente local apontar para o banco MySQL `testing`, que nao existe:
  - erro: `SQLSTATE[HY000] [1049] Unknown database 'testing'`.

---
**Data:** 16 de Maio de 2026  
**Responsavel:** Codex (IA)
