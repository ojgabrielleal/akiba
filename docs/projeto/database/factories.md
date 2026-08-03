---
status: ativo
tipo: guia-database
atualizado_em: 2026-08-03
---

# Factories

Factories criam dados válidos para testes, seeders e cenários locais.

## Onde Ficam

```txt
database/factories
database/factories/Concerns
```

## Responsabilidade

Uma factory deve criar um model em estado válido com o mínimo de configuração manual.

Use factory para:

- testes unitários;
- testes feature;
- seeds de desenvolvimento;
- cenários com relações;
- estados alternativos de model.

## Estrutura

```php
class PostFactory extends Factory
{
    use HasFakeImages;

    public function definition(): array
    {
        return [
            'is_active' => true,
            'user_id' => User::factory(),
            'title' => fake()->text(),
            'content' => fake()->paragraph(),
        ];
    }

    public function review(): static
    {
        return $this->state(fn () => [
            'module' => 'review',
        ]);
    }
}
```

## States

Use states para variações conhecidas:

```php
Post::factory()->review()->create();
Post::factory()->event()->create();
```

No projeto, `PostFactory` tem:

```txt
review()
event()
forModule(string $module)
```

## Relações

Use factories relacionadas quando o model depende de outro:

```php
'user_id' => User::factory(),
```

Ou no teste:

```php
Post::factory()->for($user, 'author')->create();
```

## Concerns

Concerns evitam repetição entre factories.

Exemplos:

```txt
HasFakeImages
HasLocutionIcons
```

`HasFakeImages` gera URLs fake de imagem:

```php
$this->fakeImageUrl(640, 360)
```

## Checklist

- A factory cria um registro válido sem ajustes extras?
- Relações obrigatórias são criadas automaticamente?
- States representam variações reais do domínio?
- Dados fake não dependem de serviço externo?
- Concerns evitam duplicação útil?
