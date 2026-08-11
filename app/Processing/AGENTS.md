# Regras De Processing

Escopo: tudo em `app/Processing`.

## Regra Principal

Processing concentra processamento interno reutilizavel que nao e regra de negocio direta e nao e integracao externa.

## Estrutura

- Arquivos ficam direto na raiz de `app/Processing`.
- Use sufixo `Process`, nao `Service`.
- Exemplos: `ImageProcess`, `AudienceCollectorProcess`.
- Nao crie subpastas sem necessidade clara de subescopo.

## Responsabilidades

- Use esta camada para manipulacao de arquivos, transformacao de dados, coleta interna e rotinas reutilizaveis.
- Nao coloque regras de negocio de modulo aqui; isso pertence a `app/Services`.
- Nao coloque chamadas para APIs externas aqui; isso pertence a `app/Integrations`.
- Processing pode ser injetado em services quando o fluxo de negocio precisar desse processamento.

## Organizacao Interna

- Atributos e construtor devem aparecer logo apos abertura da classe.
- Use nomes de metodos orientados ao processamento executado, como `store`, `delete`, `collect`.
- Mantenha dependencias explicitas via construtor quando houver colaborador, como uma integration.

## Finalizacao

- Mantenha os processos pequenos, previsiveis e reutilizaveis.
- Cubra processamento com testes focados em `tests/Unit/Processing` ou em `tests/Unit/Services` quando ainda existir pasta legada.
