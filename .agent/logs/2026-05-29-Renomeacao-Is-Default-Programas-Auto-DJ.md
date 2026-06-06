# Renomeacao Is Default Programas Auto DJ

Foi renomeado o campo `is_default` da tabela `programs` para `is_auto_dj`, deixando o nome alinhado ao uso real do programa como Auto DJ.

## Alteracoes Realizadas

### 1. Migration de Renomeacao
- Foi criada uma nova migration para renomear a coluna `is_default` para `is_auto_dj` na tabela `programs`.
- A migration usa apenas as abstracoes do Laravel via `Schema::table()` e `Blueprint::renameColumn()`.
- O rollback renomeia `is_auto_dj` de volta para `is_default`.

### 2. Ajustes de Codigo
- O model `Program` passou a usar `is_auto_dj` em `$fillable` e `$casts`.
- A `ProgramFactory` passou a preencher `is_auto_dj`.
- O estado de factory foi renomeado de `isDefault()` para `isAutoDj()`.
- `ProgramSeeder`, `OnairSeeder` e `FinishLocutionAction` passaram a consultar `is_auto_dj`.

## Validacao

- Foi realizada busca por `is_default` e `isAutoDj` nos arquivos funcionais.
- Os usos antigos restantes de `is_default` estao restritos a migrations historicas, rollback da migration nova e tabela `automatic`, que nao faz parte desta renomeacao.

---
**Data:** 29 de Maio de 2026  
**Responsavel:** Codex (IA)
