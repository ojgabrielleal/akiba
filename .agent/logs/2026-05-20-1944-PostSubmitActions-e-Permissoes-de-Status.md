# PostSubmitActions e Permissoes de Status

Foi realizada uma organizacao do fluxo de acoes de submissao de posts/reviews, centralizando os botoes em um componente reutilizavel e alinhando as permissoes ao novo modelo baseado em status editorial.

## Alteracoes Realizadas

### 1. Componente Compartilhado de Acoes
- Foi criado o componente `PostSubmitActions.svelte` em `resources/js/ui/widgets/private/form`.
- O componente recebe `status`, `post`, `create`, `update` e `approve`.
- O `status` define qual grupo de botoes deve aparecer.
- As permissoes filtram quais botoes daquele grupo o usuario pode usar.
- O botao `Aprovar post` aparece quando o status e `revision` e o usuario possui `post.approve`.
- O botao de aprovacao foi destacado com `bg-purple-mystic`, `text-neutral-white` e posicionado no canto oposto com `ml-auto`.

### 2. Integracao nos Formularios
- `PostForm.svelte` passou a usar `PostSubmitActions`.
- `ReviewForm.svelte` passou a usar `PostSubmitActions`.
- Os formularios agora montam `can.create` com:
  - `post.draft`
  - `post.revision`
  - `post.publish`
- As permissoes `post.update`, `post.update.own` e `post.approve` foram conectadas ao componente.

### 3. Permissoes e Policies
- `PermissionSeeder.php` foi ajustado para remover as permissoes separadas de review/opinion.
- A criacao de posts passou a ser representada pelas permissoes especificas de status:
  - `post.draft`
  - `post.revision`
  - `post.publish`
- `PostPolicy::create()` passou a verificar se o usuario tem qualquer uma dessas tres permissoes.
- `EventPolicy::create()` foi alinhada ao mesmo criterio.
- `ReviewPolicy` tambem foi ajustada enquanto ainda existir no codigo, usando permissoes de post.
- Foi adicionado suporte a `approve` em policies usando `post.approve`.

### 4. Ajustes Visuais nas Reviews
- Os botoes de opinions no `ReviewForm.svelte` passaram a refletir o status de cada review:
  - `published`: `bg-blue-ocean text-neutral-white`
  - `draft`: `bg-green-forest text-blue-marinho`
  - `revision`: `bg-orange-citric text-blue-marinho`
- A opinion selecionada ganhou uma cor propria:
  - `bg-purple-lilac text-blue-marinho`
- A cor selecionada sobrescreve a cor de status apenas no botao ativo.

### 5. Ajustes de Atalhos do Painel
- Referencias antigas a `post.create` no front foram trocadas pela verificacao equivalente:
  - `post.draft || post.revision || post.publish`
- Isso foi aplicado nos atalhos de criacao relacionados a posts.

## Validacao

- Foi executado `npm.cmd run build`.
- O build ainda falha antes de compilar os componentes Svelte porque o Vite tenta resolver `resources/css/app.css`, mas o arquivo existente no projeto esta em `public/css/app.css`.
- Essa falha ja estava bloqueando a validacao e nao foi causada diretamente pelos ajustes do componente.

## Observacoes

- Ainda existem referencias estruturais antigas a `Review` como model/resource/controller em partes do projeto.
- A intencao definida nesta etapa foi remover a entidade separada de permissoes de review e concentrar o fluxo editorial em permissoes de post.
- As alteracoes permanecem como modificacoes locais nao commitadas.

---
**Data:** 20 de Maio de 2026  
**Responsavel:** Codex (IA)
