---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Models

Models representam tabelas, relacionamentos e comportamentos diretamente ligados aos dados.

## Onde Ficam

```txt
app/Models
app/Models/Concerns
```

## Arquitetura do Arquivo

```txt
namespace
imports

class Nome extends Model
{
    traits

    fillable
    hidden
    appends
    casts

    relacionamentos

    accessors e mutators

    scopes

    métodos simples de domínio
}
```

## Responsabilidade

Um model deve representar o dado e suas relações diretas:

- quais campos podem ser preenchidos;
- quais campos precisam de cast;
- quais relações existem;
- quais atributos derivados são simples;
- quais scopes ajudam consultas comuns.

O model é bom para regras pequenas e naturais do dado. Para processos com vários passos, use uma action.

## Exemplo

```php
class Post extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this->hasMany(PostTag::class);
    }
}
```

## Relacionamentos

Coloque relacionamentos juntos e com nomes claros:

```php
public function author()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function comments()
{
    return $this->hasMany(PostComment::class);
}

public function tags()
{
    return $this->hasMany(PostTag::class);
}
```

Prefira nomes que expressem o domínio da tela. Se a tabela usa `user_id`, mas no produto o usuário é o autor, `author()` é mais legível que `user()`.

## Casts

Use casts para campos que não devem chegar crus ao PHP:

```php
protected function casts(): array
{
    return [
        'metadata' => 'array',
        'published_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
```

## Scopes

Use scopes para consultas pequenas e recorrentes:

```php
public function scopeActive($query)
{
    return $query->where('active', true);
}
```

Se o filtro depende de muitos parâmetros de tela, prefira criar ou usar um `Filter`.

## O Que Não Colocar no Model

- fluxo longo de criação ou atualização;
- validação de request;
- regra de permissão;
- chamada para API externa;
- montagem de props para tela.

Para isso, use `Action`, `Request`, `Policy`, `Service`, `Resource` ou `Controller`.

## Checklist

- Campos graváveis estão em `$fillable`?
- Campos JSON, booleanos e datas têm cast?
- Relacionamentos têm nomes do domínio?
- Scopes são pequenos e reutilizáveis?
- Processos longos foram para actions?
