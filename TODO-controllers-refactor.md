# Proxima etapa de reorganizacao

## Filters

Precisamos vasculhar os controllers e identificar todos os metodos `index` que fazem listagem, busca, ordenacao, paginacao ou montagem de queries.

A ideia e mover essa responsabilidade para uma camada chamada `Filters`.

Com a criacao da camada `Filters`, devemos revisar e remover a pasta `Queries`, migrando o que ainda fizer sentido para os novos filtros.

Antes de implementar, perguntar ao Joao como ele quer organizar esses filtros:

- por escopo/model, como `Filters/Post`, `Filters/Poll`, `Filters/User`;
- por contexto de tela/modulo;
- por tipo de listagem;
- ou outro padrao que ele preferir.

## Model Scopes

Tambem precisamos revisar todos os scopes dos models para entender se ainda fazem sentido depois da reorganizacao.

Objetivos:

- verificar nomes dos scopes;
- remover scopes duplicados ou confusos;
- padronizar o estilo;
- garantir que scopes representem regras reutilizaveis do model;
- evitar scopes que escondem logica especifica demais de controller/tela.

## Traits

Tambem precisamos avaliar uma forma de centralizar os `Concerns` em uma pasta chamada `Traits`.

A ideia e revisar os `Concerns` existentes e decidir se faz sentido padronizar tudo como traits em uma estrutura mais explicita.

Antes de implementar, verificar:

- quais `Concerns` existem hoje;
- se todos sao realmente traits reutilizaveis;
- se algum concern deveria continuar perto do contexto onde e usado;
- qual namespace ficaria melhor, como `App\Traits`, `App\Support\Traits` ou outro padrao;
- se a mudanca melhora a leitura sem criar uma pasta generica demais.

## Policies

Tambem precisamos revisar as policies.

A organizacao atual da pasta `app/Policies` esta boa, porque cada policy principal bate com um model. Mesmo assim, existem alguns pontos para revisar:

- verificar se os controllers estao usando os metodos corretos das policies;
- `ActivityPolicy` tem `participate()`, mas o controller de confirmacao de participante usa `authorize('update', $activity)`;
- `TaskPolicy` tem `complete()`, mas o controller que marca task para review usa `authorize('update', $task)`;
- revisar se `TaskPolicy::complete()` deveria virar outro nome, como `review()`, ou se existe outra regra faltando;
- revisar `UserPolicy::update()`, porque hoje ela permite update para quem tem `user.view`, o que parece amplo demais;
- padronizar nomes de permissoes, especialmente `deactivate`, `remove`, `delete` e permissoes com nomes compostos como `songrequest.*` e `listener.gallery.*`;
- revisar autorizacoes diretas por string, como `listener.month.view` e `locution.finish`, e decidir se elas devem continuar assim ou migrar para policies.

## Routes

Tambem precisamos revisar as rotas.

Pontos encontrados:

- existem rotas apontando para metodos que aparentemente nao existem mais:
  - `Route::delete('{event:uuid}', 'deactivateEvent');`
  - `Route::patch('{user:uuid}', 'changeUserRoles');`
  - `Route::delete('{activity:uuid}', 'removeActivity');`
- revisar a rota `dashboard/task/{task:uuid}/complete`, porque ela usa `MarkTaskToReviewController` e a action marca a task como `in_review`; talvez a URL deva ser `review`, ou o controller/action devam mudar para `complete`;
- existe nome de rota `home` duplicado em `routes/web/public.php` e `routes/web/provisory.php`;
- revisar o prefixo repetido em `locution/locution`, gerado por `Route::prefix('locution')` dentro de outro prefixo `locution`;
- avaliar quebrar `routes/web/private.php` por escopo ou reorganizar a estrutura quando os controllers private forem reorganizados.

## Controllers Private

Vamos revisar os controllers dentro de `app/Http/Controllers/Private` e reorganizar por escopo.

A nova ideia para `Private`:

- `Private/Pages`: controllers responsaveis apenas por renderizar paginas e passar props iniciais via Inertia;
- `Private/Invokes`: controllers `__invoke` apenas para acoes especiais que fogem do CRUD padrao;
- `Private/{Escopo}`: controllers de cada escopo, responsaveis pelas acoes CRUD.

Tambem vamos acabar com controllers `__invoke` para `store` e `update` CRUD.

O padrao desejado e cada controller CRUD ter metodos explicitos para seu escopo, enquanto `__invoke` fica reservado para acoes especiais, como refresh, toggle, mark, confirm, deactivate, destroy especial, etc.
