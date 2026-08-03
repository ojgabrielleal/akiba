---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Requests

Requests validam entrada do usuário e podem concentrar a autorização da operação.

## Onde Ficam

```txt
app/Http/Requests
```

## Arquitetura do Arquivo

```txt
namespace
imports

class NomeRequest extends LoggedWebRequest
{
    authorize()

    rules()

    messages()

    attributes()
}
```

## Responsabilidade

Um request deve proteger a entrada antes dela chegar na action.

Ele cuida de:

- autorização da operação;
- campos obrigatórios;
- tipos de dados;
- validação condicional;
- validação de arrays;
- mensagens e nomes amigáveis quando necessário.

Depois do request, o controller deve usar apenas `$request->validated()`.

## Exemplo

```php
class StorePostRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'module' => 'required|in:post,review,event',
            'title' => 'required|string|max:255',
            'content' => 'required_unless:module,review|nullable|string',
            'metadata' => 'required_unless:module,post|nullable|array',
        ];
    }
}
```

## Validação de Arrays

Quando o frontend envia listas, valide o array e seus filhos:

```php
return [
    'tags' => 'required|array',
    'tags.*.name' => 'required|string|max:255',
    'references' => 'nullable|array',
    'references.*.name' => 'required|string|max:255',
    'references.*.url' => 'required|url|max:255',
];
```

## Validação Condicional

Use regras condicionais quando o mesmo formulário atende módulos diferentes:

```php
return [
    'module' => 'required|in:post,review,event',
    'content' => 'required_unless:module,review|nullable|string',
    'metadata.event_date' => 'required_if:module,event|date',
    'metadata.year_of_release' => 'required_if:module,review|integer',
];
```

## Autorização

Quando a regra depende só do usuário e do model, use policy:

```php
public function authorize(): bool
{
    return $this->user()?->can('create', Post::class) ?? false;
}
```

Quando a regra depende de um model da rota:

```php
public function authorize(): bool
{
    return $this->user()?->can('update', $this->route('post')) ?? false;
}
```

## O Que Evitar

- Validar no controller e no request ao mesmo tempo.
- Passar `$request->all()` para action.
- Colocar regra de negócio longa dentro de `rules()`.
- Aceitar arrays sem validar os campos internos.
- Fazer query pesada dentro de `authorize()`.

## Checklist

- O controller usa `$request->validated()`?
- A autorização está em `authorize()` quando depende da entrada?
- Regras condicionais ficam explícitas?
- Campos de arrays usam validação para os filhos?
