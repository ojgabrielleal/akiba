---
status: ativo
tipo: guia-database
atualizado_em: 2026-08-03
---

# Banco de Dados

O banco usa MySQL 8. A estrutura fica nas migrations e os dados iniciais ficam nos seeders.

## Migrations

```txt
database/migrations
```

Use migrations para:

- criar tabelas;
- alterar colunas;
- criar índices;
- criar chaves estrangeiras;
- remover estruturas antigas com cuidado.

Arquitetura esperada:

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('title');
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

## Índices e Relações

Crie índices quando o campo for usado com frequência em busca, filtro ou ordenação.

Exemplos:

```php
$table->string('status')->index();
$table->foreignId('user_id')->constrained();
$table->timestamp('published_at')->nullable()->index();
```

Se o model terá relação `belongsTo`, normalmente a migration deve ter `foreignId`.

## Seeders

```txt
database/seeders
```

Use seeders para:

- dados iniciais;
- permissões;
- usuários de desenvolvimento;
- dados necessários para o painel funcionar localmente.

Seeders devem ser previsíveis. Evite depender de ordem frágil ou de dados que podem não existir.

Veja detalhes em [Seeders](./seeders).

## Factories

```txt
database/factories
```

Use factories para gerar dados em testes, seeds ou cenários locais.

Factory boa cria um model válido sem exigir muitos campos manuais.

Veja detalhes em [Factories](./factories).

## Ordem ao Alterar Banco

1. Criar migration.
2. Atualizar model.
3. Atualizar factory, se existir.
4. Atualizar seeder, se for dado inicial.
5. Atualizar action/filter/resource.
6. Atualizar interface.
7. Rodar migrations no ambiente local quando necessário.

## Checklist

- A migration tem `up()` e `down()` coerentes?
- Campos usados em busca têm índice quando necessário?
- Relações batem com os models?
- Seeder não depende de dado frágil sem fallback?
- Factory cria estado válido do model?
