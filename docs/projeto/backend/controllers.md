---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Controllers

Controllers recebem a requisição e decidem qual camada chamar. Eles não devem concentrar regra de negócio pesada.

## Onde Ficam

```txt
app/Http/Controllers/Public
app/Http/Controllers/Public/Pages
app/Http/Controllers/Public/Invokes
app/Http/Controllers/Private
app/Http/Controllers/Private/Pages
app/Http/Controllers/Private/Invokes
app/Http/Controllers/Api
```

## Tipos

| Tipo | Uso |
| --- | --- |
| Page Controller | Renderiza página Inertia e monta props iniciais. |
| CRUD Controller | Salva, atualiza, lista ou exibe recurso. |
| Invoke Controller | Executa uma ação pontual, como desativar, concluir ou alternar status. |
| API Controller | Retorna dados para integrações ou endpoints externos. |

## Responsabilidade

Um controller deve responder perguntas de fluxo HTTP:

- qual request valida esta entrada?
- qual usuário está tentando fazer a ação?
- qual action executa a mudança?
- qual resource formata os dados?
- qual página Inertia será renderizada?
- qual redirect ou flash message será retornado?

Ele não deve responder perguntas de regra de negócio profunda, como “quais registros filhos precisam ser criados” ou “como processar uma imagem”. Essas respostas pertencem às actions e services.

## Arquitetura do Arquivo

```txt
<?php

namespace

imports de actions
imports de filters
imports de concerns/controllers
imports de requests
imports de resources
imports de models
imports de framework

class NomeController extends Controller
{
    traits

    variáveis/propriedades

    construtor

    métodos públicos chamados pelas rotas

    métodos privados para montar dados internos
}
```

## Exemplo de Controller de Página

Use controller de página quando a rota principal só precisa abrir uma tela e enviar props iniciais.

```php
class PostPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Post';

    public function __construct(
        private PostFilter $postFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'posts' => $this->indexPosts(),
        ]);
    }

    private function indexPosts()
    {
        return $this->whenCanViewAny(Post::class,
            fn () => PostResource::collection(
                $this->postFilter->apply([
                    'user' => request()->user(),
                    'active' => true,
                    'paginate' => 10,
                ])
            )->format('grid'),
        );
    }
}
```

Padrões importantes nesse exemplo:

- `$render` guarda o caminho da página Svelte usada no Inertia.
- O construtor injeta filtros e services necessários para montar a tela.
- `render()` é o método público usado pela rota.
- Métodos privados como `indexPosts()` montam props específicas.
- `Resource::collection(...)->format('grid')` define o contrato usado pela interface.

## Exemplo de Controller Invocável

Use um controller invocável quando a rota executa uma ação única.

```php
class DeactivatePostController extends Controller
{
    use HasFlashMessages;

    public function __invoke(DeactivatePostAction $action, Post $post)
    {
        $this->authorize('delete', $post);

        $action->execute($post);

        return $this->flashMessage('delete');
    }
}
```

Quando o controller só tem um comportamento, `__invoke()` deixa a rota mais clara e evita criar métodos artificiais.

## Exemplo de Controller de Escrita

Use controller de escrita quando a rota recebe formulário ou arquivo e muda estado do sistema.

```php
class PostController extends Controller
{
    use HasFlashMessages;

    public function store(StorePostRequest $request, StorePostAction $action)
    {
        $action->execute(
            $request->user(),
            $request->validated(),
            $request->file('image'),
            $request->file('cover')
        );

        return $this->flashMessage('save');
    }
}
```

## Ordem Interna Recomendada

Dentro do arquivo, mantenha a ordem:

1. `namespace`
2. imports agrupados por domínio
3. `class`
4. traits
5. propriedades
6. `__construct()`
7. métodos públicos na ordem das rotas
8. métodos privados usados pelos métodos públicos

Exemplo de imports agrupados:

```php
use App\Actions\Post\StorePostAction;
use App\Actions\Post\UpdatePostAction;
use App\Filters\PostFilter;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Inertia\Inertia;
```

## Nomes de Métodos

Use nomes que mostrem a intenção:

| Método | Quando usar |
| --- | --- |
| `render()` | Controller de página. |
| `store()` | Criar recurso. |
| `update()` | Atualizar recurso. |
| `show()` | Exibir recurso específico. |
| `destroy()` | Remover recurso definitivamente. |
| `__invoke()` | Ação pontual. |
| `indexPosts()` | Método privado que monta prop/lista. |
| `indexPermissions()` | Método privado que monta permissões ou opções. |

## O Que Evitar

- Query longa direto no método público.
- `request()->validate()` dentro do controller quando já existe padrão de `Request`.
- Criar ou atualizar vários models diretamente no controller.
- Retornar model Eloquent cru para o frontend.
- Misturar renderização de página e regra de negócio no mesmo método.
- Repetir regras de permissão que deveriam estar em policy.

## Checklist

- O controller só coordena o fluxo?
- A validação está em um `Request`?
- A regra de negócio está em uma `Action`?
- A consulta complexa está em um `Filter`?
- A resposta para a interface passa por `Resource`?
- Métodos privados têm nomes que explicam a prop que montam?
