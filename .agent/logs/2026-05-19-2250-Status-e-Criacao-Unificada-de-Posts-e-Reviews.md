# Status e Criacao Unificada de Posts e Reviews

Foi iniciado um ajuste amplo para separar o status editorial de uma publicacao do seu tipo de conteudo, alem de aproximar a criacao de materias e reviews no fluxo unico de posts.

## Alteracoes Realizadas

### 1. Renomeacao de Type para Status
- Foi criada a migration `2026_05_18_000000_rename_type_to_status_on_posts_and_opinions_tables.php`.
- As colunas `type` de `posts` e `opinions` passaram a se chamar `status`.
- Os models `Post` e `Opinion` foram atualizados para usar `status` no fillable.
- O scope `Post::published()` passou a filtrar por `status = published`.
- Factories e testes unitarios foram ajustados para criar e validar o campo `status`.

### 2. Separacao entre Tipo e Status nos Resources
- `PostResource` passou a expor `status` e a fixar `type` como `post`.
- `ReviewResource` passou a expor `type` como `review`.
- `EventResource` passou a expor `type` como `event`.
- `OpinionResource` passou a retornar `status` em vez de `type`.
- `PublicationResource` passou a expor `status` e `type` separadamente.
- O status de uma publicacao de review passa a ser `revision` quando alguma opinion estiver em revisao.

### 3. Criacao Unificada de Materias e Reviews
- `CreatePostAction` passou a receber o campo `create` para distinguir `post` e `review`.
- A action foi separada em metodos para criar materia comum e review.
- A criacao de reviews agora tambem cria o registro relacionado em `reviews` e a primeira `opinion`.
- Foi adicionado tratamento para tipo de criacao invalido com `InvalidArgumentException`.
- `CreatePostRequest` passou a validar os campos condicionais para materia e review.

### 4. Conteudo Opcional para Reviews
- Foi criada a migration `2026_05_19_202440_make_content_nullable_on_posts_table.php`.
- O campo `content` de `posts` passou a aceitar `null`.
- Essa mudanca permite que reviews usem `sinopse` e `opinion.content` sem exigir o conteudo principal de materia.

### 5. Ajustes no Frontend de Posts
- `Post.svelte` passou a alternar entre `PostForm` e `ReviewForm` usando cookies `akiba_post_show_editor` e `akiba_post_type`.
- O botao de review passou a abrir o formulario de review dentro da tela de posts.
- `PostForm` passou a enviar `create: post` e `status`.
- `ReviewForm` foi adaptado para enviar reviews pelo endpoint de posts, com `create: review`.
- O formulario de review passou a incluir tags automaticas de `reviews` e `anime`, fontes, capa, imagem, sinopse, ano de lancamento e conteudo da opinion.
- `PostGrid` passou a usar `status` para cores e `type` apenas para decidir qual editor abrir.

### 6. Ajustes de Reviews Existentes
- `ReviewController` passou a definir `status` como `published` ao criar ou atualizar opinions pelo fluxo antigo.
- O resource de review passou a incluir `status` nulo na opinion inicial exibida para o usuario.

### 7. Escopo e Pendencias
- As alteracoes ainda aparecem como modificacoes locais nao commitadas.
- Nao foi identificado no historico desta sessao que a suite de testes tenha sido executada apos esses ajustes.
- Ainda existem pequenos pontos para revisar antes de finalizar, como imports adicionados e nao usados em `PostController` e detalhes textuais de acessibilidade nos botoes.

---
**Data:** 19 de Maio de 2026  
**Responsavel:** Codex (IA)
