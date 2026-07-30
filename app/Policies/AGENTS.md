# Regras De Policies

Escopo: tudo em `app/Policies`.

## Regra Principal

Policies concentram autorizacao por model ou acao, mantendo regras finas e previsiveis.

## Estrutura

- Policies devem ficar na raiz de `app/Policies`.
- Cada policy deve ser nomeada pelo model protegido, seguida de `Policy`.
- Exemplos: `PostPolicy`, `ProgramPolicy`, `UserPolicy`.
- Policies devem importar o model protegido e `App\Models\User` com `use` antes da declaracao da classe.

## Responsabilidades

- Metodos de autorizacao devem retornar `bool`.
- Metodos CRUD padrao devem seguir a nomenclatura do Laravel.
- Exemplos: `viewAny(User $user): bool`, `view(User $user, Model $model): bool`, `create(User $user): bool`, `update(User $user, Model $model): bool`, `delete(User $user, Model $model): bool`.
- Permissoes nao CRUD devem usar nomes explicitos que descrevem a acao.
- Exemplos: `deactivate`, `approve`, `vote`, `refreshRanking`, `toggleBoxStatus`.
- Checks de permissao devem passar pelo helper de permissoes do usuario.
- Exemplo: `$user->hasPermission('post.create')`.
- Chaves de permissao devem seguir o padrao modulo-acao ja usado no projeto.
- Exemplos: `post.list`, `program.update`, `poll.vote`.

## Finalizacao

- Mantenha policies finas.
- Nao carregue services extras nem execute operacoes de banco dentro de policies.
- Se uma regra crescer alem de um check simples de permissao, mova a logica de dominio para outro lugar e mantenha a policy focada em autorizacao.
