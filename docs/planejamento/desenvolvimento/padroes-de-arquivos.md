---
status: ativo
tipo: guia
atualizado_em: 2026-08-03
---

# Padroes de Arquivos

Este guia mostra a arquitetura interna dos principais tipos de arquivo do projeto. Use como checklist quando criar ou revisar uma feature.

## Fluxo Recomendado

1. Comece pela rota em `routes/web`.
2. Crie ou ajuste o controller que recebe a rota.
3. Coloque validacao e autorizacao em um `Request`, quando houver entrada do usuario.
4. Coloque regra de negocio em uma `Action`.
5. Formate dados de resposta em um `Resource`.
6. Renderize a tela em uma pagina Svelte de `resources/js/pages`.
7. Extraia partes reutilizaveis para `resources/js/lib`.
8. Atualize a documentacao do modulo quando o comportamento mudar.

Exemplo de fluxo para criar uma entidade privada:

```txt
routes/web/private.php
  -> Private/PostController@store
     -> StorePostRequest
     -> StorePostAction
     -> PostResource
     -> resources/js/pages/private/Post.svelte
```

## Controllers

Controllers devem ser finos. Eles fazem a ponte entre HTTP, autorizacao, actions, resources e Inertia.

Arquitetura esperada:

```txt
<?php

namespace ...

imports do dominio
imports de actions
imports de filters
imports de controllers/concerns
imports de requests
imports de resources
imports de models
imports de framework

class NomeController extends Controller
{
    traits usados pelo controller

    propriedades privadas/publicas

    construtor com dependencias injetadas

    metodos publicos chamados pelas rotas

    metodos privados de apoio para montar props, queries ou resources
}
```

Exemplo:

```php
<?php

namespace App\Http\Controllers\Private;

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

class PostController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Post';

    public function __construct(
        private PostFilter $postFilter,
    ) {}

    public function show(Post $post)
    {
        $this->authorize('view', $post);

        return Inertia::render($this->render, [
            'post' => $this->indexPost($post),
            'posts' => $this->indexPosts(),
        ]);
    }

    public function store(StorePostRequest $request, StorePostAction $action)
    {
        $action->execute($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    private function indexPost(Post $post): PostResource
    {
        return new PostResource($post->load(['tags', 'author']));
    }

    private function indexPosts()
    {
        return PostResource::collection(
            $this->postFilter->apply([
                'user' => request()->user(),
                'active' => true,
                'paginate' => 10,
            ])
        );
    }
}
```

Use controllers de pagina em `Private/Pages` ou `Public/Pages` quando a responsabilidade principal for renderizar uma tela Inertia. Use controllers invocaveis em `Invokes` para acoes pequenas, como ativar, desativar, concluir ou alternar status.

## Requests

Requests concentram validacao e autorizacao da entrada. O controller deve receber dados prontos por `validated()`.

Arquitetura esperada:

```txt
namespace
imports
classe extends LoggedWebRequest ou FormRequest
authorize()
rules()
messages(), attributes() ou helpers privados quando necessario
```

Exemplo:

```php
<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\LoggedWebRequest;
use App\Models\Post;

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
            'image' => 'required',
            'content' => 'required_unless:module,review|nullable|string',
            'metadata' => 'required_unless:module,post|nullable|array',
        ];
    }
}
```

## Actions

Actions concentram mudanca de estado e regra de negocio. Se a operacao cria ou altera varios registros, use transacao.

Arquitetura esperada:

```txt
namespace
imports de models, services e facades
classe NomeAction
propriedades privadas para services
construtor
execute() como entrada principal
metodos privados para dividir passos internos
```

Exemplo:

```php
<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StorePostAction
{
    public function execute(User $user, array $data): Post
    {
        return DB::transaction(function () use ($user, $data) {
            $post = $this->storePost($user, $data);
            $this->storeTags($post, $data['tags'] ?? []);

            return $post;
        });
    }

    private function storePost(User $user, array $data): Post
    {
        return Post::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
        ]);
    }

    private function storeTags(Post $post, array $tags): void
    {
        if (! empty($tags)) {
            $post->tags()->createMany($tags);
        }
    }
}
```

## Filters

Filters montam consultas reutilizaveis. Eles evitam que controllers fiquem cheios de `where`, `with`, `orderBy` e paginacao.

Arquitetura esperada:

```txt
namespace
imports
classe NomeFilter
apply(array $filters)
metodos privados para cada filtro complexo
retorno de query, collection ou paginator conforme o uso do modulo
```

Exemplo de uso no controller:

```php
$this->postFilter->apply([
    'user' => request()->user(),
    'active' => true,
    'with' => ['author', 'reviews.author'],
    'search' => request()->input('search'),
    'paginate' => 10,
]);
```

## Resources

Resources definem o contrato de dados enviado ao frontend. Evite retornar models crus para as paginas.

Arquitetura esperada:

```txt
namespace
imports de resources relacionados
classe extends JsonResource
traits de formatacao, quando existirem
toArray()
blocos por formato
metodos privados para campos derivados
```

Exemplo:

```php
<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

Quando um mesmo model aparece em telas diferentes, use formatos como `summary`, `grid`, `featured` ou `public-read`, seguindo o padrao de `HasFormats`.

## Models

Models representam tabelas, relacionamentos e comportamentos diretamente ligados aos dados.

Arquitetura esperada:

```txt
namespace
imports
classe extends Model
traits
fillable, casts, hidden ou appends
relacionamentos
accessors/mutators
scopes
metodos de dominio simples
```

Exemplo:

```php
class Post extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'status',
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
}
```

Nao coloque fluxos longos de negocio no model. Prefira `Action` quando houver processo com varios passos.

## Policies

Policies concentram regras de permissao por model.

Arquitetura esperada:

```txt
namespace
imports
classe NomePolicy
viewAny()
view()
create()
update()
delete() ou deactivate()
metodos privados para regras repetidas
```

Exemplo:

```php
public function update(User $user, Post $post): bool
{
    return $user->hasPermission('post.update');
}
```

Use a policy no controller com `$this->authorize(...)` ou no request dentro de `authorize()`.

## Services

Services encapsulam integracoes externas ou processos reutilizaveis.

Use `Integrations` para APIs, webhooks, streams e provedores externos. Use `Processing` para processamento interno reutilizavel, como imagem, audiencia ou rotinas.

Arquitetura esperada:

```txt
namespace
imports
classe NomeService
propriedades de configuracao ou cliente HTTP
metodos publicos pequenos
metodos privados para montar payloads, URLs e normalizacoes
tratamento claro de falhas externas
```

## Paginas Svelte

Paginas em `resources/js/pages` recebem props do Inertia e organizam a tela. Componentes reutilizaveis ficam em `resources/js/lib`.

Arquitetura esperada:

```txt
<script>
    imports externos
    imports de componentes/layouts/widgets
    leitura de props do Inertia
    estados locais
    declaracoes reativas
    funcoes de evento
    configuracoes de actions/menus
</script>

<Meta />
<Layout>
    markup da pagina
</Layout>
```

Exemplo:

```svelte
<script>
    import { page, router } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import { PostForm, PostGrid } from "@/lib/widgets/private";

    $: ({ post, posts } = $page.props);

    let show = Boolean(post);
    $: title = post ? "Editar materia" : "Criar materia";

    function openCreate() {
        show = true;
        router.visit("/panel/post");
    }
</script>

<Meta meta={{ title }} />

<Layout>
    <Section title={title}>
        {#if show}
            <PostForm {post} />
        {/if}
    </Section>

    <PostGrid posts={posts} />
</Layout>
```

## Componentes e Widgets Svelte

Componentes pequenos e genericos ficam em `resources/js/lib/components`. Blocos maiores de dominio ficam em `resources/js/lib/widgets`.

Arquitetura esperada:

```txt
<script>
    imports
    export let para props publicas
    estados locais
    valores derivados
    dispatch/events quando necessario
    funcoes auxiliares
</script>

markup

<style> apenas se Tailwind nao resolver bem </style>
```

Prefira props explicitas. Evite componente que busca dados sozinho quando os dados ja chegam pela pagina Inertia.

## Rotas

Rotas devem deixar claro o contexto da feature: publico, privado, API ou provisory.

Arquitetura esperada:

```txt
imports dos controllers
grupo de middleware/prefix/name
rotas de pagina
rotas de CRUD
rotas invocaveis de acoes pontuais
```

Exemplo:

```php
Route::middleware(['auth'])->prefix('panel')->name('panel.')->group(function () {
    Route::get('/post', [PostPageController::class, 'render'])->name('post');
    Route::post('/post', [PostController::class, 'store'])->name('post.store');
    Route::put('/post/{post}', [PostController::class, 'update'])->name('post.update');
});
```

## Checklist de Criacao

- O nome do arquivo deixa clara a acao ou responsabilidade?
- O controller ficou fino?
- A validacao esta no `Request`?
- A regra de negocio esta em uma `Action`?
- A resposta para o frontend passa por `Resource`?
- As permissoes passam por `Policy` ou `authorize()`?
- Queries complexas foram para `Filter`?
- Componentes Svelte reutilizaveis foram para `lib`?
- A documentacao do modulo foi atualizada?
