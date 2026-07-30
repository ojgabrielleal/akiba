# Regras De Resources

Escopo: tudo em `app/Http/Resources`.

## Regra Principal

Resources concentram formato de resposta e apresentacao dos dados enviados para API ou Inertia.

## Estrutura

- Resources devem ficar em `app/Http/Resources`.
- Resources com multiplas classes relacionadas podem usar pastas de escopo.
- Exemplos: `Post`, `User`, `Poll`, `Program`, `Calendar`, `Onair`.
- Classes de resource devem ser nomeadas pelo model ou payload transformado, seguidas de `Resource`.
- Exemplos: `PostResource`, `UserResource`, `ProgramAirtimeResource`.
- Resources devem estender `JsonResource`.

## Formatos

- Resource collections que suportam variantes de apresentacao devem usar a concern `HasFormats`.
- Use `format(?string $format)` para formatos alternativos.
- Exemplos: `summary`, `featured`, `home-list`, `grouped`, `history`.
- Formatacao de collection deve passar por `FormattableResourceCollection`.
- Nao duplique tratamento de formato de collection em resources individuais.

## Organizacao Interna

- `toArray(Request $request): array` deve retornar o formato padrao do resource.
- Formatos especificos devem ser tratados no topo de `toArray()`.
- Exemplo: retorne cedo quando `$this->format === 'summary'`.
- Use resources aninhados para relacionamentos.
- Exemplos: `UserResource::make($this->author)`, `PostTagResource::collection($this->tags)`.
- Use `whenLoaded()` quando uma relation so deve ser serializada se tiver sido eager-loaded.
- Use pequenos metodos privados para fragmentos reutilizaveis ou condicionais de payload.
- Exemplos: builders de URL, payload de review do usuario atual, saida agrupada de collection.

## Finalizacao

- Mantenha resources focados em formato de resposta e apresentacao.
- Nao execute operacoes de escrita em resources.
- Evite queries de banco em resources; eager-load relationships em controllers ou filters.
