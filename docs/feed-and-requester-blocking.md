# Feed externo e bloqueio individual de pedidos

**Estado atual:** Comic Natalie removida do catálogo e da configuração de capas a pedido do usuário, devido ao bloqueio de acesso. A investigação abaixo é histórica; ANN e as fontes anteriores permanecem disponíveis. O leitor Atom continua genérico, com fonte fictícia apenas nos testes.

## Arquitetura

O feed externo é uma consulta sob demanda de `PostController`, por props deferred do Inertia. Não há sincronizador, job, tabela de notícias externas ou inserção automática de matérias. `AnimeNewsFeedService` agrega e pagina a coleção; criar uma matéria continua sendo uma ação editorial separada. A coleção é deduplicada por ID (prefixado pela fonte) e URL. Consultar repetidamente não insere dados.

Fontes anteriores preservadas: Anime United, IntoxiAnime, AnimeNew e OtakuPT via WordPress REST; Crunchyroll via RSS. O catálogo foi centralizado em `config/services.php`, conforme as regras de integrações. O leitor compartilhado reconhece RSS 2.0 e Atom 1.0, normaliza o contrato existente (`id`, `source`, `title`, `url`, `excerpt`, `content`, `published_at`, `image`) e mantém logging e isolamento de falhas por fonte. Não foram adicionadas dependências ou scraping de artigos.

## Investigação externa — 16/10/2026

Requisições GET reais, sem contornar proteção:

| Fonte | Endpoint | HTTP final | Content-Type | Estrutura |
| --- | --- | --- | --- | --- |
| ANN | https://www.animenewsnetwork.com/news/rss.xml | 200 (301 inicial para `?ann-edition=us`) | application/rss+xml; charset=UTF-8 | rss/channel/item |
| Comic Natalie | https://natalie.mu/comic/feed/news | 200 | application/xml | feed/entry, namespace Atom |

ANN fornece `title`, `link`, `guid`, `description`, `pubDate`. O GUID é um permalink de CMS estável. Comic Natalie fornece `title`, `link[rel=alternate]@href`, `id` numérico, `summary`, `updated`. Não fornece `published`: `updated` é o fallback explícito, não uma garantia de data original de publicação. Não havia imagens nos feeds consultados; `image` fica nulo. O leitor aceita imagens em enclosure e Media RSS quando fornecidas.

A documentação oficial https://www.animenewsnetwork.com/encyclopedia/api.php descreve uma API de enciclopédia (anime/mangá/pessoas), não um feed de notícias. Não foi encontrada uma API pública documentada de notícias apropriada para Natalie. Os feeds no domínio de cada fonte foram validados diretamente; não é necessário recorrer a endpoints internos ou scraping. Foi escolhido Comic Natalie em vez do portal geral, evitando notícias de outras editorias. Os idiomas originais são preservados (en-US e ja).

## Bloqueio individual

Conforme esclarecimento do pedido, o bloqueio é de **uma conta**, persistente entre transmissões até o desbloqueio. O controle global `onairs.allows_song_requests` permanece independente.

`song_request_blocks` armazena a identidade polimórfica existente (`User` ou `OAuthAccount`), com índice único por tipo e ID. Não identifica por nome/IP e não bloqueia outra conta que tenha o mesmo ID numérico em outra tabela. Contas distintas do mesmo indivíduo não são vinculadas automaticamente.

O bloqueio usa somente `song.request.block` (bloquear) e `song.request.unblock` (desbloquear). Exibir o botão flutuante e a lista não exige permissão específica adicional dentro da locução. O controle global de pedidos continua usando `song.request.toggle`. PUT `/panel/locution/song-request/{uuid}/requester/block` bloqueia a conta do pedido; DELETE `/panel/locution/song-request/requester/block/{id}` desbloqueia. Há lista persistente de bloqueados, independente dos pedidos recentes. Ao bloquear, pedidos musicais pendentes dessa conta são cancelados e recados pendentes são dispensados, inclusive em outras transmissões. Itens já atendidos/lidos permanecem inalterados. Desbloquear não reabre os itens cancelados. O bloqueio impede novos envios pelo formulário de pedidos/recados dessa conta.

O envio verifica o bloqueio no service, dentro da transação, antes de criar música/pedido ou atualizar perfil. Bloquear e enviar travam a mesma linha da conta com `lockForUpdate`, serializando requisições concorrentes. O estado global também é consultado sob lock. Recusa retorna erro de validação (422 em JSON), inclusive ao chamar diretamente a rota com uma página antiga.

A prop pública `onair` inclui apenas o booleano do bloqueio da conta autenticada, sem expor lista de bloqueados. O polling existente de 10 segundos atualiza o formulário. Campos e envio ficam desabilitados, com mensagem explícita; uma tentativa antes do próximo polling é recusada pelo backend.

## Migration

`database/migrations/2026_10_16_000001_create_song_request_blocks_table.php` cria somente a tabela de bloqueios. Não transforma ou remove pedidos existentes.

## Teste manual

1. No painel de matérias, abrir Feeds externos e selecionar Anime News Network e Comic Natalie. Conferir título, resumo, link, idioma e data; ausência de imagem é esperada. Recarregar e conferir que matérias não são criadas automaticamente.
2. Iniciar uma transmissão e enviar um pedido com uma conta de ouvinte A. Enviar também com uma conta B.
3. No card do pedido de A, clicar em **Bloquear pedidos deste ouvinte**. Conferir indicação no card e lista de bloqueados. Pedidos pendentes são cancelados; itens atendidos permanecem no histórico.
4. Aguardar até 10 segundos na página de A: formulário desabilitado com mensagem. B continua enviando normalmente.
5. Repetir envio de A diretamente por POST `/song-request` (autenticado e com CSRF válido): deve retornar erro de validação, sem criar pedido.
6. Recarregar, encerrar a transmissão e iniciar outra: A continua bloqueado. Na lista do painel, desbloquear A e verificar que pode enviar novamente.
7. Com usuário sem `song.request.block` / `song.request.unblock`, tentar as respectivas rotas de bloquear e desbloquear: HTTP 403. Visitante não autenticado também não pode executar a ação.

## Verificação automatizada

`tests/Feature/Integrations/AnimeNewsFeedTest.php` cobre RSS, Atom, normalização, deduplicação, falha parcial e regressão WordPress/Crunchyroll.
`tests/Feature/Public/SongRequestBlockingTest.php` cobre envio aberto, bloqueio direto, isolamento por conta e tipo, permissões, persistência entre transmissões, desbloqueio e preservação de pedidos antigos.

Os testes usam `RefreshDatabase`. Para preservar o banco de desenvolvimento, foram executados com `DB_DATABASE=akiba_feature_test` no container. O script `run.sh` mencionado nas regras de testes não existe neste checkout; usamos `docker compose exec`.

```sh
docker compose exec -e DB_DATABASE=akiba_feature_test app php artisan test tests/Feature/Integrations/AnimeNewsFeedTest.php tests/Feature/Public/SongRequestBlockingTest.php
```

Não foi executado build frontend; os seis componentes Svelte alterados foram compilados em memória para validar a sintaxe.

## Arquivos

Criados:
- `app/Models/SongRequestBlock.php`
- `app/Services/SongRequestBlockService.php`
- `database/migrations/2026_10_16_000001_create_song_request_blocks_table.php`
- `tests/Feature/Integrations/AnimeNewsFeedTest.php`
- `tests/Feature/Public/SongRequestBlockingTest.php`
- `docs/feed-and-requester-blocking.md`

Modificados:
- `app/Integrations/AnimeNewsFeedService.php`
- `config/services.php`
- `app/Services/SongRequestService.php`
- `app/Services/LocutionService.php`
- `app/Services/OnairService.php`
- `app/Http/Controllers/Private/LocutionController.php`
- `app/Http/Middleware/HandleInertiaRequestsMiddleware.php`
- `app/Http/Resources/Onair/OnairResource.php`
- `app/Http/Resources/SongRequestResource.php`
- `routes/web/private.php`
- `resources/js/pages/private/Locution.svelte`
- `resources/js/lib/widgets/private/grid/SongRequestGrid.svelte`
- `resources/js/lib/widgets/public/form/SongRequestForm.svelte`
- `resources/js/lib/widgets/public/player/MainPlayer.svelte`
- `resources/js/lib/widgets/public/player/PlayerBar.svelte`
- `resources/js/lib/widgets/public/player/MobilePlayer.svelte`

## Capas complementares

Quando o feed não fornece imagem, a paginação consulta somente as matérias visíveis de ANN e Comic Natalie. `NewsArticleImageService` prioriza `og:image`, depois `twitter:image` e uma imagem dentro de `article`. Os metadados foram confirmados em páginas reais de ambas as fontes. A imagem é validada e armazenada como prévia local no disco público, sem copiar o artigo. O navegador usa `/storage/news-feed/...` para não depender do carregamento direto da fonte.

Cache de imagens por 24 horas e de ausência/falha por uma hora. Requests por fonte são serializados com lock e intervalo mínimo (ANN: 2 segundos, conforme robots.txt; Natalie: 1 segundo). Somente hosts/caminhos de notícias configurados em `services.news_article_images` são consultados, inclusive em redirecionamentos. Timeouts e falhas não removem notícias. A primeira visita pode demorar mais enquanto preenche o cache.

Consultados: robots.txt de ambas as fontes, https://www.animenewsnetwork.com/copyright-policy e https://natalie.mu/info/termsofuse. A existência de metadados não altera a titularidade das imagens. Mantemos os links e identificação da fonte no painel.

Arquivos desta extensão: novo `app/Integrations/NewsArticleImageService.php`, novo `tests/Feature/Integrations/NewsArticleImageTest.php`; alterações em `AnimeNewsFeedService.php`, `config/services.php` e nesta documentação.

As prévias aceitam somente JPEG, PNG, WebP e GIF válidos de até 5 MB, obtidos dos hosts de imagem configurados. HTML de bloqueio não é salvo como imagem. Falhas ficam em cache por uma hora; prévias válidas por 24 horas, com arquivos determinísticos por URL. O ambiente precisa do link padrão `php artisan storage:link` (criado localmente). A capa real de ANN foi validada pela aplicação em `localhost:8000/storage/news-feed/...`: HTTP 200, image/jpeg, 600 × 315. As URLs locais são relativas para acompanhar o host usado pelo navegador.

A migration `2026_10_17_000001_add_song_request_block_permissions.php` cadastra as duas permissões específicas e preserva o acesso dos cargos que antes usavam `song.request.toggle` para gerenciar bloqueios. Depois da migração, cada ação é autorizada independentemente.
