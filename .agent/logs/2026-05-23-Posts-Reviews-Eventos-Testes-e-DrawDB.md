# Posts Reviews Eventos Testes e DrawDB

Foi finalizada a branch de atualizacao da interface e fluxo privado de materias, reviews e eventos, junto com ajustes de testes em memoria e documentacao visual do schema para DrawDB.

## Alteracoes Realizadas

### 1. Fluxo Unificado de Posts, Reviews e Eventos
- Os fluxos antigos dedicados de `EventController` e `ReviewController` foram removidos do painel privado.
- A tela privada de posts passou a concentrar materias, reviews e eventos.
- `Post.svelte`, `PostGrid.svelte`, `PostActions.svelte` e os formularios privados foram ajustados para trabalhar com os tipos de conteudo no fluxo unificado.
- Os exports de widgets privados foram atualizados para refletir os componentes ainda ativos.

### 2. Actions e Requests de Posts
- `CreatePostAction` e `UpdatePostAction` passaram a tratar os modulos de post, review e evento de forma mais explicita.
- `CreatePostRequest` e `UpdatePostRequest` foram ajustados para validar campos condicionais conforme o tipo de publicacao.
- `PostResource` e `PostIndexResource` foram alinhados ao formato usado pela nova interface.

### 3. Testes em Memoria
- Foi criado `.env.testing.example` com configuracao minima para testes.
- `phpunit.xml` passou a usar SQLite em memoria com `DB_CONNECTION=sqlite` e `DB_DATABASE=:memory:`.
- Foi criada a pasta `tests/Feature` com `.gitkeep`, evitando falha do PHPUnit por suite declarada sem diretorio.
- Factories de `Post`, `Task` e `Onair` foram ajustadas para funcionar corretamente na suite em memoria.
- `CalendarTest` foi ajustado para nao enviar a coluna removida `has_activity`.

### 4. Cobertura de Models
- `PageView` recebeu `HasFactory`.
- Foi criada `PageViewFactory`.
- Foi criado `PageViewTest` para validar a relacao polimorfica `viewable`.
- `PostTest` passou a cobrir `views()` e `featured()`.
- `UserTest` passou a cobrir `favorites()` e os helpers `hasPermission()`, `hasRole()` e `hasAnyPermission()`.
- O teste de relacao de opinions do usuario foi renomeado para refletir o comportamento real testado.

### 5. DrawDB e Schema Visual
- Foi criado `database/drawdb.dbml` com o schema de dominio em DBML.
- Foi criado `database/drawdb-layout.json` com tabelas, relacionamentos, areas visuais e posicoes para exibicao mais espacada.
- Foi criado `database/drawdb-layout.drawdb` como versao importavel no DrawDB com metadados de diagrama.
- O schema visual foi separado em areas: Identity & Access, Content, Radio & Music e Engagement & Operations.
- Tabelas operacionais do Laravel, como cache, jobs, sessions e tokens, ficaram fora do schema visual de dominio.

## Validacao

- `composer test`
- Resultado: 84 testes passaram, 1 teste foi pulado, 145 assertions.
- O teste pulado segue sendo `Calendar::valid()` em SQLite, pois o scope usa SQL especifico de MySQL (`TIMESTAMP()` e `NOW()`).
- O arquivo `database/drawdb-layout.drawdb` foi validado localmente quanto as propriedades obrigatorias do diagrama.

## Observacoes

- O arquivo `.env.testing` real permanece ignorado pelo Git; apenas `.env.testing.example` foi versionado.
- O layout DrawDB foi criado para importacao visual, enquanto o DBML fica como representacao textual do schema.
- A branch segue vinculada a issue 50 pelo nome da branch.

---
**Data:** 23 de Maio de 2026  
**Responsavel:** Codex (IA)
