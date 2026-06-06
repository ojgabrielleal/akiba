# PostListAction e Permissoes de Listagem

Foi criada uma action reutilizavel para centralizar a listagem de posts e separar a regra de permissao entre listagem geral e listagem propria.

## Alteracoes Realizadas

### 1. Criacao da Action
- Foi criado o arquivo `app/Actions/Post/PostListAction.php`.
- A action recebe um `User` no metodo `execute`.
- A consulta base lista apenas posts ativos, com contagem de views e carregamento de `author` e `review.opinions`.

### 2. Regra de Permissao
- Usuarios com `post.list` carregam todos os posts ativos.
- Usuarios com `post.list.own` carregam apenas posts relacionados a eles.
- Para posts comuns, a relacao propria usa `posts.user_id`.
- Para reviews, tambem sao considerados posts cuja review possui uma `opinion` do usuario.

### 3. Integracao no Controller
- `PostController::indexPosts()` passou a delegar a listagem para `PostListAction`.
- O controller permanece responsavel pela autorizacao `viewAny` e pela conversao para `PublicationResource`.
- `PostController::render()` continua chamando `indexPosts()` diretamente, sem conhecer a action.

### 4. Motivacao
- A listagem sera reutilizada em outros controllers.
- A regra de permissao deixa de ficar duplicada em controllers.
- A distincao entre posts proprios e reviews com participacao propria fica centralizada em uma unica action.

### 5. Validacao
- Foi validada a sintaxe de `app/Actions/Post/PostListAction.php`.
- Foi validada a sintaxe de `app/Http/Controllers/Private/PostController.php`.

---
**Data:** 16 de Maio de 2026  
**Responsavel:** Codex (IA)
