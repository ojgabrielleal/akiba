---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Policies

Policies controlam autorização por model ou recurso.

## Onde Ficam

```txt
app/Policies
```

## Arquitetura do Arquivo

```txt
namespace
imports

class NomePolicy
{
    viewAny()
    view()
    create()
    update()
    delete()
    deactivate()

    métodos privados para regras repetidas
}
```

## Responsabilidade

Uma policy decide se um usuário pode executar uma ação sobre um recurso.

Ela deve responder:

- o usuário pode ver a listagem?
- o usuário pode ver este registro?
- o usuário pode criar?
- o usuário pode atualizar?
- o usuário pode desativar/remover?
- existe alguma regra especial por dono, status ou permissão?

## Exemplo

```php
public function update(User $user, Post $post): bool
{
    return $user->hasPermission('post.update');
}
```

## Exemplo com Dono do Registro

```php
public function update(User $user, Post $post): bool
{
    return $user->hasPermission('post.update')
        || $post->user_id === $user->id;
}
```

Use esse padrão quando o criador do conteúdo pode editar o próprio registro mesmo sem permissão administrativa ampla.

## Onde Chamar

No controller:

```php
$this->authorize('view', $post);
```

No request:

```php
return $this->user()?->can('create', Post::class) ?? false;
```

## Métodos Comuns

| Método | Uso |
| --- | --- |
| `viewAny` | Ver listagem ou acessar módulo. |
| `view` | Ver um registro específico. |
| `create` | Criar novo registro. |
| `update` | Editar registro existente. |
| `delete` | Excluir ou desativar registro. |
| `restore` | Reativar registro, quando existir. |

## O Que Evitar

- Checar permissão direto no Svelte como única barreira.
- Repetir `hasPermission()` em vários controllers.
- Colocar query pesada dentro da policy.
- Usar policy para validação de formulário.
- Retornar `true` temporário e esquecer de revisar.

## Checklist

- A regra usa permissões reais do projeto?
- O request ou controller chama a policy?
- A regra está no model certo?
- A policy evita regra duplicada em controller?
