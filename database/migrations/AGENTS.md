# Regras para migrations

- Use sempre as abstrações do Laravel (`Schema`, `Blueprint`, `foreignId`, `constrained`, índices e helpers equivalentes) para criação e alteração de tabelas.
- Use `DB::` somente quando a migration for migrar dados existentes.
- Toda migração de dados deve ser feita em uma migration separada da migration de estrutura.
- Não misture criação/alteração de schema com transformação de dados no mesmo arquivo.
