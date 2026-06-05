# Webhook do Discord e Estabilização de Redirecionamentos

Esta atualização resolveu problemas críticos de integração com o Discord em ambiente de produção e estabilizou o comportamento de navegação no painel de locução, evitando redirecionamentos inesperados para a página inicial do site.

## 1. Correção do Webhook do Discord em Produção

O Webhook do Discord não estava sendo disparado em produção devido ao uso da função `env()` diretamente no serviço. No Laravel, após o cache de configurações (`config:cache`), a função `env()` retorna `null` fora dos arquivos da pasta `config/`.

- **Mapeamento de Configuração**: Adicionada a chave ` discord.webhook` em `config/services.php` apontando para a variável de ambiente `DISCORD_STREAM_WEBHOOK`.
- **Atualização do Serviço**: O `DiscordWebhookService.php` foi atualizado para utilizar `config('services.discord.webhook')`, garantindo funcionamento mesmo com cache ativado.
- **Melhoria da Mensagem**: Adicionada a menção `@everyone` na mensagem enviada ao Discord para notificar todos os usuários do servidor sobre o início da programação ao vivo.

## 2. Estabilização de Redirecionamentos (Botão "Atender")

Foi corrigido um bug onde clicar no botão "Atender" (ícone de Like) nos pedidos musicais redirecionava o usuário para a raiz (`/`) do site em vez de mantê-lo no painel.

- **Fim dos Retornos Nulos**: Os métodos no `LocutionController.php` que retornavam `null` em caso de falha de permissão foram atualizados para retornar redirecionamentos válidos. No Inertia, requisições de ação (POST/PATCH/DELETE) precisam de um redirecionamento para atualizar os dados da página.
- **Redirecionamento Robusto**: O Trait `HasFlashMessages.php` foi otimizado para usar `redirect()->to(url()->previous())`, garantindo que o usuário permaneça na mesma URL de onde disparou a ação, mesmo em ambientes de produção onde o cabeçalho `Referer` pode ser instável.

## 3. Melhorias de Performance e UX

- **Ajuste de Polling**: O intervalo de atualização automática (polling) dos pedidos musicais no `SongRequestGrid.svelte` foi reduzido de 60 para 30 segundos, proporcionando um feedback mais rápido para o DJ sobre novos pedidos.
- **Build de Produção**: Executado `npm run build` para garantir que todos os assets e componentes Svelte estejam otimizados e prontos para o ambiente de produção após as alterações na interface.
