# Plano: Categorias de Emblemas e Conquistas Automáticas

## Objetivo

Atualizar o sistema de emblemas para trabalhar com as categorias:

- **Evento**: ganho durante um evento ou período específico. Permite vários usuários.
- **Competitivo**: dado a quem lidera um ranking. Exclusivo, apenas um usuário por vez.
- **Concedido**: entregue manualmente pelos administradores. Permite vários usuários.
- **Conquista**: desbloqueado automaticamente ao atingir uma meta. Permite vários usuários.

Para conquistas de pedidos musicais, contar apenas pedidos marcados como atendidos, ou seja, pedidos do tipo `music` com `was_reproduced = true`.

Para conquistas de podcasts, permitir que o usuário marque episódios como ouvidos e conceder emblemas quando ele atingir metas, como ouvir todos os podcasts publicados.

## Estado Atual

O projeto já possui uma base funcional em `BadgeService`, `Badge`, `BadgeAssignment` e `BadgeForm`.

Mapeamento atual:

- `fixed` equivale a **Concedido**.
- `stealable` equivale a **Competitivo**.
- `scheduled` equivale a **Evento**.

Já existem gatilhos competitivos:

- `song_request.most_requests`: pessoa que mais fez pedidos musicais.
- `enigmagame.most_wins`: pessoa que mais venceu enigmas.

O que ainda falta:

- Criar o tipo lógico **Conquista**.
- Permitir configurar uma meta no cadastro do emblema.
- Conceder automaticamente quando o usuário atingir a meta.
- Usar somente pedidos atendidos para metas de pedidos musicais.

## Modelo Proposto

Adicionar um novo tipo:

```php
Badge::TYPE_ACHIEVEMENT = 'achievement';
```

Manter os tipos existentes no banco para evitar migração destrutiva:

- `fixed`
- `stealable`
- `scheduled`
- `achievement`

Na interface, exibir nomes amigáveis:

- `fixed` -> `Concedido`
- `stealable` -> `Competitivo`
- `scheduled` -> `Evento`
- `achievement` -> `Conquista`

## Regra de Conquista

Usar o campo `rule` JSON do emblema para configurar conquistas.

Exemplo para 100 pedidos atendidos:

```json
{
  "trigger": "song_request.played_total",
  "threshold": 100
}
```

Exemplo para 500 pedidos atendidos:

```json
{
  "trigger": "song_request.played_total",
  "threshold": 500
}
```

Exemplo para ouvir todos os podcasts:

```json
{
  "trigger": "podcast.all_listened"
}
```

Exemplo para ouvir uma quantidade específica de podcasts:

```json
{
  "trigger": "podcast.listened_total",
  "threshold": 50
}
```

## Fluxo de Concessão

Quando um pedido musical for marcado como atendido:

1. `LocutionService::markSongRequestAsPlayed()` marca `was_reproduced = true`.
2. Após salvar, chamar um método no `BadgeService` para avaliar conquistas de pedidos atendidos.
3. O serviço conta os pedidos atendidos do mesmo solicitante:
   - `type = music`
   - `was_reproduced = true`
   - mesmo `requester_type`
   - mesmo `requester_id`
4. Buscar emblemas ativos do tipo `achievement` com `rule->trigger = song_request.played_total`.
5. Para cada emblema, se o total for maior ou igual a `threshold`, conceder uma vez.
6. Se o usuário já tiver o emblema ativo, não duplicar.

## Conquista de Podcasts Ouvidos

Adicionar um marcador na página de podcast para o usuário registrar que já ouviu o episódio.

Sugestões de texto:

- `Já ouvi este episódio`
- `Marcar como ouvido`
- `Podcast ouvido`

O marcador deve exigir usuário autenticado, usando o mesmo padrão público já usado por comentários, enquetes, pedidos musicais e Enigma Otaku.

### Tabela Proposta

Criar uma tabela para guardar os episódios marcados como ouvidos:

```text
podcast_listens
- id
- uuid
- podcast_id
- listener_type
- listener_id
- listened_at
- created_at
- updated_at
```

Regras:

- `listener_type` e `listener_id` devem ser polimórficos para suportar `User` e `OAuthAccount`.
- Cada usuário deve poder marcar o mesmo podcast apenas uma vez.
- O registro pode ser idempotente: se clicar novamente e já existir, mantém o mesmo registro.
- Se for desejável permitir desmarcar no futuro, adicionar endpoint de remoção ou campo `unlistened_at`. Para a primeira versão, basta marcar como ouvido.

### Fluxo de Podcasts

Quando o usuário marca um podcast como ouvido:

1. Controller público recebe a ação.
2. Resolve o usuário com `AuthenticatedMember::fromRequest($request)`.
3. Cria ou reaproveita o registro em `podcast_listens`.
4. Chama o `BadgeService` para avaliar conquistas de podcast.
5. Para `podcast.listened_total`, conta quantos podcasts ativos o usuário marcou como ouvido.
6. Para `podcast.all_listened`, compara:
   - total de podcasts ativos/publicados;
   - total de podcasts ativos marcados como ouvidos pelo usuário.
7. Se a condição for atingida, concede o emblema uma vez.

### Regras de Contagem

Para evitar inconsistência, contar apenas podcasts ativos:

```text
podcasts.is_active = true
```

Para o total ouvido pelo usuário:

```text
podcast_listens.listener_type = usuário autenticado
podcast_listens.listener_id = id do usuário autenticado
podcast_listens.podcast_id IN podcasts ativos
```

Se um podcast for desativado depois, ele não deve mais ser exigido para a conquista de ouvir todos.

### Interface

Na página `ReadPodcast.svelte`, abaixo do player ou próximo ao cabeçalho:

- Mostrar botão `Marcar como ouvido` quando ainda não foi marcado.
- Mostrar estado `Podcast ouvido` quando já foi marcado.
- Para visitante deslogado, usar `AuthGuard` ou padrão equivalente com chamada para login.

Na listagem de podcasts, opcionalmente mostrar um indicador visual nos cards já ouvidos.

### Emblemas Possíveis

Depois da implementação, criar pelo painel:

- Nome: `Maratonista AkibaCast`
- Categoria: `Conquista`
- Origem: `Podcasts`
- Gatilho: `Todos os podcasts ouvidos`

E, opcionalmente:

- Nome: `10 podcasts ouvidos`
- Categoria: `Conquista`
- Origem: `Podcasts`
- Gatilho: `Quantidade de podcasts ouvidos`
- Meta: `10`

- Nome: `50 podcasts ouvidos`
- Categoria: `Conquista`
- Origem: `Podcasts`
- Gatilho: `Quantidade de podcasts ouvidos`
- Meta: `50`

## Arquivos Prováveis

Backend:

- `app/Models/Badge.php`
- `app/Models/Podcast.php`
- `app/Models/PodcastListen.php`
- `app/Services/BadgeService.php`
- `app/Services/PodcastListenService.php`
- `app/Services/LocutionService.php`
- `app/Http/Requests/Badge/StoreBadgeRequest.php`
- `app/Http/Requests/Badge/UpdateBadgeRequest.php`
- `app/Http/Resources/BadgeResource.php`
- `app/Http/Resources/PodcastResource.php`
- `app/Http/Controllers/Public/PodcastController.php`

Frontend:

- `resources/js/lib/widgets/private/form/BadgeForm.svelte`
- `resources/js/lib/widgets/private/grid/BadgeGrid.svelte`
- `resources/js/pages/public/ReadPodcast.svelte`
- `resources/js/pages/public/Podcasts.svelte`

Testes:

- Teste para conceder conquista ao atingir 100 pedidos atendidos.
- Teste para não contar pedidos apenas enviados.
- Teste para não contar pedidos cancelados.
- Teste para não duplicar emblema já concedido.
- Teste para conceder múltiplas conquistas se o usuário atingir mais de uma meta, por exemplo 100 e 500.
- Teste para marcar podcast como ouvido.
- Teste para não duplicar marcação do mesmo podcast.
- Teste para conceder emblema ao ouvir todos os podcasts ativos.
- Teste para não exigir podcasts inativos na conquista de todos ouvidos.
- Teste para conceder emblema de quantidade de podcasts ouvidos, por exemplo 10 ou 50.

## Ajustes no Formulário

No cadastro de emblemas:

1. Trocar os rótulos das categorias:
   - `Fixo` -> `Concedido`
   - `Roubável` -> `Competitivo`
   - `Programável` -> `Evento`
2. Adicionar opção `Conquista`.
3. Para `Conquista`, mostrar:
   - Origem: `Pedidos musicais` ou `Podcasts`
   - Gatilho de pedidos: `Pedidos atendidos`
   - Gatilhos de podcasts: `Todos os podcasts ouvidos` ou `Quantidade de podcasts ouvidos`
   - Meta numérica quando o gatilho exigir quantidade, exemplo `100`, `500`, `10`, `50`
4. Para pedidos atendidos, enviar `trigger = song_request.played_total`.
5. Para todos os podcasts ouvidos, enviar `trigger = podcast.all_listened`.
6. Para quantidade de podcasts ouvidos, enviar `trigger = podcast.listened_total`.
7. Enviar `threshold` como número inteiro quando necessário.

## Criação dos Emblemas

Não é obrigatório criar os emblemas antes da implementação.

Depois que o tipo **Conquista** estiver pronto, os administradores poderão criar pelo painel:

- Nome: `100 Pedidos atendidos`
- Categoria: `Conquista`
- Origem: `Pedidos musicais`
- Gatilho: `Pedidos atendidos`
- Meta: `100`

E outro:

- Nome: `500 Pedidos atendidos`
- Categoria: `Conquista`
- Origem: `Pedidos musicais`
- Gatilho: `Pedidos atendidos`
- Meta: `500`

Para podcasts:

- Nome: `Maratonista AkibaCast`
- Categoria: `Conquista`
- Origem: `Podcasts`
- Gatilho: `Todos os podcasts ouvidos`

Ou:

- Nome: `50 podcasts ouvidos`
- Categoria: `Conquista`
- Origem: `Podcasts`
- Gatilho: `Quantidade de podcasts ouvidos`
- Meta: `50`

## Cuidados

- Não alterar a regra existente de emblemas competitivos.
- Não trocar os valores antigos no banco sem necessidade.
- Evitar duplicidade de `BadgeAssignment`.
- Contar apenas pedidos atendidos (`was_reproduced = true`), não pedidos enviados.
- O gatilho deve rodar quando um pedido passa a atendido, não em qualquer edição.
- Se um pedido já estava atendido e o botão for acionado de novo, não deve conceder emblema duplicado.
- O marcador de podcast ouvido não deve substituir métrica real de player; ele é uma declaração do usuário.
- A conquista de todos os podcasts deve considerar apenas podcasts ativos no momento da avaliação.
- Se novos podcasts forem publicados depois, quem já ganhou o emblema mantém o emblema. Não revogar automaticamente.

## Ordem Recomendada de Implementação

1. Adicionar constante `TYPE_ACHIEVEMENT` em `Badge`.
2. Atualizar validações de `StoreBadgeRequest` e `UpdateBadgeRequest`.
3. Atualizar `BadgeResource` com rótulo `Conquista`.
4. Atualizar `BadgeForm.svelte` para exibir categorias novas e campo de meta.
5. Adicionar método no `BadgeService` para conceder conquistas por pedidos atendidos.
6. Chamar esse método em `LocutionService::markSongRequestAsPlayed()` após marcar o pedido como reproduzido.
7. Criar tabela/model/service para podcasts ouvidos.
8. Adicionar rota e ação pública para marcar podcast como ouvido.
9. Expor no `PodcastResource` se o podcast atual já foi marcado como ouvido pelo usuário.
10. Adicionar botão/estado em `ReadPodcast.svelte`.
11. Adicionar método no `BadgeService` para conceder conquistas de podcasts.
12. Criar testes focados nos fluxos de pedidos atendidos e podcasts ouvidos.
13. Criar os emblemas reais no painel.
