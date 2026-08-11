# Regras De Integrations

Escopo: tudo em `app/Integrations`.

## Regra Principal

Integrations concentram comunicacao com servicos externos, APIs, webhooks, streams e provedores de terceiros.

## Estrutura

- Arquivos ficam direto na raiz de `app/Integrations`, exceto quando houver subescopo real.
- Nao use pastas `External` ou `Process`.
- Processamento interno reutilizavel pertence a `app/Processing`.
- Regras de negocio pertencem a `app/Services`.

## Responsabilidades

- Cada integration deve encapsular detalhes externos, como URL, headers, payload, timeout e tratamento basico de falha.
- Leia credenciais e URLs por `config/services.php`, nunca direto de `env()` fora de arquivos de config.
- Nao misture persistencia de dominio com chamadas externas.
- OAuth publico usa Laravel Socialite; nao recrie integrations manuais para redirect, troca de token ou busca de usuario do provider.
- Push notification usa Web Push nativo por `PushNotificationService`; nao reintroduza OneSignal.

## Finalizacao

- Retorne dados simples ou DTOs/arrays quando fizer sentido para o service consumidor.
- Registre falhas externas com contexto suficiente, mas sem vazar secrets.
- Em testes, isole chamadas externas com fakes/mocks do Laravel ou do contrato usado.
