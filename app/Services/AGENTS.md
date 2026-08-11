# Regras De Services

Escopo: tudo em `app/Services`.

## Regra Principal

Services concentram regras de negocio, operacoes de dominio e composicao de queries por escopo.

## Estrutura

- Arquivos ficam direto na raiz de `app/Services`.
- Nao crie subpastas dentro de `app/Services`.
- Use um arquivo por escopo de dominio, como `UserService.php`, `PostService.php`, `ProgramService.php`, `SongRequestService.php`.
- Nao crie `Actions` ou `Filters`; essas responsabilidades foram incorporadas aos services.

## Responsabilidades

- Operacoes de escrita devem ficar em metodos com nomes de acao claros, como `store`, `update`, `deactivate`, `markSongRequestAsPlayed`.
- Composicao de query/listagem deve ficar no metodo `filter(array $filters = [])` do service do escopo.
- Use transactions com `DB::transaction()` para operacoes que alteram multiplas tabelas ou precisam de consistencia.
- Use `app/Processing` para processamento reutilizavel que nao e regra de negocio direta, como imagem ou coleta de audiencia.
- Use `app/Integrations` para chamadas a APIs, webhooks, streams e provedores externos.

## Organizacao Interna

- Atributos e construtor devem aparecer logo apos abertura da classe.
- Use promocao de propriedades no construtor, como `public function __construct(private ImageProcess $image) {}`.
- Mantenha metodos publicos de comportamento antes dos metodos privados auxiliares relacionados.
- Prefixe helpers privados pelo fluxo principal quando isso deixar a leitura clara, como `storeMusic`, `storeRequesterName`, `finishLiveOnair`.
- Evite metodos privados que apenas retornam um array usado em um unico lugar; incorpore o array no ponto de uso.

## Finalizacao

- Nao acesse request diretamente no service quando os dados puderem chegar como parametros.
- Nao retorne response HTTP ou Inertia de services; isso pertence aos controllers.
- Nao coloque SDK externo diretamente em service de negocio; encapsule em `app/Integrations`.
