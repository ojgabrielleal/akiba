---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Resources

Resources definem o formato dos dados enviados para a interface. Eles são o contrato entre backend e frontend.

## Onde Ficam

```txt
app/Http/Resources
```

## Arquitetura do Arquivo

```txt
namespace
imports de resources relacionados

class NomeResource extends JsonResource
{
    traits de formato

    toArray(Request $request)

    métodos privados para campos derivados
}
```

## Responsabilidade

Um resource deve responder “qual formato a interface precisa?”.

Ele cuida de:

- nomes de campos enviados ao frontend;
- datas formatadas;
- relações aninhadas;
- campos derivados;
- formatos diferentes para lista, detalhe e destaque;
- esconder campos internos do banco.

O resource não deve executar regra de negócio nem criar dados.

## Exemplo

```php
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'author' => UserResource::make($this->author)->format('summary'),
            'created_at' => $this->created_at?->format('d/m/Y'),
        ];
    }
}
```

## Exemplo com Formato

```php
class PostResource extends JsonResource
{
    use HasFormats;

    public function toArray(Request $request): array
    {
        if ($this->format === 'grid') {
            return [
                'uuid' => $this->uuid,
                'title' => $this->title,
                'status' => $this->status,
                'author' => UserResource::make($this->author)->format('summary'),
            ];
        }

        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at?->format('d/m/Y'),
        ];
    }
}
```

## Formatos

Quando o mesmo model aparece em contextos diferentes, use formatos.

Exemplos:

```txt
summary       dado pequeno para select, autor, usuário resumido
grid          dado para listagem do painel
featured      destaque da home
public-read   leitura pública completa
```

## Relações

Quando o resource usa uma relação, carregue essa relação antes no controller ou filter:

```php
return new PostResource($post->load(['tags', 'references', 'author']));
```

```php
'with' => ['author', 'reviews.author']
```

Isso evita consultas extras e deixa claro o contrato de dados da tela.

## O Que Evitar

- Retornar model cru para Inertia.
- Usar resource para buscar dados que o controller/filter deveria carregar.
- Expor campos internos sem necessidade.
- Formatar a mesma estrutura manualmente em vários controllers.
- Criar formatos demais sem diferença real entre eles.

## Checklist

- A página recebe dados já formatados?
- O resource evita expor campos internos sem necessidade?
- Relações usadas pelo resource foram carregadas no controller/filter?
- O formato usado combina com a tela?
