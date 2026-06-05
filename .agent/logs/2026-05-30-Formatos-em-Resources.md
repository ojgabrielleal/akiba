# Formatos em Resources

Foi iniciado o padrao de formatos nos Resources para permitir respostas completas ou resumidas conforme o contexto do controller.

## Alteracoes Realizadas

### 1. Formato Compacto em UserResource
- Foi adicionado o metodo `format()` em `UserResource`.
- O formato `compact` passou a retornar apenas `uuid`, `name`, `nickname` e `avatar`.
- Resources que exibem usuario como autor, responsavel ou host passaram a usar `UserResource::make(...)->format('compact')`.
- Respostas diretas de usuario, como perfil e administracao, continuam usando o formato completo.

### 2. Formato Summary em PostResource
- Foi adicionado o metodo `format()` em `PostResource`.
- O formato `summary` passou a retornar a versao resumida de posts para listagens.
- A versao resumida inclui `uuid`, `slug`, `status`, `title`, `image`, `cover`, `author`, `tags`, `views` e `module`.
- A resposta completa continua preservada para detalhes de post, incluindo conteudo, reacoes, evento e dados de review.

### 3. Collection Customizada de Posts
- Foi criada `PostResourceCollection` para permitir o uso de `PostResource::collection(...)->format('summary')`.
- A collection aplica o formato escolhido em cada `PostResource` da lista.
- Isso evita espalhar `map()` manual nos controllers.

### 4. Controllers Atualizados
- Listagens de posts passaram a usar `->format('summary')`.
- `Private\PostController@indexPosts()` usa summary para paginacao.
- `Public\HomeController@indexPost()` usa summary para posts publicos da home.
- `Private\DashboardController@indexPosts()` usa summary para posts do dashboard.
- A tela de detalhe em `showPost()` segue retornando `PostResource` completo.

### 5. Lembrete de Continuidade
- Foi criado `.agent/reminder/continuar-formatos-resources.md`.
- A execucao foi marcada para `2026-05-31`.
- O lembrete orienta continuar aplicando formatos nos Resources a partir dos controllers.

## Validacao

- `php -l` em `UserResource.php`.
- `php -l` nos Resources alterados para usuario compacto.
- `php -l` em `PostResource.php`.
- `php -l` em `PostResourceCollection.php`.
- `php -l` nos controllers alterados.
- Nao foram encontrados erros de sintaxe.

## Observacoes

- O padrao criado permite evoluir para novos formatos, como `compact`, `summary` e `detail`, mantendo a decisao de payload perto da camada de Resource.
- Para collections com formato, a abordagem recomendada ficou sendo criar uma ResourceCollection especifica quando necessario.

---
**Data:** 30 de Maio de 2026  
**Responsavel:** Codex (IA)
