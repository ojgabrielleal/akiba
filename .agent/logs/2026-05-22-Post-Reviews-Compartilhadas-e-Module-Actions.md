# Post Reviews Compartilhadas e Module em Actions

Foi ajustado o fluxo privado de posts para tratar reviews como publicacoes compartilhadas, evitando expor o autor inicial como dono visual da review e separando o contexto `module` do payload dos formularios.

## Alteracoes Realizadas

### 1. Listagem Privada com Resource de Indice
- `PostController::indexPosts()` passou a retornar `PostIndexResource::collection(...)`.
- A consulta carrega `author`, `event` e `review.opinions`.
- Usuarios com `post.list` continuam vendo todos os posts ativos.
- Usuarios com `post.list.own` veem seus posts comuns e tambem posts que possuem review.
- Essa regra permite que colaboradores acessem reviews compartilhadas mesmo antes de criarem sua propria opinion.

### 2. UX de Reviews Compartilhadas no Grid
- `PostGrid.svelte` deixou de mostrar o nickname do autor inicial para cards de review.
- Reviews agora exibem uma label `Review` com estilo proprio.
- Posts comuns continuam exibindo o nickname do autor.
- As cores do rodape continuam baseadas em `status`, preservando a indicacao visual de draft, published e revision.

### 3. Module como Parametro de Action
- `CreatePostAction::execute()` passou a receber `module` como parametro nomeado.
- `UpdatePostAction::execute()` tambem passou a receber `module` como parametro nomeado.
- O valor deixou de ser injetado em `$request->all()`, mantendo o payload do formulario separado do contexto interno da action.
- `PostController::createPost()` chama o create com `module: 'post'`.
- `PostController::updatePost()` chama o update com `module: $request->input('create', 'post')`.

### 4. Update de Reviews
- `UpdatePostAction` passou a distinguir update de post e review pelo parametro `module`.
- O update de review atualiza os dados gerais do post, os dados da review e a opinion do usuario logado.
- A opinion usa `updateOrCreate` com `user_id`, permitindo que um usuario participe de uma review compartilhada mesmo sem opinion previa.

## Validacao

- Foi validada a sintaxe de `app/Http/Controllers/Private/PostController.php`.
- Foi validada a sintaxe de `app/Actions/Post/CreatePostAction.php`.
- Foi validada a sintaxe de `app/Actions/Post/UpdatePostAction.php`.

## Observacoes

- `posts.user_id` segue representando o criador/origem interna do registro.
- Para reviews compartilhadas, a autoria editorial das participacoes fica em `opinions.user_id`.
- A UI deve evitar tratar `posts.user_id` como autor principal de uma review compartilhada, exceto em contextos administrativos ou de auditoria.

---
**Data:** 22 de Maio de 2026  
**Responsavel:** Codex (IA)
