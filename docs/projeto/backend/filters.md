---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Filters

Filters centralizam consultas reutilizáveis. Eles deixam controllers menores e evitam repetição de filtros de listagem.

## Onde Ficam

```txt
app/Filters
```

Exemplos:

```txt
PostFilter.php
UserFilter.php
TaskFilter.php
ProgramFilter.php
SongRequestFilter.php
```

## Arquitetura do Arquivo

```txt
namespace
imports

class NomeFilter
{
    apply(array $filters = [])

    métodos privados para filtros específicos

    retorno com query, collection ou paginação
}
```

## Responsabilidade

Um filter deve transformar parâmetros simples em consulta.

Ele pode cuidar de:

- busca textual;
- filtros por status;
- filtros por usuário;
- filtros por módulo;
- `with` e `withCount`;
- ordenação;
- paginação.

O controller decide quais parâmetros passar. O filter decide como esses parâmetros viram query.

Filters devem usar `when()` do Eloquent para manter condicionais claras e aplicar ordenação padrão quando fizer sentido.

## Exemplo de Uso

```php
$this->postFilter->apply([
    'user' => request()->user(),
    'active' => true,
    'with' => ['author', 'reviews.author'],
    'with_count' => 'views',
    'search' => request()->input('search'),
    'paginate' => 10,
]);
```

## Exemplo de Estrutura

```php
class PostFilter
{
public function apply(array $filters)
    {
        $query = Post::query();

        $query->when($filters['active'] ?? null, fn ($query) => $query->where('active', true));
        $query->when($filters['search'] ?? null, fn ($query, $search) => $this->search($query, $search));
        $query->when($filters['with'] ?? null, fn ($query, $with) => $query->with($with));
        $query->when($filters['with_count'] ?? null, fn ($query, $withCount) => $query->withCount($withCount));

        if ($filters['paginate'] ?? null) {
            return $query->paginate($filters['paginate']);
        }

        return $query->get();
    }

    private function search($query, string $search)
    {
        return $query->where('title', 'like', "%{$search}%");
    }
}
```

No projeto, prefira a assinatura:

```php
public function apply(array $filters = [])
```

## Parâmetros Comuns

| Parâmetro | Uso |
| --- | --- |
| `user` | Filtrar ou autorizar listagem conforme usuário logado. |
| `active` | Mostrar apenas registros ativos. |
| `search` | Busca textual. |
| `with` | Carregar relações. |
| `with_count` | Contar relações. |
| `paginate` | Retornar paginator. |
| `limit` | Limitar quantidade em listas simples. |

## Quando Criar um Filter

Crie um filter quando:

- a tela tem busca;
- a tela tem paginação;
- a query usa vários `where`;
- a mesma consulta aparece em mais de um controller;
- a query precisa carregar relações com `with`;
- a listagem muda conforme usuário ou permissão.

## O Que Evitar

- Retornar tipos diferentes sem necessidade clara.
- Misturar regra de permissão complexa dentro do filter.
- Fazer validação de request no filter.
- Usar nomes de parâmetros que só fazem sentido em uma tela e confundem outras.
- Duplicar a mesma query em vários controllers.
- Formatar resposta no filter; isso pertence a resource ou controller.
- Fazer escrita no filter.

## Checklist

- O controller passa filtros em array?
- O filter esconde detalhes da query?
- Relações necessárias são carregadas com `with`?
- Paginação fica no filter quando pertence à listagem?
