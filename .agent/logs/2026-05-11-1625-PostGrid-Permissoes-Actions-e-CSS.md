# PostGrid, Permissoes, Actions e CSS

Foi registrada uma rodada de ajustes no grid de materias, na organizacao das utilities CSS e no fluxo de desativacao de posts a partir do dashboard.

## Alteracoes Realizadas

### 1. PostGrid
- O grid inferior dos cards de materias foi corrigido para usar uma classe arbitraria valida do Tailwind:
  - de `grid-cols-[0.5fr, 1fr, 1fr]`
  - para `grid-cols-[0.5fr_1fr_1fr]`
- O rodape dos cards foi ajustado para manter visualizacao, autor e acoes na mesma linha.
- As permissoes foram aplicadas na interface:
  - `post.deactivate` controla a exibicao do botao de remover;
  - `post.update` permite edicao global;
  - `post.update.own` permite edicao quando o autor do post e o usuario logado sao o mesmo.
- O botao de remover passou a chamar `router.delete()` em `/panel/dashboard/post/{uuid}` preservando o scroll.

### 2. Desativacao de Posts
- Foi confirmada a existencia da permissao `post.deactivate` no `PermissionSeeder`.
- Foi confirmada a regra `delete` na `PostPolicy`, baseada em `post.deactivate`.
- Foi criada a rota `DELETE /panel/dashboard/post/{post:uuid}` no grupo de dashboard.
- O `DashboardController` ganhou o metodo `deactivatePost`.
- A desativacao passou a marcar `is_active` como `false` e retornar o flash de `deactivate`.

### 3. Action Reutilizavel
- Foi criada `app/Actions/Post/DeactivatePostAction.php`.
- A action encapsula a regra de desativacao de post dentro de uma transacao.
- O `DashboardController` passou a receber `DeactivatePostAction` por injecao, mantendo o controller focado em autorizacao e resposta.
- A action fica disponivel para reaproveitamento em outros controllers que precisem desativar materias.

### 4. Organizacao CSS
- A utility `scroll-x` foi renomeada para `carrousel-scroll`, deixando claro que ela serve ao componente `Carrousel`.
- As utilities de gradiente foram convertidas para `@utility`, alinhando com o padrao usado pelos filtros.
- Foi adicionado comentario acima das utilities de gradiente, seguindo o estilo do bloco de filtros.
- A textura especifica do `WellcomeHero` saiu do CSS global e passou a ser aplicada diretamente no componente via classes Tailwind/arbitrarias.
- Foi adicionada configuracao em `.vscode/settings.json` para ignorar falsos positivos de `unknownAtRules` em diretivas do Tailwind v4 como `@theme` e `@utility`.

### 5. Validacao
- O formatador frontend foi executado com `npm.cmd run format:frontend`.
- A build foi executada com `npm.cmd run build` e concluida com sucesso.
- Os arquivos PHP alterados foram validados com `php -l`.
- Permanecem apenas avisos preexistentes de acessibilidade relacionados a `aria-label=""` vazio em outros componentes.

---
**Data:** 11 de Maio de 2026  
**Responsavel:** Codex (IA)
