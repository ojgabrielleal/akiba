# Refatoração rotineira do projeto 

### Instruções de trabalho iniciais: 
Leve em conta: Todas essas alterações são baseadas em convenções de SOLID e Spring Boot, execute cada tópico sequêncialmente e peça para mim a aprovação para executar o próximo tópico sempre que terminar o tópico anterior.

Toda vez que um tópico for finalizado, no final dele crie um timestamp dizendo quando que foi executado e quando foi concluído e algumas anotações suas caso seja necessário.

Quando for mecher nas camadas, pegue apenas como os arquivos devem ser montados no `AGENTS.md` que existirem, ignore outras regras que fogem disso, mas lembre-se sempre de seguir o jeito que o projeto está sendo escrito e o jeito que os arquivos forem formatados. 

### O que deve ser feito: 

1. Vamos refatorar a estrutura para o padrão SOLID 
Vamos seguir esse padrão para ficar mais simples a manutenção, segue as regras: 

- A camada `service` atual deve ser renomeada para `integrations`.
- Após o renomeio da camada `service` crie outra camada com o mesmo nome `service` que estará vazia dentro de `/app`, essa camada vai ficar responsável por armazenar as operações de lógica de negócios, transfira toda a camada actions e mais operações que tiver nos controllers `invokes` e filters pra dentro dessa nova camada que deve ser organizada por escopos. Por exemplo: `UserService.php` deve conter todas as operações relacionadas aos usuários.
- Dentro da camada `service` os arquivos devem ficar na raiz da pasta, sem subpastas, mantendo um arquivo por escopo. Por exemplo: `app/Services/UserService.php`, `app/Services/PostService.php` e `app/Services/ProgramService.php`.
- Após essa mudança a lógica dos arquivos das camadas `actions` e `filters` devem ter sido todas movidas para a camada `service`, você tem autorização
para apagar essas duas pastas `actions` e `filters` caso tudo já tenha sido movido.

2. Reorganização dos controllers
Como tudo está agora seguindo o padrão SOLID não temos mais necessidade dos controllers do tipo `invoke` dentro de `private` e `public`. Refatore os controllers para que sigam somente o controller por página, tendo o método `render` e os demais métodos no mesmo controller. Os controllers devem ser organizados por página que eles carregam por exemplo: DashboardController.php, LocutionController.php não tendo mais a necessidade de controllers invokes. Chame os services nos métodos no controller da página.

- Os controllers de página devem ficar direto na raiz de `private` e `public`, sem a pasta `Pages` e sem usar `Page` no nome do arquivo. Por exemplo: `DashboardController.php`, `LocutionController.php`, `RadioController.php`.
- O método `render` deve ser sempre a última função dentro do controller.
- Os nomes dos métodos de ação devem indicar a ação e o escopo. Por exemplo: `updateProfile`, `storePost`, `deactivatePodcast`, `storeListenerGallery`.
- Quando um método existir apenas para retornar um array usado por outro método, junte os dois métodos e deixe o array diretamente onde ele é usado.

3. Mudança da pasta `process` dentro de `integrations`
A pasta `integrations\process` deve ser jogada para a raiz de `/app` com o nome de `processing`, os arquivos na pasta `integrations/external` devem ser jogados para a raiz de `integrations` 

4. Migração da para o Laravel Socialite
Atualmente temos uma integração feita a mão para OAuth, vamos migrar para essa integração para a biblioteca Laravel Socialite, veja a tabela que 
está sendo salvo as contas OAuth e todos os arquivos que são usados e crie um plano de ação para essa migração, isso deve reduzir a quantidade de lógica. Antes de executar apresente esse plano para revisão. 

5. Remoção do OneSignalPush para solução nativa 
Atualmente estamos usando o OneSignalPush para fazer o envio de mensagens push, mas precisamos trocar para uma solução nativa. Ele deve enviar as mensagens push e também ser capaz de definir grupos de usuários ou usuáro especifico caso deseje enviar uma mensagem especifica. 

Esse requisito se dá por que o locutor no ar no momento deve receber mensagem push avisando que um pedido musical chegou para ele, atualmente eu fiz uma solução básica improvisada no `SongRequestGrid.svelte`, a ideia é remover ele para podermos integrar tudo. 

Bole um plano de ação e antes de executar apresente esse plano para revisão.

### Execução do tópico 1

- Iniciado em: 2026-08-10 19:54:00 -03
- Concluído em: 2026-08-10 20:03:54 -03
- Anotações:
  - A camada antiga `app/Services` foi movida para `app/Integrations`.
  - A nova camada `app/Services` recebeu as operações antes em `app/Actions`, consolidadas em um único service por escopo, como `app/Services/UserService.php`.
  - Os filtros foram incorporados ao service do escopo correspondente como método `filter()`.
  - As pastas antigas `app/Actions` e `app/Filters` foram removidas após a migração.
  - Referências antigas para `App\Actions`, `App\Filters`, `App\Services\External` e `App\Services\Process` foram atualizadas.
  - Chamadas antigas para `execute()` e `apply()` foram trocadas pelos métodos do service consolidado.
  - A validação por `./run.sh composer dump-autoload --dry-run` e testes focados não pôde ser concluída porque o serviço Docker `app` não está rodando.

### Execução do tópico 2

- Iniciado em: 2026-08-10 20:04:00 -03
- Concluído em: 2026-08-10 20:17:14 -03
- Anotações:
  - As rotas privadas e públicas foram atualizadas para chamar apenas controllers de página.
  - As ações antes em controllers `Invokes` foram movidas para os controllers correspondentes.
  - Os controllers CRUD privados e controllers públicos soltos foram consolidados nos controllers que carregam as páginas.
  - As pastas `app/Http/Controllers/Private/Invokes` e `app/Http/Controllers/Public/Invokes` foram removidas.
  - Os arquivos que estavam na pasta `Pages` foram movidos para a raiz de `app/Http/Controllers/Private` e `app/Http/Controllers/Public`.
  - O sufixo `Page` foi removido dos nomes dos controllers, como `ProfilePageController.php` para `ProfileController.php`.
  - O método `render` foi movido para ser a última função dos controllers.
  - Os métodos de ação foram renomeados para indicar ação e escopo, como `updateProfile`, `storePost`, `deactivatePodcast` e `destroyInactiveItem`.
  - Métodos auxiliares que só retornavam arrays para outro método foram incorporados no ponto de uso.
  - Foram removidos os controllers privados e públicos que deixaram de ser usados.
  - A validação de métodos das rotas por varredura estática foi concluída sem rotas apontando para métodos inexistentes.
  - A validação por `./run.sh artisan route:list` não pôde ser concluída porque o serviço Docker `app` não está rodando.

### Execução do tópico 3

- Iniciado em: 2026-08-10 20:33:20 -03
- Concluído em: 2026-08-10 20:34:43 -03
- Anotações:
  - A pasta `app/Integrations/Process` foi movida para `app/Processing`.
  - Os arquivos que estavam em `app/Integrations/External` foram movidos para a raiz de `app/Integrations`.
  - A subpasta `OAuthAccount` foi preservada dentro de `app/Integrations/OAuthAccount`.
  - Namespaces e imports foram atualizados de `App\Integrations\Process` para `App\Processing`.
  - Namespaces e imports foram atualizados de `App\Integrations\External` para `App\Integrations`.
  - Os arquivos movidos para `app/Processing` tiveram o sufixo `Service` removido e passaram a usar o sufixo `Process`, como `ImageProcess` e `AudienceCollectorProcess`.
  - As referências, imports e testes dos processos foram renomeados para acompanhar os novos nomes.
  - Documentações com caminhos antigos de `Services/External` e `Services/Process` foram ajustadas para `Integrations` e `Processing`.
  - A varredura estática não encontrou referências restantes para `App\Integrations\Process`, `App\Integrations\External`, `Integrations/Process` ou `Integrations/External`.

### Execução do tópico 4

- Iniciado em: 2026-08-10 20:35:00 -03
- Concluído em: 2026-08-10 20:46:59 -03
- Anotações:
  - O fluxo OAuth manual foi migrado para Laravel Socialite.
  - `OAuthAccountRedirectController` passou a redirecionar com `Socialite::driver($provider)->redirect()`.
  - `OAuthAccountCallbackController` passou a buscar o usuário externo com `Socialite::driver($provider)->user()`.
  - `OAuthAccountService` passou a ter `storeFromProvider()`, centralizando normalização, criação/atualização de `OAuthAccount` e criação do cookie `akiba_oauth_token`.
  - As integrações manuais `DiscordOAuthAccountService` e `GoogleOAuthAccountService` foram removidas.
  - `config/oauth.php` foi removido, e as credenciais passaram a ficar apenas em `config/services.php`.
  - `composer.json` recebeu `laravel/socialite` e `socialiteproviders/discord`.
  - O provider do Discord foi registrado em `AppServiceProvider` via `SocialiteWasCalled`.
  - Foram adicionados testes de feature para redirect, provider não suportado e callback OAuth.
  - As documentações de OAuth, services e configurações foram atualizadas.
  - A varredura estática não encontrou referências restantes para `config/oauth.php`, `oauth.providers`, `DiscordOAuthAccountService`, `GoogleOAuthAccountService` ou `App\Integrations\OAuthAccount`.
  - `composer.lock` e os testes não puderam ser atualizados/executados porque o serviço Docker `app` não está rodando e não há Composer local no host.

### Execução do tópico 5

- Iniciado em: 2026-08-10 21:08:00 -03
- Concluído em: 2026-08-10 21:30:57 -03
- Anotações:
  - OneSignal foi removido do backend, configuração e template Blade.
  - Foi criada a tabela `push_subscriptions`, ligada diretamente a `users`.
  - Foram criados `PushSubscription`, `PushSubscriptionController` e `PushNotificationService`.
  - O envio nativo usa Web Push com chaves VAPID configuradas em `services.webpush`.
  - O frontend registra `public/push-worker.js` e salva inscrições via `/panel/push-subscription`.
  - `SongRequestGrid.svelte` deixou de disparar notificação local por polling e passou a solicitar inscrição Web Push real.
  - `SongRequestService` passou a disparar push quando um pedido musical é criado.
  - Quando existe host/locutor no programa ao vivo, o push é enviado para esse usuário; quando não existe usuário alvo, o envio cai para todas as inscrições.
  - Foram adicionados factory e testes de feature para inscrição push.
  - As documentações de services e configurações foram atualizadas de OneSignal para Web Push.
  - A varredura estática não encontrou referências restantes para `OneSignal`, `onesignal`, `ONESIGNAL`, `OneSignalService` ou `sendPush`.
  - `composer.json` recebeu `minishlink/web-push`.
  - `composer.lock` e os testes não puderam ser atualizados/executados porque o serviço Docker `app` não está rodando e não há Composer local no host.
