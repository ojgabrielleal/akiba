# Regras De Requests

Escopo: tudo em `app/Http/Requests`.

## Regra Principal

Requests concentram autorizacao, validacao e normalizacao de payloads de entrada.

## Estrutura

- Classes de request devem ficar em pastas de escopo dentro de `app/Http/Requests`.
- Exemplos: `Post/StorePostRequest.php`, `Program/UpdateProgramRequest.php`.
- Comportamento compartilhado de requests deve ficar em `LoggedWebRequest`.
- Requests de formularios web devem estender `LoggedWebRequest`.
- Nomes de request devem descrever a operacao e o modulo, como `StorePostRequest`, `UpdateProgramRequest` ou `AuthLoginRequest`.

## Responsabilidades

- Cada request concreta deve definir `authorize(): bool`.
- Autorizacao deve usar policies do Laravel via `$this->user()?->can(...)` quando o request estiver ligado a um model protegido.
- Exemplo: `$this->user()?->can('create', Post::class) ?? false`.
- Exemplo: `$this->user()?->can('update', $this->route('post')) ?? false`.
- Requests publicas ou de autenticacao que nao exigem policy de model podem retornar `true` em `authorize()`.
- Cada request concreta deve definir `rules(): array`.
- Regras de validacao devem ficar dentro do request, nao nos controllers.
- Use `prepareForValidation()` quando dados de entrada precisarem ser normalizados antes das regras.

## Validacao

- Mantenha regras explicitas e proximas do formato do payload.
- Arrays aninhados devem validar campos internos com dot notation.
- Exemplos: `references.*.name`, `tags.*.uuid`, `metadata.address`.
- Use `required_if`, `required_unless`, `required_with` e regras nullable para modelar comportamento condicional de formulario.

## Finalizacao

- `LoggedWebRequest` deve manter logs de validacao e autorizacao centralizados.
- Nao duplique logs de falha de validacao em requests concretos.
- Mantenha campos sensiveis fora dos logs por meio de `safeInputForLog()`.
