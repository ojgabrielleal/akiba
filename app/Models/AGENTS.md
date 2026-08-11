# Regras De Models

Escopo: tudo em `app/Models`.

## Regra Principal

Models representam persistencia, casts, scopes, relationships e atributos simples do dominio.

## Estrutura

- Models devem ficar na raiz de `app/Models`.
- Comportamento compartilhado de models deve ficar em `app/Models/Concerns`.
- Exemplo: `HasPermissions`.
- Models que precisam de factories devem usar `HasFactory`.
- Models com colunas UUID devem usar `HasUuids` e definir `uniqueIds(): array`.
- O metodo deve retornar `['uuid']` quando o model usa a coluna `uuid`.

## Configuracao

- Mantenha configuracao do model perto do topo da classe.
- Ordem recomendada: `$fillable`, `$hidden`, `$casts`.
- Use `$fillable` para campos aceitos por mass assignment.
- Use `$hidden` para foreign keys internas e valores sensiveis.
- Exemplos: `user_id`, `activity_id`, `password`, `remember_token`.
- Use `$casts` para booleanos, arrays, datas e formatacao de hora/data.
- Exemplos: `is_active`, `is_virtual`, `metadata`, `phrases`, `birth_date`.

## Organizacao Interna

- Use objetos `Attribute` do Eloquent para accessors e mutators.
- Exemplos: hash de senha, geracao de slug a partir de titulo ou nickname.
- Query scopes devem usar o atributo `#[Scope]` do Laravel e ser metodos protegidos.
- Exemplo: `protected function active(Builder $query): void`.
- Mantenha logica de query reutilizavel em scopes do model.
- Exemplos: `active`, `upcoming`, `withStatus`, `authoredBy`, `forModule`, `availableForLocution`.
- Metodos de relationship devem ficar agrupados depois dos scopes.
- Relationships devem usar foreign keys explicitas quando o projeto ja faz isso.
- Exemplos: `hasMany(Post::class, 'user_id')`, `belongsTo(User::class, 'user_id')`.
- Prefira nomes expressivos de relationship que combinem com o dominio.
- Exemplos: `author`, `host`, `responsible`, `favorites`, `reviews`, `programAirtimes`.

## Finalizacao

- Mantenha models focados em persistencia, casts, scopes, relationships e atributos simples.
- Fluxos de negocio pertencem a `app/Services`.
- Composicao de query para paginas pertence ao service do escopo correspondente, normalmente em metodo `filter()`.
- Processamento reutilizavel que nao e regra de negocio direta pertence a `app/Processing`.
