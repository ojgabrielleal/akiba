# Renomeacao de Tabelas de Perfil de Usuario

Foi registrada a alteracao de nomes das tabelas relacionadas ao perfil do usuario, mantendo a estrutura e os dados existentes intactos por meio de uma migration dedicada apenas a renomeacao.

## Alteracoes Realizadas

### 1. Migrations de Renomeacao
- Foram criadas 3 migrations distintas, uma para cada tabela afetada.
- Todas as migrations usam exclusivamente `Schema::rename`, sem SQL puro e sem `DB::`.
- `database/migrations/2026_05_16_144442_rename_users_socials_table_to_socials_table.php` renomeia:
  - `users_socials` para `socials`.
- `database/migrations/2026_05_16_144443_rename_users_preferences_table_to_preferences_table.php` renomeia:
  - `users_preferences` para `preferences`.
- `database/migrations/2026_05_16_144444_rename_users_favorites_table_to_favorities_table.php` renomeia:
  - `users_favorites` para `favorities`.
- Cada migration possui `down` proprio para restaurar apenas a tabela correspondente.

### 2. Models
- `app/Models/UserSocial.php` foi renomeado para `app/Models/Social.php`.
- `app/Models/UserPreference.php` foi renomeado para `app/Models/Preference.php`.
- `app/Models/UserFavorite.php` foi renomeado para `app/Models/Favority.php`.
- Os novos models apontam para `socials`, `preferences` e `favorities`.
- O model `User` passou a usar `Social`, `Preference` e `Favority` nos relacionamentos.
- Os relacionamentos, fillables, casts e comportamento de UUID foram preservados.

### 3. Factories e Seeders
- `database/factories/UserSocialFactory.php` foi renomeada para `database/factories/SocialFactory.php`.
- `database/factories/UserPreferenceFactory.php` foi renomeada para `database/factories/PreferenceFactory.php`.
- `database/factories/UserFavoriteFactory.php` foi renomeada para `database/factories/FavorityFactory.php`.
- `database/factories/UserFactory.php` passou a criar registros padrao usando `Social`, `Preference` e `Favority`.
- Nao foi necessario alterar seeders para estas tabelas, pois nao havia referencia direta operacional aos nomes antigos nelas.

### 4. Testes
- Os testes dos models foram renomeados para acompanhar os novos models:
  - `tests/Unit/Models/SocialTest.php`;
  - `tests/Unit/Models/PreferenceTest.php`;
  - `tests/Unit/Models/FavorityTest.php`.
- Foram adicionadas assercoes nos testes dos models para garantir que:
- `Social` usa `socials`;
- `Preference` usa `preferences`;
- `Favority` usa `favorities`.
- Os testes de relacionamento existentes foram mantidos para continuar cobrindo o uso das factories e relacoes Eloquent.
- `tests/Unit/Models/UserTest.php` passou a usar `Social` e `Preference` nos relacionamentos correspondentes.

### 5. Validacao
- A sintaxe PHP foi validada com sucesso nos arquivos alterados:
  - migrations de renomeacao;
  - `app/Models/Social.php`;
  - `app/Models/Preference.php`;
  - `app/Models/Favority.php`.
- A execucao dos testes de model foi tentada, mas o ambiente local esta apontando para o banco MySQL `testing`, que nao existe:
  - erro: `SQLSTATE[HY000] [1049] Unknown database 'testing'`.

---
**Data:** 16 de Maio de 2026  
**Responsavel:** Codex (IA)
