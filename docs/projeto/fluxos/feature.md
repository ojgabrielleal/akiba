---
status: ativo
tipo: guia-fluxo
atualizado_em: 2026-08-03
---

# Fluxo de uma Feature

Este guia mostra qual caminho seguir dependendo do tipo de mudança.

## Tela de Listagem

Use este fluxo quando for criar ou alterar uma tela que lista dados.

```txt
Rota GET
  -> PageController
     -> Filter
     -> Resource::collection()
  -> Page.svelte
     -> Grid/List widget
```

Exemplo:

```php
Route::get('/post', [PostPageController::class, 'render'])->name('post');
```

```php
public function render()
{
    return Inertia::render('private/Post', [
        'posts' => $this->indexPosts(),
    ]);
}

private function indexPosts()
{
    return PostResource::collection(
        $this->postFilter->apply([
            'active' => true,
            'search' => request()->input('search'),
            'paginate' => 10,
        ])
    )->format('grid');
}
```

Na interface:

```svelte
<script>
    import { page } from "@inertiajs/svelte";
    import { PostGrid } from "@/lib/widgets/private";

    $: ({ posts } = $page.props);
</script>

<PostGrid {posts} />
```

## Formulário de Criação

Use este fluxo quando o usuário envia dados para criar um registro.

```txt
Rota POST
  -> Request
     -> Policy
  -> Controller
     -> Action
        -> Model
  -> Flash message
```

Exemplo:

```php
Route::post('/post', [PostController::class, 'store'])->name('post.store');
```

```php
public function store(StorePostRequest $request, StorePostAction $action)
{
    $action->execute($request->user(), $request->validated());

    return $this->flashMessage('save');
}
```

## Formulário de Atualização

Use este fluxo quando o usuário altera registro existente.

```txt
Rota PUT/PATCH
  -> Route model binding
  -> UpdateRequest
  -> Controller
  -> UpdateAction
  -> Flash message
```

Exemplo:

```php
Route::put('/post/{post}', [PostController::class, 'update'])->name('post.update');
```

```php
public function update(UpdatePostRequest $request, UpdatePostAction $action, Post $post)
{
    $action->execute($post, $request->validated());

    return $this->flashMessage('update');
}
```

## Ação Pontual

Use este fluxo para ações que não são CRUD clássico.

Exemplos:

```txt
concluir tarefa
desativar post
reativar item
iniciar locução
marcar pedido como tocado
```

Fluxo:

```txt
Rota POST/DELETE
  -> Controller invocável
     -> authorize()
     -> Action
  -> Flash message
```

Exemplo:

```php
Route::post('/task/{task}/complete', CompleteTaskController::class)->name('task.complete');
```

```php
class CompleteTaskController extends Controller
{
    public function __invoke(CompleteTaskAction $action, Task $task)
    {
        $this->authorize('update', $task);

        $action->execute($task);

        return back();
    }
}
```

## Alteração de Banco

Use este fluxo quando a feature precisa de tabela, coluna ou relação nova.

```txt
Migration
  -> Model
     -> relacionamento/casts/fillable
  -> Seeder/factory quando necessário
  -> Action/Filter/Resource
  -> Interface
```

Checklist específico:

- migration cria ou altera a estrutura;
- model permite preenchimento dos campos certos;
- casts foram adicionados;
- relacionamentos foram definidos;
- resource expõe apenas o necessário;
- tela usa o novo dado sem acessar campo interno indevido.

## Checklist Final

- Rota criada no contexto certo.
- Request valida e autoriza quando há entrada.
- Policy cobre permissão sensível.
- Action concentra mudança de estado.
- Controller ficou fino.
- Filter concentra listagem complexa.
- Resource protege o contrato com a interface.
- Página Svelte recebeu props pelo Inertia.
- Componentes e widgets ficaram nas pastas corretas.
