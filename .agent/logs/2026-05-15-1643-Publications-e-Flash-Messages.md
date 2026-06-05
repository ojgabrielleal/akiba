# Publications e Flash Messages

Foi registrada a rodada de unificacao do gerenciamento de publicacoes privadas, reunindo materias, reviews e eventos em uma tela/fluxo compartilhado, junto com o ajuste de flash messages para disparos mais consistentes no frontend.

## Alteracoes Realizadas

### 1. Controller Unificado de Publicacoes
- Foi criado `app/Http/Controllers/Private/PublicationController.php`.
- O fluxo privado de publicacoes passou a centralizar:
  - listagem de publicacoes;
  - criacao e exibicao de materias;
  - desativacao de materias, reviews e eventos por um endpoint unico.
- A tela `private/Publication` passou a receber `publications` a partir do novo controller.
- O controller manteve o uso de `HasFlashMessages` para respostas de criacao, atualizacao e desativacao.

### 2. Actions de Publications
- Foi criada `app/Actions/Publication/IndexPublicationAction.php`.
- A action monta uma colecao paginada combinando:
  - posts ativos;
  - eventos ativos;
  - reviews ativos.
- Foi criada `app/Actions/Publication/DeactivatePublicationAction.php`.
- A desativacao passou a resolver o model por tipo (`post`, `event` ou `review`) e `uuid`, marcando `is_active` como `false`.

### 3. Resource Unificado
- Foi criado `app/Http/Resources/PublicationResource.php`.
- O resource normaliza dados comuns para o grid:
  - `publication_type`;
  - `uuid`;
  - `slug`;
  - `title`;
  - `cover`;
  - `views`;
  - `type`;
  - `author`.
- Para reviews, o status exibido passou a ser inferido a partir dos conteudos relacionados, priorizando `revision` quando houver conteudo em revisao.

### 4. Rotas e Navegacao
- As rotas privadas antigas de post, review e event foram consolidadas sob `/panel/publication`.
- Foi adicionada a rota `DELETE /panel/publication` para desativar qualquer tipo de publicacao suportado.
- O menu privado passou a apontar para a area unificada de publicacoes.
- A pagina `resources/js/pages/private/Publication.svelte` passou a usar o `PublicationsGrid`.

### 5. Grid de Publications
- Foi criado `resources/js/ui/widgets/private/grid/PublicationsGrid.svelte`.
- O grid renderiza materias, reviews e eventos usando o payload normalizado de `PublicationResource`.
- O card exibe titulo, visualizacoes, autor, status e acoes.
- A acao de remover envia `model` e `uuid` para o endpoint unificado de desativacao.
- O grid usa permissao `publication.deactivate` para controlar o botao de remover e `publication.update` para o acesso de edicao.
- A paginacao foi integrada com o componente `Pagination` em modo `infinite`.

### 6. Permissoes e Policies
- As policies de post, event e review passaram a usar permissoes de publicacao.
- A permissao `publication.deactivate` passou a controlar a desativacao unificada.
- O seeder de permissoes foi ajustado para manter as permissoes do modulo de publicacoes alinhadas ao novo fluxo.

### 7. Reviews no Fluxo de Publicacoes
- Foi adicionada a coluna `user_id` em reviews para permitir associar autoria diretamente.
- O model, factory e seeder de reviews foram ajustados para preencher e expor essa relacao.
- `ReviewResource` e `ReviewContentResource` foram ajustados para responder os dados necessarios ao novo fluxo de publicacoes.

### 8. Flash Messages
- `app/Traits/HasFlashMessages.php` passou a incluir um `id` unico no flash retornado.
- O `id` evita que mensagens iguais sejam ignoradas pelo frontend quando a mesma acao acontece em sequencia.
- O middleware de Inertia e o layout privado foram ajustados para compartilhar e renderizar os flashes com o novo formato.
- A tela de login tambem foi ajustada para trabalhar com o mesmo contrato de flash message.

### 9. Dashboard
- O dashboard privado passou a usar o `PublicationsGrid` para exibir as ultimas materias.
- O titulo do bloco foi ajustado para `Minhas ultimas materias`.
- A listagem do dashboard continua vindo de `DashboardController@indexPosts`, limitada a posts ativos do usuario logado.

### 10. Validacao
- As alteracoes foram consolidadas no commit `16a9ac0`.
- O estado atual do git foi conferido e nao havia diff pendente antes deste registro.

---
**Data:** 15 de Maio de 2026  
**Responsavel:** Codex (IA)
